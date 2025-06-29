
<template>
  <div class="app-background mobile-safe p-3xl">
    <div class="tracker-header">
      <div class="header-content">
        <h1 class="heading-page">Tracker d'Émotions</h1>
        <p class="text-lg text-gray-600 leading-relaxed">
          Suivez vos émotions au quotidien et découvrez vos tendances
        </p>
      </div>
    </div>

    <!-- État de chargement -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p class="text-gray-600">Chargement de vos données...</p>
    </div>

    <!-- État d'erreur -->
    <div v-else-if="error" class="error-state">
      <p class="text-error mb-lg">❌ {{ error }}</p>
      <button @click="loadTrackers" class="btn btn-secondary">
        🔄 Réessayer
      </button>
    </div>

    <!-- Interface principale -->
    <div v-else class="tracker-content">
      <!-- Layout responsive : calendrier + formulaire -->
      <div class="tracker-layout">
        <!-- Calendrier -->
        <div class="calendar-section">
          <EmotionCalendar
            :trackers="currentMonthTrackers"
            :selected-date="selectedDate"
            :loading="loading"
            @day-selected="selectDay"
            @month-changed="changeMonth"
          />
        </div>

        <!-- Formulaire d'ajout (desktop uniquement) -->
        <div class="form-section desktop-only">
          <EmotionFormPlaceholder
            :selected-date="selectedDate"
            :emotions="emotions"
            :categories="categories"
            @emotion-added="handleEmotionAdded"
          />
        </div>
      </div>

      <!-- Bouton flottant mobile -->
      <button 
        class="fab mobile-only"
        @click="openMobileModal"
        :disabled="!selectedDate"
        :title="selectedDate ? 'Ajouter une émotion' : 'Sélectionnez d\'abord une date'"
      >
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
          <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>
      </button>

      <!-- Modal mobile -->
      <div v-if="showMobileModal" class="modal-overlay mobile-only" @click="closeMobileModal">
        <div class="modal-content" @click.stop>
          <div class="modal-header">
            <h3>Ajouter une émotion</h3>
            <button class="modal-close" @click="closeMobileModal">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
                <path d="m18 6-12 12M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
              </svg>
            </button>
          </div>
          <div class="modal-body">
            <EmotionFormPlaceholder
              :selected-date="selectedDate"
              :emotions="emotions"
              :categories="categories"
              @emotion-added="handleEmotionAddedMobile"
            />
          </div>
        </div>
      </div>

      <!-- Résumé des trackers du jour sélectionné -->
      <div v-if="selectedDateTrackers.length > 0" class="glass-card mb-3xl">
        <h3 class="heading-card">
          Émotions du {{ formatDate(selectedDate) }}
          <span class="badge badge-primary ml-2">({{ selectedDateTrackers.length }})</span>
        </h3>
        
        <div class="trackers-list">
          <div
            v-for="tracker in selectedDateTrackers"
            :key="tracker.id"
            class="tracker-item"
          >
            <div 
              class="emotion-indicator"
              :style="{ backgroundColor: tracker.emotion?.categorie?.couleur || '#e5e7eb' }"
            ></div>
            <div class="tracker-info">
              <div class="emotion-name">
                {{ tracker.emotion?.nom || 'Émotion inconnue' }}
              </div>
              <div class="tracker-time">
                {{ formatTime(new Date(tracker.datetime)) }}
              </div>
              <div v-if="tracker.commentaire" class="tracker-comment">
                {{ tracker.commentaire }}
              </div>
            </div>
            <div class="tracker-category">
              {{ tracker.emotion?.categorie?.nom || 'Non catégorisée' }}
            </div>
          </div>
        </div>
      </div>

      <!-- État vide pour le jour sélectionné -->
      <div v-else-if="selectedDate" class="empty-state">
        <div class="text-5xl mb-lg">😌</div>
        <h3 class="heading-small text-gray-700">Aucune émotion enregistrée</h3>
        <p class="text-gray-600">
          Aucune émotion n'a été enregistrée pour le {{ formatDate(selectedDate) }}.
          Utilisez le formulaire ci-dessus pour ajouter votre première émotion de la journée.
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import EmotionCalendar from '@/components/tracker/EmotionCalendar.vue'
import EmotionFormPlaceholder from '@/components/tracker/EmotionFormPlaceholder.vue'
import { useEmotionTracker } from '@/composables/useEmotionTracker.js'

// État du modal mobile
const showMobileModal = ref(false)

// Utilisation du composable
const {
  // État
  trackers,
  emotions,
  categories,
  currentDate,
  selectedDate,
  loading,
  error,
  
  // Computed
  currentMonthTrackers,
  selectedDateTrackers,
  
  // Méthodes
  loadTrackers,
  changeMonth,
  selectDay,
  createTracker,
  
  // Utilitaires
  formatDate,
  formatTime
} = useEmotionTracker()

// Gestion du modal mobile
const openMobileModal = () => {
  if (selectedDate.value) {
    showMobileModal.value = true
    // Empêcher le scroll du body
    document.body.style.overflow = 'hidden'
  }
}

const closeMobileModal = () => {
  showMobileModal.value = false
  // Rétablir le scroll du body
  document.body.style.overflow = ''
}

// Gestion des événements
const handleEmotionAdded = async (emotionData) => {
  try {
    await createTracker(
      emotionData.emotionId,
      emotionData.datetime,
      emotionData.commentaire
    )
  } catch (err) {
    console.error('Erreur lors de l\'ajout de l\'émotion:', err)
  }
}

const handleEmotionAddedMobile = async (emotionData) => {
  try {
    await createTracker(
      emotionData.emotionId,
      emotionData.datetime,
      emotionData.commentaire
    )
    // Fermer le modal après ajout réussi
    closeMobileModal()
  } catch (err) {
    console.error('Erreur lors de l\'ajout de l\'émotion:', err)
  }
}

// Nettoyer le style du body au démontage du composant
import { onUnmounted } from 'vue'
onUnmounted(() => {
  document.body.style.overflow = ''
})
</script>

<style scoped>
.tracker-header {
  text-align: center;
  margin-bottom: var(--spacing-5xl);
}

/* Layout principal */
.tracker-content {
  max-width: 1400px;
  margin: 0 auto;
}

.tracker-layout {
  display: grid;
  grid-template-columns: 1fr 350px;
  gap: var(--spacing-3xl);
  margin-bottom: var(--spacing-3xl);
}

.calendar-section {
  min-height: 600px;
}

.form-section {
  display: flex;
  flex-direction: column;
}

/* Trackers du jour */
.trackers-list {
  display: flex;
  flex-direction: column;
  gap: var(--spacing-lg);
}

.tracker-item {
  display: flex;
  align-items: center;
  gap: var(--spacing-lg);
  padding: var(--spacing-lg);
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(8px);
  border-radius: var(--border-radius-xl);
  border: var(--border-width) solid rgba(255, 255, 255, 0.3);
  transition: all var(--transition-normal);
}

.tracker-item:hover {
  background: rgba(255, 255, 255, 0.8);
  box-shadow: var(--shadow-md);
  transform: translateY(-2px);
}

.emotion-indicator {
  width: 24px;
  height: 24px;
  border-radius: var(--border-radius-full);
  flex-shrink: 0;
}

.tracker-info {
  flex: 1;
}

.emotion-name {
  font-weight: var(--font-weight-semibold);
  color: var(--gray-800);
  margin-bottom: var(--spacing-xs);
}

.tracker-time {
  font-size: var(--font-size-sm);
  color: var(--gray-600);
  margin-bottom: var(--spacing-xs);
}

.tracker-comment {
  font-size: var(--font-size-sm);
  color: var(--gray-700);
  font-style: italic;
}

.tracker-category {
  background: var(--primary-color);
  color: var(--white);
  padding: var(--spacing-xs) var(--spacing-md);
  border-radius: var(--border-radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

/* Bouton mobile flottant */
.fab {
  position: fixed;
  bottom: 6rem;
  right: var(--spacing-3xl);
  width: 60px;
  height: 60px;
  background: var(--primary-color);
  color: var(--white);
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

.fab:hover:not(:disabled) {
  background: var(--primary-dark);
  transform: scale(1.1);
}

.fab:disabled {
  background: var(--gray-400);
  cursor: not-allowed;
  transform: none;
}

/* Modal mobile */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: flex-end;
  justify-content: center;
  z-index: var(--z-modal-backdrop);
  padding: 0;
}

.modal-content {
  background: var(--white);
  border-radius: var(--border-radius-xl) var(--border-radius-xl) 0 0;
  box-shadow: var(--shadow-xl);
  width: 100%;
  max-height: 85vh;
  overflow-y: auto;
  z-index: var(--z-modal);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: var(--spacing-2xl) var(--spacing-2xl) var(--spacing-lg);
  border-bottom: var(--border-width) solid var(--gray-200);
}

.modal-header h3 {
  margin: 0;
  font-size: var(--font-size-xl);
  font-weight: var(--font-weight-bold);
  color: var(--gray-800);
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
  color: var(--gray-600);
}

.modal-close:hover {
  background: rgba(0, 0, 0, 0.2);
  color: var(--gray-800);
}

.modal-body {
  padding: var(--spacing-lg) var(--spacing-2xl) var(--spacing-3xl);
}

/* Classes utilitaires */
.ml-2 {
  margin-left: var(--spacing-sm);
}

/* Responsive */
@media (max-width: 1024px) {
  .tracker-layout {
    grid-template-columns: 1fr;
    gap: var(--spacing-2xl);
  }
}

@media (max-width: 768px) {
  .tracker-view {
    padding: var(--spacing-xl) var(--spacing-lg);
  }
  
  .tracker-header {
    margin-bottom: var(--spacing-3xl);
  }
  
  .tracker-content {
    padding: 0;
  }
  
  .tracker-layout {
    gap: var(--spacing-lg);
  }
  
  .tracker-item {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-md);
  }
  
  .tracker-info {
    width: 100%;
  }
  
  .fab.mobile-only {
    display: flex;
  }
  
  .desktop-only {
    display: none;
  }
}

@media (max-width: 640px) {
  .tracker-view {
    padding: var(--spacing-lg) var(--spacing-md);
  }
  
  .tracker-header {
    margin-bottom: var(--spacing-2xl);
    padding: 0 var(--spacing-xs);
  }
  
  .tracker-item {
    padding: var(--spacing-md);
  }
  
  .fab {
    bottom: 7rem;
    right: var(--spacing-lg);
    width: 52px;
    height: 52px;
  }
  
  .modal-content {
    max-height: 90vh;
    border-radius: var(--border-radius-lg) var(--border-radius-lg) 0 0;
  }
  
  .modal-header {
    padding: var(--spacing-lg) var(--spacing-lg) var(--spacing-sm);
  }
  
  .modal-header h3 {
    font-size: var(--font-size-lg);
  }
  
  .modal-close {
    width: 36px;
    height: 36px;
  }
  
  .modal-body {
    padding: var(--spacing-sm) var(--spacing-lg) var(--spacing-xl);
  }
}
</style>
