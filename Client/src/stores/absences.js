import { defineStore } from 'pinia'
import { demandesAbsenceApi } from '@/services/api'

export const useAbsencesStore = defineStore('absences', {
  state: () => ({
    demandes: [],
    demandesEnAttente: [],
    loading: false,
    error: null,
    currentDemande: null
  }),

  getters: {
    demandesParStatut: (state) => (statut) => {
      return state.demandes.filter(demande => demande.statut === statut)
    },
    
    demandesApprouvees: (state) => {
      return state.demandes.filter(demande => demande.statut === 'approuve')
    },
    
    demandesRejetees: (state) => {
      return state.demandes.filter(demande => demande.statut === 'rejete')
    },
    
    demandesEnAttenteCount: (state) => {
      return state.demandesEnAttente.length
    }
  },

  actions: {
    async fetchDemandes(params = {}) {
      this.loading = true
      this.error = null
      
      try {
        const response = await demandesAbsenceApi.list(params)
        this.demandes = response.data.data || response.data
        return this.demandes
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des demandes d\'absence'
        throw error
      } finally {
        this.loading = false
      }
    },

    async fetchDemandesEnAttente() {
      this.loading = true
      this.error = null
      
      try {
        const response = await demandesAbsenceApi.getDemandesEnAttente()
        this.demandesEnAttente = response.data.data || response.data
        return this.demandesEnAttente
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement des demandes en attente'
        throw error
      } finally {
        this.loading = false
      }
    },

    async createDemande(demandeData) {
      this.loading = true
      this.error = null
      
      try {
        const response = await demandesAbsenceApi.create(demandeData)
        const nouvelleDemande = response.data.data || response.data
        
        // Ajouter à la liste locale
        this.demandes.unshift(nouvelleDemande)
        
        return nouvelleDemande
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la création de la demande'
        throw error
      } finally {
        this.loading = false
      }
    },

    async soumettreAvecWorkflow(data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await demandesAbsenceApi.soumettreAvecWorkflow(data)
        const demandeUpdated = response.data.data || response.data
        
        // Mettre à jour dans la liste locale
        const index = this.demandes.findIndex(d => d.id === demandeUpdated.id)
        if (index !== -1) {
          this.demandes[index] = demandeUpdated
        }
        
        return demandeUpdated
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la soumission'
        throw error
      } finally {
        this.loading = false
      }
    },

    async validerAvecSuivant(data) {
      this.loading = true
      this.error = null
      
      try {
        const response = await demandesAbsenceApi.validerAvecSuivant(data)
        const demandeUpdated = response.data.data || response.data
        
        // Retirer de la liste des demandes en attente
        const indexEnAttente = this.demandesEnAttente.findIndex(d => d.id === demandeUpdated.id)
        if (indexEnAttente !== -1) {
          this.demandesEnAttente.splice(indexEnAttente, 1)
        }
        
        // Mettre à jour dans la liste générale
        const index = this.demandes.findIndex(d => d.id === demandeUpdated.id)
        if (index !== -1) {
          this.demandes[index] = demandeUpdated
        }
        
        return demandeUpdated
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la validation'
        throw error
      } finally {
        this.loading = false
      }
    },

    async getDemande(id) {
      this.loading = true
      this.error = null
      
      try {
        const response = await demandesAbsenceApi.get(id)
        this.currentDemande = response.data.data || response.data
        return this.currentDemande
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors du chargement de la demande'
        throw error
      } finally {
        this.loading = false
      }
    },

    clearError() {
      this.error = null
    },

    clearCurrentDemande() {
      this.currentDemande = null
    }
  }
})
