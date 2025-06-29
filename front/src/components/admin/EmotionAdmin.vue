<template>
  <div class="emotion-admin">
    <div class="section-header">
      <h2>Gestion des Émotions</h2>
      <div class="header-actions">
        <div class="search-container">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher une émotion..."
            class="search-input"
          />
          <span class="search-icon">🔍</span>
        </div>
        <div class="filter-container">
          <select v-model="selectedCategoryFilter" class="category-filter">
            <option value="">Toutes les catégories</option>
            <option 
              v-for="category in categories" 
              :key="category.id" 
              :value="category.id"
            >
              {{ category.nom }}
            </option>
          </select>
          <select v-model="selectedStatusFilter" class="status-filter">
            <option value="">Tous les statuts</option>
            <option value="active">Actif</option>
            <option value="inactive">Inactif</option>
          </select>
        </div>
        <button 
          @click="openCreateModal" 
          class="btn btn-primary"
          :disabled="loading"
        >
          ➕ Nouvelle émotion
        </button>
      </div>
    </div>

    <!-- States de chargement et erreur -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des émotions...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>❌ Erreur lors du chargement des émotions : {{ error }}</p>
      <button @click="loadEmotions" class="btn btn-secondary">🔄 Réessayer</button>
    </div>

    <!-- Tableau des émotions -->
    <div v-else-if="filteredEmotions.length > 0" class="emotions-table-container">
      <div class="table-info">
        <span class="results-count">{{ filteredEmotions.length }} émotion(s) trouvée(s)</span>
      </div>
      <div class="table-responsive">
        <table class="emotions-table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Nom</th>
              <th>Catégorie</th>
              <th>Statut</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="emotion in filteredEmotions" :key="emotion.id" class="emotion-row" :class="{ 'inactive': !emotion.actif }">
              <td class="image-cell">
                <div class="emotion-image">
                  <img 
                    v-if="emotion.icone" 
                    :src="getImageUrl(emotion.icone)" 
                    :alt="emotion.nom"
                    @error="handleImageError"
                  />
                  <div v-else class="no-image">🖼️</div>
                </div>
              </td>
              <td class="name-cell">
                <strong>{{ emotion.nom }}</strong>
              </td>
              <td class="category-cell">
                <span 
                  class="category-tag" 
                  :style="{ 
                    backgroundColor: getCategoryData(emotion.categorie?.id).couleur + '20',
                    color: getCategoryData(emotion.categorie?.id).couleur,
                    borderColor: getCategoryData(emotion.categorie?.id).couleur + '40'
                  }"
                >
                  {{ getCategoryData(emotion.categorie?.id).nom }}
                </span>
              </td>
              <td class="status-cell">
                <span class="status-badge" :class="{ 'active': emotion.actif, 'inactive': !emotion.actif }">
                  {{ emotion.actif ? 'Actif' : 'Inactif' }}
                </span>
              </td>
              <td class="date-cell">
                {{ formatDate(emotion.dateCreation) }}
              </td>
              <td class="actions-cell">
                <div class="action-buttons">
                  <button 
                    @click="openEditModal(emotion)" 
                    class="btn btn-sm btn-edit"
                    title="Modifier"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="toggleEmotionStatus(emotion)" 
                    class="btn btn-sm"
                    :class="emotion.actif ? 'btn-warning' : 'btn-success'"
                    :title="emotion.actif ? 'Désactiver' : 'Activer'"
                  >
                    {{ emotion.actif ? '⏸️' : '▶️' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- État vide avec recherche -->
    <div v-else-if="searchQuery && emotions.length > 0" class="empty-search-state">
      <div class="empty-icon">🔍</div>
      <h3>Aucun résultat</h3>
      <p>Aucune émotion ne correspond à votre recherche "{{ searchQuery }}".</p>
      <button @click="clearSearch" class="btn btn-secondary">
        Effacer la recherche
      </button>
    </div>

    <!-- État vide -->
    <div v-else class="empty-state">
      <div class="empty-icon">😊</div>
      <h3>Aucune émotion trouvée</h3>
      <p>Commencez par créer votre première émotion.</p>
      <button @click="openCreateModal" class="btn btn-primary">
        ➕ Créer une émotion
      </button>
    </div>

    <!-- Modal de création/édition -->
    <EmotionFormModal
      :show="showModal"
      :mode="modalMode"
      :emotion="selectedEmotion"
      :categories="categories"
      @close="closeModal"
      @success="handleModalSuccess"
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import EmotionFormModal from '@/components/admin/EmotionFormModal.vue'
import { useEmotionAdmin } from '@/composables/useEmotionAdmin.js'

// Filtres
const searchQuery = ref('')
const selectedCategoryFilter = ref('')
const selectedStatusFilter = ref('')

const {
  emotions,
  categories,
  loading,
  error,
  selectedEmotion,
  showModal,
  modalMode,
  loadEmotions,
  openCreateModal,
  openEditModal,
  closeModal,
  getCategoryName,
  getCategoryData,
  toggleEmotionStatus
} = useEmotionAdmin()

// Émotions filtrées
const filteredEmotions = computed(() => {
  let filtered = emotions.value

  // Filtre par recherche
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(emotion => 
      emotion.nom?.toLowerCase().includes(query) ||
      getCategoryName(emotion.categorie?.id)?.toLowerCase().includes(query)
    )
  }

  // Filtre par catégorie
  if (selectedCategoryFilter.value) {
    filtered = filtered.filter(emotion => 
      emotion.categorie?.id === selectedCategoryFilter.value
    )
  }

  // Filtre par statut
  if (selectedStatusFilter.value) {
    if (selectedStatusFilter.value === 'active') {
      filtered = filtered.filter(emotion => emotion.actif === true)
    } else if (selectedStatusFilter.value === 'inactive') {
      filtered = filtered.filter(emotion => emotion.actif === false)
    }
  }

  return filtered
})

// Gestion des images (pour l'affichage uniquement)
const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) return imagePath;
  // L'image est stockée côté backend dans public/uploads/emotions/
  return `http://localhost:8000${imagePath}`;
}

const handleImageError = (event) => {
  event.target.style.display = 'none'
  event.target.parentElement.innerHTML = '<div class="no-image">🖼️</div>'
}

const clearSearch = () => {
  searchQuery.value = ''
}

// Gestion de l'événement de succès de la modal
const handleModalSuccess = () => {
  loadEmotions() // Recharger la liste des émotions
}

// Utilitaires
const formatDate = (dateString) => {
  if (!dateString) return ''
  try {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric'
    })
  } catch {
    return dateString
  }
}
</script>

<style scoped>
.emotion-admin {
  background: #fff;
  border-radius: 12px;
  padding: 2rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  border: 1px solid #e5e7eb;
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
  gap: 1rem;
}

.section-header h2 {
  color: #2a5d49;
  margin: 0;
  font-size: 1.5rem;
}

.header-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

/* Recherche */
.search-container {
  position: relative;
  display: flex;
  align-items: center;
}

.search-input {
  padding: 0.75rem 1rem 0.75rem 2.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  width: 250px;
  background: white;
  transition: all 0.2s ease;
}

.search-input:focus {
  outline: none;
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  color: #6b7280;
  pointer-events: none;
}

/* Filtre par catégorie */
.filter-container {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.category-filter,
.status-filter {
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  background: white;
  min-width: 180px;
  transition: all 0.2s ease;
}

.category-filter:focus,
.status-filter:focus {
  outline: none;
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

/* Info du tableau */
.table-info {
  background: #f9fafb;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.85rem;
  color: #6b7280;
}

.results-count {
  font-weight: 500;
}

/* Boutons */
.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
}

.btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary {
  background: #22c55e;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #16a34a;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover:not(:disabled) {
  background: #4b5563;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover:not(:disabled) {
  background: #dc2626;
}

.btn-sm {
  padding: 0.5rem 0.75rem;
  font-size: 0.8rem;
}

.btn-edit {
  background: #3b82f6;
  color: white;
}

.btn-edit:hover {
  background: #2563eb;
}

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover {
  background: #d97706;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-delete {
  background: #ef4444;
  color: white;
}

.btn-delete:hover:not(:disabled) {
  background: #dc2626;
}

.btn-delete:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* États de chargement et erreur */
.loading-state, .error-state, .empty-state {
  text-align: center;
  padding: 3rem 2rem;
}

.spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f4f6;
  border-top: 4px solid #22c55e;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-state {
  color: #dc2626;
}

.empty-state .empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-state h3 {
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-state p {
  color: #6b7280;
  margin-bottom: 2rem;
}

/* Tableau responsive */
.emotions-table-container {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.table-responsive {
  overflow-x: auto;
}

.emotions-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.emotions-table th {
  background: #f9fafb;
  padding: 1rem 0.75rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  white-space: nowrap;
}

.emotions-table td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #e5e7eb;
  vertical-align: middle;
}

.emotion-row:hover {
  background: #f9fafb;
}

.emotion-row.inactive {
  opacity: 0.6;
  background: #f9fafb;
}

/* Cellules spécifiques */
.image-cell {
  width: 80px;
}

.emotion-image {
  width: 60px;
  height: 40px;
  border-radius: 4px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
  margin: 0 auto;
}

.emotion-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  font-size: 1.5rem;
  color: #9ca3af;
}

/* État vide de recherche */
.empty-search-state {
  text-align: center;
  padding: 3rem 2rem;
}

.empty-search-state .empty-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
}

.empty-search-state h3 {
  color: #374151;
  margin-bottom: 0.5rem;
}

.empty-search-state p {
  color: #6b7280;
  margin-bottom: 2rem;
}

.name-cell {
  min-width: 150px;
}

.name-cell strong {
  color: #1f2937;
  font-weight: 600;
}

.category-cell {
  min-width: 120px;
}

.category-tag {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
  border: 1px solid;
  display: inline-block;
}

.status-cell {
  min-width: 100px;
}

.status-badge {
  font-size: 0.8rem;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-weight: 500;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #dc2626;
}

.date-cell {
  min-width: 100px;
  color: #6b7280;
  font-size: 0.85rem;
}

.actions-cell {
  width: 120px;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

/* Responsive */
@media (max-width: 768px) {
  .emotion-admin {
    padding: 1.5rem;
  }
  
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .section-header h2 {
    font-size: 1.25rem;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 1rem;
  }
  
  .filter-container {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .search-input {
    width: 100%;
  }
  
  .category-filter,
  .status-filter {
    width: 100%;
  }
  
  .emotions-table th,
  .emotions-table td {
    padding: 0.75rem 0.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.25rem;
  }
}

@media (max-width: 640px) {
  .table-responsive {
    font-size: 0.8rem;
  }
  
  .category-cell {
    min-width: 80px;
  }
  
  .date-cell {
    font-size: 0.75rem;
  }
}
</style>
