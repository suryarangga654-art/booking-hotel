<script setup>
import { useRouter } from 'vue-router'
import { store, logout } from '../store/store'
import { ref, computed } from 'vue'

const router = useRouter()
const showDropdown = ref(false)
const searchQuery = ref('')

// Contoh data notifikasi (nanti ambil dari store)
const unreadCount = computed(() => store.unreadNotifications || 0)

function doLogout() {
  logout()
  router.push('/')
}

function toggleDropdown() {
  showDropdown.value = !showDropdown.value
}

function searchHotel() {
  if (searchQuery.value.trim()) {
    router.push(`/rooms?search=${searchQuery.value}`)
  }
}

// Tutup dropdown saat klik di luar
function closeDropdown() {
  showDropdown.value = false
}
</script>

<template>
  <nav class="nav">

    <!-- LOGO -->
    <router-link to="/" class="brand">
      <div class="auth-logo">
        <span class="logo-mark"></span>
        <span class="brand-name">AUREA</span>
      </div>
    </router-link>

    <!-- SEARCH BAR -->
    <div class="search-container">
      <input 
        type="text" 
        placeholder="Cari hotel atau kamar..." 
        v-model="searchQuery"
        @keyup.enter="searchHotel"
        class="search-input"
      />
      <button @click="searchHotel" class="search-btn">🔍</button>
    </div>

    <!-- NAVIGATION LINKS -->
    <div class="links">

      <router-link to="/" class="nav-link">
        Beranda
      </router-link>

      <router-link to="/rooms" class="nav-link">
        Kamar
      </router-link>

      <template v-if="!store.user">

        <router-link
          to="/login"
          class="pill outline"
        >
          Masuk
        </router-link>

        <router-link
          to="/register"
          class="pill"
        >
          Daftar
        </router-link>

      </template>

      <template v-else>

        <!-- NOTIFICATIONS -->
        <router-link to="/notifications" class="notification-icon">
          <span class="icon">🔔</span>
          <span class="badge" v-if="unreadCount > 0">{{ unreadCount }}</span>
        </router-link>

        <!-- USER DROPDOWN -->
        <div class="user-menu" @click.stop>
          <button @click="toggleDropdown" class="user-btn">
            <img 
              :src="store.user.avatar || '/default-avatar.png'" 
              class="avatar" 
            />
            <span class="who">Hai, {{ store.user.name }}</span>
            <span class="arrow" :class="{ rotated: showDropdown }">▼</span>
          </button>
          
          <div v-if="showDropdown" class="dropdown" @click="closeDropdown">
            <router-link to="/profile" class="dropdown-item">
              <span>👤</span> Profil Saya
            </router-link>
            <router-link to="/my-bookings" class="dropdown-item">
              <span>📋</span> Pemesanan Saya
            </router-link>
            <router-link to="/ulasan/tulis" class="dropdown-item">
              <span>⭐</span> Tulis Ulasan
            </router-link>
            <hr />
            <button @click="doLogout" class="dropdown-item logout-btn">
              <span>🚪</span> Keluar
            </button>
          </div>
        </div>

      </template>

    </div>

  </nav>
</template>

<style scoped>

.nav {
  width: 100%;

  display: flex;
  align-items: center;
  justify-content: space-between;

  padding: 14px 6vw;

  background: #ffffff;

  border-bottom: 1px solid #e2e8f0;

  position: relative;
  z-index: 20;
  gap: 20px;
}

/* =========================
   BRAND / LOGO (Style Velora)
   ========================= */

.brand {
  display: flex;
  align-items: center;

  text-decoration: none;
}

.auth-logo {
  display: flex;
  align-items: center;
  gap: 10px;
}

.logo-mark {
  display: inline-block;
  width: 32px;
  height: 32px;
  background: #0f172a;
  border-radius: 8px;
  position: relative;
}

.logo-mark::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 16px;
  height: 16px;
  background: #ffffff;
  border-radius: 4px;
  opacity: 0.8;
}

.brand-name {
  font-size: 22px;
  font-weight: 700;
  color: #0f172a;
  letter-spacing: 1px;
}

.brand:hover .brand-name {
  color: #0284c7;
  transition: color 0.2s ease;
}

/* =========================
   SEARCH
   ========================= */

.search-container {
  flex: 1;
  max-width: 400px;
  display: flex;
  align-items: center;
  position: relative;
}

.search-input {
  width: 100%;
  padding: 8px 40px 8px 16px;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  font-size: 14px;
  outline: none;
  transition: border-color 0.2s;
  background: #f8fafc;
}

.search-input:focus {
  border-color: #0284c7;
  background: #ffffff;
}

.search-btn {
  position: absolute;
  right: 6px;
  background: none;
  border: none;
  font-size: 18px;
  cursor: pointer;
  padding: 4px 10px;
  color: #64748b;
  transition: color 0.2s;
}

.search-btn:hover {
  color: #0284c7;
}

/* =========================
   NAVIGATION LINKS
   ========================= */

.links {
  display: flex;
  align-items: center;
  gap: 24px;
  font-size: 15px;
}

.nav-link {
  color: #475569;
  text-decoration: none;
  transition: color 0.2s ease;
  font-weight: 500;
}

.nav-link:hover {
  color: #0284c7;
}

.nav-link.router-link-active {
  color: #0284c7;
  font-weight: 600;
}

/* =========================
   BUTTONS
   ========================= */

.pill {
  padding: 9px 18px !important;
  border-radius: 999px !important;
  background: #0f172a !important;
  color: #ffffff !important;
  font-size: 14px !important;
  font-weight: 600;
  text-decoration: none;
  transition: background 0.2s;
}

.pill:hover {
  background: #1e293b !important;
  color: #ffffff !important;
}

.pill.outline {
  background: #ffffff !important;
  color: #0f172a !important;
  border: 1px solid #0f172a !important;
}

.pill.outline:hover {
  background: #0f172a !important;
  color: #ffffff !important;
}

/* =========================
   NOTIFICATION
   ========================= */

.notification-icon {
  position: relative;
  text-decoration: none;
  font-size: 20px;
  padding: 4px;
  color: #475569;
  transition: color 0.2s;
}

.notification-icon:hover {
  color: #0284c7;
}

.badge {
  position: absolute;
  top: -6px;
  right: -6px;
  background: #ef4444;
  color: white;
  font-size: 10px;
  font-weight: 700;
  padding: 2px 6px;
  border-radius: 999px;
  min-width: 18px;
  text-align: center;
}

/* =========================
   USER DROPDOWN
   ========================= */

.user-menu {
  position: relative;
}

.user-btn {
  display: flex;
  align-items: center;
  gap: 10px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 4px 8px;
  border-radius: 999px;
  transition: background 0.2s;
}

.user-btn:hover {
  background: #f1f5f9;
}

.avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #e2e8f0;
}

.who {
  color: #0f172a;
  font-size: 14px;
  font-weight: 500;
}

.arrow {
  font-size: 12px;
  color: #94a3b8;
  transition: transform 0.2s;
}

.arrow.rotated {
  transform: rotate(180deg);
}

/* =========================
   DROPDOWN
   ========================= */

.dropdown {
  position: absolute;
  top: calc(100% + 8px);
  right: 0;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12);
  min-width: 220px;
  padding: 8px 0;
  z-index: 100;
  animation: slideDown 0.2s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-8px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 10px 20px;
  text-decoration: none;
  color: #334155;
  font-size: 14px;
  background: none;
  border: none;
  width: 100%;
  cursor: pointer;
  transition: background 0.2s;
  font-family: inherit;
}

.dropdown-item:hover {
  background: #f1f5f9;
}

.dropdown-item span {
  font-size: 16px;
}

.dropdown hr {
  border: none;
  border-top: 1px solid #e2e8f0;
  margin: 6px 0;
}

.logout-btn {
  color: #ef4444;
}

.logout-btn:hover {
  background: #fef2f2;
}

/* =========================
   MOBILE
   ========================= */

@media (max-width: 1024px) {
  .search-container {
    max-width: 250px;
  }
  
  .brand-name {
    display: none;
  }
}

@media (max-width: 700px) {
  .nav {
    padding: 12px 20px;
    flex-wrap: wrap;
    gap: 12px;
  }

  .logo-mark {
    width: 28px;
    height: 28px;
  }

  .logo-mark::after {
    width: 14px;
    height: 14px;
  }

  .search-container {
    order: 3;
    flex: 1 1 100%;
    max-width: 100%;
  }

  .links {
    gap: 12px;
    font-size: 13px;
  }

  .nav-link:not(.pill) {
    display: none;
  }

  .who {
    display: none;
  }

  .dropdown {
    min-width: 180px;
    right: -20px;
  }
}

</style>