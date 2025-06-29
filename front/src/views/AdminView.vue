<template>
  <div class="admin-view">
    <div class="admin-header">
      <h1>Administration</h1>
      <p>Tableau de bord administrateur - Gestion de l'application</p>
    </div>
    
    <div v-if="isAdmin" class="admin-content">
      <AdminNavigation 
        :sections="adminSections"
        :active-section="activeSection"
        @section-change="setActiveSection"
      />

      <div v-if="activeSection === 'infos'" class="admin-section">
        <InfoAdmin />
      </div>

      <div v-else-if="activeSection === 'users'" class="admin-section">
        <UserAdmin />
      </div>
      
      <div v-else-if="activeSection === 'emotions'" class="admin-section">
        <EmotionAdmin />
      </div>

      <div v-else-if="activeSection === 'stats'" class="admin-section">
        <StatsAdmin />
      </div>

      <div v-else-if="activeSection === 'settings'" class="admin-section">
        <SettingsAdmin />
      </div>
    </div>
    
    <AccessDenied v-else />
  </div>
</template>

<script setup>
import { useAdminNavigation } from '@/composables/useAdminNavigation.js'
import AdminNavigation from '@/components/admin/AdminNavigation.vue'
import AccessDenied from '@/components/ui/AccessDenied.vue'
import InfoAdmin from '@/components/admin/InfoAdmin.vue'
import UserAdmin from '@/components/admin/UserAdmin.vue'
import EmotionAdmin from '@/components/admin/EmotionAdmin.vue'
import StatsAdmin from '@/components/admin/StatsAdmin.vue'
import SettingsAdmin from '@/components/admin/SettingsAdmin.vue'

const { activeSection, isAdmin, adminSections, setActiveSection } = useAdminNavigation()
</script>

<style scoped>
.admin-view {
  padding: 1.5rem;
  max-width: 1400px;
  margin: 0 auto;
}

.admin-header {
  text-align: center;
  margin-bottom: 2rem;
  padding-bottom: 1.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.admin-header h1 {
  color: #2a5d49;
  margin-bottom: 0.5rem;
  font-size: 2.5rem;
  font-weight: 700;
}

.admin-header p {
  color: #6b7280;
  margin: 0.5rem 0;
}

.admin-content {
  margin-top: 2rem;
}

.admin-section {
  margin-bottom: 2rem;
}

@media (max-width: 768px) {
  .admin-view {
    padding: 1rem;
  }
  
  .admin-header h1 {
    font-size: 2rem;
  }
}
</style>
