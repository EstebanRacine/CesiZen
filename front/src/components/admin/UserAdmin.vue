<template>
  <div class="user-admin">
    <div class="section-header">
      <h2>Gestion des Utilisateurs</h2>
      <div class="header-actions">
        <div class="search-container">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Rechercher un utilisateur..."
            class="search-input"
          />
          <span class="search-icon">🔍</span>
        </div>
        <div class="filter-container">
          <select v-model="roleFilter" class="role-filter">
            <option value="">Tous les rôles</option>
            <option value="ROLE_ADMIN">Administrateurs</option>
            <option value="ROLE_USER">Utilisateurs</option>
          </select>
        </div>
        <button 
          @click="openCreateModal" 
          class="btn btn-primary"
          :disabled="loading"
        >
          ➕ Nouvel Utilisateur
        </button>
      </div>
    </div>

    <!-- États de chargement et erreur -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des utilisateurs...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>❌ Erreur lors du chargement des utilisateurs : {{ error }}</p>
      <button @click="loadUsers" class="btn btn-secondary">🔄 Réessayer</button>
    </div>

    <!-- Statistiques rapides -->
    <div v-else class="stats-cards">
      <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-content">
          <div class="stat-number">{{ users.length }}</div>
          <div class="stat-label">Utilisateurs total</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🔐</div>
        <div class="stat-content">
          <div class="stat-number">{{ adminCount }}</div>
          <div class="stat-label">Administrateurs</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon">🟢</div>
        <div class="stat-content">
          <div class="stat-number">{{ activeUsersCount }}</div>
          <div class="stat-label">Utilisateurs actifs</div>
        </div>
      </div>
    </div>

    <!-- Tableau des utilisateurs -->
    <div v-if="filteredUsers.length > 0" class="users-table-container">
      <div class="table-info">
        <span class="results-count">{{ filteredUsers.length }} utilisateur(s) trouvé(s)</span>
      </div>
      <div class="table-responsive">
        <table class="users-table">
          <thead>
            <tr>
              <th>Utilisateur</th>
              <th>Rôle</th>
              <th>Statut</th>
              <th>Inscription</th>
              <th>Date suppression</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="user in filteredUsers" :key="user.id" class="user-row">
              <td class="user-info-cell">
                <div class="user-avatar">
                  <div class="avatar-placeholder">
                    {{ getUserInitials(user) }}
                  </div>
                </div>
                <div class="user-details">
                  <div class="user-name">{{ user.username }}</div>
                  <div class="user-id">ID: {{ user.id }}</div>
                </div>
              </td>
              <td class="role-cell">
                <span :class="['role-badge', getRoleClass(user.roles)]">
                  {{ getRoleLabel(user.roles) }}
                </span>
              </td>
              <td class="status-cell">
                <span :class="['status-badge', getStatusClass(user)]">
                  {{ getStatusLabel(user) }}
                </span>
              </td>
              <td class="date-cell">
                {{ formatDate(user.dateCreation) }}
              </td>
              <td class="date-cell">
                {{ formatDate(user.dateSupression) || '-' }}
              </td>
              <td class="actions-cell">
                <div class="action-buttons">
                  <button 
                    @click="openEditModal(user)" 
                    class="btn btn-sm btn-edit"
                    title="Modifier"
                  >
                    ✏️
                  </button>
                  <button 
                    @click="toggleUserStatus(user)" 
                    :class="['btn', 'btn-sm', user.isActive ? 'btn-warning' : 'btn-success']"
                    :title="user.isActive ? 'Désactiver' : 'Activer'"
                  >
                    {{ user.isActive ? '⏸️' : '▶️' }}
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- État vide -->
    <div v-else-if="!loading && !error" class="empty-state">
      <div class="empty-icon">👥</div>
      <h3>Aucun utilisateur trouvé</h3>
      <p v-if="searchQuery || roleFilter">
        Aucun utilisateur ne correspond à vos critères de recherche.
      </p>
      <p v-else>Aucun utilisateur n'est encore enregistré.</p>
      <button @click="clearFilters" v-if="searchQuery || roleFilter" class="btn btn-secondary">
        Effacer les filtres
      </button>
    </div>

    <!-- Modale de formulaire utilisateur -->
    <UserFormModal
      v-if="showFormModal"
      :show="showFormModal"
      :user="selectedUser"
      @close="closeFormModal"
      @success="handleUserSaved"
    />        <!-- Modale de confirmation de suppression -->
        <div v-if="showDeleteConfirm" class="modal-overlay" @click="cancelDelete">
          <div class="delete-confirm-modal" @click.stop>
            <h3>Confirmer la suppression</h3>
            <p>Êtes-vous sûr de vouloir supprimer l'utilisateur "<strong>{{ userToDelete?.username }}</strong>" ?</p>
            <p class="warning">⚠️ Cette action est irréversible et supprimera toutes les données associées à cet utilisateur.</p>
            <div class="modal-actions">
              <button @click="cancelDelete" class="btn btn-secondary">Annuler</button>
              <button @click="deleteUser" class="btn btn-danger" :disabled="deleting">
                {{ deleting ? 'Suppression...' : 'Supprimer' }}
              </button>
            </div>
          </div>
        </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import authService from '@/services/singleton/authService.js'
import userService from '@/services/singleton/userService.js'
import UserFormModal from '@/components/admin/UserFormModal.vue'

// Données réactives
const users = ref([])
const loading = ref(false)
const error = ref(null)
const deleting = ref(false)
const searchQuery = ref('')
const roleFilter = ref('')

// Modales
const showFormModal = ref(false)
const showDeleteConfirm = ref(false)
const selectedUser = ref(null)
const userToDelete = ref(null)

// Utilisateur actuel (pour éviter l'auto-suppression)
const currentUserId = computed(() => authService.user.value?.id)

// Utilisateurs filtrés
const filteredUsers = computed(() => {
  let filtered = users.value

  // Filtre par recherche
  if (searchQuery.value.trim()) {
    const query = searchQuery.value.toLowerCase().trim()
    filtered = filtered.filter(user => 
      user.username?.toLowerCase().includes(query) ||
      user.rolesLabel?.toLowerCase().includes(query)
    )
  }

  // Filtre par rôle
  if (roleFilter.value) {
    filtered = filtered.filter(user => 
      user.roles?.includes(roleFilter.value)
    )
  }

  return filtered
})

// Statistiques calculées
const adminCount = computed(() => 
  users.value.filter(user => user.roles?.includes('ROLE_ADMIN')).length
)

const activeUsersCount = computed(() => 
  users.value.filter(user => user.isActive).length
)

// Chargement au montage
onMounted(() => {
  loadUsers()
})

// Chargement des utilisateurs avec le service réel
const loadUsers = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await userService.getAllUsers()
    const rawUsers = response.data || response || []
    
    // Formatter les utilisateurs pour l'affichage
    users.value = rawUsers.map(user => userService.formatUserForDisplay(user))
  } catch (err) {
    console.error('Erreur lors du chargement des utilisateurs:', err)
    error.value = err.message || 'Erreur lors du chargement des utilisateurs'
    
    // En cas d'erreur, utiliser des données simulées pour la démo
    users.value = [
      {
        id: 1,
        username: 'admin',
        roles: ['ROLE_ADMIN'],
        isActive: true,
        dateCreation: '2024-01-15T10:30:00Z',
        dateSupression: null,
        isAdmin: true,
        statusLabel: 'Actif',
        rolesLabel: 'Administrateur'
      },
      {
        id: 2,
        username: 'user1',
        roles: ['ROLE_USER'],
        isActive: true,
        dateCreation: '2024-02-20T09:15:00Z',
        dateSupression: null,
        isAdmin: false,
        statusLabel: 'Actif',
        rolesLabel: 'Utilisateur'
      },
      {
        id: 3,
        username: 'user_deleted',
        roles: ['ROLE_USER'],
        isActive: false,
        dateCreation: '2024-03-10T14:20:00Z',
        dateSupression: '2024-06-20T10:00:00Z',
        isAdmin: false,
        statusLabel: 'Supprimé',
        rolesLabel: 'Utilisateur'
      }
    ]
  } finally {
    loading.value = false
  }
}

// Gestion des modales
const openCreateModal = () => {
  selectedUser.value = null
  showFormModal.value = true
}

const openEditModal = (user) => {
  selectedUser.value = { ...user }
  showFormModal.value = true
}

const closeFormModal = () => {
  showFormModal.value = false
  selectedUser.value = null
}

const handleUserSaved = () => {
  closeFormModal()
  loadUsers()
}

const cancelDelete = () => {
  showDeleteConfirm.value = false
  userToDelete.value = null
}

const deleteUser = async () => {
  if (!userToDelete.value) return
  
  deleting.value = true
  
  try {
    await userService.deleteUser(userToDelete.value.id)
    
    // Retirer l'utilisateur de la liste locale
    users.value = users.value.filter(user => user.id !== userToDelete.value.id)
    
    showDeleteConfirm.value = false
    userToDelete.value = null
  } catch (err) {
    console.error('Erreur lors de la suppression:', err)
    alert('Erreur lors de la suppression de l\'utilisateur: ' + (err.message || 'Erreur inconnue'))
  } finally {
    deleting.value = false
  }
}

// Basculer le statut actif/inactif
const toggleUserStatus = async (user) => {
  try {
    const newStatus = !user.isActive
    await userService.toggleUserStatus(user.id, newStatus)
    
    // Mettre à jour localement
    const userIndex = users.value.findIndex(u => u.id === user.id)
    if (userIndex !== -1) {
      users.value[userIndex].isActive = newStatus
      users.value[userIndex].statusLabel = newStatus ? 'Actif' : 'Inactif'
    }
  } catch (err) {
    console.error('Erreur lors de la modification du statut:', err)
    alert('Erreur lors de la modification du statut de l\'utilisateur: ' + (err.message || 'Erreur inconnue'))
  }
}

// Utilitaires
const getUserInitials = (user) => {
  const username = user.username || ''
  return username.slice(0, 2).toUpperCase() || '?'
}

const getRoleClass = (roles) => {
  if (roles?.includes('ROLE_ADMIN')) return 'admin'
  return 'user'
}

const getRoleLabel = (roles) => {
  console.log('getRoleLabel called with roles:', roles)
  if (!roles) return 'Utilisateur'
  
  if (roles.includes('ROLE_ADMIN')) {
    return 'Administrateur'
  }
  if (roles.includes('ROLE_USER')) {
    return 'Utilisateur'
  }
  
  return 'Utilisateur'
}

const getStatusClass = (user) => {
  if (user.dateSupression) return 'deleted'
  return user.isActive ? 'active' : 'inactive'
}

const getStatusLabel = (user) => {
  if (user.dateSupression) return 'Supprimé'
  return user.isActive ? 'Actif' : 'Inactif'
}

const formatDate = (dateString) => {
  if (!dateString) return null
  try {
    return new Date(dateString).toLocaleDateString('fr-FR', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  } catch {
    return dateString
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  roleFilter.value = ''
}
</script>

<style scoped>
.user-admin {
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

/* Recherche et filtres */
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

.filter-container {
  display: flex;
  align-items: center;
}

.role-filter {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  background: white;
  transition: all 0.2s ease;
  cursor: pointer;
}

.role-filter:focus {
  outline: none;
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

/* Cartes de statistiques */
.stats-cards {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.stat-card {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease;
}

.stat-card:hover {
  transform: translateY(-2px);
}

.stat-icon {
  font-size: 2.5rem;
  padding: 0.75rem;
  background: rgba(34, 197, 94, 0.1);
  border-radius: 12px;
}

.stat-content {
  flex: 1;
}

.stat-number {
  font-size: 2rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
}

.stat-label {
  font-size: 0.85rem;
  color: #6b7280;
  margin-top: 0.25rem;
}

/* États */
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

/* Tableau */
.users-table-container {
  background: #fff;
  border-radius: 8px;
  overflow: hidden;
  border: 1px solid #e5e7eb;
}

.table-info {
  background: #f9fafb;
  padding: 0.75rem 1rem;
  border-bottom: 1px solid #e5e7eb;
  font-size: 0.85rem;
  color: #6b7280;
}

.table-responsive {
  overflow-x: auto;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 0.9rem;
}

.users-table th {
  background: #f9fafb;
  padding: 1rem 0.75rem;
  text-align: left;
  font-weight: 600;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
  white-space: nowrap;
}

.users-table td {
  padding: 1rem 0.75rem;
  border-bottom: 1px solid #e5e7eb;
  vertical-align: middle;
}

.user-row:hover {
  background: #f9fafb;
}

/* Cellules spécifiques */
.user-info-cell {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  min-width: 200px;
}

.user-avatar {
  flex-shrink: 0;
}

.avatar-placeholder {
  width: 40px;
  height: 40px;
  background: #22c55e;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 600;
  font-size: 0.85rem;
}

.user-details {
  flex: 1;
}

.user-name {
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.user-username {
  font-size: 0.8rem;
  color: #6b7280;
}

.email-cell {
  min-width: 200px;
}

.email {
  color: #6b7280;
  font-size: 0.9rem;
}

/* Badges */
.role-badge, .status-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.025em;
}

.role-badge.admin {
  background: #fef3c7;
  color: #92400e;
  border: 1px solid #fbbf24;
}

.role-badge.user {
  background: #dbeafe;
  color: #1e40af;
  border: 1px solid #60a5fa;
}

.status-badge.active {
  background: #dcfce7;
  color: #166534;
  border: 1px solid #22c55e;
}

.status-badge.inactive {
  background: #fee2e2;
  color: #991b1b;
  border: 1px solid #ef4444;
}

.status-badge.deleted {
  background: #fee2e2;
  color: #dc2626;
  border: 1px solid #fecaca;
}

.date-cell {
  min-width: 120px;
  color: #6b7280;
  font-size: 0.85rem;
}

.actions-cell {
  width: 140px;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
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

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover:not(:disabled) {
  background: #d97706;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover:not(:disabled) {
  background: #059669;
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

/* Modale de confirmation */
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

/* États vides */
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

/* Responsive */
@media (max-width: 768px) {
  .user-admin {
    padding: 1.5rem;
  }
  
  .section-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
  }
  
  .header-actions {
    flex-direction: column;
    gap: 1rem;
  }
  
  .search-input {
    width: 100%;
  }
  
  .stats-cards {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .users-table th,
  .users-table td {
    padding: 0.75rem 0.5rem;
  }
  
  .action-buttons {
    flex-direction: column;
    gap: 0.25rem;
  }
}
</style>
