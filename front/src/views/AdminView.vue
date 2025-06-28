<template>
  <div class="admin-view">
    <h1>Administration</h1>
    <p>Page d'administration - À compléter</p>
    <p><em>Cette page nécessite une authentification et le rôle administrateur.</em></p>
    
    <div v-if="isAdmin" class="admin-content">
      <h2>Panneau d'administration</h2>
      <div class="admin-sections">
        <div class="admin-card">
          <h3>Gestion des utilisateurs</h3>
          <p>À implémenter</p>
        </div>
        <div class="admin-card">
          <h3>Gestion des articles</h3>
          <p>À implémenter</p>
        </div>
        <div class="admin-card">
          <h3>Statistiques</h3>
          <p>À implémenter</p>
        </div>
      </div>
    </div>
    
    <div v-else class="access-denied">
      <p>⚠️ Accès refusé - Droits administrateur requis</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import authService from '@/services/singleton/authService.js'

// Vérifier si l'utilisateur est admin
const isAdmin = computed(() => authService.isAdmin.value)
</script>

<style scoped>
.admin-view {
  padding: 2rem;
}

h1 {
  color: #2a5d49;
  margin-bottom: 1rem;
}

.admin-content {
  margin-top: 2rem;
}

.admin-content h2 {
  color: #2a5d49;
  margin-bottom: 1.5rem;
  font-size: 1.3rem;
}

.admin-sections {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 1.5rem;
}

.admin-card {
  background: #f8fffe;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.admin-card h3 {
  color: #2a5d49;
  margin-bottom: 0.5rem;
  font-size: 1.1rem;
}

.admin-card p {
  color: #666;
  margin: 0;
}

.access-denied {
  background: #ffe6e6;
  color: #d63031;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #fab1a0;
  margin-top: 2rem;
  text-align: center;
}

em {
  color: #666;
  font-size: 0.9rem;
}
</style>
