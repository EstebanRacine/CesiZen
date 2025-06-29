<template>
  <div class="emotion-form-container">
    <div class="form-header">
      <h3>Ajouter une émotion</h3>
      <p class="selected-date" v-if="selectedDate">
        {{ formattedSelectedDate }}
      </p>
    </div>
    
    <div class="form-placeholder">
      <div class="placeholder-icon">
        ✨
      </div>
      <h4>Formulaire d'émotion</h4>
      <p>
        Ce composant contiendra le formulaire pour ajouter une émotion 
        à la date sélectionnée avec votre design spécifique.
      </p>
      <div class="placeholder-info">
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
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  padding: 1.5rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
  height: fit-content;
  position: sticky;
  top: 2rem;
}

.form-header {
  margin-bottom: 1.5rem;
  text-align: center;
  border-bottom: 1px solid #e5e7eb;
  padding-bottom: 1rem;
}

.form-header h3 {
  color: #2a5d49;
  margin: 0 0 0.5rem 0;
  font-size: 1.25rem;
  font-weight: 700;
}

.selected-date {
  color: #6b7280;
  margin: 0;
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: capitalize;
}

.form-placeholder {
  text-align: center;
  padding: 2rem 1rem;
  border: 2px dashed rgba(42, 93, 73, 0.3);
  border-radius: 20px;
  background: rgba(255, 255, 255, 0.5);
  backdrop-filter: blur(8px);
}

.placeholder-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.form-placeholder h4 {
  color: #374151;
  margin: 0 0 1rem 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.form-placeholder p {
  color: #6b7280;
  margin: 0 0 1.5rem 0;
  line-height: 1.5;
}

.placeholder-info {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 1rem;
  text-align: left;
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.4);
}

.info-item {
  margin-bottom: 0.5rem;
  font-size: 0.875rem;
  color: #374151;
}

.info-item:last-child {
  margin-bottom: 0;
}

.info-item strong {
  color: #2a5d49;
  font-weight: 600;
}

/* Responsive */
@media (max-width: 768px) {
  .emotion-form-container {
    position: static;
    margin-top: 2rem;
  }
  
  .form-placeholder {
    padding: 1.5rem 1rem;
  }
  
  .placeholder-icon {
    font-size: 2.5rem;
  }
}
</style>
