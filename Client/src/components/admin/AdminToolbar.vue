<template>
  <v-app-bar app color="white" elevation="1">
    <v-toolbar-title class="font-weight-bold text-grey-darken-3">
      {{ title }}
    </v-toolbar-title>
    <v-spacer></v-spacer>

    <!-- Notifications Menu -->
    <v-menu offset-y>
      <template v-slot:activator="{ props }">
        <v-btn icon v-bind="props">
          <v-badge
            :content="pendingDemandesCount"
            color="red"
            :model-value="pendingDemandesCount > 0"
          >
            <v-icon>mdi-bell-outline</v-icon>
          </v-badge>
        </v-btn>
      </template>
      <v-list>
        <v-list-subheader>Notifications</v-list-subheader>
        <v-list-item v-if="pendingDemandesCount === 0">
          <v-list-item-title>Aucune nouvelle notification</v-list-item-title>
        </v-list-item>
        <v-list-item
          v-for="demande in pendingDemandes.slice(0, 5)"
          :key="demande.id"
          :title="`${demande.prenom} ${demande.nom}`"
          :subtitle="demande.typeDemande"
        >
        </v-list-item>
        <v-divider v-if="pendingDemandesCount > 5"></v-divider>
        <v-list-item
          v-if="pendingDemandesCount > 0"
          to="/admin/history"
          class="text-center"
        >
          <v-list-item-title class="text-blue">Voir tout</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>

    <!-- User Menu -->
    <v-menu offset-y>
      <template v-slot:activator="{ props }">
        <v-btn icon v-bind="props" class="ml-2">
          <v-avatar color="primary" size="36">
            <span class="white--text font-weight-bold">{{ userInitials }}</span>
          </v-avatar>
        </v-btn>
      </template>
      <v-list>
        <v-list-item v-if="currentUser" class="mb-2">
          <template v-slot:prepend>
            <v-avatar color="primary" size="40">
              <span class="white--text font-weight-bold">{{
                userInitials
              }}</span>
            </v-avatar>
          </template>
          <v-list-item-title class="font-weight-bold"
            >{{ currentUser.name }} {{ currentUser.prenom }}</v-list-item-title
          >
          <v-list-item-subtitle>{{ currentUser.role }}</v-list-item-subtitle>
        </v-list-item>
        <v-divider></v-divider>
        <v-list-item link>
          <template v-slot:prepend
            ><v-icon>mdi-account-outline</v-icon></template
          >
          <v-list-item-title>Mon Profil</v-list-item-title>
        </v-list-item>
        <v-list-item link @click="handleLogout">
          <template v-slot:prepend><v-icon>mdi-logout</v-icon></template>
          <v-list-item-title>Déconnexion</v-list-item-title>
        </v-list-item>
      </v-list>
    </v-menu>
  </v-app-bar>
</template>

<script setup>
import { computed } from "vue";
import { useRouter } from "vue-router";
import { useUsersStore } from "@/stores/useUsersStore";
import { useDemandesStore } from "@/stores/demandes";

defineProps({
  title: {
    type: String,
    default: "Administration",
  },
});

const router = useRouter();
const usersStore = useUsersStore();
const demandesStore = useDemandesStore();

const currentUser = computed(() => usersStore.currentUser);
const pendingDemandes = computed(() => demandesStore.demandesEnAttente);
const pendingDemandesCount = computed(() => pendingDemandes.value.length);

const userInitials = computed(() => {
  if (currentUser.value) {
    const { prenom, name } = currentUser.value;
    return `${prenom.charAt(0)}${name.charAt(0)}`.toUpperCase();
  }
  return "?";
});

const handleLogout = () => {
  usersStore.logout();
  window.location.pathname = "/";
};
</script>

<style scoped>
.white--text {
  color: white !important;
}
</style>
