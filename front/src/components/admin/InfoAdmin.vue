<template>
  <div class="info-admin">
    <div class="section-header">
      <h2>Gestion des Articles / Infos</h2>
      <div class="header-actions">
        <div class="search-container">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher une info..."
            class="search-input"
          />
          <span class="search-icon">🔍</span>
        </div>
        <button 
          @click="openCreateModal" 
          class="btn btn-primary"
          :disabled="loading"
        >
          ➕ Nouvelle Info
        </button>
      </div>
    </div>

    <!-- States de chargement et erreur -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des infos...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>❌ Erreur lors du chargement des infos : {{ error }}</p>
      <button @click="loadInfos" class="btn btn-secondary">🔄 Réessayer</button>
    </div>

    <!-- Tableau des infos -->
    <div v-else-if="filteredInfos.length > 0" class="infos-table-container">
      <div class="table-info">
        <span class="results-count">{{ filteredInfos.length }} info(s) trouvée(s)</span>
      </div>
      <div class="table-responsive">
        <table class="infos-table">
          <thead>
            <tr>
              <th>Image</th>
              <th>Titre</th>
              <th>Aperçu</th>
              <th>Menu</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="info in filteredInfos" :key="info.id" class="info-row">
              <td class="image-cell">
                <div class="table-image">
                  <img 
                    v-if="info.image" 
                    :src="getImageUrl(info.image)" 
                    :alt="info.titre"
                    @error="handleImageError"
                  />
                  <div v-else class="no-image">📄</div>
                </div>
              </td>
              <td class="title-cell">
                <strong>{{ info.titre }}</strong>
              </td>
              <td class="preview-cell">
                <div class="preview-text">
                  {{ truncateText(info.contenu, 80) }}
                </div>
              </td>
              <td class="menu-cell">
                <span class="menu-tag">{{ info.menu?.nom || 'Aucun' }}</span>
              </td>
              <td class="date-cell">
                {{ formatDate(info.dateCreation) }}
              </td>
              <td class="actions-cell">
                <div class="action-buttons">
                  <button 
                    @click="openEditModal(info)" 
                    class="btn btn-sm btn-edit"
                    title="Modifier"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="confirmDelete(info)" 
                    class="btn btn-sm btn-delete"
                    title="Supprimer"
                  >
                    🗑️
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- État vide avec recherche -->
    <div v-else-if="searchQuery && infos.length > 0" class="empty-search-state">
      <div class="empty-icon">🔍</div>
      <h3>Aucun résultat</h3>
      <p>Aucune info ne correspond à votre recherche "{{ searchQuery }}".</p>
      <button @click="clearSearch" class="btn btn-secondary">
        Effacer la recherche
      </button>
    </div>

    <!-- État vide -->
    <div v-else class="empty-state">
      <div class="empty-icon">📄</div>
      <h3>Aucune info trouvée</h3>
      <p>Commencez par créer votre première info.</p>
      <button @click="openCreateModal" class="btn btn-primary">
        ➕ Créer une info
      </button>
    </div>

    <!-- Modale de formulaire pour créer/modifier une info -->
    <InfoFormModal
      v-if="showFormModal"
      :show="showFormModal"
      :info="selectedInfo"
      :menus="menus"
      @close="closeFormModal"
      @success="handleInfoSaved"
    />

    <!-- Modale de confirmation de suppression -->
    <div v-if="showDeleteConfirm" class="modal-overlay" @click="cancelDelete">
      <div class="delete-confirm-modal" @click.stop>
        <h3>Confirmer la suppression</h3>
        <p>Êtes-vous sûr de vouloir supprimer l'info "<strong>{{ infoToDelete?.titre }}</strong>" ?</p>
        <p class="warning">⚠️ Cette action est irréversible.</p>
        <div class="modal-actions">
          <button @click="cancelDelete" class="btn btn-secondary">Annuler</button>
          <button @click="deleteInfo" class="btn btn-danger" :disabled="deleting">
            {{ deleting ? 'Suppression...' : 'Supprimer' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import infoService from '@/services/singleton/infoService.js'
import InfoFormModal from '@/components/admin/InfoFormModal.vue'

// Réactivité
const infos = ref([])
const menus = ref([])
const loading = ref(false)
const error = ref(null)
const deleting = ref(false)
const searchQuery = ref('')

// Modales
const showFormModal = ref(false)
const showDeleteConfirm = ref(false)
const selectedInfo = ref(null)
const infoToDelete = ref(null)

// Infos filtrées par la recherche
const filteredInfos = computed(() => {
  if (!searchQuery.value.trim()) {
    return infos.value
  }
  
  const query = searchQuery.value.toLowerCase().trim()
  return infos.value.filter(info => 
    info.titre?.toLowerCase().includes(query) ||
    info.contenu?.toLowerCase().includes(query) ||
    info.menu?.nom?.toLowerCase().includes(query)
  )
})

// Chargement des données au montage du composant
onMounted(() => {
  loadInfos()
  loadMenus()
})

// Charger les infos
const loadInfos = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await infoService.getAllInfos()
    infos.value = response.data || response || []
  } catch (err) {
    console.error('Erreur lors du chargement des infos:', err)
    error.value = err.message || 'Erreur lors du chargement des infos'
  } finally {
    loading.value = false
  }
}

// Charger les menus
const loadMenus = async () => {
  try {
    const response = await infoService.getAllMenus()
    menus.value = response.data || response || []
  } catch (err) {
    console.error('Erreur lors du chargement des menus:', err)
  }
}

// Gestion des modales
const openCreateModal = () => {
  selectedInfo.value = null
  showFormModal.value = true
}

const openEditModal = (info) => {
  selectedInfo.value = { ...info }
  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  selectedInfo.value = null
}

const handleInfoSaved = () => {
  closeFormModal()
  loadInfos() // Recharger la liste
}

// Gestion de la suppression
const confirmDelete = (info) => {
  infoToDelete.value = info
  showDeleteConfirm.value = true
}

const cancelDelete = () => {
  showDeleteConfirm.value = false
  infoToDelete.value = null
}

const deleteInfo = async () => {
  if (!infoToDelete.value) return
  
  deleting.value = true
  
  try {
    await infoService.deleteInfo(infoToDelete.value.id)
    
    // Retirer l'info de la liste locale
    infos.value = infos.value.filter(info => info.id !== infoToDelete.value.id)
    
    // Fermer la modale
    showDeleteConfirm.value = false
    infoToDelete.value = null
  } catch (err) {
    console.error('Erreur lors de la suppression:', err)
    alert('Erreur lors de la suppression de l\'info')
  } finally {
    deleting.value = false
  }
}

// Utilitaires
const getImageUrl = (imagePath) => {
  if (!imagePath) return '';
  if (imagePath.startsWith('http')) return imagePath;
  // L'image est stockée côté backend dans public/uploads/infos/
  return `http://localhost:8000${imagePath}`;
}

const truncateText = (text, maxLength) => {
  if (!text) return ''
  return text.length > maxLength ? text.substring(0, maxLength) + '...' : text
}

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

const handleImageError = (event) => {
  event.target.style.display = 'none'
  event.target.parentElement.innerHTML = '<div class="no-image">📄</div>'
}

const clearSearch = () => {
  searchQuery.value = ''
}
</script>

<style scoped>
.info-admin {
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

.btn-delete {
  background: #ef4444;
  color: white;
}

.btn-delete:hover {
  background: #dc2626;
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

/* Tableau responsive */
.infos-table-container {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.table-responsive {
  overflow-x: auto;
}

.infos-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.infos-table th {
  background: #f9fafb;
  padding: 1rem 0.75rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  white-space: nowrap;
}

.infos-table td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #e5e7eb;
  vertical-align: middle;
}

.info-row:hover {
  background: #f9fafb;
}

/* Cellules spécifiques */
.image-cell {
  width: 80px;
}

.table-image {
  width: 60px;
  height: 40px;
  border-radius: 4px;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f3f4f6;
}

.table-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.no-image {
  font-size: 1.5rem;
  color: #9ca3af;
}

.title-cell {
  min-width: 200px;
  max-width: 250px;
}

.title-cell strong {
  color: #1f2937;
  font-weight: 600;
}

.preview-cell {
  max-width: 300px;
}

.preview-text {
  color: #6b7280;
  line-height: 1.4;
  word-break: break-word;
}

.menu-cell {
  min-width: 120px;
}

.menu-tag {
  background: #dbeafe;
  color: #1e40af;
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
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

/* Modale de confirmation de suppression */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 1rem;
}

.delete-confirm-modal {
  background: white;
  padding: 2rem;
  border-radius: 12px;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
}

.delete-confirm-modal h3 {
  color: #dc2626;
  margin-bottom: 1rem;
  font-size: 1.25rem;
}

.delete-confirm-modal p {
  color: #374151;
  margin-bottom: 1rem;
  line-height: 1.5;
}

.warning {
  color: #f59e0b !important;
  font-weight: 500;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
}

/* Responsive */
@media (max-width: 768px) {
  .info-admin {
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
  
  .search-input {
    width: 100%;
  }
  
  .infos-table th,
  .infos-table td {
    padding: 0.75rem 0.5rem;
  }
  
  .title-cell,
  .preview-cell {
    min-width: 150px;
    max-width: 200px;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.25rem;
  }
  
  .delete-confirm-modal {
    margin: 1rem;
    padding: 1.5rem;
  }
  
  .modal-actions {
    flex-direction: column;
  }
}

@media (max-width: 640px) {
  .table-responsive {
    font-size: 0.8rem;
  }
  
  .preview-cell {
    display: none;
  }
  
  .menu-cell {
    min-width: 80px;
  }
  
  .date-cell {
    font-size: 0.75rem;
  }
}
</style>
