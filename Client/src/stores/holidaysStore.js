import { defineStore } from "pinia";

export const useHolidaysStore = defineStore("holidays", {
  state: () => ({
    holidays: [
      { id: 1, name: "Nouvel An", date: "2024-01-01" },
      { id: 2, name: "Fête de l'indépendance", date: "2024-04-04" },
      { id: 3, name: "Lundi de Pâques", date: "2024-04-01" },
      { id: 4, name: "Korité", date: "2024-04-10" },
    ],
  }),
  actions: {
    addHoliday(holiday) {
      this.holidays.push({ ...holiday, id: Date.now() });
    },
    updateHoliday(updatedHoliday) {
      const index = this.holidays.findIndex((h) => h.id === updatedHoliday.id);
      if (index !== -1) {
        this.holidays[index] = updatedHoliday;
      }
    },
    deleteHoliday(holidayId) {
      this.holidays = this.holidays.filter((h) => h.id !== holidayId);
    },
  },
});
