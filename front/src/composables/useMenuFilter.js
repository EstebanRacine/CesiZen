import { ref, computed, onMounted } from 'vue'
import menuService from '@/services/singleton/menuService.js'

export function useMenuFilter() {
  const menus = ref([])
  const selectedMenuId = ref(null)
  const loadingMenus = ref(false)
  const menuError = ref(null)

  // Menu "Tous" virtuel pour afficher tous les articles
  const allMenu = { id: null, nom: 'Tous les articles', icone: '📑' }

  // Liste des menus avec l'option "Tous" en premier
  const menusWithAll = computed(() => [allMenu, ...menus.value])

  // Menu actuellement sélectionné
  const selectedMenu = computed(() => {
    if (selectedMenuId.value === null) return allMenu
    return menus.value.find(menu => menu.id === selectedMenuId.value) || allMenu
  })

  const fetchMenus = async () => {
    try {
      loadingMenus.value = true
      menuError.value = null
      const response = await menuService.getAllActiveMenus()
      menus.value = response
    } catch (err) {
      console.error('Erreur lors de la récupération des menus:', err)
      menuError.value = 'Impossible de charger les menus'
    } finally {
      loadingMenus.value = false
    }
  }

  const selectMenu = (menuId) => {
    selectedMenuId.value = menuId
  }

  const isMenuSelected = (menuId) => {
    return selectedMenuId.value === menuId
  }

  // Fonction pour filtrer les infos par menu sélectionné
  const filterInfosByMenu = (infos) => {
    if (selectedMenuId.value === null) {
      return infos // Afficher tous les articles
    }
    return infos.filter(info => info.menu?.id === selectedMenuId.value)
  }

  onMounted(() => {
    fetchMenus()
  })

  return {
    menus,
    menusWithAll,
    selectedMenuId,
    selectedMenu,
    loadingMenus,
    menuError,
    selectMenu,
    isMenuSelected,
    filterInfosByMenu,
    fetchMenus
  }
}
