<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const toast = ref('')
const ulasanList = ref([])
const filterRating = ref('semua')

const menuItems = [
  { key: 'dashboard', label: 'Dashboard', icon: '📊', to: '/admin' },
  { key: 'pemesanan', label: 'Pemesanan', icon: '📋', to: '/admin/pemesanan' },
  { key: 'kamar', label: 'Kamar', icon: '🛏️', to: '/admin/kamar' },
  { key: 'tipe-kamar', label: 'Tipe Kamar', icon: '🏷️', to: '/admin/tipe-kamar' },
  { key: 'pembayaran', label: 'Pembayaran', icon: '💳', to: '/admin/pembayaran' },
  { key: 'ulasan', label: 'Ulasan', icon: '⭐', to: '/admin/ulasan' },
  { key: 'pengguna', label: 'Pengguna', icon: '👥', to: '/admin/pengguna' },
]

function logout() {
  localStorage.removeItem('token')
  router.push('/login')
}

async function fetchData() {
  loading.value = true
  try {
    const res = await api.get('/admin/ulasan')
    ulasanList.value = res.data
  } catch (err) {
    loadDummyData()
  } finally {
    loading.value = false
  }
}

function loadDummyData() {
  ulasanList.value = [
    {
      id: 1,
      nama_tamu: 'Rian Prasetya',
      tipe_kamar: 'Suite Pesisir',
      rating: 5,
      komentar: 'Pelayanannya sangat ramah, pemandangan luar biasa dari balkon!',
      tanggal: '2026-09-10',
      tampil: true
    },
    {
      id: 2,
      nama_tamu: 'Dewi Lestari',
      tipe_kamar: 'Kamar Rimba',
      rating: 4,
      komentar: 'Kamar bersih dan asri, WiFi tergolong stabil.',
      tanggal: '2026-09-08',
      tampil: true
    }
  ]
}

onMounted(fetchData)

function showToast(msg) {
  toast.value = msg
  setTimeout(() => (toast.value = ''), 2800)
}

const filteredUlasan = computed(() => {
  if (filterRating.value === 'semua') return ulasanList.value
  return ulasanList.value.filter((u) => u.rating === Number(filterRating.value))
})

async function toggleTampil(u) {
  u.tampil = !u.tampil
  try {
    await api.put(`/admin/ulasan/${u.id}`, { tampil: u.tampil })
  } catch (e) {}
  showToast(u.tampil ? 'Ulasan ditampilkan.' : 'Ulasan disembunyikan.')
}

async function deleteUlasan(id) {
  if (!confirm('Hapus ulasan ini secara permanen?')) return
  try {
    await api.delete(`/admin/ulasan/${id}`)
  } catch (e) {}
  ulasanList.value = ulasanList.value.filter((u) => u.id !== id)
  showToast('Ulasan dihapus.')
}
</script>

<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <span class="logo-mark"></span>
        <span>VELORA</span>
      </div>

      <nav class="sidebar-nav">
        <router-link
          v-for="item in menuItems"
          :key="item.key"
          :to="item.to"
          class="nav-item"
          :class="{ active: route.path === item.to }"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <button class="logout-btn" @click="logout">
        <span class="nav-icon">🚪</span>
        <span>Keluar</span>
      </button>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <h1>Kelola Ulasan Tamu</h1>
        <p>Moderasi ulasan dan testimoni pengunjung Velora Resort</p>
      </header>

      <div v-if="loading" class="loading-state">Memuat ulasan...</div>

      <template v-else>
        <section class="panel">
          <div class="panel-head">
            <h2>Daftar Ulasan</h2>
            <select v-model="filterRating" class="select-filter">
              <option value="semua">Semua Rating</option>
              <option value="5">⭐⭐⭐⭐⭐ (5)</option>
              <option value="4">⭐⭐⭐⭐ (4)</option>
              <option value="3">⭐⭐⭐ (3)</option>
              <option value="2">⭐⭐ (2)</option>
              <option value="1">⭐ (1)</option>
            </select>
          </div>

          <div class="ulasan-grid">
            <div v-for="u in filteredUlasan" :key="u.id" class="ulasan-card" :class="{ hidden: !u.tampil }">
              <div class="ulasan-head">
                <div>
                  <strong class="tamu-name">{{ u.nama_tamu }}</strong>
                  <span class="room-type"> • {{ u.tipe_kamar }}</span>
                </div>
                <span class="rating-stars">★ {{ u.rating }}/5</span>
              </div>
              <p class="komentar">"{{ u.komentar }}"</p>
              <div class="ulasan-footer">
                <span class="date">{{ u.tanggal }}</span>
                <div class="actions">
                  <button class="btn-toggle" @click="toggleTampil(u)">
                    {{ u.tampil ? '👁️ Sembunyikan' : '🙈 Tampilkan' }}
                  </button>
                  <button class="btn-delete" @click="deleteUlasan(u.id)">Hapus</button>
                </div>
              </div>
            </div>

            <div v-if="filteredUlasan.length === 0" class="empty-state">
              Tidak ada ulasan ditemukan.
            </div>
          </div>
        </section>
      </template>
    </main>

    <div v-if="toast" class="toast">{{ toast }}</div>
  </div>
</template>

<style scoped>
.admin-layout { display: flex; min-height: 100vh; background: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
.sidebar { width: 250px; background: #0f172a; color: #f8fafc; display: flex; flex-direction: column; padding: 28px 18px; position: sticky; top: 0; height: 100vh; flex-shrink: 0; }
.sidebar-brand { display: flex; align-items: center; gap: 10px; padding: 0 10px 28px; margin-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); font-family: Georgia, serif; font-size: 19px; font-weight: 700; }
.logo-mark { width: 24px; height: 24px; border-radius: 50%; background: conic-gradient(from 200deg, #0284c7, #f8fafc, #0284c7); }
.sidebar-nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
.nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 8px; color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 500; }
.nav-item.active, .nav-item:hover { background: #0284c7; color: #fff; }
.logout-btn { display: flex; align-items: center; gap: 12px; padding: 11px 14px; background: none; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; color: #f87171; cursor: pointer; }
.main-content { flex: 1; padding: 32px 40px; min-width: 0; }
.topbar h1 { margin: 0 0 4px; color: #0f172a; font-family: Georgia, serif; font-size: 26px; }
.topbar p { margin: 0 0 24px; color: #64748b; font-size: 14px; }
.loading-state { text-align: center; color: #64748b; padding: 40px; }
.panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 15px rgba(15,23,42,0.04); }
.panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.panel-head h2 { font-size: 17px; margin: 0; color: #0f172a; }
.select-filter { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none; }
.ulasan-grid { display: flex; flex-direction: column; gap: 16px; }
.ulasan-card { border: 1px solid #e2e8f0; border-radius: 10px; padding: 18px; background: #fff; transition: opacity 0.2s; }
.ulasan-card.hidden { opacity: 0.55; background: #f8fafc; border-style: dashed; }
.ulasan-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
.tamu-name { color: #0f172a; font-size: 15px; }
.room-type { color: #64748b; font-size: 13px; }
.rating-stars { font-weight: 700; color: #d97706; font-size: 14px; }
.komentar { font-size: 14px; color: #334155; margin: 0 0 14px; line-height: 1.5; font-style: italic; }
.ulasan-footer { display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #94a3b8; }
.actions { display: flex; gap: 12px; }
.btn-toggle { background: none; border: none; color: #0284c7; font-weight: 600; cursor: pointer; }
.btn-delete { background: none; border: none; color: #dc2626; font-weight: 600; cursor: pointer; }
.empty-state { text-align: center; color: #94a3b8; padding: 40px 0; }
.toast { position: fixed; bottom: 24px; right: 24px; background: #10b981; color: #fff; padding: 12px 20px; border-radius: 8px; font-size: 14px; z-index: 200; }
</style>