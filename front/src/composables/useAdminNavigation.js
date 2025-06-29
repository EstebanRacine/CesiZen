import { ref, computed } from 'vue'
import authService from '@/services/singleton/authService.js'

export function useAdminNavigation() {
  const activeSection = ref('infos')
  
  const isAdmin = computed(() => authService.isAdmin.value)

  const adminSections = [
    {
      id: 'infos',
      title: 'Articles / Infos',
      icon: '📄'
    },
    {
      id: 'users',
      title: 'Utilisateurs',
      icon: '👥'
    },
    {
      id: 'stats',
      title: 'Statistiques',
      icon: '📊'
    },
    {
      id: 'settings',
      title: 'Paramètres',
      icon: '⚙️'
    }
  ]

  const setActiveSection = (sectionId) => {
    activeSection.value = sectionId
  }

  return {
    activeSection,
    isAdmin,
    adminSections,
    setActiveSection
  }
}
