<template>
  <div v-if="isNative" class="mobile-info">
    <div class="device-info">
      <h3>📱 Informations de l'appareil</h3>
      <div v-if="deviceInfo" class="info-grid">
        <div class="info-item">
          <span class="label">Plateforme:</span>
          <span class="value">{{ deviceInfo.platform }}</span>
        </div>
        <div class="info-item">
          <span class="label">OS Version:</span>
          <span class="value">{{ deviceInfo.osVersion }}</span>
        </div>
        <div class="info-item">
          <span class="label">Modèle:</span>
          <span class="value">{{ deviceInfo.model }}</span>
        </div>
        <div class="info-item">
          <span class="label">Fabricant:</span>
          <span class="value">{{ deviceInfo.manufacturer }}</span>
        </div>
      </div>
    </div>

    <div class="network-info">
      <h3>📶 Statut réseau</h3>
      <div v-if="networkStatus" class="info-grid">
        <div class="info-item">
          <span class="label">Connecté:</span>
          <span class="value" :class="{ 'online': isOnline(), 'offline': !isOnline() }">
            {{ isOnline() ? 'Oui' : 'Non' }}
          </span>
        </div>
        <div v-if="isOnline()" class="info-item">
          <span class="label">Type:</span>
          <span class="value">{{ networkStatus.connectionType }}</span>
        </div>
      </div>
    </div>

    <button 
      @click="testHaptic" 
      class="haptic-btn"
    >
      🔔 Tester les vibrations
    </button>
  </div>
  <div v-else class="web-info">
    <p>🌐 Application en mode web</p>
  </div>
</template>

<script setup>
import { useCapacitor } from '@/composables/useCapacitor.js'

const { isNative, deviceInfo, networkStatus, hapticFeedback, isOnline } = useCapacitor()

const testHaptic = async () => {
  await hapticFeedback()
}
</script>

<style scoped>
.mobile-info {
  background: white;
  border-radius: 12px;
  padding: 20px;
  margin: 20px 0;
  box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
}

.device-info, .network-info {
  margin-bottom: 20px;
}

h3 {
  margin: 0 0 15px 0;
  color: #1f2937;
  font-weight: 600;
}

.info-grid {
  display: grid;
  gap: 10px;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 8px 0;
  border-bottom: 1px solid #e5e7eb;
}

.info-item:last-child {
  border-bottom: none;
}

.label {
  font-weight: 500;
  color: #6b7280;
}

.value {
  font-weight: 600;
  color: #1f2937;
}

.value.online {
  color: #10b981;
}

.value.offline {
  color: #ef4444;
}

.haptic-btn {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: transform 0.2s;
}

.haptic-btn:hover {
  transform: translateY(-2px);
}

.haptic-btn:active {
  transform: translateY(0);
}

.web-info {
  background: #f3f4f6;
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  padding: 20px;
  text-align: center;
  color: #6b7280;
  font-weight: 500;
}
</style>
