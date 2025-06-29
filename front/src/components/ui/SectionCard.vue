<template>
  <div 
    class="section-card"
    :class="[
      `section-${variant}`,
      { 'section-clickable': clickable }
    ]"
    @click="handleClick"
  >
    <div v-if="title || $slots.header" class="section-header">
      <div v-if="title" class="section-title">
        <h3 class="heading-card">{{ title }}</h3>
        <p v-if="subtitle" class="section-subtitle text-secondary">{{ subtitle }}</p>
      </div>
      <div v-if="$slots.header" class="section-header-content">
        <slot name="header" />
      </div>
      <div v-if="$slots.actions" class="section-actions">
        <slot name="actions" />
      </div>
    </div>
    
    <div class="section-content">
      <slot />
    </div>
    
    <div v-if="$slots.footer" class="section-footer">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    default: null
  },
  subtitle: {
    type: String,
    default: null
  },
  variant: {
    type: String,
    default: 'default',
    validator: value => ['default', 'glass', 'elevated'].includes(value)
  },
  clickable: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['click'])

const handleClick = () => {
  if (props.clickable) {
    emit('click')
  }
}
</script>

<style scoped>
.section-card {
  border-radius: var(--border-radius-xl);
  transition: all var(--transition-normal);
  overflow: hidden;
}

.section-default {
  background: var(--bg-primary);
  border: var(--border-width) solid var(--border-color);
  box-shadow: var(--shadow-md);
}

.section-glass {
  background: var(--bg-glass);
  backdrop-filter: var(--blur-md);
  border: var(--border-width) solid var(--border-color-light);
  box-shadow: var(--shadow-glass);
}

.section-elevated {
  background: var(--bg-primary);
  box-shadow: var(--shadow-lg);
  border: none;
}

.section-clickable {
  cursor: pointer;
}

.section-clickable:hover {
  transform: translateY(-2px);
}

.section-default:hover {
  box-shadow: var(--shadow-lg);
}

.section-glass:hover {
  background: var(--bg-glass-strong);
  box-shadow: var(--shadow-lg);
}

.section-elevated:hover {
  box-shadow: var(--shadow-xl);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: var(--spacing-lg);
  padding: var(--spacing-3xl) var(--spacing-3xl) var(--spacing-lg);
  border-bottom: var(--border-width) solid var(--border-color);
}

.section-title {
  flex: 1;
  min-width: 0;
}

.section-subtitle {
  margin-top: var(--spacing-sm);
  font-size: var(--font-size-sm);
}

.section-header-content {
  flex: 1;
}

.section-actions {
  flex-shrink: 0;
  display: flex;
  gap: var(--spacing-sm);
}

.section-content {
  padding: var(--spacing-lg) var(--spacing-3xl);
}

.section-footer {
  padding: var(--spacing-lg) var(--spacing-3xl) var(--spacing-3xl);
  border-top: var(--border-width) solid var(--border-color);
  background: var(--bg-secondary);
}

/* Variante sans header */
.section-card:not(:has(.section-header)) .section-content {
  padding: var(--spacing-3xl);
}

@media (max-width: 768px) {
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: var(--spacing-md);
    padding: var(--spacing-2xl) var(--spacing-lg) var(--spacing-lg);
  }
  
  .section-content {
    padding: var(--spacing-lg);
  }
  
  .section-footer {
    padding: var(--spacing-lg) var(--spacing-lg) var(--spacing-2xl);
  }
  
  .section-card:not(:has(.section-header)) .section-content {
    padding: var(--spacing-2xl) var(--spacing-lg);
  }
  
  .section-actions {
    justify-content: flex-end;
  }
}
</style>
