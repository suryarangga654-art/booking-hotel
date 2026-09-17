<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { store } from '../store/store'
import api from '../utils/api'

const router = useRouter()
const reviews = ref([])
const loadingReviews = ref(true)

// URL Video Stok Hotel/Resort Luxury
const resortVideoUrl = '/videos/resort.mp4'

// Gambar fallback / default hero
const resortImageUrl = '/salah.jpg'

async function fetchReviews() {
  loadingReviews.value = true

  try {
    // Memanggil endpoint publik /api/ulasan (tanpa token)
    const res = await api.get('/ulasan')
    const rawData = res.data?.data || res.data
    const arrayData = Array.isArray(rawData) ? rawData : Object.values(rawData || {})
    
    // Filter ulasan yang valid/tampil
    reviews.value = arrayData.filter((review) => review.tampil !== false)
  } catch (error) {
    console.error('Gagal mengambil data ulasan publik:', error)
    reviews.value = []
  } finally {
    loadingReviews.value = false
  }
}

onMounted(fetchReviews)

function goToBooking(room) {
  router.push(`/booking/${room.id}`)
}

// Format harga mata uang Rupiah
function formatPrice(n) {
  return Number(n || 0).toLocaleString('id-ID')
}

// Helper format tanggal ulasan
function formatDate(dateString) {
  if (!dateString) return 'Baru saja'
  const options = { year: 'numeric', month: 'short', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('id-ID', options)
}

const roomPhotos = {
  1: '/aurea.jpg',
  2: '/aurea2.jpg',
  3: '/aurea3.jpg',
  4: '/aurea4.jpg',
}
</script>

<template>
  <div class="page-wrapper">
    <!-- HERO SECTION WITH VIDEO BACKGROUND -->
    <section class="hero-video-container">
      <video 
        :src="resortVideoUrl" 
        autoplay 
        muted 
        loop 
        playsinline 
        class="bg-video">
        Browser Anda tidak mendukung tag video.
      </video>
      
      <!-- Gradient Overlay untuk Keterbacaan Teks -->
      <div class="video-overlay"></div>

      <!-- Konten Utama di Atas Video -->
      <div class="hero-content">
        <!-- Kolom Kiri: Teks -->
        <div class="hero-text-col">
          <span class="badge-tag">LUXURY TROPICAL RESORT</span>
          <h1 class="hero-title">AUREA THE RESORT</h1>
          <p class="hero-subtitle">
            Nikmati keindahan Uluwatu, Bali dengan pemandangan samudera dan sunset terbaik.
          </p>

          <!-- Form Pencarian / Booking Ringkas -->
          <div class="search-box">
            <div class="field">
              <label>Check-in</label>
              <input type="date" />
            </div>
            <div class="field">
              <label>Check-out</label>
              <input type="date" />
            </div>
            <div class="field">
              <label>Tamu</label>
              <select>
                <option>1 Tamu</option>
                <option>2 Tamu</option>
                <option>3 Tamu</option>
                <option>4+ Tamu</option>
              </select>
            </div>
            <button class="btn-search">Cari Kamar</button>
          </div>
        </div>

        <!-- Kolom Kanan: Gambar dengan Bingkai Bening -->
        <div class="hero-image-col">
          <div class="hero-image-frame">
            <img :src="resortImageUrl" alt="Aurea The Resort" class="hero-image" />
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION KAMAR & VILLA -->
    <section class="section" id="rooms">
      <div class="section-head">
        <h2>Kamar &amp; Villa</h2>
        <span>{{ store.rooms ? store.rooms.length : 0 }} pilihan tersedia</span>
      </div>
      <div class="rooms">
        <div class="room-card" v-for="room in store.rooms" :key="room.id">
          <div class="room-art">
            <!-- Fallback gambar jika room.id tidak ada di roomPhotos -->
            <img :src="roomPhotos[room.id] || resortImageUrl" :alt="room.name || 'Foto Kamar'" />
          </div>
          <h3>{{ room.name }}</h3>
          <p class="desc">{{ room.desc }}</p>
          <p class="meta">Maks {{ room.capacity }} tamu</p>
          <div class="price-row">
            <div class="price">
              Rp{{ formatPrice(room.price) }}<small> / malam</small>
            </div>
            <button class="book-btn" @click="goToBooking(room)">Pesan Kamar</button>
          </div>
        </div>
      </div>
    </section>

    <!-- SECTION ULASAN TAMU -->
    <section class="section reviews-section">
      <div class="section-head">
        <h2>Ulasan Tamu</h2>
        <span>{{ reviews.length }} review</span>
      </div>

      <div v-if="loadingReviews" class="review-loading">Memuat ulasan...</div>

      <div v-else class="reviews-grid">
        <article v-for="review in reviews" :key="review.id" class="review-card">
          <div class="review-header">
            <div>
              <strong>
                {{ review.nama_tamu || review.pemesanan?.user?.nama || review.user?.nama || 'Tamu Anonim' }}
              </strong>
              <p>
                {{ review.tipe_kamar || review.pemesanan?.detail_pemesanan?.[0]?.kamar?.tipe_kamar?.nama_tipe || 'Kamar Resort' }}
              </p>
            </div>
            <span class="stars">{{ '★'.repeat(review.penilaian || review.rating || 0) }}</span>
          </div>

          <p class="review-comment">"{{ review.komentar || 'Tidak ada komentar.' }}"</p>

          <div class="review-footer">
            <span>{{ formatDate(review.created_at || review.tanggal) }}</span>
          </div>
        </article>

        <div v-if="reviews.length === 0" class="empty-review">
          Belum ada ulasan yang ditampilkan.
        </div>
      </div>
    </section>

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
}

/* =========================================
   HERO VIDEO SECTION
   ========================================= */
.hero-video-container {
  position: relative;
  height: 85vh;
  min-height: 550px;
  width: 100%;
  overflow: hidden;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #0f172a;
  color: #ffffff;
}

.bg-video {
  position: absolute;
  top: 50%;
  left: 50%;
  min-width: 100%;
  min-height: 100%;
  width: auto;
  height: auto;
  z-index: 0;
  transform: translate(-50%, -50%);
  object-fit: cover;
}

.video-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    180deg,
    rgba(15, 23, 42, 0.45) 0%,
    rgba(15, 23, 42, 0.75) 100%
  );
  z-index: 1;
}

.hero-content {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 1280px;
  margin: 0 auto;
  padding: 0 40px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 48px;
}

/* Kolom Kiri: Teks */
.hero-text-col {
  flex: 1;
  min-width: 0;
  text-align: left;
}

.badge-tag {
  display: inline-block;
  padding: 6px 16px;
  background: rgba(255, 255, 255, 0.15);
  backdrop-filter: blur(8px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  border-radius: 30px;
  font-size: 0.75rem;
  font-weight: 600;
  letter-spacing: 2px;
  text-transform: uppercase;
  margin-bottom: 20px;
}

.hero-title {
  font-size: 3.2rem;
  font-weight: 800;
  margin: 0 0 12px 0;
  letter-spacing: -1px;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.3);
  text-align: left;
}

.hero-subtitle {
  font-size: 1.15rem;
  margin-bottom: 36px;
  opacity: 0.9;
  font-weight: 300;
  line-height: 1.6;
  max-width: 480px;
  text-align: left;
}

/* Kolom Kanan: Gambar dengan Bingkai Bening */
.hero-image-col {
  flex: 1;
  min-width: 0;
  display: flex;
  justify-content: flex-end;
}

.hero-image-frame {
  border: 1px solid rgba(255, 255, 255, 0.6);
  background: rgba(255, 255, 255, 0.08);
  backdrop-filter: blur(6px);
  padding: 10px;
  border-radius: 14px;
  box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35);
  max-width: 440px;
  width: 100%;
}

.hero-image {
  width: 100%;
  height: 340px;
  object-fit: cover;
  display: block;
  border-radius: 8px;
}

/* =========================================
   SEARCH BOX
   ========================================= */
.search-box {
  display: flex;
  gap: 12px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  padding: 16px;
  border-radius: 12px;
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.25);
  color: #333;
  align-items: flex-end;
  max-width: 560px;
}

.field {
  flex: 1;
  display: flex;
  flex-direction: column;
  text-align: left;
}

.field label {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  color: #64748b;
  margin-bottom: 6px;
}

.field input,
.field select {
  width: 100%;
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 0.9rem;
  outline: none;
  background-color: #fff;
  box-sizing: border-box;
}

.btn-search {
  background: #0f172a;
  color: #ffffff;
  border: none;
  padding: 0 28px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.2s ease;
  height: 42px;
  white-space: nowrap;
}

.btn-search:hover {
  background: #1e293b;
}

/* =========================================
   ROOMS SECTION
   ========================================= */
.section {
  max-width: 1200px;
  margin: 0 auto;
  padding: 60px 20px;
}

.section-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-end;
  margin-bottom: 32px;
  border-bottom: 2px solid #e2e8f0;
  padding-bottom: 12px;
}

.section-head h2 {
  font-size: 1.8rem;
  margin: 0;
  color: #0f172a;
}

.section-head span {
  color: #64748b;
  font-size: 0.9rem;
}

.rooms {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 24px;
}

.room-card {
  background: #ffffff;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  border: 1px solid #e2e8f0;
  display: flex;
  flex-direction: column;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.room-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 25px rgba(0, 0, 0, 0.1);
}

.room-art img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.room-card h3 {
  font-size: 1.25rem;
  margin: 16px 16px 8px;
  color: #0f172a;
}

.desc {
  font-size: 0.875rem;
  color: #64748b;
  margin: 0 16px 12px;
  line-height: 1.5;
  flex-grow: 1;
}

.meta {
  font-size: 0.8rem;
  color: #0284c7;
  font-weight: 600;
  margin: 0 16px 16px;
}

.price-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 16px;
  border-top: 1px solid #f1f5f9;
  background: #fafafa;
}

.price {
  font-weight: 700;
  color: #0f172a;
  font-size: 1.1rem;
}

.price small {
  font-size: 0.75rem;
  color: #64748b;
  font-weight: 400;
}

.book-btn {
  background: #0284c7;
  color: #ffffff;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  font-size: 0.85rem;
  transition: background 0.2s;
}

.book-btn:hover {
  background: #0369a1;
}

/* =========================================
   REVIEWS SECTION
   ========================================= */
.reviews-section {
  padding-top: 8px;
}

.reviews-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
  margin-top: 20px;
}

.review-card {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 20px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.04);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 12px;
  margin-bottom: 12px;
}

.review-header strong {
  display: block;
  font-size: 0.98rem;
  color: #0f172a;
}

.review-header p {
  margin: 4px 0 0;
  color: #64748b;
  font-size: 0.78rem;
}

.stars {
  color: #f59e0b;
  font-size: 1rem;
  white-space: nowrap;
}

.review-comment {
  margin: 0;
  color: #334155;
  line-height: 1.6;
  font-style: italic;
}

.review-footer {
  margin-top: 14px;
  color: #94a3b8;
  font-size: 0.75rem;
}

.review-loading,
.empty-review {
  margin-top: 18px;
  color: #64748b;
  font-size: 0.95rem;
}

/* =========================================
   FOOTER
   ========================================= */
footer {
  border-top: 1px solid #e2e8f0;
  padding: 24px 20px;
  display: flex;
  justify-content: space-between;
  max-width: 1200px;
  margin: 0 auto;
  color: #64748b;
  font-size: 0.875rem;
}

/* =========================================
   RESPONSIVE
   ========================================= */
@media (max-width: 900px) {
  .hero-video-container {
    height: auto;
    min-height: 100vh;
    padding: 100px 0 40px;
  }

  .hero-content {
    flex-direction: column;
    text-align: center;
  }
  .hero-text-col {
    text-align: center;
  }
  .hero-title,
  .hero-subtitle {
    text-align: center;
    margin-left: auto;
    margin-right: auto;
  }
  .hero-image-col {
    justify-content: center;
    margin-top: 24px;
  }
  .search-box {
    margin: 0 auto;
  }

  .rooms {
    grid-template-columns: 1fr;
  }

  .section {
    padding: 40px 20px;
  }

  .section-head {
    flex-direction: column;
    align-items: flex-start;
    gap: 6px;
  }
}

@media (max-width: 768px) {
  .hero-title {
    font-size: 2.2rem;
  }
  .search-box {
    flex-direction: column;
    gap: 12px;
  }
  .btn-search {
    width: 100%;
  }
  .hero-image {
    height: 240px;
  }
}

@media (max-width: 480px) {
  .price-row {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .book-btn {
    width: 100%;
  }
}
</style>