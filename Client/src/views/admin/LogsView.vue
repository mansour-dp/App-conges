<template>
  <v-container fluid class="logs-view">
    <v-card class="rounded-lg" elevation="2">
      <v-card-title class="d-flex align-center pa-4">
        <span class="text-h5 font-weight-bold">Journaux d'Activité</span>
        <v-spacer></v-spacer>
        <v-select
          v-model="selectedLevels"
          :items="logLevels"
          label="Filtrer par niveau"
          multiple
          chips
          clearable
          variant="outlined"
          density="compact"
          hide-details
          class="mr-4"
          style="max-width: 350px"
        ></v-select>
        <v-text-field
          v-model="search"
          append-inner-icon="mdi-magnify"
          label="Rechercher..."
          variant="outlined"
          density="compact"
          hide-details
          style="max-width: 350px"
        ></v-text-field>
      </v-card-title>

      <v-data-table
        :headers="headers"
        :items="filteredLogs"
        :search="search"
        :items-per-page="15"
        class="elevation-0"
        hover
      >
        <template v-slot:item.level="{ item }">
          <v-chip :color="getLevelColor(item.level)" dark small label>
            {{ item.level }}
          </v-chip>
        </template>
        <template v-slot:item.timestamp="{ item }">
          <span>{{ new Date(item.timestamp).toLocaleString() }}</span>
        </template>
      </v-data-table>
    </v-card>
  </v-container>
</template>

<script setup>
import { ref, computed } from "vue";

const search = ref("");
const logLevels = ["INFO", "WARN", "ERROR", "SUCCESS"];
const selectedLevels = ref([]);

const headers = ref([
  { title: "Date", key: "timestamp", align: "start", width: "200px" },
  { title: "Niveau", key: "level", align: "center", width: "120px" },
  { title: "Message", key: "message", sortable: false },
]);

const getLevelColor = (level) => {
  const colors = {
    INFO: "blue-grey",
    WARN: "orange-darken-2",
    ERROR: "red-darken-2",
    SUCCESS: "green-darken-1",
  };
  return colors[level] || "grey";
};

// Données de logs factices
const logs = ref([
  {
    id: 1,
    timestamp: "2024-05-21T10:00:00Z",
    level: "SUCCESS",
    message: 'Utilisateur "A. Ndiaye" créé avec succès.',
  },
  {
    id: 2,
    timestamp: "2024-05-21T10:05:12Z",
    level: "INFO",
    message: 'Rôle "Superieur" assigné à "M. Fall".',
  },
  {
    id: 3,
    timestamp: "2024-05-21T10:15:30Z",
    level: "WARN",
    message: 'Tentative de connexion échouée pour l\'utilisateur "guest".',
  },
  {
    id: 4,
    timestamp: "2024-05-21T11:00:00Z",
    level: "INFO",
    message: "Mise à jour des paramètres de sécurité effectuée par l'admin.",
  },
  {
    id: 5,
    timestamp: "2024-05-21T11:30:45Z",
    level: "ERROR",
    message:
      "Impossible de se connecter à la base de données. Erreur: Timeout.",
  },
  {
    id: 6,
    timestamp: "2024-05-21T12:00:00Z",
    level: "INFO",
    message: "Génération du rapport mensuel des congés.",
  },
  {
    id: 7,
    timestamp: "2024-05-21T12:02:15Z",
    level: "SUCCESS",
    message: 'Rapport "Conges_Mai_2024.pdf" généré.',
  },
  {
    id: 8,
    timestamp: "2024-05-21T13:10:05Z",
    level: "WARN",
    message:
      'Le champ "email" est manquant pour la création d\'un utilisateur.',
  },
  {
    id: 9,
    timestamp: "2024-05-21T14:00:00Z",
    level: "ERROR",
    message: "Service de notification inaccessible. URL: smtp.example.com",
  },
]);

const filteredLogs = computed(() => {
  if (selectedLevels.value.length === 0) {
    return logs.value;
  }
  return logs.value.filter((log) => selectedLevels.value.includes(log.level));
});
</script>

<style scoped>
.logs-view {
  background-color: #f4f6f8;
}
.rounded-lg {
  border-radius: 12px;
}
</style>
