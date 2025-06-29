<template>
  <div class="emotion-calendar">
    <!-- En-tête du calendrier -->
    <div class="calendar-header">
      <button @click="previousMonth" class="nav-button" :disabled="loading">
        <ChevronLeft :size="20" />
      </button>
      <h2 class="month-title">{{ formattedMonth }}</h2>
      <button @click="nextMonth" class="nav-button" :disabled="loading">
        <ChevronRight :size="20" />
      </button>
    </div>

    <!-- Jours de la semaine -->
    <div class="weekdays">
      <div v-for="day in weekdays" :key="day" class="weekday">
        {{ day }}
      </div>
    </div>

    <!-- Grille du calendrier -->
    <div class="calendar-grid">
      <div
        v-for="day in calendarDays"
        :key="`${day.date.getTime()}`"
        class="calendar-day"
        :class="{
          'other-month': !day.isCurrentMonth,
          'today': day.isToday,
          'selected': day.isSelected,
          'has-trackers': day.trackers.length > 0
        }"
        @click="selectDay(day)"
      >
        <div class="day-number">{{ day.date.getDate() }}</div>
        <div class="day-trackers" v-if="day.trackers.length > 0">
          <div 
            class="tracker-gradient" 
            :style="{ background: generateGradient(day.trackers) }"
          ></div>
          <div class="tracker-count">{{ day.trackers.length }}</div>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="calendar-loading">
      <div class="spinner"></div>
      <p>Chargement du calendrier...</p>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import { ChevronLeft, ChevronRight } from 'lucide-vue-next'

const props = defineProps({
  trackers: {
    type: Array,
    default: () => []
  },
  selectedDate: {
    type: Date,
    default: () => new Date()
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['daySelected', 'monthChanged'])

const currentDate = ref(new Date(props.selectedDate))
const selectedDay = ref(null)

const weekdays = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim']

// Computed pour le titre du mois
const formattedMonth = computed(() => {
  return currentDate.value.toLocaleDateString('fr-FR', {
    month: 'long',
    year: 'numeric'
  }).replace(/^\w/, c => c.toUpperCase())
})

// Computed pour générer les jours du calendrier
const calendarDays = computed(() => {
  const year = currentDate.value.getFullYear()
  const month = currentDate.value.getMonth()
  
  // Premier jour du mois
  const firstDay = new Date(year, month, 1)
  // Dernier jour du mois
  const lastDay = new Date(year, month + 1, 0)
  
  // Ajuster le premier jour pour commencer le lundi (0 = dimanche, 1 = lundi, etc.)
  const startDate = new Date(firstDay)
  const dayOfWeek = firstDay.getDay()
  const daysToSubtract = dayOfWeek === 0 ? 6 : dayOfWeek - 1
  startDate.setDate(firstDay.getDate() - daysToSubtract)
  
  // Calculer le dernier jour à afficher (pour remplir la grille)
  const endDate = new Date(lastDay)
  const lastDayOfWeek = lastDay.getDay()
  const daysToAdd = lastDayOfWeek === 0 ? 0 : 7 - lastDayOfWeek
  endDate.setDate(lastDay.getDate() + daysToAdd)
  
  const days = []
  const currentDateCopy = new Date(startDate)
  const today = new Date()
  
  while (currentDateCopy <= endDate) {
    const day = {
      date: new Date(currentDateCopy),
      isCurrentMonth: currentDateCopy.getMonth() === month,
      isToday: isSameDay(currentDateCopy, today),
      isSelected: selectedDay.value ? isSameDay(currentDateCopy, selectedDay.value) : false,
      trackers: getTrackersForDay(currentDateCopy)
    }
    
    days.push(day)
    currentDateCopy.setDate(currentDateCopy.getDate() + 1)
  }
  
  return days
})

// Fonction pour vérifier si deux dates sont le même jour
const isSameDay = (date1, date2) => {
  return date1.getDate() === date2.getDate() &&
         date1.getMonth() === date2.getMonth() &&
         date1.getFullYear() === date2.getFullYear()
}

// Fonction pour récupérer les trackers d'un jour donné
const getTrackersForDay = (date) => {
  return props.trackers.filter(tracker => {
    const trackerDate = new Date(tracker.datetime)
    return isSameDay(trackerDate, date)
  }).sort((a, b) => new Date(a.datetime) - new Date(b.datetime))
}

// Fonction pour générer le dégradé basé sur les trackers
const generateGradient = (trackers) => {
  if (trackers.length === 0) return 'transparent'
  
  if (trackers.length === 1) {
    const color = trackers[0].emotion?.categorie?.couleur || '#e5e7eb'
    return color
  }
  
  const colors = trackers.map(tracker => 
    tracker.emotion?.categorie?.couleur || '#e5e7eb'
  )
  
  // Créer un dégradé linéaire avec les couleurs des émotions
  const gradientStops = colors.map((color, index) => {
    const percentage = (index / (colors.length - 1)) * 100
    return `${color} ${percentage}%`
  }).join(', ')
  
  return `linear-gradient(45deg, ${gradientStops})`
}

// Navigation du calendrier
const previousMonth = () => {
  const newDate = new Date(currentDate.value)
  newDate.setMonth(newDate.getMonth() - 1)
  currentDate.value = newDate
  emit('monthChanged', newDate)
}

const nextMonth = () => {
  const newDate = new Date(currentDate.value)
  newDate.setMonth(newDate.getMonth() + 1)
  currentDate.value = newDate
  emit('monthChanged', newDate)
}

// Sélection d'un jour
const selectDay = (day) => {
  if (!day.isCurrentMonth) return
  
  selectedDay.value = day.date
  emit('daySelected', day)
}

// Watcher pour la date sélectionnée depuis les props
watch(() => props.selectedDate, (newDate) => {
  selectedDay.value = newDate
  currentDate.value = new Date(newDate)
})

onMounted(() => {
  selectedDay.value = props.selectedDate
})
</script>

<style scoped>
.emotion-calendar {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  border-radius: 24px;
  padding: 1.5rem;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.calendar-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
  padding: 0 0.5rem;
}

.month-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #2a5d49;
  margin: 0;
}

.nav-button {
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.4);
  border-radius: 50%;
  width: 48px;
  height: 48px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #2a5d49;
  box-shadow: 0 4px 16px rgba(42, 93, 73, 0.1);
}

.nav-button:hover:not(:disabled) {
  background: rgba(42, 93, 73, 0.1);
  border-color: rgba(42, 93, 73, 0.3);
  transform: translateY(-2px) scale(1.05);
  box-shadow: 0 6px 20px rgba(42, 93, 73, 0.2);
}

.nav-button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.weekdays {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 1px;
  margin-bottom: 1rem;
}

.weekday {
  text-align: center;
  padding: 0.75rem 0.5rem;
  font-weight: 600;
  color: #6b7280;
  font-size: 0.875rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.calendar-grid {
  display: grid;
  grid-template-columns: repeat(7, 1fr);
  gap: 12px;
  padding: 1rem;
  background: transparent;
  border-radius: 16px;
}

.calendar-day {
  background: rgba(255, 255, 255, 0.4);
  backdrop-filter: blur(8px);
  min-height: 80px;
  border-radius: 20px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: flex-start;
  padding: 0.75rem 0.5rem;
  border: 1px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
}

.calendar-day:hover {
  background: rgba(255, 255, 255, 0.6);
  transform: translateY(-4px) scale(1.02);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
  border-color: rgba(42, 93, 73, 0.2);
}

.calendar-day.other-month {
  background: rgba(255, 255, 255, 0.2);
  color: rgba(156, 163, 175, 0.7);
  cursor: default;
  backdrop-filter: blur(4px);
}

.calendar-day.other-month:hover {
  transform: none;
  background: rgba(255, 255, 255, 0.2);
}

.calendar-day.today {
  background: rgba(34, 197, 94, 0.2);
  border: 2px solid rgba(34, 197, 94, 0.4);
  box-shadow: 0 0 20px rgba(34, 197, 94, 0.3);
}

.calendar-day.selected {
  background: rgba(59, 130, 246, 0.2);
  border: 2px solid rgba(59, 130, 246, 0.4);
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
}

.calendar-day.has-trackers {
  background: rgba(255, 255, 255, 0.5);
  border: 1px solid rgba(255, 255, 255, 0.4);
}

.day-number {
  font-weight: 600;
  font-size: 0.875rem;
  color: #374151;
  margin-bottom: 0.25rem;
}

.calendar-day.other-month .day-number {
  color: #9ca3af;
}

.calendar-day.today .day-number {
  color: #16a34a;
  font-weight: 700;
}

.calendar-day.selected .day-number {
  color: #2563eb;
  font-weight: 700;
}

.day-trackers {
  flex: 1;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
}

.tracker-gradient {
  width: 100%;
  height: 24px;
  border-radius: 50px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.tracker-count {
  font-size: 0.75rem;
  font-weight: 700;
  color: #2a5d49;
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
  border-radius: 50px;
  padding: 0.25rem 0.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.4);
  min-width: 20px;
  text-align: center;
}

.calendar-loading {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(10px);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-radius: 24px;
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top: 3px solid #2a5d49;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Responsive */
@media (max-width: 768px) {
  .emotion-calendar {
    padding: 0.75rem;
    margin: 0 -1rem;
    border-radius: 16px;
    background: rgba(255, 255, 255, 0.8);
  }
  
  .calendar-header {
    margin-bottom: 1rem;
    padding: 0 0.25rem;
  }
  
  .month-title {
    font-size: 1.25rem;
  }
  
  .nav-button {
    width: 40px;
    height: 40px;
  }
  
  .calendar-grid {
    gap: 8px;
    padding: 0.5rem;
  }
  
  .calendar-day {
    min-height: 60px;
    padding: 0.25rem;
    border-radius: 16px;
  }
  
  .day-number {
    font-size: 0.75rem;
  }
  
  .tracker-gradient {
    height: 16px;
  }
  
  .weekday {
    padding: 0.5rem 0.25rem;
    font-size: 0.75rem;
  }
}

@media (max-width: 480px) {
  .emotion-calendar {
    padding: 0.5rem;
    margin: 0 -0.75rem;
    border-radius: 12px;
  }
  
  .calendar-header {
    margin-bottom: 0.75rem;
    padding: 0;
  }
  
  .month-title {
    font-size: 1.125rem;
  }
  
  .nav-button {
    width: 36px;
    height: 36px;
  }
  
  .calendar-grid {
    gap: 6px;
    padding: 0.25rem;
  }
  
  .calendar-day {
    min-height: 50px;
    padding: 0.125rem;
    border-radius: 12px;
  }
  
  .day-number {
    font-size: 0.625rem;
    margin-bottom: 0.125rem;
  }
  
  .tracker-gradient {
    height: 12px;
  }
  
  .tracker-count {
    font-size: 0.5rem;
    padding: 0.0625rem 0.25rem;
    min-width: 16px;
  }
  
  .weekday {
    padding: 0.375rem 0.125rem;
    font-size: 0.625rem;
  }
  
  .day-trackers {
    gap: 0.25rem;
  }
}
</style>
