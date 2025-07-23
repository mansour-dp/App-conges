import { defineStore } from "pinia";

export const useLeavePlansStore = defineStore("leavePlans", {
  state: () => ({
    leavePlans: [
      {
        id: 1,
        name: "Congé Annuel 2024 - Tranche 1",
        startDate: "2024-06-01",
        endDate: "2024-07-31",
      },
      {
        id: 2,
        name: "Congé Annuel 2024 - Tranche 2",
        startDate: "2024-08-01",
        endDate: "2024-09-30",
      },
      {
        id: 3,
        name: "Pont de la Toussaint",
        startDate: "2024-11-02",
        endDate: "2024-11-05",
      },
    ],
  }),
  actions: {
    addPlan(plan) {
      this.leavePlans.push({ ...plan, id: Date.now() });
    },
    updatePlan(updatedPlan) {
      const index = this.leavePlans.findIndex((p) => p.id === updatedPlan.id);
      if (index !== -1) {
        this.leavePlans[index] = updatedPlan;
      }
    },
    deletePlan(planId) {
      this.leavePlans = this.leavePlans.filter((p) => p.id !== planId);
    },
  },
});
