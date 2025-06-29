
<template>
  <div class="app-background mobile-safe p-3xl">
    <!-- En-tête de page -->
    <PageHeader
      title="Tracker d'Émotions"
      subtitle="Suivez vos émotions au quotidien et découvrez vos tendances"
    />

    <!-- État de chargement -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p class="text-secondary">Chargement de vos données...</p>
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
            :current-date="currentDate"
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
      <FloatingActionButton
        @click="openMobileModal"
        :disabled="!selectedDate"
        :tooltip="selectedDate ? 'Ajouter une émotion' : 'Sélectionnez d\'abord une date'"
      />

      <!-- Modal mobile -->
      <BaseModal
        v-model="showMobileModal"
        title="Ajouter une émotion"
        is-mobile
        @close="closeMobileModal"
      >
        <EmotionFormPlaceholder
          :selected-date="selectedDate"
          :emotions="emotions"
          :categories="categories"
          @emotion-added="handleEmotionAddedMobile"
        />
      </BaseModal>

      <!-- Modal d'édition -->
      <BaseModal
        v-model="showEditModal"
        title="Modifier l'émotion"
        @close="closeEditModal"
      >
        <div v-if="editingTracker" class="edit-tracker-form">
          <div class="tracker-info">
            <div class="emotion-display">
              <div 
                class="emotion-indicator" 
                :style="{ backgroundColor: editingTracker.emotion?.categorie?.couleur }"
              ></div>
              <div class="emotion-details">
                <h4 class="emotion-name">{{ editingTracker.emotion?.nom }}</h4>
                <p class="emotion-category">{{ editingTracker.emotion?.categorie?.nom }}</p>
              </div>
            </div>
          </div>
          
          <div class="form-group">
            <label class="form-label">Heure</label>
            <input 
              v-model="editForm.time"
              type="time"
              class="form-input"
            />
          </div>
          
          <div class="form-group">
            <label class="form-label">Commentaire</label>
            <textarea 
              v-model="editForm.commentaire"
              class="form-textarea"
              placeholder="Décrivez ce que vous ressentez..."
              rows="4"
            ></textarea>
          </div>
          
          <div class="modal-actions">
            <button 
              class="btn btn-secondary"
              @click="closeEditModal"
            >
              Annuler
            </button>
            <button 
              class="btn btn-primary"
              @click="saveTrackerChanges"
            >
              Enregistrer
            </button>
          </div>
        </div>
      </BaseModal>

      <!-- Résumé des trackers du jour sélectionné -->
      <SectionCard
        v-if="selectedDateTrackers.length > 0"
        variant="glass"
        class="mb-3xl"
      >
        <template #header>
          <div class="flex items-center gap-sm">
            <span>Émotions du {{ formatDate(selectedDate) }}</span>
            <span class="badge badge-primary">({{ selectedDateTrackers.length }})</span>
          </div>
        </template>
        
        <ItemList
          :items="selectedDateTrackers"
          :show-indicator="true"
          :show-category="true"
          indicator-color-field="emotion.categorie.couleur"
        >
          <template #primary="{ item }">
            {{ item.emotion?.nom || 'Émotion inconnue' }}
          </template>
          
          <template #secondary="{ item }">
            {{ formatTime(new Date(item.datetime)) }}
          </template>
          
          <template #meta="{ item }">
            <span v-if="item.commentaire">{{ item.commentaire }}</span>
          </template>
          
          <template #actions="{ item }">
            <div class="tracker-actions">
              <span class="category-name">{{ item.emotion?.categorie?.nom || 'Non catégorisée' }}</span>
              <div class="action-buttons">
                <button 
                  class="btn-action btn-edit"
                  @click="openEditModal(item)"
                  title="Modifier"
                >
                  ✏️
                </button>
                <button 
                  class="btn-action btn-delete"
                  @click="confirmDeleteTracker(item)"
                  title="Supprimer"
                >
                  🗑️
                </button>
              </div>
            </div>
          </template>
        </ItemList>
      </SectionCard>

      <!-- État vide pour le jour sélectionné -->
      <EmptyState
        v-else-if="selectedDate"
        icon="😌"
        title="Aucune émotion enregistrée"
        :message="`Aucune émotion n'a été enregistrée pour le ${formatDate(selectedDate)}. Utilisez le formulaire ci-dessus pour ajouter votre première émotion de la journée.`"
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import EmotionCalendar from '@/components/tracker/EmotionCalendar.vue'
import EmotionFormPlaceholder from '@/components/tracker/EmotionFormPlaceholder.vue'
import PageHeader from '@/components/ui/PageHeader.vue'
import FloatingActionButton from '@/components/ui/FloatingActionButton.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import ItemList from '@/components/ui/ItemList.vue'
import SectionCard from '@/components/ui/SectionCard.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import { useEmotionTracker } from '@/composables/useEmotionTracker.js'

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
  updateTracker,
  deleteTracker,
  
  // Utilitaires
  formatDate,
  formatTime
} = useEmotionTracker()

// État du modal mobile
const showMobileModal = ref(false)

// État du modal d'édition
const showEditModal = ref(false)
const editingTracker = ref(null)
const editForm = ref({
  commentaire: '',
  time: ''
})

// Gestion du modal mobile
const openMobileModal = () => {
  if (selectedDate.value) {
    showMobileModal.value = true
  }
}

const closeMobileModal = () => {
  showMobileModal.value = false
}

// Gestion du modal d'édition
const openEditModal = (tracker) => {
  editingTracker.value = tracker
  const trackerDate = new Date(tracker.datetime)
  editForm.value = {
    commentaire: tracker.commentaire || '',
    time: trackerDate.toTimeString().slice(0, 5)
  }
  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editingTracker.value = null
  editForm.value = {
    commentaire: '',
    time: ''
  }
}

const saveTrackerChanges = async () => {
  if (!editingTracker.value) return
  
  try {
    // Construire la nouvelle datetime
    const originalDate = new Date(editingTracker.value.datetime)
    const [hours, minutes] = editForm.value.time.split(':').map(Number)
    
    const newDateTime = new Date(
      originalDate.getFullYear(),
      originalDate.getMonth(),
      originalDate.getDate(),
      hours,
      minutes
    )
  

    const updates = {
      commentaire: editForm.value.commentaire || null,
      datetime: newDateTime // On passe l'objet Date, le formatage se fera dans le composable
    }

    console.log('Mise à jour du tracker:', updates)
    
    await updateTracker(editingTracker.value.id, updates)
    closeEditModal()
  } catch (err) {
    console.error('Erreur lors de la modification du tracker:', err)
  }
}

const confirmDeleteTracker = async (tracker) => {
  if (confirm(`Êtes-vous sûr de vouloir supprimer cette émotion "${tracker.emotion?.nom}" ?`)) {
    try {
      await deleteTracker(tracker.id)
    } catch (err) {
      console.error('Erreur lors de la suppression du tracker:', err)
    }
  }
}

// Gestion des événements
const handleEmotionAdded = async (emotionData) => {
  console.log('Ajout de l\'émotion:', emotionData)
  
  // Construire la datetime en utilisant la date et l'heure sélectionnées
  // Attention : créer une date locale sans conversion UTC
  const [year, month, day] = emotionData.date.split('-').map(Number)
  const [hours, minutes] = emotionData.time.split(':').map(Number)
  
  const datetime = new Date(year, month - 1, day, hours, minutes)
  
  try {
    await createTracker(
      emotionData.emotion.id,
      datetime,
      emotionData.comment
    )
  } catch (err) {
    console.error('Erreur lors de l\'ajout de l\'émotion:', err)
  }
}

const handleEmotionAddedMobile = async (emotionData) => {
  console.log('Ajout de l\'émotion mobile:', emotionData)
  
  // Construire la datetime en utilisant la date et l'heure sélectionnées
  // Attention : créer une date locale sans conversion UTC
  const [year, month, day] = emotionData.date.split('-').map(Number)
  const [hours, minutes] = emotionData.time.split(':').map(Number)
  
  const datetime = new Date(year, month - 1, day, hours, minutes)
  
  try {
    await createTracker(
      emotionData.emotion.id,
      datetime,
      emotionData.comment
    )
    // Fermer le modal après ajout réussi
    closeMobileModal()
  } catch (err) {
    console.error('Erreur lors de l\'ajout de l\'émotion:', err)
  }
}
</script>

<style scoped>
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

/* Tracker Actions */
.tracker-actions {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: var(--spacing-md);
  width: 100%;
}

.category-name {
  flex: 1;
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
}

.action-buttons {
  display: flex;
  gap: var(--spacing-xs);
}

.btn-action {
  background: transparent;
  border: none;
  padding: var(--spacing-xs);
  border-radius: var(--border-radius-md);
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: var(--font-size-sm);
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-edit:hover {
  background: rgba(59, 130, 246, 0.1);
  transform: scale(1.1);
}

.btn-delete:hover {
  background: rgba(239, 68, 68, 0.1);
  transform: scale(1.1);
}

/* Modal d'édition */
.edit-tracker-form {
  padding: var(--spacing-lg);
}

.tracker-info {
  margin-bottom: var(--spacing-2xl);
  padding: var(--spacing-lg);
  background: var(--bg-glass);
  border-radius: var(--border-radius-xl);
  border: var(--border-width) solid var(--border-color);
}

.emotion-display {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
}

.emotion-indicator {
  width: 16px;
  height: 16px;
  border-radius: 50%;
  flex-shrink: 0;
}

.emotion-details {
  flex: 1;
}

.emotion-name {
  font-size: var(--font-size-lg);
  font-weight: var(--font-weight-semibold);
  color: var(--text-primary);
  margin: 0 0 var(--spacing-xs) 0;
}

.emotion-category {
  font-size: var(--font-size-sm);
  color: var(--text-secondary);
  margin: 0;
}

.form-group {
  margin-bottom: var(--spacing-lg);
}

.form-label {
  display: block;
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  margin-bottom: var(--spacing-sm);
  font-size: var(--font-size-sm);
}

.form-input, .form-textarea {
  width: 100%;
  padding: var(--spacing-md);
  border: var(--border-width) solid var(--border-color);
  border-radius: var(--border-radius-lg);
  background: var(--bg-glass);
  backdrop-filter: var(--blur-sm);
  color: var(--text-primary);
  font-size: var(--font-size-sm);
  transition: all 0.3s ease;
}

.form-input:focus, .form-textarea:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 3px rgba(42, 93, 73, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: var(--spacing-md);
  margin-top: var(--spacing-2xl);
  padding-top: var(--spacing-lg);
  border-top: var(--border-width) solid var(--border-color);
}

.btn {
  padding: var(--spacing-md) var(--spacing-xl);
  border-radius: var(--border-radius-lg);
  font-weight: var(--font-weight-medium);
  font-size: var(--font-size-sm);
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
}

.btn-primary:hover {
  background: var(--primary-dark);
  transform: translateY(-1px);
  box-shadow: var(--shadow-lg);
}

.btn-secondary {
  background: var(--bg-glass);
  color: var(--text-primary);
  border: var(--border-width) solid var(--border-color);
}

.btn-secondary:hover {
  background: var(--bg-secondary);
  transform: translateY(-1px);
}

/* Responsive */
@media (max-width: 1024px) {
  .tracker-layout {
    grid-template-columns: 1fr;
    gap: var(--spacing-2xl);
  }
}

@media (max-width: 768px) {
  .tracker-content {
    padding: 0;
  }
  
  .tracker-layout {
    gap: var(--spacing-lg);
  }

  .calendar-section {
    min-height: max-content;
  }
  
  .tracker-actions {
    flex-direction: column;
    align-items: flex-start;
    gap: var(--spacing-sm);
  }
  
  .action-buttons {
    align-self: flex-end;
  }
  
  .edit-tracker-form {
    padding: var(--spacing-md);
  }
}
</style>
