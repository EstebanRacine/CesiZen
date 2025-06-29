<template>
  <Teleport to="body">
    <div v-if="modelValue" class="modal-overlay" @click="closeModal">
      <div 
        class="modal-content"
        :class="[
          `modal-${size}`,
          { 'modal-mobile': isMobile }
        ]"
        @click.stop
      >
        <div class="modal-header">
          <h3 class="modal-title">{{ title }}</h3>
          <button class="modal-close" @click="closeModal" :title="closeButtonTitle">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="m18 6-12 12M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
          </button>
        </div>
        <div class="modal-body">
          <slot />
        </div>
        <div v-if="$slots.footer" class="modal-footer">
          <slot name="footer" />
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    required: true
  },
  size: {
    type: String,
    default: 'md',
    validator: value => ['sm', 'md', 'lg', 'xl'].includes(value)
  },
  isMobile: {
    type: Boolean,
    default: false
  },
  closeButtonTitle: {
    type: String,
    default: 'Fermer'
  }
})

const emit = defineEmits(['update:modelValue', 'close'])

const closeModal = () => {
  emit('update:modelValue', false)
  emit('close')
}

// Gestion du scroll du body
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = ''
  }
})
</script>

<style scoped>
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: var(--z-modal-backdrop);
  padding: var(--spacing-lg);
}

.modal-content {
  background: var(--bg-primary);
  border-radius: var(--border-radius-xl);
  box-shadow: var(--shadow-2xl);
  max-height: 85vh;
  overflow-y: auto;
  z-index: var(--z-modal);
  width: 100%;
}

.modal-sm { max-width: 400px; }
.modal-md { max-width: 500px; }
.modal-lg { max-width: 700px; }
.modal-xl { max-width: 900px; }

.modal-mobile {
  margin: 0;
  padding: 0;
  max-height: 85vh;
  border-radius: var(--border-radius-xl) var(--border-radius-xl) 0 0;
  align-self: flex-end;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-2xl) var(--spacing-2xl) var(--spacing-lg);
  border-bottom: var(--border-width) solid var(--border-color);
}

.modal-title {
  margin: 0;
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--text-primary);
}

.modal-close {
  background: rgba(0, 0, 0, 0.1);
  border: none;
  border-radius: var(--border-radius-full);
  width: 40px;
  height: 40px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all var(--transition-normal);
  color: var(--text-secondary);
}

.modal-close:hover {
  background: rgba(0, 0, 0, 0.2);
  color: var(--text-primary);
}

.modal-body {
  padding: var(--spacing-lg) var(--spacing-2xl);
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: var(--spacing-md);
  padding: var(--spacing-lg) var(--spacing-2xl) var(--spacing-2xl);
  border-top: var(--border-width) solid var(--border-color);
}

@media (max-width: 768px) {
  .modal-overlay {
    padding: 0;
    align-items: flex-end;
  }
  
  .modal-content {
    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
    max-height: 90vh;
  }
  
  .modal-header {
    padding: var(--spacing-lg) var(--spacing-lg) var(--spacing-sm);
  }
  
  .modal-title {
    font-size: var(--font-size-lg);
  }
  
  .modal-close {
    width: 36px;
    height: 36px;
  }
  
  .modal-body {
    padding: var(--spacing-sm) var(--spacing-lg);
  }
  
  .modal-footer {
    padding: var(--spacing-sm) var(--spacing-lg) var(--spacing-xl);
  }
}
</style>
