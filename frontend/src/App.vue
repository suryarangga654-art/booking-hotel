<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import Navbar from './views/Navbar.vue'

const route = useRoute()
const showNavbar = computed(() => !route.meta.hideNavbar)
</script>

<template>
  <div class="app-shell">
    <Navbar v-if="showNavbar" />

    <main class="page-shell">
      <router-view />
    </main>
  </div>
</template>

<style scoped>
.app-shell {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
  background: #f8fafc;
}

.page-shell {
  flex: 1;
  width: 100%;
}
</style>