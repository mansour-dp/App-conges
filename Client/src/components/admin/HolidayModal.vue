<template>
  <v-dialog v-model="dialog" max-width="600px" persistent>
    <v-card>
      <v-card-title class="modal-title">
        <span class="headline">{{ holiday && holiday.id ? 'Modifier le jour férié' : 'Nouveau jour férié' }}</span>
        <v-spacer></v-spacer>
        <v-btn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="closeModal"
        ></v-btn>
      </v-card-title>

      <v-card-text>
        <!-- Info jour férié pour édition -->
        <div v-if="holiday && holiday.id" class="holiday-info">
          <v-icon icon="mdi-calendar-star" class="holiday-icon"></v-icon>
          <div>
            <p class="holiday-name">{{ holiday.name }}</p>
            <p class="holiday-date">{{ formatDate(holiday.date) }} - {{ getDayOfWeek(holiday.date) }}</p>
          </div>
        </div>

        <v-form ref="formRef" v-model="valid" @submit.prevent="handleSubmit">
          <div class="form-fields">
            <v-text-field
              v-model="form.name"
              label="Nom du jour férié"
              variant="outlined"
              density="comfortable"
              :rules="nameRules"
              hide-details="auto"
              prepend-inner-icon="mdi-text"
            />

            <v-text-field
              v-model="form.date"
              label="Date"
              type="date"
              variant="outlined"
              density="comfortable"
              :rules="dateRules"
              hide-details="auto"
              prepend-inner-icon="mdi-calendar"
            />

            <v-select
              v-model="form.type"
              label="Type de jour férié"
              :items="holidayTypes"
              variant="outlined"
              density="comfortable"
              hide-details="auto"
              prepend-inner-icon="mdi-tag"
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

          <!-- Info sur le jour de la semaine -->
          <v-alert
            v-if="form.date"
            type="info"
            variant="tonal"
            class="mt-4"
          >
            <template #text>
              <strong>{{ formatDate(form.date) }}</strong> tombe un <strong>{{ getDayOfWeek(form.date) }}</strong>
            </template>
          </v-alert>

          <!-- Alerte pour les weekends -->
          <v-alert
            v-if="form.date && isWeekend(form.date)"
            type="warning"
            variant="tonal"
            class="mt-4"
          >
            <template #text>
              Cette date tombe un weekend. Vérifiez si c'est nécessaire d'ajouter ce jour férié.
            </template>
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
          {{ holiday && holiday.id ? 'Modifier' : 'Ajouter' }}
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
  holiday: {
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
  date: '',
  type: 'national',
  description: ''
});

// Options
const holidayTypes = ref([
  { title: 'Jour férié national', value: 'national' },
  { title: 'Jour férié religieux', value: 'religious' },
  { title: 'Jour férié local', value: 'local' },
  { title: 'Autre', value: 'company' }
]);

// Computed
const dialog = computed({
  get: () => props.modelValue,
  set: (value) => emit('update:modelValue', value)
});

const canSubmit = computed(() => {
  return form.value.name && form.value.date;
});

// Rules de validation
const nameRules = [
  v => !!v || 'Le nom du jour férié est obligatoire',
  v => (v && v.length >= 3) || 'Le nom doit contenir au moins 3 caractères',
  v => (v && v.length <= 100) || 'Le nom ne peut pas dépasser 100 caractères'
];

const dateRules = [
  v => !!v || 'La date est obligatoire'
];

// Methods
const resetForm = () => {
  form.value = {
    name: '',
    date: '',
    type: 'national',
    description: ''
  };
  if (formRef.value) {
    formRef.value.resetValidation();
  }
};

const populateForm = () => {
  if (props.holiday) {
    form.value = {
      name: props.holiday.name || '',
      date: props.holiday.date || '',
      type: props.holiday.type || 'national',
      description: props.holiday.description || ''
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
      emit('submit', { ...form.value });
    }
  }
};

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString('fr-FR');
};

const getDayOfWeek = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  const days = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
  return days[date.getDay()];
};

const isWeekend = (dateString) => {
  if (!dateString) return false;
  const date = new Date(dateString);
  const day = date.getDay();
  return day === 0 || day === 6; // 0 = Dimanche, 6 = Samedi
};

// Watchers
watch(() => props.modelValue, (newVal) => {
  if (newVal) {
    nextTick(() => {
      populateForm();
    });
  }
});

watch(() => props.holiday, () => {
  if (props.modelValue) {
    populateForm();
  }
});
</script>

<style scoped>
.modal-title {
  background-color: #fff7ed;
  border-bottom: 1px solid #fed7aa;
  padding: 20px 24px;
}

.holiday-info {
  display: flex;
  align-items: center;
  padding: 16px;
  background-color: #fff7ed;
  border-radius: 8px;
  margin-bottom: 24px;
  border-left: 4px solid #ea580c;
}

.holiday-icon {
  font-size: 32px;
  color: #ea580c;
  margin-right: 16px;
}

.holiday-name {
  font-size: 16px;
  font-weight: 600;
  margin: 0;
  color: #1e293b;
}

.holiday-date {
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
