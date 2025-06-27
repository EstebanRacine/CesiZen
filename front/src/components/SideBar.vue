<script setup>
import { Home, User, Mail, Settings } from 'lucide-vue-next'
import logoUrl from '@/assets//Logo/CESIZENLOGO_no_text.png'

const items = [
  { name: 'Accueil', icon: Home, link: '/' },
  { name: 'Profil', icon: User, link: '/about' },
  { name: 'Messages', icon: Mail, link: '#' },
  { name: 'Paramètres', icon: Settings, link: '#' },
]

</script>

<template>
  <aside class="sidebar">
    <div class="brand">
      <img :src="logoUrl" alt="Logo" class="logo" />
      <span class="app-name">CesiZen</span>
    </div>

    <nav class="nav">
      <router-link
        v-for="item in items"
        :key="item.name"
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

  .nav {
    flex-direction: row;
    gap: 1.5rem;
    width: 100%;
    justify-content: space-around;
  }

  .label {
    display: none;
  }

  .icon > * {
    width: 26px;
    height: 26px;
    stroke: #fade6d;
  }

  /* .nav-item:hover {
    background: transparent;
    color: #fade6d;
  }

  .nav-item:hover .icon {
    background: transparent;
  } */
}

</style>
