<template>
  <v-container fluid class="leave-planning-view">
    <v-card class="rounded-lg" elevation="2">
      <v-card-title class="d-flex align-center pa-4">
        <span class="text-h5 font-weight-bold">Planification Annuelle</span>
        <v-spacer></v-spacer>
        <v-btn
          color="primary"
          @click="openActiveDialog"
          prepend-icon="mdi-plus-circle"
          :key="tab"
        >
          Ajouter {{ tab === "plans" ? "une période" : "un jour férié" }}
        </v-btn>
      </v-card-title>

      <v-tabs v-model="tab" color="primary" align-tabs="center" class="mb-4">
        <v-tab value="plans">Périodes de Congé</v-tab>
        <v-tab value="holidays">Jours Fériés</v-tab>
      </v-tabs>

      <v-window v-model="tab">
        <v-window-item value="plans">
          <v-data-table
            :headers="planHeaders"
            :items="leavePlans"
            class="elevation-0"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon small class="mr-2" @click="openPlanDialog(item)"
                >mdi-pencil</v-icon
              >
              <v-icon small @click="deletePlan(item.id)">mdi-delete</v-icon>
            </template>
          </v-data-table>
        </v-window-item>

        <v-window-item value="holidays">
          <v-data-table
            :headers="holidayHeaders"
            :items="holidays"
            class="elevation-0"
          >
            <template v-slot:item.actions="{ item }">
              <v-icon small class="mr-2" @click="openHolidayDialog(item)"
                >mdi-pencil</v-icon
              >
              <v-icon small @click="deleteHoliday(item.id)">mdi-delete</v-icon>
            </template>
          </v-data-table>
        </v-window-item>
      </v-window>
    </v-card>

    <!-- Dialog pour les Périodes de Congé -->
    <v-dialog v-model="planDialog" max-width="500px">
      <v-card>
        <v-card-title>{{ planFormTitle }}</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="editedPlan.name"
            label="Nom de la période"
          ></v-text-field>
          <v-text-field
            v-model="editedPlan.startDate"
            label="Date de début"
            type="date"
          ></v-text-field>
          <v-text-field
            v-model="editedPlan.endDate"
            label="Date de fin"
            type="date"
          ></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="closePlanDialog">Annuler</v-btn>
          <v-btn color="primary" @click="savePlan">Sauvegarder</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Dialog pour les Jours Fériés -->
    <v-dialog v-model="holidayDialog" max-width="500px">
      <v-card>
        <v-card-title>{{ holidayFormTitle }}</v-card-title>
        <v-card-text>
          <v-text-field
            v-model="editedHoliday.name"
            label="Nom du jour férié"
          ></v-text-field>
          <v-text-field
            v-model="editedHoliday.date"
            label="Date"
            type="date"
          ></v-text-field>
        </v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="closeHolidayDialog">Annuler</v-btn>
          <v-btn color="primary" @click="saveHoliday">Sauvegarder</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<script setup>
import { ref, computed } from "vue";
import { useLeavePlansStore } from "@/stores/leavePlansStore";
import { useHolidaysStore } from "@/stores/holidaysStore";
import { storeToRefs } from "pinia";

const tab = ref("plans");

// Store pour les périodes de congé
const leavePlansStore = useLeavePlansStore();
const { leavePlans } = storeToRefs(leavePlansStore);
const { addPlan, updatePlan, deletePlan } = leavePlansStore;

// Store pour les jours fériés
const holidaysStore = useHolidaysStore();
const { holidays } = storeToRefs(holidaysStore);
const { addHoliday, updateHoliday, deleteHoliday } = holidaysStore;

// State pour les périodes de congé
const planDialog = ref(false);
const editedPlan = ref({});
const planHeaders = ref([
  { title: "Nom", key: "name" },
  { title: "Début", key: "startDate" },
  { title: "Fin", key: "endDate" },
  { title: "Actions", key: "actions", sortable: false },
]);
const planFormTitle = computed(() =>
  editedPlan.value.id ? "Modifier la Période" : "Nouvelle Période"
);

function openPlanDialog(plan) {
  editedPlan.value = plan ? { ...plan } : {};
  planDialog.value = true;
}
function closePlanDialog() {
  planDialog.value = false;
}
function savePlan() {
  if (editedPlan.value.id) {
    updatePlan(editedPlan.value);
  } else {
    addPlan(editedPlan.value);
  }
  closePlanDialog();
}

// State pour les jours fériés
const holidayDialog = ref(false);
const editedHoliday = ref({});
const holidayHeaders = ref([
  { title: "Nom", key: "name" },
  { title: "Date", key: "date" },
  { title: "Actions", key: "actions", sortable: false },
]);
const holidayFormTitle = computed(() =>
  editedHoliday.value.id ? "Modifier le Jour Férié" : "Nouveau Jour Férié"
);

function openHolidayDialog(holiday) {
  editedHoliday.value = holiday ? { ...holiday } : {};
  holidayDialog.value = true;
}
function closeHolidayDialog() {
  holidayDialog.value = false;
}
function saveHoliday() {
  if (editedHoliday.value.id) {
    updateHoliday(editedHoliday.value);
  } else {
    addHoliday(editedHoliday.value);
  }
  closeHolidayDialog();
}

function openActiveDialog() {
  if (tab.value === "plans") {
    openPlanDialog();
  } else {
    openHolidayDialog();
  }
}
</script>

<style scoped>
.leave-planning-view {
  background-color: #f4f6f8;
}
.rounded-lg {
  border-radius: 12px;
}
</style>
