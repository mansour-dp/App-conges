<template>
  <v-navigation-drawer
    v-model="drawer"
    :rail="isRailMode"
    permanent
    @click="expandSidebar"
    app
    color="primary"
  >
    <!-- Section profil utilisateur -->
    <div class="user-profile-section">
      <div class="logo-container">
        <img
          src="@/assets/images/logo-senelec.png"
          alt="Logo SENELEC"
          class="senelec-logo"
        />
      </div>
      <div v-if="!isRailMode" class="user-info">
        <div class="user-details">
          <span class="user-name">{{ userName }}</span>
          <div class="user-role-container">
            <div class="connection-indicator"></div>
            <span class="user-role">Administrateur</span>
          </div>
        </div>
      </div>
    </div>

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
import { ref, computed } from "vue";
import { useUserStore } from '@/stores/users';

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false, // Fermée par défaut
  },
});

const drawer = ref(true);
const userStore = useUserStore();

// Contrôler le mode rail basé sur la prop isOpen
const isRailMode = computed(() => !props.isOpen);

// Fonction pour étendre temporairement la sidebar quand on clique dessus
const expandSidebar = () => {
  if (isRailMode.value) {
    // Ne rien faire, laisser le contrôle au parent via la prop
    return;
  }
};

const userName = computed(() => {
  if (!userStore.user) return 'Admin Inconnu';
  
  // Support pour les deux formats de noms
  const firstName = userStore.user.first_name || userStore.user.prenom || '';
  const lastName = userStore.user.name || userStore.user.nom || '';
  return `${firstName} ${lastName}`.trim() || 'Admin Inconnu';
});

const userFunction = computed(() => {
  return userStore.user?.fonction || userStore.user?.position || 'Administrateur';
});

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
    icon: "mdi-text-box-multiple-outline",
    to: "/admin/logs",
  },
  { title: "Paramètres", icon: "mdi-cog", to: "/admin/settings" },
]);
</script>

<style scoped>
.white--text {
  color: white !important;
}

/* Styles pour la section profil utilisateur */
.user-profile-section {
  background-color: rgba(55, 71, 79, 0.1);
  padding: 20px 0;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  text-align: center;
}

.logo-container {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 15px;
  padding: 0 20px;
}

.senelec-logo {
  width: 80px;
  height: auto;
  object-fit: contain;
  transition: all 0.3s ease;
}

.senelec-logo:hover {
  transform: scale(1.05);
  filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.3));
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 0 20px;
}

.user-details {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
}

.user-name {
  font-weight: 600;
  margin-bottom: 4px;
  font-size: 15px;
  color: rgba(255, 255, 255, 0.95);
}

.user-function {
  font-size: 12px;
  color: rgba(255, 255, 255, 0.7);
  margin-bottom: 2px;
}

.user-role-container {
  display: flex;
  align-items: center;
}

.user-role {
  font-size: 11px;
  color: #ffd700;
  font-weight: 500;
  background: rgba(255, 215, 0, 0.1);
  padding: 2px 6px;
  border-radius: 4px;
  display: inline-block;
  width: fit-content;
  margin-top: 5px;
}

.connection-indicator {
  width: 10px;
  height: 10px;
  border-radius: 50%;
  background-color: #4caf50;
  margin-right: 5px;
  animation: blink 2s infinite;
  box-shadow: 0 0 10px rgba(76, 175, 80, 0.5);
}

@keyframes blink {
  0%,
  50% {
    opacity: 1;
    transform: scale(1);
  }
  25%,
  75% {
    opacity: 0.7;
    transform: scale(0.9);
  }
}
</style>
