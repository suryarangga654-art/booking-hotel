<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const saving = ref(false)
const toast = ref('')
const loadError = ref('')

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
  { key: 'promo', label: 'Promo', icon: '🏷️', to: '/admin/promo' },
  { key: 'pembayaran', label: 'Pembayaran', icon: '💳', to: '/admin/pembayaran' },
  { key: 'ulasan', label: 'Ulasan', icon: '⭐', to: '/admin/ulasan' },
  { key: 'pengguna', label: 'Pengguna', icon: '👥', to: '/admin/pengguna' },
]

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('velora_token')
  localStorage.removeItem('velora_user')
  router.push('/login')
}

// ============================================
// BASE URL untuk file di storage Laravel
// (menghapus akhiran /api dari baseURL axios, lalu tambah /storage/)
// Sesuaikan jika struktur baseURL api.js kamu berbeda.
// ============================================
const API_ORIGIN = (api.defaults.baseURL || '').replace(/\/api\/?$/, '')

function buildBuktiUrl(path) {
  if (!path) return ''
  if (/^https?:\/\//i.test(path)) return path // sudah full URL / dummy placeholder
  return `${API_ORIGIN}/storage/${path}`
}

// ============================================
// Label & mapping status SESUAI ENUM BACKEND
// (kolom `status` di tabel pembayaran hanya menerima:
//  menunggu | berhasil | gagal | kedaluwarsa)
// ============================================
const statusLabel = {
  menunggu: 'Menunggu Verifikasi',
  berhasil: 'Lunas',
  gagal: 'Ditolak',
  kedaluwarsa: 'Kedaluwarsa',
}

// Helper untuk Normalisasi Data dari API Laravel
function normalizePembayaranList(payload) {
  const raw = payload?.data ?? payload
  const list = Array.isArray(raw) ? raw : []

  return list.map((item) => {
    const pemesanan = item.pemesanan || {}

    return {
      id: item.id,
      pemesanan_id: item.pemesanan_id,
      kode_pemesanan: pemesanan.kode_pemesanan ?? 'N/A',
      nama_tamu: pemesanan.nama_tamu ?? pemesanan.user?.name ?? 'Tamu',
      metode: item.metode_pembayaran ?? '-',
      nomor_transaksi: item.nomor_transaksi ?? '-',
      // kolom asli di DB adalah jumlah_bayar, fallback ke total pemesanan kalau kosong
      jumlah: Number(item.jumlah_bayar ?? pemesanan.jumlah_total ?? 0),
      // fallback berlapis: waktu_bayar (pembayaran) -> created_at (pembayaran)
      // -> created_at (pemesanan terkait) -> tanggal_check_in (pemesanan)
      tanggal:
        item.waktu_bayar ??
        item.created_at ??
        pemesanan.created_at ??
        pemesanan.tanggal_check_in ??
        null,
      // status mentah dari backend: menunggu | berhasil | gagal | kedaluwarsa
      status: item.status ?? 'menunggu',
      bukti_url: buildBuktiUrl(item.bukti_transfer),
    }
  })
}

async function fetchData() {
  loading.value = true
  loadError.value = ''
  try {
    const res = await api.get('/admin/pembayaran')
    pembayaranList.value = normalizePembayaranList(res.data)
  } catch (error) {
    console.error('Gagal memuat pembayaran:', error)
    loadError.value = error.response?.data?.message || 'Data pembayaran gagal dimuat dari server.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

function formatRupiah(n) {
  return 'Rp' + Number(n || 0).toLocaleString('id-ID')
}

function formatTanggal(value) {
  if (!value) return '-'
  const d = new Date(value)
  if (isNaN(d.getTime())) return String(value) // biarin apa adanya kalau formatnya aneh
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

const filteredList = computed(() => {
  return pembayaranList.value.filter((p) => {
    const matchStatus = activeFilter.value === 'semua' || p.status === activeFilter.value
    const matchSearch =
      String(p.kode_pemesanan).toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      String(p.nama_tamu).toLowerCase().includes(searchQuery.value.toLowerCase())
    return matchStatus && matchSearch
  })
})

function openDetail(p) {
  selectedPayment.value = { ...p }
  showModal.value = true
}

// ============================================
// KONFIRMASI PEMBAYARAN
// Memakai endpoint khusus PembayaranController@konfirmasi
// POST /admin/pembayaran/{id}/konfirmasi  body: { status: 'diterima' | 'ditolak' }
// ⚠️ Cek routes/api.php kamu — sesuaikan path ini kalau nama route-nya beda.
// ============================================
async function updateStatus(action) {
  // action harus 'diterima' atau 'ditolak' (sesuai validasi controller)
  if (!selectedPayment.value) return
  saving.value = true

  try {
    await api.post(`/admin/pembayaran/${selectedPayment.value.id}/konfirmasi`, {
      status: action,
    })

    const backendStatus = action === 'diterima' ? 'berhasil' : 'gagal'
    const idx = pembayaranList.value.findIndex((p) => p.id === selectedPayment.value.id)
    if (idx !== -1) {
      pembayaranList.value[idx].status = backendStatus
    }

    showToast(
      action === 'diterima'
        ? 'Pembayaran diverifikasi & pemesanan dikonfirmasi.'
        : 'Pembayaran ditolak.'
    )
    showModal.value = false
  } catch (error) {
    console.error('Gagal memperbarui status pembayaran:', error.response?.data || error)
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
      <div v-else-if="loadError" class="loading-state error-state">
        {{ loadError }} <button class="btn-primary" @click="fetchData">Coba Lagi</button>
      </div>

      <template v-else>
        <section class="panel">
          <div class="panel-head">
            <div class="filter-tabs">
              <button
                v-for="f in [
                  { key: 'semua', label: 'Semua' },
                  { key: 'menunggu', label: 'Perlu Verifikasi' },
                  { key: 'berhasil', label: 'Lunas' },
                  { key: 'gagal', label: 'Ditolak' },
                  { key: 'kedaluwarsa', label: 'Kedaluwarsa' }
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
                  <td>{{ formatTanggal(p.tanggal) }}</td>
                  <td>
                    <span class="badge" :class="p.status">
                      {{ statusLabel[p.status] || p.status }}
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

    <!-- MODAL DETAIL & BUKTI TRANSFER -->
    <div v-if="showModal && selectedPayment" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal">
        <h3>Detail Pembayaran {{ selectedPayment.kode_pemesanan }}</h3>
        <p class="modal-sub">Pemohon: <strong>{{ selectedPayment.nama_tamu }}</strong></p>

        <div class="bukti-container">
          <template v-if="selectedPayment.bukti_url">
            <img :src="selectedPayment.bukti_url" alt="Bukti Transfer" class="bukti-img" />
          </template>
          <template v-else>
            <p class="no-bukti-text">Tidak ada foto bukti transfer yang dilampirkan.</p>
          </template>
        </div>

        <div class="modal-info">
          <div><span>Jumlah Transfer:</span> <strong>{{ formatRupiah(selectedPayment.jumlah) }}</strong></div>
          <div><span>Metode:</span> <strong>{{ selectedPayment.metode }}</strong></div>
          <div v-if="selectedPayment.nomor_transaksi && selectedPayment.nomor_transaksi !== '-'">
            <span>No. Transaksi:</span> <strong>{{ selectedPayment.nomor_transaksi }}</strong>
          </div>
        </div>

        <div class="modal-actions">
          <button class="btn-danger" :disabled="saving" @click="updateStatus('ditolak')">Tolak</button>
          <button class="btn-success" :disabled="saving" @click="updateStatus('diterima')">Verifikasi & Terima</button>
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
.error-state { display: grid; justify-items: center; gap: 12px; color: #b91c1c; }
.panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 15px rgba(15,23,42,0.04); }
.panel-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; flex-wrap: wrap; }
.filter-tabs { display: flex; gap: 6px; flex-wrap: wrap; }
.tab-btn { background: #f1f5f9; border: none; padding: 8px 14px; border-radius: 6px; font-size: 13px; font-weight: 600; color: #475569; cursor: pointer; }
.tab-btn.active { background: #0f172a; color: #fff; }
.search-input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
.data-table th, .data-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
.data-table th { background: #f8fafc; color: #475569; font-weight: 600; }
.font-bold { font-weight: 600; color: #0f172a; }
.badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
.badge.menunggu { background: #fef3c7; color: #d97706; }
.badge.berhasil { background: #d1fae5; color: #059669; }
.badge.gagal { background: #fee2e2; color: #dc2626; }
.badge.kedaluwarsa { background: #e2e8f0; color: #475569; }
.btn-link { background: none; border: none; color: #0284c7; font-size: 13px; font-weight: 600; cursor: pointer; }
.empty-state { text-align: center; color: #94a3b8; padding: 30px; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(4px); }
.modal { background: #fff; border-radius: 12px; padding: 24px; width: 100%; max-width: 440px; }
.modal h3 { margin: 0 0 4px; font-family: Georgia, serif; color: #0f172a; }
.modal-sub { margin: 0 0 16px; font-size: 13px; color: #64748b; }
.bukti-container { text-align: center; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; margin-bottom: 16px; min-height: 120px; display: flex; align-items: center; justify-content: center; }
.bukti-img { max-height: 250px; max-width: 100%; border-radius: 6px; object-fit: contain; }
.no-bukti-text { font-size: 13px; color: #94a3b8; margin: 0; }
.modal-info { font-size: 13.5px; color: #334155; margin-bottom: 20px; display: flex; flex-direction: column; gap: 6px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; }
.btn-danger { background: #dc2626; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.btn-danger:disabled, .btn-success:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-success { background: #059669; color: #fff; border: none; padding: 9px 16px; border-radius: 6px; font-weight: 600; cursor: pointer; }
.toast { position: fixed; bottom: 24px; right: 24px; background: #10b981; color: #fff; padding: 12px 20px; border-radius: 8px; font-size: 14px; z-index: 200; }
</style>