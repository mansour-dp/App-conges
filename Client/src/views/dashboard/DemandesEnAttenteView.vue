<template>
  <div class="demandes-en-attente-container">
    <!-- En-tête simple -->
    <div class="page-header">
      <h1>
        <i class="pi pi-inbox"></i>
        Demandes en attente
        <span class="count-badge">{{ finalFilteredDemandes.length }}</span>
      </h1>
    </div>

    <!-- Filtres simples -->
    <div class="filters-bar">
      <div class="search-box">
        <i class="pi pi-search"></i>
        <InputText
          v-model="searchTerm"
          placeholder="Rechercher par nom ou matricule..."
          class="search-input"
        />
      </div>
      
      <div class="filter-buttons">
        <Button
          @click="setTypeFilter('tous')"
          label="Tous"
          :class="{ 'filter-active': currentTypeFilter === 'tous' }"
          class="filter-btn filter-tous"
          size="small"
          icon="pi pi-list"
        />
        <Button
          @click="setTypeFilter('conges')"
          label="Congés"
          :class="{ 'filter-active': currentTypeFilter === 'conges' }"
          class="filter-btn filter-conges"
          size="small"
          icon="pi pi-calendar"
        />
        <Button
          @click="setTypeFilter('absences')"
          label="Absences"
          :class="{ 'filter-active': currentTypeFilter === 'absences' }"
          class="filter-btn filter-absences"
          size="small"
          icon="pi pi-times-circle"
        />
        <Button
          @click="setTypeFilter('reports')"
          label="Reports"
          :class="{ 'filter-active': currentTypeFilter === 'reports' }"
          class="filter-btn filter-reports"
          size="small"
          icon="pi pi-refresh"
        />
      </div>
    </div>

    <!-- États -->
    <div v-if="loading" class="loading-state">
      <!-- Skeleton Cards -->
      <div class="demandes-grid">
        <div v-for="i in 6" :key="i" class="skeleton-card">
          <div class="skeleton-header">
            <div class="skeleton-avatar"></div>
            <div class="skeleton-text">
              <div class="skeleton-line skeleton-title"></div>
              <div class="skeleton-line skeleton-subtitle"></div>
            </div>
          </div>
          <div class="skeleton-body">
            <div class="skeleton-line skeleton-date"></div>
            <div class="skeleton-line skeleton-reason"></div>
          </div>
          <div class="skeleton-footer">
            <div class="skeleton-badge"></div>
            <div class="skeleton-button"></div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="error" class="error-state">
      <i class="pi pi-exclamation-triangle"></i>
      <p>{{ error }}</p>
      <Button @click="chargerDemandes" label="Réessayer" icon="pi pi-refresh" />
    </div>

    <div v-else-if="finalFilteredDemandes.length === 0" class="empty-state">
      <i class="pi pi-inbox"></i>
      <h3>Aucune demande</h3>
      <p>Il n'y a aucune demande en attente pour le moment.</p>
    </div>

    <!-- Grille simple des demandes -->
    <div v-else class="demandes-grid">
      <div
        v-for="demande in finalFilteredDemandes"
        :key="`${demande.type_source}-${demande.id}`"
        class="demande-card"
        @click="openValidationModal(demande)"
      >
        <!-- En-tête de carte -->
        <div class="card-header">
          <div class="type-badge" :class="demande.type_source">
            <i :class="demande.type_icon"></i>
            {{ demande.type_label }}
          </div>
          <div class="status-badge" :class="getStatusClass(demande.statut)">
            {{ getStatusText(demande.statut) }}
          </div>
        </div>

        <!-- Informations employé -->
        <div class="employee-info">
          <div class="avatar">
            {{ getInitials(demande.user?.nom || demande.user?.name, demande.user?.prenom || demande.user?.first_name) }}
          </div>
          <div class="employee-details">
            <h3>{{ getUserFullName(demande.user) }}</h3>
            <p class="matricule">{{ demande.user?.matricule }}</p>
            <p class="department">{{ demande.user?.department?.nom || 'Département non défini' }}</p>
          </div>
        </div>

        <!-- Détails de la demande -->
        <div class="demande-details">
          <!-- Congés -->
          <div v-if="demande.type_source === 'conges'" class="details-grid">
            <div class="detail-item">
              <span class="label">Type:</span>
              <span class="value">{{ demande.type_conge?.nom || 'Non spécifié' }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Du:</span>
              <span class="value">{{ formatDate(demande.date_debut) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Au:</span>
              <span class="value">{{ formatDate(demande.date_fin) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Durée:</span>
              <span class="value">{{ demande.duree_jours }} jour(s)</span>
            </div>
          </div>

          <!-- Absences -->
          <div v-else-if="demande.type_source === 'absences'" class="details-grid">
            <div class="detail-item">
              <span class="label">Type:</span>
              <span class="value">{{ demande.type_absence?.nom || 'Non spécifié' }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Du:</span>
              <span class="value">{{ formatDate(demande.date_debut) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Au:</span>
              <span class="value">{{ formatDate(demande.date_fin) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Durée:</span>
              <span class="value">{{ demande.duree_jours }} jour(s)</span>
            </div>
          </div>

          <!-- Reports -->
          <div v-else-if="demande.type_source === 'reports'" class="details-grid">
            <div class="detail-item">
              <span class="label">Type:</span>
              <span class="value">Report de congé</span>
            </div>
            <div class="detail-item">
              <span class="label">Du:</span>
              <span class="value">{{ formatDate(demande.nouvelle_date_debut) }}</span>
            </div>
            <div class="detail-item">
              <span class="label">Au:</span>
              <span class="value">{{ formatDate(demande.nouvelle_date_fin) }}</span>
            </div>
            <div v-if="demande.conge_original" class="detail-item">
              <span class="label">Original:</span>
              <span class="value">{{ formatDate(demande.conge_original.date_debut) }}</span>
            </div>
          </div>

          <!-- Motif si présent -->
          <div v-if="demande.motif" class="motif">
            <strong>Motif:</strong> {{ demande.motif }}
          </div>

          <!-- Date de soumission -->
          <div class="submission-date">
            <i class="pi pi-calendar"></i>
            Soumis le {{ formatDate(demande.created_at) }}
          </div>
        </div>

        <!-- Bouton d'action -->
        <div class="card-footer">
          <Button
            @click.stop="openValidationModal(demande)"
            label="Traiter"
            icon="pi pi-check-circle"
            severity="success"
            size="small"
            fluid
          />
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

// PrimeVue imports
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import ProgressSpinner from 'primevue/progressspinner'

export default {
  name: "DemandesEnAttenteView",
  components: {
    ValidationModal,
    Button,
    InputText,
    ProgressSpinner
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

      // 3️⃣ FILTRAGE PAR RECHERCHE (optimisé pour nom et matricule)
      if (searchTerm.value) {
        const term = searchTerm.value.toLowerCase().trim()
        filtered = filtered.filter((d) => {
          // Recherche par nom complet
          const nomComplet = getUserFullName(d.user).toLowerCase()
          
          // Recherche par matricule
          const matricule = (d.user?.matricule || "").toLowerCase()
          
          // Recherche par nom séparé
          const nom = (d.user?.nom || d.user?.name || "").toLowerCase()
          const prenom = (d.user?.prenom || d.user?.first_name || "").toLowerCase()
          
          return nomComplet.includes(term) ||
                 matricule.includes(term) ||
                 nom.includes(term) ||
                 prenom.includes(term)
        })
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
      if (!user) return "Utilisateur inconnu"
      const nom = user.nom || user.name || ""
      const prenom = user.prenom || user.first_name || ""
      return `${prenom} ${nom}`.trim() || "Utilisateur inconnu"
    }

    // Nouvelles méthodes utilitaires pour PrimeVue
    const getInitials = (nom, prenom) => {
      if (!nom && !prenom) return '?'
      const firstInitial = (prenom || nom || "").charAt(0).toUpperCase()
      const lastInitial = (nom || prenom || "").charAt(0).toUpperCase()
      return firstInitial !== lastInitial ? `${firstInitial}${lastInitial}` : firstInitial || '?'
    }

    const getTypeSeverity = (type) => {
      switch (type) {
        case 'conges':
          return 'info'
        case 'absences':
          return 'danger'
        case 'reports':
          return 'warning'
        default:
          return 'secondary'
      }
    }

    const getStatusSeverity = (status) => {
      switch (status) {
        case 'en_attente_superieur':
        case 'en_attente_directeur_unite':
        case 'en_attente_responsable_rh':
        case 'en_attente_directeur_rh':
          return 'warning'
        case 'approuve':
        case 'valide':
          return 'success'
        case 'rejete':
        case 'refuse':
          return 'danger'
        default:
          return 'secondary'
      }
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
      showValidationModal,
      selectedDemande,
      loading,
      error,
      currentUserRole,
      formatDate,
      getStatusClass,
      getStatusText,
      getUserFullName,
      getInitials,
      getTypeSeverity,
      getStatusSeverity,
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
/* Container principal */
.demandes-en-attente-container {
  padding: 2rem;
  max-width: 1400px;
  margin: 0 auto;
  background-color: #f8fafc;
  min-height: 100vh;
}

/* En-tête simple */
.page-header {
  margin-bottom: 2rem;
  text-align: center;
}

.page-header h1 {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  color: #1e293b;
  font-size: 2rem;
  font-weight: 700;
  margin: 0;
}

.page-header i {
  color: #008a9b; /* Primary SENELEC */
}

.count-badge {
  background: #008a9b; /* Primary SENELEC */
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 1rem;
  font-size: 0.875rem;
  font-weight: 600;
}

/* Barre de filtres simple */
.filters-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 2rem;
  margin-bottom: 2rem;
  background: white;
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.search-box {
  position: relative;
  flex: 1;
  max-width: 400px;
}

.search-box i {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding-left: 2.5rem !important;
}

.filter-buttons {
  display: flex;
  gap: 0.5rem;
  flex-wrap: wrap;
}

/* États */
.loading-state,
.error-state,
.empty-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
  background: white;
  border-radius: 12px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.loading-state i,
.error-state i,
.empty-state i {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.loading-state i {
  color: #008a9b; /* Primary SENELEC */
}

.error-state i {
  color: #b10064; /* Rouge SENELEC pour erreurs */
}

.empty-state i {
  color: #9ca3af;
}

/* Grille 3 colonnes */
.demandes-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

/* Cartes simples */
.demande-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  overflow: hidden;
  transition: all 0.2s ease;
  cursor: pointer;
  border-left: 4px solid #e5e7eb;
}

.demande-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.demande-card.conges {
  border-left-color: #008a9b; /* Primary SENELEC pour congés */
}

.demande-card.absences {
  border-left-color: #b10064; /* Rouge SENELEC pour absences */
}

.demande-card.reports {
  border-left-color: #f59e0b; /* Orange pour reports */
}

/* En-tête de carte */
.card-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
  background: #f8fafc;
  border-bottom: 1px solid #e5e7eb;
}

.type-badge {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  color: white;
}

.type-badge.conges {
  background: #008a9b; /* Primary SENELEC pour congés */
}

.type-badge.absences {
  background: #b10064; /* Rouge SENELEC pour absences */
}

.type-badge.reports {
  background: #f59e0b; /* Orange pour reports */
}

.status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.pending {
  background: rgba(255, 193, 7, 0.1); /* Jaune pour en attente */
  color: #e68900;
  border: 1px solid rgba(255, 193, 7, 0.3);
}

.status-badge.approved {
  background: rgba(0, 138, 155, 0.1); /* Primary SENELEC pour approuvé */
  color: #008a9b;
  border: 1px solid rgba(0, 138, 155, 0.3);
}

.status-badge.rejected {
  background: rgba(177, 0, 100, 0.1); /* Rouge SENELEC pour rejeté */
  color: #b10064;
  border: 1px solid rgba(177, 0, 100, 0.3);
}

/* Informations employé */
.employee-info {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem 1.5rem;
  border-bottom: 1px solid #e5e7eb;
}

.avatar {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  background: linear-gradient(135deg, #008a9b, #261555); /* Dégradé SENELEC */
  color: white;
  font-weight: 600;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  flex-shrink: 0;
}

.employee-details {
  flex: 1;
}

.employee-details h3 {
  margin: 0 0 0.25rem 0;
  font-size: 1.1rem;
  font-weight: 600;
  color: #261555; /* Secondary SENELEC */
}

.employee-details p {
  margin: 0;
  font-size: 0.875rem;
  color: #64748b;
}

.matricule {
  font-weight: 500;
}

/* Détails de la demande */
.demande-details {
  padding: 1rem 1.5rem;
}

.details-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 0.75rem;
  margin-bottom: 1rem;
}

.detail-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.detail-item .label {
  font-size: 0.75rem;
  font-weight: 600;
  color: #64748b;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.detail-item .value {
  font-size: 0.875rem;
  font-weight: 500;
  color: #261555; /* Secondary SENELEC */
}

.motif {
  margin: 1rem 0;
  padding: 0.75rem;
  background: #f8fafc;
  border-radius: 6px;
  font-size: 0.875rem;
  color: #374151;
  border-left: 3px solid #e5e7eb;
}

.submission-date {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.75rem;
  color: #9ca3af;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

/* Pied de carte */
.card-footer {
  padding: 1rem 1.5rem;
  background: #f8fafc;
  border-top: 1px solid #e5e7eb;
}

/* Responsive */
@media (max-width: 1200px) {
  .demandes-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Styles pour les boutons de filtrage */
.filter-buttons {
  gap: 0.75rem;
}

.filter-btn {
  position: relative;
  transition: all 0.3s ease;
  border: 1px solid #e5e7eb;
  background: #ffffff;
  color: #6b7280;
}

.filter-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Bouton Tous */
.filter-tous {
  border-color: #d1d5db;
}

.filter-tous.filter-active {
  background: linear-gradient(135deg, #008a9b, #0da4ba);
  color: white;
  border-color: #008a9b;
  box-shadow: 0 4px 12px rgba(0, 138, 155, 0.3);
}

/* Bouton Congés - Couleur identique au badge congés (#008a9b) */
.filter-conges {
  border-color: #d1d5db;
}

.filter-conges.filter-active {
  background: linear-gradient(135deg, #008a9b, #0da4ba);
  color: white;
  border-color: #008a9b;
  box-shadow: 0 4px 12px rgba(0, 138, 155, 0.3);
}

/* Bouton Absences - Couleur identique au badge absences (#b10064) */
.filter-absences {
  border-color: #d1d5db;
}

.filter-absences.filter-active {
  background: linear-gradient(135deg, #b10064, #d1007a);
  color: white;
  border-color: #b10064;
  box-shadow: 0 4px 12px rgba(177, 0, 100, 0.3);
}

/* Bouton Reports - Couleur identique au badge reports (#f59e0b) */
.filter-reports {
  border-color: #d1d5db;
}

.filter-reports.filter-active {
  background: linear-gradient(135deg, #f59e0b, #d97706);
  color: white;
  border-color: #f59e0b;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

/* Skeleton Loading */
.skeleton-card {
  background: white;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
  animation: pulse 1.5s ease-in-out infinite;
}

.skeleton-header {
  display: flex;
  align-items: center;
  gap: 1rem;
  margin-bottom: 1rem;
}

.skeleton-avatar {
  width: 3rem;
  height: 3rem;
  border-radius: 50%;
  background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-text {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.skeleton-line {
  height: 0.75rem;
  border-radius: 4px;
  background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-title {
  width: 60%;
  height: 1rem;
}

.skeleton-subtitle {
  width: 40%;
}

.skeleton-body {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.skeleton-date {
  width: 50%;
}

.skeleton-reason {
  width: 80%;
}

.skeleton-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.skeleton-badge {
  width: 80px;
  height: 24px;
  border-radius: 12px;
  background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

.skeleton-button {
  width: 100px;
  height: 32px;
  border-radius: 6px;
  background: linear-gradient(90deg, #f3f4f6 0%, #e5e7eb 50%, #f3f4f6 100%);
  background-size: 200% 100%;
  animation: shimmer 1.5s infinite;
}

@keyframes shimmer {
  0% {
    background-position: -200% 0;
  }
  100% {
    background-position: 200% 0;
  }
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.7;
  }
}

@media (max-width: 768px) {
  .demandes-en-attente-container {
    padding: 1rem;
  }

  .filters-bar {
    flex-direction: column;
    gap: 1rem;
  }

  .search-box {
    max-width: none;
  }

  .filter-buttons {
    justify-content: center;
  }

  .demandes-grid {
    grid-template-columns: 1fr;
  }

  .details-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .page-header h1 {
    font-size: 1.5rem;
  }

  .employee-info {
    flex-direction: column;
    text-align: center;
  }
}
</style>
