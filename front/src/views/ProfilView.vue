<template>
  <div class="app-background mobile-safe p-3xl">
    <PageHeader title="Profil" />
    
    <div class="container">
      <SectionCard
        v-if="currentUser"
        title="Informations utilisateur"
        variant="glass"
        class="mb-3xl"
      >
        <div class="user-info-content">
          <div class="info-item">
            <strong>Nom d'utilisateur :</strong>
            <span>{{ currentUser.username || 'Non défini' }}</span>
          </div>
          <div class="info-item">
            <strong>Rôle :</strong>
            <span class="badge badge-primary">{{ displayUserRole }}</span>
          </div>
        </div>
      </SectionCard>

      <SectionCard
        v-if="currentUser"
        title="Changer le mot de passe"
        variant="default"
      >
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

          <div v-if="successMessage" class="alert alert-success">
            {{ successMessage }}
          </div>
          <div v-if="errorMessage" class="alert alert-error">
            {{ errorMessage }}
          </div>

          <div class="form-actions">
            <button 
              type="submit" 
              class="btn btn-primary"
              :disabled="isLoading"
            >
              {{ isLoading ? 'Changement en cours...' : 'Changer le mot de passe' }}
            </button>
          </div>
        </form>
      </SectionCard>

      <SectionCard
        v-if="currentUser"
        title="Déconnexion"
        variant="default"
        class="mt-3xl"
      >
        <div class="logout-content">
          <p class="logout-description">
            Vous souhaitez vous déconnecter de votre compte ? Cette action vous redirigera vers la page d'accueil.
          </p>
          <div class="logout-actions">
            <button 
              @click="handleLogout" 
              class="btn btn-error"
              :disabled="isLoggingOut"
            >
              {{ isLoggingOut ? 'Déconnexion en cours...' : 'Se déconnecter' }}
            </button>
          </div>
        </div>
      </SectionCard>
    </div>
  </div>
</template>

<script setup>
import PasswordField from '@/components/forms/PasswordField.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import SectionCard from '@/components/ui/SectionCard.vue'
import { useUserInfo } from '@/composables/useUserInfo.js'
import { usePasswordForm } from '@/composables/usePasswordForm.js'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import authService from '@/services/singleton/authService.js'

const router = useRouter()

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

// État pour la déconnexion
const isLoggingOut = ref(false)

// Fonction de déconnexion
const handleLogout = async () => {
  if (isLoggingOut.value) return
  
  try {
    isLoggingOut.value = true
    await authService.logout()
    
    // Rediriger vers la page d'accueil après déconnexion
    await router.push('/')
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error)
    // Même en cas d'erreur, on peut rediriger car le token est nettoyé côté client
    await router.push('/')
  } finally {
    isLoggingOut.value = false
  }
}
</script>

<style scoped>
.user-info-content {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
}

.info-item {
  display: flex;
  justify-content: start;
  align-items: center;
  gap: 10px;
  padding: var(--spacing-md) 0;
  border-bottom: var(--border-width) solid var(--border-color);
}

.info-item:last-child {
  border-bottom: none;
}

.info-item strong {
  color: var(--text-primary);
  font-weight: var(--font-weight-semibold);
}

.password-form {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-xl);
  max-width: 400px;
}

.form-actions {
  margin-top: var(--spacing-lg);
}

.logout-content {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
  text-align: center;
}

.logout-description {
  color: var(--text-secondary);
  line-height: 1.6;
  margin: 0;
}

.logout-actions {
  display: flex;
  justify-content: center;
}

.btn-danger {
  background-color: #ff6b6b;
  color: white;
  border: none;
  padding: var(--spacing-md) var(--spacing-xl);
  border-radius: var(--border-radius);
  font-weight: var(--font-weight-semibold);
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-danger:hover:not(:disabled) {
  background-color: #ff5252;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(255, 107, 107, 0.3);
}

.btn-danger:disabled {
  background-color: #cccccc;
  color: #666666;
  cursor: not-allowed;
  transform: none;
  box-shadow: none;
}

@media (max-width: 768px) {
  .info-item {
    flex-direction: column;
    gap: var(--spacing-sm);
  }
  
  .password-form {
    max-width: none;
  }
  
  .logout-content {
    gap: var(--spacing-md);
  }
}
</style>
