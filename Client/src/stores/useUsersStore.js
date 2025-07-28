import { defineStore } from "pinia";
import { authApi, usersApi, rolesApi, departmentsApi } from "@/services/api";

export const useUsersStore = defineStore("users", {
  state: () => ({
    currentUser: null,
    users: [],
    roles: [],
    departments: [],
    managers: [],
    loading: false,
    rolesLoading: false,
    departmentsLoading: false,
    usersLoading: false,
    error: null,
    pagination: {
      currentPage: 1,
      perPage: 10,
      total: 0,
      lastPage: 1,
    },
    // Cache pour éviter les rechargements inutiles
    cache: {
      roles: { data: null, timestamp: null, ttl: 5 * 60 * 1000 }, // 5 minutes
      departments: { data: null, timestamp: null, ttl: 5 * 60 * 1000 }, // 5 minutes
      users: { data: null, timestamp: null, ttl: 1 * 60 * 1000 }, // 1 minute
    },
  }),

  getters: {
    totalUsers: (state) => state.pagination.total,
    usersByRole: (state) => {
      return state.users.reduce((acc, user) => {
        const roleName = user.role?.nom || 'Non défini';
        acc[roleName] = (acc[roleName] || 0) + 1;
        return acc;
      }, {});
    },
    getUserById: (state) => (id) => {
      return state.users.find((user) => user.id === id);
    },
    activeUsers: (state) => state.users.filter(user => user.is_active),
    inactiveUsers: (state) => state.users.filter(user => !user.is_active),
  },

  actions: {
    // Vérifier si les données du cache sont encore valides
    isCacheValid(cacheKey) {
      const cache = this.cache[cacheKey];
      if (!cache.timestamp || !cache.data) return false;
      return (Date.now() - cache.timestamp) < cache.ttl;
    },

    // Mettre à jour le cache
    updateCache(cacheKey, data) {
      this.cache[cacheKey] = {
        data: JSON.parse(JSON.stringify(data)), // Deep copy
        timestamp: Date.now(),
        ttl: this.cache[cacheKey].ttl
      };
    },

    // Récupérer tous les utilisateurs avec pagination et cache
    async fetchUsers(page = 1, perPage = 10, search = '', forceRefresh = false) {
      // Si pas de forceRefresh et que le cache est valide, utiliser le cache
      if (!forceRefresh && !search && this.isCacheValid('users') && page === 1) {
        console.log('📦 Utilisation du cache pour les utilisateurs');
        this.users = this.cache.users.data.users || [];
        this.pagination = this.cache.users.data.pagination || this.pagination;
        return this.cache.users.data;
      }

      console.log('👥 Chargement des utilisateurs depuis l\'API...', { page, perPage, search });
      this.usersLoading = true;
      this.error = null;

      try {
        const params = { page, per_page: perPage };
        if (search) params.search = search;

        const response = await usersApi.list(params);
        console.log('✅ Réponse API utilisateurs:', response.data);

        // La structure de l'API Laravel avec pagination est response.data.data.data
        this.users = response.data.data.data || response.data.data || [];
        console.log('🔄 Utilisateurs extraits:', this.users.length, this.users);
        this.pagination = {
          currentPage: response.data.data.current_page || response.data.current_page || page,
          perPage: response.data.data.per_page || response.data.per_page || perPage,
          total: response.data.data.total || response.data.total || 0,
          lastPage: response.data.data.last_page || response.data.last_page || 1,
        };
        console.log('📊 Pagination mise à jour:', this.pagination);

        // Mettre en cache uniquement pour la première page sans recherche
        if (!search && page === 1) {
          this.updateCache('users', {
            users: this.users,
            pagination: this.pagination,
            response: response.data
          });
        }

        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors du chargement des utilisateurs:', error);
        throw error;
      } finally {
        this.usersLoading = false;
      }
    },

    // Récupérer les rôles disponibles avec cache
    async fetchRoles(forceRefresh = false) {
      // Vérifier le cache d'abord
      if (!forceRefresh && this.isCacheValid('roles')) {
        console.log('📦 Utilisation du cache pour les rôles');
        this.roles = this.cache.roles.data;
        return { data: this.roles };
      }

      console.log('📋 Appel API rôles...');
      this.rolesLoading = true;
      try {
        const response = await rolesApi.list();
        console.log('✅ Rôles reçus:', response.data);
        this.roles = response.data.data;

        // Mettre en cache
        this.updateCache('roles', this.roles);

        return response.data;
      } catch (error) {
        console.error('❌ Erreur API rôles:', error);
        this.error = error.message;
        throw error;
      } finally {
        this.rolesLoading = false;
      }
    },

    // Récupérer les départements avec cache
    async fetchDepartments(forceRefresh = false) {
      // Vérifier le cache d'abord
      if (!forceRefresh && this.isCacheValid('departments')) {
        console.log('📦 Utilisation du cache pour les départements');
        this.departments = this.cache.departments.data;
        return { data: this.departments };
      }

      console.log('🏢 Appel API départements...');
      this.departmentsLoading = true;
      try {
        const response = await departmentsApi.list();
        console.log('✅ Départements reçus:', response.data);
        this.departments = response.data.data;

        // Mettre en cache
        this.updateCache('departments', this.departments);

        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors du chargement des départements:', error);
        throw error;
      } finally {
        this.departmentsLoading = false;
      }
    },

    // Récupérer les managers disponibles
    async fetchManagers() {
      try {
        const response = await usersApi.managers();
        this.managers = response.data.data;
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors du chargement des managers:', error);
        throw error;
      }
    },

    // Ajouter un nouvel utilisateur
    async addUser(userData) {
      console.log('➕ Création utilisateur - Données envoyées:', userData);
      this.loading = true;
      this.error = null;

      try {
        const response = await usersApi.create(userData);
        console.log('✅ Utilisateur créé avec succès:', response.data);

        // Rafraîchir la liste des utilisateurs
        await this.fetchUsers(this.pagination.currentPage, this.pagination.perPage);

        return response.data;
      } catch (error) {
        console.error('❌ Erreur création utilisateur:', error);

        // Gestion spéciale des erreurs de validation (422)
        if (error.response?.status === 422) {
          const validationErrors = error.response.data.errors;
          console.error('❌ Erreurs de validation:', validationErrors);

          // Construire un message d'erreur lisible
          const errorMessages = Object.values(validationErrors).flat();
          this.error = 'Erreurs de validation: ' + errorMessages.join(', ');
        } else {
          this.error = error.response?.data?.message || error.message;
        }

        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Mettre à jour un utilisateur
    async updateUser(userId, userData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await usersApi.update(userId, userData);

        // Mettre à jour dans le state local
        const index = this.users.findIndex(user => user.id === userId);
        if (index !== -1) {
          this.users[index] = response.data.data;
        }

        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors de la mise à jour de l\'utilisateur:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Supprimer un utilisateur
    async removeUser(userId) {
      this.loading = true;
      this.error = null;

      try {
        await usersApi.delete(userId);

        // Supprimer du state local
        this.users = this.users.filter(user => user.id !== userId);
        this.pagination.total -= 1;

        return true;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors de la suppression de l\'utilisateur:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Activer/Désactiver un utilisateur
    async toggleUserStatus(userId) {
      this.loading = true;
      this.error = null;

      try {
        console.log('🔄 Toggle status API pour utilisateur:', userId);
        const response = await usersApi.toggleStatus(userId);
        console.log('✅ Réponse API toggle status complète:', response);
        console.log('✅ Structure response.data:', response.data);

        // Mettre à jour dans le state local avec la bonne structure
        const index = this.users.findIndex(user => user.id === userId);
        if (index !== -1) {
          // Utiliser response.data.data car c'est la structure retournée par Laravel
          const updatedUser = response.data.data;
          console.log('👤 Utilisateur avant mise à jour:', this.users[index]);
          console.log('👤 Nouvelles données utilisateur:', updatedUser);
          this.users[index] = { ...this.users[index], ...updatedUser };
          console.log('✅ Utilisateur mis à jour localement:', this.users[index]);
        } else {
          console.warn('⚠️ Utilisateur non trouvé dans la liste locale:', userId);
        }

        // Invalider le cache pour forcer le refresh lors du prochain chargement
        this.cache.users.timestamp = null;

        console.log('📤 Retour de la fonction toggle:', response.data);
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('❌ Erreur lors du changement de statut:', error);
        console.error('❌ Détails de l\'erreur:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Réinitialiser le mot de passe d'un utilisateur
    async resetUserPassword(userId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await usersApi.resetPassword(userId);
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors de la réinitialisation du mot de passe:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Réinitialiser le mot de passe avec des données spécifiques
    async resetUserPasswordWithData(userId, passwordData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await usersApi.resetPasswordWithData(userId, passwordData);
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors de la réinitialisation du mot de passe:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Récupérer l'utilisateur connecté
    async fetchCurrentUser() {
      try {
        const response = await authApi.user();
        this.currentUser = response.data.data;
        return response.data;
      } catch (error) {
        this.error = error.message;
        console.error('Erreur lors du chargement de l\'utilisateur connecté:', error);
        throw error;
      }
    },

    // Logout
    logout() {
      this.currentUser = null;
      this.users = [];
      this.roles = [];
      this.departments = [];
      this.managers = [];
      this.error = null;
    },

    // Initialiser le store (charger les données de base)
    async initializeStore() {
      console.log('🔄 Initialisation du store utilisateurs...');
      try {
        console.log('📋 Chargement des rôles...');
        await this.fetchRoles();
        console.log('✅ Rôles chargés:', this.roles.length);

        console.log('🏢 Chargement des départements...');
        await this.fetchDepartments();
        console.log('✅ Départements chargés:', this.departments.length);

        console.log('✨ Initialisation du store terminée avec succès');
      } catch (error) {
        console.error('❌ Erreur lors de l\'initialisation du store:', error);
        this.error = error.message;
      }
    },
  },
});
