<template>
  <div class="gestion-demandes">
    <div class="content-wrapper">
      <!-- Section des cartes -->
      <div class="cards-section">
        <div class="cards-grid">
          <!-- Carte pour les congés annuels -->
          <div
            class="demand-card"
            @click="navigateToComponent('planification')"
          >
            <div class="card-icon">
              <i class="fas fa-umbrella-beach"></i>
            </div>
            <div class="card-content">
              <h3>Demande de Congés</h3>
              <p>
                Planifiez vos congés annuels et consultez votre solde disponible
              </p>
            </div>
            <div class="card-arrow">
              <i class="fas fa-chevron-right"></i>
            </div>
          </div>

          <!-- Carte pour les reports de congés -->
          <div class="demand-card" @click="navigateToComponent('report')">
            <div class="card-icon">
              <i class="fas fa-clock"></i>
            </div>
            <div class="card-content">
              <h3>Report de Congés</h3>
              <p>Demandez le report de vos jours de congés non utilisés</p>
            </div>
            <div class="card-arrow">
              <i class="fas fa-chevron-right"></i>
            </div>
          </div>

          <!-- Carte pour les absences -->
          <div class="demand-card" @click="navigateToComponent('absence')">
            <div class="card-icon">
              <i class="fas fa-user-clock"></i>
            </div>
            <div class="card-content">
              <h3>Demande d'Absence</h3>
              <p>Déclarez une absence exceptionnelle ou une permission</p>
            </div>
            <div class="card-arrow">
              <i class="fas fa-chevron-right"></i>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "GestionDemandesView",
  computed: {
    rolePrefix() {
      const path = this.$route.path;
      if (path.startsWith('/superieur')) return 'superieur';
      if (path.startsWith('/directeur-unite')) return 'directeurUnite';
      if (path.startsWith('/responsable-rh')) return 'responsableRH';
      if (path.startsWith('/directeur-rh')) return 'directeurRH';
      return '';
    }
  },
  methods: {
    navigateToComponent(type) {
      const currentPath = this.$route.path;
      let prefix = '';
      
      // Déterminer le préfixe de route en fonction du chemin actuel
      if (currentPath.includes('/employe')) prefix = '/employe';
      else if (currentPath.includes('/superieur')) prefix = '/superieur';
      else if (currentPath.includes('/directeur-unite')) prefix = '/directeur-unite';
      else if (currentPath.includes('/responsable-rh')) prefix = '/responsable-rh';
      else if (currentPath.includes('/directeur-rh')) prefix = '/directeur-rh';
      
      // Construire le chemin de destination
      let path = '';
      switch (type) {
        case "planification":
          path = `${prefix}/demande-conges`;
          break;
        case "report":
          path = `${prefix}/demande-report`;
          break;
        case "absence":
          path = `${prefix}/demande-absence`;
          break;
      }
      
      // Naviguer vers la nouvelle route
      if (path) {
        this.$router.push(path).catch(err => {
          if (err.name !== 'NavigationDuplicated') {
            throw err;
          }
        });
      }
    }
  }
};
</script>

<style scoped>
.gestion-demandes {
  min-height: 100vh;
  background-color: #f8fafc;
  font-family: "Inter", sans-serif;
  position: relative;
}

.content-wrapper {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

/* Page Header */
.page-header {
  text-align: center;
  margin-bottom: 3rem;
  padding: 2rem;
  background-color: #f8fafc;
  border-radius: 16px;
  border-left: 4px solid #008a9b;
}

.page-header h1 {
  color: #261555;
  font-size: 2.5rem;
  font-weight: 700;
  margin: 0 0 0.5rem 0;
}

.page-header p {
  color: #64748b;
  font-size: 1.1rem;
  margin: 0;
}

/* Cards Grid */
.cards-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 2rem;
  padding: 1rem;
}

/* Demand Card */
.demand-card {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  display: flex;
  align-items: center;
  gap: 1.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: 1px solid #e2e8f0;
  box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1),
    0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.demand-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1),
    0 4px 6px -2px rgba(0, 0, 0, 0.05);
  border-color: #008a9b;
}

.card-icon {
  width: 60px;
  height: 60px;
  background: #f0f9ff;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.card-icon i {
  font-size: 24px;
  color: #008a9b;
}

.card-content {
  flex: 1;
}

.card-content h3 {
  color: #261555;
  font-size: 1.25rem;
  font-weight: 600;
  margin: 0 0 0.5rem 0;
}

.card-content p {
  color: #64748b;
  font-size: 0.95rem;
  margin: 0;
  line-height: 1.5;
}

.card-arrow {
  color: #008a9b;
  font-size: 1.25rem;
  opacity: 0;
  transition: all 0.3s ease;
}

.demand-card:hover .card-arrow {
  opacity: 1;
  transform: translateX(4px);
}

@media (max-width: 768px) {
  .content-wrapper {
    padding: 1rem;
  }

  .page-header {
    padding: 1.5rem;
    margin-bottom: 2rem;
  }

  .page-header h1 {
    font-size: 2rem;
  }

  .cards-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }

  .demand-card {
    padding: 1.5rem;
  }
}
</style>
