<template>
  <v-dialog v-model="dialog" max-width="600px" persistent>
    <v-card>
      <v-card-title class="modal-title">
        <span class="headline">{{ department && department.id ? 'Modifier le département' : 'Nouveau département' }}</span>
        <v-spacer></v-spacer>
        <v-btn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="closeModal"
        ></v-btn>
      </v-card-title>

      <v-card-text>
        <!-- Info département pour édition -->
        <div v-if="department && department.id" class="department-info">
          <v-icon icon="mdi-office-building" class="department-icon"></v-icon>
          <div>
            <p class="department-name">{{ department.name }}</p>
            <p class="department-description">{{ department.description || 'Aucune description' }}</p>
          </div>
        </div>

        <v-form ref="formRef" v-model="valid" @submit.prevent="handleSubmit">
          <div class="form-fields">
            <v-text-field
              v-model="form.name"
              label="Nom du département"
              variant="outlined"
              density="comfortable"
              :rules="nameRules"
              hide-details="auto"
              prepend-inner-icon="mdi-office-building"
            />

            <v-text-field
              v-model="form.code"
              label="Code du département"
              variant="outlined"
              density="comfortable"
              :rules="codeRules"
              hide-details="auto"
              prepend-inner-icon="mdi-identifier"
              placeholder="Ex: RH, IT, COM"
            />

            <v-textarea
              v-model="form.description"
              label="Description"
              variant="outlined"
              density="comfortable"
              :rules="descriptionRules"
              hide-details="auto"
              prepend-inner-icon="mdi-text"
              rows="3"
              placeholder="Description du département..."
            />
          </div>
        </v-form>
      </v-card-text>

      <v-card-actions class="modal-actions">
        <v-btn
          variant="outlined"
          @click="closeModal"
          :disabled="loading"
        >
          Annuler
        </v-btn>
        <v-btn
          color="primary"
          :loading="loading"
          :disabled="!valid"
          @click="handleSubmit"
        >
          {{ department && department.id ? 'Modifier' : 'Créer le département' }}
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "DepartmentModal",
  props: {
    modelValue: {
      type: Boolean,
      default: false
    },
    department: {
      type: Object,
      default: null
    },
    loading: {
      type: Boolean,
      default: false
    }
  },
  emits: ['update:modelValue', 'submit'],
  data() {
    return {
      valid: false,
      form: this.initializeForm(),
      
      // Règles de validation
      nameRules: [
        v => !!v || 'Le nom du département est requis',
        v => (v && v.length >= 2) || 'Le nom doit contenir au moins 2 caractères',
        v => (v && v.length <= 100) || 'Le nom ne peut pas dépasser 100 caractères',
      ],
      codeRules: [
        v => !!v || 'Le code du département est requis',
        v => (v && v.length >= 2) || 'Le code doit contenir au moins 2 caractères',
        v => (v && v.length <= 10) || 'Le code ne peut pas dépasser 10 caractères',
        v => (v && /^[A-Z0-9-_]+$/i.test(v)) || 'Le code doit contenir uniquement des lettres, chiffres, tirets et underscores',
      ],
      descriptionRules: [
        v => !v || v.length <= 500 || 'La description ne peut pas dépasser 500 caractères',
      ],
    }
  },
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
  watch: {
    modelValue(newVal) {
      if (newVal) {
        this.resetForm();
      }
    },
    department: {
      handler() {
        this.resetForm();
      },
      deep: true
    }
  },
  methods: {
    initializeForm() {
      return {
        name: '',
        code: '',
        description: '',
        manager_id: null
      };
    },
    
    resetForm() {
      if (this.department && this.department.id) {
        // Mode édition
        this.form = {
          name: this.department.name || '',
          code: this.department.code || '',
          description: this.department.description || '',
          manager_id: this.department.manager_id || null
        };
      } else {
        // Mode création
        this.form = this.initializeForm();
      }
      
      // Reset validation
      if (this.$refs.formRef) {
        this.$refs.formRef.resetValidation();
      }
    },
    
    async handleSubmit() {
      const { valid } = await this.$refs.formRef.validate();
      
      if (!valid) {
        return;
      }
      
      // Préparer les données à envoyer
      const formData = { ...this.form };
      
      // Ajouter l'ID si on est en mode édition
      if (this.department && this.department.id) {
        formData.id = this.department.id;
      }
      
      this.$emit('submit', formData);
    },
    
    closeModal() {
      this.dialog = false;
    }
  }
}
</script>

<style scoped>
.modal-title {
  padding: 20px 24px 16px 24px;
  border-bottom: 1px solid #e0e0e0;
}

.headline {
  font-size: 1.25rem;
  font-weight: 600;
}

.department-info {
  display: flex;
  align-items: center;
  gap: 16px;
  padding: 16px;
  background: #f8f9fa;
  border-radius: 8px;
  margin-bottom: 24px;
}

.department-icon {
  color: #008a9b;
  font-size: 40px !important;
}

.department-name {
  font-size: 1.1rem;
  font-weight: 600;
  margin: 0 0 4px 0;
  color: #2c3e50;
}

.department-description {
  margin: 0;
  color: #6c757d;
  font-size: 0.9rem;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 20px;
}

.status-section {
  background: #f5f5f5;
  border-radius: 8px;
  padding: 16px;
  border-left: 4px solid #008a9b;
}

.status-title {
  display: flex;
  align-items: center;
  gap: 8px;
  margin: 0 0 12px 0;
  font-weight: 600;
  color: #374151;
}

.modal-actions {
  padding: 16px 24px 20px 24px;
  border-top: 1px solid #e0e0e0;
  gap: 12px;
}

.modal-actions .v-btn {
  min-width: 100px;
  height: 40px;
  text-transform: none;
  font-weight: 500;
}

/* Style pour les champs de formulaire */
:deep(.v-text-field .v-field) {
  border-radius: 8px;
}

:deep(.v-textarea .v-field) {
  border-radius: 8px;
}

:deep(.v-select .v-field) {
  border-radius: 8px;
}

:deep(.v-field--focused .v-field__outline) {
  border-color: #008a9b;
  border-width: 2px;
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
