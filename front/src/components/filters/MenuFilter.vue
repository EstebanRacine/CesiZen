<template>
  <div class="menu-filter">
    <div v-if="loadingMenus" class="loading-filters">
      <div class="loading-spinner-small"></div>
      <span>Chargement...</span>
    </div>

    <div v-else-if="menuError" class="filter-error">
      <span>{{ menuError }}</span>
      <button @click="$emit('retry-menus')" class="retry-btn-small">
        🔄
      </button>
    </div>

    <div v-else class="filter-buttons">
      <button
        v-for="menu in menusWithAll"
        :key="menu.id || 'all'"
        @click="$emit('menu-selected', menu.id)"
        :class="[
          'filter-btn',
          { 'active': isMenuSelected(menu.id) }
        ]"
        :title="menu.nom"
      >
        <component :is="getMenuIcon(menu.icone)" :size="16" />
        <span class="menu-name">{{ menu.nom }}</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import * as LucideIcons from 'lucide-vue-next'

const props = defineProps({
  menusWithAll: {
    type: Array,
    required: true
  },
  selectedMenuId: {
    type: [Number, null],
    required: true
  },
  loadingMenus: {
    type: Boolean,
    default: false
  },
  menuError: {
    type: String,
    default: null
  },
  isMenuSelected: {
    type: Function,
    required: true
  }
})

defineEmits(['menu-selected', 'retry-menus'])

// Fonction pour obtenir l'icône Lucide à partir du nom
const getMenuIcon = (iconeName) => {
  if (!iconeName) return LucideIcons.FileText
  
  // Convertir le nom en PascalCase pour Lucide
  const pascalCaseName = iconeName
    .split('-')
    .map(word => word.charAt(0).toUpperCase() + word.slice(1))
    .join('')
  
  return LucideIcons[pascalCaseName] || LucideIcons.FileText
}
</script>

<style scoped>
.menu-filter {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 2rem;
  margin-bottom: 1.5rem;
}

.loading-filters {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #6c757d;
  font-size: 0.85rem;
}

.loading-spinner-small {
  width: 14px;
  height: 14px;
  border: 2px solid #e9ecef;
  border-top: 2px solid #2a5d49;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.filter-error {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  color: #dc2626;
  font-size: 0.85rem;
}

.retry-btn-small {
  background: none;
  border: none;
  color: #2a5d49;
  cursor: pointer;
  font-size: 0.9rem;
  padding: 0.25rem;
  border-radius: 4px;
  transition: background-color 0.2s ease;
}

.retry-btn-small:hover {
  background: rgba(42, 93, 73, 0.1);
}

.filter-buttons {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  align-items: center;
}

.filter-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.5rem 0.75rem;
  background: #f8f9fa;
  border: 1px solid #e9ecef;
  border-radius: 6px;
  color: #495057;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  min-height: 32px;
}

.filter-btn:hover {
  background: rgba(42, 93, 73, 0.05);
  border-color: rgba(42, 93, 73, 0.3);
}

.filter-btn.active {
  background: #2a5d49;
  color: white;
  border-color: #2a5d49;
}

.menu-name {
  font-weight: 500;
  white-space: nowrap;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
  .menu-filter {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .filter-buttons {
    gap: 0.4rem;
  }
  
  .filter-btn {
    padding: 0.4rem 0.6rem;
    font-size: 0.8rem;
  }
}
</style>
