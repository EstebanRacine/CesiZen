<template>
  <div class="profil-view">
    <h1>Profil</h1>
    <p>Page du profil utilisateur - À compléter</p>
    <p><em>Cette page nécessite une authentification.</em></p>
    
    <div v-if="currentUser" class="user-info">
      <h2>Informations utilisateur</h2>
      <p><strong>Nom d'utilisateur :</strong> {{ currentUser.username || 'Non défini' }}</p>
      <p><strong>Email :</strong> {{ currentUser.email || 'Non défini' }}</p>
      <p><strong>Rôles :</strong> {{ userRoles.join(', ') || 'Aucun' }}</p>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import authService from '@/services/singleton/authService.js'

// Informations de l'utilisateur connecté
const currentUser = computed(() => authService.getCurrentUser())
const userRoles = computed(() => authService.getUserRoles())
</script>

<style scoped>
.profil-view {
  padding: 2rem;
}

h1 {
  color: #2a5d49;
  margin-bottom: 1rem;
}

.user-info {
  background: #f8fffe;
  padding: 1.5rem;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  margin-top: 2rem;
}

.user-info h2 {
  color: #2a5d49;
  margin-bottom: 1rem;
  font-size: 1.2rem;
}

.user-info p {
  margin: 0.5rem 0;
}

em {
  color: #666;
  font-size: 0.9rem;
}
</style>
