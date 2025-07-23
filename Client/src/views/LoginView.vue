<template>
  <div class="login-view">
    <div class="login-container">
      <div class="welcome-section">
        <div class="welcome-content">
          <div class="logo-container">
            <!-- Emplacement pour votre logo d'entreprise -->
            <img
              src="../assets/images/logo-senelec.png"
              alt="Logo SENELEC"
              class="company-logo"
            />
          </div>
          <h1>Portail de Gestion des Congés SENELEC</h1>
          <p>
            Bienvenue sur votre espace collaboratif dédié à la gestion des
            congés. Notre plateforme innovante vous permet de :
          </p>
          <ul class="features-list">
            <li>Gérer vos demandes de congés en temps réel</li>
            <li>Suivre l'état de vos demandes</li>
            <li>Consulter votre solde de congés</li>
            <li>Planifier vos absences en toute simplicité</li>
          </ul>
        </div>
      </div>
      <div class="form-section">
        <LoginForm />
      </div>
    </div>
  </div>
</template>

<script>
import LoginForm from "@/components/LoginForm.vue";

export default {
  name: "LoginView",
  components: {
    LoginForm,
  },
};
</script>

<style scoped>
.login-view {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #100644, #261555 70%);
  padding: 20px;
}

.login-container {
  display: flex;
  width: 100%;
  max-width: 1200px;
  min-height: 600px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  border-radius: 16px;
  overflow: hidden;
  background-color: white;
}

.welcome-section {
  flex: 2;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 50px;
  background: linear-gradient(
    145deg,
    var(--primary-color),
    var(--tertiary-color)
  );
  position: relative;
  overflow: hidden;
}

/* Effet de vague pour le fond */
.welcome-section::after {
  content: "";
  position: absolute;
  bottom: -50px;
  left: 0;
  width: 100%;
  height: 200px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50% 50% 0 0;
  transform: scale(1.5);
}

.welcome-section::before {
  content: "";
  position: absolute;
  top: -50px;
  right: -50px;
  width: 200px;
  height: 200px;
  background: rgba(255, 255, 255, 0.1);
  border-radius: 50%;
}

.welcome-content {
  position: relative;
  z-index: 1;
  text-align: center;
  max-width: 450px;
}

.logo-container {
  margin-bottom: 35px;
  display: flex;
  justify-content: center;
}

.company-logo {
  max-width: 180px;
  height: auto;
  filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.2));
}

.welcome-content h1 {
  font-size: 28px;
  margin-bottom: 24px;
  font-weight: 700;
  color: white;
  text-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
  line-height: 1.3;
}

.welcome-content p {
  font-size: 16px;
  line-height: 1.7;
  color: rgba(255, 255, 255, 0.9);
  margin-bottom: 20px;
  text-align: left;
}

.features-list {
  list-style: none;
  padding: 0;
  margin: 0 0 40px 0;
  text-align: left;
}

.features-list li {
  color: rgba(255, 255, 255, 0.9);
  font-size: 15px;
  margin-bottom: 12px;
  padding-left: 28px;
  position: relative;
  line-height: 1.5;
}

.features-list li::before {
  content: "✓";
  position: absolute;
  left: 0;
  color: #4caf50;
  font-weight: bold;
}

/* Ajout d'une animation subtile au survol des éléments de la liste */
.features-list li:hover {
  transform: translateX(5px);
  transition: transform 0.3s ease;
}

.form-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px;
  background-color: white;
  position: relative;
}

/* Motif subtil pour le fond du formulaire */
.form-section::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: radial-gradient(
    circle at 50% 50%,
    var(--primary-color) 1px,
    transparent 1px
  );
  background-size: 30px 30px;
  opacity: 0.03;
}

@media (max-width: 992px) {
  .login-container {
    max-width: 90%;
  }

  .welcome-content h1 {
    font-size: 24px;
  }

  .welcome-content p {
    font-size: 15px;
  }
}

@media (max-width: 768px) {
  .login-container {
    flex-direction: column;
    height: auto;
    max-width: 100%;
  }

  .welcome-section {
    padding: 40px 20px;
    flex: none;
    min-height: 300px;
  }

  .welcome-content h1 {
    font-size: 22px;
  }

  .welcome-content p {
    font-size: 14px;
    margin-bottom: 20px;
  }

  .company-logo {
    max-width: 140px;
  }

  .form-section {
    padding: 30px 20px;
  }
}

@media (max-width: 480px) {
  .welcome-section {
    min-height: 280px;
    padding: 30px 15px;
  }

  .logo-container {
    margin-bottom: 20px;
  }

  .welcome-content h1 {
    font-size: 18px;
    margin-bottom: 15px;
  }

  .welcome-content p {
    font-size: 13px;
    line-height: 1.5;
  }

  .company-logo {
    max-width: 120px;
  }
}
</style>
