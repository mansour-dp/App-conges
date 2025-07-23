<template>
  <v-app>
    <v-layout class="admin-dashboard-layout">
      <AdminSidebar />
      <AdminToolbar :title="currentTitle" />
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
