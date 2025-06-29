<template>
  <div class="empty-container">
    <div class="empty-content">
      <component :is="icon" :size="iconSize" />
      <h3>{{ title }}</h3>
      <p>{{ message }}</p>
      <slot name="action"></slot>
    </div>
  </div>
</template>

<script setup>
import { FileText } from 'lucide-vue-next'

defineProps({
  icon: {
    type: [Object, String],
    default: () => FileText
  },
  iconSize: {
    type: Number,
    default: 64
  },
  title: {
    type: String,
    required: true
  },
  message: {
    type: String,
    required: true
  }
})
</script>

<style scoped>
.empty-container {
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 4rem 2rem;
}

.empty-content {
  text-align: center;
  color: #6b7280;
  max-width: 500px;
}

.empty-content :deep(svg) {
  color: #d1d5db;
  margin-bottom: 1.5rem;
}

.empty-content h3 {
  margin: 0 0 1rem 0;
  font-size: 1.25rem;
  font-weight: 600;
  color: #374151;
}

.empty-content p {
  margin: 0;
  line-height: 1.6;
}
</style>
