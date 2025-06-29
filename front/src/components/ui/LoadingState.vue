<template>
  <div class="loading-state">
    <div class="spinner"></div>
    <p class="text-secondary">{{ message }}</p>
  </div>
</template>

<script setup>
defineProps({
  message: {
    type: String,
    default: 'Chargement...'
  }
})
</script>

<style scoped>
/* Les styles sont maintenant dans utilities.css */
</style>
