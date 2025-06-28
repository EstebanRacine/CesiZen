<script setup>
import { RouterLink, RouterView } from 'vue-router'
import SideBar from './components/SideBar.vue'
import AuthModal from './components/modal/AuthModal.vue'
import authService from '@/services/singleton/authService.js'
import { onMounted } from 'vue'

onMounted(() => {
    authService.initializeAuth()
})
</script>

<template>
  <SideBar />

  <main class="main-content">
      <RouterView />
  </main>

  <AuthModal />
</template>

<style scoped>
.main-content {
  flex: 1;
  overflow-y: auto;
  max-height: 100dvh;
  margin-left: 25px;
  margin-right: 25px;
}
</style>
 