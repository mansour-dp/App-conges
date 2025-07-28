<template>
  <v-dialog v-model="dialog" max-width="500px" persistent>
    <v-card>
      <v-card-title class="delete-modal-title">
        <v-icon icon="mdi-alert-circle" class="warning-icon"></v-icon>
        <span class="headline">Confirmer la suppression</span>
      </v-card-title>

      <v-card-text class="delete-modal-content">
        <div class="warning-section">
          <v-icon icon="mdi-account" class="user-icon"></v-icon>
          <div class="user-details">
            <p class="user-name">{{ user?.first_name }} {{ user?.name }}</p>
            <p class="user-email" v-if="user?.email">{{ user.email }}</p>
            <p class="user-role" v-if="user?.role">{{ user.role.nom || user.role.name }}</p>
            <p class="user-department" v-if="user?.department">{{ user.department.name }}</p>
          </div>
        </div>

        <v-alert
          type="warning"
          variant="tonal"
          class="warning-alert"
          border="start"
        >
          <template #text>
            <strong>Attention !</strong> Cette action est irréversible.
            <br>
            L'utilisateur sera définitivement supprimé du système.
          </template>
        </v-alert>

        <v-alert
          type="error"
          variant="tonal"
          border="start"
          class="danger-alert"
        >
          <template #text>
            <strong>Suppression des données associées :</strong>
            <br>
            • Toutes les demandes de congés de cet utilisateur
            <br>
            • L'historique des validations effectuées
            <br>
            • Les notifications liées à cet utilisateur
          </template>
        </v-alert>

        <p class="confirmation-text">
          Êtes-vous absolument sûr de vouloir supprimer cet utilisateur ?
        </p>
      </v-card-text>

      <v-card-actions class="delete-modal-actions">
        <v-btn
          variant="outlined"
          @click="closeModal"
          :disabled="loading"
        >
          Annuler
        </v-btn>
        <v-btn
          color="error"
          variant="elevated"
          :loading="loading"
          @click="confirmDelete"
        >
          <v-icon icon="mdi-delete" start></v-icon>
          Supprimer définitivement
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "UserDeleteModal",
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    user: {
      type: Object,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue', 'confirm'],
  computed: {
    dialog: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      }
    }
  },
  methods: {
    confirmDelete() {
      this.$emit('confirm', this.user);
    },
    
    closeModal() {
      this.dialog = false;
    }
  }
}
</script>

<style scoped>
.delete-modal-title {
  background: white;
  color: #374151;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 12px;
  border-bottom: 1px solid #e0e0e0;
}

.warning-icon {
  font-size: 24px !important;
  color: #f59e0b;
}

.headline {
  font-size: 1.25rem;
  font-weight: 600;
}

.delete-modal-content {
  padding: 24px !important;
}

.warning-section {
  display: flex;
  align-items: flex-start;
  gap: 16px;
  padding: 16px;
  background: #fff5f5;
  border-radius: 8px;
  margin-bottom: 20px;
  border-left: 4px solid #ff6b6b;
}

.user-icon {
  color: #ff6b6b;
  font-size: 40px !important;
  margin-top: 4px;
}

.user-details {
  flex: 1;
}

.user-name {
  font-size: 1.2rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #2c3e50;
}

.user-email {
  margin: 0 0 4px 0;
  color: #6c757d;
  font-size: 0.9rem;
}

.user-role {
  margin: 0 0 4px 0;
  color: #495057;
  font-size: 0.85rem;
  font-weight: 500;
}

.user-department {
  margin: 0;
  color: #6c757d;
  font-size: 0.85rem;
}

.warning-alert {
  margin-bottom: 16px;
}

.danger-alert {
  margin-bottom: 20px;
}

.confirmation-text {
  font-weight: 600;
  color: #495057;
  margin: 16px 0 0 0;
  text-align: center;
  font-size: 1rem;
}

.delete-modal-actions {
  padding: 16px 24px 20px 24px;
  border-top: 1px solid #e0e0e0;
  gap: 12px;
}

.delete-modal-actions .v-btn {
  min-width: 140px;
  height: 40px;
  text-transform: none;
  font-weight: 500;
}

/* Animation pour l'ouverture */
:deep(.v-dialog > .v-overlay__content) {
  animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-30px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
