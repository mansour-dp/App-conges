<template>
  <v-container fluid class="user-management-view">
    <v-card class="rounded-lg" elevation="2">
      <v-card-title class="d-flex align-center pa-4">
        <span class="text-h5 font-weight-bold">Gestion des Utilisateurs</span>
        <v-spacer></v-spacer>
        
        <!-- Section Simulation Utilisateur -->
        <div class="d-flex align-center mr-4">
          <v-chip
            v-if="selectedUserForSimulation"
            color="primary"
            variant="outlined"
            class="mr-2"
            prepend-icon="mdi-account-circle"
          >
            {{ getSelectedUserName() }}
          </v-chip>
          <v-btn
            color="primary"
            :disabled="!selectedUserForSimulation || simulationLoading"
            :loading="simulationLoading"
            @click="simulateUser"
            prepend-icon="mdi-account-switch"
            variant="elevated"
          >
            Simuler
          </v-btn>
        </div>
        
        <v-text-field
          v-model="search"
          append-inner-icon="mdi-magnify"
          label="Rechercher par nom, prénom, email, département, rôle..."
          variant="outlined"
          density="compact"
          hide-details
          class="mr-4"
          style="max-width: 400px"
          @keyup.enter="searchUsers"
        ></v-text-field>
        <v-btn
          color="primary"
          @click="openDialog()"
          prepend-icon="mdi-plus-circle"
        >
          Ajouter un utilisateur
        </v-btn>
      </v-card-title>

      <!-- Alerte d'erreur -->
      <v-alert
        v-if="error"
        type="error"
        variant="tonal"
        closable
        class="ma-4"
        @click:close="clearError"
      >
        {{ error }}
      </v-alert>

      <!-- Skeleton Loader pour les utilisateurs -->
      <v-skeleton-loader
        v-if="loadingUsers"
        type="table-row@5"
        class="ma-4"
      ></v-skeleton-loader>

      <v-data-table
        v-else
        :headers="headers"
        :items="filteredUsers"
        :items-per-page="itemsPerPage"
        :items-per-page-options="[10, 20, 50, 100]"
        class="elevation-0"
        hover
        no-data-text="Aucun utilisateur trouvé"
        @click:row="selectUserForSimulation"
        :row-props="getRowProps"
      >
        <template v-slot:item.role="{ item }">
          <v-chip :color="getRoleColor(item.role?.nom || 'N/A')" dark small>
            {{ item.role?.nom || 'Non défini' }}
          </v-chip>
        </template>
        <template v-slot:item.department="{ item }">
          <span>{{ item.department?.name || 'Non défini' }}</span>
        </template>
        <template v-slot:item.is_active="{ item }">
          <v-chip
            :color="item.is_active ? 'green' : 'grey'"
            dark
            small
          >
            {{ item.is_active ? 'Actif' : 'Inactif' }}
          </v-chip>
        </template>
        <template v-slot:item.created_at="{ item }">
          {{ formatDate(item.created_at) }}
        </template>
        <template v-slot:item.actions="{ item }">
          <v-tooltip text="Modifier">
            <template v-slot:activator="{ props }">
              <v-icon
                v-bind="props"
                color="grey-darken-1"
                class="mr-2"
                @click="openDialog(item)"
              >
                mdi-pencil
              </v-icon>
            </template>
          </v-tooltip>
          <v-tooltip :text="item.is_active ? 'Désactiver' : 'Activer'">
            <template v-slot:activator="{ props }">
              <v-icon
                v-bind="props"
                :color="item.is_active ? 'green-darken-1' : 'orange-darken-1'"
                class="mr-2"
                @click="toggleUserStatus(item)"
              >
                {{ item.is_active ? 'mdi-account-check' : 'mdi-account-off' }}
              </v-icon>
            </template>
          </v-tooltip>
          <v-tooltip text="Réinitialiser le mot de passe">
            <template v-slot:activator="{ props }">
              <v-icon
                v-bind="props"
                color="blue-darken-2"
                class="mr-2"
                @click="resetPassword(item)"
              >
                mdi-lock-reset
              </v-icon>
            </template>
          </v-tooltip>
          <v-tooltip text="Supprimer">
            <template v-slot:activator="{ props }">
              <v-icon
                v-bind="props"
                color="red-darken-2"
                @click="confirmDelete(item)"
              >
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <!-- Modal pour Ajouter/Modifier un utilisateur -->
    <UserModal
      v-model="dialog"
      :user="editedUser"
      :roles="roles"
      :departments="departments"
      :loading="usersStore.loading"
      @submit="saveUser"
    />

    <!-- Modal de confirmation de suppression -->
    <UserDeleteModal
      v-model="dialogDelete"
      :user="userToDelete"
      :loading="usersStore.loading"
      @confirm="deleteUser"
    />

    <!-- Reset Password Modal -->
    <ResetPasswordModal
      v-model="dialogResetPassword"
      :user="userToResetPassword"
      :loading="usersStore.loading"
      @submit="handleResetPassword"
    />

  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useRouter } from "vue-router";
import { useUsersAdminStore } from "@/stores/usersAdmin";
import { useUserStore } from "@/stores/users";
import { useUsersStore } from "@/stores/auth";
import { useToast } from 'primevue/usetoast';
import { storeToRefs } from "pinia";
import { usersApi } from "@/services/api";
import UserModal from "@/components/admin/UserModal.vue";
import UserDeleteModal from "@/components/admin/UserDeleteModal.vue";
import ResetPasswordModal from "@/components/admin/ResetPasswordModal.vue";

// Router et stores
const router = useRouter();
const usersStore = useUsersAdminStore();
const userStore = useUserStore();
const authStore = useUsersStore();
const toast = useToast();
const {
  users,
  roles,
  departments,
  error,
  pagination
} = storeToRefs(usersStore);

// Reactive variables
const search = ref("");
const dialog = ref(false);
const dialogDelete = ref(false);
const dialogResetPassword = ref(false);
const userToDelete = ref(null);
const userToResetPassword = ref(null);
const currentPage = ref(1);
const itemsPerPage = ref(10);
const editedIndex = ref(-1);

// Loading state for skeleton loader
const loadingUsers = ref(true);

// Variables pour la simulation d'utilisateur
const selectedUserForSimulation = ref(null);
const simulationLoading = ref(false);

const editedUser = ref({
  id: null,
  name: "",
  first_name: "",
  matricule: "",
  email: "",
  phone: "",
  department_id: null,
  role_id: null,
  is_active: true,
  password: "",
  password_confirmation: "",
});

const defaultUser = {
  id: null,
  name: "",
  first_name: "",
  matricule: "",
  email: "",
  phone: "",
  department_id: null,
  role_id: null,
  is_active: true,
  password: "",
  password_confirmation: "",
};

// Computed properties
const totalUsers = computed(() => pagination.value?.total || 0);

// Filtrage simple et efficace des utilisateurs
const filteredUsers = computed(() => {
  if (!search.value || search.value.trim() === '') {
    return users.value;
  }
  
  const searchTerm = search.value.toLowerCase().trim();
  
  return users.value.filter(user => {
    // Recherche dans nom
    if (user.name && user.name.toLowerCase().includes(searchTerm)) return true;
    
    // Recherche dans prénom
    if (user.first_name && user.first_name.toLowerCase().includes(searchTerm)) return true;
    
    // Recherche dans email
    if (user.email && user.email.toLowerCase().includes(searchTerm)) return true;
    
    // Recherche dans matricule
    if (user.matricule && user.matricule.toLowerCase().includes(searchTerm)) return true;
    
    // Recherche dans département
    if (user.department?.name && user.department.name.toLowerCase().includes(searchTerm)) return true;
    
    // Recherche dans rôle
    if (user.role?.nom && user.role.nom.toLowerCase().includes(searchTerm)) return true;
    
    // Recherche dans statut
    const status = user.is_active ? 'actif' : 'inactif';
    if (status.includes(searchTerm)) return true;
    
    return false;
  });
});

// Computed property pour obtenir tous les utilisateurs actifs pour la simulation
const allUsers = computed(() => {
  return users.value.filter(user => user.is_active);
});

// Table headers configuration
const headers = ref([
  { title: "Nom", key: "name", align: "start", sortable: false },
  { title: "Prénom", key: "first_name", sortable: false },
  { title: "Matricule", key: "matricule", sortable: false },
  { title: "Email", key: "email", sortable: false },
  { title: "Département", key: "department", sortable: false },
  { title: "Rôle", key: "role", sortable: false },
  { title: "Statut", key: "is_active", sortable: false },
  { title: "Date création", key: "created_at", sortable: false },
  { title: "Actions", key: "actions", sortable: false, align: "end" },
]);

// Methods
const searchUsers = () => {
  // Suppression du snackbar de recherche
};
const getRoleColor = (role) => {
  const colors = {
    'Admin': "red-darken-2",
    'Directeur RH': "purple-darken-2",
    'Responsable RH': "indigo-darken-2",
    'Directeur Unite': "blue-darken-2",
    'Superieur': "cyan-darken-2",
    'Employe': "green-darken-2",
  };
  return colors[role] || "grey";
};

const formatDate = (dateString) => {
  if (!dateString) return 'N/A';
  return new Date(dateString).toLocaleDateString('fr-FR');
};

// Méthodes pour la simulation d'utilisateur
const selectUserForSimulation = (event, { item }) => {
  selectedUserForSimulation.value = item.id;
};

const getSelectedUserName = () => {
  if (!selectedUserForSimulation.value) return '';
  const user = allUsers.value.find(u => u.id === selectedUserForSimulation.value);
  return user ? `${user.first_name} ${user.name}` : '';
};

const getRowProps = ({ item }) => {
  return {
    class: selectedUserForSimulation.value === item.id ? 'user-selected' : '',
  };
};

const clearError = () => {
  usersStore.error = null;
};

// Fonction pour rediriger vers le dashboard approprié selon le rôle
const redirectToDashboard = (userRole) => {
  // Correspondance directe avec les rôles exacts de la base de données
  // Utilise replace() pour éviter les problèmes de navigation arrière pendant la simulation
  switch (userRole) {
    case 'Admin':
      router.replace('/admin/dashboard');
      break;
    case 'Directeur RH':
      router.replace('/directeur-rh/dashboard');
      break;
    case 'Responsable RH':
      router.replace('/responsable-rh/dashboard');
      break;
    case 'Directeur Unité':
      router.replace('/directeur-unite/dashboard');
      break;
    case 'Superieur':
      router.replace('/superieur/dashboard');
      break;
    case 'Employe':
    default:
      router.replace('/employe/dashboard');
      break;
  }
};

// Méthode pour simuler la connexion d'un utilisateur
const simulateUser = async () => {
  if (!selectedUserForSimulation.value) {
    toast.add({
      severity: 'warning',
      summary: 'Attention',
      detail: 'Veuillez sélectionner un utilisateur à simuler.',
      life: 3000
    });
    return;
  }
  
  simulationLoading.value = true;
  
  try {
    const response = await usersApi.simulateLogin(selectedUserForSimulation.value);
    
    if (response.data.success && response.data.token) {
      // Sauvegarder le token de l'admin pour pouvoir revenir plus tard
      const currentToken = localStorage.getItem('auth_token');
      const currentUser = localStorage.getItem('user');
      localStorage.setItem('admin_token_backup', currentToken);
      localStorage.setItem('admin_user_backup', currentUser);
      
      // Mettre à jour l'authentification avec les données de l'utilisateur simulé
      const userData = response.data.user;
      const userRole = userData.role?.name || userData.role || 'Employe';
      
      // Sauvegarder les nouvelles données
      localStorage.setItem('auth_token', response.data.token);
      localStorage.setItem('user', JSON.stringify(userData));
      localStorage.setItem('user_role', userRole);
      
      // Mettre à jour le store d'authentification
      authStore.currentUser = userData;
      authStore.token = response.data.token;
      authStore.isAuthenticated = true;
      
      // Afficher un message de succès
      toast.add({
        severity: 'success',
        summary: 'Simulation activée',
        detail: `Vous êtes maintenant connecté en tant que ${userData.first_name} ${userData.name}`,
        life: 4000
      });
      
      // Rediriger vers le dashboard approprié selon le rôle
      setTimeout(() => {
        redirectToDashboard(userRole);
      }, 1000);
    } else {
      throw new Error(response.data.message || 'Erreur lors de la simulation');
    }
  } catch (error) {
    console.error('Erreur simulation utilisateur:', error);
    toast.add({
      severity: 'error',
      summary: 'Erreur de simulation',
      detail: error.response?.data?.message || 'Impossible de simuler cet utilisateur. Veuillez réessayer.',
      life: 5000
    });
  } finally {
    simulationLoading.value = false;
  }
};

const openDialog = (user) => {
  if (user) {
    editedIndex.value = users.value.findIndex((u) => u.id === user.id);
    editedUser.value = {
      id: user.id,
      name: user.name,
      first_name: user.first_name,
      matricule: user.matricule,
      email: user.email,
      phone: user.phone || '',
      department_id: user.department?.id || null,
      role_id: user.role?.id || null,
      is_active: user.is_active !== undefined ? user.is_active : true,
      password: "",
      password_confirmation: "",
    };
  } else {
    editedIndex.value = -1;
    editedUser.value = { ...defaultUser };
  }
  dialog.value = true;
};

const closeDialog = () => {
  dialog.value = false;
  editedUser.value = { ...defaultUser };
  editedIndex.value = -1;
};

const saveUser = async (formData) => {
  // Validation des champs requis
  if (!formData.name || !formData.first_name || !formData.matricule || !formData.email || !formData.department_id || !formData.role_id) {
    toast.add({
      severity: 'warn',
      summary: 'Champs requis',
      detail: 'Veuillez compléter tous les champs obligatoires avant de continuer.',
      life: 4000
    });
    return;
  }

  // Validation du mot de passe pour les nouveaux utilisateurs
  if (editedIndex.value === -1) {
    if (!formData.password || !formData.password_confirmation) {
      toast.add({
        severity: 'warn',
        summary: 'Mot de passe requis',
        detail: 'Un mot de passe est obligatoire pour créer un nouvel utilisateur.',
        life: 4000
      });
      return;
    }
    if (formData.password !== formData.password_confirmation) {
      toast.add({
        severity: 'error',
        summary: 'Mots de passe différents',
        detail: 'Les mots de passe saisis ne correspondent pas. Veuillez vérifier.',
        life: 4000
      });
      return;
    }
  }

  // Mapper les champs frontend vers ce que l'API attend
  const mappedData = {
    prenom: formData.first_name,
    nom: formData.name,
    email: formData.email,
    matricule: formData.matricule,
    telephone: formData.phone || '',
    department_id: formData.department_id,
    role_id: formData.role_id,
    is_active: formData.is_active,
    date_embauche: new Date().toISOString().split('T')[0]
  };

  // Ajouter l'ID pour les modifications
  if (editedIndex.value > -1 && formData.id) {
    mappedData.id = formData.id;
  }

  // Ajouter le mot de passe pour un nouvel utilisateur
  if (editedIndex.value === -1 && formData.password) {
    mappedData.password = formData.password;
  }

  try {
    console.log('💾 Sauvegarde utilisateur:', formData);
    if (editedIndex.value > -1) {
      // Mise à jour utilisateur existant
      console.log('📝 Mise à jour utilisateur ID:', formData.id);
      await usersStore.updateUser(formData.id, mappedData);
      console.log('✅ Utilisateur mis à jour avec succès');
      toast.add({
        severity: 'success',
        summary: 'Utilisateur modifié',
        detail: `Le profil de ${formData.first_name} ${formData.name} a été mis à jour avec succès.`,
        life: 3000
      });
    } else {
      // Création nouvel utilisateur
      console.log('✨ Création nouvel utilisateur');
      await usersStore.addUser(mappedData);
      console.log('✅ Utilisateur créé avec succès');
      toast.add({
        severity: 'success',
        summary: 'Utilisateur créé',
        detail: `${formData.first_name} ${formData.name} a été ajouté à l'équipe avec succès.`,
        life: 3000
      });
    }
    closeDialog();
    await loadAllUsers(); // Force refresh avec tous les utilisateurs
  } catch (error) {
    console.error('❌ Erreur lors de la sauvegarde de l\'utilisateur:', error);
    console.error('❌ Response data:', error.response?.data);
    console.error('❌ Status:', error.response?.status);
    toast.add({
      severity: 'error',
      summary: 'Erreur de sauvegarde',
      detail: 'Impossible de sauvegarder les données utilisateur. Veuillez réessayer.',
      life: 5000
    });
  }
};

const confirmDelete = (user) => {
  userToDelete.value = user;
  dialogDelete.value = true;
};

const deleteUser = async (user) => {
  if (!user) return;

  try {
    console.log('🗑️ Suppression utilisateur:', user);
    await usersStore.removeUser(user.id);
    console.log('✅ Utilisateur supprimé avec succès');
    toast.add({
      severity: 'success',
      summary: 'Utilisateur supprimé',
      detail: `${user.first_name} ${user.name} a été retiré du système avec succès.`,
      life: 3000
    });
    closeDeleteDialog();
    await loadAllUsers(); // Force refresh avec tous les utilisateurs
  } catch (error) {
    console.error('❌ Erreur lors de la suppression de l\'utilisateur:', error);
    console.error('❌ Response data:', error.response?.data);
    console.error('❌ Status:', error.response?.status);
    toast.add({
      severity: 'error',
      summary: 'Erreur de suppression',
      detail: 'Impossible de supprimer cet utilisateur. Il pourrait avoir des données liées.',
      life: 5000
    });
  }
};

const closeDeleteDialog = () => {
  dialogDelete.value = false;
  userToDelete.value = null;
};

const toggleUserStatus = async (user) => {
  try {
    const response = await usersStore.toggleUserStatus(user.id);
    let newStatus;
    if (response.data && response.data.is_active !== undefined) {
      newStatus = response.data.is_active;
    } else if (response.data && response.data.data && response.data.data.is_active !== undefined) {
      newStatus = response.data.data.is_active;
    } else {
      const updatedUser = users.value.find(u => u.id === user.id);
      newStatus = updatedUser ? updatedUser.is_active : !user.is_active;
    }
    
    const statusText = newStatus ? 'activé' : 'désactivé';
    toast.add({
      severity: 'info',
      summary: `Compte ${statusText}`,
      detail: `Le compte de ${user.first_name} ${user.name} a été ${statusText} avec succès.`,
      life: 3000
    });
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Erreur de statut',
      detail: 'Impossible de modifier le statut de cet utilisateur.',
      life: 4000
    });
  }
};

const resetPassword = (user) => {
  userToResetPassword.value = user;
  dialogResetPassword.value = true;
};

const handleResetPassword = async (passwordData) => {
  try {
    console.log('🔑 Réinitialisation mot de passe pour:', userToResetPassword.value);
    // Mettre à jour le store pour envoyer les données du mot de passe
    await usersStore.resetUserPasswordWithData(userToResetPassword.value.id, passwordData);
    console.log('✅ Mot de passe réinitialisé avec succès');

    toast.add({
      severity: 'success',
      summary: 'Mot de passe réinitialisé',
      detail: `Le mot de passe de ${userToResetPassword.value.first_name} ${userToResetPassword.value.name} a été réinitialisé avec succès.`,
      life: 3000
    });

    // Fermer le modal
    dialogResetPassword.value = false;
    userToResetPassword.value = null;
  } catch (error) {
    console.error('❌ Erreur lors de la réinitialisation du mot de passe:', error);
    console.error('❌ Response data:', error.response?.data);
    console.error('❌ Status:', error.response?.status);
    toast.add({
      severity: 'error',
      summary: 'Erreur de réinitialisation',
      detail: 'Impossible de réinitialiser le mot de passe. Veuillez réessayer.',
      life: 4000
    });
  }
};

const loadUsers = async (forceRefresh = false) => {
  try {
    await usersStore.fetchUsers(
      currentPage.value,
      itemsPerPage.value,
      '',
      forceRefresh
    );
  } catch (error) {
    // Chargement silencieux - pas de toast pour cette action
  }
};

// Watch search input for real-time feedback
let searchTimeout;
watch(search, (newVal, oldVal) => {
  clearTimeout(searchTimeout);
  
  // Suppression des toasts de recherche - recherche silencieuse
  // La recherche se fait automatiquement via filteredUsers computed
});

// Load initial data
onMounted(() => {
  // Démarre le skeleton loader
  loadingUsers.value = true;
  
  // Simule un délai de 1 seconde avant de charger les données
  setTimeout(async () => {
    await loadAllUsers(); // Charger tous les utilisateurs
    usersStore.fetchRoles();
    usersStore.fetchDepartments();
    loadingUsers.value = false;
  }, 1000);
  
  // Test toast au chargement
  setTimeout(() => {
    // toast.add({
    //   severity: 'info',
    //   summary: 'Système initialisé',
    //   detail: 'Interface de gestion des utilisateurs chargée avec succès.',
    //   life: 3000
    // });
  }, 1000);
});

// Load all users initially
const loadAllUsers = async () => {
  try {
    // Charger avec une pagination élevée pour obtenir tous les utilisateurs
    await usersStore.fetchUsers(1, 100, '', true);
  } catch (error) {
    // Chargement silencieux - pas de toast
  }
};

</script>

<style scoped>
.user-management-view {
  padding: 20px;
}

.user-selected {
  background-color: #e3f2fd !important;
  border-left: 4px solid #1976d2 !important;
}

.user-selected:hover {
  background-color: #bbdefb !important;
}

.v-data-table tbody tr {
  cursor: pointer;
}

.v-data-table tbody tr:hover {
  background-color: #f5f5f5 !important;
}
</style>
