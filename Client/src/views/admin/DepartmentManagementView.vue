<template>
  <v-container fluid class="department-management-view">
    <v-card class="rounded-lg" elevation="2">
      <v-card-title class="d-flex align-center pa-4">
        <span class="text-h5 font-weight-bold">Gestion des Départements</span>
        <v-spacer></v-spacer>
        <v-text-field
          v-model="search"
          append-inner-icon="mdi-magnify"
          label="Rechercher un département..."
          variant="outlined"
          density="compact"
          hide-details
          class="mr-4"
          style="max-width: 350px"
        ></v-text-field>
        <v-btn
          color="primary"
          @click="openDialog()"
          prepend-icon="mdi-plus-circle"
        >
          Ajouter un département
        </v-btn>
      </v-card-title>

      <!-- Skeleton Loader pour les départements -->
      <v-skeleton-loader
        v-if="loadingDepartments"
        type="table-row@5"
        class="ma-4"
      ></v-skeleton-loader>

      <v-data-table
        v-else
        :headers="headers"
        :items="computedDepartments"
        :search="search"
        :items-per-page="10"
        class="elevation-0"
        hover
      >
        <template v-slot:item.code="{ item }">
          <span>{{ item.code || 'Aucun code' }}</span>
        </template>
        <template v-slot:item.directorName="{ item }">
          <v-chip v-if="item.description" color="primary" small>{{
            item.description
          }}</v-chip>
          <span v-else class="text-grey">Aucune description</span>
        </template>
        <template v-slot:item.employeeCount="{ item }">
          <v-chip color="blue-grey" dark small
            >{{ item.employeeCount }} employé(s)</v-chip
          >
        </template>
        <template v-slot:item.actions="{ item }">
          <v-tooltip text="Modifier">
            <template v-slot:activator="{ props }">
              <v-icon
                v-bind="props"
                color="grey-darken-1"
                class="mr-2"
                @click="openDialog(item)"
                >mdi-pencil</v-icon
              >
            </template>
          </v-tooltip>
          <v-tooltip text="Supprimer">
            <template v-slot:activator="{ props }">
              <v-icon
                v-bind="props"
                color="red-darken-2"
                @click="confirmDelete(item)"
                >mdi-delete</v-icon
              >
            </template>
          </v-tooltip>
        </template>
      </v-data-table>
    </v-card>

    <!-- Modal pour Ajouter/Modifier un département -->
    <DepartmentModal
      v-model="dialog"
      :department="editedDepartment"
      :loading="departmentsStore.loading"
      @submit="saveDepartment"
    />

    <!-- Modal de confirmation de suppression -->
    <DepartmentDeleteModal
      v-model="dialogDelete"
      :department="departmentToDelete"
      :employee-count="getEmployeeCountForDepartment(departmentToDelete)"
      :loading="departmentsStore.loading"
      @confirm="deleteDept"
    />

  </v-container>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { useDepartmentsStore } from "@/stores/departments";
import { useUsersAdminStore } from "@/stores/usersAdmin";
import { storeToRefs } from "pinia";
import { useToast } from 'primevue/usetoast';
import DepartmentModal from "@/components/admin/DepartmentModal.vue";
import DepartmentDeleteModal from "@/components/admin/DepartmentDeleteModal.vue";

const departmentsStore = useDepartmentsStore();
const { departments } = storeToRefs(departmentsStore);

const usersStore = useUsersAdminStore();
const { users } = storeToRefs(usersStore);

const toast = useToast();

const search = ref("");
const dialog = ref(false);
const dialogDelete = ref(false);
const departmentToDelete = ref(null);

// Loading state for skeleton loader
const loadingDepartments = ref(true);

const editedIndex = ref(-1);
const editedDepartment = ref(null);
const defaultDepartment = {
  id: null,
  name: "",
  code: "",
  description: "",
};

const formTitle = computed(() =>
  editedIndex.value === -1 ? "Nouveau Département" : "Modifier le Département"
);

const headers = ref([
  { title: "Nom du Département", key: "name", sortable: true },
  { title: "Code", key: "code", sortable: true },
  { title: "Description", key: "directorName", sortable: false },
  { title: "Nombre d'Employés", key: "employeeCount", sortable: false },
  { title: "Actions", key: "actions", sortable: false, align: "end" },
]);

const computedDepartments = computed(() => {
  return departments.value.map((dept) => {
    const employeeCount = users.value.filter(
      (user) => user.department_id === dept.id || user.department?.id === dept.id
    ).length;
    
    return {
      ...dept,
      directorName: dept.description || null, // description dynamique
      employeeCount, // nombre d'employés dynamique
    };
  });
});

// Fonction pour obtenir le nombre d'employés dans un département
const getEmployeeCountForDepartment = (dept) => {
  if (!dept) return 0;
  return users.value.filter(
    (user) => user.department_id === dept.id || user.department?.id === dept.id
  ).length;
};

const openDialog = (dept) => {
  editedIndex.value = dept
    ? departments.value.findIndex((d) => d.id === dept.id)
    : -1;
  editedDepartment.value = dept ? { ...dept } : { ...defaultDepartment };
  dialog.value = true;
};

const closeDialog = () => {
  dialog.value = false;
};

const saveDepartment = async (formData) => {
  try {
    if (editedIndex.value > -1) {
      // Mise à jour
      await departmentsStore.updateDepartment(formData.id, formData);
      
      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Département modifié avec succès',
        life: 3000
      });
    } else {
      // Création
      await departmentsStore.addDepartment(formData);
      
      toast.add({
        severity: 'success',
        summary: 'Succès',
        detail: 'Département créé avec succès',
        life: 3000
      });
    }
    closeDialog();
    
    // Recharger les données
    await departmentsStore.fetchDepartments();
  } catch (error) {
    
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Erreur lors de la sauvegarde du département',
      life: 5000
    });
  }
};

const confirmDelete = (dept) => {
  departmentToDelete.value = dept;
  dialogDelete.value = true;
};

const deleteDept = async (dept) => {
  try {
  
    await departmentsStore.deleteDepartment(dept.id);
  
    toast.add({
      severity: 'success',
      summary: 'Succès',
      detail: 'Département supprimé avec succès',
      life: 3000
    });
    closeDeleteDialog();
    
    // Recharger les données
    await departmentsStore.fetchDepartments();
    await usersStore.fetchUsers(1, 100, '', true);
  } catch (error) {
    
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: 'Erreur lors de la suppression du département',
      life: 5000
    });
  }
};

const closeDeleteDialog = () => {
  dialogDelete.value = false;
  departmentToDelete.value = null;
};

// Lifecycle
onMounted(async () => {
  
  
  // Démarre le skeleton loader
  loadingDepartments.value = true;
  
  // Simule un délai de 1 seconde avant de charger les données
  setTimeout(async () => {
    try {
      await departmentsStore.fetchDepartments();
      await usersStore.fetchUsers(1, 100, '', true); 
    } catch (error) {
    } finally {
      loadingDepartments.value = false;
    }
  }, 1000);
});
</script>

<style scoped>
.department-management-view {
  background-color: #f4f6f8;
}
.rounded-lg {
  border-radius: 12px;
}
</style>
