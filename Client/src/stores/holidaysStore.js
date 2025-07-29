import { defineStore } from "pinia";
import apiClient from "@/services/api";

export const useHolidaysStore = defineStore("holidays", {
  state: () => ({
    holidays: [],
    loading: false,
    error: null
  }),

  getters: {
    getHolidayById: (state) => (id) => {
      return state.holidays.find(holiday => holiday.id === id);
    },
    
    upcomingHolidays: (state) => {
      const today = new Date();
      return state.holidays.filter(holiday => new Date(holiday.date) >= today)
                           .sort((a, b) => new Date(a.date) - new Date(b.date));
    },
    
    holidaysByType: (state) => (type) => {
      return state.holidays.filter(holiday => holiday.type === type);
    },
    
    totalHolidays: (state) => state.holidays.length,
    
    activeHolidays: (state) => {
      return state.holidays.filter(holiday => holiday.is_active);
    }
  },

  actions: {
    async fetchHolidays() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiClient.get('/holidays');
        this.holidays = response.data.data;
        return this.holidays;
      } catch (error) {
        this.error = "Erreur lors du chargement des jours fériés";
        console.error('Erreur lors du chargement des jours fériés:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async addHoliday(holiday) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiClient.post('/holidays', holiday);
        this.holidays.push(response.data.data);
        // Trier par date
        this.holidays.sort((a, b) => new Date(a.date) - new Date(b.date));
        return response.data.data;
      } catch (error) {
        this.error = error.response?.data?.message || "Erreur lors de l'ajout du jour férié";
        console.error('Erreur lors de l\'ajout du jour férié:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateHoliday(updatedHoliday) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await apiClient.put(`/holidays/${updatedHoliday.id}`, updatedHoliday);
        const index = this.holidays.findIndex((h) => h.id === updatedHoliday.id);
        if (index !== -1) {
          this.holidays[index] = response.data.data;
          // Trier par date
          this.holidays.sort((a, b) => new Date(a.date) - new Date(b.date));
          return response.data.data;
        } else {
          throw new Error("Jour férié non trouvé");
        }
      } catch (error) {
        this.error = error.response?.data?.message || "Erreur lors de la modification du jour férié";
        console.error('Erreur lors de la modification du jour férié:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteHoliday(holidayId) {
      this.loading = true;
      this.error = null;
      
      try {
        await apiClient.delete(`/holidays/${holidayId}`);
        const index = this.holidays.findIndex((h) => h.id === holidayId);
        if (index !== -1) {
          this.holidays.splice(index, 1);
          return true;
        } else {
          throw new Error("Jour férié non trouvé");
        }
      } catch (error) {
        this.error = error.response?.data?.message || "Erreur lors de la suppression du jour férié";
        console.error('Erreur lors de la suppression du jour férié:', error);
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
