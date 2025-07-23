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
    userName: (state) => state.user ? `${state.user.prenom} ${state.user.nom}` : '',
    canValidateLeave: (state) => state.user?.can_validate_leave || false,
    isManager: (state) => state.user?.is_manager || false,
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
