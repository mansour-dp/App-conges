<template>
  <v-container fluid class="settings-view">
    <v-row>
      <v-col cols="12">
        <h1 class="text-h4 font-weight-bold text-grey-darken-3">Paramètres</h1>
        <p class="text-subtitle-1 text-grey-darken-1">
          Gérez la configuration globale de l'application.
        </p>
      </v-col>
    </v-row>

    <v-row>
      <!-- Security Settings -->
      <v-col cols="12" md="6">
        <v-card class="rounded-lg" elevation="2">
          <v-card-title class="d-flex align-center">
            <v-icon start icon="mdi-shield-lock-outline"></v-icon>
            <span class="font-weight-bold">Sécurité</span>
          </v-card-title>
          <v-list-item
            title="Authentification à deux facteurs (2FA)"
            subtitle="Exiger un code de vérification à la connexion"
          >
            <template v-slot:append>
              <v-switch
                v-model="settings.security.twoFactorAuth"
                color="primary"
                inset
                hide-details
              ></v-switch>
            </template>
          </v-list-item>
          <v-list-item title="Complexité du mot de passe">
            <v-select
              v-model="settings.security.passwordPolicy"
              :items="['Simple', 'Moyen', 'Complexe']"
              variant="outlined"
              density="compact"
              hide-details
              class="mt-2"
            ></v-select>
          </v-list-item>
        </v-card>
      </v-col>

      <!-- Notification Settings -->
      <v-col cols="12" md="6">
        <v-card class="rounded-lg" elevation="2">
          <v-card-title class="d-flex align-center">
            <v-icon start icon="mdi-email-outline"></v-icon>
            <span class="font-weight-bold">Notifications</span>
          </v-card-title>
          <v-list-item title="Notifications par e-mail">
            <template v-slot:append>
              <v-switch
                v-model="settings.notifications.enableEmails"
                color="primary"
                inset
                hide-details
              ></v-switch>
            </template>
          </v-list-item>
          <v-list-item title="E-mail de l'expéditeur">
            <v-text-field
              v-model="settings.notifications.senderEmail"
              variant="outlined"
              density="compact"
              hide-details
              placeholder="noreply@senelec.sn"
              class="mt-2"
            ></v-text-field>
          </v-list-item>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <!-- General Settings -->
      <v-col cols="12">
        <v-card class="rounded-lg" elevation="2">
          <v-card-title class="d-flex align-center">
            <v-icon start icon="mdi-cog-outline"></v-icon>
            <span class="font-weight-bold">Général</span>
          </v-card-title>
          <v-list-item
            title="Mode Maintenance"
            subtitle="Rendre l'application temporairement inaccessible aux utilisateurs"
          >
            <template v-slot:append>
              <v-switch
                v-model="settings.general.maintenanceMode"
                color="warning"
                inset
                hide-details
              ></v-switch>
            </template>
          </v-list-item>
        </v-card>
      </v-col>
    </v-row>

    <v-row>
      <v-col class="text-right">
        <v-btn size="large" color="primary" prepend-icon="mdi-content-save">
          Sauvegarder les changements
        </v-btn>
      </v-col>
    </v-row>
  </v-container>
</template>

<script setup>
import { ref } from "vue";

const settings = ref({
  security: {
    twoFactorAuth: false,
    passwordPolicy: "Moyen",
  },
  notifications: {
    enableEmails: true,
    senderEmail: "noreply@senelec.sn",
  },
  general: {
    maintenanceMode: false,
  },
});
</script>

<style scoped>
.settings-view {
  background-color: #f4f6f8;
}
.rounded-lg {
  border-radius: 12px;
}
.v-list-item {
  padding: 16px 20px !important;
}
.v-card-title {
  padding: 16px 20px;
}
</style>
