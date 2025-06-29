import { ref, watch, readonly } from 'vue'

const pageTitle = ref('CesiZen - Votre compagnon bien-être')

export function usePageTitle() {
  const setTitle = (title) => {
    if (title) {
      pageTitle.value = `${title} - CesiZen`
      document.title = pageTitle.value
    } else {
      pageTitle.value = 'CesiZen - Votre compagnon bien-être'
      document.title = pageTitle.value
    }
  }

  const resetTitle = () => {
    pageTitle.value = 'CesiZen - Votre compagnon bien-être'
    document.title = pageTitle.value
  }

  // Watcher pour mettre à jour le titre du document
  watch(pageTitle, (newTitle) => {
    document.title = newTitle
  }, { immediate: true })

  return {
    pageTitle: readonly(pageTitle),
    setTitle,
    resetTitle
  }
}

// Fonction utilitaire pour définir le titre depuis n'importe où
export const setPageTitle = (title) => {
  if (title) {
    document.title = `${title} - CesiZen`
  } else {
    document.title = 'CesiZen - Votre compagnon bien-être'
  }
}
