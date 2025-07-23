<template>
  <div class="historique-container">
    <div class="historique-header">
      <h2>Historique des congés</h2>
      <div class="filters">
        <div class="filter-item">
          <label for="anneeFilter">Année</label>
          <select
            id="anneeFilter"
            :value="filtreAnnee"
            @change="filtreAnnee = $event.target.value"
            class="filter-select"
          >
            <option value="all">Toutes les années</option>
            <option value="2025">2025</option>
            <option value="2024">2024</option>
            <option value="2023">2023</option>
            <option value="2022">2022</option>
            <option value="2021">2021</option>
          </select>
        </div>
        <div class="filter-item">
          <label for="typeFilter">Type de congé</label>
          <select
            id="typeFilter"
            :value="filtreType"
            @change="filtreType = $event.target.value"
            class="filter-select"
          >
            <option value="all">Tous types</option>
            <option value="annuel">Congé annuel</option>
            <option value="fractionnes">Congés fractionnés</option>
            <option value="autres_legaux">Autres congés légaux</option>
          </select>
        </div>
      </div>
    </div>

    <div class="historique-table-container">
      <table class="historique-table">
        <thead>
          <tr>
            <th>Période</th>
            <th>Type</th>
            <th>Durée</th>
            <th>Statut</th>
            <th>Date de demande</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(conge, index) in congesFiltres"
            :key="index"
            class="table-row"
          >
            <td>
              <div class="date-cell">
                <i class="fas fa-calendar"></i>
                <span>{{ conge.dateDebut }} - {{ conge.dateFin }}</span>
              </div>
            </td>
            <td>
              <span class="badge" :class="'badge-' + conge.typeClass">
                <i :class="getTypeIcon(conge.typeClass)"></i>
                {{ conge.type }}
              </span>
            </td>
            <td>
              <div class="duree-cell">
                <i class="fas fa-clock"></i>
                <span>{{ conge.duree }} jours</span>
              </div>
            </td>
            <td>
              <span class="status" :class="'status-' + conge.statutClass">
                <i :class="getStatusIcon(conge.statutClass)"></i>
                {{ conge.statut }}
              </span>
            </td>
            <td>
              <div class="date-cell">
                <i class="fas fa-calendar-check"></i>
                <span>{{ conge.dateDemande }}</span>
              </div>
            </td>
            <td>
              <div class="actions-cell">
                <button
                  class="btn-action"
                  @click="voirDetails(conge)"
                  title="Voir détails"
                >
                  <i class="fas fa-eye"></i>
                </button>
                <button
                  class="btn-action"
                  @click="telechargerAttestion(conge)"
                  v-if="conge.statutClass === 'approuve'"
                  title="Télécharger attestation"
                >
                  <i class="fas fa-download"></i>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <div v-if="congesFiltres.length === 0" class="empty-state">
        <div class="empty-icon">
          <i class="fas fa-history"></i>
        </div>
        <p>Aucun historique de congé ne correspond à vos critères</p>
      </div>
    </div>

    <div class="pagination">
      <button
        class="pagination-btn"
        :disabled="currentPage === 1"
        @click="currentPage--"
      >
        <i class="fas fa-chevron-left"></i>
      </button>
      <span class="pagination-info"
        >Page {{ currentPage }} sur {{ totalPages }}</span
      >
      <button
        class="pagination-btn"
        :disabled="currentPage === totalPages"
        @click="currentPage++"
      >
        <i class="fas fa-chevron-right"></i>
      </button>
    </div>
  </div>
</template>

<script>
export default {
  name: "HistoriqueConges",
  data() {
    return {
      filtreAnnee: "all",
      filtreType: "all",
      currentPage: 1,
      itemsPerPage: 5,
      conges: [
        {
          dateDebut: "15/06/2025",
          dateFin: "30/06/2025",
          type: "Congé annuel",
          typeClass: "annuel",
          duree: 16,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "01/05/2025",
        },
        {
          dateDebut: "10/03/2025",
          dateFin: "15/03/2025",
          type: "Congés fractionnés",
          typeClass: "fractionnes",
          duree: 6,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "09/03/2025",
        },
        {
          dateDebut: "05/01/2024",
          dateFin: "07/01/2024",
          type: "Autres congés légaux",
          typeClass: "autres_legaux",
          duree: 3,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "20/12/2023",
        },
        {
          dateDebut: "23/11/2024",
          dateFin: "25/11/2024",
          type: "Congés fractionnés",
          typeClass: "fractionnes",
          duree: 3,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "15/11/2024",
        },
        {
          dateDebut: "01/08/2023",
          dateFin: "15/08/2023",
          type: "Congé annuel",
          typeClass: "annuel",
          duree: 15,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "01/07/2023",
        },
        {
          dateDebut: "10/05/2023",
          dateFin: "12/05/2023",
          type: "Autres congés légaux",
          typeClass: "autres_legaux",
          duree: 3,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "10/05/2023",
        },
        {
          dateDebut: "05/12/2022",
          dateFin: "20/12/2022",
          type: "Congé annuel",
          typeClass: "annuel",
          duree: 16,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "01/11/2022",
        },
        {
          dateDebut: "15/03/2022",
          dateFin: "20/03/2022",
          type: "Congés fractionnés",
          typeClass: "fractionnes",
          duree: 6,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "10/03/2022",
        },
        {
          dateDebut: "20/07/2021",
          dateFin: "05/08/2021",
          type: "Congé annuel",
          typeClass: "annuel",
          duree: 17,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "15/06/2021",
        },
        {
          dateDebut: "10/12/2021",
          dateFin: "12/12/2021",
          type: "Autres congés légaux",
          typeClass: "autres_legaux",
          duree: 3,
          statut: "Approuvé",
          statutClass: "approuve",
          dateDemande: "05/12/2021",
        },
      ],
    };
  },
  computed: {
    congesFiltres() {
      let filtered = [...this.conges];

      if (this.filtreAnnee !== "all") {
        filtered = filtered.filter((conge) => {
          const dateDemande = conge.dateDemande.split("/");
          return dateDemande[2] === this.filtreAnnee;
        });
      }

      if (this.filtreType !== "all") {
        const typeMap = {
          annuel: "Congé annuel",
          fractionnes: "Congés fractionnés",
          autres_legaux: "Autres congés légaux",
        };
        filtered = filtered.filter(
          (conge) => conge.type === typeMap[this.filtreType]
        );
      }

      const start = (this.currentPage - 1) * this.itemsPerPage;
      const end = start + this.itemsPerPage;
      return filtered.slice(start, end);
    },
    totalPages() {
      let filtered = [...this.conges];

      if (this.filtreAnnee !== "all") {
        filtered = filtered.filter((conge) => {
          const dateDemande = conge.dateDemande.split("/");
          return dateDemande[2] === this.filtreAnnee;
        });
      }

      if (this.filtreType !== "all") {
        const typeMap = {
          annuel: "Congé annuel",
          fractionnes: "Congés fractionnés",
          autres_legaux: "Autres congés légaux",
        };
        filtered = filtered.filter(
          (conge) => conge.type === typeMap[this.filtreType]
        );
      }

      return Math.ceil(filtered.length / this.itemsPerPage);
    },
  },
  methods: {
    getTypeIcon(typeClass) {
      const icons = {
        annuel: "fas fa-umbrella-beach",
        fractionnes: "fas fa-calendar-week",
        autres_legaux: "fas fa-gavel",
      };
      return icons[typeClass] || "fas fa-calendar";
    },
    getStatusIcon(statutClass) {
      const icons = {
        approuve: "fas fa-check-circle",
        en_attente: "fas fa-clock",
        rejete: "fas fa-times-circle",
      };
      return icons[statutClass] || "fas fa-info-circle";
    },
    voirDetails(conge) {
      console.log("Voir détails du congé:", conge);
    },
    telechargerAttestion(conge) {
      console.log("Télécharger attestation pour:", conge);
      alert(
        `Attestation de congé pour la période du ${conge.dateDebut} au ${conge.dateFin} téléchargée.`
      );
    },
  },
  watch: {
    filtreAnnee() {
      this.currentPage = 1;
    },
    filtreType() {
      this.currentPage = 1;
    },
  },
};
</script>

<style scoped>
.historique-container {
  background-color: white;
  border-radius: 16px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  padding: 30px;
}

.historique-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
  flex-wrap: wrap;
  gap: 20px;
}

.historique-header h2 {
  color: #261555;
  font-size: 24px;
  font-weight: 700;
  margin: 0;
  background: none;
  -webkit-background-clip: initial;
  background-clip: initial;
}

.filters {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.filter-item {
  display: flex;
  flex-direction: column;
  min-width: 200px;
}

.filter-item label {
  font-size: 14px;
  color: #666;
  margin-bottom: 8px;
}

.filter-select {
  padding: 10px 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  background-color: #f8fafc;
  color: #261555;
  font-size: 14px;
  transition: all 0.3s ease;
}

.filter-select:focus {
  border-color: #008a9b;
  outline: none;
  box-shadow: 0 0 0 3px rgba(0, 138, 155, 0.1);
}

.historique-table-container {
  overflow-x: auto;
  margin-bottom: 30px;
}

.historique-table {
  width: 100%;
  border-collapse: separate;
  border-spacing: 0;
}

.historique-table th {
  background-color: #f8fafc;
  color: #261555;
  font-weight: 600;
  padding: 15px 12px;
  text-align: left;
  border-bottom: 2px solid #e0e0e0;
}

.historique-table td {
  padding: 16px;
  border-bottom: 1px solid #e0e0e0;
  color: #1a1a1a;
  font-size: 14px;
}

.table-row {
  transition: all 0.3s ease;
}

.table-row:hover {
  background-color: #f8fafc;
}

.date-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.date-cell i {
  color: #008a9b;
  font-size: 16px;
}

.duree-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}

.duree-cell i {
  color: #008a9b;
  font-size: 16px;
}

.badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.badge i {
  font-size: 14px;
}

.badge-annuel {
  background-color: rgba(0, 138, 155, 0.1);
  color: #008a9b;
}

.badge-fractionnes {
  background-color: rgba(177, 0, 100, 0.1);
  color: #b10064;
}

.badge-autres_legaux {
  background-color: rgba(38, 21, 85, 0.1);
  color: #261555;
}

.status {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 12px;
  font-weight: 600;
  display: inline-flex;
  align-items: center;
  gap: 6px;
}

.status i {
  font-size: 14px;
}

.status-approuve {
  background-color: rgba(0, 138, 155, 0.1);
  color: #008a9b;
  border: 1px solid rgba(0, 138, 155, 0.2);
}

.status-en_attente {
  background-color: rgba(177, 0, 100, 0.1);
  color: #b10064;
  border: 1px solid rgba(177, 0, 100, 0.2);
}

.status-rejete {
  background-color: rgba(38, 21, 85, 0.1);
  color: #261555;
  border: 1px solid rgba(38, 21, 85, 0.2);
}

.actions-cell {
  display: flex;
  gap: 8px;
}

.btn-action {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background-color: #f5f5f5;
  border: none;
  color: #666;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-action:hover {
  background-color: #008a9b;
  color: white;
}

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #666;
}

.empty-icon {
  font-size: 48px;
  color: #e0e0e0;
  margin-bottom: 16px;
}

.pagination {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 16px;
  margin-top: 30px;
}

.pagination-btn {
  padding: 8px 12px;
  border: 2px solid #008a9b;
  background: transparent;
  color: #008a9b;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.pagination-btn:hover:not(:disabled) {
  background-color: #008a9b;
  color: white;
}

.pagination-btn:disabled {
  border-color: #e0e0e0;
  color: #666;
  cursor: not-allowed;
}

.pagination-info {
  color: #666;
  font-size: 14px;
}

@media (max-width: 768px) {
  .historique-container {
    padding: 20px;
  }

  .historique-header {
    flex-direction: column;
    align-items: stretch;
  }

  .filters {
    flex-direction: column;
  }

  .filter-item {
    width: 100%;
  }

  .historique-table th,
  .historique-table td {
    padding: 12px;
  }

  .badge,
  .status {
    padding: 4px 8px;
    font-size: 12px;
  }
}
</style>
