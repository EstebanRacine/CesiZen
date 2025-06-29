<template>
  <header class="page-header">
    <div class="header-content">
      <h1 class="heading-page">{{ title }}</h1>
      <p v-if="subtitle" class="subtitle">{{ subtitle }}</p>
      <slot name="actions" />
    </div>
  </header>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: null
  }
})
</script>

<style scoped>
.page-header {
  text-align: center;
  margin-bottom: var(--spacing-5xl);
}

.header-content {
  max-width: 800px;
  margin: 0 auto;
}

.heading-page{
    font-size: 2.5rem;
    font-weight: 700;
    color: #2a5d49;
    margin: 0 0 1rem 0;
    background: linear-gradient(135deg, #2a5d49 0%, #89c997 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.subtitle {
  font-size: var(--font-size-lg);
  color: var(--text-secondary);
  line-height: var(--line-height-relaxed);
  margin: var(--spacing-lg) 0 0 0;
}

@media (max-width: 768px) {
  .page-header {
    margin-bottom: var(--spacing-3xl);
    padding: 0 var(--spacing-lg);
  }
  
  .heading-page {
    font-size: var(--font-size-3xl);
  }
  
  .subtitle {
    font-size: var(--font-size-base);
  }
}
</style>
