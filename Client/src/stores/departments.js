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
        console.log('🏢 Départements API response:', response.data);
        this.departments = response.data.data || [];
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors du chargement des départements:', error);
        
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
        console.log('🏢 Tentative de création département:', department);
        const response = await departmentsApi.create(department);
        console.log('✅ Département créé:', response.data);
        await this.fetchDepartments(); // Recharger la liste
        return response.data;
      } catch (error) {
        console.error('❌ Erreur lors de la création du département:', error);
        console.error('❌ Response data:', error.response?.data);
        console.error('❌ Status:', error.response?.status);
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
        console.log('🏢 Tentative de mise à jour département:', departmentId, department);
        const response = await departmentsApi.update(departmentId, department);
        console.log('✅ Département mis à jour:', response.data);
        
        // Mettre à jour dans le state local
        const index = this.departments.findIndex(d => d.id === departmentId);
        if (index !== -1) {
          this.departments[index] = response.data.data;
        }
        
        return response.data;
      } catch (error) {
        console.error('❌ Erreur lors de la mise à jour du département:', error);
        console.error('❌ Response data:', error.response?.data);
        console.error('❌ Status:', error.response?.status);
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
        console.log('🗑️ Tentative de suppression département:', departmentId);
        await departmentsApi.delete(departmentId);
        console.log('✅ Département supprimé');
        
        // Supprimer du state local
        this.departments = this.departments.filter(d => d.id !== departmentId);
        
        return true;
      } catch (error) {
        console.error('❌ Erreur lors de la suppression du département:', error);
        console.error('❌ Response data:', error.response?.data);
        console.error('❌ Status:', error.response?.status);
        this.error = error.response?.data?.message || error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
