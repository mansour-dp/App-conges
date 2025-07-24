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

    <!-- User Menu remplacé par bouton déconnexion -->
    <v-btn
      icon
      color="primary"
      @click="handleLogout"
      title="Déconnexion"
      class="ml-2"
    >
      <v-icon>mdi-logout</v-icon>
    </v-btn>
  </v-app-bar>
</template>

<script setup>
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import { useUsersAdminStore } from "@/stores/usersAdmin";
import { useUserStore } from "@/stores/users";
import { useDemandesStore } from "@/stores/demandes";

defineProps({
  title: {
    type: String,
    default: "Administration",
  },
});

const router = useRouter();
const usersStore = useUsersAdminStore();
const userStore = useUserStore();
const demandesStore = useDemandesStore();

const currentUser = computed(() => userStore.user);
const pendingDemandes = computed(() => demandesStore.demandesEnAttente);
const pendingDemandesCount = computed(() => pendingDemandes.value.length);

const handleLogout = async () => {
  try {
    await userStore.logout();
    router.push('/');
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error);
    // Forcer la redirection même en cas d'erreur
    window.location.href = '/';
  }
};

// S'assurer que les données utilisateur sont chargées
onMounted(async () => {
  if (!currentUser.value && userStore.token) {
    await userStore.fetchUser();
  }
});
</script>

<style scoped>
.white--text {
  color: white !important;
}
</style>
