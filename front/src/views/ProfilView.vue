<template>
  <div class="profil-view">
    <h1>Profil</h1>
    
    <div v-if="currentUser" class="user-info">
      <h2>Informations utilisateur</h2>
      <p><strong>Nom d'utilisateur :</strong> {{ currentUser.username || 'Non défini' }}</p>
      <p><strong>Rôle :</strong> {{ displayUserRole }}</p>
    </div>

    <div v-if="currentUser" class="password-change-section">
      <h2>Changer le mot de passe</h2>
      
      <form @submit.prevent="handlePasswordChange" class="password-form">
        <PasswordField
          id="current-password"
          label="Mot de passe actuel"
          v-model="passwordForm.currentPassword"
          :show-password="showCurrentPassword"
          :error-message="validationErrors.currentPassword"
          :has-error="!!validationErrors.currentPassword"
          required
          @toggle-visibility="showCurrentPassword = !showCurrentPassword"
        />

        <PasswordField
          id="new-password"
          label="Nouveau mot de passe"
          v-model="passwordForm.newPassword"
          :show-password="showNewPassword"
          :error-message="validationErrors.newPassword"
          :has-error="!!validationErrors.newPassword"
          required
          @toggle-visibility="showNewPassword = !showNewPassword"
        />

        <PasswordField
          id="confirm-password"
          label="Confirmer le nouveau mot de passe"
          v-model="passwordForm.confirmPassword"
          :show-password="showConfirmPassword"
          :error-message="validationErrors.confirmPassword"
          :has-error="!!validationErrors.confirmPassword"
          required
          @toggle-visibility="showConfirmPassword = !showConfirmPassword"
        />

        <div v-if="successMessage" class="success-message">
          {{ successMessage }}
        </div>
        <div v-if="errorMessage" class="error-message">
          {{ errorMessage }}
        </div>

        <div class="form-actions">
          <button 
            type="submit" 
            class="change-password-btn"
            :disabled="isLoading"
          >
            {{ isLoading ? 'Changement en cours...' : 'Changer le mot de passe' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import PasswordField from '@/components/forms/PasswordField.vue'
import { useUserInfo } from '@/composables/useUserInfo.js'
import { usePasswordForm } from '@/composables/usePasswordForm.js'

const { currentUser, displayUserRole } = useUserInfo()
const {
  passwordForm,
  showCurrentPassword,
  showNewPassword,
  showConfirmPassword,
  validationErrors,
  errorMessage,
  successMessage,
  isLoading,
  handlePasswordChange
} = usePasswordForm()
</script>

<style scoped>
.profil-view {
  padding: 2rem;
  max-width: 800px;
  margin: 0 auto;
}

h1 {
  color: #2a5d49;
  margin-bottom: 1rem;
}

.user-info {
  background: #f8fffe;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  margin-top: 2rem;
}

.user-info h2 {
  color: #2a5d49;
  margin-bottom: 1rem;
  font-size: 1.2rem;
}

.user-info p {
  margin: 0.5rem 0;
}

.password-change-section {
  background: #f8fffe;
  padding: 2rem;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  margin-top: 2rem;
}

.password-change-section h2 {
  color: #2a5d49;
  margin-bottom: 1.5rem;
  font-size: 1.2rem;
}

.password-form {
  max-width: 400px;
}

.form-actions {
  margin-top: 2rem;
}

.change-password-btn {
  background-color: #2a5d49;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  font-size: 1rem;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.change-password-btn:hover:not(:disabled) {
  background-color: #1d4435;
}

.change-password-btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.error-message {
  color: #e74c3c;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}

.success-message {
  color: #27ae60;
  font-size: 0.875rem;
  margin-top: 0.25rem;
  display: block;
}
</style>
