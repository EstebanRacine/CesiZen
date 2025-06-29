<template>
  <div class="access-denied">
    <div class="denied-icon">🚫</div>
    <h2>{{ title }}</h2>
    <p>{{ message }}</p>
    <p v-if="instruction">{{ instruction }}</p>
    <slot name="action"></slot>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Accès refusé'
  },
  message: {
    type: String,
    default: 'Vous devez disposer des droits administrateur pour accéder à cette page.'
  },
  instruction: {
    type: String,
    default: 'Veuillez vous connecter avec un compte administrateur.'
  }
})
</script>

<style scoped>
.access-denied {
  background: #fef2f2;
  color: #dc2626;
  padding: 3rem 2rem;
  border-radius: 12px;
  border: 1px solid #fecaca;
  text-align: center;
  margin-top: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.denied-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.access-denied h2 {
  color: #dc2626;
  margin-bottom: 1rem;
  font-size: 1.5rem;
}

.access-denied p {
  margin: 0.5rem 0;
  line-height: 1.6;
}
</style>
