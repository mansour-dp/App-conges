<template>
  <v-container fluid class="demandes-history-view pa-6">
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 font-weight-bold text-grey-darken-3">
          Historique des Demandes
        </h1>
        <p class="text-subtitle-1 text-grey-darken-1">
          Suivi de toutes les demandes de congé et de leur état d'approbation.
        </p>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-card elevation="2" class="rounded-lg">
          <v-card-title class="d-flex align-center pe-2">
            <v-icon icon="mdi-history"></v-icon>
            <span class="ms-2 font-weight-bold">Toutes les demandes</span>
            <v-spacer></v-spacer>
            <v-text-field
              v-model="search"
              density="compact"
              label="Rechercher"
              prepend-inner-icon="mdi-magnify"
              variant="solo-filled"
              flat
              hide-details
              single-line
            ></v-text-field>
          </v-card-title>
          <v-divider></v-divider>
          <v-data-table
            :headers="headers"
            :items="allDemandes"
            :search="search"
            :loading="loading"
            loading-text="Chargement des données..."
            no-data-text="Aucune demande trouvée."
            items-per-page-text="Demandes par page"
            class="elevation-0"
          >
            <template v-slot:item.status="{ value }">
              <v-chip
                :color="getStatusColor(value)"
                variant="elevated"
                size="small"
              >
                {{ getStatusText(value) }}
              </v-chip>
            </template>
            <template v-slot:item.niveauApprobation="{ value }">
              <v-chip
                :color="getNiveauColor(value)"
                variant="tonal"
                size="small"
              >
                {{ value }}
              </v-chip>
            </template>
            <template v-slot:item.nom="{ item }">
              {{ item.prenom }} {{ item.nom }}
            </template>
          </v-data-table>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref, computed } from "vue";
import { useDemandesStore } from "@/stores/demandes.js";

const search = ref("");
const demandesStore = useDemandesStore();

const allDemandes = computed(() => demandesStore.demandes);
const loading = computed(() => demandesStore.loading);

const headers = ref([
  { title: "Employé", key: "nom", sortable: true },
  { title: "Matricule", key: "matricule", sortable: true },
  { title: "Type", key: "typeDemande", sortable: true },
  { title: "Date Début", key: "dateDebut", sortable: true },
  { title: "Date Fin", key: "dateFin", sortable: true },
  { title: "Statut", key: "status", sortable: true },
  { title: "Niveau d'Approbation", key: "niveauApprobation", sortable: true },
]);

const getStatusColor = (status) => {
  const colors = {
    en_attente: "orange-darken-2",
    approuvee: "green-darken-1",
    rejetee: "red-darken-2",
  };
  return colors[status] || "grey";
};

const getStatusText = (status) => {
  const texts = {
    en_attente: "En attente",
    approuvee: "Approuvée",
    rejetee: "Rejetée",
  };
  return texts[status] || status;
};

const getNiveauColor = (niveau) => {
  if (niveau.includes("Supérieur")) return "blue-lighten-1";
  if (niveau.includes("Unité")) return "purple-lighten-1";
  if (niveau.includes("RH")) return "teal-lighten-1";
  if (niveau.includes("Directeur RH")) return "amber-darken-1";
  if (niveau === "Terminé") return "green-lighten-1";
  if (niveau === "Rejeté") return "red-lighten-1";
  return "grey";
};
</script>

<style scoped>
.demandes-history-view {
  background-color: #f4f6f8;
}
.rounded-lg {
  border-radius: 12px;
}
</style>
