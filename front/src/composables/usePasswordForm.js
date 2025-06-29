import { ref, reactive } from 'vue'
import authService from '@/services/singleton/authService.js'

export function usePasswordForm() {
  const passwordForm = reactive({
    currentPassword: '',
    newPassword: '',
    confirmPassword: ''
  })

  const showCurrentPassword = ref(false)
  const showNewPassword = ref(false)
  const showConfirmPassword = ref(false)
  const validationErrors = ref({})
  const errorMessage = ref('')
  const successMessage = ref('')
  const isLoading = ref(false)

  const validateForm = () => {
    const errors = {}
    
    if (!passwordForm.currentPassword) {
      errors.currentPassword = 'Le mot de passe actuel est requis'
    }
    
    if (!passwordForm.newPassword) {
      errors.newPassword = 'Le nouveau mot de passe est requis'
    } else if (!/^(?=.*[a-zA-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/.test(passwordForm.newPassword)) {
      errors.newPassword = 'Le mot de passe doit contenir au moins 6 caractères, une lettre, un chiffre et un caractère spécial (@$!%*?&)'
    }
    
    if (!passwordForm.confirmPassword) {
      errors.confirmPassword = 'La confirmation du mot de passe est requise'
    } else if (passwordForm.newPassword !== passwordForm.confirmPassword) {
      errors.confirmPassword = 'Les mots de passe ne correspondent pas'
    }
    
    validationErrors.value = errors
    return Object.keys(errors).length === 0
  }

  const resetMessages = () => {
    errorMessage.value = ''
    successMessage.value = ''
    validationErrors.value = {}
  }

  const resetForm = () => {
    passwordForm.currentPassword = ''
    passwordForm.newPassword = ''
    passwordForm.confirmPassword = ''
    showCurrentPassword.value = false
    showNewPassword.value = false
    showConfirmPassword.value = false
    resetMessages()
  }

  const handlePasswordChange = async () => {
    resetMessages()
    
    if (!validateForm()) {
      return
    }
    
    isLoading.value = true
    
    try {
      await authService.changePassword(
        passwordForm.currentPassword,
        passwordForm.newPassword
      )
      
      resetForm()
      successMessage.value = 'Mot de passe changé avec succès !'
      
      setTimeout(() => {
        successMessage.value = ''
      }, 5000)
      
    } catch (error) {
      console.error('Erreur lors du changement de mot de passe:', error)
      
      if (error.response?.status === 401) {
        errorMessage.value = 'Mot de passe actuel incorrect'
      } else if (error.response?.status === 400) {
        errorMessage.value = error.response.data?.message || 'Données invalides'
      } else if (error.response?.data?.message) {
        errorMessage.value = error.response.data.message
      } else {
        errorMessage.value = 'Une erreur est survenue lors du changement de mot de passe'
      }
    } finally {
      isLoading.value = false
    }
  }

  return {
    passwordForm,
    showCurrentPassword,
    showNewPassword,
    showConfirmPassword,
    validationErrors,
    errorMessage,
    successMessage,
    isLoading,
    handlePasswordChange,
    resetForm
  }
}
