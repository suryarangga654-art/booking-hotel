<script setup>
import Navbar from './views/Navbar.vue'
</script>

<template>
  <div class="app-shell">
    <Navbar />

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