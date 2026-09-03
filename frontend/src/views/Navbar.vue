<script setup>
import { useRouter } from 'vue-router'
import { store, logout } from '../store/store'

const router = useRouter()

function doLogout() {
  logout()
  router.push('/')
}
</script>

<template>
  <nav class="nav">
    <router-link to="/" class="brand">
      <span class="mark"></span>VELORA
    </router-link>

    <div class="links">
      <router-link to="/">Beranda</router-link>

      <template v-if="!store.user">
        <router-link to="/login" class="pill outline">Masuk</router-link>
        <router-link to="/register" class="pill">Daftar</router-link>
      </template>

      <template v-else>
        <span class="who">Hai, {{ store.user.name }}</span>
        <button @click="doLogout">Keluar</button>
      </template>
    </div>
  </nav>
</template>

<style scoped>
.who {
  font-size: 14px;
  color: var(--rattan);
}
</style>