<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

const activeMenu = ref('dashboard')
const loading = ref(true)

// ============================================
// DATA STATE (sesuai skema database)
// ============================================
const stats = ref({
  totalPemesananHariIni: 0,
  pendapatanBulanIni: 0,
  kamarTersedia: 0,
  totalKamar: 0,
  tamuAktif: 0,
})

const pemesananList = ref([])
const kamarList = ref([])
const tipeKamarList = ref([])

// ============================================
// FETCH DATA DARI API
// ============================================
async function fetchDashboardData() {
  loading.value = true
  try {
    const [statsRes, pemesananRes, kamarRes, tipeKamarRes] = await Promise.all([
      api.get('/admin/stats'),
      api.get('/admin/pemesanan?limit=8'),
      api.get('/admin/kamar'),
      api.get('/admin/tipe-kamar'),
    ])

    stats.value = statsRes.data
    pemesananList.value = pemesananRes.data
    kamarList.value = kamarRes.data
    tipeKamarList.value = tipeKamarRes.data
  } catch (error) {
    console.error('Gagal memuat data dashboard:', error)
    // Fallback data contoh, hapus ini setelah API tersambung
    loadDummyData()
  } finally {
    loading.value = false
  }
}

function loadDummyData() {
  stats.value = {
    totalPemesananHariIni: 12,
    pendapatanBulanIni: 84500000,
    kamarTersedia: 18,
    totalKamar: 32,
    tamuAktif: 24,
  }

  pemesananList.value = [
    { id: 1, kode_pemesanan: 'VLR-20260901-001', nama_tamu: 'Rizky Ananda', tipe_kamar: 'Suite Pesisir', tanggal_check_in: '2026-09-05', tanggal_check_out: '2026-09-07', jumlah_total: 2500000, status_pemesanan: 'dikonfirmasi', status_pembayaran: 'lunas' },
    { id: 2, kode_pemesanan: 'VLR-20260901-002', nama_tamu: 'Siti Nurhaliza', tipe_kamar: 'Kamar Rimba', tanggal_check_in: '2026-09-06', tanggal_check_out: '2026-09-08', jumlah_total: 1300000, status_pemesanan: 'menunggu', status_pembayaran: 'belum_dibayar' },
    { id: 3, kode_pemesanan: 'VLR-20260901-003', nama_tamu: 'Bagas Wibowo', tipe_kamar: 'Villa Batu', tanggal_check_in: '2026-09-04', tanggal_check_out: '2026-09-10', jumlah_total: 12600000, status_pemesanan: 'check_in', status_pembayaran: 'lunas' },
    { id: 4, kode_pemesanan: 'VLR-20260902-001', nama_tamu: 'Amanda Putri', tipe_kamar: 'Kamar Sawah', tanggal_check_in: '2026-09-09', tanggal_check_out: '2026-09-11', jumlah_total: 960000, status_pemesanan: 'dikonfirmasi', status_pembayaran: 'dibayar_sebagian' },
    { id: 5, kode_pemesanan: 'VLR-20260902-002', nama_tamu: 'Fajar Nugroho', tipe_kamar: 'Suite Pesisir', tanggal_check_in: '2026-08-30', tanggal_check_out: '2026-09-02', jumlah_total: 3750000, status_pemesanan: 'check_out', status_pembayaran: 'lunas' },
    { id: 6, kode_pemesanan: 'VLR-20260903-001', nama_tamu: 'Dewi Lestari', tipe_kamar: 'Kamar Rimba', tanggal_check_in: '2026-09-12', tanggal_check_out: '2026-09-13', jumlah_total: 650000, status_pemesanan: 'dibatalkan', status_pembayaran: 'dikembalikan' },
  ]

  tipeKamarList.value = [
    { id: 1, nama: 'Kamar Rimba', harga_dasar: 650000, kapasitas: 2 },
    { id: 2, nama: 'Suite Pesisir', harga_dasar: 1250000, kapasitas: 3 },
    { id: 3, nama: 'Kamar Sawah', harga_dasar: 480000, kapasitas: 2 },
    { id: 4, nama: 'Villa Batu', harga_dasar: 2100000, kapasitas: 4 },
  ]

  kamarList.value = [
    { id: 1, nomor_kamar: '101', lantai: 1, tipe_kamar_id: 1, status: 'tersedia' },
    { id: 2, nomor_kamar: '102', lantai: 1, tipe_kamar_id: 1, status: 'terisi' },
    { id: 3, nomor_kamar: '201', lantai: 2, tipe_kamar_id: 2, status: 'tersedia' },
    { id: 4, nomor_kamar: '202', lantai: 2, tipe_kamar_id: 2, status: 'dibersihkan' },
    { id: 5, nomor_kamar: '301', lantai: 3, tipe_kamar_id: 3, status: 'terisi' },
    { id: 6, nomor_kamar: '302', lantai: 3, tipe_kamar_id: 3, status: 'perbaikan' },
    { id: 7, nomor_kamar: '401', lantai: 4, tipe_kamar_id: 4, status: 'tersedia' },
    { id: 8, nomor_kamar: '402', lantai: 4, tipe_kamar_id: 4, status: 'tersedia' },
  ]
}

onMounted(() => {
  fetchDashboardData()
})

// ============================================
// HELPER
// ============================================
function formatRupiah(n) {
  return 'Rp' + n.toLocaleString('id-ID')
}

function formatTanggal(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

const statusPemesananLabel = {
  menunggu: 'Menunggu',
  dikonfirmasi: 'Dikonfirmasi',
  check_in: 'Check-in',
  check_out: 'Check-out',
  dibatalkan: 'Dibatalkan',
}

const statusPembayaranLabel = {
  belum_dibayar: 'Belum Dibayar',
  dibayar_sebagian: 'Dibayar Sebagian',
  lunas: 'Lunas',
  dikembalikan: 'Dikembalikan',
}

const kamarStatusLabel = {
  tersedia: 'Tersedia',
  terisi: 'Terisi',
  perbaikan: 'Perbaikan',
  dibersihkan: 'Dibersihkan',
}

function tipeKamarNama(id) {
  const t = tipeKamarList.value.find((t) => t.id === id)
  return t ? t.nama : '-'
}

const okupansiPercent = computed(() => {
  if (!stats.value.totalKamar) return 0
  const terisi = stats.value.totalKamar - stats.value.kamarTersedia
  return Math.round((terisi / stats.value.totalKamar) * 100)
})

function logout() {
  localStorage.removeItem('token')
  router.push('/login')
}

const menuItems = [
  { key: 'dashboard', label: 'Dashboard', icon: '📊', to: '/admin' },
  { key: 'pemesanan', label: 'Pemesanan', icon: '📋', to: '/admin/pemesanan' },
  { key: 'kamar', label: 'Kamar', icon: '🛏️', to: '/admin/kamar' },
  { key: 'tipe-kamar', label: 'Tipe Kamar', icon: '🏷️', to: '/admin/tipe-kamar' },
  { key: 'pembayaran', label: 'Pembayaran', icon: '💳', to: '/admin/pembayaran' },
  { key: 'ulasan', label: 'Ulasan', icon: '⭐', to: '/admin/ulasan' },
  { key: 'pengguna', label: 'Pengguna', icon: '👥', to: '/admin/pengguna' },
]
</script>

<template>
  <div class="admin-layout">

    <!-- SIDEBAR -->
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
          :class="{ active: item.key === 'dashboard' }"
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

    <!-- MAIN CONTENT -->
    <main class="main-content">

      <header class="topbar">
        <div>
          <h1>Dashboard Admin</h1>
          <p>Ringkasan operasional Velora Resort hari ini</p>
        </div>
        <div class="admin-profile">
          <span class="avatar-mark"></span>
          <span>Admin</span>
        </div>
      </header>

      <div v-if="loading" class="loading-state">
        Memuat data dashboard...
      </div>

      <template v-else>

        <!-- STAT CARDS -->
        <section class="stats-grid">
          <div class="stat-card">
            <span class="stat-label">Pemesanan Hari Ini</span>
            <span class="stat-value">{{ stats.totalPemesananHariIni }}</span>
          </div>

          <div class="stat-card accent">
            <span class="stat-label">Pendapatan Bulan Ini</span>
            <span class="stat-value">{{ formatRupiah(stats.pendapatanBulanIni) }}</span>
          </div>

          <div class="stat-card">
            <span class="stat-label">Kamar Tersedia</span>
            <span class="stat-value">{{ stats.kamarTersedia }} / {{ stats.totalKamar }}</span>
          </div>

          <div class="stat-card">
            <span class="stat-label">Okupansi</span>
            <span class="stat-value">{{ okupansiPercent }}%</span>
          </div>
        </section>

        <!-- PEMESANAN TERBARU -->
        <section class="panel">
          <div class="panel-head">
            <h2>Pemesanan Terbaru</h2>
            <span>{{ pemesananList.length }} pemesanan</span>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Tamu</th>
                  <th>Tipe Kamar</th>
                  <th>Check-in</th>
                  <th>Check-out</th>
                  <th>Total</th>
                  <th>Status Pemesanan</th>
                  <th>Pembayaran</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in pemesananList" :key="p.id">
                  <td class="code">{{ p.kode_pemesanan }}</td>
                  <td>{{ p.nama_tamu }}</td>
                  <td>{{ p.tipe_kamar }}</td>
                  <td>{{ formatTanggal(p.tanggal_check_in) }}</td>
                  <td>{{ formatTanggal(p.tanggal_check_out) }}</td>
                  <td>{{ formatRupiah(p.jumlah_total) }}</td>
                  <td>
                    <span class="badge" :class="'status-' + p.status_pemesanan">
                      {{ statusPemesananLabel[p.status_pemesanan] }}
                    </span>
                  </td>
                  <td>
                    <span class="badge" :class="'pay-' + p.status_pembayaran">
                      {{ statusPembayaranLabel[p.status_pembayaran] }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- STATUS KAMAR -->
        <section class="panel">
          <div class="panel-head">
            <h2>Status Kamar</h2>
            <span>{{ kamarList.length }} kamar</span>
          </div>

          <div class="room-grid">
            <div
              v-for="k in kamarList"
              :key="k.id"
              class="room-tile"
              :class="'room-' + k.status"
            >
              <span class="room-number">{{ k.nomor_kamar }}</span>
              <span class="room-type">{{ tipeKamarNama(k.tipe_kamar_id) }}</span>
              <span class="room-status">{{ kamarStatusLabel[k.status] }}</span>
            </div>
          </div>
        </section>

      </template>

    </main>
  </div>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* =========================================
   SIDEBAR
   ========================================= */
.sidebar {
  width: 250px;
  flex-shrink: 0;

  background: #0f172a;
  color: #f8fafc;

  display: flex;
  flex-direction: column;

  padding: 28px 18px;
  position: sticky;
  top: 0;
  height: 100vh;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;

  padding: 0 10px 28px;
  margin-bottom: 20px;

  border-bottom: 1px solid rgba(255, 255, 255, 0.1);

  font-family: Georgia, serif;
  font-size: 19px;
  font-weight: 700;
  letter-spacing: 1px;
}

.logo-mark {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: conic-gradient(from 200deg, #0284c7, #f8fafc, #0284c7);
  flex-shrink: 0;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;

  padding: 11px 14px;

  background: none;
  border: none;
  border-radius: 8px;

  color: #cbd5e1;

  font-family: inherit;
  font-size: 14px;
  font-weight: 500;
  text-align: left;

  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff;
}

.nav-item.active {
  background: #0284c7;
  color: #ffffff;
}

.nav-icon {
  font-size: 16px;
  width: 18px;
  text-align: center;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 12px;

  padding: 11px 14px;
  margin-top: 12px;

  background: none;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;

  color: #f87171;

  font-family: inherit;
  font-size: 14px;
  font-weight: 600;
  text-align: left;

  cursor: pointer;
  transition: background 0.2s ease;
}

.logout-btn:hover {
  background: rgba(248, 113, 113, 0.1);
}

/* =========================================
   MAIN CONTENT
   ========================================= */
.main-content {
  flex: 1;
  padding: 32px 40px;
  min-width: 0;
}

.topbar {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  margin-bottom: 28px;
}

.topbar h1 {
  margin: 0 0 4px;
  color: #0f172a;
  font-family: Georgia, serif;
  font-size: 26px;
  font-weight: 600;
}

.topbar p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.admin-profile {
  display: flex;
  align-items: center;
  gap: 10px;

  padding: 8px 14px;

  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 999px;

  color: #0f172a;
  font-size: 14px;
  font-weight: 600;
}

.avatar-mark {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  background: conic-gradient(from 200deg, #0284c7, #0f172a, #0284c7);
}

.loading-state {
  padding: 60px 0;
  text-align: center;
  color: #64748b;
  font-size: 14px;
}

/* =========================================
   STAT CARDS
   ========================================= */
.stats-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 18px;
  margin-bottom: 28px;
}

.stat-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;

  padding: 20px 22px;

  display: flex;
  flex-direction: column;
  gap: 8px;

  box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
}

.stat-card.accent {
  background: #0f172a;
  border-color: #0f172a;
}

.stat-card.accent .stat-label {
  color: #94a3b8;
}

.stat-card.accent .stat-value {
  color: #ffffff;
}

.stat-label {
  font-size: 13px;
  color: #64748b;
  font-weight: 600;
}

.stat-value {
  font-size: 24px;
  font-weight: 700;
  color: #0f172a;
  font-family: Georgia, serif;
}

/* =========================================
   PANEL / TABLE
   ========================================= */
.panel {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;

  padding: 24px;
  margin-bottom: 24px;

  box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;

  margin-bottom: 18px;
  padding-bottom: 14px;

  border-bottom: 2px solid #f1f5f9;
}

.panel-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 17px;
  font-weight: 700;
}

.panel-head span {
  color: #64748b;
  font-size: 13px;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13.5px;
}

thead th {
  text-align: left;
  padding: 10px 12px;
  color: #64748b;
  font-weight: 600;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

tbody td {
  padding: 12px;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
  white-space: nowrap;
}

tbody tr:last-child td {
  border-bottom: none;
}

tbody tr:hover {
  background: #f8fafc;
}

.code {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #0284c7;
}

/* BADGES */
.badge {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  font-size: 11.5px;
  font-weight: 700;
  white-space: nowrap;
}

.status-menunggu { background: #fef3c7; color: #92400e; }
.status-dikonfirmasi { background: #dbeafe; color: #1e40af; }
.status-check_in { background: #dcfce7; color: #166534; }
.status-check_out { background: #e0e7ff; color: #3730a3; }
.status-dibatalkan { background: #fee2e2; color: #991b1b; }

.pay-belum_dibayar { background: #fee2e2; color: #991b1b; }
.pay-dibayar_sebagian { background: #fef3c7; color: #92400e; }
.pay-lunas { background: #dcfce7; color: #166534; }
.pay-dikembalikan { background: #f1f5f9; color: #475569; }

/* =========================================
   ROOM GRID
   ========================================= */
.room-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
  gap: 12px;
}

.room-tile {
  border-radius: 10px;
  padding: 14px;

  display: flex;
  flex-direction: column;
  gap: 4px;

  border: 1px solid #e2e8f0;
  background: #f8fafc;
}

.room-number {
  font-size: 18px;
  font-weight: 700;
  color: #0f172a;
  font-family: Georgia, serif;
}

.room-type {
  font-size: 12px;
  color: #64748b;
}

.room-status {
  margin-top: 6px;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.room-tersedia { border-color: #86efac; background: #f0fdf4; }
.room-tersedia .room-status { color: #166534; }

.room-terisi { border-color: #93c5fd; background: #eff6ff; }
.room-terisi .room-status { color: #1e40af; }

.room-perbaikan { border-color: #fca5a5; background: #fef2f2; }
.room-perbaikan .room-status { color: #991b1b; }

.room-dibersihkan { border-color: #fde68a; background: #fffbeb; }
.room-dibersihkan .room-status { color: #92400e; }

/* =========================================
   RESPONSIVE
   ========================================= */
@media (max-width: 900px) {
  .admin-layout {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    flex-direction: row;
    align-items: center;
    padding: 14px 18px;
    overflow-x: auto;
  }

  .sidebar-brand {
    padding: 0 16px 0 0;
    margin-bottom: 0;
    border-bottom: none;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
  }

  .sidebar-nav {
    flex-direction: row;
    flex: none;
    margin-left: 14px;
  }

  .nav-item span:last-child {
    display: none;
  }

  .logout-btn {
    margin-top: 0;
    margin-left: 10px;
  }

  .logout-btn span:last-child {
    display: none;
  }

  .main-content {
    padding: 20px;
  }

  .topbar {
    flex-direction: column;
    gap: 12px;
  }
}
</style>