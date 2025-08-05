// Constantes pour l'application de gestion des congés SENELEC

// Couleurs des rôles utilisateur
export const ROLE_COLORS = {
  'Admin': "red-darken-2",
  'Directeur RH': "purple-darken-2",
  'Responsable RH': "indigo-darken-2",
  'Directeur Unité': "blue-darken-2",
  'Superieur': "cyan-darken-2",
  'Employe': "green-darken-2",
};

// Routes par rôle utilisateur
export const ROLE_ROUTES = {
  'Admin': '/admin/dashboard',
  'Directeur RH': '/directeur-rh/dashboard',
  'Responsable RH': '/responsable-rh/dashboard',
  'Directeur Unité': '/directeur-unite/dashboard',
  'Superieur': '/superieur/dashboard',
  'Employe': '/employe/dashboard',
};

// Statuts des demandes
export const DEMANDE_STATUS = {
  PENDING: 'pending',
  APPROVED: 'approved',
  REJECTED: 'rejected',
  CANCELLED: 'cancelled'
};

// Types de congés
export const LEAVE_TYPES = {
  ANNUAL: 'annual',
  SICK: 'sick',
  MATERNITY: 'maternity',
  PATERNITY: 'paternity',
  EXCEPTIONAL: 'exceptional'
};

// Configuration de pagination par défaut
export const PAGINATION_CONFIG = {
  DEFAULT_ITEMS_PER_PAGE: 10,
  ITEMS_PER_PAGE_OPTIONS: [10, 20, 50, 100],
  MAX_ITEMS_FOR_ALL_USERS: 100
};

// Messages de toast par défaut
export const TOAST_MESSAGES = {
  USER_CREATED: 'Utilisateur créé avec succès',
  USER_UPDATED: 'Utilisateur mis à jour avec succès',
  USER_DELETED: 'Utilisateur supprimé avec succès',
  PASSWORD_RESET: 'Mot de passe réinitialisé avec succès',
  ERROR_GENERIC: 'Une erreur est survenue. Veuillez réessayer.'
};
