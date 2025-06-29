<script setup>
import { Home, User, Newspaper, Laugh, Settings } from 'lucide-vue-next'
import logoUrl from '@/assets//Logo/CESIZENLOGO_no_text.png'
import authService from '@/services/singleton/authService.js'

// Définir vos items de navigation avec leurs exigences (authentification et/ou rôles)
const items = [
  { name: 'Articles', icon: Newspaper, link: '/', requiresAuth: false, roles: [] },
  { name: 'Tracker', icon: Laugh, link: '/tracker', requiresAuth: true, roles: [] },
  { name: 'Profil', icon: User, link: '/profil', requiresAuth: false, roles: [] },
  { name: 'Administration', icon: Settings, link: '/admin', requiresAuth: true, roles: ['ROLE_ADMIN'] },
]

// Fonction pour déterminer si un élément de navigation doit être affiché
const shouldShowItem = (item) => {
  if (!item) {
    console.warn("shouldShowItem: item est null ou undefined.");
    return false;
  }

  if (item.requiresAuth && !authService.isAuthenticated.value) {
    return false;
  }

  if (item.roles && item.roles.length > 0 && !authService.hasRole(item.roles)) {
    return false;
  }

  return true;
}

// Fonction pour gérer la déconnexion
const handleLogout = async () => {
  try {
    await authService.logout();
    console.log('Déconnexion réussie');
    window.location.href = '/';
  } catch (error) {
    console.error('Erreur lors de la déconnexion:', error);
  }
};

// Fonction pour ouvrir la modale de connexion
const handleLogin = () => {
  authService.openLoginModal('Veuillez vous connecter pour accéder à plus de fonctionnalités.');
};

// Fonction pour afficher le rôle de l'utilisateur de manière lisible
const getUserRoleDisplay = () => {
  const roles = authService.getUserRoles();
  if (roles.includes('ROLE_ADMIN')) {
    return 'Administrateur';
  }
  if (roles.includes('ROLE_USER')) {
    return 'Utilisateur';
  }
  return 'Membre';
};

</script>

<template>
  <aside class="sidebar">
    <div class="brand">
      <img :src="logoUrl" alt="Logo" class="logo" />
      <span class="app-name">CesiZen</span>
    </div>

    <!-- Section utilisateur connecté -->
    <div v-if="authService.isAuthenticated.value" class="user-info">
      <div class="user-details">
        <div class="user-avatar">
          <User :size="20" />
        </div>
        <div class="user-text">
          <p class="user-name">{{ authService.getCurrentUser()?.username || 'Utilisateur' }}</p>
          <p class="user-role">{{ getUserRoleDisplay() }}</p>
        </div>
      </div>
    </div>

    <nav class="nav">
      <template v-for="item in items" :key="item.name">
        <router-link
          v-if="item && shouldShowItem(item)"
          :to="item.link"
          class="nav-item"
          active-class="active"
          exact
        >
          <span class="icon">
            <component :is="item.icon"/>
          </span>
          <span class="label">{{ item.name }}</span>
        </router-link>
      </template>

      <!-- Boutons de connexion/déconnexion cachés sur mobile -->
      <button
        v-if="!authService.isAuthenticated.value"
        @click="handleLogin"
        class="nav-item connect-button desktop-only"
      >
        <span class="icon">
          <User />
        </span>
        <span class="label">Se connecter</span>
      </button>

      <!-- Affiché si l'utilisateur EST authentifié -->
      <button
        v-else
        @click="handleLogout"
        class="nav-item disconnect-button desktop-only"
      >
        <span class="icon">
          <User />
        </span>
        <span class="label">Se déconnecter</span>
      </button>
    </nav>
  </aside>
</template>

<style scoped>
.sidebar {
  display: flex;
  flex-direction: column;
  width: 240px;
  min-height: 100dvh;

  z-index: 1000;
  background: linear-gradient(180deg, #2a5d49 0%, #89c997 100%);
  color: #f4fff5; /* blanc cassé */
  padding: 1.5rem 1.2rem;
  transition: all 0.3s ease;
}

.brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  margin-bottom: 2.5rem;
}

.logo {
  width: 50px;
  height: 50px;
  border-radius: 10px;
}

.app-name {
  font-size: 1.4rem;
  font-weight: 700;
  color: #fade6d; /* jaune clair */
  text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.nav {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  color: #f4fff5;
  font-weight: 600;
  text-decoration: none;
  padding: 0.7rem 1rem;
  border-radius: 12px;
  transition: background 0.25s ease, box-shadow 0.25s ease;
  background: none;
  border: none;
  cursor: pointer;
  width: 100%; /* S'assure que le bouton prend toute la largeur comme un lien */
  text-align: left; /* Aligne le texte à gauche */
}

.nav-item:hover {
  background: #fade6d; /* jaune clair */
  color: #2a5d49; /* vert foncé */
  box-shadow: 0 4px 10px rgba(250, 222, 109, 0.5);
}

.icon {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  border-radius: 50%;
  background: rgba(255 255 255 / 0.2);
  box-shadow: inset 0 0 8px rgba(255 255 255 / 0.15);
  transition: background 0.25s ease;
}

.nav-item:hover .icon {
  background: #2a5d49; /* vert foncé */
  box-shadow: none;
}

.icon > * {
  width: 22px;
  height: 22px;
  stroke: #fade6d; /* jaune clair */
  transition: stroke 0.25s ease;
}

.nav-item:hover .icon > * {
  stroke: #f4fff5; /* blanc cassé */
}

.nav-item.active {
  background: #00bf6a; /* Vert vif pour actif */
  color: #fff;
  box-shadow: 0 4px 12px rgba(0, 191, 106, 0.7);
}

.nav-item.active .icon {
  background: #f4fff5; /* Fond clair sur icône active */
}

.nav-item.active .icon > * {
  stroke: #00bf6a; /* Icône vert vif */
}

/* Styles spécifiques pour le bouton "Se connecter" (jaune) */
.connect-button {
  background: #fade6d; /* Jaune clair */
  color: #2a5d49; /* Vert foncé */
  margin-top: 1.5rem; /* Espace au-dessus des boutons de connexion/déconnexion */
  font-weight: 700; /* Plus gras */
  box-shadow: 0 4px 10px rgba(250, 222, 109, 0.5);
}

.connect-button:hover {
  background: #ffe88d; /* Jaune un peu plus clair au hover */
  color: #2a5d49; /* Reste vert foncé */
  box-shadow: 0 6px 15px rgba(250, 222, 109, 0.6);
}

.connect-button .icon {
  background: rgba(42, 93, 73, 0.2); /* Fond d'icône plus sombre */
  box-shadow: inset 0 0 8px rgba(42, 93, 73, 0.15);
}

.connect-button:hover .icon {
  background: #2a5d49; /* Vert foncé au hover */
}

.connect-button .icon > * {
  stroke: #2a5d49; /* Icône vert foncé */
}

.connect-button:hover .icon > * {
  stroke: #f4fff5; /* Icône blanc cassé au hover */
}


/* Styles spécifiques pour le bouton "Se déconnecter" */
.disconnect-button {
  background: #ff6b6b; /* Un rouge pour la déconnexion */
  color: #f4fff5;
  margin-top: 1.5rem;
  font-weight: 700;
  box-shadow: 0 4px 10px rgba(255, 107, 107, 0.5);
}

.disconnect-button:hover {
  background: #ff8e8e;
  box-shadow: 0 6px 15px rgba(255, 107, 107, 0.6);
}

.disconnect-button .icon {
  background: rgba(244, 255, 245, 0.1);
}

.disconnect-button:hover .icon {
  background: #f4fff5;
}

.disconnect-button .icon > * {
  stroke: #f4fff5;
}

.disconnect-button:hover .icon > * {
  stroke: #ff6b6b;
}

/* Section utilisateur connecté */
.user-info {
  margin-bottom: 2rem;
  padding: 1rem;
  background: rgba(255, 255, 255, 0.08);
  border-radius: 12px;
  border: 1px solid rgba(250, 222, 109, 0.2);
}

.user-details {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.user-avatar {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #fade6d;
  color: #2a5d49;
  flex-shrink: 0;
}

.user-text {
  flex: 1;
  min-width: 0; /* Permet à flex de réduire */
}

.user-name {
  font-size: 0.9rem;
  font-weight: 600;
  color: #f4fff5;
  margin: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  font-size: 0.75rem;
  color: #fade6d;
  margin: 0;
  margin-top: 2px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Classe pour cacher des éléments sur mobile */
.desktop-only {
  display: flex;
}

/* Responsive */
@media (max-width: 768px) {
  .sidebar {
    flex-direction: row;
    width: 100%;
    height: 60px;
    padding: 0 1rem;
    position: fixed;
    bottom: 0;
    left: 0;
    min-height: 0;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(90deg, #2a5d49 0%, #89c997 100%);
    border-right: none;
  }

  .brand {
    display: none;
  }

  .user-info {
    display: none; /* Cache les informations utilisateur sur mobile */
  }

  .desktop-only {
    display: none !important; /* Cache les boutons de connexion/déconnexion sur mobile */
  }

  .nav {
    flex-direction: row;
    gap: 1.5rem;
    width: 100%;
    justify-content: space-around;
  }

  .nav-item{
    justify-content: center;
  }

  .label {
    display: none;
  }

  .icon > * {
    width: 26px;
    height: 26px;
    stroke: #fade6d;
  }
}
</style>