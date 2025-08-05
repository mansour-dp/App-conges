<template>
  <div class="directeur-unite-dashboard">
    <SidebarSuperieur
      :is-open="isSidebarOpen"
      @toggle-sidebar="toggleSidebar"
      role="Directeur d'Unité"
    />

    <div
      class="dashboard-content"
      :class="{ 'sidebar-closed': !isSidebarOpen }"
    >
      <DashboardToolbar
        :page-title="currentPageTitle"
        :notification-count="3"
        :sidebar-open="isSidebarOpen"
        @toggle-sidebar="toggleSidebar"
      />
      <div class="content-container">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </div>
  </div>
</template>

<script>
import SidebarSuperieur from "@/components/dashboard/SidebarSuperieur.vue";
import DashboardToolbar from "@/components/dashboard/DashboardToolbar.vue";

export default {
  name: "DirecteurUniteDashboard",
  components: {
    SidebarSuperieur,
    DashboardToolbar,
  },
  data() {
    return {
      isSidebarOpen: true,
    };
  },
  methods: {
    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    },
  },
  computed: {
    currentPageTitle() {
      const routePath = this.$route.path;

      if (routePath.includes("/gestion-demandes"))
        return "Gestion des demandes";
      if (routePath.includes("/planification"))
        return "Planification des congés";
      if (routePath.includes("/etat-demandes")) return "État des demandes";
      if (routePath.includes("/solde")) return "Solde de congés";
      if (routePath.includes("/historique")) return "Historique des congés";
      if (routePath.includes("/validation-demandes"))
        return "Validation des demandes";
      if (routePath.includes("/demandes-en-attente"))
        return "Liste des demandes";
      if (routePath.includes("/settings")) return "Paramètres";

      return "Tableau de bord";
    },
    shouldShowHeader() {
      const routePath = this.$route.path;

      if (routePath.includes("/gestion-demandes")) {
        const hasSelectedForm =
          this.$route.query.form === "planification" ||
          this.$route.query.form === "report" ||
          this.$route.query.form === "absence";
        return !hasSelectedForm;
      }

      return true;
    },
  },
};
</script>

<style scoped>
.directeur-unite-dashboard {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}



.dashboard-content {
  flex: 1;
  padding: 25px;
  margin-left: 250px;
  transition: margin-left 0.3s ease;
}

.dashboard-content.sidebar-closed {
  margin-left: 0;
}

.content-container {
  padding: 15px;
  position: relative;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(20px);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-20px);
}

@media (max-width: 1024px) {
  .dashboard-content {
    margin-left: 200px;
    padding: 20px;
  }
}

@media (max-width: 768px) {
  .dashboard-content {
    margin-left: 0;
    padding: 15px;
  }

  .dashboard-content.sidebar-closed {
    margin-left: 0;
  }

  .content-container {
    padding: 10px;
  }
}
</style> 
