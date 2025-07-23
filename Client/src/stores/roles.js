import { reactive } from 'vue';

const state = reactive({
  roles: ['admin', 'employe', 'superieur', 'directeur', 'responsableRH', 'directeurRH', 'directeurUnite']
});

export default {
  namespaced: true,
  state,
  getters: {
    roles: state => state.roles
  }
};
