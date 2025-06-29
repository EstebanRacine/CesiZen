<template>
  <div class="error-container">
    <div class="error-content">
      <h3>{{ title }}</h3>
      <p>{{ message }}</p>
      <button v-if="showRetry" @click="$emit('retry')" class="retry-button">
        {{ retryText }}
      </button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    default: 'Erreur lors du chargement'
  },
  message: {
    type: String,
    required: true
  },
  showRetry: {
    type: Boolean,
    default: true
  },
  retryText: {
    type: String,
    default: 'Réessayer'
  }
})

defineEmits(['retry'])
</script>

<style scoped>
.error-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 4rem 2rem;
}

.error-content {
  background: #fef2f2;
  color: #dc2626;
  padding: 2rem;
  border-radius: 12px;
  border: 1px solid #fecaca;
  text-align: center;
  max-width: 500px;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.error-content h3 {
  margin: 0 0 1rem 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.error-content p {
  margin: 0 0 1.5rem 0;
  line-height: 1.6;
}

.retry-button {
  background: #dc2626;
  color: white;
  border: none;
  padding: 0.75rem 1.5rem;
  border-radius: 8px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.retry-button:hover {
  background: #b91c1c;
}
</style>
