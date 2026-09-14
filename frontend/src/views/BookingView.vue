<script setup>
import { ref, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { store, addBooking } from '../store/store'

const route = useRoute()
const router = useRouter()

const guestName = ref(store.user ? store.user.name : '')
const checkIn = ref('')
const checkOut = ref('')
const toast = ref('')
const submitted = ref(false)

// Ambil tanggal hari ini (format YYYY-MM-DD lokal) untuk mengunci tanggal lalu
const today = computed(() => {
  const d = new Date()
  const year = d.getFullYear()
  const month = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
})

// Mengunci tanggal Check-out agar tidak bisa sebelum Check-in (atau hari ini jika belum pilih Check-in)
const minCheckOut = computed(() => {
  return checkIn.value ? checkIn.value : today.value
})

const roomPhotos = {
  1: '/aurea.jpg',
  2: '/aurea2.jpg',
  3: '/aurea3.jpg',
  4: '/aurea4.jpg',
}

// Ambil data kamar berdasarkan id di URL (/booking/:id)
const room = computed(() => {
  const id = Number(route.params.id)
  return store.rooms.find((r) => r.id === id)
})

// Kalau belum login, lempar ke halaman login dulu
if (!store.user) {
  router.push('/login')
}

function formatPrice(n) {
  return n.toLocaleString('id-ID')
}

const jumlahMalam = computed(() => {
  if (!checkIn.value || !checkOut.value) return 0
  const inDate = new Date(checkIn.value)
  const outDate = new Date(checkOut.value)
  const diff = (outDate - inDate) / (1000 * 60 * 60 * 24)
  return diff > 0 ? diff : 0
})

const totalHarga = computed(() => {
  if (!room.value || jumlahMalam.value <= 0) return 0
  return room.value.price * jumlahMalam.value
})

function confirmBooking() {
  if (!checkIn.value || !checkOut.value || !guestName.value) return
  if (jumlahMalam.value <= 0) {
    toast.value = 'Tanggal check-out harus setelah check-in.'
    setTimeout(() => (toast.value = ''), 3000)
    return
  }

  addBooking({
    roomId: room.value.id,
    guestName: guestName.value,
    checkIn: checkIn.value,
    checkOut: checkOut.value,
  })

  submitted.value = true
}

function backToHome() {
  router.push('/#rooms')
}
</script>

<template>
  <div class="page-wrapper">

    <div v-if="!room" class="not-found">
      <p>Kamar tidak ditemukan.</p>
      <router-link to="/#rooms" class="link-back">&larr; Kembali ke daftar kamar</router-link>
    </div>

    <div v-else class="booking-container">

      <router-link to="/#rooms" class="link-back">&larr; Kembali ke daftar kamar</router-link>

      <!-- STATE: BERHASIL DIPESAN -->
      <div v-if="submitted" class="success-card">
        <div class="success-icon">✓</div>
        <h2>Pemesanan Terkirim</h2>
        <p>
          Terima kasih, {{ guestName }}. Pemesanan {{ room.name }} kamu sedang
          menunggu konfirmasi dari tim kami.
        </p>
        <button class="btn-primary" @click="backToHome">Kembali ke Beranda</button>
      </div>

      <!-- STATE: FORM PEMESANAN -->
      <div v-else class="booking-grid">

        <!-- INFO KAMAR -->
        <div class="room-info">
          <img :src="roomPhotos[room.id]" :alt="room.name" class="room-photo" />
          <h1>{{ room.name }}</h1>
          <p class="room-desc">{{ room.desc }}</p>
          <p class="room-meta">Maks {{ room.capacity }} tamu</p>
          <div class="room-price">
            Rp{{ formatPrice(room.price) }}<small> / malam</small>
          </div>
        </div>

        <!-- FORM PEMESANAN -->
        <div class="booking-form">
          <h2>Detail Pemesanan</h2>

          <div class="fieldset">
            <label>Nama Tamu</label>
            <input v-model="guestName" type="text" placeholder="Nama lengkap" />
          </div>

          <div class="fieldset-row">
            <div class="fieldset">
              <label>Check-in</label>
              <input 
                v-model="checkIn" 
                type="date" 
                :min="today" 
              />
            </div>
            <div class="fieldset">
              <label>Check-out</label>
              <input 
                v-model="checkOut" 
                type="date" 
                :min="minCheckOut" 
              />
            </div>
          </div>

          <div v-if="jumlahMalam > 0" class="price-summary">
            <div class="price-row">
              <span>Rp{{ formatPrice(room.price) }} x {{ jumlahMalam }} malam</span>
              <span>Rp{{ formatPrice(totalHarga) }}</span>
            </div>
            <div class="price-row total">
              <span>Total</span>
              <span>Rp{{ formatPrice(totalHarga) }}</span>
            </div>
          </div>

          <button class="btn-primary w-full" @click="confirmBooking">
            Konfirmasi Pemesanan
          </button>
        </div>

      </div>
    </div>

    <!-- TOAST -->
    <div v-if="toast" class="toast">{{ toast }}</div>

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

.not-found {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 12px;
  color: #64748b;
}

.booking-container {
  flex: 1;
  max-width: 1000px;
  width: 100%;
  margin: 0 auto;
  padding: 40px 20px 60px;
}

.link-back {
  display: inline-block;
  margin-bottom: 24px;
  color: #0284c7;
  text-decoration: none;
  font-size: 14px;
  font-weight: 600;
}

.link-back:hover {
  text-decoration: underline;
}

/* =========================================
   GRID: INFO + FORM
   ========================================= */
.booking-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 40px;
  align-items: start;
}

.room-info {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.room-photo {
  width: 100%;
  height: 260px;
  object-fit: cover;
  display: block;
}

.room-info h1 {
  margin: 20px 20px 8px;
  font-size: 1.6rem;
  color: #0f172a;
}

.room-desc {
  margin: 0 20px 12px;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.6;
}

.room-meta {
  margin: 0 20px 16px;
  color: #0284c7;
  font-weight: 600;
  font-size: 0.85rem;
}

.room-price {
  margin: 0 20px 20px;
  padding-top: 16px;
  border-top: 1px solid #f1f5f9;
  font-size: 1.4rem;
  font-weight: 700;
  color: #0f172a;
}

.room-price small {
  font-size: 0.8rem;
  color: #64748b;
  font-weight: 400;
}

/* =========================================
   FORM
   ========================================= */
.booking-form {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 28px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.booking-form h2 {
  margin: 0 0 20px;
  font-size: 1.2rem;
  color: #0f172a;
}

.fieldset {
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.fieldset-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 14px;
}

.fieldset label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
}

.fieldset input {
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  outline: none;
  font-size: 0.9rem;
}

.fieldset input:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

.price-summary {
  margin: 20px 0;
  padding: 14px 16px;
  background: #f8fafc;
  border-radius: 8px;
}

.price-row {
  display: flex;
  justify-content: space-between;
  font-size: 0.85rem;
  color: #64748b;
  padding: 4px 0;
}

.price-row.total {
  margin-top: 6px;
  padding-top: 10px;
  border-top: 1px solid #e2e8f0;
  font-weight: 700;
  color: #0f172a;
  font-size: 1rem;
}

.btn-primary {
  background: #0f172a;
  color: #ffffff;
  border: none;
  padding: 12px 24px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s;
}

.btn-primary:hover {
  background: #1e293b;
}

.btn-primary.w-full {
  width: 100%;
}

/* =========================================
   SUCCESS STATE
   ========================================= */
.success-card {
  max-width: 480px;
  margin: 40px auto 0;
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 40px;
  text-align: center;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.success-icon {
  width: 56px;
  height: 56px;
  margin: 0 auto 20px;
  background: #dcfce7;
  color: #166534;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 28px;
  font-weight: 700;
}

.success-card h2 {
  margin: 0 0 12px;
  color: #0f172a;
}

.success-card p {
  margin: 0 0 24px;
  color: #64748b;
  font-size: 0.9rem;
  line-height: 1.6;
}

/* =========================================
   TOAST
   ========================================= */
.toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: #ef4444;
  color: #ffffff;
  padding: 14px 24px;
  border-radius: 8px;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  z-index: 200;
  font-weight: 500;
}

/* =========================================
   FOOTER
   ========================================= */
footer {
  border-top: 1px solid #e2e8f0;
  padding: 24px 20px;
  text-align: center;
  color: #64748b;
  font-size: 0.875rem;
}

/* =========================================
   RESPONSIVE
   ========================================= */
@media (max-width: 800px) {
  .booking-grid {
    grid-template-columns: 1fr;
  }

  .fieldset-row {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 480px) {
  .toast {
    left: 16px;
    right: 16px;
    text-align: center;
  }
}
</style>