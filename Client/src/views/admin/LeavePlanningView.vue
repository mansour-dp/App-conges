<template>
  <v-container fluid class="leave-planning-view">
    <v-card class="rounded-lg" elevation="2">
      <v-card-title class="d-flex align-center pa-4">
        <v-icon icon="mdi-calendar-month" class="mr-2" color="primary"></v-icon>
        <span class="text-h5 font-weight-bold">Planification des Congés</span>
      </v-card-title>

      <v-tabs v-model="activeTab" bg-color="primary" color="white" centered>
        <v-tab value="plans">
          <v-icon class="mr-2">mdi-calendar-account</v-icon>
          Plans de Congés
        </v-tab>
        <v-tab value="holidays">
          <v-icon class="mr-2">mdi-calendar-star</v-icon>
          Jours Fériés
        </v-tab>
      </v-tabs>

      <v-card-text>
        <v-tabs-window v-model="activeTab">
          <!-- Onglet Plans de Congés -->
          <v-tabs-window-item value="plans">
            <div class="d-flex justify-space-between align-center mb-4">
              <v-text-field
                v-model="leavePlanSearch"
                label="Rechercher une période de congés..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="comfortable"
                clearable
                hide-details
                style="max-width: 400px"
              />
              
              <v-btn
                color="primary"
                @click="openLeavePlanDialog()"
                prepend-icon="mdi-plus-circle"
                variant="elevated"
              >
                Ajouter une période
              </v-btn>
            </div>

            <!-- Skeleton Loader pour les plans de congés -->
            <v-skeleton-loader
              v-if="loadingPlans"
              type="table-row@5"
              class="mb-4"
            ></v-skeleton-loader>

            <v-data-table
              v-else
              :headers="leavePlanHeaders"
              :items="leavePlansStore.leavePlans"
              :search="leavePlanSearch"
              :items-per-page="10"
              class="elevation-0"
              hover
              no-data-text="Aucune période de congés trouvée"
            >
              <template v-slot:item.start_date="{ item }">
                {{ formatDate(item.start_date) }}
              </template>
              <template v-slot:item.end_date="{ item }">
                {{ formatDate(item.end_date) }}
              </template>
              <template v-slot:item.leave_type="{ item }">
                <v-chip
                  :color="getTypeColor(item.leave_type)"
                  variant="tonal"
                  size="small"
                >
                  {{ getTypeLabel(item.leave_type) }}
                </v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-btn
                  size="small"
                  variant="text"
                  icon="mdi-pencil"
                  color="primary"
                  @click="openLeavePlanDialog(item)"
                ></v-btn>
                <v-btn
                  size="small"
                  variant="text"
                  icon="mdi-delete"
                  color="error"
                  @click="openDeleteLeavePlanDialog(item)"
                ></v-btn>
              </template>
            </v-data-table>
          </v-tabs-window-item>

          <!-- Onglet Jours Fériés -->
          <v-tabs-window-item value="holidays">
            <div class="d-flex justify-space-between align-center mb-4">
              <v-text-field
                v-model="holidaySearch"
                label="Rechercher un jour férié..."
                prepend-inner-icon="mdi-magnify"
                variant="outlined"
                density="comfortable"
                clearable
                hide-details
                style="max-width: 400px"
              />
              
              <v-btn
                color="primary"
                @click="openHolidayDialog()"
                prepend-icon="mdi-plus-circle"
                variant="elevated"
              >
                Ajouter un jour férié
              </v-btn>
            </div>

            <!-- Skeleton Loader pour les jours fériés -->
            <v-skeleton-loader
              v-if="loadingHolidays"
              type="table-row@5"
              class="mb-4"
            ></v-skeleton-loader>

            <v-data-table
              v-else
              :headers="holidayHeaders"
              :items="holidaysStore.holidays"
              :search="holidaySearch"
              :items-per-page="10"
              class="elevation-0"
              hover
              no-data-text="Aucun jour férié trouvé"
            >
              <template v-slot:item.date="{ item }">
                {{ formatDate(item.date) }}
              </template>
              <template v-slot:item.type="{ item }">
                <v-chip
                  :color="getHolidayTypeColor(item.type)"
                  variant="tonal"
                  size="small"
                >
                  {{ getHolidayTypeLabel(item.type) }}
                </v-chip>
              </template>
              <template v-slot:item.actions="{ item }">
                <v-btn
                  size="small"
                  variant="text"
                  icon="mdi-pencil"
                  color="primary"
                  @click="openHolidayDialog(item)"
                ></v-btn>
                <v-btn
                  size="small"
                  variant="text"
                  icon="mdi-delete"
                  color="error"
                  @click="openDeleteHolidayDialog(item)"
                ></v-btn>
              </template>
            </v-data-table>
          </v-tabs-window-item>
        </v-tabs-window>
      </v-card-text>
    </v-card>

    <!-- Modals professionnels -->
    <LeavePlanModal
      v-model="leavePlanDialog"
      :leave-plan="selectedLeavePlan"
      :loading="leavePlansStore.loading"
      @submit="handleLeavePlanSubmit"
    />

    <LeavePlanDeleteModal
      v-model="deleteLeavePlanDialog"
      :leave-plan="leavePlanToDelete"
      @confirm="deleteLeavePlan"
    />

    <HolidayModal
      v-model="holidayDialog"
      :holiday="selectedHoliday"
      :loading="holidaysStore.loading"
      @submit="handleHolidaySubmit"
    />

    <HolidayDeleteModal
      v-model="deleteHolidayDialog"
      :holiday="holidayToDelete"
      @confirm="deleteHoliday"
    />
  </v-container>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useLeavePlansStore } from "@/stores/leavePlansStore";
import { useHolidaysStore } from "@/stores/holidaysStore";
import { useToast } from 'primevue/usetoast';
import LeavePlanModal from "@/components/admin/LeavePlanModal.vue";
import LeavePlanDeleteModal from "@/components/admin/LeavePlanDeleteModal.vue";
import HolidayModal from "@/components/admin/HolidayModal.vue";
import HolidayDeleteModal from "@/components/admin/HolidayDeleteModal.vue";

// Stores
const leavePlansStore = useLeavePlansStore();
const holidaysStore = useHolidaysStore();
const toast = useToast();

// Onglets
const activeTab = ref('plans');

// Loading states for skeleton loaders
const loadingPlans = ref(true);
const loadingHolidays = ref(true);

// Dialogs
const leavePlanDialog = ref(false);
const deleteLeavePlanDialog = ref(false);
const holidayDialog = ref(false);
const deleteHolidayDialog = ref(false);
const selectedLeavePlan = ref(null);
const selectedHoliday = ref(null);
const leavePlanToDelete = ref(null);
const holidayToDelete = ref(null);

// Recherche
const leavePlanSearch = ref('');
const holidaySearch = ref('');

// Headers pour les tables
const leavePlanHeaders = [
  { title: "Nom", key: "name" },
  { title: "Date de début", key: "start_date" },
  { title: "Date de fin", key: "end_date" },
  { title: "Nombre de jours", key: "days_count" },
  { title: "Type de congé", key: "leave_type" },
  { title: "Actions", key: "actions", sortable: false },
];

const holidayHeaders = [
  { title: "Nom", key: "name" },
  { title: "Date", key: "date" },
  { title: "Type", key: "type" },
  { title: "Actions", key: "actions", sortable: false },
];

// Utility functions
const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return date.toLocaleDateString('fr-FR');
};

const getTypeColor = (type) => {
  const colors = {
    conge_annuel: 'primary',
    conges_fractionnes: 'info',
    autres_conges_legaux: 'success',
    conge_maladie: 'warning',
    conge_maternite: 'pink',
    conge_paternite: 'blue',
    conge_sans_solde: 'grey',
    absence_exceptionnelle: 'orange',
    report_conge: 'purple'
  };
  return colors[type] || 'default';
};

const getTypeLabel = (type) => {
  const labels = {
    conge_annuel: 'Congé annuel',
    conges_fractionnes: 'Congés fractionnés',
    autres_conges_legaux: 'Autres congés légaux',
    conge_maladie: 'Congé maladie',
    conge_maternite: 'Congé maternité',
    conge_paternite: 'Congé paternité',
    conge_sans_solde: 'Congé sans solde',
    absence_exceptionnelle: 'Absence exceptionnelle',
    report_conge: 'Report de congé'
  };
  return labels[type] || type;
};

const getHolidayTypeColor = (type) => {
  const colors = {
    national: 'red',
    religious: 'blue',
    local: 'orange',
    company: 'green'
  };
  return colors[type] || 'default';
};

const getHolidayTypeLabel = (type) => {
  const labels = {
    national: 'National',
    religious: 'Religieux',
    local: 'Local',
    company: 'Autre'
  };
  return labels[type] || type;
};

// Actions pour les périodes de congés
const openLeavePlanDialog = (leavePlan = null) => {
  selectedLeavePlan.value = leavePlan;
  leavePlanDialog.value = true;
};

const openDeleteLeavePlanDialog = (leavePlan) => {
  leavePlanToDelete.value = leavePlan;
  deleteLeavePlanDialog.value = true;
};

const handleLeavePlanSubmit = async (formData) => {
  try {
    if (selectedLeavePlan.value?.id) {
      await leavePlansStore.updateLeavePlan({ ...formData, id: selectedLeavePlan.value.id });
      toast.add({
        severity: 'success',
        summary: 'Modification réussie',
        detail: 'La période de congés a été modifiée avec succès',
        life: 3000
      });
    } else {
      await leavePlansStore.addLeavePlan(formData);
      toast.add({
        severity: 'success',
        summary: 'Ajout réussi',
        detail: 'La période de congés a été ajoutée avec succès',
        life: 3000
      });
    }
    leavePlanDialog.value = false;
    selectedLeavePlan.value = null;
  } catch (error) {
    console.error('Erreur lors de la sauvegarde de la période de congés:', error);
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.response?.data?.message || 'Erreur lors de la sauvegarde de la période de congés',
      life: 5000
    });
  }
};

const deleteLeavePlan = async () => {
  try {
    await leavePlansStore.deleteLeavePlan(leavePlanToDelete.value.id);
    toast.add({
      severity: 'success',
      summary: 'Suppression réussie',
      detail: 'La période de congés a été supprimée avec succès',
      life: 3000
    });
    deleteLeavePlanDialog.value = false;
    leavePlanToDelete.value = null;
  } catch (error) {
    console.error('Erreur lors de la suppression de la période de congés:', error);
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.response?.data?.message || 'Erreur lors de la suppression de la période de congés',
      life: 5000
    });
  }
};

// Actions pour les jours fériés
const openHolidayDialog = (holiday = null) => {
  selectedHoliday.value = holiday;
  holidayDialog.value = true;
};

const openDeleteHolidayDialog = (holiday) => {
  holidayToDelete.value = holiday;
  deleteHolidayDialog.value = true;
};

const handleHolidaySubmit = async (formData) => {
  try {
    if (selectedHoliday.value?.id) {
      await holidaysStore.updateHoliday({ ...formData, id: selectedHoliday.value.id });
      toast.add({
        severity: 'success',
        summary: 'Modification réussie',
        detail: 'Le jour férié a été modifié avec succès',
        life: 3000
      });
    } else {
      await holidaysStore.addHoliday(formData);
      toast.add({
        severity: 'success',
        summary: 'Ajout réussi',
        detail: 'Le jour férié a été ajouté avec succès',
        life: 3000
      });
    }
    holidayDialog.value = false;
    selectedHoliday.value = null;
  } catch (error) {
    console.error('Erreur lors de la sauvegarde du jour férié:', error);
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.response?.data?.message || 'Erreur lors de la sauvegarde du jour férié',
      life: 5000
    });
  }
};

const deleteHoliday = async () => {
  try {
    await holidaysStore.deleteHoliday(holidayToDelete.value.id);
    toast.add({
      severity: 'success',
      summary: 'Suppression réussie',
      detail: 'Le jour férié a été supprimé avec succès',
      life: 3000
    });
    deleteHolidayDialog.value = false;
    holidayToDelete.value = null;
  } catch (error) {
    console.error('Erreur lors de la suppression du jour férié:', error);
    toast.add({
      severity: 'error',
      summary: 'Erreur',
      detail: error.response?.data?.message || 'Erreur lors de la suppression du jour férié',
      life: 5000
    });
  }
};

// Initialisation
onMounted(async () => {
  try {
    // Démarre les skeleton loaders
    loadingPlans.value = true;
    loadingHolidays.value = true;

    // Simule un délai de 1 seconde avant de charger les données
    setTimeout(async () => {
      await leavePlansStore.fetchLeavePlans();
      loadingPlans.value = false;
    }, 1000);

    setTimeout(async () => {
      await holidaysStore.fetchHolidays();
      loadingHolidays.value = false;
    }, 1000);

  } catch (error) {
    console.error('Erreur lors du chargement des données:', error);
    // Arrête les loaders même en cas d'erreur
    loadingPlans.value = false;
    loadingHolidays.value = false;
  }
});
</script>

<style scoped>
.leave-planning-view {
  padding: 20px;
}

.rounded-lg {
  border-radius: 12px;
}

/* Centrage simple des onglets */
:deep(.v-tabs .v-slide-group__content) {
  justify-content: center !important;
}
</style>
