<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

const loading = ref(true)
const toast = ref('')
const pemesananList = ref([])

const filterStatus = ref('semua')
const searchQuery = ref('')

// ============================================
// FETCH DATA
// ============================================
async function fetchData() {
  loading.value = true
  try {
    const res = await api.get('/admin/pemesanan')
    pemesananList.value = res.data
  } catch (error) {
    console.error('Gagal memuat data pemesanan:', error)
    loadDummyData()
  } finally {
    loading.value = false
  }
}

function loadDummyData() {
  pemesananList.value = [
    {
      id: 1, kode_pemesanan: 'VLR-20260901-001', nama_tamu: 'Rizky Ananda', email: 'rizky@example.com',
      tipe_kamar: 'Suite Pesisir', nomor_kamar: '201',
      tanggal_check_in: '2026-09-05', tanggal_check_out: '2026-09-07',
      jumlah_total: 2500000, status_pemesanan: 'dikonfirmasi', status_pembayaran: 'lunas',
      layanan: ['Sarapan Pagi', 'Antar Jemput Bandara'],
    },
    {
      id: 2, kode_pemesanan: 'VLR-20260901-002', nama_tamu: 'Siti Nurhaliza', email: 'siti@example.com',
      tipe_kamar: 'Kamar Rimba', nomor_kamar: '101',
      tanggal_check_in: '2026-09-06', tanggal_check_out: '2026-09-08',
      jumlah_total: 1300000, status_pemesanan: 'menunggu', status_pembayaran: 'belum_dibayar',
      layanan: [],
    },
    {
      id: 3, kode_pemesanan: 'VLR-20260901-003', nama_tamu: 'Bagas Wibowo', email: 'bagas@example.com',
      tipe_kamar: 'Villa Batu', nomor_kamar: '401',
      tanggal_check_in: '2026-09-04', tanggal_check_out: '2026-09-10',
      jumlah_total: 12600000, status_pemesanan: 'check_in', status_pembayaran: 'lunas',
      layanan: ['Spa Pijat Tradisional'],
    },
    {
      id: 4, kode_pemesanan: 'VLR-20260902-001', nama_tamu: 'Amanda Putri', email: 'amanda@example.com',
      tipe_kamar: 'Kamar Sawah', nomor_kamar: '301',
      tanggal_check_in: '2026-09-09', tanggal_check_out: '2026-09-11',
      jumlah_total: 960000, status_pemesanan: 'dikonfirmasi', status_pembayaran: 'dibayar_sebagian',
      layanan: ['Sarapan Pagi'],
    },
    {
      id: 5, kode_pemesanan: 'VLR-20260902-002', nama_tamu: 'Fajar Nugroho', email: 'fajar@example.com',
      tipe_kamar: 'Suite Pesisir', nomor_kamar: '202',
      tanggal_check_in: '2026-08-30', tanggal_check_out: '2026-09-02',
      jumlah_total: 3750000, status_pemesanan: 'check_out', status_pembayaran: 'lunas',
      layanan: [],
    },
    {
      id: 6, kode_pemesanan: 'VLR-20260903-001', nama_tamu: 'Dewi Lestari', email: 'dewi@example.com',
      tipe_kamar: 'Kamar Rimba', nomor_kamar: '102',
      tanggal_check_in: '2026-09-12', tanggal_check_out: '2026-09-13',
      jumlah_total: 650000, status_pemesanan: 'dibatalkan', status_pembayaran: 'dikembalikan',
      layanan: [],
    },
  ]
}

onMounted(fetchData)

// ============================================
// HELPER
// ============================================
function formatRupiah(n) {
  return 'Rp' + Number(n).toLocaleString('id-ID')
}

function formatTanggal(dateStr) {
  return new Date(dateStr).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function showToast(msg) {
  toast.value = msg
  setTimeout(() => (toast.value = ''), 2800)
}

const statusPemesananOptions = ['menunggu', 'dikonfirmasi', 'check_in', 'check_out', 'dibatalkan']
const statusPembayaranOptions = ['belum_dibayar', 'dibayar_sebagian', 'lunas', 'dikembalikan']

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

// ============================================
// FILTER & SEARCH
// ============================================
const filteredList = computed(() => {
  let list = pemesananList.value

  if (filterStatus.value !== 'semua') {
    list = list.filter((p) => p.status_pemesanan === filterStatus.value)
  }

  if (searchQuery.value.trim()) {
    const q = searchQuery.value.toLowerCase()
    list = list.filter(
      (p) =>
        p.kode_pemesanan.toLowerCase().includes(q) ||
        p.nama_tamu.toLowerCase().includes(q)
    )
  }

  return list
})

// ============================================
// UPDATE STATUS
// ============================================
async function updateStatusPemesanan(p, status) {
  const prev = p.status_pemesanan
  p.status_pemesanan = status
  try {
    await api.put(`/admin/pemesanan/${p.id}`, { status_pemesanan: status })
    showToast(`Status pemesanan ${p.kode_pemesanan} diperbarui.`)
  } catch (error) {
    // biarkan perubahan lokal tetap berlaku sebagai fallback
    showToast(`Status pemesanan ${p.kode_pemesanan} diperbarui (lokal).`)
  }
}

async function updateStatusPembayaran(p, status) {
  p.status_pembayaran = status
  try {
    await api.put(`/admin/pemesanan/${p.id}`, { status_pembayaran: status })
    showToast(`Status pembayaran ${p.kode_pemesanan} diperbarui.`)
  } catch (error) {
    showToast(`Status pembayaran ${p.kode_pemesanan} diperbarui (lokal).`)
  }
}

// ============================================
// MODAL DETAIL
// ============================================
const showDetailModal = ref(false)
const activeDetail = ref(null)

function openDetail(p) {
  activeDetail.value = p
  showDetailModal.value = true
}

const jumlahMalam = computed(() => {
  if (!activeDetail.value) return 0
  const inDate = new Date(activeDetail.value.tanggal_check_in)
  const outDate = new Date(activeDetail.value.tanggal_check_out)
  const diff = (outDate - inDate) / (1000 * 60 * 60 * 24)
  return diff > 0 ? diff : 1
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
          :class="{ active: item.key === 'pemesanan' }"
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
          <h1>Kelola Pemesanan</h1>
          <p>Pantau dan kelola semua pemesanan tamu Velora Resort</p>
        </div>
      </header>

      <div v-if="loading" class="loading-state">
        Memuat data pemesanan...
      </div>

      <template v-else>

        <section class="panel">
          <div class="panel-head">
            <h2>Daftar Pemesanan</h2>
            <div class="panel-actions">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari kode / nama tamu..."
                class="search-input"
              />
              <select v-model="filterStatus" class="filter-select">
                <option value="semua">Semua Status</option>
                <option v-for="s in statusPemesananOptions" :key="s" :value="s">
                  {{ statusPemesananLabel[s] }}
                </option>
              </select>
            </div>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>Kode</th>
                  <th>Tamu</th>
                  <th>Kamar</th>
                  <th>Check-in</th>
                  <th>Check-out</th>
                  <th>Total</th>
                  <th>Status Pemesanan</th>
                  <th>Pembayaran</th>
                  <th>Detail</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="p in filteredList" :key="p.id">
                  <td class="code">{{ p.kode_pemesanan }}</td>
                  <td>{{ p.nama_tamu }}</td>
                  <td>{{ p.tipe_kamar }} · {{ p.nomor_kamar }}</td>
                  <td>{{ formatTanggal(p.tanggal_check_in) }}</td>
                  <td>{{ formatTanggal(p.tanggal_check_out) }}</td>
                  <td>{{ formatRupiah(p.jumlah_total) }}</td>
                  <td>
                    <select
                      class="status-select"
                      :class="'status-' + p.status_pemesanan"
                      v-model="p.status_pemesanan"
                      @change="updateStatusPemesanan(p, p.status_pemesanan)"
                    >
                      <option v-for="s in statusPemesananOptions" :key="s" :value="s">
                        {{ statusPemesananLabel[s] }}
                      </option>
                    </select>
                  </td>
                  <td>
                    <select
                      class="status-select"
                      :class="'pay-' + p.status_pembayaran"
                      v-model="p.status_pembayaran"
                      @change="updateStatusPembayaran(p, p.status_pembayaran)"
                    >
                      <option v-for="s in statusPembayaranOptions" :key="s" :value="s">
                        {{ statusPembayaranLabel[s] }}
                      </option>
                    </select>
                  </td>
                  <td>
                    <button class="btn-link" @click="openDetail(p)">Lihat</button>
                  </td>
                </tr>
                <tr v-if="filteredList.length === 0">
                  <td colspan="9" class="empty-row">Tidak ada pemesanan yang cocok.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

      </template>

    </main>

    <!-- MODAL DETAIL PEMESANAN -->
    <div v-if="showDetailModal && activeDetail" class="modal-backdrop" @click.self="showDetailModal = false">
      <div class="modal detail-modal">
        <div class="detail-head">
          <h3>{{ activeDetail.kode_pemesanan }}</h3>
          <span class="badge" :class="'status-' + activeDetail.status_pemesanan">
            {{ statusPemesananLabel[activeDetail.status_pemesanan] }}
          </span>
        </div>

        <div class="detail-section">
          <span class="detail-label">Tamu</span>
          <p class="detail-value">{{ activeDetail.nama_tamu }}</p>
          <p class="detail-sub">{{ activeDetail.email }}</p>
        </div>

        <div class="detail-grid">
          <div>
            <span class="detail-label">Kamar</span>
            <p class="detail-value">{{ activeDetail.tipe_kamar }} · No. {{ activeDetail.nomor_kamar }}</p>
          </div>
          <div>
            <span class="detail-label">Durasi</span>
            <p class="detail-value">{{ jumlahMalam }} malam</p>
          </div>
          <div>
            <span class="detail-label">Check-in</span>
            <p class="detail-value">{{ formatTanggal(activeDetail.tanggal_check_in) }}</p>
          </div>
          <div>
            <span class="detail-label">Check-out</span>
            <p class="detail-value">{{ formatTanggal(activeDetail.tanggal_check_out) }}</p>
          </div>
        </div>

        <div class="detail-section" v-if="activeDetail.layanan && activeDetail.layanan.length">
          <span class="detail-label">Layanan Tambahan</span>
          <ul class="layanan-list">
            <li v-for="(l, idx) in activeDetail.layanan" :key="idx">{{ l }}</li>
          </ul>
        </div>

        <div class="detail-total">
          <span>Total Pembayaran</span>
          <strong>{{ formatRupiah(activeDetail.jumlah_total) }}</strong>
        </div>

        <div class="detail-section">
          <span class="detail-label">Status Pembayaran</span>
          <span class="badge" :class="'pay-' + activeDetail.status_pembayaran">
            {{ statusPembayaranLabel[activeDetail.status_pembayaran] }}
          </span>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel" @click="showDetailModal = false">Tutup</button>
        </div>
      </div>
    </div>

    <!-- TOAST -->
    <div v-if="toast" class="toast">{{ toast }}</div>

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
  text-decoration: none;

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
  margin-bottom: 24px;
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

.loading-state {
  padding: 60px 0;
  text-align: center;
  color: #64748b;
  font-size: 14px;
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
  flex-wrap: wrap;
  gap: 12px;

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

.panel-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 13px;
  color: #334155;
  outline: none;
  min-width: 200px;
}

.search-input:focus {
  border-color: #0284c7;
}

.filter-select {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 13px;
  color: #334155;
  background: #ffffff;
  outline: none;
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

.empty-row {
  text-align: center;
  color: #94a3b8;
  padding: 30px 0;
}

.btn-link {
  background: none;
  border: none;
  color: #0284c7;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}

.btn-link:hover {
  text-decoration: underline;
}

/* STATUS SELECT & BADGE */
.status-select {
  padding: 5px 10px;
  border-radius: 999px;
  border: none;
  font-size: 11.5px;
  font-weight: 700;
  cursor: pointer;
  outline: none;
}

.badge {
  display: inline-block;
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
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
   MODAL
   ========================================= */
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  backdrop-filter: blur(4px);
  padding: 20px;
}

.modal {
  background: #ffffff;
  border-radius: 12px;
  padding: 28px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
  max-height: 90vh;
  overflow-y: auto;
}

.detail-modal {
  max-width: 460px;
}

.detail-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}

.detail-head h3 {
  margin: 0;
  font-family: 'Courier New', monospace;
  color: #0284c7;
  font-size: 16px;
}

.detail-section {
  margin-bottom: 18px;
}

.detail-label {
  display: block;
  font-size: 11.5px;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  color: #94a3b8;
  margin-bottom: 4px;
}

.detail-value {
  margin: 0;
  color: #0f172a;
  font-size: 14.5px;
  font-weight: 600;
}

.detail-sub {
  margin: 2px 0 0;
  color: #64748b;
  font-size: 13px;
}

.detail-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
  margin-bottom: 18px;
  padding: 14px;
  background: #f8fafc;
  border-radius: 8px;
}

.layanan-list {
  margin: 0;
  padding-left: 18px;
  color: #334155;
  font-size: 13.5px;
  line-height: 1.7;
}

.detail-total {
  display: flex;
  justify-content: space-between;
  align-items: center;

  padding: 14px 16px;
  margin-bottom: 18px;

  background: #0f172a;
  border-radius: 8px;

  color: #f8fafc;
  font-size: 14px;
}

.detail-total strong {
  font-size: 17px;
  font-family: Georgia, serif;
}

.modal h3 {
  margin: 0 0 20px;
  color: #0f172a;
  font-family: Georgia, serif;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 8px;
}

.btn-cancel {
  padding: 9px 16px;
  background: #e2e8f0;
  color: #334155;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
}

.btn-cancel:hover {
  background: #cbd5e1;
}

/* =========================================
   TOAST
   ========================================= */
.toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: #10b981;
  color: #ffffff;
  padding: 14px 24px;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  z-index: 200;
  font-weight: 500;
  font-size: 14px;
}

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

  .detail-grid {
    grid-template-columns: 1fr;
  }
}
</style>