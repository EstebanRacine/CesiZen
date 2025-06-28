<template>
  <div v-if="showModal" class="modal-overlay">
    <div class="modal-content">
      <button @click="closeModal" class="close-button" aria-label="Fermer la modale">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
      </button>

      <h2 class="modal-title">{{ getModalTitle() }}</h2>

      <div class="auth-forms-container">
        <!-- Formulaire de Connexion -->
        <form v-if="isLoginMode" @submit.prevent="handleLogin" class="auth-form">
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
        <form v-if="!isLoginMode" @submit.prevent="handleCreateAccount" class="auth-form">
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
          <div class="input-group">
            <label for="create-password-confirm" class="sr-only">Confirmer le mot de passe</label>
            <input
              id="create-password-confirm"
              type="password"
              v-model="createPasswordConfirm"
              placeholder="Confirmer le mot de passe"
              required
              class="auth-input"
              :class="{ 'password-mismatch': passwordMismatch }"
            />
          </div>
          <!-- Message d'erreur pour les mots de passe non identiques -->
          <div v-if="passwordMismatch" class="password-error">
            Les mots de passe ne correspondent pas.
          </div>
          <button type="submit" class="auth-button create-button" :disabled="passwordMismatch">Créer le compte</button>
        </form>
      </div>

      <!-- Messages d'erreur -->
      <div v-if="errorMessage" class="error-message">{{ errorMessage }}</div>
      
      <!-- Messages d'erreur de validation -->
      <div v-if="validationErrors && Object.keys(validationErrors).length > 0" class="validation-errors">
        <div v-for="(messages, field) in validationErrors" :key="field" class="field-error">
          <strong>{{ getFieldLabel(field) }} :</strong>
          <ul>
            <li v-for="message in messages" :key="message">{{ message }}</li>
          </ul>
        </div>
      </div>

      <!-- Lien pour basculer entre les modes -->
      <div class="switch-mode">
        <button 
          @click="toggleMode" 
          class="switch-button"
          type="button"
        >
          {{ isLoginMode ? 'Pas encore de compte ? Créer un compte' : 'Déjà un compte ? Se connecter' }}
        </button>
      </div>
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
const emit = defineEmits(['close', 'login-success', 'register-success']);

// État pour basculer entre connexion et création de compte
const isLoginMode = ref(true);

// États locaux pour les champs de formulaire de connexion
const loginUsername = ref('');
const loginPassword = ref('');

// États locaux pour les champs de formulaire de création de compte
const createUsername = ref('');
const createPassword = ref('');
const createPasswordConfirm = ref('');

// État pour les messages d'erreur à afficher à l'utilisateur
const errorMessage = ref('');
const validationErrors = ref({});

// Computed property pour vérifier si les mots de passe correspondent
const passwordMismatch = computed(() => {
  return createPassword.value !== '' && createPasswordConfirm.value !== '' && createPassword.value !== createPasswordConfirm.value;
});

// Computed properties pour la réactivité avec le service
const showModal = computed(() => props.show || authService.showLoginModal.value);
const modalMessage = computed(() => props.message || authService.loginModalMessage.value);

// Fonction pour obtenir le titre de la modale
const getModalTitle = () => {
  if (modalMessage.value) {
    return modalMessage.value;
  }
  return isLoginMode.value ? 'Connectez-vous' : 'Créer un compte';
};

// Fonction pour obtenir le label d'un champ pour les erreurs de validation
const getFieldLabel = (field) => {
  const labels = {
    username: 'Nom d\'utilisateur',
    password: 'Mot de passe',
    email: 'Email'
  };
  return labels[field] || field;
};

// Fonction pour basculer entre les modes connexion/création
const toggleMode = () => {
  isLoginMode.value = !isLoginMode.value;
  resetMessages();
};

// Fonction pour fermer la modale
const closeModal = () => {
  resetMessages();
  resetForms();
  isLoginMode.value = true; // Revenir au mode connexion par défaut
  authService.closeLoginModal();
  emit('close');
};

// Fonction pour réinitialiser les messages d'erreur
const resetMessages = () => {
  errorMessage.value = '';
  validationErrors.value = {};
};

// Fonction pour réinitialiser les formulaires
const resetForms = () => {
  loginUsername.value = '';
  loginPassword.value = '';
  createUsername.value = '';
  createPassword.value = '';
  createPasswordConfirm.value = '';
};

// Fonction pour gérer la soumission du formulaire de connexion
const handleLogin = async () => {
  resetMessages();
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
  resetMessages();
  
  // Vérifier que les mots de passe correspondent
  if (createPassword.value !== createPasswordConfirm.value) {
    errorMessage.value = 'Les mots de passe ne correspondent pas.';
    return;
  }
  
  try {
    const response = await authService.register(createUsername.value, createPassword.value);
    
    // Émettre l'événement de succès d'inscription
    emit('register-success');
    
    // Fermer la modale après une inscription réussie
    closeModal();
  } catch (error) {
    console.error("Erreur de création de compte:", error);
    
    // Gestion des erreurs de validation spécifiques
    if (error.response?.data?.errors) {
      validationErrors.value = error.response.data.errors;
      if (error.response.data.message) {
        errorMessage.value = error.response.data.message;
      }
    } else if (error.code === 'ERR_NETWORK') {
      errorMessage.value = 'Impossible de se connecter au serveur. Vérifiez que le serveur backend est démarré sur http://localhost:8000';
    } else if (error.response?.status === 400) {
      errorMessage.value = error.response.data?.message || 'Données invalides. Veuillez vérifier vos informations.';
    } else if (error.response?.status === 409) {
      errorMessage.value = 'Ce nom d\'utilisateur est déjà utilisé. Veuillez en choisir un autre.';
    } else if (error.response?.status === 500) {
      errorMessage.value = 'Erreur interne du serveur. Veuillez réessayer plus tard.';
    } else if (error.response?.data?.message) {
      errorMessage.value = error.response.data.message;
    } else {
      errorMessage.value = 'Erreur lors de la création du compte. Veuillez réessayer.';
    }
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
  align-items: flex-start; /* Alignement en haut pour permettre le scroll */
  z-index: 2000; /* Assurez-vous qu'elle soit au-dessus de tout le reste */
  backdrop-filter: blur(5px); /* Effet de flou sur l'arrière-plan */
  animation: fadeIn 0.3s ease-out forwards; /* Animation d'apparition */
  overflow-y: auto; /* Permettre le scroll vertical */
  padding: 20px 0; /* Espacement en haut et en bas */
  
  /* Cacher la scrollbar */
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* Internet Explorer 10+ */
}

/* Cacher la scrollbar pour WebKit (Chrome, Safari, Edge) */
.modal-overlay::-webkit-scrollbar {
  display: none;
}

/* Contenu principal de la modale */
.modal-content {
  background: linear-gradient(145deg, #2a5d49 0%, #89c997 100%); /* Gradient similaire à la sidebar */
  color: #f4fff5; /* Texte blanc cassé */
  padding: 2rem 1.5rem; /* Réduction du padding */
  border-radius: 16px; /* Bords arrondis */
  position: relative;
  max-width: 600px; /* Réduction de la largeur maximale */
  width: 90%;
  max-height: calc(100vh - 40px); /* Hauteur maximale moins l'espacement */
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4); /* Ombre prononcée */
  animation: slideIn 0.3s ease-out forwards; /* Animation d'apparition du contenu */
  border: 1px solid rgba(250, 222, 109, 0.3); /* Petite bordure accentuée */
  margin: auto; /* Centrage automatique */
  overflow-y: auto; /* Scroll interne si nécessaire */
  
  /* Cacher la scrollbar interne aussi */
  scrollbar-width: none; /* Firefox */
  -ms-overflow-style: none; /* Internet Explorer 10+ */
}

/* Cacher la scrollbar interne pour WebKit */
.modal-content::-webkit-scrollbar {
  display: none;
}

/* Titre de la modale */
.modal-title {
  font-size: 1.6rem; /* Réduction de la taille */
  font-weight: 700;
  color: #fade6d; /* Jaune clair comme l'application-name */
  text-align: center;
  margin-bottom: 1.5rem; /* Réduction de la marge */
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
  gap: 20px; /* Réduction de l'espace entre les formulaires */
  justify-content: center; /* Centrer les formulaires */
}

/* Style commun aux formulaires */
.auth-form {
  flex: 1; /* Chaque formulaire prend la place disponible */
  min-width: 280px; /* Largeur minimale avant de passer à la ligne */
  display: flex;
  flex-direction: column;
  gap: 12px; /* Réduction de l'espace entre les éléments du formulaire */
  background: rgba(255, 255, 255, 0.08); /* Fond légèrement transparent pour les formulaires */
  padding: 1.2rem; /* Réduction du padding */
  border-radius: 12px;
  box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.1); /* Ombre intérieure subtile */
  border: 1px solid rgba(250, 222, 109, 0.2);
}

.form-title {
  font-size: 1.2rem; /* Réduction de la taille */
  color: #fade6d; /* Jaune clair pour les titres de formulaire */
  margin-bottom: 0.8rem; /* Réduction de la marge */
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

/* Input avec erreur de mot de passe */
.auth-input.password-mismatch {
  border-color: #ff6b6b;
  background-color: rgba(255, 107, 107, 0.1);
}

.auth-input.password-mismatch:focus {
  border-color: #ff6b6b;
  box-shadow: 0 0 0 3px rgba(255, 107, 107, 0.3);
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

.auth-button:disabled {
  background: #666;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

.auth-button:disabled:hover {
  background: #666;
  box-shadow: none;
  transform: none;
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

/* Message d'erreur pour les mots de passe */
.password-error {
  color: #fff; /* Texte blanc pour une meilleure lisibilité */
  font-size: 0.85rem; /* Réduction de la taille */
  margin-top: 0.3rem; /* Réduction de la marge */
  padding: 0.4rem; /* Réduction du padding */
  background-color: rgba(220, 53, 69, 0.9); /* Rouge plus opaque */
  border-radius: 6px;
  border: 1px solid #dc3545; /* Bordure rouge vif */
  text-align: center;
}

/* Message d'erreur */
.error-message {
  color: #fff; /* Texte blanc pour une meilleure lisibilité */
  text-align: center;
  margin-top: 1rem; /* Réduction de la marge */
  font-weight: 500;
  padding: 0.6rem; /* Réduction du padding */
  background-color: rgba(220, 53, 69, 0.9); /* Rouge plus opaque */
  border-radius: 8px;
  border: 1px solid #dc3545; /* Bordure rouge vif */
}

/* Messages d'erreur de validation */
.validation-errors {
  margin-top: 1rem; /* Réduction de la marge */
  background-color: rgba(220, 53, 69, 0.9); /* Rouge plus opaque */
  border-radius: 8px;
  border: 1px solid #dc3545; /* Bordure rouge vif */
  padding: 0.8rem; /* Réduction du padding */
}

.field-error {
  margin-bottom: 0.5rem;
  color: #fff; /* Texte blanc pour une meilleure lisibilité */
}

.field-error:last-child {
  margin-bottom: 0;
}

.field-error strong {
  color: #ffe6e6; /* Blanc légèrement teinté pour les titres */
  font-weight: 600;
}

.field-error ul {
  margin: 0.3rem 0 0 1rem;
  padding: 0;
}

.field-error li {
  font-size: 0.9rem;
  margin-bottom: 0.2rem;
}

/* Section pour basculer entre les modes */
.switch-mode {
  text-align: center;
  margin-top: 1rem; /* Réduction de la marge */
  padding-top: 0.8rem; /* Réduction du padding */
  border-top: 1px solid rgba(250, 222, 109, 0.3);
}

.switch-button {
  background: none;
  border: none;
  color: #fade6d;
  font-size: 0.9rem; /* Réduction de la taille */
  cursor: pointer;
  text-decoration: underline;
  transition: color 0.25s ease;
  padding: 0.4rem; /* Réduction du padding */
}

.switch-button:hover {
  color: #ffe88d;
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
  .modal-overlay {
    padding: 10px 0; /* Réduction de l'espacement sur mobile */
  }

  .modal-content {
    padding: 1.2rem 1rem; /* Réduction du padding sur mobile */
    width: 95%;
    max-width: none; /* Pas de largeur maximale sur mobile */
  }

  .modal-title {
    font-size: 1.4rem; /* Réduction sur mobile */
    margin-bottom: 1rem;
  }

  .auth-forms-container {
    flex-direction: column; /* Les formulaires s'empilent */
    gap: 15px; /* Réduction de l'espace */
  }

  .auth-form {
    min-width: unset; /* Annule la largeur minimale */
    width: 100%;
    padding: 1rem; /* Réduction du padding sur mobile */
    gap: 10px; /* Réduction de l'espace entre les éléments */
  }

  .form-title {
    font-size: 1.1rem; /* Réduction sur mobile */
    margin-bottom: 0.6rem;
  }

  .auth-input {
    padding: 0.7rem 0.8rem; /* Réduction du padding des inputs */
    font-size: 0.9rem; /* Réduction de la taille de la police */
  }

  .auth-button {
    padding: 0.8rem 1rem; /* Réduction du padding du bouton */
    font-size: 1rem; /* Réduction de la taille de la police */
  }

  .form-separator {
    display: none; /* Cache le séparateur sur mobile */
  }

  .error-message, .validation-errors {
    margin-top: 0.8rem; /* Réduction des marges sur mobile */
    padding: 0.5rem;
    font-size: 0.85rem;
    color: #fff; /* Maintenir le texte blanc sur mobile */
  }

  .switch-mode {
    margin-top: 0.8rem;
    padding-top: 0.6rem;
  }

  .switch-button {
    font-size: 0.85rem;
    padding: 0.3rem;
  }
}

/* Responsive pour les très petits écrans */
@media (max-width: 480px) {
  .modal-overlay {
    padding: 5px 0;
  }

  .modal-content {
    padding: 1rem 0.8rem;
    width: 98%;
    border-radius: 12px;
  }

  .modal-title {
    font-size: 1.2rem;
    margin-bottom: 0.8rem;
  }

  .auth-form {
    padding: 0.8rem;
  }

  .form-title {
    font-size: 1rem;
  }

  .close-button {
    top: 10px;
    right: 10px;
    padding: 0.3rem;
  }

  .close-button svg {
    width: 20px;
    height: 20px;
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