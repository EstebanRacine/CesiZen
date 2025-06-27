<script setup>
import { RouterLink, RouterView } from 'vue-router'
import SideBar from './components/SideBar.vue'
</script>

<template>
  <SideBar />

  <main class="main-content">
      <RouterView />
  </main>
</template>

<style scoped>
.main-content {
  flex: 1;
  overflow-y: auto;
  max-height: 100dvh;
}
</style>
