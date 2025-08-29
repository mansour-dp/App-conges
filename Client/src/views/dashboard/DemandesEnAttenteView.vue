<template>
  <div class="demandes-en-attente-container">
    <!-- Section de filtrage -->
    <div class="filters-section">
      <!-- Barre de recherche -->
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input
          v-model="searchTerm"
          type="text"
          placeholder="Rechercher par nom, matricule ou département..."
        />
      </div>

      <!-- Filtres par type de demande -->
      <div class="type-filter-buttons">
        <button
          v-for="typeFilter in typeFilters"
          :key="typeFilter.value"
          @click="setTypeFilter(typeFilter.value)"
          :class="['filter-btn', { active: currentTypeFilter === typeFilter.value }]"
        >
          <i :class="typeFilter.icon"></i>
          {{ typeFilter.label }}
        </button>
      </div>

      <!-- Filtres par statut -->
      <div class="status-filter-buttons">
        <button
          v-for="filter in filters"
          :key="filter.value"
          @click="setFilter(filter.value)"
          :class="['filter-btn', { active: currentFilter === filter.value }]"
        >
          {{ filter.label }}
        </button>
      </div>
    </div>

    <!-- État de chargement -->
    <div v-if="loading" class="loading-container">
      <div class="spinner"></div>
      <p>Chargement des demandes...</p>
    </div>

    <!-- Message d'erreur -->
    <div v-else-if="error" class="error-container">
      <i class="fas fa-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <button @click="chargerDemandes" class="retry-btn">
        <i class="fas fa-redo"></i>
        Réessayer
      </button>
    </div>

    <!-- Grille unifiée des cartes de demandes -->
    <div v-else class="demandes-container">
      <!-- Message si aucune demande -->
      <div v-if="finalFilteredDemandes.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>Aucune demande en attente</h3>
        <p>Il n'y a actuellement aucune demande à traiter.</p>
      </div>

      <!-- Grille des cartes côte à côte -->
      <div v-else class="demandes-grid-unified">
        <div
          v-for="demande in finalFilteredDemandes"
          :key="`${demande.type_source}-${demande.id}`"
          :class="['demande-card', demande.type_source]"
        >
          <!-- En-tête de la carte avec type et statut -->
          <div class="demande-header">
            <div class="demande-type-info">
              <i :class="demande.type_icon" :style="{ color: demande.type_color }"></i>
              <span class="demande-type">{{ demande.type_label }}</span>
            </div>
            <span :class="['demande-status', getStatusClass(demande.statut)]">
              {{ getStatusText(demande.statut) }}
            </span>
          </div>

          <!-- Informations employé -->
          <div class="demande-employee">
            <div class="employee-info">
              <h3>{{ getUserFullName(demande.user) }}</h3>
              <span class="employee-details">
                <i class="fas fa-id-badge"></i>
                {{ demande.user?.matricule }}
                <span class="separator">•</span>
                <i class="fas fa-building"></i>
                {{ demande.user?.department?.nom || 'Département non défini' }}
              </span>
            </div>
          </div>

          <!-- Détails de la demande -->
          <div class="demande-details">
            <!-- Détails spécifiques au type de congé -->
            <div v-if="demande.type_source === 'conges'" class="details-grid">
              <div class="detail-item">
                <i class="fas fa-tag"></i>
                <span>{{ demande.type_conge?.nom || 'Type non spécifié' }}</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-calendar"></i>
                <span>{{ formatDate(demande.date_debut) }} - {{ formatDate(demande.date_fin) }}</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-clock"></i>
                <span>{{ demande.duree_jours }} jour(s)</span>
              </div>
            </div>

            <!-- Détails spécifiques au type d'absence -->
            <div v-else-if="demande.type_source === 'absences'" class="details-grid">
              <div class="detail-item">
                <i class="fas fa-tag"></i>
                <span>{{ demande.type_absence?.nom || 'Type non spécifié' }}</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-calendar"></i>
                <span>{{ formatDate(demande.date_debut) }} - {{ formatDate(demande.date_fin) }}</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-clock"></i>
                <span>{{ demande.duree_jours }} jour(s)</span>
              </div>
            </div>

            <!-- Détails spécifiques au report -->
            <div v-else-if="demande.type_source === 'reports'" class="details-grid">
              <div class="detail-item">
                <i class="fas fa-calendar-alt"></i>
                <span>Report de congé</span>
              </div>
              <div class="detail-item">
                <i class="fas fa-calendar-check"></i>
                <span>Nouvelles dates: {{ formatDate(demande.nouvelle_date_debut) }} - {{ formatDate(demande.nouvelle_date_fin) }}</span>
              </div>
              <div class="detail-item" v-if="demande.conge_original">
                <i class="fas fa-history"></i>
                <span>Congé original: {{ formatDate(demande.conge_original.date_debut) }} - {{ formatDate(demande.conge_original.date_fin) }}</span>
              </div>
            </div>

            <!-- Motif -->
            <div v-if="demande.motif" class="motif-section">
              <h4><i class="fas fa-comment-alt"></i> Motif</h4>
              <p>{{ demande.motif }}</p>
            </div>

            <!-- Date de demande -->
            <div class="date-demande">
              <small>
                <i class="fas fa-calendar-plus"></i>
                Demandé le {{ formatDate(demande.created_at) }}
              </small>
            </div>
          </div>

          <!-- Actions -->
          <div class="demande-actions">
            <button
              @click="openValidationModal(demande)"
              class="validate-btn"
            >
              <i class="fas fa-check-circle"></i>
              Traiter la demande
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de validation -->
    <ValidationModal
      v-model="showValidationModal"
      :demande="selectedDemande"
      @submit="handleValidationResult"
    />
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue'
import { useToast } from 'primevue/usetoast'
import { useUserStore } from '@/stores/users'
import { useNotificationsStore } from '@/stores/notifications'
import ValidationModal from '@/components/workflow/ValidationModal.vue'
import { demandesApi, demandesAbsenceApi, demandesReportApi } from '@/services/api'

export default {
  name: "DemandesEnAttenteView",
  components: {
    ValidationModal
  },
  setup() {
    const toast = useToast()
    const notificationsStore = useNotificationsStore()
    const userStore = useUserStore()
    const searchTerm = ref("")
    const currentFilter = ref("toutes")
    const currentTypeFilter = ref("tous") // Nouveau filtre par type
    const showValidationModal = ref(false)
    const selectedDemande = ref(null)
    const isLastValidator = ref(false)
    const loading = ref(false)
    const error = ref(null)

    // Mapping des rôles vers les statuts qu'ils peuvent voir
    const ROLE_STATUS_MAP = {
      'Superieur': ['en_attente_superieur'],
      'Directeur Unité': ['en_attente_superieur', 'en_attente_directeur_unite'],
      'Responsable RH': ['en_attente_superieur', 'en_attente_responsable_rh'],
      'Directeur RH': ['en_attente_superieur', 'en_attente_directeur_rh'],
      'Admin': ['en_attente_superieur', 'en_attente_directeur_unite', 'en_attente_responsable_rh', 'en_attente_directeur_rh']
    }

    // Récupérer le rôle de l'utilisateur connecté
    const currentUserRole = computed(() => {
      return userStore.userRole?.nom || userStore.userRole?.name || null
    })

    // Statuts autorisés pour ce rôle
    const allowedStatuses = computed(() => {
      return ROLE_STATUS_MAP[currentUserRole.value] || []
    })

    // Filtres par type de demande
    const typeFilters = computed(() => [
      { label: "Tous", value: "tous", icon: "fas fa-list" },
      { label: "Congés", value: "conges", icon: "fas fa-calendar-alt" },
      { label: "Absences", value: "absences", icon: "fas fa-user-times" },
      { label: "Reports", value: "reports", icon: "fas fa-exchange-alt" }
    ])

    // Filtres dynamiques basés sur le rôle
    const filters = computed(() => {
      const baseFilters = [{ label: "Toutes", value: "toutes" }]
      const allowedStatusList = allowedStatuses.value
      
      const statusFilters = [
        { label: "En attente supérieur", value: "en_attente_superieur" },
        { label: "En attente directeur", value: "en_attente_directeur_unite" },
        { label: "En attente RH", value: "en_attente_responsable_rh" },
        { label: "En attente DRH", value: "en_attente_directeur_rh" },
      ].filter(filter => allowedStatusList.includes(filter.value))

      return [...baseFilters, ...statusFilters]
    })

    const demandes = ref([])

    // Charger les demandes depuis l'API - TOUS LES TYPES
    const chargerDemandes = async () => {
      try {
        loading.value = true
        error.value = null
        
        console.log('🔄 Chargement de tous les types de demandes pour le rôle:', currentUserRole.value)
        console.log('📋 Statuts autorisés:', allowedStatuses.value)
        
        // Récupérer toutes les demandes en parallèle
        const [congesResponse, absencesResponse, reportsResponse] = await Promise.allSettled([
          demandesApi.getDemandesRecues(),
          demandesAbsenceApi.getDemandesRecues(),
          demandesReportApi.getDemandesRecues()
        ])
        
        let allDemandes = []
        
        // Traitement des congés
        if (congesResponse.status === 'fulfilled' && congesResponse.value.data?.success) {
          const conges = congesResponse.value.data.data || []
          conges.forEach(demande => {
            demande.type_source = 'conges'
            demande.type_label = 'Congé'
            demande.type_icon = 'fas fa-calendar-alt'
            demande.type_color = '#3b82f6'
          })
          allDemandes = [...allDemandes, ...conges]
        } else if (congesResponse.status === 'rejected') {
          console.warn('⚠️ Erreur lors du chargement des congés:', congesResponse.reason)
        }
        
        // Traitement des absences
        if (absencesResponse.status === 'fulfilled' && absencesResponse.value.data?.success) {
          const absences = absencesResponse.value.data.data || []
          absences.forEach(demande => {
            demande.type_source = 'absences'
            demande.type_label = 'Absence'
            demande.type_icon = 'fas fa-user-times'
            demande.type_color = '#ef4444'
          })
          allDemandes = [...allDemandes, ...absences]
        } else if (absencesResponse.status === 'rejected') {
          console.warn('⚠️ Erreur lors du chargement des absences:', absencesResponse.reason)
        }
        
        // Traitement des reports
        if (reportsResponse.status === 'fulfilled' && reportsResponse.value.data?.success) {
          const reports = reportsResponse.value.data.data || []
          reports.forEach(demande => {
            demande.type_source = 'reports'
            demande.type_label = 'Report'
            demande.type_icon = 'fas fa-exchange-alt'
            demande.type_color = '#f59e0b'
          })
          allDemandes = [...allDemandes, ...reports]
        } else if (reportsResponse.status === 'rejected') {
          console.warn('⚠️ Erreur lors du chargement des reports:', reportsResponse.reason)
        }
        
        demandes.value = allDemandes
        console.log('✅ Total demandes récupérées:', allDemandes.length)
        console.log('📊 Par type:', {
          conges: allDemandes.filter(d => d.type_source === 'conges').length,
          absences: allDemandes.filter(d => d.type_source === 'absences').length,
          reports: allDemandes.filter(d => d.type_source === 'reports').length
        })
        
        // Log des statuts des demandes reçues
        const statusCount = {}
        allDemandes.forEach(d => {
          statusCount[d.statut] = (statusCount[d.statut] || 0) + 1
        })
        console.log('📊 Répartition par statut:', statusCount)
        
      } catch (err) {
        console.error('❌ Erreur lors du chargement des demandes:', err)
        error.value = `Erreur ${err.response?.status || 'inconnue'}: ${err.response?.data?.message || err.message}`
        
        toast.add({
          severity: 'error',
          summary: 'Erreur',
          detail: `Erreur lors du chargement des demandes: ${err.response?.status || err.message}`,
          life: 5000
        })
        demandes.value = []
      } finally {
        loading.value = false
      }
    }

    const filteredDemandes = computed(() => {
      let filtered = demandes.value

      // 1️⃣ FILTRAGE PAR RÔLE (prioritaire)
      // Ne montrer que les demandes avec les statuts autorisés pour ce rôle
      filtered = filtered.filter(demande => 
        allowedStatuses.value.includes(demande.statut)
      )

      // 2️⃣ FILTRAGE PAR TYPE DE DEMANDE
      if (currentTypeFilter.value !== "tous") {
        filtered = filtered.filter((d) => d.type_source === currentTypeFilter.value)
      }

      // 3️⃣ FILTRAGE PAR STATUT (si un filtre spécifique est sélectionné)
      if (currentFilter.value !== "toutes") {
        filtered = filtered.filter((d) => d.statut === currentFilter.value)
      }

      // 4️⃣ FILTRAGE PAR RECHERCHE
      if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase()
        filtered = filtered.filter(
          (d) =>
            d.user?.name?.toLowerCase().includes(term) ||
            d.user?.first_name?.toLowerCase().includes(term) ||
            d.user?.matricule?.toLowerCase().includes(term) ||
            d.user?.department?.nom?.toLowerCase().includes(term)
        )
      }

      return filtered
    })

    // Demandes finales après tous les filtrages
    const finalFilteredDemandes = computed(() => {
      return filteredDemandes.value || []
    })

    const formatDate = (dateString) => {
      if (!dateString) return ""
      const options = { year: "numeric", month: "2-digit", day: "2-digit" }
      return new Date(dateString).toLocaleDateString("fr-FR", options)
    }

    const getStatusClass = (status) => {
      switch (status) {
        case "en_attente_superieur":
        case "en_attente_directeur_unite":
        case "en_attente_responsable_rh":
        case "en_attente_directeur_rh":
          return "pending"
        case "approuve":
          return "approved"
        case "rejete":
          return "rejected"
        default:
          return ""
      }
    }

    const getStatusText = (status) => {
      switch (status) {
        case "en_attente_superieur":
          return "En attente supérieur"
        case "en_attente_directeur_unite":
          return "En attente directeur"
        case "en_attente_responsable_rh":
          return "En attente RH"
        case "en_attente_directeur_rh":
          return "En attente DRH"
        case "approuve":
          return "Approuvé"
        case "rejete":
          return "Rejeté"
        default:
          return status
      }
    }

    const getUserFullName = (user) => {
      return user ? `${user.first_name || ""} ${user.name || ""}` : "Utilisateur inconnu"
    }

    const openValidationModal = (demande) => {
      selectedDemande.value = demande
      showValidationModal.value = true
    }

    const closeValidationModal = () => {
      showValidationModal.value = false
      selectedDemande.value = null
    }

    const handleValidationResult = async (validationData) => {
      try {
        console.log('🎯 Validation des données reçues:', validationData)
        console.log('📋 Demande sélectionnée:', selectedDemande.value)
        
        if (!selectedDemande.value || !validationData) {
          throw new Error('Données de validation manquantes')
        }
        
        let response
        const typeSource = selectedDemande.value.type_source
        
        // Préparer les données pour l'API backend
        const apiData = {
          demande_id: selectedDemande.value.id,
          decision: validationData.decision,
          commentaire: validationData.commentaire,
          signature: validationData.signature
        }
        
        // Si approuvé et qu'un utilisateur suivant est sélectionné
        if (validationData.decision === 'approuve' && validationData.selectedUser) {
          apiData.next_validator_email = validationData.selectedUser.email
        }
        
        // Appeler la bonne API selon le type de demande
        console.log(`📞 Appel API pour ${typeSource} avec:`, apiData)
        
        if (typeSource === 'conges') {
          response = await demandesApi.validerAvecSuivant(apiData)
        } else if (typeSource === 'absences') {
          response = await demandesAbsenceApi.validerAvecSuivant(apiData)
        } else if (typeSource === 'reports') {
          response = await demandesReportApi.validerAvecSuivant(apiData)
        } else {
          throw new Error(`Type de demande non supporté: ${typeSource}`)
        }
        
        console.log('✅ Réponse API:', response.data)
        
        // Traiter la réponse de l'API
        if (response.data.success) {
          toast.add({
            severity: 'success',
            summary: 'Validation réussie',
            detail: validationData.decision === 'approuve' ? 'Demande approuvée avec succès' : 'Demande rejetée',
            life: 4000
          })
          
          // Recharger les demandes
          await chargerDemandes()
        } else {
          throw new Error(response.data.message || 'Erreur lors de la validation')
        }
        
      } catch (error) {
        console.error('❌ Erreur lors de la validation:', error)
        toast.add({
          severity: 'error',
          summary: 'Erreur de validation',
          detail: error.response?.data?.message || error.message || 'Une erreur est survenue lors de la validation',
          life: 5000
        })
      } finally {
        // Fermer le modal dans tous les cas
        showValidationModal.value = false
        selectedDemande.value = null
      }
    }

    const setFilter = (filter) => {
      currentFilter.value = filter
    }

    const setTypeFilter = (typeFilter) => {
      currentTypeFilter.value = typeFilter
    }

    const filterDemandes = () => {
      // La logique de filtrage est gérée dans le computed
    }

    const validerDemande = (demande) => {
      // Vérifier si l'utilisateur a le droit de valider cette demande
      if (!allowedStatuses.value.includes(demande.statut)) {
        toast.add({
          severity: 'warn',
          summary: 'Accès refusé',
          detail: 'Vous n\'avez pas les permissions pour valider cette demande.',
          life: 4000
        })
        return
      }
      
      selectedDemande.value = demande
      // Détermine s'il s'agit du dernier validateur
      isLastValidator.value = demande.statut === 'en_attente_directeur_rh'
      showValidationModal.value = true
      
      console.log('🎯 Validation de la demande:', demande.id, 'Statut:', demande.statut)
      console.log('🏁 Dernier validateur:', isLastValidator.value)
    }

    const handleValidationSubmit = async (validationData) => {
      try {
        loading.value = true
        await demandesApi.validerAvecSuivant({
          demande_id: selectedDemande.value.id,
          ...validationData
        })
        
        toast.add({
          severity: 'success',
          summary: 'Succès',
          detail: 'Demande validée avec succès',
          life: 3000
        })
        showValidationModal.value = false
        await chargerDemandes()
      } catch (error) {
        console.error('Erreur lors de la validation:', error)
        toast.add({
          severity: 'error',
          summary: 'Erreur',
          detail: 'Erreur lors de la validation de la demande',
          life: 5000
        })
      } finally {
        loading.value = false
      }
    }

    onMounted(() => {
      chargerDemandes()
    })

    return {
      demandes,
      filteredDemandes,
      finalFilteredDemandes,
      searchTerm,
      currentFilter,
      currentTypeFilter,
      typeFilters,
      filters,
      showValidationModal,
      selectedDemande,
      loading,
      error,
      currentUserRole,
      formatDate,
      getStatusClass,
      getStatusText,
      getUserFullName,
      openValidationModal,
      closeValidationModal,
      handleValidationResult,
      setFilter,
      setTypeFilter,
      chargerDemandes
    }
  }
}
</script>

<style scoped>
.demandes-en-attente-container {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* En-tête */
.page-header {
  text-align: center;
  margin-bottom: 2rem;
}

.page-header h1 {
  color: #1e293b;
  font-size: 2rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.page-header h1 i {
  color: #3b82f6;
  font-size: 1.75rem;
}

.demandes-count {
  background: linear-gradient(135deg, #3b82f6, #1d4ed8);
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 600;
}

.page-description {
  color: #64748b;
  font-size: 1.1rem;
  margin: 0;
}

/* Section de filtrage */
.filters-section {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-bottom: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

/* Barre de recherche */
.search-box {
  position: relative;
  max-width: 400px;
  margin: 0 auto;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.search-box input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 1rem;
  transition: border-color 0.2s;
}

.search-box input:focus {
  outline: none;
  border-color: #3b82f6;
}

/* Boutons de filtre par type */
.type-filter-buttons,
.status-filter-buttons {
  display: flex;
  justify-content: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.filter-btn {
  padding: 0.5rem 1rem;
  border: 2px solid #e5e7eb;
  background: white;
  color: #374151;
  border-radius: 6px;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.filter-btn:hover {
  border-color: #3b82f6;
  color: #3b82f6;
}

.filter-btn.active {
  background: #3b82f6;
  color: white;
  border-color: #3b82f6;
}

.filter-btn i {
  font-size: 0.875rem;
}

/* États de chargement et d'erreur */
.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 3rem;
  text-align: center;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-container i {
  font-size: 3rem;
  color: #ef4444;
  margin-bottom: 1rem;
}

.retry-btn {
  background: #3b82f6;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 1rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.2s;
}

.retry-btn:hover {
  background: #2563eb;
}

/* État vide */
.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.empty-state i {
  font-size: 4rem;
  color: #9ca3af;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6b7280;
  margin: 0;
}

/* Grille unifiée des demandes */
.demandes-grid-unified {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
  gap: 1.5rem;
}

/* Cartes de demandes */
.demande-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: transform 0.2s, box-shadow 0.2s;
  border-left: 4px solid transparent;
}

.demande-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.demande-card.conges {
  border-left-color: #3b82f6;
}

.demande-card.absences {
  border-left-color: #ef4444;
}

.demande-card.reports {
  border-left-color: #f59e0b;
}

/* En-tête de carte */
.demande-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
}

.demande-type-info {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.demande-type-info i {
  font-size: 1.25rem;
}

.demande-type {
  font-weight: 600;
  color: #374151;
}

.demande-status {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.demande-status.pending {
  background: #fef3c7;
  color: #92400e;
}

.demande-status.approved {
  background: #d1fae5;
  color: #065f46;
}

.demande-status.rejected {
  background: #fee2e2;
  color: #991b1b;
}

/* Informations employé */
.demande-employee {
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.employee-info h3 {
  color: #1e293b;
  margin: 0 0 0.5rem 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.employee-details {
  color: #64748b;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.employee-details i {
  color: #9ca3af;
}

.separator {
  color: #d1d5db;
}

/* Détails de la demande */
.demande-details {
  padding: 1rem 1.5rem;
}

.details-grid {
  display: grid;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #374151;
  font-size: 0.875rem;
}

.detail-item i {
  color: #6b7280;
  width: 1rem;
  text-align: center;
}

.motif-section {
  margin: 1rem 0;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 6px;
}

.motif-section h4 {
  margin: 0 0 0.5rem 0;
  color: #374151;
  font-size: 0.875rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.motif-section p {
  margin: 0;
  color: #6b7280;
  font-size: 0.875rem;
  line-height: 1.4;
}

.date-demande {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.date-demande small {
  color: #9ca3af;
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

/* Actions */
.demande-actions {
  padding: 1rem 1.5rem;
  background: #f8fafc;
  border-top: 1px solid #e5e7eb;
}

.validate-btn {
  width: 100%;
  background: linear-gradient(135deg, #3b82f6, #2563eb);
  color: white;
  border: none;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.validate-btn:hover {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 768px) {
  .demandes-en-attente-container {
    padding: 1rem;
  }

  .demandes-grid-unified {
    grid-template-columns: 1fr;
  }

  .filters-section {
    padding: 1rem;
  }

  .search-box {
    max-width: none;
  }

  .type-filter-buttons,
  .status-filter-buttons {
    justify-content: center;
  }

  .demande-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }

  .employee-details {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: white;
  border-radius: 16px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

.search-box {
  position: relative;
  max-width: 300px;
  flex: 1;
}

.search-box i {
  position: absolute;
  left: 15px;
  top: 50%;
  transform: translateY(-50%);
  color: #94a3b8;
}

.search-box input {
  width: 100%;
  padding: 12px 12px 12px 45px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  font-size: 14px;
  transition: all 0.2s ease;
}

.search-box input:focus {
  outline: none;
  border-color: #008a9b;
  box-shadow: 0 0 0 3px rgba(0, 138, 155, 0.1);
}

.filter-buttons {
  display: flex;
  gap: 10px;
}

.filter-btn {
  padding: 8px 16px;
  border: 1px solid #e2e8f0;
  background: white;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.2s ease;
  font-size: 14px;
  color: #64748b;
}

.filter-btn:hover {
  border-color: #008a9b;
  color: #008a9b;
}

.filter-btn.active {
  background: linear-gradient(135deg, #008a9b, #00b4d8);
  color: white;
  border-color: transparent;
}

.demandes-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.demande-card {
  background: white;
  border-radius: 16px;
  padding: 20px;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  border-left: 4px solid #e2e8f0;
}

.demande-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.demande-card.pending {
  border-left-color: #b10064;
}

.demande-card.approved {
  border-left-color: #008a9b;
}

.demande-card.rejected {
  border-left-color: #261555;
}

.demande-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 15px;
}

.demande-info h3 {
  margin: 0 0 5px 0;
  color: #261555;
  font-size: 18px;
  font-weight: 600;
}

.demande-info .matricule {
  margin: 0 0 3px 0;
  font-size: 14px;
  color: #64748b;
  font-weight: 500;
}

.demande-info .unite {
  margin: 0;
  font-size: 13px;
  color: #94a3b8;
}

.demande-status {
  text-align: right;
}

.status-badge {
  display: inline-block;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 5px;
}

.status-badge.pending {
  background: rgba(177, 0, 100, 0.1);
  color: #b10064;
}

.status-badge.approved {
  background: rgba(0, 138, 155, 0.1);
  color: #008a9b;
}

.status-badge.rejected {
  background: rgba(38, 21, 85, 0.1);
  color: #261555;
}

.date-demande {
  display: block;
  font-size: 12px;
  color: #94a3b8;
}

.demande-details {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 10px;
  margin-bottom: 15px;
  padding: 15px;
  background: #f8fafc;
  border-radius: 10px;
}

.detail-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.detail-label {
  font-weight: 500;
  color: #64748b;
  font-size: 14px;
}

.detail-value {
  font-weight: 600;
  color: #261555;
  font-size: 14px;
}

.demande-actions {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  gap: 10px;
}

.btn-validate {
  display: flex;
  align-items: center;
  gap: 5px;
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  gap: 5px;
  transition: all 0.2s ease;
  font-weight: 500;
  background: linear-gradient(135deg, #008a9b, #00b4d8);
  color: white;
}

.btn-validate:hover {
  background: linear-gradient(135deg, #007c8b, #00a2c7);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 138, 155, 0.3);
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.empty-state i {
  font-size: 48px;
  margin-bottom: 15px;
  opacity: 0.5;
}

.empty-state h3 {
  margin: 0 0 10px 0;
  color: #64748b;
}

.empty-state p {
  margin: 0;
  font-size: 16px;
}

.loading-state,
.error-state {
  text-align: center;
  padding: 60px 20px;
  color: #64748b;
}

.loading-state i {
  font-size: 48px;
  margin-bottom: 15px;
  color: #008a9b;
}

.error-state i {
  font-size: 48px;
  margin-bottom: 15px;
  color: #dc3545;
}

.error-state h3 {
  margin: 0 0 10px 0;
  color: #dc3545;
}

.retry-btn {
  background: #008a9b;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  cursor: pointer;
  margin-top: 1rem;
  transition: background-color 0.3s ease;
  font-size: 14px;
}

.retry-btn:hover {
  background: #007088;
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.modal-header h2 {
  margin: 0;
  color: #261555;
}

.close-btn {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  color: #64748b;
  padding: 5px;
}

.close-btn:hover {
  color: #475569;
}

.modal-body {
  padding: 20px;
}

@media (max-width: 768px) {
  .filters-section {
    flex-direction: column;
    align-items: stretch;
  }

  .search-box {
    max-width: none;
  }

  .filter-buttons {
    justify-content: center;
  }

  }
}
