<template>
  <div class="stats-admin">
    <div class="section-header">
      <h2>Statistiques et Analytics</h2>
      <div class="header-actions">
        <div class="date-filter">
          <select v-model="selectedPeriod" class="period-select">
            <option value="7">7 derniers jours</option>
            <option value="30">30 derniers jours</option>
            <option value="90">3 derniers mois</option>
            <option value="365">Année en cours</option>
          </select>
        </div>
        <button @click="refreshStats" class="btn btn-secondary" :disabled="loading">
          🔄 Actualiser
        </button>
        <button @click="exportStats" class="btn btn-primary" :disabled="loading">
          📊 Exporter
        </button>
      </div>
    </div>

    <!-- États de chargement -->
    <div v-if="loading" class="loading-state">
      <div class="spinner"></div>
      <p>Chargement des statistiques...</p>
    </div>

    <div v-else-if="error" class="error-state">
      <p>❌ Erreur lors du chargement des statistiques : {{ error }}</p>
      <button @click="refreshStats" class="btn btn-secondary">🔄 Réessayer</button>
    </div>

    <!-- Contenu principal -->
    <div v-else class="stats-content">
      
      <!-- Cartes de métriques principales -->
      <div class="metrics-grid">
        <div class="metric-card">
          <div class="metric-icon">📄</div>
          <div class="metric-content">
            <div class="metric-number">{{ stats.totalInfos }}</div>
            <div class="metric-label">Articles publiés</div>
            <div class="metric-change" :class="{ positive: stats.infosGrowth >= 0, negative: stats.infosGrowth < 0 }">
              {{ stats.infosGrowth >= 0 ? '+' : '' }}{{ stats.infosGrowth }}% vs période précédente
            </div>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-icon">👁️</div>
          <div class="metric-content">
            <div class="metric-number">{{ formatNumber(stats.totalViews) }}</div>
            <div class="metric-label">Vues total</div>
            <div class="metric-change" :class="{ positive: stats.viewsGrowth >= 0, negative: stats.viewsGrowth < 0 }">
              {{ stats.viewsGrowth >= 0 ? '+' : '' }}{{ stats.viewsGrowth }}% vs période précédente
            </div>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-icon">👥</div>
          <div class="metric-content">
            <div class="metric-number">{{ stats.activeUsers }}</div>
            <div class="metric-label">Utilisateurs actifs</div>
            <div class="metric-change" :class="{ positive: stats.usersGrowth >= 0, negative: stats.usersGrowth < 0 }">
              {{ stats.usersGrowth >= 0 ? '+' : '' }}{{ stats.usersGrowth }}% vs période précédente
            </div>
          </div>
        </div>

        <div class="metric-card">
          <div class="metric-icon">🎯</div>
          <div class="metric-content">
            <div class="metric-number">{{ formatNumber(stats.emotionTracking) }}</div>
            <div class="metric-label">Données émotionnelles</div>
            <div class="metric-change" :class="{ positive: stats.emotionGrowth >= 0, negative: stats.emotionGrowth < 0 }">
              {{ stats.emotionGrowth >= 0 ? '+' : '' }}{{ stats.emotionGrowth }}% vs période précédente
            </div>
          </div>
        </div>
      </div>

      <!-- Graphiques -->
      <div class="charts-grid">
        
        <!-- Graphique des vues dans le temps -->
        <div class="chart-card">
          <div class="chart-header">
            <h3>Évolution des vues</h3>
            <div class="chart-legend">
              <span class="legend-item views">📈 Vues quotidiennes</span>
            </div>
          </div>
          <div class="chart-container">
            <div class="chart-placeholder">
              <div class="placeholder-icon">📈</div>
              <p>Graphique des vues dans le temps</p>
              <p class="chart-note">Intégration à venir avec Chart.js ou D3.js</p>
            </div>
          </div>
        </div>

        <!-- Top articles -->
        <div class="chart-card">
          <div class="chart-header">
            <h3>Articles les plus consultés</h3>
          </div>
          <div class="top-articles-list">
            <div v-for="(article, index) in stats.topArticles" :key="article.id" class="article-item">
              <div class="article-rank">{{ index + 1 }}</div>
              <div class="article-info">
                <div class="article-title">{{ article.titre }}</div>
                <div class="article-views">{{ formatNumber(article.views) }} vues</div>
              </div>
              <div class="article-bar">
                <div class="bar-fill" :style="{ width: (article.views / stats.topArticles[0].views * 100) + '%' }"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Répartition par catégorie -->
        <div class="chart-card">
          <div class="chart-header">
            <h3>Répartition par menu/catégorie</h3>
          </div>
          <div class="category-stats">
            <div v-for="category in stats.categoryStats" :key="category.name" class="category-item">
              <div class="category-info">
                <span class="category-name">{{ category.name }}</span>
                <span class="category-count">{{ category.count }} articles</span>
              </div>
              <div class="category-bar">
                <div 
                  class="bar-fill"
                  :style="{ 
                    width: (category.count / stats.categoryStats[0].count * 100) + '%',
                    backgroundColor: category.color 
                  }"
                ></div>
              </div>
              <div class="category-percentage">{{ Math.round(category.percentage) }}%</div>
            </div>
          </div>
        </div>

        <!-- Données émotionnelles -->
        <div class="chart-card">
          <div class="chart-header">
            <h3>Tracking émotionnel</h3>
          </div>
          <div class="emotion-stats">
            <div v-for="emotion in stats.emotionStats" :key="emotion.name" class="emotion-item">
              <div class="emotion-icon">{{ emotion.icon }}</div>
              <div class="emotion-info">
                <div class="emotion-name">{{ emotion.name }}</div>
                <div class="emotion-count">{{ formatNumber(emotion.count) }} entrées</div>
              </div>
              <div class="emotion-percentage">{{ Math.round(emotion.percentage) }}%</div>
            </div>
          </div>
        </div>

      </div>

      <!-- Activité récente -->
      <div class="recent-activity">
        <h3>Activité récente</h3>
        <div class="activity-list">
          <div v-for="activity in stats.recentActivity" :key="activity.id" class="activity-item">
            <div class="activity-icon">{{ activity.icon }}</div>
            <div class="activity-content">
              <div class="activity-description">{{ activity.description }}</div>
              <div class="activity-time">{{ formatTimeAgo(activity.timestamp) }}</div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'

// Données réactives
const loading = ref(false)
const error = ref(null)
const selectedPeriod = ref('30')
const stats = ref({
  totalInfos: 0,
  totalViews: 0,
  activeUsers: 0,
  emotionTracking: 0,
  infosGrowth: 0,
  viewsGrowth: 0,
  usersGrowth: 0,
  emotionGrowth: 0,
  topArticles: [],
  categoryStats: [],
  emotionStats: [],
  recentActivity: []
})

// Watcher pour recharger les stats quand la période change
watch(selectedPeriod, () => {
  refreshStats()
})

// Chargement au montage
onMounted(() => {
  refreshStats()
})

// Chargement/rechargement des statistiques
const refreshStats = async () => {
  loading.value = true
  error.value = null
  
  try {
    // Simulation de chargement - À remplacer par l'appel API réel
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // Données simulées
    stats.value = {
      totalInfos: 48,
      totalViews: 12547,
      activeUsers: 156,
      emotionTracking: 2341,
      infosGrowth: 12.5,
      viewsGrowth: 8.3,
      usersGrowth: -2.1,
      emotionGrowth: 15.7,
      topArticles: [
        { id: 1, titre: "Guide complet de méditation", views: 1567 },
        { id: 2, titre: "Exercices de respiration anti-stress", views: 1234 },
        { id: 3, titre: "Comment gérer ses émotions", views: 987 },
        { id: 4, titre: "Techniques de relaxation", views: 756 },
        { id: 5, titre: "Mindfulness au quotidien", views: 543 }
      ],
      categoryStats: [
        { name: "Bien-être", count: 18, percentage: 37.5, color: "#22c55e" },
        { name: "Méditation", count: 12, percentage: 25, color: "#3b82f6" },
        { name: "Nutrition", count: 8, percentage: 16.7, color: "#f59e0b" },
        { name: "Sport", count: 6, percentage: 12.5, color: "#ef4444" },
        { name: "Sommeil", count: 4, percentage: 8.3, color: "#8b5cf6" }
      ],
      emotionStats: [
        { name: "Joie", icon: "😊", count: 1245, percentage: 35.2 },
        { name: "Calme", icon: "😌", count: 987, percentage: 28.9 },
        { name: "Stress", icon: "😰", count: 654, percentage: 18.5 },
        { name: "Tristesse", icon: "😢", count: 432, percentage: 12.1 },
        { name: "Colère", icon: "😠", count: 189, percentage: 5.3 }
      ],
      recentActivity: [
        { id: 1, icon: "📝", description: "Nouvel article publié: \"Techniques de méditation avancées\"", timestamp: new Date(Date.now() - 2 * 60 * 60 * 1000) },
        { id: 2, icon: "👤", description: "Nouvel utilisateur inscrit: marie.dubois@example.com", timestamp: new Date(Date.now() - 4 * 60 * 60 * 1000) },
        { id: 3, icon: "📊", description: "L'article \"Guide du sommeil\" a atteint 1000 vues", timestamp: new Date(Date.now() - 6 * 60 * 60 * 1000) },
        { id: 4, icon: "💝", description: "10 nouvelles données émotionnelles enregistrées", timestamp: new Date(Date.now() - 8 * 60 * 60 * 1000) },
        { id: 5, icon: "🔧", description: "Maintenance système effectuée avec succès", timestamp: new Date(Date.now() - 12 * 60 * 60 * 1000) }
      ]
    }
  } catch (err) {
    console.error('Erreur lors du chargement des statistiques:', err)
    error.value = err.message || 'Erreur lors du chargement des statistiques'
  } finally {
    loading.value = false
  }
}

// Export des statistiques (simulé)
const exportStats = async () => {
  try {
    // Simulation d'export - À remplacer par l'appel API réel
    await new Promise(resolve => setTimeout(resolve, 1000))
    
    const data = {
      period: selectedPeriod.value,
      exportDate: new Date().toISOString(),
      stats: stats.value
    }
    
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `cesizen-stats-${new Date().toISOString().split('T')[0]}.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Erreur lors de l\'export:', err)
    alert('Erreur lors de l\'export des statistiques')
  }
}

// Utilitaires
const formatNumber = (num) => {
  if (num >= 1000000) {
    return (num / 1000000).toFixed(1) + 'M'
  } else if (num >= 1000) {
    return (num / 1000).toFixed(1) + 'K'
  }
  return num.toString()
}

const formatTimeAgo = (date) => {
  const now = new Date()
  const diffInMs = now - date
  const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60))
  const diffInDays = Math.floor(diffInHours / 24)
  
  if (diffInDays > 0) {
    return `Il y a ${diffInDays} jour${diffInDays > 1 ? 's' : ''}`
  } else if (diffInHours > 0) {
    return `Il y a ${diffInHours} heure${diffInHours > 1 ? 's' : ''}`
  } else {
    return 'Il y a moins d\'une heure'
  }
}
</script>

<style scoped>
.stats-admin {
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

.period-select {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  background: white;
  transition: all 0.2s ease;
  cursor: pointer;
}

.period-select:focus {
  outline: none;
  border-color: #22c55e;
  box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
}

/* États */
.loading-state, .error-state {
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

/* Métriques principales */
.metrics-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.metric-card {
  background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 1.5rem;
  display: flex;
  align-items: center;
  gap: 1rem;
  transition: transform 0.2s ease;
}

.metric-card:hover {
  transform: translateY(-2px);
}

.metric-icon {
  font-size: 3rem;
  padding: 1rem;
  background: rgba(34, 197, 94, 0.1);
  border-radius: 12px;
  flex-shrink: 0;
}

.metric-content {
  flex: 1;
}

.metric-number {
  font-size: 2.5rem;
  font-weight: 700;
  color: #1f2937;
  line-height: 1;
  margin-bottom: 0.25rem;
}

.metric-label {
  font-size: 1rem;
  color: #6b7280;
  margin-bottom: 0.5rem;
}

.metric-change {
  font-size: 0.85rem;
  font-weight: 500;
}

.metric-change.positive {
  color: #059669;
}

.metric-change.negative {
  color: #dc2626;
}

/* Graphiques */
.charts-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
  gap: 1.5rem;
  margin-bottom: 2rem;
}

.chart-card {
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.chart-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.chart-header h3 {
  color: #1f2937;
  margin: 0;
  font-size: 1.1rem;
}

.chart-legend {
  display: flex;
  gap: 1rem;
}

.legend-item {
  font-size: 0.85rem;
  color: #6b7280;
}

/* Placeholder pour graphiques */
.chart-container {
  height: 250px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.chart-placeholder {
  text-align: center;
  color: #9ca3af;
}

.placeholder-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.chart-note {
  font-size: 0.8rem;
  font-style: italic;
  margin-top: 0.5rem;
}

/* Top articles */
.top-articles-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.article-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
}

.article-rank {
  width: 24px;
  height: 24px;
  background: #22c55e;
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.8rem;
  font-weight: 600;
  flex-shrink: 0;
}

.article-info {
  flex: 1;
}

.article-title {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.article-views {
  font-size: 0.85rem;
  color: #6b7280;
}

.article-bar {
  width: 80px;
  height: 6px;
  background: #e5e7eb;
  border-radius: 3px;
  overflow: hidden;
}

.bar-fill {
  height: 100%;
  background: #22c55e;
  transition: width 0.3s ease;
}

/* Stats par catégorie */
.category-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.category-item {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.category-info {
  flex: 1;
}

.category-name {
  font-weight: 500;
  color: #1f2937;
  margin-right: 0.5rem;
}

.category-count {
  font-size: 0.85rem;
  color: #6b7280;
}

.category-bar {
  width: 100px;
  height: 8px;
  background: #e5e7eb;
  border-radius: 4px;
  overflow: hidden;
}

.category-percentage {
  min-width: 40px;
  text-align: right;
  font-size: 0.85rem;
  color: #6b7280;
  font-weight: 500;
}

/* Stats émotionnelles */
.emotion-stats {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.emotion-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
}

.emotion-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.emotion-info {
  flex: 1;
}

.emotion-name {
  font-weight: 500;
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.emotion-count {
  font-size: 0.85rem;
  color: #6b7280;
}

.emotion-percentage {
  min-width: 50px;
  text-align: right;
  font-weight: 600;
  color: #1f2937;
}

/* Activité récente */
.recent-activity {
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 1px solid #e5e7eb;
}

.recent-activity h3 {
  color: #1f2937;
  margin-bottom: 1.5rem;
  font-size: 1.25rem;
}

.activity-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.activity-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 8px;
  border-left: 4px solid #22c55e;
}

.activity-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.activity-content {
  flex: 1;
}

.activity-description {
  color: #1f2937;
  margin-bottom: 0.25rem;
}

.activity-time {
  font-size: 0.85rem;
  color: #6b7280;
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

/* Responsive */
@media (max-width: 768px) {
  .stats-admin {
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
  
  .metrics-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .charts-grid {
    grid-template-columns: 1fr;
    gap: 1rem;
  }
  
  .chart-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 0.5rem;
  }
  
  .article-item,
  .category-item,
  .emotion-item,
  .activity-item {
    padding: 0.5rem;
    gap: 0.5rem;
  }
}

@media (max-width: 640px) {
  .metric-card {
    flex-direction: column;
    text-align: center;
    gap: 0.75rem;
  }
  
  .metric-icon {
    align-self: center;
  }
  
  .article-bar,
  .category-bar {
    width: 60px;
  }
}
</style>
