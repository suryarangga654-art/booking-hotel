<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { store, bookingListStore } from '../store/store' // Tambahkan bookingListStore atau state global
import api from '../utils/api'

const router = useRouter()

const loading = ref(true)
const bookingList = ref([])
const filterStatus = ref('semua')

// Kalau belum login, lempar ke halaman login dulu
if (!store.user) {
  router.push('/login')
}

async function fetchBookings() {
  loading.value = true
  try {
    const res = await api.get('/pemesanan/saya')
    bookingList.value = res.data
  } catch (error) {
    console.error('Gagal memuat pemesanan dari API, menggunakan data lokal/store:', error)
    loadDummyData()
  } finally {
    loading.value = false
  }
}

function loadDummyData() {
  // Jika di store/store.js sudah menyimpan data booking, gunakan itu; jika kosong, pakai default
  if (bookingListStore && bookingListStore.value && bookingListStore.value.length > 0) {
    bookingList.value = bookingListStore.value
    return
  }

  bookingList.value = [
    {
      id: 1,
      kode_pemesanan: 'VLR-20260910-001',
      tipe_kamar: 'Suite Pesisir',
      tanggal_check_in: '2026-09-20',
      tanggal_check_out: '2026-09-22',
      jumlah_total: 2500000,
      status_pemesanan: 'dikonfirmasi',
      status_pembayaran: 'lunas',
    },
    {
      id: 2,
      kode_pemesanan: 'VLR-20260908-002',
      tipe_kamar: 'Kamar Rimba',
      tanggal_check_in: '2026-09-14',
      tanggal_check_out: '2026-09-16',
      jumlah_total: 1300000,
      status_pemesanan: 'menunggu',
      status_pembayaran: 'belum_dibayar',
    },
    {
      id: 3,
      kode_pemesanan: 'VLR-20260820-003',
      tipe_kamar: 'Villa Batu',
      tanggal_check_in: '2026-08-25',
      tanggal_check_out: '2026-08-28',
      jumlah_total: 6300000,
      status_pemesanan: 'check_out',
      status_pembayaran: 'lunas',
    },
  ]
}

onMounted(fetchBookings)

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

const statusPemesananOptions = ['menunggu', 'dikonfirmasi', 'check_in', 'check_out', 'dibatalkan']

const statusPemesananLabel = {
  menunggu: 'Menunggu Konfirmasi',
  dikonfirmasi: 'Dikonfirmasi',
  check_in: 'Sedang Menginap',
  check_out: 'Selesai',
  dibatalkan: 'Dibatalkan',
}

const statusPembayaranLabel = {
  belum_dibayar: 'Belum Dibayar',
  dibayar_sebagian: 'Dibayar Sebagian',
  lunas: 'Lunas',
  dikembalikan: 'Dana Dikembalikan',
}

const filteredList = computed(() => {
  if (filterStatus.value === 'semua') return bookingList.value
  return bookingList.value.filter((b) => b.status_pemesanan === filterStatus.value)
})

function jumlahMalam(booking) {
  const inDate = new Date(booking.tanggal_check_in)
  const outDate = new Date(booking.tanggal_check_out)
  const diff = (outDate - inDate) / (1000 * 60 * 60 * 24)
  return diff > 0 ? diff : 1
}
</script>

<template>
  <div class="page-wrapper">
    <div class="container">

      <header class="page-header">
        <h1>Pemesanan Saya</h1>
        <p>Riwayat dan status semua pemesanan kamu di Velora Resort</p>
      </header>

      <!-- FILTER -->
      <div class="filter-tabs">
        <button
          class="tab-btn"
          :class="{ active: filterStatus === 'semua' }"
          @click="filterStatus = 'semua'"
        >
          Semua
        </button>
        <button
          v-for="s in statusPemesananOptions"
          :key="s"
          class="tab-btn"
          :class="{ active: filterStatus === s }"
          @click="filterStatus = s"
        >
          {{ statusPemesananLabel[s] }}
        </button>
      </div>

      <div v-if="loading" class="loading-state">
        Memuat pemesanan kamu...
      </div>

      <template v-else>
        <!-- LIST BOOKING -->
        <div v-if="filteredList.length > 0" class="booking-list">
          <div class="booking-card" v-for="b in filteredList" :key="b.id">
            <div class="booking-top">
              <span class="kode">{{ b.kode_pemesanan }}</span>
              <span class="badge" :class="'status-' + b.status_pemesanan">
                {{ statusPemesananLabel[b.status_pemesanan] }}
              </span>
            </div>

            <h3>{{ b.tipe_kamar }}</h3>

            <div class="booking-info">
              <div class="info-item">
                <span class="info-label">Check-in</span>
                <span class="info-value">{{ formatTanggal(b.tanggal_check_in) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Check-out</span>
                <span class="info-value">{{ formatTanggal(b.tanggal_check_out) }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Durasi</span>
                <span class="info-value">{{ jumlahMalam(b) }} malam</span>
              </div>
            </div>

            <div class="booking-bottom">
              <div class="total-price">
                {{ formatRupiah(b.jumlah_total) }}
              </div>
              <span class="badge pay" :class="'pay-' + b.status_pembayaran">
                {{ statusPembayaranLabel[b.status_pembayaran] }}
              </span>
            </div>
          </div>
        </div>

        <!-- EMPTY STATE -->
        <div v-else class="empty-state">
          <p>Belum ada pemesanan dengan status ini.</p>
          <router-link to="/#rooms" class="btn-primary">Cari Kamar</router-link>
        </div>
      </template>

    </div>

    <footer>
      <span>© 2026 VELORA</span>
    </footer>
  </div>
</template>

<style scoped>
.page-wrapper {
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  color: #1e293b;
  background-color: #f8fafc;
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

.container {
  flex: 1;
  max-width: 900px;
  width: 100%;
  margin: 0 auto;
  padding: 40px 20px 60px;
}

.page-header {
  margin-bottom: 24px;
}

.page-header h1 {
  margin: 0 0 6px;
  font-size: 1.8rem;
  color: #0f172a;
  font-family: Georgia, serif;
}

.page-header p {
  margin: 0;
  color: #64748b;
  font-size: 0.9rem;
}

.filter-tabs {
  display: flex;
  gap: 8px;
  flex-wrap: wrap;
  margin-bottom: 24px;
}

.tab-btn {
  padding: 8px 16px;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 999px;
  color: #64748b;
  font-size: 0.8rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  white-space: nowrap;
}

.tab-btn:hover {
  border-color: #0284c7;
  color: #0284c7;
}

.tab-btn.active {
  background: #0f172a;
  border-color: #0f172a;
  color: #ffffff;
}

.loading-state {
  text-align: center;
  padding: 60px 0;
  color: #64748b;
}

.booking-list {
  display: flex;
  flex-direction: column;
  gap: 16px;
}

.booking-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 20px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.04);
}

.booking-top {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 8px;
}

.kode {
  font-family: 'Courier New', monospace;
  font-weight: 700;
  color: #0284c7;
  font-size: 0.85rem;
}

.booking-card h3 {
  margin: 0 0 16px;
  font-size: 1.15rem;
  color: #0f172a;
}

.booking-info {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 12px;
  margin-bottom: 16px;
  padding: 12px 14px;
  background: #f8fafc;
  border-radius: 8px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 2px;
}

.info-label {
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #94a3b8;
}

.info-value {
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
}

.booking-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 14px;
  border-top: 1px solid #f1f5f9;
}

.total-price {
  font-size: 1.1rem;
  font-weight: 700;
  color: #0f172a;
}

.badge {
  display: inline-block;
  padding: 5px 12px;
  border-radius: 999px;
  font-size: 0.75rem;
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

.empty-state {
  text-align: center;
  padding: 60px 20px;
  background: #ffffff;
  border: 1px dashed #cbd5e1;
  border-radius: 12px;
}

.empty-state p {
  color: #64748b;
  margin: 0 0 20px;
}

.btn-primary {
  display: inline-block;
  background: #0f172a;
  color: #ffffff;
  padding: 10px 24px;
  border-radius: 6px;
  font-weight: 600;
  text-decoration: none;
  font-size: 0.85rem;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #1e293b;
}

footer {
  border-top: 1px solid #e2e8f0;
  padding: 24px 20px;
  text-align: center;
  color: #64748b;
  font-size: 0.875rem;
}

@media (max-width: 600px) {
  .booking-info {
    grid-template-columns: 1fr;
  }

  .booking-bottom {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }
}
</style>