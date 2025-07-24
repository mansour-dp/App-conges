<template>
  <v-app>
    <router-view />
    <NotificationSystem />
    <!-- PrimeVue Toast global -->
    <Toast position="bottom-right" />
  </v-app>
</template>

<script>
import NotificationSystem from '@/components/ui/NotificationSystem.vue'
import Toast from 'primevue/toast'

export default {
  name: "App",
  components: {
    NotificationSystem,
    Toast
  }
};
</script>

<style>
:root {
  /* Couleurs SENELEC */
  --primary-color: #008a9b; /* Bleu-vert */
  --secondary-color: #261555; /* Violet foncé */
  --tertiary-color: #100644; /* Bleu très foncé */
}

* {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body,
html {
  font-family: "Inter", "Roboto", Arial, sans-serif;
  font-size: 15px;
  height: 100%;
  width: 100%;
  margin: 0;
  padding: 0;
  overflow-x: hidden;
  background-color: #f5f5f5;
}

h1 {
  font-size: 2rem;
  font-weight: 700;
}
h2 {
  font-size: 1.5rem;
  font-weight: 600;
}
h3 {
  font-size: 1.2rem;
  font-weight: 600;
}
h4,
.card-title {
  font-size: 1rem;
  font-weight: 500;
}
.small,
.label,
.card-content p,
.sidebar .user-function {
  font-size: 0.9rem;
}

#app {
  min-height: 100vh;
  width: 100%;
  color: #333;
}

button {
  cursor: pointer;
}

input,
button {
  font-family: inherit;
}
</style>
