import axios from 'axios';

// Récupérer la configuration depuis les variables d'environnement
const API_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000/api';

// Configuration de base d'Axios avec plus de robustesse
const apiClient = axios.create({
  baseURL: API_URL,
  timeout: 30000, // 30 secondes
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  // Configuration pour éviter les problèmes CORS
  withCredentials: true,
});

// Intercepteur pour ajouter le token d'authentification
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Intercepteur pour gérer les réponses et les erreurs
apiClient.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    // Gestion des erreurs d'authentification
    if (error.response?.status === 401) {
      // Ne pas rediriger automatiquement si c'est une tentative de login
      if (!error.config?.url?.includes('/login')) {
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user');
        window.location.href = '/';
      }
    }

    // Gestion des erreurs de validation (422)
    if (error.response?.status === 422) {
      const validationErrors = error.response.data.errors;
      if (validationErrors) {
        const errorMessages = Object.values(validationErrors).flat();
        error.message = errorMessages.join(', ');
      }
    }

    // Gestion des erreurs Bad Request (400)
    if (error.response?.status === 400) {
      error.message = error.response.data.message || 'Requête invalide. Vérifiez les données envoyées.';
      console.error('Erreur 400 - Détails:', error.response.data);
    }

    // Gestion des erreurs de limitation (429)
    if (error.response?.status === 429) {
      error.message = 'Trop de tentatives. Veuillez patienter avant de réessayer.';
    }

    // Gestion des erreurs serveur (500)
    if (error.response?.status >= 500) {
      error.message = 'Erreur serveur. Veuillez réessayer plus tard.';
    }

    // Gestion des erreurs réseau
    if (!error.response) {
      error.message = 'Erreur de connexion. Vérifiez votre connexion internet.';
    }

    return Promise.reject(error);
  }
);

// Services API
export const authApi = {
  login: (credentials) => apiClient.post('/login', credentials),
  logout: () => apiClient.post('/logout'),
  user: () => apiClient.get('/user'),
  refresh: () => apiClient.post('/refresh'),
};

export const dashboardApi = {
  stats: () => apiClient.get('/dashboard/stats'),
  recentActivity: () => apiClient.get('/dashboard/recent-activity'),
  statsManager: () => apiClient.get('/dashboard/stats-manager'),
};

export const usersApi = {
  list: (params = {}) => apiClient.get('/users', { params }),
  create: (data) => apiClient.post('/users', data),
  get: (id) => apiClient.get(`/users/${id}`),
  update: (id, data) => apiClient.put(`/users/${id}`, data),
  delete: (id) => apiClient.delete(`/users/${id}`),
  toggleStatus: (id) => apiClient.patch(`/users/${id}/toggle-status`),
  resetPassword: (id) => apiClient.post(`/users/${id}/reset-password`, {}),
  resetPasswordWithData: (id, passwordData) => apiClient.post(`/users/${id}/reset-password`, passwordData),
  managers: () => apiClient.get('/managers'),
  simulateLogin: (userId) => apiClient.post(`/users/${userId}/simulate-login`),
  searchByEmail: (email) => apiClient.get(`/users/search-by-email?email=${encodeURIComponent(email)}`),
  searchByName: (name) => apiClient.get(`/users/search-by-name?name=${encodeURIComponent(name)}`),
};

export const rolesApi = {
  list: () => apiClient.get('/roles'),
  create: (data) => apiClient.post('/roles', data),
  get: (id) => apiClient.get(`/roles/${id}`),
  update: (id, data) => apiClient.put(`/roles/${id}`, data),
  delete: (id) => apiClient.delete(`/roles/${id}`),
};

export const departmentsApi = {
  list: () => apiClient.get('/departments'),
  create: (data) => apiClient.post('/departments', data),
  get: (id) => apiClient.get(`/departments/${id}`),
  update: (id, data) => apiClient.put(`/departments/${id}`, data),
  delete: (id) => apiClient.delete(`/departments/${id}`),
  stats: (id) => apiClient.get(`/departments/${id}/stats`),
};

export const demandesApi = {
  list: (params = {}) => apiClient.get('/demandes-conges', { params }),
  create: (data) => apiClient.post('/demandes-conges', data),
  get: (id) => apiClient.get(`/demandes-conges/${id}`),
  update: (id, data) => apiClient.put(`/demandes-conges/${id}`, data),
  delete: (id) => apiClient.delete(`/demandes-conges/${id}`),
  validate: (id, data) => apiClient.post(`/demandes-conges/${id}/validate`, data),
  getDemandesEnAttente: (params = {}) => apiClient.get('/demandes-a-valider', { params }),
  getDemandesRecues: (params = {}) => apiClient.get('/demandes-conges/recues', { params }),
  soumettreAvecWorkflow: (data) => apiClient.post('/demandes-conges/submit-with-workflow', data),
  validerAvecSuivant: (data) => apiClient.post('/demandes-conges/validate-with-next', data),
};

export const notificationsApi = {
  list: (params = {}) => apiClient.get('/notifications', { params }),
  unread: () => apiClient.get('/notifications/unread'),
  markAsRead: (id) => apiClient.patch(`/notifications/${id}/read`),
  markAllAsRead: () => apiClient.patch('/notifications/read-all'),
};

export default apiClient;
