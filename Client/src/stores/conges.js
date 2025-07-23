import { defineStore } from "pinia";

// Constantes de style
export const STYLE_CONSTANTS = {
  colors: {
    primary: {
      light: "#e6f7ff",
      main: "#008a9b",
      dark: "#006d7a",
    },
    warning: {
      light: "#fff7e6",
      main: "#d97706",
      dark: "#b45309",
    },
    success: {
      light: "#f0fdf4",
      main: "#16a34a",
      dark: "#15803d",
    },
    error: {
      light: "#fef2f2",
      main: "#dc2626",
      dark: "#b91c1c",
    },
    info: {
      light: "#e6f7ff",
      main: "#0284c7",
      dark: "#0369a1",
    },
    neutral: {
      light: "#f8fafc",
      main: "#64748b",
      dark: "#475569",
    },
  },
  gradients: {
    primary: "linear-gradient(135deg, #008a9b, #00b4d8)",
    warning: "linear-gradient(135deg, #d97706, #f59e0b)",
    success: "linear-gradient(135deg, #16a34a, #22c55e)",
    error: "linear-gradient(135deg, #dc2626, #ef4444)",
    info: "linear-gradient(135deg, #0284c7, #0ea5e9)",
  },
};

export const useCongesStore = defineStore("conges", {
  state: () => ({
    // Données pour le dashboard - basées sur SoldeConges
    stats: {
      congesRestants: 27, // Total des restes (13 + 7 + 7)
      demandesEnCours: 1, // 1 demande en attente dans EtatDemandes
      demandesApprouvees: 1, // 1 demande approuvée dans EtatDemandes
    },

    // Prochains congés - basés sur EtatDemandes et HistoriqueConges
    prochainsConges: [
      {
        id: 1,
        dateDebut: "15/08/2023",
        dateFin: "25/08/2023",
        type: "Congé annuel",
        statut: "En attente",
      },
      {
        id: 2,
        dateDebut: "15/06/2025",
        dateFin: "30/06/2025",
        type: "Congé annuel",
        statut: "Approuvé",
      },
    ],

    // Données pour l'état des demandes - basées sur EtatDemandes
    demandesEnCours: [
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

    // Données pour l'historique - basées sur HistoriqueConges
    historiqueConges: [
      {
        id: 1,
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
        id: 2,
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
        id: 3,
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
        id: 4,
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
        id: 5,
        dateDebut: "01/08/2023",
        dateFin: "15/08/2023",
        type: "Congé annuel",
        typeClass: "annuel",
        duree: 15,
        statut: "Approuvé",
        statutClass: "approuve",
        dateDemande: "01/07/2023",
      },
    ],

    // Données pour le solde - basées sur SoldeConges
    soldeConges: {
      congesAnnuel: {
        acquis: 25,
        pris: 12,
        reste: 13,
        pourcentage: 48,
      },
      congesFractionnes: {
        acquis: 15,
        pris: 8,
        reste: 7,
        pourcentage: 53,
      },
      autresConges: {
        acquis: 10,
        pris: 3,
        reste: 7,
        pourcentage: 30,
      },
      congesPlanifies: 5,
    },

    loading: false,
    error: null,
  }),

  getters: {
    // Getters pour le dashboard
    getCongesRestants: (state) => state.stats.congesRestants,
    getDemandesEnCours: (state) => state.stats.demandesEnCours,
    getDemandesApprouvees: (state) => state.stats.demandesApprouvees,
    getProchainsConges: (state) => state.prochainsConges,

    // Getters pour l'état des demandes
    getDemandesEnCoursList: (state) => state.demandesEnCours,

    // Getters pour l'historique
    getHistoriqueConges: (state) => state.historiqueConges,

    // Getters pour le solde
    getSoldeConges: (state) => state.soldeConges,

    // Getters calculés pour la cohérence
    getTotalAcquis: (state) => {
      return (
        state.soldeConges.congesAnnuel.acquis +
        state.soldeConges.congesFractionnes.acquis +
        state.soldeConges.autresConges.acquis
      );
    },
    getTotalPris: (state) => {
      return (
        state.soldeConges.congesAnnuel.pris +
        state.soldeConges.congesFractionnes.pris +
        state.soldeConges.autresConges.pris
      );
    },
    getSoldeDisponible: (state) => {
      return (
        state.soldeConges.congesAnnuel.reste +
        state.soldeConges.congesFractionnes.reste +
        state.soldeConges.autresConges.reste -
        state.soldeConges.congesPlanifies
      );
    },
  },

  actions: {
    async fetchStats() {
      this.loading = true;
      try {
        // Mise à jour des stats basées sur les données cohérentes
        this.stats = {
          congesRestants: this.getSoldeDisponible,
          demandesEnCours: this.demandesEnCours.filter(
            (d) => d.status === "en_attente"
          ).length,
          demandesApprouvees: this.demandesEnCours.filter(
            (d) => d.status === "approuve"
          ).length,
        };
      } catch (error) {
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },

    async fetchProchainsConges() {
      this.loading = true;
      try {
        // Prochains congés basés sur les demandes en cours et l'historique récent
        this.prochainsConges = [
          ...this.demandesEnCours
            .filter((d) => d.status === "en_attente")
            .slice(0, 2),
          ...this.historiqueConges
            .filter((c) => c.statut === "Approuvé")
            .slice(0, 2),
        ].map((item, index) => ({
          id: index + 1,
          dateDebut: item.dateDebut,
          dateFin: item.dateFin,
          type: item.type,
          statut: item.status || item.statut,
        }));
      } catch (error) {
        this.error = error.message;
      } finally {
        this.loading = false;
      }
    },
  },
});
