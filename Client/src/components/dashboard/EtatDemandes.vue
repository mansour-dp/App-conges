<template>
  <div class="etat-demandes">
    <div class="filter-bar">
      <div class="filter-group">
        <label for="statusFilter"> <i class="fas fa-filter"></i> Statut </label>
        <select id="statusFilter" v-model="filters.status">
          <option value="all">Tous</option>
          <option value="en_attente">En attente</option>
          <option value="approuve">Approuvé</option>
          <option value="rejete">Rejeté</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="typeFilter"> <i class="fas fa-tags"></i> Type </label>
        <select id="typeFilter" v-model="filters.type">
          <option value="all">Tous</option>
          <option value="annuel">Congé annuel</option>
          <option value="fractionnes">Congés fractionnés</option>
          <option value="autres_legaux">Autres congés légaux</option>
        </select>
      </div>

      <div class="filter-group">
        <label for="yearFilter">
          <i class="fas fa-calendar-alt"></i> Année
        </label>
        <select id="yearFilter" v-model="filters.year">
          <option value="all">Toutes</option>
          <option value="2025">2025</option>
          <option value="2024">2024</option>
          <option value="2023">2023</option>
        </select>
      </div>
    </div>

    <div class="demandes-list">
      <div
        v-for="demande in filteredDemandes"
        :key="demande.id"
        class="demande-card"
        :class="statusClass(demande.status)"
      >
        <div class="demande-header">
          <div class="demande-title">
            <h3>{{ demande.type }}</h3>
            <span class="demande-status">{{
              formatStatus(demande.status)
            }}</span>
          </div>
          <div class="demande-actions">
            <button
              v-if="demande.status === 'en_attente'"
              class="btn-icon"
              @click="annulerDemande(demande)"
            >
              <i class="fas fa-times"></i>
            </button>
            <button class="btn-icon" @click="voirDetails(demande)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>
        <div class="demande-info">
          <div class="info-row">
            <div class="info-label">Période</div>
            <div class="info-value">
              {{ demande.dateDebut }} - {{ demande.dateFin }}
            </div>
          </div>
          <div class="info-row">
            <div class="info-label">Durée</div>
            <div class="info-value">{{ demande.duree }} jours</div>
          </div>
          <div class="info-row">
            <div class="info-label">Étape actuelle</div>
            <div class="info-value">{{ demande.etapeActuelle }}</div>
          </div>
        </div>
        <div class="demande-footer">
          <div class="demande-date">Soumis le {{ demande.dateSoumission }}</div>
          <div v-if="demande.motifRejet" class="demande-rejet">
            Motif: {{ demande.motifRejet }}
          </div>
        </div>
      </div>

      <div v-if="filteredDemandes.length === 0" class="empty-state">
        <i class="fas fa-search"></i>
        <p>Aucune demande ne correspond à vos critères de recherche</p>
      </div>
    </div>
  </div>
</template>

<script>
import { useNotificationsStore } from '@/stores/notifications'

export default {
  name: "EtatDemandes",
  setup() {
    const notificationsStore = useNotificationsStore()
    return { notificationsStore }
  },
  data() {
    return {
      filters: {
        status: "all",
        type: "all",
        year: "all",
      },
      demandes: [
        {
          id: 1,
          type: "Congé annuel",
          dateDebut: "15/08/2023",
          dateFin: "25/08/2023",
          duree: 11,
          status: "en_attente",
          etapeActuelle: "Approbation Supérieur Hiérarchique",
          dateSoumission: "01/08/2023",
        },
        {
          id: 2,
          type: "Congé fractionnés",
          dateDebut: "05/06/2023",
          dateFin: "12/06/2023",
          duree: 8,
          status: "approuve",
          etapeActuelle: "Approuvé",
          dateSoumission: "04/06/2023",
        },
        {
          id: 3,
          type: "Autres congés légaux",
          dateDebut: "10/04/2023",
          dateFin: "15/04/2023",
          duree: 6,
          status: "rejete",
          etapeActuelle: "Rejeté",
          dateSoumission: "01/04/2023",
          motifRejet: "Demande hors délai règlementaire",
        },
      ],
    };
  },
  computed: {
    filteredDemandes() {
      return this.demandes.filter((demande) => {
        if (
          this.filters.status !== "all" &&
          demande.status !== this.filters.status
        )
          return false;
        if (
          this.filters.type !== "all" &&
          demande.type !== this.convertTypeFilter(this.filters.type)
        )
          return false;
        if (
          this.filters.year !== "all" &&
          !this.isDemandeInYear(demande, this.filters.year)
        )
          return false;
        return true;
      });
    },
  },
  methods: {
    convertTypeFilter(type) {
      const typeMap = {
        annuel: "Congé annuel",
        fractionnes: "Congé fractionnés",
        autres_legaux: "Autres congés légaux",
      };
      return typeMap[type] || "";
    },
    statusClass(status) {
      return {
        "status-pending": status === "en_attente",
        "status-approved": status === "approuve",
        "status-rejected": status === "rejete",
      };
    },
    formatStatus(status) {
      const statusMap = {
        en_attente: "En attente",
        approuve: "Approuvé",
        rejete: "Rejeté",
      };
      return statusMap[status] || status;
    },
    annulerDemande(demande) {
      if (
        confirm(
          `Êtes-vous sûr de vouloir annuler cette demande de ${demande.type}?`
        )
      ) {
        // Simule l'annulation
        demande.status = 'annule';
        
        // Notification d'annulation
        this.notificationsStore.notifyDemandeCancelled('Votre demande');
        
        console.log("Demande annulée:", demande.id);
      }
    },
    voirDetails(demande) {
      console.log("Voir détails de la demande:", demande.id);
      // Ouvrir modal ou naviguer vers page détaillée
    },
    isDemandeInYear(demande, year) {
      // Extraire l'année de la date de début (format DD/MM/YYYY)
      const dateParts = demande.dateDebut.split("/");
      if (dateParts.length === 3) {
        const demandeYear = parseInt(dateParts[2]);
        return demandeYear === parseInt(year);
      }
      return false;
    },
  },
};
</script>

<style scoped>
.etat-demandes {
  background-color: white;
  border-radius: 12px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  padding: 25px;
}

.filter-bar {
  display: flex;
  gap: 20px;
  margin-bottom: 25px;
  padding: 20px;
  border-bottom: 2px solid #f0f0f0;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border-radius: 12px;
  position: relative;
}

.filter-group {
  display: flex;
  flex-direction: column;
  min-width: 150px;
  position: relative;
}

.filter-group label {
  font-size: 13px;
  margin-bottom: 8px;
  color: #374151;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 6px;
}

.filter-group label i {
  color: #261555 !important;
  font-size: 14px;
}

.filter-group select {
  padding: 12px 16px;
  border: 2px solid #d1d5db;
  border-radius: 8px;
  font-size: 14px;
  background-color: white;
  color: #374151;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
}

.filter-group select:hover {
  border-color: #9ca3af;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.filter-group select:focus {
  border-color: #008a9b;
  outline: none;
  box-shadow: 0 0 0 3px rgba(0, 138, 155, 0.1);
}

.filter-group::after {
  content: "▼";
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  color: #6b7280;
  pointer-events: none;
  font-size: 12px;
}

.demandes-list {
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.demande-card {
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  border-left: 4px solid #ddd;
}

.demande-card.status-pending {
  border-left-color: #b10064;
  background-color: rgba(177, 0, 100, 0.05);
}

.demande-card.status-approved {
  border-left-color: #008a9b;
  background-color: rgba(0, 138, 155, 0.05);
}

.demande-card.status-rejected {
  border-left-color: #261555;
  background-color: rgba(38, 21, 85, 0.05);
}

.demande-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 15px;
}

.demande-title {
  display: flex;
  align-items: center;
}

.demande-title h3 {
  font-size: 16px;
  color: #261555;
  margin: 0;
  margin-right: 12px;
}

.demande-status {
  font-size: 12px;
  padding: 4px 8px;
  border-radius: 4px;
  background-color: #f0f0f0;
  color: #666;
}

.status-pending .demande-status {
  background-color: rgba(177, 0, 100, 0.15);
  color: #b10064;
  border: 1px solid rgba(177, 0, 100, 0.3);
}

.status-approved .demande-status {
  background-color: rgba(0, 138, 155, 0.15);
  color: #008a9b;
  border: 1px solid rgba(0, 138, 155, 0.3);
}

.status-rejected .demande-status {
  background-color: rgba(38, 21, 85, 0.15);
  color: #261555;
  border: 1px solid rgba(38, 21, 85, 0.3);
}

.demande-actions {
  display: flex;
  gap: 10px;
}

.btn-icon {
  width: 32px;
  height: 32px;
  border-radius: 6px;
  background-color: #261555;
  border: none;
  color: #fff;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-icon i {
  color: #fff !important;
}

.btn-icon:hover {
  background-color: #008a9b;
  color: #fff;
}

.demande-info {
  margin-bottom: 15px;
}

.info-row {
  display: flex;
  margin-bottom: 8px;
}

.info-label {
  width: 120px;
  color: #777;
  font-size: 14px;
}

.info-value {
  font-weight: 500;
  font-size: 14px;
}

.demande-footer {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #888;
  padding-top: 12px;
  border-top: 1px solid #eee;
}

.demande-rejet {
  color: #c62828;
  font-style: italic;
}

.empty-state {
  padding: 40px 20px;
  text-align: center;
  color: #888;
}

.empty-state i {
  font-size: 40px;
  margin-bottom: 15px;
  opacity: 0.5;
  color: #261555 !important;
}

@media (max-width: 768px) {
  .filter-bar {
    flex-direction: column;
    gap: 15px;
  }

  .filter-group {
    width: 100%;
  }
}
</style>
