<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const toast = ref('')
const loadError = ref('')
const pemesananList = ref([])

const filterStatus = ref('semua')
const searchQuery = ref('')

// ============================================
// BASE URL untuk file di storage Laravel (sama seperti AdminPembayaran.vue)
// ============================================
const API_ORIGIN = (api.defaults.baseURL || '').replace(/\/api\/?$/, '')

function buildBuktiUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//i.test(path)) return path
  return `${API_ORIGIN}/storage/${path}`
}

// Label status Pembayaran SESUAI ENUM ASLI tabel `pembayaran`
// (menunggu | berhasil | gagal | kedaluwarsa) — lihat PembayaranController
const pembayaranStatusLabel = {
  menunggu: 'Menunggu Verifikasi',
  berhasil: 'Lunas',
  gagal: 'Ditolak',
  kedaluwarsa: 'Kedaluwarsa',
}

// ============================================
// FETCH DATA
// ============================================
function normalizePemesananList(payload) {
  const list = Array.isArray(payload) ? payload : Array.isArray(payload?.data) ? payload.data : []

  return list.map((p) => {
    // Relasi asli dari backend: with(['user', 'pembayaran', 'detail_pemesanan.kamar'])
    const detailPertama = p.detail_pemesanan?.[0] || p.detail_pemesanan || {}
    const kamarData = detailPertama.kamar || {}
    const tipeKamarData = kamarData.tipe_kamar || {}
    const pembayaran = p.pembayaran || null

    const namaTipeKamar =
      tipeKamarData.nama_tipe_kamar ||
      tipeKamarData.nama ||
      kamarData.nama_tipe_kamar ||
      'Kamar Standar'

    const nomorKamar = kamarData.nomor_kamar || detailPertama.nomor_kamar || '-'

    return {
      id: p.id,
      kode_pemesanan: p.kode_pemesanan ?? p.kode_booking ?? 'N/A',
      nama_tamu: p.nama_tamu ?? p.user?.name ?? 'Tamu',
      email: p.user?.email ?? '-',
      tipe_kamar: namaTipeKamar,
      nomor_kamar: nomorKamar,
      tanggal_check_in: p.tanggal_check_in ?? detailPertama.tanggal_check_in ?? '',
      tanggal_check_out: p.tanggal_check_out ?? detailPertama.tanggal_check_out ?? '',
      jumlah_total: Number(p.jumlah_total ?? 0),
      status_pemesanan: p.status_pemesanan ?? 'menunggu',
      // Data pembayaran ASLI dari tabel `pembayaran` yang tersambung ke pemesanan ini
      pembayaran: pembayaran
        ? {
            id: pembayaran.id,
            status: pembayaran.status ?? 'menunggu', // menunggu|berhasil|gagal|kedaluwarsa
            metode: pembayaran.metode_pembayaran ?? '-',
            jumlah_bayar: Number(pembayaran.jumlah_bayar ?? 0),
            bukti_url: buildBuktiUrl(pembayaran.bukti_transfer),
            waktu_bayar: pembayaran.waktu_bayar ?? pembayaran.created_at ?? p.created_at ?? null,
          }
        : null,
      layanan: Array.isArray(p.layanan_tambahan)
        ? p.layanan_tambahan.map((item) => item.nama)
        : Array.isArray(p.layanan) ? p.layanan : (p.layanan ? [p.layanan] : []),
    }
  })
}

async function fetchData() {
  loading.value = true
  loadError.value = ''
  try {
    const res = await api.get('/admin/pemesanan')
    pemesananList.value = normalizePemesananList(res.data)
  } catch (error) {
    console.error('Gagal memuat data pemesanan:', error)
    loadError.value = error.response?.data?.message || 'Data pemesanan gagal dimuat dari server.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

// ============================================
// HELPER
// ============================================
function formatRupiah(n) {
  return 'Rp' + Number(n || 0).toLocaleString('id-ID')
}

function formatTanggal(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return String(dateStr)
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  })
}

function formatWaktu(dateStr) {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return String(dateStr)
  return d.toLocaleString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

function showToast(msg) {
  toast.value = msg
  setTimeout(() => (toast.value = ''), 2800)
}

const statusPemesananOptions = ['menunggu', 'dikonfirmasi', 'check_in', 'check_out', 'dibatalkan']

const statusPemesananLabel = {
  menunggu: 'Menunggu',
  dikonfirmasi: 'Dikonfirmasi',
  check_in: 'Check-in',
  check_out: 'Check-out',
  dibatalkan: 'Dibatalkan',
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

function exportCsv() {
  const columns = [
    ['Kode', 'kode_pemesanan'],
    ['Nama Tamu', 'nama_tamu'],
    ['Email', 'email'],
    ['Tipe Kamar', 'tipe_kamar'],
    ['Nomor Kamar', 'nomor_kamar'],
    ['Check-in', 'tanggal_check_in'],
    ['Check-out', 'tanggal_check_out'],
    ['Total', 'jumlah_total'],
    ['Status Pemesanan', 'status_pemesanan'],
    ['Status Pembayaran', (item) => item.pembayaran?.status || 'belum ada'],
  ]
  const escapeCsv = (value) => `"${String(value ?? '').replaceAll('"', '""')}"`
  const rows = [
    columns.map(([label]) => escapeCsv(label)).join(','),
    ...filteredList.value.map((item) => columns
      .map(([, key]) => escapeCsv(typeof key === 'function' ? key(item) : item[key]))
      .join(',')),
  ]
  const blob = new Blob(['\uFEFF' + rows.join('\r\n')], { type: 'text/csv;charset=utf-8' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.href = url
  link.download = 'laporan-pemesanan.csv'
  link.click()
  URL.revokeObjectURL(url)
}

// ============================================
// UPDATE STATUS PEMESANAN (enum: menunggu|dikonfirmasi|check_in|check_out|dibatalkan)
// ============================================
async function updateStatusPemesanan(p, status) {
  const previous = p.status_pemesanan
  p.status_pemesanan = status
  try {
    await api.patch(`/admin/pemesanan/${p.id}/status`, { status_pemesanan: status })
    showToast(`Status pemesanan ${p.kode_pemesanan} diperbarui.`)
  } catch (error) {
    console.error('Gagal update status pemesanan:', error.response?.data || error)
    p.status_pemesanan = previous // rollback tampilan kalau backend nolak
    const msg = error.response?.data?.message || 'Gagal memperbarui status pemesanan.'
    showToast(msg)
  }
}

// ============================================
// KONFIRMASI PEMBAYARAN — tersambung ke tabel `pembayaran` yang sebenarnya
// Memakai endpoint yang sama dengan halaman Pembayaran:
// POST /admin/pembayaran/{pembayaran.id}/konfirmasi  body: { status: 'diterima' | 'ditolak' }
// ⚠️ Sesuaikan path ini kalau nama route di routes/api.php kamu berbeda.
// ============================================
async function confirmPembayaran(action) {
  if (!activeDetail.value?.pembayaran?.id) {
    showToast('Data pembayaran untuk pemesanan ini tidak ditemukan.')
    return
  }

  saving.value = true
  const pembayaranId = activeDetail.value.pembayaran.id

  try {
    await api.post(`/admin/pembayaran/${pembayaranId}/konfirmasi`, { status: action })

    const backendStatus = action === 'diterima' ? 'berhasil' : 'gagal'

    // Update tampilan lokal: baik di modal aktif maupun baris tabel
    activeDetail.value.pembayaran.status = backendStatus
    if (action === 'diterima') {
      activeDetail.value.status_pemesanan = 'dikonfirmasi'
    } else {
      activeDetail.value.status_pemesanan = 'dibatalkan'
    }

    const idx = pemesananList.value.findIndex((p) => p.id === activeDetail.value.id)
    if (idx !== -1) {
      pemesananList.value[idx].pembayaran.status = backendStatus
      pemesananList.value[idx].status_pemesanan = activeDetail.value.status_pemesanan
    }

    showToast(action === 'diterima' ? 'Pembayaran diverifikasi & pemesanan dikonfirmasi.' : 'Pembayaran ditolak.')
  } catch (error) {
    console.error('Gagal konfirmasi pembayaran:', error.response?.data || error)
    const msg =
      error.response?.data?.message ||
      (error.response?.data?.errors
        ? Object.values(error.response.data.errors).flat().join(', ')
        : 'Gagal memperbarui status pembayaran.')
    showToast(msg)
  } finally {
    saving.value = false
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
  localStorage.removeItem('velora_token')
  localStorage.removeItem('velora_user')
  router.push('/login')
}

const menuItems = [
  { key: 'dashboard', label: 'Dashboard', icon: '📊', to: '/admin' },
  { key: 'pemesanan', label: 'Pemesanan', icon: '📋', to: '/admin/pemesanan' },
  { key: 'kamar', label: 'Kamar', icon: '🛏️', to: '/admin/kamar' },
  { key: 'tipe-kamar', label: 'Tipe Kamar', icon: '🏷️', to: '/admin/tipe-kamar' },
  { key: 'promo', label: 'Promo', icon: '🏷️', to: '/admin/promo' },
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
      <div v-else-if="loadError" class="loading-state error-state">
        {{ loadError }}
        <button class="btn-primary" @click="fetchData">Coba Lagi</button>
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
              <button class="btn-primary" type="button" @click="exportCsv">Ekspor CSV</button>
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
                      :value="p.status_pemesanan"
                      @change="updateStatusPemesanan(p, $event.target.value)"
                    >
                      <option v-for="s in statusPemesananOptions" :key="s" :value="s">
                        {{ statusPemesananLabel[s] }}
                      </option>
                    </select>
                  </td>
                  <td>
                    <!-- Status pembayaran ASLI dari tabel `pembayaran`, read-only di sini.
                         Verifikasi/tolak dilakukan lewat modal detail. -->
                    <span v-if="p.pembayaran" class="badge" :class="'pay-' + p.pembayaran.status">
                      {{ pembayaranStatusLabel[p.pembayaran.status] || p.pembayaran.status }}
                    </span>
                    <span v-else class="badge pay-none">Belum ada data</span>
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

    <!-- MODAL DETAIL PEMESANAN + PEMBAYARAN -->
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
          <span>Total Pemesanan</span>
          <strong>{{ formatRupiah(activeDetail.jumlah_total) }}</strong>
        </div>

        <!-- ============ BLOK PEMBAYARAN (tersambung ke tabel `pembayaran`) ============ -->
        <div class="payment-block" v-if="activeDetail.pembayaran">
          <div class="detail-head" style="margin-bottom: 12px;">
            <span class="detail-label" style="margin: 0;">Pembayaran</span>
            <span class="badge" :class="'pay-' + activeDetail.pembayaran.status">
              {{ pembayaranStatusLabel[activeDetail.pembayaran.status] || activeDetail.pembayaran.status }}
            </span>
          </div>

          <div class="detail-grid">
            <div>
              <span class="detail-label">Metode</span>
              <p class="detail-value">{{ activeDetail.pembayaran.metode }}</p>
            </div>
            <div>
              <span class="detail-label">Jumlah Dibayar</span>
              <p class="detail-value">{{ formatRupiah(activeDetail.pembayaran.jumlah_bayar) }}</p>
            </div>
            <div style="grid-column: 1 / -1;">
              <span class="detail-label">Waktu</span>
              <p class="detail-value">{{ formatWaktu(activeDetail.pembayaran.waktu_bayar) }}</p>
            </div>
          </div>

          <div class="bukti-container">
            <template v-if="activeDetail.pembayaran.bukti_url">
              <img :src="activeDetail.pembayaran.bukti_url" alt="Bukti Transfer" class="bukti-img" />
            </template>
            <template v-else>
              <p class="no-bukti-text">Tidak ada foto bukti transfer yang dilampirkan.</p>
            </template>
          </div>

          <div class="modal-actions" v-if="activeDetail.pembayaran.status === 'menunggu'">
            <button class="btn-danger" :disabled="saving" @click="confirmPembayaran('ditolak')">Tolak</button>
            <button class="btn-success" :disabled="saving" @click="confirmPembayaran('diterima')">Verifikasi & Terima</button>
          </div>
        </div>
        <div class="payment-block" v-else>
          <p class="no-bukti-text">Belum ada data pembayaran untuk pemesanan ini.</p>
        </div>
        <!-- ============ END BLOK PEMBAYARAN ============ -->

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

/* Badge pembayaran mengikuti ENUM asli tabel `pembayaran` */
.pay-menunggu { background: #fef3c7; color: #92400e; }
.pay-berhasil { background: #dcfce7; color: #166534; }
.pay-gagal { background: #fee2e2; color: #991b1b; }
.pay-kedaluwarsa { background: #e2e8f0; color: #475569; }
.pay-none { background: #f1f5f9; color: #94a3b8; }

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

.btn-danger { background: #dc2626; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-success { background: #059669; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-danger:disabled, .btn-success:disabled { opacity: 0.6; cursor: not-allowed; }

/* =========================================
   BLOK PEMBAYARAN DI DALAM MODAL
   ========================================= */
.payment-block {
  margin: 20px 0;
  padding: 16px;
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 10px;
}

.bukti-container {
  text-align: center;
  background: #ffffff;
  border: 1px dashed #cbd5e1;
  border-radius: 8px;
  padding: 12px;
  margin-top: 6px;
  min-height: 100px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.bukti-img {
  max-height: 220px;
  max-width: 100%;
  border-radius: 6px;
  object-fit: contain;
}

.no-bukti-text {
  font-size: 13px;
  color: #94a3b8;
  margin: 0;
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

@media (max-width: 560px) {
  .main-content { padding: 14px; }
  .panel { padding: 14px; }
  .panel-actions { width: 100%; flex-direction: column; align-items: stretch; }
  .panel-actions > * { width: 100%; }
  .detail-modal { padding: 18px; }
}
.error-state { display: grid; justify-items: center; gap: 12px; color: #b91c1c; }
</style>