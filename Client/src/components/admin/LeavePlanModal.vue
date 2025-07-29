<template>
  <v-dialog v-model="dialog" max-width="600px" persistent>
    <v-card>
      <v-card-title class="modal-title">
        <span class="headline">{{ leavePlan && leavePlan.id ? 'Modifier la période de congés' : 'Nouvelle période de congés' }}</span>
        <v-spacer></v-spacer>
        <v-btn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="closeModal"
        ></v-btn>
      </v-card-title>

      <v-card-text>
        <!-- Info période pour édition -->
        <div v-if="leavePlan && leavePlan.id" class="leave-plan-info">
          <v-icon icon="mdi-calendar-month" class="leave-plan-icon"></v-icon>
          <div>
            <p class="leave-plan-name">{{ leavePlan.name }}</p>
            <p class="leave-plan-dates">{{ formatDate(leavePlan.startDate) }} - {{ formatDate(leavePlan.endDate) }}</p>
          </div>
        </div>

        <v-form ref="formRef" v-model="valid" @submit.prevent="handleSubmit">
          <div class="form-fields">
            <v-text-field
              v-model="form.name"
              label="Nom de la période"
              variant="outlined"
              density="comfortable"
              :rules="nameRules"
              hide-details="auto"
              prepend-inner-icon="mdi-text"
            />

            <v-text-field
              v-model="form.startDate"
              label="Date de début"
              type="date"
              variant="outlined"
              density="comfortable"
              :rules="startDateRules"
              hide-details="auto"
              prepend-inner-icon="mdi-calendar-start"
            />

            <v-text-field
              v-model="form.endDate"
              label="Date de fin"
              type="date"
              variant="outlined"
              density="comfortable"
              :rules="endDateRules"
              hide-details="auto"
              prepend-inner-icon="mdi-calendar-end"
            />

            <v-select
              v-model="form.leaveType"
              label="Type de congé"
              :items="leaveTypes"
              variant="outlined"
              density="comfortable"
              :rules="leaveTypeRules"
              hide-details="auto"
              prepend-inner-icon="mdi-briefcase"
            />

            <v-textarea
              v-model="form.description"
              label="Description (optionnelle)"
              variant="outlined"
              density="comfortable"
              rows="3"
              hide-details="auto"
              prepend-inner-icon="mdi-text-long"
            />
          </div>

          <!-- Durée calculée -->
          <v-alert
            v-if="form.startDate && form.endDate && calculateDuration() > 0"
            type="info"
            variant="tonal"
            class="mt-4"
          >
            <template #text>
              <strong>Durée de la période :</strong> {{ calculateDuration() }} jour{{ calculateDuration() > 1 ? 's' : '' }}
            </template>
          </v-alert>

          <!-- Alerte d'erreur de validation -->
          <v-alert
            v-if="form.startDate && form.endDate && new Date(form.startDate) > new Date(form.endDate)"
            type="error"
            variant="tonal"
            class="mt-4"
          >
            La date de fin doit être postérieure à la date de début.
          </v-alert>
        </v-form>
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
          color="primary"
          @click="handleSubmit"
          :loading="loading"
          :disabled="!valid || !canSubmit"
          variant="elevated"
        >
          {{ leavePlan && leavePlan.id ? 'Modifier' : 'Ajouter' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';

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

const emit = defineEmits(['update:modelValue', 'submit']);

// Form
const formRef = ref(null);
const valid = ref(false);
const form = ref({
  name: '',
  startDate: '',
  endDate: '',
  leaveType: '',
  description: ''
});

// Options pour les champs select
const leaveTypes = [
  { title: 'Congé annuel', value: 'conge_annuel' },
  { title: 'Congés fractionnés', value: 'conges_fractionnes' },
  { title: 'Autres congés légaux', value: 'autres_conges_legaux' },
  { title: 'Congé maladie', value: 'conge_maladie' },
  { title: 'Congé maternité', value: 'conge_maternite' },
  { title: 'Congé paternité', value: 'conge_paternite' },
  { title: 'Congé sans solde', value: 'conge_sans_solde' },
  { title: 'Absence exceptionnelle', value: 'absence_exceptionnelle' },
  { title: 'Report de congé', value: 'report_conge' }
];

// Computed
const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const canSubmit = computed(() => {
  return form.value.name && 
         form.value.startDate && 
         form.value.endDate &&
         form.value.leaveType &&
         new Date(form.value.startDate) <= new Date(form.value.endDate);
});

// Rules de validation
const nameRules = [
  v => !!v || 'Le nom de la période est obligatoire',
  v => (v && v.length >= 3) || 'Le nom doit contenir au moins 3 caractères',
  v => (v && v.length <= 100) || 'Le nom ne peut pas dépasser 100 caractères'
];

const startDateRules = [
  v => !!v || 'La date de début est obligatoire'
];

const endDateRules = [
  v => !!v || 'La date de fin est obligatoire',
  v => {
    if (!v || !form.value.startDate) return true;
    return new Date(v) >= new Date(form.value.startDate) || 'La date de fin doit être postérieure à la date de début';
  }
];

const leaveTypeRules = [
  v => !!v || 'Le type de congé est obligatoire'
];

// Methods
const resetForm = () => {
  form.value = {
    name: '',
    startDate: '',
    endDate: '',
    leaveType: '',
    description: ''
  };
  if (formRef.value) {
    formRef.value.resetValidation();
  }
};

const populateForm = () => {
  if (props.leavePlan) {
    form.value = {
      name: props.leavePlan.name || '',
      startDate: props.leavePlan.start_date || '',
      endDate: props.leavePlan.end_date || '',
      leaveType: props.leavePlan.leave_type || '',
      description: props.leavePlan.description || ''
    };
  } else {
    resetForm();
  }
};

const closeModal = () => {
  emit('update:modelValue', false);
  resetForm();
};

const handleSubmit = async () => {
  if (formRef.value) {
    const { valid: isValid } = await formRef.value.validate();
    if (isValid && canSubmit.value) {
      // Convertir les noms de champs pour l'API
      const formData = {
        name: form.value.name,
        start_date: form.value.startDate,
        end_date: form.value.endDate,
        leave_type: form.value.leaveType,
        description: form.value.description
      };
      
      // Ajouter l'ID si c'est une modification
      if (props.leavePlan && props.leavePlan.id) {
        formData.id = props.leavePlan.id;
      }
      
      emit('submit', formData);
    }
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR');
};

const calculateDuration = () => {
  if (!form.value.startDate || !form.value.endDate) return 0;
  const start = new Date(form.value.startDate);
  const end = new Date(form.value.endDate);
  if (start > end) return 0;
  const diffTime = Math.abs(end - start);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
};

// Watchers
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    nextTick(() => {
      populateForm();
    });
  }
});

watch(() => props.leavePlan, () => {
  if (props.modelValue) {
    populateForm();
  }
});
</script>

<style scoped>
.modal-title {
  background-color: #f8fafc;
  border-bottom: 1px solid #e2e8f0;
  padding: 20px 24px;
}

.leave-plan-info {
  display: flex;
  align-items: center;
  padding: 16px;
  background-color: #f0f9ff;
  border-radius: 8px;
  margin-bottom: 24px;
  border-left: 4px solid #0284c7;
}

.leave-plan-icon {
  font-size: 32px;
  color: #0284c7;
  margin-right: 16px;
}

.leave-plan-name {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: #1e293b;
}

.leave-plan-dates {
  font-size: 14px;
  color: #64748b;
  margin: 4px 0 0 0;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.modal-actions {
  padding: 16px 24px;
  background-color: #f8fafc;
  border-top: 1px solid #e2e8f0;
}

.headline {
  font-size: 20px;
  font-weight: 600;
  color: #1e293b;
}
</style>
