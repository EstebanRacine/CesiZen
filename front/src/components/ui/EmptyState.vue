<template>
  <div class="empty-state">
    <div class="empty-icon">
      <slot name="icon">{{ icon }}</slot>
    </div>
    <h3 class="heading-small text-secondary">{{ title }}</h3>
    <p v-if="message" class="text-secondary">{{ message }}</p>
    <div v-if="$slots.action" class="empty-actions">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  icon: {
    type: String,
    default: '📝'
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    default: null
  }
})
</script>

<style scoped>
.empty-icon {
  font-size: var(--font-size-5xl);
  margin-bottom: var(--spacing-lg);
}

.empty-actions {
  margin-top: var(--spacing-lg);
  display: flex;
  gap: var(--spacing-md);
  justify-content: center;
  flex-wrap: wrap;
}
</style>
