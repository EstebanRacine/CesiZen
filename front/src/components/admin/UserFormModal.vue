<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal-container" @click.stop>
      <div class="modal-header">
        <h3>{{ isEditMode ? 'Modifier l\'utilisateur' : 'Créer un utilisateur' }}</h3>
        <button @click="closeModal" class="close-btn" title="Fermer">✕</button>
      </div>

      <form @submit.prevent="handleSubmit" class="modal-form">
        <!-- Nom d'utilisateur -->
        <div class="form-group">
          <label for="username" class="form-label">
            Nom d'utilisateur *
          </label>
          <input
            id="username"
            v-model="form.username"
            type="text"
            class="form-input"
            :class="{ 'error': errors.username }"
            placeholder="Ex: jean_dupont"
            required
            :disabled="saving"
          />
          <div v-if="errors.username" class="error-message">
            {{ errors.username }}
          </div>
          <div class="form-help">
            3-50 caractères, lettres, chiffres et tirets bas uniquement
          </div>
        </div>

        <!-- Mot de passe (uniquement en création ou réinitialisation) -->
        <div v-if="!isEditMode || showPasswordReset" class="form-group">
          <label for="password" class="form-label">
            {{ isEditMode ? 'Nouveau mot de passe *' : 'Mot de passe *' }}
          </label>
          <div class="password-input-group">
            <input
              id="password"
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              class="form-input"
              :class="{ 'error': errors.password }"
              placeholder="Mot de passe sécurisé"
              :required="!isEditMode || showPasswordReset"
              :disabled="saving"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="password-toggle"
              title="Afficher/masquer le mot de passe"
            >
              {{ showPassword ? '👁️' : '🔒' }}
            </button>
            <button
              type="button"
              @click="generatePassword"
              class="generate-password-btn"
              title="Générer un mot de passe"
              :disabled="saving"
            >
              🎲
            </button>
          </div>
          <div v-if="errors.password" class="error-message">
            {{ errors.password }}
          </div>
          <div class="form-help">
            Min. 6 caractères avec au moins une majuscule et un chiffre
          </div>
        </div>

        <!-- Bouton pour réinitialiser le mot de passe en mode édition -->
        <div v-if="isEditMode && !showPasswordReset" class="form-group">
          <button
            type="button"
            @click="showPasswordReset = true"
            class="btn btn-warning btn-full"
            :disabled="saving"
          >
            🔑 Réinitialiser le mot de passe
          </button>
        </div>

        <!-- Rôle administrateur -->
        <div class="form-group">
          <label class="form-label">Privilèges</label>
          <div class="admin-toggle">
            <label class="toggle-switch">
              <input
                type="checkbox"
                v-model="form.isAdmin"
                :disabled="saving"
              />
              <span class="toggle-slider"></span>
            </label>
            <div class="admin-info">
              <span class="admin-label">
                {{ form.isAdmin ? 'Administrateur' : 'Utilisateur standard' }}
              </span>
              <span class="admin-description">
                {{ form.isAdmin ? 'Accès complet à l\'administration' : 'Accès utilisateur normal' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Statut (uniquement en édition) -->
        <div v-if="isEditMode" class="form-group">
          <label class="form-label">Statut du compte</label>
          <div class="status-toggle">
            <label class="toggle-switch">
              <input
                type="checkbox"
                v-model="form.isActive"
                :disabled="saving || isCurrentUser"
              />
              <span class="toggle-slider"></span>
            </label>
            <div class="status-info">
              <span class="status-label">
                {{ form.isActive ? 'Compte actif' : 'Compte désactivé' }}
              </span>
              <span class="status-description">
                {{ form.isActive ? 'L\'utilisateur peut se connecter' : 'L\'utilisateur ne peut pas se connecter' }}
              </span>
            </div>
          </div>
          <div v-if="isCurrentUser" class="form-help warning">
            ⚠️ Vous ne pouvez pas désactiver votre propre compte
          </div>
        </div>

        <!-- Informations supplémentaires en mode édition -->
        <div v-if="isEditMode && originalUser" class="user-info-section">
          <h4>Informations du compte</h4>
          <div class="info-grid">
            <div class="info-item">
              <span class="info-label">ID :</span>
              <span class="info-value">{{ originalUser.id }}</span>
            </div>
            <div class="info-item">
              <span class="info-label">Créé le :</span>
              <span class="info-value">{{ formatDate(originalUser.dateCreation) }}</span>
            </div>
            <div v-if="originalUser.lastLogin" class="info-item">
              <span class="info-label">Dernière connexion :</span>
              <span class="info-value">{{ formatDate(originalUser.lastLogin) }}</span>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="modal-actions">
          <button
            type="button"
            @click="closeModal"
            class="btn btn-secondary"
            :disabled="saving"
          >
            Annuler
          </button>
          <button
            type="submit"
            class="btn btn-primary"
            :disabled="saving || !isFormValid"
          >
            <span v-if="saving" class="loading-spinner"></span>
            {{ saving ? 'Sauvegarde...' : (isEditMode ? 'Mettre à jour' : 'Créer') }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import userService from '@/services/singleton/userService.js'
import authService from '@/services/singleton/authService.js'

// Props
const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  }
})

// Events
const emit = defineEmits(['close', 'success'])

// Données réactives
const form = ref({
  username: '',
  password: '',
  isAdmin: false,
  isActive: true
})

const errors = ref({})
const saving = ref(false)
const showPassword = ref(false)
const showPasswordReset = ref(false)
const originalUser = ref(null)

// Computed
const isEditMode = computed(() => !!props.user)
const isCurrentUser = computed(() => 
  isEditMode.value && props.user?.id === authService.user.value?.id
)

const isFormValid = computed(() => {
  return form.value.username.trim() && 
         (!showPasswordReset.value || form.value.password.trim()) &&
         (isEditMode.value || form.value.password.trim()) &&
         Object.keys(errors.value).length === 0
})

// Watchers pour validation en temps réel
watch(() => form.value.username, (newUsername) => {
  const usernameErrors = userService.validateUsername(newUsername)
  if (usernameErrors.length > 0) {
    errors.value.username = usernameErrors[0]
  } else {
    delete errors.value.username
  }
})

watch(() => form.value.password, (newPassword) => {
  if (newPassword && newPassword.trim()) {
    const passwordErrors = userService.validatePassword(newPassword)
    if (passwordErrors.length > 0) {
      errors.value.password = passwordErrors[0]
    } else {
      delete errors.value.password
    }
  } else if (!isEditMode.value || showPasswordReset.value) {
    errors.value.password = 'Le mot de passe est requis'
  } else {
    delete errors.value.password
  }
})

// Méthodes
const resetForm = () => {
  form.value = {
    username: '',
    password: '',
    isAdmin: false,
    isActive: true
  }
  errors.value = {}
  showPassword.value = false
  showPasswordReset.value = false
  originalUser.value = null
}


const populateForm = () => {
  if (props.user) {
    originalUser.value = { ...props.user }
    const userData = userService.formatUserForDisplay(props.user)
    form.value = {
      username: userData.username || '',
      password: '',
      isAdmin: userData.isAdmin || false,
      isActive: userData.isActive !== undefined ? userData.isActive : true
    }
  }
}

const generatePassword = () => {
  form.value.password = userService.generateTemporaryPassword()
  showPassword.value = true
}

const validateForm = () => {
  errors.value = {}
  
  // Validation du nom d'utilisateur
  const usernameErrors = userService.validateUsername(form.value.username)
  if (usernameErrors.length > 0) {
    errors.value.username = usernameErrors[0]
  }
  
  // Validation du mot de passe
  if (!isEditMode.value || showPasswordReset.value) {
    if (!form.value.password || !form.value.password.trim()) {
      errors.value.password = 'Le mot de passe est requis'
    } else {
      const passwordErrors = userService.validatePassword(form.value.password)
      if (passwordErrors.length > 0) {
        errors.value.password = passwordErrors[0]
      }
    }
  }
  
  return Object.keys(errors.value).length === 0
}

const handleSubmit = async () => {
  if (!validateForm()) {
    return
  }
  
  saving.value = true
  
  try {
    const userData = {
      username: form.value.username.trim()
    }
    
    if (!isEditMode.value) {
      // Création d'un nouvel utilisateur
      userData.password = form.value.password
      userData.isAdmin = form.value.isAdmin
      await userService.createUser(userData)
    } else {
      // Mise à jour d'un utilisateur existant
      if (showPasswordReset.value && form.value.password) {
        // Réinitialiser le mot de passe si demandé
        await userService.adminResetPassword(props.user.id, form.value.password)
      }
      
      // Mettre à jour les informations de base
      await userService.updateUser(props.user.id, userData)
      
      // Mettre à jour le statut admin si nécessaire
      const currentIsAdmin = originalUser.value.roles?.includes('ROLE_ADMIN') || false
      if (form.value.isAdmin !== currentIsAdmin) {
        await userService.toggleAdminRole(props.user.id)
      }
      
      // Mettre à jour le statut actif si nécessaire
      if (form.value.isActive !== originalUser.value.isActive) {
        await userService.toggleUserStatus(props.user.id)
      }
    }
    
    emit('success')
    closeModal()
  } catch (error) {
    console.error('Erreur lors de la sauvegarde:', error)
    
    // Gestion des erreurs spécifiques
    if (error.response?.status === 409) {
      errors.value.username = 'Ce nom d\'utilisateur est déjà pris'
    } else if (error.response?.status === 400) {
      // Erreur de validation côté serveur
      const errorMessage = error.response.data?.message || 'Données invalides'
      if (errorMessage.toLowerCase().includes('username')) {
        errors.value.username = errorMessage
      } else if (errorMessage.toLowerCase().includes('password')) {
        errors.value.password = errorMessage
      } else {
        errors.value.general = errorMessage
      }
    } else {
      errors.value.general = 'Une erreur est survenue lors de la sauvegarde'
    }
  } finally {
    saving.value = false
  }
}

const closeModal = () => {
  resetForm()
  emit('close')
}

const formatDate = (dateString) => {
  if (!dateString) return 'N/A'
  try {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateString
  }
}

// Lifecycle
onMounted(() => {
  // Plus besoin de charger les rôles disponibles
})

// Watcher pour remplir le formulaire quand l'utilisateur change
watch(() => props.user, () => {
  if (props.show) {
    populateForm()
  }
}, { immediate: true })

watch(() => props.show, (newShow) => {
  if (newShow) {
    resetForm()
    populateForm()
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.modal-container {
  background: white;
  border-radius: 12px;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 2rem 2rem 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header h3 {
  color: #1f2937;
  margin: 0;
  font-size: 1.5rem;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  color: #6b7280;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.modal-form {
  padding: 1.5rem 2rem 2rem;
}

.form-group {
  margin-bottom: 2rem;
}

.form-label {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
  font-size: 0.95rem;
}

.form-input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
  box-sizing: border-box;
}

.form-input:focus {
  outline: none;
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.form-input.error {
  border-color: #ef4444;
  box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}

.form-input:disabled {
  background: #f9fafb;
  color: #6b7280;
  cursor: not-allowed;
}

.password-input-group {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input-group .form-input {
  padding-right: 6rem;
}

.password-toggle, .generate-password-btn {
  position: absolute;
  right: 0.5rem;
  background: none;
  border: none;
  padding: 0.5rem;
  cursor: pointer;
  border-radius: 4px;
  font-size: 1rem;
  color: #6b7280;
  transition: all 0.2s ease;
}

.password-toggle {
  right: 3rem;
}

.password-toggle:hover, .generate-password-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.password-toggle:disabled, .generate-password-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.error-message {
  color: #ef4444;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.error-message::before {
  content: '⚠️';
}

.form-help {
  color: #6b7280;
  font-size: 0.85rem;
  margin-top: 0.5rem;
  line-height: 1.4;
}

.form-help.warning {
  color: #f59e0b;
  font-weight: 500;
}

/* Rôles */
.roles-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Section admin-toggle */
.admin-toggle {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  border: 1px solid #e5e7eb;
  border-radius: 8px;
  background: #f9fafb;
  transition: all 0.2s ease;
}

.admin-toggle:hover {
  border-color: #d1d5db;
  background: #f3f4f6;
}

.admin-info {
  flex: 1;
}

.admin-label {
  display: block;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.admin-description {
  display: block;
  font-size: 0.85rem;
  color: #6b7280;
}

/* Toggle switch pour le statut */
.status-toggle {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 50px;
  height: 24px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.2s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.2s;
  border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
  background-color: #22c55e;
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(26px);
}

.toggle-switch input:disabled + .toggle-slider {
  opacity: 0.5;
  cursor: not-allowed;
}

.status-info {
  flex: 1;
}

.status-label {
  display: block;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.status-description {
  display: block;
  font-size: 0.85rem;
  color: #6b7280;
}

/* Informations utilisateur */
.user-info-section {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e5e7eb;
}

.user-info-section h4 {
  color: #1f2937;
  margin-bottom: 1rem;
  font-size: 1.1rem;
}

.info-grid {
  display: grid;
  gap: 0.75rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 6px;
}

.info-label {
  font-weight: 500;
  color: #6b7280;
}

.info-value {
  color: #1f2937;
  font-weight: 500;
}

/* Boutons */
.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  text-decoration: none;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #22c55e;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #16a34a;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #4b5563;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover:not(:disabled) {
  background: #d97706;
}

.btn-full {
  width: 100%;
}

.loading-spinner {
  width: 16px;
  height: 16px;
  border: 2px solid transparent;
  border-top: 2px solid currentColor;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}

/* Responsive */
@media (max-width: 768px) {
  .modal-container {
    margin: 1rem;
    max-height: calc(100vh - 2rem);
  }
  
  .modal-header,
  .modal-form {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
  }
  
  .modal-actions {
    flex-direction: column;
  }
  
  .password-input-group .form-input {
    padding-right: 5rem;
  }
  
  .password-toggle {
    right: 2.5rem;
  }
}

@media (max-width: 480px) {
  .modal-header,
  .modal-form {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  .admin-toggle {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .status-toggle {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
}
</style>
