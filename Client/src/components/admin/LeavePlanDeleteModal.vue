<template>
  <v-dialog v-model="dialog" max-width="500px" persistent>
    <v-card>
      <v-card-title class="delete-modal-title">
        <v-icon icon="mdi-alert-circle" class="warning-icon"></v-icon>
        <span class="headline">Confirmer la suppression</span>
      </v-card-title>

      <v-card-text class="delete-modal-content">
        <div class="warning-section">
          <v-icon icon="mdi-calendar-month" class="leave-plan-icon"></v-icon>
          <div class="leave-plan-details">
            <p class="leave-plan-name">{{ leavePlan?.name }}</p>
            <p class="leave-plan-period" v-if="leavePlan?.startDate && leavePlan?.endDate">
              {{ formatDate(leavePlan.startDate) }} - {{ formatDate(leavePlan.endDate) }}
            </p>
            <p class="leave-plan-duration" v-if="leavePlan?.startDate && leavePlan?.endDate">
              Durée : {{ calculateDuration() }} jour{{ calculateDuration() > 1 ? 's' : '' }}
            </p>
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
            La période de congés sera définitivement supprimée du système.
          </template>
        </v-alert>

        <v-alert
          type="error"
          variant="tonal"
          border="start"
          class="danger-alert"
        >
          <template #text>
            <strong>Impact de la suppression :</strong>
            <br>
            • Les demandes de congés liées à cette période pourraient être affectées
            <br>
            • La planification des congés sera mise à jour
            <br>
            • Cette action ne peut pas être annulée
          </template>
        </v-alert>

        <p class="confirmation-text">
          Êtes-vous sûr de vouloir supprimer cette période de congés ?
        </p>
      </v-card-text>

      <v-card-actions class="modal-actions">
        <v-spacer></v-spacer>
        <v-btn
          variant="text"
          @click="closeModal"
          :disabled="loading"
        >
          Annuler
        </v-btn>
        <v-btn
          color="error"
          @click="confirmDelete"
          :loading="loading"
          variant="elevated"
        >
          Supprimer définitivement
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  leavePlan: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'confirm']);

// Computed
const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

// Methods
const closeModal = () => {
  emit('update:modelValue', false);
};

const confirmDelete = () => {
  emit('confirm');
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR');
};

const calculateDuration = () => {
  if (!props.leavePlan?.startDate || !props.leavePlan?.endDate) return 0;
  const start = new Date(props.leavePlan.startDate);
  const end = new Date(props.leavePlan.endDate);
  const diffTime = Math.abs(end - start);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
};
</script>

<style scoped>
.delete-modal-title {
  background-color: #fef2f2;
  border-bottom: 1px solid #fecaca;
  padding: 20px 24px;
  display: flex;
  align-items: center;
  gap: 12px;
}

.warning-icon {
  color: #dc2626;
  font-size: 28px;
}

.headline {
  font-size: 20px;
  font-weight: 600;
  color: #991b1b;
}

.delete-modal-content {
  padding: 24px;
}

.warning-section {
  display: flex;
  align-items: flex-start;
  padding: 16px;
  background-color: #f0f9ff;
  border-radius: 8px;
  margin-bottom: 16px;
  border-left: 4px solid #0284c7;
}

.leave-plan-icon {
  font-size: 32px;
  color: #0284c7;
  margin-right: 16px;
  margin-top: 4px;
}

.leave-plan-details {
  flex: 1;
}

.leave-plan-name {
  font-size: 16px;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #1e293b;
}

.leave-plan-period {
  font-size: 14px;
  color: #64748b;
  margin: 0 0 4px 0;
}

.leave-plan-duration {
  font-size: 14px;
  color: #0284c7;
  margin: 0;
  font-weight: 500;
}

.warning-alert {
  margin-bottom: 16px;
  border-radius: 8px;
}

.danger-alert {
  margin-bottom: 16px;
  border-radius: 8px;
}

.confirmation-text {
  font-size: 16px;
  font-weight: 500;
  color: #374151;
  text-align: center;
  margin: 16px 0 0 0;
}

.modal-actions {
  padding: 16px 24px;
  background-color: #f8fafc;
  border-top: 1px solid #e2e8f0;
}
</style>
