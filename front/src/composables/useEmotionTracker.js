import { ref, computed, onMounted } from 'vue'
import TrackerApiService from '@/services/api/TrackerApiService.js'
import EmotionApiService from '@/services/api/EmotionApiService.js'
import CategorieEmotionApiService from '@/services/api/CategorieEmotionApiService.js'

export function useEmotionTracker() {
  // État
  const trackers = ref([])
  const emotions = ref([])
  const categories = ref([])
  const currentDate = ref(new Date())
  const selectedDate = ref(new Date())
  const loading = ref(false)
  const error = ref(null)
  
  // Services
  const trackerService = new TrackerApiService()
  const emotionService = new EmotionApiService()
  const categorieService = new CategorieEmotionApiService()
  
  // Computed pour le mois actuel
  const currentMonth = computed(() => {
    return {
      year: currentDate.value.getFullYear(),
      month: currentDate.value.getMonth(),
      date: new Date(currentDate.value.getFullYear(), currentDate.value.getMonth(), 1)
    }
  })
  
  // Computed pour les trackers du mois actuel
  const currentMonthTrackers = computed(() => {
    // Les trackers sont déjà filtrés par mois via l'API
    return trackers.value
  })
  
  // Computed pour les trackers de la date sélectionnée
  const selectedDateTrackers = computed(() => {
    if (!selectedDate.value) return []
    
    return trackers.value.filter(tracker => {
      const trackerDate = new Date(tracker.datetime)
      return isSameDay(trackerDate, selectedDate.value)
    }).sort((a, b) => new Date(a.datetime) - new Date(b.datetime))
  })
  
  // Fonction utilitaire pour vérifier si deux dates sont le même jour
  const isSameDay = (date1, date2) => {
    return date1.getDate() === date2.getDate() &&
           date1.getMonth() === date2.getMonth() &&
           date1.getFullYear() === date2.getFullYear()
  }
  
  // Chargement des données
  const loadTrackers = async () => {
    try {
      loading.value = true
      error.value = null
      
      // Charger les trackers du mois actuel uniquement
      const year = currentDate.value.getFullYear()
      const month = currentDate.value.getMonth() + 1 // getMonth() retourne 0-11, on veut 1-12
      
      const monthTrackers = await trackerService.getTrackersByMonth(year, month)
      trackers.value = monthTrackers
      
    } catch (err) {
      error.value = 'Erreur lors du chargement des trackers'
      console.error('Erreur chargement trackers:', err)
    } finally {
      loading.value = false
    }
  }
  
  const loadEmotions = async () => {
    try {
      const activeEmotions = await emotionService.getActiveEmotions()
      emotions.value = activeEmotions
    } catch (err) {
      console.error('Erreur chargement émotions:', err)
    }
  }
  
  const loadCategories = async () => {
    try {
      const allCategories = await categorieService.getAllCategories()
      categories.value = allCategories
    } catch (err) {
      console.error('Erreur chargement catégories:', err)
    }
  }
  
  // Gestion du calendrier
  const changeMonth = async (newDate) => {
    currentDate.value = new Date(newDate)
    await loadTrackersForMonth(newDate)
  }
  
  const loadTrackersForMonth = async (date) => {
    try {
      loading.value = true
      error.value = null
      
      const year = date.getFullYear()
      const month = date.getMonth() + 1 // getMonth() retourne 0-11, on veut 1-12
      
      console.log(`Chargement des trackers pour ${year}/${month}`)
      
      const monthTrackers = await trackerService.getTrackersByMonth(year, month)
      trackers.value = monthTrackers
      
    } catch (err) {
      error.value = 'Erreur lors du chargement du mois'
      console.error('Erreur chargement mois:', err)
    } finally {
      loading.value = false
    }
  }
  
  const selectDay = (day) => {
    selectedDate.value = new Date(day.date)
  }
  
  // Gestion des trackers
  const createTracker = async (emotionId, datetime, commentaire = null) => {
    console.log('Création tracker:', emotionId, datetime, commentaire)
    try {
      const trackerData = {
        emotion: emotionId,
        datetime: trackerService.formatDateTimeForAPI(datetime),
        commentaire
      }
      
      const newTracker = await trackerService.createTracker(trackerData)
      
      // Ajouter le nouveau tracker à la liste locale seulement s'il appartient au mois actuel
      const trackerDate = new Date(newTracker.datetime)
      const currentYear = currentDate.value.getFullYear()
      const currentMonth = currentDate.value.getMonth()
      
      if (trackerDate.getFullYear() === currentYear && trackerDate.getMonth() === currentMonth) {
        trackers.value.push(newTracker)
      }
      
      return newTracker
    } catch (err) {
      error.value = 'Erreur lors de la création du tracker'
      console.error('Erreur création tracker:', err)
      throw err
    }
  }
  
  const updateTracker = async (trackerId, updates) => {
    try {
      // Si on met à jour la datetime, la formater correctement
      if (updates.datetime) {
        updates.datetime = trackerService.formatDateTimeForAPI(new Date(updates.datetime))
      }
      
      const updatedTracker = await trackerService.updateTracker(trackerId, updates)
      
      // Mettre à jour le tracker dans la liste locale
      const index = trackers.value.findIndex(t => t.id === trackerId)
      if (index !== -1) {
        // Vérifier si le tracker modifié appartient toujours au mois actuel
        const trackerDate = new Date(updatedTracker.datetime)
        const currentYear = currentDate.value.getFullYear()
        const currentMonth = currentDate.value.getMonth()
        
        if (trackerDate.getFullYear() === currentYear && trackerDate.getMonth() === currentMonth) {
          trackers.value[index] = updatedTracker
        } else {
          // Le tracker a été déplacé vers un autre mois, le retirer de la liste actuelle
          trackers.value.splice(index, 1)
        }
      }
      
      return updatedTracker
    } catch (err) {
      error.value = 'Erreur lors de la modification du tracker'
      console.error('Erreur modification tracker:', err)
      throw err
    }
  }
  
  const deleteTracker = async (trackerId) => {
    try {
      await trackerService.deleteTracker(trackerId)
      
      // Supprimer le tracker de la liste locale
      trackers.value = trackers.value.filter(t => t.id !== trackerId)
      
    } catch (err) {
      error.value = 'Erreur lors de la suppression du tracker'
      console.error('Erreur suppression tracker:', err)
      throw err
    }
  }
  
  // Utilitaires
  const getEmotionById = (emotionId) => {
    return emotions.value.find(emotion => emotion.id === emotionId)
  }
  
  const getCategoryById = (categoryId) => {
    return categories.value.find(category => category.id === categoryId)
  }
  
  const formatDate = (date) => {
    return date.toLocaleDateString('fr-FR', {
      weekday: 'long',
      year: 'numeric',
      month: 'long',
      day: 'numeric'
    })
  }
  
  const formatTime = (date) => {
    return date.toLocaleTimeString('fr-FR', {
      hour: '2-digit',
      minute: '2-digit'
    })
  }
  
  const formatDateTime = (date) => {
    return date.toLocaleDateString('fr-FR', {
      weekday: 'short',
      day: 'numeric',
      month: 'short',
      hour: '2-digit',
      minute: '2-digit'
    })
  }
  
  // Initialisation
  onMounted(async () => {
    await Promise.all([
      loadTrackers(),
      loadEmotions(),
      loadCategories()
    ])
  })
  
  return {
    // État
    trackers,
    emotions,
    categories,
    currentDate,
    selectedDate,
    loading,
    error,
    
    // Computed
    currentMonth,
    currentMonthTrackers,
    selectedDateTrackers,
    
    // Méthodes
    loadTrackers,
    loadEmotions,
    loadCategories,
    changeMonth,
    selectDay,
    createTracker,
    updateTracker,
    deleteTracker,
    
    // Utilitaires
    getEmotionById,
    getCategoryById,
    formatDate,
    formatTime,
    formatDateTime,
    isSameDay
  }
}
