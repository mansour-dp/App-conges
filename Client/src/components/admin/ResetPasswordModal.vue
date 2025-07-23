<template>
  <v-dialog v-model="dialog" max-width="500px" persistent>
    <v-card>
      <v-card-title class="modal-title">
        <span class="headline">Réinitialiser le mot de passe</span>
        <v-spacer></v-spacer>
        <v-btn
          icon="mdi-close"
          variant="text"
          size="small"
          @click="closeModal"
        ></v-btn>
      </v-card-title>

      <v-card-text>
        <div class="user-info">
          <v-icon icon="mdi-account" class="user-icon"></v-icon>
          <div>
            <p class="user-name">{{ user?.prenom }} {{ user?.nom }}</p>
            <p class="user-email">{{ user?.email }}</p>
          </div>
        </div>

        <v-form ref="formRef" v-model="valid" @submit.prevent="handleSubmit">
          <div class="form-fields">
            <v-text-field
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              :append-inner-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
              label="Nouveau mot de passe"
              variant="outlined"
              density="comfortable"
              :rules="passwordRules"
              hide-details="auto"
              @click:append-inner="showPassword = !showPassword"
            />

            <v-text-field
              v-model="form.password_confirmation"
              :type="showPasswordConfirm ? 'text' : 'password'"
              :append-inner-icon="showPasswordConfirm ? 'mdi-eye' : 'mdi-eye-off'"
              label="Confirmer le nouveau mot de passe"
              variant="outlined"
              density="comfortable"
              :rules="passwordConfirmRules"
              hide-details="auto"
              @click:append-inner="showPasswordConfirm = !showPasswordConfirm"
            />

            <div class="password-strength">
              <v-progress-linear
                :model-value="passwordStrength.score"
                :color="passwordStrength.color"
                height="4"
                rounded
              ></v-progress-linear>
              <p class="strength-text" :style="{ color: passwordStrength.color }">
                {{ passwordStrength.text }}
              </p>
            </div>

            <div class="password-requirements">
              <p class="requirements-title">Le mot de passe doit contenir :</p>
              <div class="requirement-item" :class="{ met: form.password.length >= 8 }">
                <v-icon :icon="form.password.length >= 8 ? 'mdi-check' : 'mdi-close'" size="16"></v-icon>
                Au moins 8 caractères
              </div>
              <div class="requirement-item" :class="{ met: /[A-Z]/.test(form.password) }">
                <v-icon :icon="/[A-Z]/.test(form.password) ? 'mdi-check' : 'mdi-close'" size="16"></v-icon>
                Une lettre majuscule
              </div>
              <div class="requirement-item" :class="{ met: /[a-z]/.test(form.password) }">
                <v-icon :icon="/[a-z]/.test(form.password) ? 'mdi-check' : 'mdi-close'" size="16"></v-icon>
                Une lettre minuscule
              </div>
              <div class="requirement-item" :class="{ met: /\d/.test(form.password) }">
                <v-icon :icon="/\d/.test(form.password) ? 'mdi-check' : 'mdi-close'" size="16"></v-icon>
                Un chiffre
              </div>
            </div>
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
          Réinitialiser
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<script>
export default {
  name: "ResetPasswordModal",
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
  emits: ['update:modelValue', 'submit'],
  data() {
    return {
      valid: false,
      showPassword: false,
      showPasswordConfirm: false,
      form: {
        password: '',
        password_confirmation: ''
      },
      passwordRules: [
        v => !!v || 'Le mot de passe est requis',
        v => (v && v.length >= 8) || 'Le mot de passe doit contenir au moins 8 caractères',
        v => /[A-Z]/.test(v) || 'Le mot de passe doit contenir au moins une lettre majuscule',
        v => /[a-z]/.test(v) || 'Le mot de passe doit contenir au moins une lettre minuscule',
        v => /\d/.test(v) || 'Le mot de passe doit contenir au moins un chiffre',
      ]
    };
  },
  computed: {
    dialog: {
      get() {
        return this.modelValue;
      },
      set(value) {
        this.$emit('update:modelValue', value);
      }
    },
    passwordConfirmRules() {
      return [
        v => !!v || 'La confirmation du mot de passe est requise',
        v => v === this.form.password || 'Les mots de passe ne correspondent pas',
      ];
    },
    passwordStrength() {
      const password = this.form.password;
      if (!password) return { score: 0, color: '#e0e0e0', text: '' };
      
      let score = 0;
      let feedback = '';
      
      // Longueur
      if (password.length >= 8) score += 25;
      else feedback = 'Trop court';
      
      // Majuscule
      if (/[A-Z]/.test(password)) score += 25;
      else if (!feedback) feedback = 'Ajouter une majuscule';
      
      // Minuscule
      if (/[a-z]/.test(password)) score += 25;
      else if (!feedback) feedback = 'Ajouter une minuscule';
      
      // Chiffre
      if (/\d/.test(password)) score += 25;
      else if (!feedback) feedback = 'Ajouter un chiffre';
      
      // Couleur et texte selon le score
      if (score < 50) return { score, color: '#f44336', text: feedback || 'Faible' };
      if (score < 75) return { score, color: '#ff9800', text: 'Moyen' };
      if (score < 100) return { score, color: '#2196f3', text: 'Bon' };
      return { score, color: '#4caf50', text: 'Excellent' };
    }
  },
  watch: {
    modelValue(newVal) {
      if (newVal) {
        this.resetForm();
      }
    }
  },
  methods: {
    async handleSubmit() {
      const { valid } = await this.$refs.formRef.validate();
      if (!valid) return;
      
      this.$emit('submit', {
        password: this.form.password,
        password_confirmation: this.form.password_confirmation
      });
    },
    closeModal() {
      this.dialog = false;
      this.resetForm();
    },
    resetForm() {
      this.form = {
        password: '',
        password_confirmation: ''
      };
      this.$refs.formRef?.resetValidation();
    }
  }
};
</script>

<style scoped>
.modal-title {
  padding: 20px 24px 16px 24px;
  border-bottom: 1px solid #e0e0e0;
}

.user-info {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background-color: #f5f5f5;
  border-radius: 8px;
  margin-bottom: 24px;
}

.user-icon {
  color: #008a9b;
  font-size: 32px;
}

.user-name {
  font-weight: 600;
  font-size: 16px;
  margin: 0;
  color: #1a1a1a;
}

.user-email {
  color: #6b7280;
  font-size: 14px;
  margin: 4px 0 0 0;
}

.form-fields {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.password-strength {
  margin-top: 8px;
}

.strength-text {
  font-size: 12px;
  margin: 4px 0 0 0;
  font-weight: 500;
}

.password-requirements {
  background-color: #f9f9f9;
  padding: 16px;
  border-radius: 8px;
  border-left: 4px solid #008a9b;
}

.requirements-title {
  font-size: 14px;
  font-weight: 600;
  margin: 0 0 12px 0;
  color: #374151;
}

.requirement-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 13px;
  margin-bottom: 6px;
  color: #6b7280;
  transition: color 0.2s ease;
}

.requirement-item:last-child {
  margin-bottom: 0;
}

.requirement-item.met {
  color: #4caf50;
}

.requirement-item .v-icon {
  color: inherit;
}

.modal-actions {
  padding: 16px 24px 20px 24px;
  border-top: 1px solid #e0e0e0;
  gap: 12px;
}

.modal-actions .v-btn {
  text-transform: none;
  font-weight: 500;
}
</style>
