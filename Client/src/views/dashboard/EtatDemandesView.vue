<template>
  <div class="etat-demandes">
    <EtatDemandes />
  </div>
</template>

<script>
import EtatDemandes from "@/components/dashboard/EtatDemandes.vue";

export default {
  name: "EtatDemandesView",
  components: {
    EtatDemandes,
  },
};
</script>

<style scoped>
.etat-demandes {
  padding: 2rem;
  min-height: 100vh;
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  font-family: "Inter", sans-serif;
}
</style>
