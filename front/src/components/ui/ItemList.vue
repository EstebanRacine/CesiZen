<template>
  <div class="item-list">
    <div
      v-for="item in items"
      :key="item.id"
      class="list-item"
      :class="{ 'list-item-clickable': clickable }"
      @click="handleItemClick(item)"
    >
      <div 
        v-if="showIndicator"
        class="item-indicator"
        :style="{ backgroundColor: getIndicatorColor(item) }"
      ></div>
      
      <div class="item-content">
        <div class="item-primary">
          <slot name="primary" :item="item">
            {{ item.name || item.title || 'Élément' }}
          </slot>
        </div>
        
        <div v-if="$slots.secondary" class="item-secondary">
          <slot name="secondary" :item="item" />
        </div>
        
        <div v-if="$slots.meta" class="item-meta">
          <slot name="meta" :item="item" />
        </div>
      </div>
      
      <div v-if="$slots.actions" class="item-actions">
        <slot name="actions" :item="item" />
      </div>
      
      <div v-if="showCategory && item.category" class="item-category">
        {{ item.category }}
      </div>
    </div>
    
    <div v-if="items.length === 0" class="empty-list">
      <slot name="empty">
        <div class="text-5xl mb-lg">📝</div>
        <p class="text-secondary">Aucun élément à afficher</p>
      </slot>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  items: {
    type: Array,
    default: () => []
  },
  showIndicator: {
    type: Boolean,
    default: true
  },
  showCategory: {
    type: Boolean,
    default: false
  },
  clickable: {
    type: Boolean,
    default: false
  },
  indicatorColorField: {
    type: String,
    default: 'color'
  }
})

const emit = defineEmits(['item-click'])

const getIndicatorColor = (item) => {
  // Essaie plusieurs chemins pour la couleur
  return item[props.indicatorColorField] || 
         item.emotion?.categorie?.couleur || 
         item.category?.color || 
         item.categoryColor ||
         '#e5e7eb'
}

const handleItemClick = (item) => {
  if (props.clickable) {
    emit('item-click', item)
  }
}
</script>

<style scoped>
.item-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
}

.list-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-lg);
  padding: var(--spacing-lg);
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: var(--blur-md);
  border-radius: var(--border-radius-xl);
  border: var(--border-width) solid var(--border-color-light);
  transition: all var(--transition-normal);
}

.list-item:hover {
  background: rgba(255, 255, 255, 0.8);
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.list-item-clickable {
  cursor: pointer;
}

.list-item-clickable:hover {
  background: rgba(255, 255, 255, 0.9);
  box-shadow: var(--shadow-lg);
  transform: translateY(-3px);
}

.item-indicator {
  width: 24px;
  height: 24px;
  border-radius: var(--border-radius-full);
  flex-shrink: 0;
}

.item-content {
  flex: 1;
  min-width: 0; /* Pour permettre l'ellipsis */
}

.item-primary {
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin-bottom: var(--spacing-xs);
}

.item-secondary {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  margin-bottom: var(--spacing-xs);
}

.item-meta {
  font-size: var(--font-size-sm);
  color: var(--text-muted);
  font-style: italic;
}

.item-actions {
  display: flex;
  gap: var(--spacing-sm);
  flex-shrink: 0;
}

.item-category {
  background: var(--primary-color);
  color: var(--text-white);
  padding: var(--spacing-xs) var(--spacing-md);
  border-radius: var(--border-radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  text-transform: uppercase;
  letter-spacing: 0.025em;
  flex-shrink: 0;
}

.empty-list {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: var(--spacing-6xl) var(--spacing-3xl);
  text-align: center;
  color: var(--text-secondary);
}

@media (max-width: 768px) {
  .list-item {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-md);
    padding: var(--spacing-md);
  }
  
  .item-content {
    width: 100%;
  }
  
  .item-actions {
    width: 100%;
    justify-content: flex-end;
  }
}
</style>
