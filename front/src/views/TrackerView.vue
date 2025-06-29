
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
            {{ item.emotion?.categorie?.nom || 'Non catégorisée' }}
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
  }
}

const closeMobileModal = () => {
  showMobileModal.value = false
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
}
</style>
