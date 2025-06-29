import { computed } from 'vue'
import authService from '@/services/singleton/authService.js'

export function useUserInfo() {
  const currentUser = computed(() => authService.getCurrentUser())
  const userRoles = computed(() => authService.getUserRoles())

  const displayUserRole = computed(() => {
    if (userRoles.value.includes('ROLE_ADMIN')) {
      return 'Administrateur'
    }
    return 'Utilisateur'
  })

  return {
    currentUser,
    userRoles,
    displayUserRole
  }
}
