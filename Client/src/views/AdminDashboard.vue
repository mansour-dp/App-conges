<template>
  <v-app>
    <v-layout class="admin-dashboard-layout">
      <AdminSidebar :is-open="isSidebarOpen" />
      <AdminToolbar 
        :title="currentTitle" 
        @toggle-sidebar="toggleSidebar"
      />
      <v-main>
        <router-view />
      </v-main>
    </v-layout>
  </v-app>
</template>

<script>
import AdminSidebar from "@/components/admin/AdminSidebar.vue";
import AdminToolbar from "@/components/admin/AdminToolbar.vue";

export default {
  name: "AdminDashboard",
  components: { AdminSidebar, AdminToolbar },
  data() {
    return {
      isSidebarOpen: false, // Sidebar fermée par défaut
    };
  },
  methods: {
    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    },
  },
  computed: {
    currentTitle() {
      // Logic to determine title based on route
      const routeName = this.$route.meta.title || "Administration";
      return routeName;
    },
  },
};
</script>

<style scoped>
.admin-dashboard-layout {
  background-color: #f4f6f8;
}
</style>
