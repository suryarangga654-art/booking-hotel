<script setup>
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { store, addBooking } from '../store/store'

const route = useRoute()
const router = useRouter()

const showModal = ref(false)
const activeRoom = ref(null)
const guestName = ref('')
const checkIn = ref('')
const checkOut = ref('')
const toast = ref('')

function openBooking(room) {
  if (!store.user) {
    router.push('/login')
    return
  }
  activeRoom.value = room
  guestName.value = store.user.name
  showModal.value = true
}

function confirmBooking() {
  if (!checkIn.value || !checkOut.value || !guestName.value) return
  addBooking({
    roomId: activeRoom.value.id,
    guestName: guestName.value,
    checkIn: checkIn.value,
    checkOut: checkOut.value,
  })
  showModal.value = false
  toast.value = 'Pemesanan terkirim. Menunggu konfirmasi tim kami.'
  setTimeout(() => (toast.value = ''), 3200)
}

function formatPrice(n) {
  return n.toLocaleString('id-ID')
}

const roomPhotos = {
  1: '/aurea.jpg',
  2: '/aurea2.jpg',
  3: '/aurea3.jpg',
  4: '/aurea4.jpg',
}

// Kalau ada query ?search= dari navbar, dipakai buat filter (opsional)
const searchTerm = route.query.search
  ? String(route.query.search).toLowerCase()
  : ''

const filteredRooms = searchTerm
  ? store.rooms.filter((r) => r.name.toLowerCase().includes(searchTerm))
  : store.rooms
</script>

<template>
  <div class="page-wrapper">

    <!-- SECTION KAMAR & VILLA -->
    <section class="section">
      <div class="section-head">
        <h2>Kamar &amp; Villa</h2>
        <span>{{ filteredRooms.length }} pilihan tersedia</span>
      </div>
      <div class="rooms">
        <div class="room-card" v-for="room in filteredRooms" :key="room.id">
          <div class="room-art">
            <img :src="roomPhotos[room.id]" :alt="room.name" />
          </div>
          <h3>{{ room.name }}</h3>
          <p class="desc">{{ room.desc }}</p>
          <p class="meta">Maks {{ room.capacity }} tamu</p>
          <div class="price-row">
            <div class="price">
              Rp{{ formatPrice(room.price) }}<small> / malam</small>
            </div>
            <button class="book-btn" @click="openBooking(room)">Pesan Kamar</button>
          </div>
        </div>
      </div>

      <div v-if="filteredRooms.length === 0" class="empty-state">
        Tidak ada kamar yang cocok dengan pencarian kamu.
      </div>
    </section>

    <!-- MODAL PEMESANAN -->
    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal">
        <h3>Pesan {{ activeRoom.name }}</h3>
        <p class="sub">Rp{{ formatPrice(activeRoom.price) }} / malam</p>
        <div class="fieldset">
          <label>Nama Tamu</label>
          <input v-model="guestName" type="text" />
        </div>
        <div class="fieldset">
          <label>Check-in</label>
          <input v-model="checkIn" type="date" />
        </div>
        <div class="fieldset">
          <label>Check-out</label>
          <input v-model="checkOut" type="date" />
        </div>
        <div class="modal-actions">
          <button class="btn-cancel" @click="showModal = false">Batal</button>
          <button class="primary" @click="confirmBooking">Konfirmasi</button>
        </div>
      </div>
    </div>

    <!-- TOAST NOTIFIKASI -->
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

.empty-state {
  text-align: center;
  padding: 40px 20px;
  color: #64748b;
  font-size: 0.95rem;
}

/* =========================================
   MODAL & TOAST
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
}

.modal {
  background: #ffffff;
  border-radius: 12px;
  padding: 28px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
}

.modal h3 {
  margin: 0 0 4px 0;
  color: #0f172a;
}

.modal .sub {
  color: #64748b;
  font-size: 0.9rem;
  margin-bottom: 20px;
}

.fieldset {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.fieldset label {
  font-size: 0.8rem;
  font-weight: 600;
  color: #334155;
}

.fieldset input {
  padding: 10px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  outline: none;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 24px;
}

.modal-actions button {
  padding: 10px 18px;
  border-radius: 6px;
  font-weight: 600;
  cursor: pointer;
  border: none;
}

.btn-cancel {
  background: #e2e8f0;
  color: #334155;
}

.btn-cancel:hover {
  background: #cbd5e1;
}

.modal-actions button.primary {
  background: #0284c7;
  color: #ffffff;
}

.modal-actions button.primary:hover {
  background: #0369a1;
}

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
</style>