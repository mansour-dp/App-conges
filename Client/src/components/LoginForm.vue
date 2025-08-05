<template>
  <div class="login-form">
    <div class="form-header">
      <h1>Connexion</h1>
    </div>
    
    <!-- Erreur générale simple -->
    <div v-if="error" class="simple-error-message" @click="clearError">
      {{ error }}
    </div>

    <form @submit.prevent="handleSubmit" autocomplete="off" novalidate>
      <div class="form-group">
        <label for="email">Adresse e-mail</label>
        <input
          type="email"
          id="email"
          v-model="email"
          required
          placeholder="Entrez votre adresse e-mail"
          :disabled="loading"
          :class="{ 'input-error': emailError }"
          autocomplete="off"
          @input="emailError = ''"
          @focus="emailError = ''"
        />
        <div v-if="emailError" class="error-message">
          <v-icon icon="mdi-alert-circle" size="small"></v-icon>
          {{ emailError }}
        </div>
      </div>
      
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <div class="password-input-container">
          <input
            :type="showPassword ? 'text' : 'password'"
            id="password"
            v-model="password"
            required
            placeholder="Entrez votre mot de passe"
            :disabled="loading"
            :class="{ 'input-error': passwordError }"
            autocomplete="off"
            @input="passwordError = ''"
            @focus="passwordError = ''"
          />
          <button
            type="button"
            class="password-toggle"
            @click="showPassword = !showPassword"
            :disabled="loading"
          >
            <v-icon :icon="showPassword ? 'mdi-eye-off' : 'mdi-eye'" size="small"></v-icon>
          </button>
        </div>
        <div v-if="passwordError" class="error-message">
          <v-icon icon="mdi-alert-circle" size="small"></v-icon>
          {{ passwordError }}
        </div>
      </div>
      <div class="form-actions">
        <button 
          type="submit" 
          class="login-btn" 
          :disabled="loading"
          @click.prevent="handleSubmit"
        >
          <span v-if="loading" class="loading-spinner"></span>
          <v-icon v-if="!loading" icon="mdi-login" start size="small"></v-icon>
          {{ loading ? 'Connexion en cours...' : 'Se connecter' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useUserStore } from '@/stores/users';

// Composables
const router = useRouter();
const usersStore = useUserStore();

// Data
const email = ref('');
const password = ref('');
const loading = ref(false);
const error = ref('');
const emailError = ref('');
const passwordError = ref('');
const lastSubmitTime = ref(0);
const showPassword = ref(false);

// Methods
const clearError = () => {
  error.value = '';
  emailError.value = '';
  passwordError.value = '';
};

const validateEmail = () => {
  if (!email.value) {
    emailError.value = 'L\'adresse e-mail est obligatoire.';
    return false;
  }
  // Validation simplifiée - on accepte même si pas parfait
  emailError.value = '';
  return true;
};

const validatePassword = () => {
  if (!password.value) {
    passwordError.value = 'Le mot de passe est obligatoire.';
    return false;
  }
  // Validation simplifiée - on accepte n'importe quel mot de passe non vide
  passwordError.value = '';
  return true;
};

const validateForm = () => {
  const isEmailValid = validateEmail();
  const isPasswordValid = validatePassword();
  return isEmailValid && isPasswordValid;
};

const handleSubmit = (event) => {
  event.preventDefault();
  event.stopPropagation();
  login();
};

const login = async () => {
  // Empêcher les soumissions multiples avec debounce
  const now = Date.now();
  if (loading.value || (now - lastSubmitTime.value) < 1000) return;
  
  lastSubmitTime.value = now;
  loading.value = true;
  error.value = '';
  emailError.value = '';
  passwordError.value = '';

  try {
    // Validation comme Facebook - montrer les erreurs mais permettre la soumission
    let hasErrors = false;
    
    if (!email.value.trim()) {
      emailError.value = 'L\'adresse e-mail est obligatoire.';
      hasErrors = true;
    }
    
    if (!password.value.trim()) {
      passwordError.value = 'Le mot de passe est obligatoire.';
      hasErrors = true;
    }
    
    // Si erreurs de base, arrêter ici
    if (hasErrors) {
      return;
    }

    // Utiliser la méthode login du store
    const result = await usersStore.login({
      email: email.value.trim(),
      password: password.value
    });

    if (result.success) {
      const userData = result.user;
      
      // Extraction directe du rôle utilisateur depuis l'API
      let userRole = 'Employe'; // Valeur par défaut

      if (userData.role) {
        // Support pour les deux formats de rôle
        userRole = userData.role.name || userData.role.nom || 'Employe';
      }
      
      // Rediriger vers le tableau de bord approprié
      setTimeout(() => {
        redirectToDashboard(userRole);
      }, 100); // Petit délai pour éviter les conflicts
    } else {
      // Messages d'erreur professionnels - NE PAS EFFACER AUTOMATIQUEMENT
      error.value = getErrorMessage(result.error);
    }
  } catch (err) {
    
    error.value = getErrorMessage(err.message);
  } finally {
    loading.value = false;
  }
};

const getErrorMessage = (errorMsg) => {
  // Messages d'erreur professionnels basés sur le type d'erreur
  if (!errorMsg) return 'Une erreur inattendue s\'est produite.';
  
  const lowerError = errorMsg.toLowerCase();
  
  if (lowerError.includes('invalid credentials') || 
      lowerError.includes('mot de passe') ||
      lowerError.includes('password') ||
      lowerError.includes('identifiants')) {
    return 'Identifiants invalides. Vérifiez votre e-mail et mot de passe.';
  }
  
  if (lowerError.includes('network') || 
      lowerError.includes('timeout') ||
      lowerError.includes('connexion')) {
    return 'Problème de connexion. Veuillez réessayer.';
  }
  
  if (lowerError.includes('server') || 
      lowerError.includes('500')) {
    return 'Erreur serveur temporaire. Veuillez réessayer plus tard.';
  }
  
  if (lowerError.includes('validation') || 
      lowerError.includes('422')) {
    return 'Données invalides. Vérifiez le format de votre e-mail.';
  }
  
  if (lowerError.includes('unauthorized') || 
      lowerError.includes('401')) {
    return 'Accès non autorisé. Vérifiez vos identifiants.';
  }
  
  // Message générique pour les autres erreurs
  return 'Erreur de connexion. Veuillez réessayer.';
};

const redirectToDashboard = (userRole) => {
  // Correspondance directe avec les rôles exacts de la base de données
  switch (userRole) {
    case 'Admin':
      router.push('/admin/dashboard');
      break;
    case 'Directeur RH':
      router.push('/directeur-rh/dashboard');
      break;
    case 'Responsable RH':
      router.push('/responsable-rh/dashboard');
      break;
    case 'Directeur Unité':
      router.push('/directeur-unite/dashboard');
      break;
    case 'Superieur':
      router.push('/superieur/dashboard');
      break;
    case 'Employe':
    default:
      router.push('/employe/dashboard');
      break;
  }
};

</script>

<style scoped>
.login-form {
  background-color: rgba(255, 255, 255, 0.95);
  border-radius: 12px;
  padding: 35px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
  width: 100%;
  max-width: 420px;
  backdrop-filter: blur(10px);
}

.form-header {
  text-align: center;
  margin-bottom: 35px;
}

.form-header h1 {
  color: var(--primary-color);
  font-size: 28px;
  font-weight: 700;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin: 0;
}

/* Messages d'erreur simplifiés */
.simple-error-message {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-bottom: 16px;
  padding: 8px 12px;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 6px;
  font-size: 13px;
  color: #dc2626;
  animation: slideDown 0.3s ease-out;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.simple-error-message:hover {
  background-color: #fde8e8;
}

.simple-error-message::before {
  content: "⚠";
  color: #dc2626;
  font-weight: bold;
}

.form-group {
  margin-bottom: 24px;
  position: relative;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
  color: var(--secondary-color);
  text-align: left;
  font-size: 14px;
}

.form-group input {
  width: 100%;
  padding: 14px 16px;
  border: 1.5px solid #e0e0e0;
  border-radius: 8px;
  font-size: 15px;
  transition: all 0.3s ease;
  background-color: #f9f9f9;
}

.form-group input:focus {
  border-color: var(--primary-color);
  outline: none;
  box-shadow: 0 0 0 3px rgba(0, 138, 155, 0.15);
  background-color: white;
}

.form-group input.input-error {
  border-color: #dc2626;
  background-color: #fef2f2;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
}

.form-group input.input-error:focus {
  border-color: #dc2626;
  box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2);
}

.form-group input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.password-input-container {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input-container input {
  padding-right: 50px;
}

.password-toggle {
  position: absolute;
  right: 12px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #6b7280;
  transition: all 0.2s ease;
}

.password-toggle:hover:not(:disabled) {
  background-color: #f3f4f6;
  color: #374151;
}

.password-toggle:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  display: flex;
  align-items: center;
  gap: 6px;
  margin-top: 6px;
  padding: 8px 12px;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 6px;
  font-size: 13px;
  color: #dc2626;
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-actions {
  margin-top: 30px;
}

.login-btn {
  width: 100%;
  padding: 14px;
  background-color: var(--primary-color);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  letter-spacing: 0.5px;
  box-shadow: 0 4px 8px rgba(0, 138, 155, 0.2);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  position: relative;
  overflow: hidden;
}

.login-btn::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
  transition: left 0.5s;
}

.login-btn:hover:not(:disabled)::before {
  left: 100%;
}

.login-btn:hover:not(:disabled) {
  background-color: var(--tertiary-color);
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 138, 155, 0.25);
}

.login-btn:active:not(:disabled) {
  transform: translateY(0);
  box-shadow: 0 2px 4px rgba(0, 138, 155, 0.2);
}

.login-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
  transform: none;
  background-color: #6b7280;
  pointer-events: none;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 50%;
  border-top-color: white;
  animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Animation d'apparition simple */
@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.form-links {
  margin-top: 24px;
  text-align: center;
}

.form-links a {
  color: var(--primary-color);
  text-decoration: none;
  font-size: 14px;
  transition: color 0.3s ease;
}

.form-links a:hover {
  color: var(--tertiary-color);
  text-decoration: underline;
}
</style>
