import { defineStore } from 'pinia';
import { authApi } from '@/services/api';

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
    userName: (state) => {
      if (!state.user) return '';
      // Support pour les deux formats de noms
      const firstName = state.user.first_name || state.user.prenom || '';
      const lastName = state.user.name || state.user.nom || '';
      return `${firstName} ${lastName}`.trim();
    },
    canValidateLeave: (state) => state.user?.can_validate_leave || false,
    isManager: (state) => state.user?.is_manager || false,
    isAdmin: (state) => {
      const roleName = state.user?.role?.name || state.user?.role?.nom;
      return roleName === 'Admin';
    },
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

          return { success: true, user: this.user };
        } else {
          this.error = response.data.message || 'Identifiants invalides';
          // Ne pas modifier isAuthenticated en cas d'erreur de login
          return { success: false, error: this.error };
        }
      } catch (error) {
        console.error('Erreur de connexion détaillée:', error);
        
        let errorMessage = 'Erreur de connexion';
        
        if (error.response) {
          // Erreur de réponse du serveur
          const status = error.response.status;
          const data = error.response.data;
          
          switch (status) {
            case 401:
              errorMessage = 'Identifiants invalides';
              break;
            case 403:
              errorMessage = data.message || 'Accès refusé';
              break;
            case 422:
              errorMessage = data.message || 'Données de connexion invalides';
              break;
            case 429:
              errorMessage = 'Trop de tentatives de connexion. Veuillez patienter avant de réessayer.';
              break;
            case 500:
              errorMessage = 'Erreur serveur temporaire';
              break;
            default:
              errorMessage = data.message || 'Erreur de connexion';
          }
        } else if (error.request) {
          // Erreur de réseau
          errorMessage = 'Problème de connexion réseau';
        } else {
          // Autre erreur
          errorMessage = error.message || 'Erreur inattendue';
        }
        
        this.error = errorMessage;
        // Ne pas modifier isAuthenticated en cas d'erreur de login
        return { success: false, error: this.error };
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;

      try {
        if (this.token) {
          await authApi.logout();
        }
      } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
      } finally {
        // Nettoyer le state et localStorage
        this.user = null;
        this.token = null;
        this.isAuthenticated = false;
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        this.loading = false;
      }
    },

    async fetchUser() {
      if (!this.token) return;

      this.loading = true;
      try {
        const response = await authApi.user();
        if (response.data.success) {
          this.user = response.data.data.user;
          this.isAuthenticated = true;
          localStorage.setItem('user', JSON.stringify(this.user));
        }
      } catch (error) {
        console.error('Erreur lors de la récupération de l\'utilisateur:', error);
        this.logout();
      } finally {
        this.loading = false;
      }
    },

    async refreshToken() {
      if (!this.token) return;

      try {
        const response = await authApi.refresh();
        if (response.data.success) {
          this.token = response.data.data.token;
          localStorage.setItem('auth_token', this.token);
        }
      } catch (error) {
        console.error('Erreur lors du rafraîchissement du token:', error);
        this.logout();
      }
    },

    // Initialiser l'utilisateur depuis localStorage
    initializeAuth() {
      const token = localStorage.getItem('auth_token');
      const user = localStorage.getItem('user');

      if (token && user) {
        this.token = token;
        this.user = JSON.parse(user);
        this.isAuthenticated = true;
        
        // Vérifier si le token est toujours valide
        this.fetchUser();
      }
    },

    // Réinitialiser l'état d'erreur
    clearError() {
      this.error = null;
    },
  },
});
