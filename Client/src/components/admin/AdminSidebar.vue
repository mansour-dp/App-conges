<template>
  <v-navigation-drawer
    v-model="drawer"
    :rail="rail"
    permanent
    @click="rail = false"
    app
    color="primary"
  >
    <v-list-item
      prepend-avatar="@/assets/images/logo-senelec.png"
      title="Admin Senelec"
      nav
      class="white--text"
    >
      <template v-slot:append>
        <v-btn
          variant="text"
          icon="mdi-chevron-left"
          @click.stop="rail = !rail"
        ></v-btn>
      </template>
    </v-list-item>

    <v-divider></v-divider>

    <v-list density="compact" nav>
      <v-list-item
        v-for="item in menuItems"
        :key="item.title"
        :prepend-icon="item.icon"
        :title="item.title"
        :to="item.to"
        link
      ></v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<script setup>
import { ref } from "vue";

const drawer = ref(true);
const rail = ref(false);

const menuItems = ref([
  {
    title: "Tableau de Bord",
    icon: "mdi-view-dashboard",
    to: "/admin/dashboard",
  },
  { title: "Utilisateurs", icon: "mdi-account-group", to: "/admin/users" },
  {
    title: "Départements",
    icon: "mdi-office-building",
    to: "/admin/departments",
  },
  {
    title: "Planification Congés",
    icon: "mdi-calendar-month",
    to: "/admin/planning",
  },
  {
    title: "Historique Demandes",
    icon: "mdi-history",
    to: "/admin/history",
  },
  {
    title: "Journaux d'Activité",
    icon: "mdi-post-it-note-text-outline",
    to: "/admin/logs",
  },
  { title: "Paramètres", icon: "mdi-cog", to: "/admin/settings" },
]);
</script>

<style scoped>
.white--text {
  color: white !important;
}
</style>
