import { ref, computed, onMounted } from 'vue'
import infoService from '@/services/singleton/infoService.js'
import { useMenuFilter } from './useMenuFilter.js'

export function useArticles() {
  const allInfos = ref([])
  const loading = ref(true)
  const error = ref(null)
  const showModal = ref(false)
  const selectedInfo = ref({})

  // Intégration du filtrage par menu
  const {
    menusWithAll,
    selectedMenuId,
    selectedMenu,
    loadingMenus,
    menuError,
    selectMenu,
    isMenuSelected,
    filterInfosByMenu,
    fetchMenus
  } = useMenuFilter()

  // Infos filtrées selon le menu sélectionné
  const infos = computed(() => filterInfosByMenu(allInfos.value))

  const fetchInfos = async () => {
    try {
      loading.value = true
      error.value = null
      const response = await infoService.getAllInfos()
      allInfos.value = response
    } catch (err) {
      console.error('Erreur lors de la récupération des infos:', err)
      error.value = 'Impossible de charger les articles. Veuillez réessayer plus tard.'
    } finally {
      loading.value = false
    }
  }

  const openModal = (info) => {
    selectedInfo.value = info
    showModal.value = true
  }

  const closeModal = () => {
    showModal.value = false
    selectedInfo.value = {}
  }

  const handleMenuSelected = (menuId) => {
    selectMenu(menuId)
  }

  const retryMenus = () => {
    fetchMenus()
  }

  onMounted(() => {
    fetchInfos()
  })

  return {
    infos,
    allInfos,
    loading,
    error,
    showModal,
    selectedInfo,
    
    // Filtrage par menu
    menusWithAll,
    selectedMenuId,
    selectedMenu,
    loadingMenus,
    menuError,
    isMenuSelected,
    handleMenuSelected,
    retryMenus,
    
    // Actions
    fetchInfos,
    openModal,
    closeModal
  }
}
