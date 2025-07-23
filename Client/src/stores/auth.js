import { defineStore } from 'pinia';
import { authApi } from '@/services/api';

/**
 * Store pour gérer l'authentification et les données utilisateur
 */
export const useUsersStore = defineStore('user-auth', {
  state: () => ({
    currentUser: null,
    token: localStorage.getItem('auth_token') || null,
    isAuthenticated: false,
    loading: false,
    error: null,
  }),

  getters: {
    // Vérifier si l'utilisateur est connecté
    isLoggedIn: (state) => state.isAuthenticated && state.currentUser !== null,
    
    // Obtenir le rôle de l'utilisateur
    userRole: (state) => {
      if (!state.currentUser) return null;
      
      if (state.currentUser.role) {
        if (typeof state.currentUser.role === 'object') {
          return state.currentUser.role.name;
        }
        if (typeof state.currentUser.role === 'string') {
          return state.currentUser.role;
        }
      }
      
      // Fallback pour les cas spéciaux
      if (state.currentUser.is_admin) return 'admin';
      if (state.currentUser.is_manager) {
        if (state.currentUser.department?.name?.toLowerCase().includes('rh')) {
          return 'directeur_rh';
        }
        return 'superieur';
      }
      
      return 'employe'; // Valeur par défaut
    },
    
    // Autres getters utiles
    userName: (state) => {
      if (!state.currentUser) return '';
      return `${state.currentUser.first_name || ''} ${state.currentUser.name || ''}`.trim();
    },
    
    canValidateLeave: (state) => state.currentUser?.can_validate_leave || false,
    isManager: (state) => state.currentUser?.is_manager || false,
  },

  actions: {
    /**
     * Connecte l'utilisateur
     * @param {Object} credentials - Identifiants de connexion
     */
    async login(credentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await authApi.login(credentials);
        console.log('Réponse de connexion:', response);
        
        if (response.data.success) {
          const userData = response.data.data.user;
          const token = response.data.data.token;
          
          // Mettre à jour le state
          this.currentUser = userData;
          this.token = token;
          this.isAuthenticated = true;

          // Sauvegarder dans localStorage
          localStorage.setItem('auth_token', token);
          localStorage.setItem('user', JSON.stringify(userData));
          
          // Sauvegarder le rôle explicitement
          if (userData.role && typeof userData.role === 'object') {
            localStorage.setItem('user_role', userData.role.name);
          } else if (typeof userData.role === 'string') {
            localStorage.setItem('user_role', userData.role);
          }

          return { success: true, user: userData };
        } else {
          this.error = response.data.message || 'Erreur de connexion';
          return { success: false, error: this.error };
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur de connexion';
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    /**
     * Déconnecte l'utilisateur
     */
    async logout() {
      try {
        if (this.token) {
          await authApi.logout();
        }
      } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
      } finally {
        this.currentUser = null;
        this.token = null;
        this.isAuthenticated = false;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        localStorage.removeItem('user_role');
      }
    },

    /**
     * Récupère les informations utilisateur
     */
    async fetchUser() {
      try {
        const response = await authApi.user();
        this.currentUser = response.data.data.user;
        this.isAuthenticated = true;
        localStorage.setItem('user', JSON.stringify(this.currentUser));
        return response.data;
      } catch (error) {
        console.error('Erreur lors de la récupération des données utilisateur:', error);
        this.logout();
      }
    },

    /**
     * Initialise l'authentification à partir du localStorage
     */
    initializeAuth() {
      const token = localStorage.getItem('auth_token');
      const user = localStorage.getItem('user');
      const role = localStorage.getItem('user_role');

      if (token && user) {
        try {
          this.token = token;
          this.currentUser = JSON.parse(user);
          this.isAuthenticated = true;
          
          // Si un rôle explicite est stocké, assurons-nous qu'il est cohérent
          if (role && this.currentUser.role) {
            if (typeof this.currentUser.role === 'object') {
              this.currentUser.role.name = role;
            } else if (typeof this.currentUser.role === 'string') {
              this.currentUser.role = role;
            }
          }
          
          // Vérifier si le token est toujours valide
          this.fetchUser().catch(() => this.logout());
          
        } catch (e) {
          console.error('Erreur lors de l\'initialisation de l\'authentification:', e);
          this.logout();
        }
      }
    },
    
    /**
     * Réinitialise l'état d'erreur
     */
    clearError() {
      this.error = null;
    },
  },
});
