<template>
  <div class="solde">
    <SoldeConges />
  </div>
</template>

<script>
import SoldeConges from "@/components/dashboard/SoldeConges.vue";

export default {
  name: "SoldeView",
  components: {
    SoldeConges,
  },
};
</script>

<style scoped>
.solde {
  padding: 2rem;
  min-height: 100vh;
  background: #f8fafc;
}
</style>
