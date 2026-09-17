<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { store, bookingListStore } from '../store/store'

const router = useRouter()

if (!store.user) {
  router.push('/login')
}

const bookingCount = computed(() => bookingListStore.value?.length || 0)
const profileName = computed(() => store.user?.name || 'Tamu')
const profileEmail = computed(() => store.user?.email || '-')
const profileAvatar = computed(() => store.user?.avatar || '/default-avatar.png')

function goHome() {
  router.push('/')
}
</script>

<template>
  <div class="page-wrapper">
    <div class="profile-shell">
      <div class="profile-card">
        <button class="back-link" @click="goHome">← Kembali</button>

        <div class="profile-header">
          <img :src="profileAvatar" alt="Avatar profil" class="avatar" />
          <div>
            <p class="eyebrow">Profil Saya</p>
            <h1>{{ profileName }}</h1>
            <span class="status">Tamu aktif</span>
          </div>
        </div>

        <div class="info-grid">
          <div class="info-box">
            <label>Nama Lengkap</label>
            <p>{{ profileName }}</p>
          </div>

          <div class="info-box">
            <label>Email</label>
            <p>{{ profileEmail }}</p>
          </div>

          <div class="info-box">
            <label>Jumlah Pemesanan</label>
            <p>{{ bookingCount }} pesanan</p>
          </div>

          <div class="info-box">
            <label>Status</label>
            <p>Sudah terdaftar</p>
          </div>
        </div>

        <div class="mini-panel">
          <h2>Ringkasan</h2>
          <ul>
            <li>Anda bisa melihat riwayat pemesanan di halaman pemesanan.</li>
            <li>Anda bisa menulis ulasan setelah pengalaman menginap.</li>
            <li>Data profil ini tersimpan di sesi login saat ini.</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-wrapper {
  min-height: 100vh;
  background: linear-gradient(180deg, #f8fafc 0%, #eff6ff 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 18px;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
}

.profile-shell {
  width: 100%;
  max-width: 900px;
}

.profile-card {
  background: #fff;
  border: 1px solid #e2e8f0;
  border-radius: 20px;
  box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
  padding: 28px;
}

.back-link {
  border: none;
  background: transparent;
  color: #0284c7;
  font-weight: 700;
  cursor: pointer;
  margin-bottom: 18px;
  padding: 0;
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 20px;
  padding-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
  margin-bottom: 24px;
}

.avatar {
  width: 90px;
  height: 90px;
  border-radius: 50%;
  object-fit: cover;
  border: 3px solid #dbeafe;
  background: #e2e8f0;
}

.eyebrow {
  margin: 0 0 8px;
  color: #0284c7;
  font-size: 12px;
  font-weight: 700;
  letter-spacing: 1.5px;
  text-transform: uppercase;
}

.profile-header h1 {
  margin: 0;
  font-size: clamp(1.8rem, 3vw, 2.6rem);
  color: #0f172a;
}

.status {
  display: inline-block;
  margin-top: 8px;
  background: #dcfce7;
  color: #166534;
  border-radius: 999px;
  padding: 6px 10px;
  font-size: 12px;
  font-weight: 700;
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  gap: 18px;
}

.info-box {
  background: #f8fafc;
  border: 1px solid #e2e8f0;
  border-radius: 14px;
  padding: 16px;
}

.info-box label {
  display: block;
  color: #64748b;
  font-size: 12px;
  font-weight: 700;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.info-box p {
  margin: 0;
  color: #0f172a;
  font-size: 1rem;
  font-weight: 600;
}

.mini-panel {
  border: 1px solid #dbeafe;
  background: #eff6ff;
  border-radius: 16px;
  padding: 18px 20px;
  margin-top: 24px;
}

.mini-panel h2 {
  margin: 0 0 12px;
  font-size: 1.1rem;
  color: #0f172a;
}

.mini-panel ul {
  margin: 0;
  padding-left: 20px;
  color: #334155;
  line-height: 1.7;
}

@media (max-width: 640px) {
  .profile-card {
    padding: 20px;
  }

  .profile-header {
    flex-direction: column;
    align-items: flex-start;
  }
}
</style>