<template>
  <v-dialog v-model="dialog" max-width="500px" persistent>
    <v-card>
      <v-card-title class="delete-modal-title">
        <v-icon icon="mdi-alert-circle" class="warning-icon"></v-icon>
        <span class="headline">Confirmer la suppression</span>
      </v-card-title>

      <v-card-text class="delete-modal-content">
        <div class="warning-section">
          <v-icon icon="mdi-office-building" class="department-icon"></v-icon>
          <div class="department-details">
            <p class="department-name">{{ department?.name }}</p>
            <p class="department-code" v-if="department?.code">Code: {{ department.code }}</p>
            <p class="department-description" v-if="department?.description">{{ department.description }}</p>
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
            Le département sera définitivement supprimé du système.
          </template>
        </v-alert>

        <div v-if="employeeCount > 0" class="employee-warning">
          <v-alert
            type="error"
            variant="tonal"
            border="start"
          >
            <template #text>
              <strong>Impossible de supprimer !</strong>
              <br>
              Ce département contient encore <strong>{{ employeeCount }} employé(s)</strong>.
              <br>
              Veuillez d'abord réassigner ou supprimer les employés de ce département.
            </template>
          </v-alert>
        </div>

        <p class="confirmation-text">
          Êtes-vous absolument sûr de vouloir supprimer ce département ?
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
          :disabled="employeeCount > 0"
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
  name: "DepartmentDeleteModal",
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    department: {
      type: Object,
      default: null
    },
    employeeCount: {
      type: Number,
      default: 0
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
      this.$emit('confirm', this.department);
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
  border: 1px solid #fed7d7;
}

.department-icon {
  color: #e53e3e;
  font-size: 32px !important;
  margin-top: 4px;
}

.department-details {
  flex: 1;
}

.department-name {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #2d3748;
}

.department-code {
  margin: 0 0 4px 0;
  color: #718096;
  font-size: 0.9rem;
  font-weight: 500;
}

.department-description {
  margin: 0;
  color: #4a5568;
  font-size: 0.9rem;
  line-height: 1.4;
}

.warning-alert {
  margin-bottom: 16px;
}

.employee-warning {
  margin-bottom: 20px;
}

.confirmation-text {
  font-size: 1rem;
  color: #2d3748;
  margin: 16px 0 0 0;
  text-align: center;
  font-weight: 500;
}

.delete-modal-actions {
  padding: 16px 24px;
  background: #fff5f5;
  border-top: 1px solid #fed7d7;
  gap: 12px;
}

.delete-modal-actions .v-btn {
  min-width: 120px;
  height: 40px;
}

/* Animations */
:deep(.v-dialog > .v-overlay__content) {
  animation: deleteModalSlideIn 0.3s ease-out;
}

@keyframes deleteModalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* Shake animation pour les erreurs */
.employee-warning {
  animation: shake 0.5s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}
</style>
