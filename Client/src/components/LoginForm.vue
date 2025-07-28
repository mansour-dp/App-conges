<template>
  <div class="login-form">
    <div class="form-header">
      <h1>Connexion</h1>
    </div>
    
    <!-- Alerte d'erreur -->
    <div v-if="error" class="error-alert">
      <span class="error-icon"></span>
      <span class="error-text">{{ error }}</span>
    </div>

    <form @submit.prevent="login" autocomplete="off">
      <div class="form-group">
        <label for="email">Adresse e-mail</label>
        <input
          type="email"
          id="email"
          v-model="email"
          required
          placeholder="Entrez votre adresse e-mail"
          :disabled="loading"
          autocomplete="off"
        />
      </div>
      <div class="form-group">
        <label for="password">Mot de passe</label>
        <input
          type="password"
          id="password"
          v-model="password"
          required
          placeholder="Entrez votre mot de passe"
          :disabled="loading"
          autocomplete="off"
        />
      </div>
      <div class="form-actions">
        <button type="submit" class="login-btn" :disabled="loading">
          <span v-if="loading" class="loading-spinner"></span>
          {{ loading ? 'Connexion...' : 'Se connecter' }}
        </button>
      </div>
      <div class="form-links">
        <a href="#" @click.prevent="forgotPassword">Mot de passe oublié?</a>
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

// Methods
const login = async () => {
  loading.value = true;
  error.value = '';

  try {
    // Utiliser la méthode login du store
    const result = await usersStore.login({
      email: email.value,
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
      
      console.log('✅ Connexion réussie:', {
        user: userData.email,
        role: userRole
      });
      
      // Rediriger vers le tableau de bord approprié
      redirectToDashboard(userRole);
    } else {
      error.value = result.error || 'Erreur de connexion';
      console.error('❌ Échec de connexion:', result.error);
    }
  } catch (err) {
    console.error('Erreur de connexion:', err);
    error.value = `Erreur de connexion: ${err.message || 'Erreur inconnue'}`;
  } finally {
    loading.value = false;
  }
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

const forgotPassword = () => {
  // TODO: Implémenter la réinitialisation de mot de passe
  console.log("Réinitialisation du mot de passe demandée");
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

/* Alertes */
.error-alert {
  display: flex;
  align-items: center;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 20px;
  font-size: 14px;
  background-color: #fef2f2;
  border: 1px solid #fecaca;
  color: #dc2626;
}

.error-icon {
  margin-right: 8px;
  font-size: 16px;
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

.form-group input:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
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
