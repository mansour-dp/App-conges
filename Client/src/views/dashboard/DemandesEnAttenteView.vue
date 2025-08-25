<template>
  <div class="demandes-en-attente">
    <div class="filters-section">
      <div class="search-box">
        <i class="fas fa-search"></i>
        <input
          type="text"
          v-model="searchTerm"
          placeholder="Rechercher par nom, matricule..."
          @input="filterDemandes"
        />
      </div>
      <div class="filter-buttons">
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

    <div v-if="loading" class="loading-state">
      <i class="fas fa-spinner fa-spin"></i>
      <h3>Chargement des demandes...</h3>
      <p>Veuillez patienter pendant le chargement des données.</p>
    </div>

    <div v-else-if="error" class="error-state">
      <i class="fas fa-exclamation-triangle"></i>
      <h3>Erreur de chargement</h3>
      <p>{{ error }}</p>
      <button @click="chargerDemandes" class="retry-btn">
        <i class="fas fa-redo"></i>
        Réessayer
      </button>
    </div>

    <div v-else class="demandes-list">
      <div
        v-for="demande in filteredDemandes"
        :key="demande.id"
        class="demande-card"
        :class="getStatusClass(demande.statut)"
      >
        <div class="demande-header">
          <div class="demande-info">
            <h3>{{ demande.user?.first_name }} {{ demande.user?.name }}</h3>
            <p class="matricule">Matricule: {{ demande.user?.matricule }}</p>
            <p class="unite">{{ demande.user?.department?.nom }}</p>
          </div>
          <div class="demande-status">
            <span :class="['status-badge', getStatusClass(demande.statut)]">
              {{ getStatusLabel(demande.statut) }}
            </span>
            <span class="date-demande">
              {{ formatDate(demande.date_soumission) }}
            </span>
          </div>
        </div>

        <div class="demande-details">
          <div class="detail-row">
            <span class="detail-label">Type de demande:</span>
            <span class="detail-value">{{ demande.type_demande }}</span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Période:</span>
            <span class="detail-value">
              {{ formatDate(demande.date_debut) }} - {{ formatDate(demande.date_fin) }}
            </span>
          </div>
          <div class="detail-row">
            <span class="detail-label">Durée:</span>
            <span class="detail-value">{{ demande.duree_jours }} jour(s)</span>
          </div>
          <div class="detail-row" v-if="demande.motif">
            <span class="detail-label">Motif:</span>
            <span class="detail-value">{{ demande.motif }}</span>
          </div>
        </div>

        <div class="demande-actions">
          <button 
            v-if="demande.statut === 'en_attente_superieur' || demande.statut === 'en_attente_directeur_unite' || demande.statut === 'en_attente_responsable_rh' || demande.statut === 'en_attente_directeur_rh'"
            @click="validerDemande(demande)" 
            class="btn-validate"
          >
            <i class="fas fa-gavel"></i>
            Valider
          </button>
        </div>
      </div>

      <div v-if="filteredDemandes.length === 0" class="empty-state">
        <i class="fas fa-inbox"></i>
        <h3>Aucune demande trouvée</h3>
        <p>
          {{
            searchTerm
              ? "Aucune demande ne correspond à votre recherche."
              : "Toutes les demandes ont été traitées."
          }}
        </p>
      </div>
    </div>

    <!-- Modal de validation uniquement -->
    <ValidationModal
      v-model="showValidationModal"
      :demande="selectedDemande"
      :is-last-validator="isLastValidator"
      @submit="handleValidationSubmit"
    />
  </div>
</template>

<script>
import { ref, onMounted, computed } from 'vue'
import { useToast } from 'primevue/usetoast'
import { demandesApi } from '@/services/api'
import { useNotificationsStore } from '@/stores/notifications'
import { useUserStore } from '@/stores/users'
import ValidationModal from '@/components/workflow/ValidationModal.vue'

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

    // Charger les demandes depuis l'API
    const chargerDemandes = async () => {
      try {
        loading.value = true
        error.value = null
        
        console.log('🔄 Chargement des demandes pour le rôle:', currentUserRole.value)
        console.log('📋 Statuts autorisés:', allowedStatuses.value)
        
        const response = await demandesApi.getDemandesRecues()
        
        if (response.data && response.data.success) {
          demandes.value = response.data.data || []
          console.log('✅ Demandes brutes récupérées:', demandes.value.length)
          
          // Log des statuts des demandes reçues
          const statusCount = {}
          demandes.value.forEach(d => {
            statusCount[d.statut] = (statusCount[d.statut] || 0) + 1
          })
          console.log('📊 Répartition par statut:', statusCount)
          
        } else {
          demandes.value = []
          console.warn('⚠️ Aucune demande trouvée ou réponse invalide')
        }
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

      // 2️⃣ FILTRAGE PAR STATUT (si un filtre spécifique est sélectionné)
      if (currentFilter.value !== "toutes") {
        filtered = filtered.filter((d) => d.statut === currentFilter.value)
      }

      // 3️⃣ FILTRAGE PAR RECHERCHE
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

    // Debugging des demandes filtrées
    const debugFilteredDemandes = computed(() => {
      const count = filteredDemandes.value.length
      console.log(`🔍 Demandes filtrées pour ${currentUserRole.value}:`, count)
      if (count > 0) {
        const statusCounts = {}
        filteredDemandes.value.forEach(d => {
          statusCounts[d.statut] = (statusCounts[d.statut] || 0) + 1
        })
        console.log('📈 Répartition des demandes filtrées:', statusCounts)
      }
      return count
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

    const getStatusLabel = (status) => {
      switch (status) {
        case "en_attente_superieur":
          return "En attente supérieur"
        case "en_attente_directeur_unite":
          return "En attente directeur unité"
        case "en_attente_responsable_rh":
          return "En attente responsable RH"
        case "en_attente_directeur_rh":
          return "En attente directeur RH"
        case "approuve":
          return "Approuvée"
        case "rejete":
          return "Rejetée"
        default:
          return status
      }
    }

    const setFilter = (filter) => {
      currentFilter.value = filter
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
      searchTerm,
      currentFilter,
      showValidationModal,
      selectedDemande,
      isLastValidator,
      loading,
      error,
      filters,
      demandes,
      filteredDemandes,
      debugFilteredDemandes, // Pour le debugging
      currentUserRole,
      allowedStatuses,
      formatDate,
      getStatusClass,
      getStatusLabel,
      setFilter,
      filterDemandes,
      validerDemande,
      handleValidationSubmit,
      chargerDemandes
    }
  }
}
</script>

<style scoped>
.demandes-en-attente {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
  font-family: "Inter", sans-serif;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
  padding: 2rem;
  background-color: #f8fafc;
  border-radius: 16px;
  border-left: 4px solid #008a9b;
}

.page-header h1 {
  color: #261555;
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.page-header p {
  color: #6c757d;
  font-size: 1.1rem;
  margin: 0;
}

.filters-section {
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

  .demande-header {
    flex-direction: column;
    align-items: flex-start;
  }

  .demande-status {
    text-align: left;
  }

  .demande-details {
    grid-template-columns: 1fr;
  }
}
</style>
