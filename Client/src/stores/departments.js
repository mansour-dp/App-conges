import { defineStore } from "pinia";
import { departmentsApi } from "@/services/api";

export const useDepartmentsStore = defineStore("departments", {
  state: () => ({
    departments: [],
    loading: false,
    error: null,
  }),
  
  getters: {
    totalDepartments: (state) => state.departments.length,
    activeDepartments: (state) => state.departments.filter(dept => dept.status !== 'inactive').length,
  },
  
  actions: {
    async fetchDepartments() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await departmentsApi.list();
        this.departments = response.data.data || [];
        return response.data;
      } catch (error) {
        this.error = error.message;
        
        // En cas d'erreur, utiliser des données par défaut
        this.departments = [
          {
            id: 1,
            nom: 'Administration',
            description: 'Département administratif',
            chef_id: null
          },
          {
            id: 2,
            nom: 'Ressources Humaines',
            description: 'Gestion des ressources humaines',
            chef_id: null
          },
          {
            id: 3,
            nom: 'Informatique',
            description: 'Services informatiques',
            chef_id: null
          }
        ];
        
        return { data: this.departments };
      } finally {
        this.loading = false;
      }
    },
    
    async addDepartment(department) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await departmentsApi.create(department);
        await this.fetchDepartments(); // Recharger la liste
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async updateDepartment(departmentId, department) {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await departmentsApi.update(departmentId, department);
        
        // Mettre à jour dans le state local
        const index = this.departments.findIndex(d => d.id === departmentId);
        if (index !== -1) {
          this.departments[index] = response.data.data;
        }
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
    
    async deleteDepartment(departmentId) {
      this.loading = true;
      this.error = null;
      
      try {
        await departmentsApi.delete(departmentId);
        
        // Supprimer du state local
        this.departments = this.departments.filter(d => d.id !== departmentId);
        
        return true;
      } catch (error) {
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
