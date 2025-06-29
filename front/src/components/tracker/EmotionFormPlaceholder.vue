<template>
  <div class="emotion-form-container" :style="containerStyles">
    <div class="form-header">
      <h3 class="heading-card">Ajouter une émotion</h3>
      <p class="selected-date text-secondary" v-if="selectedDate">
        {{ formattedSelectedDate }}
      </p>
      
      <!-- Progress indicator -->
      <div class="progress-indicator">
        <div class="step" :class="{ active: currentStep >= 1, completed: currentStep > 1 }">
          <div class="step-number">1</div>
          <span>Catégorie</span>
        </div>
        <div class="progress-line" :class="{ active: currentStep > 1 }"></div>
        <div class="step" :class="{ active: currentStep >= 2, completed: currentStep > 2 }">
          <div class="step-number">2</div>
          <span>Émotion</span>
        </div>
      </div>
    </div>
    
    <div class="form-content">
      <!-- Loading state -->
      <div v-if="loading" class="loading-state">
        <div class="loading-spinner"></div>
        <p class="text-secondary">Chargement des émotions...</p>
      </div>
      
      <!-- Error state -->
      <div v-else-if="error" class="error-state">
        <div class="error-icon">⚠️</div>
        <p class="text-danger">{{ error }}</p>
        <button class="btn btn-primary" @click="loadData">
          Réessayer
        </button>
      </div>
      
      <!-- Form content -->
      <template v-else>
        <!-- Step 1: Sélection de catégorie -->
        <transition name="step" mode="out-in">
          <div v-if="currentStep === 1" key="step1" class="step-content">
            <div class="step-title">
              <h4 class="heading-small">Quelle catégorie d'émotion ressentez-vous ?</h4>
              <p class="text-secondary">Choisissez la famille d'émotion qui correspond le mieux</p>
            </div>
            
            <div class="categories-grid">
              <div 
                v-for="category in categories" 
                :key="category.id"
                class="category-card"
                :class="{ selected: selectedCategory?.id === category.id }"
                @click="selectCategory(category)"
              >
                <div class="category-icon" :style="{ backgroundColor: category.couleur }">
                  {{ category.icone || '🎭' }}
                </div>
                <span class="category-name">{{ category.nom }}</span>
              </div>
            </div>
            
            <div class="step-actions">
              <button 
                class="btn btn-primary"
                :disabled="!selectedCategory"
                @click="nextStep"
              >
                Continuer
                <span class="btn-icon">→</span>
              </button>
            </div>
          </div>
          
          <!-- Step 2: Sélection d'émotion détaillée -->
          <div v-else-if="currentStep === 2" key="step2" class="step-content">
            <div class="step-title">
              <h4 class="heading-small">Précisez votre émotion</h4>
              <p class="text-secondary">
                Catégorie : <span class="category-badge" :style="{ backgroundColor: lightenColor(selectedCategory.couleur, 0.8), color: selectedCategory.couleur }">
                  {{ selectedCategory.nom }}
                </span>
              </p>
            </div>
            
            <div class="emotions-section">
              <!-- Search/Filter bar -->
              <div class="emotions-filter">
                <input 
                  v-model="emotionSearch"
                  type="text"
                  class="search-input"
                  placeholder="Rechercher une émotion..."
                />
                <div class="emotions-count">
                  {{ filteredEmotions.length }} émotion{{ filteredEmotions.length > 1 ? 's' : '' }}
                </div>
              </div>
              
              <!-- Emotions grid with scroll -->
              <div class="emotions-container">
                <div class="emotions-grid">
                  <div 
                    v-for="emotion in paginatedEmotions" 
                    :key="emotion.id"
                    class="emotion-card"
                    :class="{ selected: selectedEmotion?.id === emotion.id }"
                    @click="selectEmotion(emotion)"
                  >
                    <div class="emotion-icon">
                      <img v-if="emotion.icone" :src="getImageUrl(emotion.icone)" :alt="emotion.nom" class="emotion-image" />
                      <span v-else>😊</span>
                    </div>
                    <span class="emotion-name">{{ emotion.nom }}</span>
                  </div>
                </div>
                
                <!-- Load more button if there are more emotions -->
                <div v-if="hasMoreEmotions" class="load-more-container">
                  <button class="btn-load-more" @click="loadMoreEmotions">
                    Voir plus d'émotions ({{ remainingEmotionsCount }})
                  </button>
                </div>
              </div>
            </div>
            
            <!-- Commentaire -->
            <div class="form-group">
              <label class="form-label">Commentaire (optionnel)</label>
              <textarea 
                v-model="comment"
                class="form-textarea"
                placeholder="Décrivez ce que vous ressentez..."
                rows="3"
              ></textarea>
            </div>
            
            <!-- Date et heure -->
            <div class="form-row">
              <div class="form-group">
                <label class="form-label">Date</label>
                <input 
                  v-model="selectedDateString"
                  type="date"
                  class="form-input"
                />
              </div>
              <div class="form-group">
                <label class="form-label">Heure</label>
                <input 
                  v-model="selectedTime"
                  type="time"
                  class="form-input"
                />
              </div>
            </div>
            
            <div class="step-actions">
              <button 
                class="btn btn-secondary"
                @click="previousStep"
              >
                <span class="btn-icon">←</span>
                Retour
              </button>
              <button 
                class="btn btn-primary"
                :disabled="!selectedEmotion"
                @click="submitEmotion"
              >
                Ajouter l'émotion
                <span class="btn-icon">✓</span>
              </button>
            </div>
          </div>
        </transition>
      </template>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch, onMounted } from 'vue'
import EmotionApiService from '@/services/api/EmotionApiService.js'
import CategorieEmotionApiService from '@/services/api/CategorieEmotionApiService.js'

// Services API
const emotionApiService = new EmotionApiService()
const categorieApiService = new CategorieEmotionApiService()

const props = defineProps({
  selectedDate: {
    type: Date,
    default: null
  }
})

const emit = defineEmits(['emotionAdded'])

// State management
const currentStep = ref(1)
const selectedCategory = ref(null)
const selectedEmotion = ref(null)
const comment = ref('')
const selectedTime = ref('')

// Data from API
const categories = ref([])
const emotions = ref([])
const loading = ref(true)
const error = ref(null)

// Emotions filtering and pagination
const emotionSearch = ref('')
const emotionsPerPage = 12
const currentEmotionsPage = ref(1)

// Date management
const selectedDateString = ref('')

// Fonction utilitaire pour formater une date en string local (YYYY-MM-DD)
const formatDateToLocal = (date) => {
  if (!date) return ''
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

// Initialize date and time
if (props.selectedDate) {
  selectedDateString.value = formatDateToLocal(props.selectedDate)
}

// Set current time as default
const now = new Date()
selectedTime.value = now.toTimeString().slice(0, 5)

// Computed properties
const formattedSelectedDate = computed(() => {
  if (!props.selectedDate) return ''
  
  return props.selectedDate.toLocaleDateString('fr-FR', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
})

const filteredEmotions = computed(() => {
  if (!selectedCategory.value) return []
  
  let filtered = emotions.value.filter(emotion => emotion.categorie.id === selectedCategory.value.id)
  
  // Apply search filter
  if (emotionSearch.value.trim()) {
    const searchTerm = emotionSearch.value.toLowerCase().trim()
    filtered = filtered.filter(emotion => 
      emotion.nom.toLowerCase().includes(searchTerm)
    )
  }
  
  return filtered
})

const paginatedEmotions = computed(() => {
  const startIndex = 0
  const endIndex = currentEmotionsPage.value * emotionsPerPage
  return filteredEmotions.value.slice(startIndex, endIndex)
})

const hasMoreEmotions = computed(() => {
  return filteredEmotions.value.length > currentEmotionsPage.value * emotionsPerPage
})

const remainingEmotionsCount = computed(() => {
  return filteredEmotions.value.length - (currentEmotionsPage.value * emotionsPerPage)
})

const containerStyles = computed(() => {
  if (!selectedCategory.value || currentStep.value === 1) return {}
  
  return {
    background: `linear-gradient(135deg, ${lightenColor(selectedCategory.value.couleur, 0.95)}, ${lightenColor(selectedCategory.value.couleur, 0.85)})`
  }
})

// Methods
const loadData = async () => {
  try {
    loading.value = true
    error.value = null
    
    // Charger les catégories et émotions en parallèle
    const [categoriesResponse, emotionsResponse] = await Promise.all([
      categorieApiService.getAllCategories(),
      emotionApiService.getActiveEmotions()
    ])
    
    categories.value = categoriesResponse
    emotions.value = emotionsResponse
    
  } catch (err) {
    console.error('Erreur lors du chargement des données:', err)
    error.value = 'Erreur lors du chargement des données'
  } finally {
    loading.value = false
  }
}

const selectCategory = (category) => {
  selectedCategory.value = category
  // Reset emotions pagination and search when category changes
  currentEmotionsPage.value = 1
  emotionSearch.value = ''
}

const selectEmotion = (emotion) => {
  selectedEmotion.value = emotion
}

const loadMoreEmotions = () => {
  currentEmotionsPage.value += 1
}

const nextStep = () => {
  if (currentStep.value === 1 && selectedCategory.value) {
    currentStep.value = 2
  }
}

const previousStep = () => {
  if (currentStep.value === 2) {
    currentStep.value = 1
    selectedEmotion.value = null
  }
}

const submitEmotion = () => {
  if (!selectedEmotion.value) return
  
  const emotionData = {
    emotion: selectedEmotion.value,
    category: selectedCategory.value,
    comment: comment.value,
    date: selectedDateString.value,
    time: selectedTime.value,
    timestamp: new Date(`${selectedDateString.value}T${selectedTime.value}`)
  }
  
  emit('emotionAdded', emotionData)
  
  // Reset form
  resetForm()
}

const resetForm = () => {
  currentStep.value = 1
  selectedCategory.value = null
  selectedEmotion.value = null
  comment.value = ''
  emotionSearch.value = ''
  currentEmotionsPage.value = 1
  
  // Reset to current date/time
  if (props.selectedDate) {
    selectedDateString.value = formatDateToLocal(props.selectedDate)
  }
  const now = new Date()
  selectedTime.value = now.toTimeString().slice(0, 5)
}

const lightenColor = (color, factor) => {
  // Convert hex to RGB
  const hex = color.replace('#', '')
  const r = parseInt(hex.substr(0, 2), 16)
  const g = parseInt(hex.substr(2, 2), 16)
  const b = parseInt(hex.substr(4, 2), 16)
  
  // Lighten by mixing with white
  const newR = Math.round(r + (255 - r) * factor)
  const newG = Math.round(g + (255 - g) * factor)
  const newB = Math.round(b + (255 - b) * factor)
  
  return `rgb(${newR}, ${newG}, ${newB})`
}

// Initialize component
onMounted(() => {
  loadData()
})

// Gestion des images (pour l'affichage uniquement)
const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) return imagePath;
  // L'image est stockée côté backend dans public/uploads/emotions/
  return `http://localhost:8000${imagePath}`;
}

// Watch for selectedDate changes
watch(() => props.selectedDate, (newDate) => {
  if (newDate) {
    selectedDateString.value = formatDateToLocal(newDate)
  }
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
  transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
  overflow: hidden;
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

/* Progress Indicator */
.progress-indicator {
  display: flex;
  align-items: center;
  justify-content: center;
  margin-top: var(--spacing-lg);
  gap: var(--spacing-md);
}

.step {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: var(--spacing-xs);
  transition: all 0.3s ease;
}

.step-number {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: var(--font-weight-bold);
  font-size: var(--font-size-sm);
  background: var(--bg-secondary);
  color: var(--text-secondary);
  border: 2px solid var(--border-color);
  transition: all 0.3s ease;
}

.step.active .step-number {
  background: var(--primary-color);
  color: white;
  border-color: var(--primary-color);
  transform: scale(1.1);
}

.step.completed .step-number {
  background: var(--success-color);
  border-color: var(--success-color);
}

.step span {
  font-size: var(--font-size-xs);
  color: var(--text-secondary);
  font-weight: var(--font-weight-medium);
}

.step.active span {
  color: var(--primary-color);
  font-weight: var(--font-weight-semibold);
}

.progress-line {
  width: 40px;
  height: 2px;
  background: var(--border-color);
  transition: all 0.3s ease;
}

.progress-line.active {
  background: var(--success-color);
}

/* Form Content */
.form-content {
  min-height: 400px;
}

/* Loading and Error States */
.loading-state, .error-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  text-align: center;
  gap: var(--spacing-lg);
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 3px solid var(--border-color);
  border-top: 3px solid var(--primary-color);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-icon {
  font-size: var(--font-size-4xl);
  margin-bottom: var(--spacing-md);
}

.text-danger {
  color: var(--error-color);
  font-weight: var(--font-weight-medium);
}

.step-content {
  animation: slideIn 0.5s ease-out;
}

.step-title {
  text-align: center;
  margin-bottom: var(--spacing-2xl);
}

.step-title h4 {
  margin-bottom: var(--spacing-sm);
  color: var(--text-primary);
}

.category-badge {
  padding: var(--spacing-xs) var(--spacing-sm);
  border-radius: var(--border-radius-full);
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

/* Categories Grid */
.categories-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: var(--spacing-sm);
  margin-bottom: var(--spacing-2xl);
}

.category-card {
  background: var(--bg-glass);
  border: var(--border-width) solid var(--border-color);
  border-radius: var(--border-radius-xl);
  padding: var(--spacing-md);
  text-align: center;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: var(--blur-sm);
  position: relative;
  overflow: hidden;
  min-height: 80px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.category-card:hover {
  transform: translateY(-2px);
  box-shadow: var(--shadow-md);
  border-color: var(--primary-color);
}

.category-card.selected {
  transform: translateY(-1px) scale(1.02);
  border-color: var(--primary-color);
  box-shadow: 0 4px 16px rgba(42, 93, 73, 0.2);
}

.category-card.selected::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(135deg, rgba(42, 93, 73, 0.1), rgba(42, 93, 73, 0.05));
  pointer-events: none;
}

.category-icon {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: var(--font-size-lg);
  margin-bottom: var(--spacing-xs);
  transition: all 0.3s ease;
}

.category-card:hover .category-icon {
  transform: scale(1.1);
}

.category-name {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  text-align: center;
  line-height: 1.2;
}

/* Emotions Section */
.emotions-section {
  margin-bottom: var(--spacing-2xl);
}

.emotions-filter {
  display: flex;
  align-items: center;
  gap: var(--spacing-md);
  margin-bottom: var(--spacing-lg);
}

.search-input {
  flex: 1;
  padding: var(--spacing-sm) var(--spacing-md);
  border: var(--border-width) solid var(--border-color);
  border-radius: var(--border-radius-lg);
  background: var(--bg-glass);
  backdrop-filter: var(--blur-sm);
  color: var(--text-primary);
  font-size: var(--font-size-sm);
  transition: all 0.3s ease;
}

.search-input:focus {
  outline: none;
  border-color: var(--primary-color);
  box-shadow: 0 0 0 2px rgba(42, 93, 73, 0.1);
}

.emotions-count {
  font-size: var(--font-size-xs);
  color: var(--text-secondary);
  font-weight: var(--font-weight-medium);
  white-space: nowrap;
}

.emotions-container {
  max-height: 300px;
  overflow-y: auto;
  padding-right: var(--spacing-xs);
}

.emotions-container::-webkit-scrollbar {
  width: 4px;
}

.emotions-container::-webkit-scrollbar-track {
  background: var(--bg-secondary);
  border-radius: var(--border-radius-full);
}

.emotions-container::-webkit-scrollbar-thumb {
  background: var(--border-color);
  border-radius: var(--border-radius-full);
}

.emotions-container::-webkit-scrollbar-thumb:hover {
  background: var(--primary-color);
}

/* Emotions Grid */
.emotions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
  gap: var(--spacing-sm);
  margin-bottom: var(--spacing-md);
}

.emotion-card {
  background: var(--bg-glass);
  border: var(--border-width) solid var(--border-color);
  border-radius: var(--border-radius-lg);
  padding: var(--spacing-sm);
  text-align: center;
  cursor: pointer;
  transition: all 0.3s ease;
  position: relative;
  overflow: hidden;
  min-height: 70px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.emotion-card:hover {
  transform: translateY(-1px);
  box-shadow: var(--shadow-sm);
  border-color: var(--primary-color);
}

.emotion-card.selected {
  border-color: var(--primary-color);
  transform: translateY(-1px) scale(1.02);
  box-shadow: 0 2px 8px rgba(42, 93, 73, 0.2);
  background: rgba(42, 93, 73, 0.05);
}

.load-more-container {
  text-align: center;
  margin-top: var(--spacing-md);
}

.btn-load-more {
  background: var(--bg-glass);
  border: var(--border-width) dashed var(--border-color);
  color: var(--text-secondary);
  padding: var(--spacing-sm) var(--spacing-lg);
  border-radius: var(--border-radius-lg);
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
}

.btn-load-more:hover {
  border-color: var(--primary-color);
  color: var(--primary-color);
  background: rgba(42, 93, 73, 0.05);
}

.emotion-icon {
  font-size: var(--font-size-lg);
  margin-bottom: var(--spacing-xs);
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  height: 24px;
}

.emotion-image {
  width: 20px;
  height: 20px;
  object-fit: contain;
  border-radius: var(--border-radius-sm);
}

.emotion-card:hover .emotion-icon {
  transform: scale(1.1);
}

.emotion-name {
  font-size: var(--font-size-xs);
  font-weight: var(--font-weight-medium);
  color: var(--text-primary);
  display: block;
  line-height: 1.2;
  text-align: center;
}

/* Form Elements */
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
  min-height: 80px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: var(--spacing-lg);
}

/* Buttons */
.step-actions {
  display: flex;
  justify-content: space-between;
  gap: var(--spacing-md);
  margin-top: var(--spacing-2xl);
}

.btn {
  padding: var(--spacing-md) var(--spacing-xl);
  border-radius: var(--border-radius-lg);
  font-weight: var(--font-weight-medium);
  font-size: var(--font-size-sm);
  transition: all 0.3s ease;
  cursor: pointer;
  border: none;
  display: flex;
  align-items: center;
  gap: var(--spacing-sm);
  position: relative;
  overflow: hidden;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  transform: none !important;
}

.btn-primary {
  background: var(--primary-color);
  color: white;
  flex: 1;
  justify-content: center;
}

.btn-primary:not(:disabled):hover {
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

.btn-icon {
  transition: all 0.3s ease;
}

.btn:hover .btn-icon {
  transform: translateX(2px);
}

.btn-secondary:hover .btn-icon {
  transform: translateX(-2px);
}

/* Animations */
@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateX(20px);
  }
  to {
    opacity: 1;
    transform: translateX(0);
  }
}

.step-enter-active, .step-leave-active {
  transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.step-enter-from {
  opacity: 0;
  transform: translateX(30px);
}

.step-leave-to {
  opacity: 0;
  transform: translateX(-30px);
}

/* Responsive */
@media (max-width: 768px) {
  .emotion-form-container {
    position: static;
    margin-top: var(--spacing-3xl);
    padding: var(--spacing-lg);
  }
  
  .categories-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: var(--spacing-sm);
  }
  
  .emotions-grid {
    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
    gap: var(--spacing-xs);
  }
  
  .emotions-container {
    max-height: 250px;
  }
  
  .form-row {
    grid-template-columns: 1fr;
    gap: var(--spacing-md);
  }
  
  .step-actions {
    flex-direction: column;
  }
  
  .progress-indicator {
    gap: var(--spacing-sm);
  }
  
  .progress-line {
    width: 30px;
  }
  
  .emotions-filter {
    flex-direction: column;
    align-items: stretch;
    gap: var(--spacing-sm);
  }
}

@media (max-width: 480px) {
  .categories-grid {
    grid-template-columns: repeat(2, 1fr);
  }
  
  .emotions-grid {
    grid-template-columns: repeat(auto-fill, minmax(60px, 1fr));
  }
  
  .category-card {
    min-height: 70px;
  }
  
  .emotion-card {
    min-height: 60px;
  }
  
  .category-icon {
    width: 28px;
    height: 28px;
    font-size: var(--font-size-md);
  }
  
  .emotion-icon {
    height: 20px;
  }
  
  .emotion-image {
    width: 16px;
    height: 16px;
  }
}
</style>
