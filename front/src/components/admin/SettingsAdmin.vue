<template>
  <div class="settings-admin">
    <div class="section-header">
      <h2>Paramètres système</h2>
      <div class="header-actions">
        <button @click="saveAllSettings" class="btn btn-primary" :disabled="saving || !hasChanges">
          💾 {{ saving ? 'Sauvegarde...' : 'Sauvegarder' }}
        </button>
        <button @click="resetToDefaults" class="btn btn-secondary">
          🔄 Restaurer par défaut
        </button>
      </div>
    </div>

    <!-- Navigation des sections de paramètres -->
    <div class="settings-navigation">
      <button 
        v-for="section in settingSections" 
        :key="section.id"
        @click="activeSettingSection = section.id"
        :class="['setting-nav-btn', { 'active': activeSettingSection === section.id }]"
      >
        <span class="nav-icon">{{ section.icon }}</span>
        {{ section.title }}
      </button>
    </div>

    <!-- Section Interface -->
    <div v-if="activeSettingSection === 'interface'" class="settings-section">
      <div class="section-title">
        <h3>🎨 Personnalisation de l'interface</h3>
        <p>Configurez l'apparence et le comportement de l'application</p>
      </div>

      <div class="settings-grid">
        <div class="setting-group">
          <label class="setting-label">Thème de l'application</label>
          <select v-model="settings.interface.theme" class="setting-input">
            <option value="light">Clair</option>
            <option value="dark">Sombre</option>
            <option value="auto">Automatique (système)</option>
          </select>
          <p class="setting-description">Choisissez le thème d'affichage de l'application</p>
        </div>

        <div class="setting-group">
          <label class="setting-label">Couleur principale</label>
          <div class="color-picker-group">
            <input 
              v-model="settings.interface.primaryColor" 
              type="color" 
              class="color-picker"
            />
            <input 
              v-model="settings.interface.primaryColor" 
              type="text" 
              class="color-input"
              placeholder="#22c55e"
            />
          </div>
          <p class="setting-description">Couleur principale utilisée dans l'interface</p>
        </div>

        <div class="setting-group">
          <label class="setting-label">Logo de l'application</label>
          <div class="file-upload-group">
            <input 
              type="file" 
              ref="logoInput"
              @change="handleLogoUpload"
              accept="image/*"
              class="file-input"
            />
            <button @click="$refs.logoInput.click()" class="btn btn-outline">
              📷 Choisir un logo
            </button>
            <div v-if="settings.interface.logo" class="current-logo">
              <img :src="settings.interface.logo" alt="Logo actuel" class="logo-preview" />
            </div>
          </div>
          <p class="setting-description">Logo affiché dans l'en-tête de l'application</p>
        </div>

        <div class="setting-group">
          <div class="setting-toggle">
            <input 
              v-model="settings.interface.showAnimations" 
              type="checkbox" 
              id="animations" 
              class="toggle-input"
            />
            <label for="animations" class="toggle-label">
              <span class="toggle-slider"></span>
            </label>
            <div class="toggle-text">
              <span class="toggle-title">Animations d'interface</span>
              <span class="toggle-description">Active les transitions et animations</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Notifications -->
    <div v-else-if="activeSettingSection === 'notifications'" class="settings-section">
      <div class="section-title">
        <h3>📧 Configuration des notifications</h3>
        <p>Gérez les notifications système et utilisateur</p>
      </div>

      <div class="settings-grid">
        <div class="setting-group">
          <div class="setting-toggle">
            <input 
              v-model="settings.notifications.emailEnabled" 
              type="checkbox" 
              id="email-notif" 
              class="toggle-input"
            />
            <label for="email-notif" class="toggle-label">
              <span class="toggle-slider"></span>
            </label>
            <div class="toggle-text">
              <span class="toggle-title">Notifications par email</span>
              <span class="toggle-description">Envoi d'emails automatiques aux utilisateurs</span>
            </div>
          </div>
        </div>

        <div class="setting-group">
          <label class="setting-label">Serveur SMTP</label>
          <input 
            v-model="settings.notifications.smtpHost" 
            type="text" 
            class="setting-input"
            placeholder="smtp.example.com"
          />
          <p class="setting-description">Adresse du serveur SMTP pour l'envoi d'emails</p>
        </div>

        <div class="setting-group">
          <label class="setting-label">Port SMTP</label>
          <input 
            v-model="settings.notifications.smtpPort" 
            type="number" 
            class="setting-input"
            placeholder="587"
          />
        </div>

        <div class="setting-group">
          <label class="setting-label">Email expéditeur</label>
          <input 
            v-model="settings.notifications.fromEmail" 
            type="email" 
            class="setting-input"
            placeholder="noreply@cesizen.com"
          />
        </div>

        <div class="setting-group">
          <div class="setting-toggle">
            <input 
              v-model="settings.notifications.welcomeEmail" 
              type="checkbox" 
              id="welcome-email" 
              class="toggle-input"
            />
            <label for="welcome-email" class="toggle-label">
              <span class="toggle-slider"></span>
            </label>
            <div class="toggle-text">
              <span class="toggle-title">Email de bienvenue</span>
              <span class="toggle-description">Envoi automatique lors de l'inscription</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Sécurité -->
    <div v-else-if="activeSettingSection === 'security'" class="settings-section">
      <div class="section-title">
        <h3>🔒 Paramètres de sécurité</h3>
        <p>Configuration de la sécurité et des accès</p>
      </div>

      <div class="settings-grid">
        <div class="setting-group">
          <label class="setting-label">Durée de session (minutes)</label>
          <input 
            v-model="settings.security.sessionDuration" 
            type="number" 
            class="setting-input"
            min="15"
            max="1440"
          />
          <p class="setting-description">Durée avant expiration automatique des sessions</p>
        </div>

        <div class="setting-group">
          <label class="setting-label">Tentatives de connexion maximum</label>
          <input 
            v-model="settings.security.maxLoginAttempts" 
            type="number" 
            class="setting-input"
            min="3"
            max="10"
          />
          <p class="setting-description">Nombre d'échecs avant blocage temporaire</p>
        </div>

        <div class="setting-group">
          <div class="setting-toggle">
            <input 
              v-model="settings.security.twoFactorRequired" 
              type="checkbox" 
              id="2fa-required" 
              class="toggle-input"
            />
            <label for="2fa-required" class="toggle-label">
              <span class="toggle-slider"></span>
            </label>
            <div class="toggle-text">
              <span class="toggle-title">Authentification à deux facteurs obligatoire</span>
              <span class="toggle-description">Force l'utilisation de la 2FA pour les admins</span>
            </div>
          </div>
        </div>

        <div class="setting-group">
          <div class="setting-toggle">
            <input 
              v-model="settings.security.passwordComplexity" 
              type="checkbox" 
              id="pwd-complexity" 
              class="toggle-input"
            />
            <label for="pwd-complexity" class="toggle-label">
              <span class="toggle-slider"></span>
            </label>
            <div class="toggle-text">
              <span class="toggle-title">Mots de passe complexes</span>
              <span class="toggle-description">Exige des mots de passe forts</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section Maintenance -->
    <div v-else-if="activeSettingSection === 'maintenance'" class="settings-section">
      <div class="section-title">
        <h3>🗄️ Gestion des sauvegardes et maintenance</h3>
        <p>Outils de maintenance et de sauvegarde du système</p>
      </div>

      <div class="maintenance-tools">
        <div class="tool-card">
          <div class="tool-icon">💾</div>
          <div class="tool-content">
            <h4>Sauvegarde de la base de données</h4>
            <p>Créer une sauvegarde complète des données</p>
            <button @click="createBackup" class="btn btn-primary" :disabled="backupInProgress">
              {{ backupInProgress ? 'Sauvegarde en cours...' : 'Créer une sauvegarde' }}
            </button>
          </div>
        </div>

        <div class="tool-card">
          <div class="tool-icon">🧹</div>
          <div class="tool-content">
            <h4>Nettoyage du cache</h4>
            <p>Vider le cache système pour optimiser les performances</p>
            <button @click="clearCache" class="btn btn-secondary" :disabled="cacheClearing">
              {{ cacheClearing ? 'Nettoyage...' : 'Vider le cache' }}
            </button>
          </div>
        </div>

        <!-- <div class="tool-card">
          <div class="tool-icon">📊</div>
          <div class="tool-content">
            <h4>Optimisation de la base</h4>
            <p>Optimiser et réparer les tables de la base de données</p>
            <button @click="optimizeDatabase" class="btn btn-warning" :disabled="dbOptimizing">
              {{ dbOptimizing ? 'Optimisation...' : 'Optimiser la base' }}
            </button>
          </div>
        </div> -->

        <div class="tool-card">
          <div class="tool-icon">📋</div>
          <div class="tool-content">
            <h4>Logs système</h4>
            <p>Consulter et télécharger les logs d'activité</p>
            <button @click="downloadLogs" class="btn btn-outline">
              📥 Télécharger les logs
            </button>
          </div>
        </div>
      </div>

      <!-- Mode maintenance -->
      <!-- <div class="maintenance-mode">
        <div class="mode-header">
          <h4>🚧 Mode maintenance</h4>
          <div class="mode-toggle">
            <input 
              v-model="settings.maintenance.enabled" 
              type="checkbox" 
              id="maintenance-mode" 
              class="toggle-input"
            />
            <label for="maintenance-mode" class="toggle-label">
              <span class="toggle-slider"></span>
            </label>
          </div>
        </div>
        <p>Active le mode maintenance pour bloquer l'accès aux utilisateurs</p>
        <div v-if="settings.maintenance.enabled" class="maintenance-config">
          <div class="setting-group">
            <label class="setting-label">Message de maintenance</label>
            <textarea 
              v-model="settings.maintenance.message" 
              class="setting-textarea"
              rows="3"
              placeholder="L'application est temporairement en maintenance..."
            ></textarea>
          </div>
        </div>
      </div> -->
    </div>

    <!-- Indicateur de changements non sauvegardés -->
    <div v-if="hasChanges" class="unsaved-changes">
      ⚠️ Vous avez des modifications non sauvegardées
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'

// Navigation des sections
const activeSettingSection = ref('interface')
const settingSections = [
  { id: 'interface', title: 'Interface', icon: '🎨' },
  // { id: 'notifications', title: 'Notifications', icon: '📧' },
  // { id: 'security', title: 'Sécurité', icon: '🔒' },
  { id: 'maintenance', title: 'Maintenance', icon: '🗄️' }
]

// États
const saving = ref(false)
const backupInProgress = ref(false)
const cacheClearing = ref(false)
const dbOptimizing = ref(false)

// Paramètres par défaut
const defaultSettings = {
  interface: {
    theme: 'light',
    primaryColor: '#22c55e',
    logo: null,
    showAnimations: true
  },
  notifications: {
    emailEnabled: true,
    smtpHost: 'localhost',
    smtpPort: 587,
    fromEmail: 'noreply@cesizen.com',
    welcomeEmail: true
  },
  security: {
    sessionDuration: 120,
    maxLoginAttempts: 5,
    twoFactorRequired: false,
    passwordComplexity: true
  },
  maintenance: {
    enabled: false,
    message: 'L\'application est temporairement en maintenance. Nous reviendrons bientôt !'
  }
}

// Paramètres actuels
const settings = ref(JSON.parse(JSON.stringify(defaultSettings)))
const originalSettings = ref(JSON.parse(JSON.stringify(defaultSettings)))

// Computed pour détecter les changements
const hasChanges = computed(() => {
  return JSON.stringify(settings.value) !== JSON.stringify(originalSettings.value)
})

// Chargement des paramètres au montage
onMounted(() => {
  loadSettings()
})

// Watcher pour autosave (optionnel)
watch(settings, () => {
  // Ici, on pourrait implémenter un autosave
  // console.log('Settings changed:', settings.value)
}, { deep: true })

// Chargement des paramètres
const loadSettings = async () => {
  try {
    // Simulation - À remplacer par l'appel API réel
    const savedSettings = localStorage.getItem('cesizen-admin-settings')
    if (savedSettings) {
      const parsed = JSON.parse(savedSettings)
      settings.value = { ...defaultSettings, ...parsed }
      originalSettings.value = JSON.parse(JSON.stringify(settings.value))
    }
  } catch (err) {
    console.error('Erreur lors du chargement des paramètres:', err)
  }
}

// Sauvegarde de tous les paramètres
const saveAllSettings = async () => {
  saving.value = true
  
  try {
    // Simulation - À remplacer par l'appel API réel
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    localStorage.setItem('cesizen-admin-settings', JSON.stringify(settings.value))
    originalSettings.value = JSON.parse(JSON.stringify(settings.value))
    
    // Notification de succès
    alert('Paramètres sauvegardés avec succès !')
  } catch (err) {
    console.error('Erreur lors de la sauvegarde:', err)
    alert('Erreur lors de la sauvegarde des paramètres')
  } finally {
    saving.value = false
  }
}

// Restauration des paramètres par défaut
const resetToDefaults = () => {
  if (confirm('Êtes-vous sûr de vouloir restaurer les paramètres par défaut ? Cette action est irréversible.')) {
    settings.value = JSON.parse(JSON.stringify(defaultSettings))
  }
}

// Gestion de l'upload de logo
const handleLogoUpload = (event) => {
  const file = event.target.files[0]
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      settings.value.interface.logo = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

// Outils de maintenance
const createBackup = async () => {
  backupInProgress.value = true
  
  try {
    // Simulation - À remplacer par l'appel API réel
    await new Promise(resolve => setTimeout(resolve, 3000))
    
    // Créer un fichier de sauvegarde fictif
    const backupData = {
      timestamp: new Date().toISOString(),
      version: '1.0.0',
      settings: settings.value,
      // Ici seraient incluses les données de la base
    }
    
    const blob = new Blob([JSON.stringify(backupData, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `cesizen-backup-${new Date().toISOString().split('T')[0]}.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
    
    alert('Sauvegarde créée avec succès !')
  } catch (err) {
    console.error('Erreur lors de la sauvegarde:', err)
    alert('Erreur lors de la création de la sauvegarde')
  } finally {
    backupInProgress.value = false
  }
}

const clearCache = async () => {
  cacheClearing.value = true
  
  try {
    // Simulation - À remplacer par l'appel API réel
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // Vider le cache local du navigateur
    if ('caches' in window) {
      const cacheNames = await caches.keys()
      await Promise.all(cacheNames.map(name => caches.delete(name)))
    }
    
    alert('Cache vidé avec succès !')
  } catch (err) {
    console.error('Erreur lors du nettoyage du cache:', err)
    alert('Erreur lors du nettoyage du cache')
  } finally {
    cacheClearing.value = false
  }
}

const optimizeDatabase = async () => {
  dbOptimizing.value = true
  
  try {
    // Simulation - À remplacer par l'appel API réel
    await new Promise(resolve => setTimeout(resolve, 4000))
    
    alert('Base de données optimisée avec succès !')
  } catch (err) {
    console.error('Erreur lors de l\'optimisation:', err)
    alert('Erreur lors de l\'optimisation de la base de données')
  } finally {
    dbOptimizing.value = false
  }
}

const downloadLogs = async () => {
  try {
    // Simulation - À remplacer par l'appel API réel
    const logData = [
      '[2024-06-25 14:30:15] INFO: User login successful - jean.dupont@example.com',
      '[2024-06-25 14:32:20] INFO: Article created - "Guide de méditation"',
      '[2024-06-25 14:35:45] WARNING: Failed login attempt - invalid-email@test.com',
      '[2024-06-25 14:40:12] INFO: Admin settings updated',
      '[2024-06-25 14:42:30] ERROR: Database connection timeout - retrying...',
      '[2024-06-25 14:42:35] INFO: Database connection restored'
    ].join('\n')
    
    const blob = new Blob([logData], { type: 'text/plain' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `cesizen-logs-${new Date().toISOString().split('T')[0]}.log`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Erreur lors du téléchargement des logs:', err)
    alert('Erreur lors du téléchargement des logs')
  }
}
</script>

<style scoped>
.settings-admin {
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

/* Navigation des paramètres */
.settings-navigation {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  overflow-x: auto;
}

.setting-nav-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  background: white;
  color: #6b7280;
  font-size: 0.95rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.setting-nav-btn:hover {
  background: #f3f4f6;
  color: #374151;
  transform: translateY(-1px);
}

.setting-nav-btn.active {
  background: #2a5d49;
  color: white;
  box-shadow: 0 4px 6px rgba(42, 93, 73, 0.2);
}

.nav-icon {
  font-size: 1.1rem;
}

/* Sections */
.settings-section {
  margin-bottom: 2rem;
}

.section-title {
  margin-bottom: 2rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.section-title h3 {
  color: #1f2937;
  margin-bottom: 0.5rem;
  font-size: 1.25rem;
}

.section-title p {
  color: #6b7280;
  margin: 0;
}

/* Grid des paramètres */
.settings-grid {
  display: grid;
  gap: 2rem;
  max-width: 800px;
}

.setting-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.setting-label {
  font-weight: 600;
  color: #374151;
  font-size: 0.95rem;
}

.setting-input, .setting-textarea {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  background: white;
  transition: all 0.2s ease;
}

.setting-input:focus, .setting-textarea:focus {
  outline: none;
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

.setting-description {
  color: #6b7280;
  font-size: 0.85rem;
  margin: 0;
  line-height: 1.4;
}

/* Color picker */
.color-picker-group {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.color-picker {
  width: 60px;
  height: 40px;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  cursor: pointer;
}

.color-input {
  flex: 1;
  max-width: 200px;
}

/* File upload */
.file-upload-group {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.file-input {
  display: none;
}

.current-logo {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.logo-preview {
  max-width: 100px;
  max-height: 60px;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

/* Toggle switches */
.setting-toggle {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.toggle-input {
  display: none;
}

.toggle-label {
  position: relative;
  width: 50px;
  height: 24px;
  background: #e5e7eb;
  border-radius: 12px;
  cursor: pointer;
  transition: background-color 0.2s ease;
}

.toggle-slider {
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  transition: transform 0.2s ease;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

.toggle-input:checked + .toggle-label {
  background: #22c55e;
}

.toggle-input:checked + .toggle-label .toggle-slider {
  transform: translateX(26px);
}

.toggle-text {
  flex: 1;
}

.toggle-title {
  display: block;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.25rem;
}

.toggle-description {
  display: block;
  font-size: 0.85rem;
  color: #6b7280;
}

/* Outils de maintenance */
.maintenance-tools {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.tool-card {
  background: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease;
}

.tool-card:hover {
  transform: translateY(-2px);
}

.tool-icon {
  font-size: 2.5rem;
  flex-shrink: 0;
}

.tool-content {
  flex: 1;
}

.tool-content h4 {
  color: #1f2937;
  margin: 0 0 0.5rem 0;
  font-size: 1rem;
}

.tool-content p {
  color: #6b7280;
  margin: 0 0 1rem 0;
  font-size: 0.9rem;
  line-height: 1.4;
}

/* Mode maintenance */
.maintenance-mode {
  background: #fef3c7;
  border: 1px solid #fbbf24;
  border-radius: 12px;
  padding: 1.5rem;
  margin-top: 2rem;
}

.mode-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.mode-header h4 {
  color: #92400e;
  margin: 0;
  font-size: 1.1rem;
}

.maintenance-config {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid #fbbf24;
}

/* Indicateur de changements */
.unsaved-changes {
  position: fixed;
  bottom: 2rem;
  right: 2rem;
  background: #fbbf24;
  color: #92400e;
  padding: 1rem 1.5rem;
  border-radius: 8px;
  font-weight: 600;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
  from {
    transform: translateX(100%);
  }
  to {
    transform: translateX(0);
  }
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

.btn-warning {
  background: #f59e0b;
  color: white;
}

.btn-warning:hover:not(:disabled) {
  background: #d97706;
}

.btn-outline {
  background: transparent;
  color: #374151;
  border: 2px solid #e5e7eb;
}

.btn-outline:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

/* Responsive */
@media (max-width: 768px) {
  .settings-admin {
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
  
  .settings-navigation {
    flex-direction: column;
    gap: 0.5rem;
  }
  
  .setting-nav-btn {
    justify-content: center;
  }
  
  .maintenance-tools {
    grid-template-columns: 1fr;
  }
  
  .tool-card {
    flex-direction: column;
    text-align: center;
    gap: 1rem;
  }
  
  .color-picker-group {
    flex-direction: column;
    align-items: stretch;
  }
  
  .mode-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
  
  .unsaved-changes {
    position: relative;
    bottom: auto;
    right: auto;
    margin-top: 2rem;
  }
}

@media (max-width: 640px) {
  .setting-toggle {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.75rem;
  }
  
  .toggle-text {
    text-align: center;
  }
}
</style>
