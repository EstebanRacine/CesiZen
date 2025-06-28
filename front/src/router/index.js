import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
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
        requiresRoles: ['ROLE_ADMIN'],
        title: 'Administration'
      }
    }
  ],
})

export default router
