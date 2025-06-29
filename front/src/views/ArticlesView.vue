<template>
  <div class="articles-view">
    <header class="page-header">
      <h1 class="page-title">Articles</h1>
      <p class="page-subtitle">Découvrez nos dernières actualités et informations</p>
    </header>

    <MenuFilter
      :menus-with-all="menusWithAll"
      :selected-menu-id="selectedMenuId"
      :loading-menus="loadingMenus"
      :menu-error="menuError"
      :infos="allInfos"
      :is-menu-selected="isMenuSelected"
      @menu-selected="handleMenuSelected"
      @retry-menus="retryMenus"
    />

    <LoadingState v-if="loading" message="Chargement des articles..." />

    <ErrorState 
      v-else-if="error" 
      :message="error"
      @retry="fetchInfos"
    />

    <EmptyState 
      v-else-if="infos.length === 0 && selectedMenuId === null"
      title="Aucun article disponible"
      message="Il n'y a actuellement aucun article à afficher."
    />

    <EmptyState 
      v-else-if="infos.length === 0 && selectedMenuId !== null"
      title="Aucun article dans cette catégorie"
      :message="`Aucun article trouvé pour la catégorie '${selectedMenu.nom}'.`"
    />

    <div v-else>
      <div class="articles-header">
        <div class="articles-count">
          <span class="count-badge">{{ infos.length }}</span>
          <span class="count-text">
            {{ infos.length === 1 ? 'article' : 'articles' }}
            {{ selectedMenuId !== null ? `dans "${selectedMenu.nom}"` : 'au total' }}
          </span>
        </div>
      </div>

      <div class="articles-grid">
        <InfoCard 
          v-for="info in infos" 
          :key="info.id" 
          :info="info"
          @click="openModal"
        />
      </div>
    </div>

    <InfoModal 
      :show="showModal" 
      :info="selectedInfo"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { useArticles } from '@/composables/useArticles.js'
import MenuFilter from '@/components/filters/MenuFilter.vue'
import LoadingState from '@/components/ui/LoadingState.vue'
import ErrorState from '@/components/ui/ErrorState.vue'
import EmptyState from '@/components/ui/EmptyState.vue'
import InfoCard from '@/components/info/InfoCard.vue'
import InfoModal from '@/components/info/InfoModal.vue'

const {
  infos,
  allInfos,
  loading,
  error,
  showModal,
  selectedInfo,
  menusWithAll,
  selectedMenuId,
  selectedMenu,
  loadingMenus,
  menuError,
  isMenuSelected,
  handleMenuSelected,
  retryMenus,
  fetchInfos,
  openModal,
  closeModal
} = useArticles()
</script>

<style scoped>
.articles-view {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.page-header {
  text-align: center;
  margin-bottom: 3rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: #2a5d49;
  margin: 0 0 1rem 0;
  background: linear-gradient(135deg, #2a5d49 0%, #89c997 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.page-subtitle {
  font-size: 1.1rem;
  color: #6c757d;
  margin: 0;
  max-width: 600px;
  margin: 0 auto;
}

.articles-header {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-bottom: 1.5rem;
}

.articles-count {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.count-badge {
  background: linear-gradient(135deg, #2a5d49 0%, #89c997 100%);
  color: white;
  padding: 0.4rem 0.8rem;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
  box-shadow: 0 2px 8px rgba(42, 93, 73, 0.2);
}

.count-text {
  color: #6c757d;
  font-size: 0.95rem;
  font-weight: 500;
}

.articles-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 2rem;
  margin-top: 2rem;
}

@media (max-width: 768px) {
  .articles-view {
    padding: 1rem;
  }
  
  .page-header {
    margin-bottom: 2rem;
  }
  
  .page-title {
    font-size: 2rem;
  }
  
  .page-subtitle {
    font-size: 1rem;
  }
  
  .articles-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 1.5rem;
  }
}

@media (max-width: 480px) {
  .articles-view {
    padding: 1rem 0.75rem;
  }
  
  .page-title {
    font-size: 1.75rem;
  }
  
  .articles-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
}

.articles-grid > * {
  animation: fadeInUp 0.5s ease-out forwards;
}

.articles-grid > *:nth-child(1) { animation-delay: 0.1s; }
.articles-grid > *:nth-child(2) { animation-delay: 0.2s; }
.articles-grid > *:nth-child(3) { animation-delay: 0.3s; }
.articles-grid > *:nth-child(4) { animation-delay: 0.4s; }
.articles-grid > *:nth-child(5) { animation-delay: 0.5s; }
.articles-grid > *:nth-child(6) { animation-delay: 0.6s; }

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(30px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
