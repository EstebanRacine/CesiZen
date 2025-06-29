<template>
  <div class="emotion-form-container">
    <div class="form-header">
      <h3 class="heading-card">Ajouter une émotion</h3>
      <p class="selected-date text-secondary" v-if="selectedDate">
        {{ formattedSelectedDate }}
      </p>
    </div>
    
    <div class="form-placeholder">
      <div class="placeholder-icon">
        ✨
      </div>
      <h4 class="heading-small">Formulaire d'émotion</h4>
      <p class="text-secondary">
        Ce composant contiendra le formulaire pour ajouter une émotion 
        à la date sélectionnée avec votre design spécifique.
      </p>
      <div class="placeholder-info glass-card">
        <div class="info-item">
          <strong>Date sélectionnée :</strong>
          {{ selectedDate ? formattedSelectedDate : 'Aucune' }}
        </div>
        <div class="info-item">
          <strong>Heure :</strong>
          Sera sélectionnable dans le formulaire
        </div>
        <div class="info-item">
          <strong>Émotions disponibles :</strong>
          {{ emotions.length }} émotions actives
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  selectedDate: {
    type: Date,
    default: null
  },
  emotions: {
    type: Array,
    default: () => []
  },
  categories: {
    type: Array,
    default: () => []
  }
})

const emit = defineEmits(['emotionAdded'])

const formattedSelectedDate = computed(() => {
  if (!props.selectedDate) return ''
  
  return props.selectedDate.toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})
</script>

<style scoped>
.emotion-form-container {
  background: var(--bg-glass);
  backdrop-filter: var(--blur-lg);
  border-radius: var(--border-radius-3xl);
  padding: var(--spacing-2xl);
  box-shadow: var(--shadow-glass);
  border: var(--border-width) solid var(--border-color-light);
  height: fit-content;
  position: sticky;
  top: var(--spacing-3xl);
}

.form-header {
  margin-bottom: var(--spacing-2xl);
  text-align: center;
  border-bottom: var(--border-width) solid var(--border-color);
  padding-bottom: var(--spacing-lg);
}

.selected-date {
  margin: var(--spacing-sm) 0 0 0;
  font-size: var(--font-size-sm);
  font-weight: var(--font-weight-medium);
  text-transform: capitalize;
}

.form-placeholder {
  text-align: center;
  padding: var(--spacing-3xl) var(--spacing-lg);
  border: var(--border-width-thick) dashed rgba(42, 93, 73, 0.3);
  border-radius: var(--border-radius-2xl);
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: var(--blur-md);
}

.placeholder-icon {
  font-size: var(--font-size-5xl);
  margin-bottom: var(--spacing-lg);
}

.placeholder-info {
  text-align: left;
  padding: var(--spacing-lg);
}

.info-item {
  margin-bottom: var(--spacing-sm);
  font-size: var(--font-size-sm);
  color: var(--text-primary);
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-item strong {
  color: var(--primary-color);
  font-weight: var(--font-weight-semibold);
}

/* Responsive */
@media (max-width: 768px) {
  .emotion-form-container {
    position: static;
    margin-top: var(--spacing-3xl);
  }
  
  .form-placeholder {
    padding: var(--spacing-2xl) var(--spacing-lg);
  }
  
  .placeholder-icon {
    font-size: var(--font-size-4xl);
  }
}
</style>
