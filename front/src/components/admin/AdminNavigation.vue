<template>
  <div class="admin-navigation">
    <button 
      v-for="section in sections" 
      :key="section.id"
      @click="$emit('section-change', section.id)"
      :class="['nav-btn', { 'active': activeSection === section.id }]"
    >
      <span class="nav-icon">{{ section.icon }}</span>
      {{ section.title }}
    </button>
  </div>
</template>

<script setup>
defineProps({
  sections: {
    type: Array,
    required: true
  },
  activeSection: {
    type: String,
    required: true
  }
})

defineEmits(['section-change'])
</script>

<style scoped>
.admin-navigation {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 2rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
  overflow-x: auto;
}

.nav-btn {
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

.nav-btn:hover {
  background: #f3f4f6;
  color: #374151;
  transform: translateY(-1px);
}

.nav-btn.active {
  background: #2a5d49;
  color: white;
  box-shadow: 0 4px 6px rgba(42, 93, 73, 0.2);
}

.nav-icon {
  font-size: 1.1rem;
}
</style>
