<template>
  <button 
    class="fab"
    :class="[
      `fab-${variant}`,
      { 'fab-disabled': disabled }
    ]"
    @click="$emit('click')"
    :disabled="disabled"
    :title="tooltip"
  >
    <slot>
      <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
      </svg>
    </slot>
  </button>
</template>

<script setup>
defineProps({
  variant: {
    type: String,
    default: 'primary',
    validator: value => ['primary', 'secondary', 'success', 'error'].includes(value)
  },
  disabled: {
    type: Boolean,
    default: false
  },
  tooltip: {
    type: String,
    default: null
  }
})

defineEmits(['click'])
</script>

<style scoped>
.fab {
  position: fixed;
  bottom: 6rem;
  right: var(--spacing-3xl);
  width: 60px;
  height: 60px;
  border: none;
  border-radius: var(--border-radius-full);
  cursor: pointer;
  box-shadow: var(--shadow-lg);
  display: none;
  align-items: center;
  justify-content: center;
  transition: all var(--transition-normal);
  z-index: var(--z-fixed);
}

.fab-primary {
  background: var(--primary-color);
  color: var(--text-white);
}

.fab-primary:hover:not(:disabled) {
  background: var(--primary-dark);
  transform: scale(1.1);
}

.fab-secondary {
  background: var(--bg-primary);
  color: var(--primary-color);
  border: var(--border-width) solid var(--border-color);
}

.fab-secondary:hover:not(:disabled) {
  background: var(--bg-secondary);
  transform: scale(1.1);
}

.fab-success {
  background: var(--success-color);
  color: var(--text-white);
}

.fab-success:hover:not(:disabled) {
  background: #059669;
  transform: scale(1.1);
}

.fab-error {
  background: var(--error-color);
  color: var(--text-white);
}

.fab-error:hover:not(:disabled) {
  background: #dc2626;
  transform: scale(1.1);
}

.fab-disabled {
  background: var(--text-muted) !important;
  cursor: not-allowed !important;
  transform: none !important;
}

@media (max-width: 768px) {
  .fab {
    display: flex;
    bottom: 7rem;
    right: var(--spacing-lg);
    width: 52px;
    height: 52px;
  }
}
</style>
