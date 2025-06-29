import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import authService from './services/singleton/authService.js'
import capacitorService from './plugins/capacitor.js'

const app = createApp(App)

// Initialiser l'authentification au démarrage de l'application
authService.initializeAuth()

// Initialiser Capacitor pour les fonctionnalités mobiles
capacitorService.initialize()

// Configurer les gestionnaires pour les applications mobiles
capacitorService.setupBackButtonHandler(router)
capacitorService.setupAppStateHandlers()

// Rendre les services disponibles globalement
app.config.globalProperties.$authService = authService
app.config.globalProperties.$capacitor = capacitorService

// Garde de route pour vérifier l'authentification
router.beforeEach((to, from, next) => {
  // Vérifier si la route nécessite une authentification
  if (to.matched.some(record => record.meta.requiresAuth)) {
    if (!authService.isAuthenticated.value) {
      // Ouvrir la modale de connexion au lieu de rediriger
      authService.openLoginModal('Vous devez être connecté pour accéder à cette page.')
      next(false) // Empêcher la navigation
      return
    }
  }

  // Vérifier les rôles requis
  if (to.matched.some(record => record.meta.requiresRoles)) {
    const requiredRoles = to.meta.requiresRoles
    if (!authService.hasRole(requiredRoles)) {
      authService.openLoginModal('Vous n\'avez pas les permissions nécessaires pour accéder à cette page.')
      next(false)
      return
    }
  }

  next()
})

app.use(createPinia())
app.use(router)

app.mount('#app')
