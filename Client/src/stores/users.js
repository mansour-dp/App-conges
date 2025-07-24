import { defineStore } from 'pinia';
import { authApi, usersApi, rolesApi, departmentsApi } from '@/services/api';

// Store pour l'authentification
export const useUserStore = defineStore('user', {
  state: () => ({
    user: null,
    token: localStorage.getItem('auth_token') || null,
    isAuthenticated: false,
    loading: false,
    error: null,
  }),

  getters: {
    isLoggedIn: (state) => state.isAuthenticated && state.user !== null,
    userRole: (state) => state.user?.role || null,
    userName: (state) => state.user ? `${state.user.first_name} ${state.user.name}` : '',
    canValidateLeave: (state) => state.user?.can_validate_leave || false,
    isManager: (state) => state.user?.is_manager || false,
    isAdmin: (state) => state.user?.role?.name === 'Admin' || state.user?.role?.nom === 'Admin',
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await authApi.login(credentials);
        
        if (response.data.success) {
          this.user = response.data.data.user;
          this.token = response.data.data.token;
          this.isAuthenticated = true;

          // Sauvegarder dans localStorage
          localStorage.setItem('auth_token', this.token);
          localStorage.setItem('user', JSON.stringify(this.user));

          console.log('✅ Connexion réussie:', this.user);
          return { success: true, user: this.user };
        } else {
          this.error = response.data.message || 'Erreur de connexion';
          return { success: false, error: this.error };
        }
      } catch (error) {
        console.error('❌ Erreur de connexion:', error);
        this.error = error.response?.data?.message || error.message || 'Erreur de connexion';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;
      try {
        await authApi.logout();
      } catch (error) {
        console.warn('⚠️ Erreur lors de la déconnexion côté serveur:', error);
      } finally {
        // Nettoyer l'état local même si l'API échoue
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;
        this.error = null;
        
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        
        this.loading = false;
        console.log('✅ Déconnexion réussie');
      }
    },

    async initFromStorage() {
      const token = localStorage.getItem('auth_token');
      const userStr = localStorage.getItem('user');

      if (token && userStr) {
        try {
          this.token = token;
          this.user = JSON.parse(userStr);
          this.isAuthenticated = true;
          console.log('✅ Session restaurée depuis le localStorage');
        } catch (error) {
          console.error('❌ Erreur lors de la restauration de session:', error);
          this.clearStorage();
        }
      }
    },

    clearStorage() {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user');
      this.user = null;
      this.token = null;
      this.isAuthenticated = false;
    },

    async initializeAuth() {
      // Restaurer la session depuis le localStorage au démarrage
      await this.initFromStorage();
      console.log('✅ Authentification initialisée');
    },

    clearError() {
      this.error = null;
    },
  },
});

// Store pour la gestion des utilisateurs (Admin)
export const useUsersStore = defineStore('users', {
  state: () => ({
    users: [],
    roles: [],
    departments: [],
    managers: [],
    loading: false,
    error: null,
    lastFetch: {
      users: null,
      roles: null,
      departments: null,
    },
  }),

  getters: {
    totalUsers: (state) => state.users.length,
    activeUsers: (state) => state.users.filter(user => user.is_active),
    inactiveUsers: (state) => state.users.filter(user => !user.is_active),
    
    usersByRole: (state) => {
      const grouped = {};
      state.users.forEach(user => {
        const roleName = user.role?.nom || user.role?.name || 'Non défini';
        grouped[roleName] = (grouped[roleName] || 0) + 1;
      });
      return grouped;
    },
    
    usersByDepartment: (state) => {
      const grouped = {};
      state.users.forEach(user => {
        const deptName = user.department?.name || 'Non défini';
        grouped[deptName] = (grouped[deptName] || 0) + 1;
      });
      return grouped;
    },
    
    getUserById: (state) => (id) => {
      return state.users.find((user) => user.id === id);
    },
  },

  actions: {
    // Charger tous les utilisateurs
    async fetchAllUsers(forceRefresh = false) {
      const now = Date.now();
      if (!forceRefresh && this.lastFetch.users && (now - this.lastFetch.users) < 120000 && this.users.length > 0) {
        console.log('📦 Utilisation des utilisateurs en cache');
        return;
      }

      console.log('🔄 Chargement des utilisateurs...');
      this.loading = true;
      this.error = null;

      try {
        const response = await usersApi.list({ page: 1, per_page: 1000 });
        console.log('✅ Réponse API utilisateurs:', response.data);

        if (response.data && response.data.success && response.data.data) {
          this.users = response.data.data.data || [];
          this.lastFetch.users = now;
          console.log(`👥 ${this.users.length} utilisateurs chargés`);
        } else {
          console.error('❌ Format de réponse inattendu:', response.data);
          this.users = [];
        }
      } catch (error) {
        this.error = error.message || 'Erreur lors du chargement des utilisateurs';
        console.error('❌ Erreur utilisateurs:', error);
        this.users = [];
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Charger tous les rôles
    async fetchRoles(forceRefresh = false) {
      const now = Date.now();
      if (!forceRefresh && this.lastFetch.roles && (now - this.lastFetch.roles) < 300000 && this.roles.length > 0) {
        console.log('📦 Utilisation des rôles en cache');
        return;
      }

      console.log('🔄 Chargement des rôles...');
      this.loading = true;

      try {
        const response = await rolesApi.list();
        console.log('✅ Réponse API rôles:', response.data);
        
        if (response.data && response.data.success) {
          this.roles = response.data.data || [];
          this.lastFetch.roles = now;
          console.log(`✅ ${this.roles.length} rôles chargés`);
        }
      } catch (error) {
        this.error = error.message;
        console.error('❌ Erreur rôles:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Charger tous les départements
    async fetchDepartments(forceRefresh = false) {
      const now = Date.now();
      if (!forceRefresh && this.lastFetch.departments && (now - this.lastFetch.departments) < 300000 && this.departments.length > 0) {
        console.log('📦 Utilisation des départements en cache');
        return;
      }

      console.log('🔄 Chargement des départements...');
      this.loading = true;

      try {
        const response = await departmentsApi.list();
        console.log('✅ Réponse API départements:', response.data);
        
        if (response.data && response.data.success) {
          this.departments = response.data.data || [];
          this.lastFetch.departments = now;
          console.log(`✅ ${this.departments.length} départements chargés`);
        }
      } catch (error) {
        this.error = error.message;
        console.error('❌ Erreur départements:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Méthode pour charger toutes les données
    async loadAllData(forceRefresh = false) {
      console.log('🚀 Chargement de toutes les données...');
      
      try {
        await Promise.all([
          this.fetchAllUsers(forceRefresh),
          this.fetchRoles(forceRefresh),
          this.fetchDepartments(forceRefresh),
        ]);
        console.log('✅ Toutes les données chargées');
      } catch (error) {
        console.error('❌ Erreur chargement global:', error);
        throw error;
      }
    },

    // CRUD Utilisateurs
    async addUser(userData) {
      this.loading = true;
      try {
        const response = await usersApi.create(userData);
        if (response.data && response.data.success) {
          this.users.unshift(response.data.data);
          console.log('✅ Utilisateur ajouté:', response.data.data);
        }
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateUser(userId, userData) {
      this.loading = true;
      try {
        const response = await usersApi.update(userId, userData);
        if (response.data && response.data.success) {
          const index = this.users.findIndex(u => u.id === userId);
          if (index !== -1) {
            this.users[index] = response.data.data;
          }
          console.log('✅ Utilisateur modifié:', response.data.data);
        }
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async removeUser(userId) {
      this.loading = true;
      try {
        await usersApi.delete(userId);
        this.users = this.users.filter(u => u.id !== userId);
        console.log('✅ Utilisateur supprimé:', userId);
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async toggleUserStatus(userId) {
      this.loading = true;
      try {
        const response = await usersApi.toggleStatus(userId);
        if (response.data && response.data.success) {
          const user = this.users.find(u => u.id === userId);
          if (user) {
            user.is_active = response.data.data.is_active;
          }
        }
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async resetUserPassword(userId, passwordData) {
      this.loading = true;
      try {
        const response = await usersApi.resetPasswordWithData(userId, passwordData);
        return response.data;
      } catch (error) {
        this.error = error.message;
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Nettoyer le cache
    clearCache() {
      this.lastFetch = {
        users: null,
        roles: null,
        departments: null,
      };
    },

    clearError() {
      this.error = null;
    },
  },
});
