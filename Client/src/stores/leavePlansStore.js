import { defineStore } from "pinia";
import apiClient from "@/services/api";

export const useLeavePlansStore = defineStore("leavePlans", {
  state: () => ({
    leavePlans: [],
    loading: false,
    error: null
  }),

  getters: {
    getLeavePlanById: (state) => (id) => {
      return state.leavePlans.find(plan => plan.id === id);
    },
    
    activeLeavePlans: (state) => {
      return state.leavePlans.filter(plan => plan.is_active);
    },
    
    leavePlansByType: (state) => (type) => {
      return state.leavePlans.filter(plan => plan.type === type);
    },
    
    totalLeavePlans: (state) => state.leavePlans.length,
    
    currentLeavePlans: (state) => {
      const today = new Date();
      return state.leavePlans.filter(plan => {
        const startDate = new Date(plan.start_date);
        const endDate = new Date(plan.end_date);
        return startDate <= today && endDate >= today;
      });
    }
  },

  actions: {
    async fetchLeavePlans() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiClient.get('/leave-plans');
        this.leavePlans = response.data.data;
        return this.leavePlans;
      } catch (error) {
        this.error = "Erreur lors du chargement des périodes de congés";
        console.error('Erreur lors du chargement des périodes de congés:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async addLeavePlan(leavePlan) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiClient.post('/leave-plans', leavePlan);
        this.leavePlans.push(response.data.data);
        // Trier par date de début
        this.leavePlans.sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Erreur lors de l'ajout de la période de congés";
        console.error('Erreur lors de l\'ajout de la période de congés:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateLeavePlan(updatedLeavePlan) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiClient.put(`/leave-plans/${updatedLeavePlan.id}`, updatedLeavePlan);
        const index = this.leavePlans.findIndex((plan) => plan.id === updatedLeavePlan.id);
        if (index !== -1) {
          this.leavePlans[index] = response.data.data;
          // Trier par date de début
          this.leavePlans.sort((a, b) => new Date(a.start_date) - new Date(b.start_date));
          return response.data.data;
        } else {
          throw new Error("Période de congés non trouvée");
        }
      } catch (error) {
        this.error = error.response?.data?.message || "Erreur lors de la modification de la période de congés";
        console.error('Erreur lors de la modification de la période de congés:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteLeavePlan(leavePlanId) {
      this.loading = true;
      this.error = null;
      
      try {
        await apiClient.delete(`/leave-plans/${leavePlanId}`);
        const index = this.leavePlans.findIndex((plan) => plan.id === leavePlanId);
        if (index !== -1) {
          this.leavePlans.splice(index, 1);
          return true;
        } else {
          throw new Error("Période de congés non trouvée");
        }
      } catch (error) {
        this.error = error.response?.data?.message || "Erreur lors de la suppression de la période de congés";
        console.error('Erreur lors de la suppression de la période de congés:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    clearError() {
      this.error = null;
    }
  },
});
