<template>
  <div class="app-background mobile-safe p-3xl">

    <PageHeader
      title="Administration"
      subtitle="Tableau de bord administrateur - Gestion de l'application"
    />
    
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
import PageHeader from '@/components/ui/PageHeader.vue'
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
