<template>
  <v-container fluid class="user-management-view">
    <v-card class="rounded-lg" elevation="2">
      <v-card-title class="d-flex align-center pa-4">
        <span class="text-h5 font-weight-bold">Gestion des Utilisateurs</span>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-inner-icon="mdi-magnify"
          label="Rechercher un utilisateur..."
          variant="outlined"
          density="compact"
          hide-details
          class="mr-4"
          style="max-width: 350px"
          @keyup.enter="searchUsers"
        ></v-text-field>
        <v-btn
          variant="outlined"
          color="primary"
          @click="refreshAllData"
          :loading="usersLoading || rolesLoading || departmentsLoading"
          class="mr-2"
          title="Actualiser les données"
        >
          <v-icon>mdi-refresh</v-icon>
        </v-btn>
        <v-btn
          color="primary"
          @click="openDialog()"
          prepend-icon="mdi-plus-circle"
          :loading="loading"
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

      <v-data-table
        :headers="headers"
        :items="users"
        :loading="usersLoading || loading"
        v-model:page="currentPage"
        v-model:items-per-page="itemsPerPage"
        :items-per-page-options="[5, 10, 20, 50]"
        :server-items-length="totalUsers"
        class="elevation-0"
        hover
        loading-text="Chargement des utilisateurs..."
        no-data-text="Aucun utilisateur trouvé"
        @update:page="handlePageChange"
        @update:items-per-page="handleItemsPerPageChange"
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
      :roles-loading="rolesLoading"
      :departments-loading="departmentsLoading"
      :loading="loading"
      @submit="saveUser"
    />

    <!-- Confirmation Dialog for Delete -->
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-card>
        <v-card-title class="text-h5">
          Confirmer la suppression
        </v-card-title>
        <v-card-text>
          Êtes-vous sûr de vouloir supprimer l'utilisateur
          <strong>{{ userToDelete?.name }} {{ userToDelete?.first_name }}</strong> ?
          <br><br>
          Cette action est irréversible.
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="closeDeleteDialog" :disabled="loading">Annuler</v-btn>
          <v-btn
            color="red-darken-1"
            variant="elevated"
            @click="deleteUser"
            :loading="loading"
          >
            Supprimer
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Reset Password Modal -->
    <ResetPasswordModal
      v-model="dialogResetPassword"
      :user="userToResetPassword"
      :loading="loading"
      @submit="handleResetPassword"
    />

  </v-container>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { useUsersStore } from "@/stores/useUsersStore";
import { useNotificationsStore } from "@/stores/notifications";
import { storeToRefs } from "pinia";
import UserModal from "@/components/admin/UserModal.vue";
import ResetPasswordModal from "@/components/admin/ResetPasswordModal.vue";

// Store
const usersStore = useUsersStore();
const notificationsStore = useNotificationsStore();
const {
  users,
  roles,
  departments,
  loading,
  usersLoading,
  rolesLoading,
  departmentsLoading,
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

const showSnackbar = (text, color = 'success') => {
  const type = color === 'success' ? 'success' : color === 'error' ? 'error' : 'info';
  notificationsStore.addToast({
    type,
    title: type === 'success' ? '✅ Succès' : type === 'error' ? '❌ Erreur' : 'ℹ️ Information',
    message: text,
    timeout: 5000
  });
};

const handleFormError = (errorMessage) => {
  notificationsStore.notifyError(errorMessage);
};

const clearError = () => {
  usersStore.error = null;
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
    showSnackbar('Veuillez remplir tous les champs obligatoires', 'error');
    return;
  }

  // Validation du mot de passe pour les nouveaux utilisateurs
  if (editedIndex.value === -1) {
    if (!formData.password || !formData.password_confirmation) {
      showSnackbar('Le mot de passe est requis pour un nouvel utilisateur', 'error');
      return;
    }
    if (formData.password !== formData.password_confirmation) {
      showSnackbar('Les mots de passe ne correspondent pas', 'error');
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
    date_embauche: new Date().toISOString().split('T')[0]
  };

  // Ajouter le mot de passe pour un nouvel utilisateur
  if (editedIndex.value === -1 && formData.password) {
    mappedData.password = formData.password;
  }

  try {
    if (editedIndex.value > -1) {
      // Mise à jour utilisateur existant
      await usersStore.updateUser(formData.id, mappedData);
      showSnackbar(` L'utilisateur ${formData.first_name} ${formData.name} a été modifié avec succès`, 'success');
    } else {
      // Création nouvel utilisateur
      await usersStore.addUser(mappedData);
      showSnackbar(` L'utilisateur ${formData.first_name} ${formData.name} a été créé avec succès`, 'success');
    }
    closeDialog();
    invalidateCache(); // Invalider le cache
    await loadUsers(true); // Force refresh
  } catch (error) {
    showSnackbar(' Erreur lors de la sauvegarde: ' + error.message, 'error');
  }
};

const confirmDelete = (user) => {
  userToDelete.value = user;
  dialogDelete.value = true;
};

const deleteUser = async () => {
  if (!userToDelete.value) return;

  try {
    await usersStore.removeUser(userToDelete.value.id);
    showSnackbar(` L'utilisateur ${userToDelete.value.first_name} ${userToDelete.value.name} a été supprimé avec succès`, 'success');
    closeDeleteDialog();
    invalidateCache(); // Invalider le cache
    await loadUsers(true); // Force refresh
  } catch (error) {
    showSnackbar(' Erreur lors de la suppression de l\'utilisateur', 'error');
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
    showSnackbar(
      `L'utilisateur ${user.first_name} ${user.name} a été ${statusText} avec succès`,
      'success'
    );
  } catch (error) {
    showSnackbar('Erreur lors du changement de statut de l\'utilisateur', 'error');
  }
};

const resetPassword = (user) => {
  userToResetPassword.value = user;
  dialogResetPassword.value = true;
};

const handleResetPassword = async (passwordData) => {
  try {
    // Mettre à jour le store pour envoyer les données du mot de passe
    await usersStore.resetUserPasswordWithData(userToResetPassword.value.id, passwordData);

    showSnackbar(
      `Mot de passe réinitialisé avec succès pour ${userToResetPassword.value.first_name} ${userToResetPassword.value.name}`,
      'success'
    );

    // Fermer le modal
    dialogResetPassword.value = false;
    userToResetPassword.value = null;
  } catch (error) {
    showSnackbar(' Erreur lors de la réinitialisation du mot de passe', 'error');
  }
};

const handlePageChange = (page) => {
  currentPage.value = page;
  loadUsers();
};

const handleItemsPerPageChange = (perPage) => {
  itemsPerPage.value = perPage;
  currentPage.value = 1;
  loadUsers();
};

const searchUsers = () => {
  currentPage.value = 1;
  loadUsers();
  // Notification pour la recherche
  if (search.value.trim()) {
    showSnackbar(`Recherche en cours pour "${search.value}"...`, 'info');
  }
};

const loadUsers = async (forceRefresh = false) => {
  try {
    await usersStore.fetchUsers(
      currentPage.value,
      itemsPerPage.value,
      search.value,
      forceRefresh
    );
  } catch (error) {
    showSnackbar('Erreur lors du chargement des utilisateurs', 'error');
  }
};

// Fonction pour forcer le refresh de toutes les données
const refreshAllData = async () => {
  try {
    usersStore.cache.roles.timestamp = null;
    usersStore.cache.departments.timestamp = null;
    usersStore.cache.users.timestamp = null;
    const promises = [
      usersStore.fetchRoles(true),
      usersStore.fetchDepartments(true),
      loadUsers(true)
    ];
    await Promise.all(promises);
    showSnackbar('données actualisées avec succès', 'success');
  } catch (error) {
    showSnackbar('Erreur lors de l\'actualisation des données', 'error');
  }
};

// Optimisation des actions CRUD - invalider le cache après modification
const invalidateCache = () => {
  usersStore.cache.users.timestamp = null;
};

// Watch search input with optimized debounce
let searchTimeout;
watch(search, (newVal, oldVal) => {
  // Si la recherche est vidée, charger immédiatement
  if (!newVal && oldVal) {
    clearTimeout(searchTimeout);
    searchUsers();
    return;
  }

  // Debounce pour les autres cas
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    if (search.value.trim()) {
      searchUsers();
    }
  }, 300);
});

// Load initial data
onMounted(() => {
  loadUsers();
  usersStore.fetchRoles();
  usersStore.fetchDepartments();
});

</script>
