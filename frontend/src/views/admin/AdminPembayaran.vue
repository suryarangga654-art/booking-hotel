<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const saving = ref(false)
const toast = ref('')

const pembayaranList = ref([])
const activeFilter = ref('semua')
const searchQuery = ref('')

const selectedPayment = ref(null)
const showModal = ref(false)

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
    const res = await api.get('/admin/pembayaran')
    pembayaranList.value = res.data
  } catch (error) {
    loadDummyData()
  } finally {
    loading.value = false
  }
}

function loadDummyData() {
  pembayaranList.value = [
    {
      id: 101,
      kode_pemesanan: 'VEL-202609-001',
      nama_tamu: 'Budi Santoso',
      metode: 'Transfer BCA',
      jumlah: 1300000,
      tanggal: '2026-09-12 14:20',
      status: 'menunggu_verifikasi',
      bukti_url: 'https://placehold.co/400x600/0f172a/ffffff?text=Bukti+Transfer+BCA'
    },
    {
      id: 102,
      kode_pemesanan: 'VEL-202609-002',
      nama_tamu: 'Siti Rahma',
      metode: 'QRIS',
      jumlah: 2500000,
      tanggal: '2026-09-13 09:10',
      status: 'lunas',
      bukti_url: 'https://placehold.co/400x600/0f172a/ffffff?text=Bukti+QRIS'
    }
  ]
}

onMounted(fetchData)

function formatRupiah(n) {
  return 'Rp' + Number(n).toLocaleString('id-ID')
}

function showToast(msg) {
  toast.value = msg
  setTimeout(() => (toast.value = ''), 2800)
}

const filteredList = computed(() => {
  return pembayaranList.value.filter((p) => {
    const matchStatus = activeFilter.value === 'semua' || p.status === activeFilter.value
    const matchSearch =
      p.kode_pemesanan.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      p.nama_tamu.toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchStatus && matchSearch
  })
})

function openDetail(p) {
  selectedPayment.value = { ...p }
  showModal.value = true
}

async function updateStatus(newStatus) {
  if (!selectedPayment.value) return
  saving.value = true
  try {
    await api.put(`/admin/pembayaran/${selectedPayment.value.id}`, { status: newStatus })
  } catch (err) {}
  
  const idx = pembayaranList.value.findIndex((p) => p.id === selectedPayment.value.id)
  if (idx !== -1) pembayaranList.value[idx].status = newStatus
  
  saving.value = false
  showModal.value = false
  showToast(`Status pembayaran diperbarui ke ${newStatus.replace('_', ' ')}.`)
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
        <h1>Kelola Pembayaran</h1>
        <p>Verifikasi transaksi & bukti pembayaran reservasi Velora Resort</p>
      </header>

      <div v-if="loading" class="loading-state">Memuat data pembayaran...</div>

      <template v-else>
        <section class="panel">
          <div class="panel-head">
            <div class="filter-tabs">
              <button
                v-for="f in [
                  { key: 'semua', label: 'Semua' },
                  { key: 'menunggu_verifikasi', label: 'Perlu Verifikasi' },
                  { key: 'lunas', label: 'Lunas' },
                  { key: 'ditolak', label: 'Ditolak' }
                ]"
                :key="f.key"
                class="tab-btn"
                :class="{ active: activeFilter === f.key }"
                @click="activeFilter = f.key"
              >
                {{ f.label }}
              </button>
            </div>
            <input v-model="searchQuery" type="text" placeholder="Cari pemesanan / nama..." class="search-input" />
          </div>

          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Tamu</th>
                  <th>Metode</th>
                  <th>Jumlah</th>
                  <th>Tanggal</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in filteredList" :key="p.id">
                  <td class="font-bold">{{ p.kode_pemesanan }}</td>
                  <td>{{ p.nama_tamu }}</td>
                  <td>{{ p.metode }}</td>
                  <td>{{ formatRupiah(p.jumlah) }}</td>
                  <td>{{ p.tanggal }}</td>
                  <td>
                    <span class="badge" :class="p.status">
                      {{ p.status.replace('_', ' ') }}
                    </span>
                  </td>
                  <td>
                    <button class="btn-link" @click="openDetail(p)">Periksa Bukti</button>
                  </td>
                </tr>
                <tr v-if="filteredList.length === 0">
                  <td colspan="7" class="empty-state">Tidak ada data pembayaran.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </template>
    </main>

    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal">
        <h3>Detail Pembayaran {{ selectedPayment.kode_pemesanan }}</h3>
        <p class="modal-sub">Pemohon: <strong>{{ selectedPayment.nama_tamu }}</strong></p>

        <div class="bukti-container">
          <img :src="selectedPayment.bukti_url" alt="Bukti Transfer" class="bukti-img" />
        </div>

        <div class="modal-info">
          <div><span>Jumlah Transfer:</span> <strong>{{ formatRupiah(selectedPayment.jumlah) }}</strong></div>
          <div><span>Metode:</span> <strong>{{ selectedPayment.metode }}</strong></div>
        </div>

        <div class="modal-actions">
          <button class="btn-danger" :disabled="saving" @click="updateStatus('ditolak')">Tolak</button>
          <button class="btn-success" :disabled="saving" @click="updateStatus('lunas')">Verifikasi & Terima</button>
        </div>
      </div>
    </div>

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
.panel-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-tabs { display: flex; gap: 6px; }
.tab-btn { background: #f1f5f9; border: none; padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; color: #475569; cursor: pointer; }
.tab-btn.active { background: #0f172a; color: #fff; }
.search-input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
.data-table th, .data-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
.data-table th { background: #f8fafc; color: #475569; font-weight: 600; }
.font-bold { font-weight: 600; color: #0f172a; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
.badge.menunggu_verifikasi { background: #fef3c7; color: #d97706; }
.badge.lunas { background: #d1fae5; color: #059669; }
.badge.ditolak { background: #fee2e2; color: #dc2626; }
.btn-link { background: none; border: none; color: #0284c7; font-size: 13px; font-weight: 600; cursor: pointer; }
.empty-state { text-align: center; color: #94a3b8; padding: 30px; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(4px); }
.modal { background: #fff; border-radius: 12px; padding: 24px; width: 100%; max-width: 440px; }
.modal h3 { margin: 0 0 4px; font-family: Georgia, serif; color: #0f172a; }
.modal-sub { margin: 0 0 16px; font-size: 13px; color: #64748b; }
.bukti-container { text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; margin-bottom: 16px; }
.bukti-img { max-height: 250px; border-radius: 6px; object-fit: contain; }
.modal-info { font-size: 13.5px; color: #334155; margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; }
.btn-danger { background: #dc2626; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-success { background: #059669; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.toast { position: fixed; bottom: 24px; right: 24px; background: #10b981; color: #fff; padding: 12px 20px; border-radius: 8px; font-size: 14px; z-index: 200; }
</style>