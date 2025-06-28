import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
      meta: {
        requiresAuth: false,
        title: 'Accueil'
      }
    },
    {
      path: '/about',
      name: 'about',
      component: () => import('../views/AboutView.vue'),
      meta: {
        requiresAuth: false, 
        title: 'À propos'
      }
    },
    {
      path: '/articles',
      name: 'articles',
      component: () => import('../views/ArticlesView.vue'),
      meta: {
        requiresAuth: false, 
        title: 'Articles'
      }
    },
    {
      path: '/tracker',
      name: 'tracker',
      component: () => import('../views/TrackerView.vue'),
      meta: {
        requiresAuth: true,
        title: 'Tracker'
      }
    },
    {
      path: '/profil',
      name: 'profil',
      component: () => import('../views/ProfilView.vue'),
      meta: {
        requiresAuth: true,
        title: 'Profil'
      }
    },
    {
      path: '/admin',
      name: 'admin',
      component: () => import('../views/AdminView.vue'),
      meta: {
        requiresAuth: true,
        requiresRoles: ['admin'],
        title: 'Administration'
      }
    }
  ],
})

export default router
