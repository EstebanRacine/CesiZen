<template>
  <div v-if="showModal" class="modal-overlay">
    <div class="modal-content">
      <button @click="closeModal" class="close-button" aria-label="Fermer la modale">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
      </button>

      <h2 class="modal-title">{{ modalMessage || 'Connectez-vous' }}</h2>

      <div class="auth-forms-container">
        <form @submit.prevent="handleLogin" class="auth-form">
          <h3 class="form-title">Se connecter</h3>
          <div class="input-group">
            <label for="login-username" class="sr-only">Nom d'utilisateur</label>
            <input
              id="login-username"
              type="text"
              v-model="loginUsername"
              placeholder="Nom d'utilisateur"
              required
              class="auth-input"
            />
          </div>
          <div class="input-group">
            <label for="login-password" class="sr-only">Mot de passe</label>
            <input
              id="login-password"
              type="password"
              v-model="loginPassword"
              placeholder="Mot de passe"
              required
              class="auth-input"
            />
          </div>
          <button type="submit" class="auth-button">Se connecter</button>
        </form>

        <!-- Formulaire de Création de Compte -->
        <!-- <form @submit.prevent="handleCreateAccount" class="auth-form">
          <h3 class="form-title">Créer un compte</h3>
          <div class="input-group">
            <label for="create-username" class="sr-only">Nom d'utilisateur</label>
            <input
              id="create-username"
              type="text"
              v-model="createUsername"
              placeholder="Nom d'utilisateur"
              required
              class="auth-input"
            />
          </div>
          <div class="input-group">
            <label for="create-email" class="sr-only">Email</label>
            <input
              id="create-email"
              type="email"
              v-model="createEmail"
              placeholder="Email"
              required
              class="auth-input"
            />
          </div>
          <div class="input-group">
            <label for="create-password" class="sr-only">Mot de passe</label>
            <input
              id="create-password"
              type="password"
              v-model="createPassword"
              placeholder="Mot de passe"
              required
              class="auth-input"
            />
          </div>
          <button type="submit" class="auth-button create-button">Créer le compte</button>
        </form> -->
      </div>

      <p v-if="errorMessage" class="error-message">{{ errorMessage }}</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import authService from '@/services/singleton/authService.js';

// Props pour contrôler la modale depuis le parent
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  message: {
    type: String,
    default: ''
  }
});

// Emit pour communiquer avec le composant parent
const emit = defineEmits(['close', 'login-success']);

// États locaux pour les champs de formulaire de connexion
const loginUsername = ref('');
const loginPassword = ref('');

// États locaux pour les champs de formulaire de création de compte
const createUsername = ref('');
const createEmail = ref('');
const createPassword = ref('');

// État pour les messages d'erreur à afficher à l'utilisateur
const errorMessage = ref('');

// Computed properties pour la réactivité avec le service
const showModal = computed(() => props.show || authService.showLoginModal.value);
const modalMessage = computed(() => props.message || authService.loginModalMessage.value);

// Fonction pour fermer la modale
const closeModal = () => {
  errorMessage.value = '';
  resetForms();
  authService.closeLoginModal();
  emit('close');
};

// Fonction pour réinitialiser les formulaires
const resetForms = () => {
  loginUsername.value = '';
  loginPassword.value = '';
  createUsername.value = '';
  createEmail.value = '';
  createPassword.value = '';
};

// Fonction pour gérer la soumission du formulaire de connexion
const handleLogin = async () => {
  errorMessage.value = '';
  try {
    await authService.login(loginUsername.value, loginPassword.value);
    
    // Émettre l'événement de succès de connexion
    emit('login-success');
    
    // Fermer la modale après une connexion réussie
    closeModal();
  } catch (error) {
    console.error("Erreur de connexion:", error);
    
    // Gestion des différents types d'erreurs
    if (error.code === 'ERR_NETWORK') {
      errorMessage.value = 'Impossible de se connecter au serveur. Vérifiez que le serveur backend est démarré sur http://localhost:8000';
    } else if (error.response?.status === 401) {
      errorMessage.value = 'Nom d\'utilisateur ou mot de passe incorrect.';
    } else if (error.response?.status === 500) {
      errorMessage.value = 'Erreur interne du serveur. Veuillez réessayer plus tard.';
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Erreur de connexion. Veuillez vérifier vos identifiants et réessayer.';
    }
  }
};

// Fonction pour gérer la soumission du formulaire de création de compte
const handleCreateAccount = async () => {
  errorMessage.value = ''; // Réinitialise le message d'erreur
  try {
    // Cette fonctionnalité peut être implémentée plus tard
    // const response = await authService.register(createUsername.value, createEmail.value, createPassword.value);
    
    errorMessage.value = 'Fonctionnalité de création de compte à implémenter.';
  } catch (error) {
    // Capture et affiche l'erreur de l'API ou une erreur générique
    errorMessage.value = error.response?.data?.message || 'Erreur lors de la création du compte. Veuillez réessayer.';
    console.error("Erreur de création de compte:", error);
  }
};
</script>

<style scoped>
/* Overlay de la modale */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7); /* Fond semi-transparent sombre */
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 2000; /* Assurez-vous qu'elle soit au-dessus de tout le reste */
  backdrop-filter: blur(5px); /* Effet de flou sur l'arrière-plan */
  animation: fadeIn 0.3s ease-out forwards; /* Animation d'apparition */
}

/* Contenu principal de la modale */
.modal-content {
  background: linear-gradient(145deg, #2a5d49 0%, #89c997 100%); /* Gradient similaire à la sidebar */
  color: #f4fff5; /* Texte blanc cassé */
  padding: 2.5rem 2rem;
  border-radius: 16px; /* Bords arrondis */
  position: relative;
  max-width: 700px; /* Largeur maximale */
  width: 90%;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); /* Ombre prononcée */
  animation: slideIn 0.3s ease-out forwards; /* Animation d'apparition du contenu */
  border: 1px solid rgba(250, 222, 109, 0.3); /* Petite bordure accentuée */
}

/* Titre de la modale */
.modal-title {
  font-size: 1.8rem;
  font-weight: 700;
  color: #fade6d; /* Jaune clair comme l'application-name */
  text-align: center;
  margin-bottom: 2rem;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

/* Bouton de fermeture */
.close-button {
  position: absolute;
  top: 15px;
  right: 15px;
  background: none;
  border: none;
  color: #f4fff5; /* Couleur de l'icône */
  font-size: 1.8rem;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 50%;
  transition: background 0.2s ease;
  display: flex; /* Centrer l'icône SVG */
  align-items: center;
  justify-content: center;
}

.close-button:hover {
  background: rgba(255, 255, 255, 0.2);
}

.close-button svg {
  width: 24px;
  height: 24px;
}

/* Conteneur des formulaires (flexbox pour alignement côte à côte) */
.auth-forms-container {
  display: flex;
  flex-wrap: wrap; /* Permet aux formulaires de passer à la ligne sur petits écrans */
  gap: 30px; /* Espace entre les formulaires */
  justify-content: center; /* Centrer les formulaires */
}

/* Style commun aux formulaires */
.auth-form {
  flex: 1; /* Chaque formulaire prend la place disponible */
  min-width: 280px; /* Largeur minimale avant de passer à la ligne */
  display: flex;
  flex-direction: column;
  gap: 15px; /* Espace entre les éléments du formulaire */
  background: rgba(255, 255, 255, 0.08); /* Fond légèrement transparent pour les formulaires */
  padding: 1.5rem;
  border-radius: 12px;
  box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1); /* Ombre intérieure subtile */
  border: 1px solid rgba(250, 222, 109, 0.2);
}

.form-title {
  font-size: 1.3rem;
  color: #fade6d; /* Jaune clair pour les titres de formulaire */
  margin-bottom: 1rem;
  text-align: center;
}

/* Groupes d'inputs */
.input-group {
  width: 100%;
}

/* Inputs de formulaire */
.auth-input {
  width: 100%;
  padding: 0.8rem 1rem;
  border: 1px solid rgba(250, 222, 109, 0.4); /* Bordure jaune clair */
  border-radius: 8px;
  background-color: rgba(255, 255, 255, 0.15); /* Fond d'input légèrement transparent */
  color: #f4fff5; /* Texte des inputs */
  font-size: 1rem;
  transition: border-color 0.25s ease, background-color 0.25s ease;
}

.auth-input::placeholder {
  color: rgba(244, 255, 245, 0.7); /* Placeholder légèrement transparent */
}

.auth-input:focus {
  outline: none;
  border-color: #00bf6a; /* Bordure verte vif au focus */
  background-color: rgba(255, 255, 255, 0.25);
  box-shadow: 0 0 0 3px rgba(0, 191, 106, 0.3); /* Légère ombre au focus */
}

/* Boutons de soumission */
.auth-button {
  width: 100%;
  padding: 0.9rem 1.5rem;
  border: none;
  border-radius: 10px;
  background: #00bf6a; /* Vert vif comme le nav-item.active */
  color: #fff;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.25s ease, box-shadow 0.25s ease;
  margin-top: 10px;
  box-shadow: 0 4px 12px rgba(0, 191, 106, 0.4);
}

.auth-button:hover {
  background: #00e080; /* Vert un peu plus clair au hover */
  box-shadow: 0 6px 15px rgba(0, 191, 106, 0.6);
  transform: translateY(-2px); /* Léger soulèvement */
}

/* Bouton spécifique pour "Créer le compte" si vous voulez un style légèrement différent */
.create-button {
  background: #fade6d; /* Jaune clair pour créer le compte */
  color: #2a5d49; /* Texte vert foncé */
  box-shadow: 0 4px 12px rgba(250, 222, 109, 0.4);
}

.create-button:hover {
  background: #ffe88d; /* Jaune un peu plus clair au hover */
  box-shadow: 0 6px 15px rgba(250, 222, 109, 0.6);
}

/* Message d'erreur */
.error-message {
  color: #ff6b6b; /* Rouge pour les erreurs */
  text-align: center;
  margin-top: 1.5rem;
  font-weight: 500;
  padding: 0.8rem;
  background-color: rgba(255, 107, 107, 0.1);
  border-radius: 8px;
  border: 1px solid #ff6b6b;
}

/* Séparateur visuel entre les formulaires sur grand écran */
.form-separator {
  width: 1px;
  background: rgba(250, 222, 109, 0.3); /* Ligne jaune claire */
  margin: 0 20px; /* Espacement */
  min-height: 250px; /* Hauteur minimale */
}

/* Responsive pour les petits écrans */
@media (max-width: 768px) {
  .modal-content {
    padding: 1.5rem;
    width: 95%;
  }

  .modal-title {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
  }

  .auth-forms-container {
    flex-direction: column; /* Les formulaires s'empilent */
    gap: 20px;
  }

  .auth-form {
    min-width: unset; /* Annule la largeur minimale */
    width: 100%;
    padding: 1rem;
  }

  .form-separator {
    display: none; /* Cache le séparateur sur mobile */
  }
}

/* Animations */
@keyframes fadeIn {
  from { opacity: 0; }
  to { opacity: 1; }
}

@keyframes slideIn {
  from { transform: translateY(-50px) scale(0.9); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}
</style>