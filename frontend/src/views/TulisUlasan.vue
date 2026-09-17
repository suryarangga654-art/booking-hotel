<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { store } from '../store/store'
import api from '../utils/api'

const router = useRouter()
const route = useRoute()

const rating = ref(0)
const hoverRating = ref(0)
const komentar = ref('')
const pemesananId = ref('')
const bookingsOptions = ref([])
const loading = ref(false)
const errorMsg = ref('')
const submitted = ref(false)

// Cek token auth
const token = localStorage.getItem('token') || localStorage.getItem('velora_token')
if (!token && !store.user) {
  router.push('/login')
}

async function loadBookings() {
  try {
    // Ambil daftar pemesanan milik tamu yang bisa diulas
    const res = await api.get('/tamu/pemesanan')
    const data = res.data?.data || res.data
    bookingsOptions.value = Array.isArray(data) ? data : []
  } catch (e) {
    console.error('Gagal mengambil daftar pemesanan:', e)
  }

  // Jika ada param :id (pemesanan_id) di URL (/tulis-ulasan/:id)
  if (route.params.id) {
    pemesananId.value = route.params.id
  }
}

onMounted(loadBookings)

async function submitUlasan() {
  errorMsg.value = ''

  if (!pemesananId.value) {
    errorMsg.value = 'Pilih riwayat pemesanan yang ingin diulas.'
    return
  }
  if (rating.value === 0) {
    errorMsg.value = 'Berikan rating bintang dulu ya.'
    return
  }
  if (!komentar.value.trim()) {
    errorMsg.value = 'Tulis komentar singkat tentang pengalaman kamu.'
    return
  }

  loading.value = true
  try {
    // Endpoint disesuaikan ke /tamu/ulasan & key payload sesuai field DB (penilaian, pemesanan_id)
    await api.post('/tamu/ulasan', {
      pemesanan_id: pemesananId.value,
      penilaian: rating.value,
      komentar: komentar.value,
    })
    submitted.value = true
  } catch (error) {
    errorMsg.value =
      error.response?.data?.message || 'Gagal mengirim ulasan. Coba lagi nanti.'
  } finally {
    loading.value = false
  }
}

function backToHome() {
  router.push('/')
}
</script>

<template>
  <div class="page-wrapper">
    <div class="review-container">

      <router-link to="/" class="link-back">&larr; Kembali ke Beranda</router-link>

      <!-- STATE: BERHASIL -->
      <div v-if="submitted" class="success-card">
        <div class="success-icon">✓</div>
        <h2>Terima Kasih!</h2>
        <p>Ulasan kamu sudah terkirim dan berhasil disimpan.</p>
        <button class="btn-primary" @click="backToHome">Kembali ke Beranda</button>
      </div>

      <!-- STATE: FORM -->
      <div v-else class="review-card">
        <h1>Bagikan Pengalaman Kamu</h1>
        <p class="sub">
          Ceritakan bagaimana pengalaman menginap kamu di Velora Resort.
        </p>

        <p v-if="errorMsg" class="error-message">{{ errorMsg }}</p>

        <div class="fieldset">
          <label>Pilih Pemesanan</label>
          <select v-model="pemesananId">
            <option value="" disabled>Pilih riwayat pemesanan kamu</option>
            <option v-for="booking in bookingsOptions" :key="booking.id" :value="booking.id">
              Pemesanan #{{ booking.id }} - {{ booking.kamar?.nama || 'Kamar' }}
            </option>
          </select>
        </div>

        <div class="fieldset">
          <label>Rating</label>
          <div class="star-picker">
            <span
              v-for="n in 5"
              :key="n"
              class="star"
              :class="{ filled: n <= (hoverRating || rating) }"
              @mouseenter="hoverRating = n"
              @mouseleave="hoverRating = 0"
              @click="rating = n"
            >
              ★
            </span>
          </div>
        </div>

        <div class="fieldset">
          <label>Komentar</label>
          <textarea
            v-model="komentar"
            rows="4"
            placeholder="Bagaimana pelayanan, kamar, dan fasilitasnya?"
          ></textarea>
        </div>

        <button class="btn-primary w-full" :disabled="loading" @click="submitUlasan">
          {{ loading ? 'Mengirim...' : 'Kirim Ulasan' }}
        </button>
      </div>

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

.review-container {
  flex: 1;
  max-width: 560px;
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

.review-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 32px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
}

.review-card h1 {
  margin: 0 0 6px;
  font-size: 1.5rem;
  color: #0f172a;
  font-family: Georgia, serif;
}

.sub {
  margin: 0 0 24px;
  color: #64748b;
  font-size: 0.9rem;
}

.error-message {
  margin: 0 0 18px;
  padding: 10px 13px;
  border-radius: 6px;
  background: rgba(220, 38, 38, 0.08);
  border: 1px solid rgba(220, 38, 38, 0.2);
  color: #dc2626;
  font-size: 13px;
}

.fieldset {
  margin-bottom: 20px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.fieldset label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
}

.fieldset select,
.fieldset textarea {
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  outline: none;
  font-family: inherit;
  font-size: 0.9rem;
  color: #0f172a;
  resize: vertical;
}

.fieldset select:focus,
.fieldset textarea:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

.star-picker {
  display: flex;
  gap: 6px;
}

.star {
  font-size: 2rem;
  line-height: 1;
  color: #e2e8f0;
  cursor: pointer;
  transition: color 0.15s ease, transform 0.1s ease;
}

.star:hover {
  transform: scale(1.1);
}

.star.filled {
  color: #f59e0b;
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

.btn-primary:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.btn-primary.w-full {
  width: 100%;
}

.success-card {
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

footer {
  border-top: 1px solid #e2e8f0;
  padding: 24px 20px;
  text-align: center;
  color: #64748b;
  font-size: 0.875rem;
}
</style>