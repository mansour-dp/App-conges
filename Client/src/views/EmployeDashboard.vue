<template>
  <div class="employe-dashboard">
    <SidebarEmploye :is-open="isSidebarOpen" @toggle-sidebar="toggleSidebar" />

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
import SidebarEmploye from "@/components/dashboard/SidebarEmploye.vue";
import DashboardToolbar from "@/components/dashboard/DashboardToolbar.vue";

export default {
  name: "EmployeDashboard",
  components: {
    SidebarEmploye,
    DashboardToolbar,
  },
  data() {
    return {
      isSidebarOpen: true, // Changer à true pour que la sidebar soit visible par défaut
    };
  },
  methods: {
    toggleSidebar() {
      this.isSidebarOpen = !this.isSidebarOpen;
    },
  },
  computed: {
    currentPageTitle() {
      // Obtenir le titre en fonction de la route actuelle
      const routePath = this.$route.path;

      if (routePath.includes("/gestion-demandes"))
        return "Gestion des demandes";
      if (routePath.includes("/planification"))
        return "Planification des congés";
      if (routePath.includes("/etat-demandes")) return "État des demandes";
      if (routePath.includes("/solde")) return "Solde de congés";
      if (routePath.includes("/historique")) return "Historique des congés";
      if (routePath.includes("/settings")) return "Paramètres";

      return "Tableau de bord"; // Par défaut
    },
    shouldShowHeader() {
      // Masquer le header quand on est sur la page de gestion des demandes
      // et qu'un formulaire spécifique est affiché
      const routePath = this.$route.path;

      // Si on est sur la page de gestion des demandes
      if (routePath.includes("/gestion-demandes")) {
        // Vérifier si un formulaire est sélectionné via les query params
        const hasSelectedForm =
          this.$route.query.form === "planification" ||
          this.$route.query.form === "report" ||
          this.$route.query.form === "absence";
        return !hasSelectedForm;
      }

      return true; // Afficher le header pour toutes les autres pages
    },
  },
};
</script>

<style scoped>
.employe-dashboard {
  display: flex;
  min-height: 100vh;
  background-color: #f8fafc;
}


.dashboard-content {
  flex: 1;
  padding: 25px;
  margin-left: 250px; /* Correspond à la largeur du sidebar */
  transition: margin-left 0.3s ease; /* Ajouter une transition pour le mouvement */
}

.dashboard-content.sidebar-closed {
  margin-left: 0; /* Lorsque la barre latérale est fermée, le contenu prend toute la largeur */
}

.content-container {
  padding: 15px;
  position: relative;
}

/* Transitions pour le router-view */
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

/* Media Queries */
@media (max-width: 1024px) {
  .dashboard-content {
    margin-left: 200px;
    padding: 20px;
  }
}

@media (max-width: 768px) {
  .dashboard-content {
    margin-left: 0; /* Sur mobile, le contenu est toujours à 0 margin-left */
    padding: 15px;
  }

  .dashboard-content.sidebar-closed {
    margin-left: 0; /* Assurez-vous que cela reste 0 sur mobile */
  }

  .content-container {
    padding: 10px;
  }

  /* Ces styles nécessiteraient un menu burger sur mobile */
  /* qui affiche/masque le sidebar */
}
</style>
