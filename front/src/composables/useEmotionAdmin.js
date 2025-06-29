import { ref, onMounted } from 'vue'
import EmotionApiService from '@/services/api/EmotionApiService.js'
import CategorieEmotionApiService from '@/services/api/CategorieEmotionApiService.js'

export function useEmotionAdmin() {
  const emotions = ref([])
  const categories = ref([])
  const loading = ref(false)
  const error = ref(null)
  const selectedEmotion = ref(null)
  const showModal = ref(false)
  const modalMode = ref('create') // 'create' ou 'edit'

  // Instances des services
  const emotionService = new EmotionApiService()
  const categorieService = new CategorieEmotionApiService()

  // Form data
  const emotionForm = ref({
    nom: '',
    categorie: null,
    actif: true
  })

  const loadEmotions = async () => {
    try {
      loading.value = true
      error.value = null
      emotions.value = await emotionService.getAllEmotions()
    } catch (err) {
      error.value = 'Erreur lors du chargement des émotions'
      console.error('Erreur chargement émotions:', err)
    } finally {
      loading.value = false
    }
  }

  const loadCategories = async () => {
    try {
      categories.value = await categorieService.getAllCategories()
    } catch (err) {
      console.error('Erreur chargement catégories:', err)
    }
  }

  const openCreateModal = () => {
    modalMode.value = 'create'
    emotionForm.value = {
      nom: '',
      categorie: null,
      actif: true
    }
    selectedEmotion.value = null
    showModal.value = true
  }

  const openEditModal = (emotion) => {
    modalMode.value = 'edit'
    selectedEmotion.value = emotion
    emotionForm.value = {
      nom: emotion.nom,
      icone: emotion.icone,
      categorie: emotion.categorie?.id || null,
      actif: emotion.actif
    }
    showModal.value = true
  }

  const closeModal = () => {
    showModal.value = false
    selectedEmotion.value = null
    emotionForm.value = {
      nom: '',
      categorie: null,
      actif: true
    }
  }

  const saveEmotion = async (imageFile = null) => {
    try {
      if (modalMode.value === 'create') {
        await emotionService.createEmotion(emotionForm.value, imageFile)
      } else {
        await emotionService.updateEmotion(selectedEmotion.value.id, emotionForm.value, imageFile)
      }
      await loadEmotions()
      closeModal()
    } catch (err) {
      error.value = `Erreur lors de la ${modalMode.value === 'create' ? 'création' : 'modification'} de l'émotion`
      console.error('Erreur sauvegarde émotion:', err)
    }
  }

  const deleteEmotion = async (emotion) => {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer l'émotion "${emotion.nom}" ?`)) {
      return
    }

    try {
      await emotionService.deleteEmotion(emotion.id)
      await loadEmotions()
    } catch (err) {
      error.value = 'Erreur lors de la suppression de l\'émotion'
      console.error('Erreur suppression émotion:', err)
    }
  }

  const toggleEmotionStatus = async (emotion) => {
    const newStatus = !emotion.actif
    const action = newStatus ? 'activer' : 'désactiver'

    try {
      await emotionService.updateEmotion(emotion.id, { actif: newStatus })
      await loadEmotions()
    } catch (err) {
      error.value = `Erreur lors de la ${action}ion de l'émotion`
      console.error(`Erreur ${action}ion émotion:`, err)
    }
  }

  const getCategoryName = (categoryId) => {
    const category = categories.value.find(cat => cat.id === categoryId)
    return category?.nom || 'Non définie'
  }

  const getCategoryData = (categoryId) => {
    const category = categories.value.find(cat => cat.id === categoryId)
    return {
      nom: category?.nom || 'Non définie',
      couleur: category?.couleur || '#6b7280'
    }
  }

  onMounted(() => {
    loadEmotions()
    loadCategories()
  })

  return {
    emotions,
    categories,
    loading,
    error,
    selectedEmotion,
    showModal,
    modalMode,
    emotionForm,
    loadEmotions,
    openCreateModal,
    openEditModal,
    closeModal,
    saveEmotion,
    deleteEmotion,
    getCategoryName,
    getCategoryData,
    toggleEmotionStatus
  }
}
