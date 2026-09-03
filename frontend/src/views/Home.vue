<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { store, addBooking } from '../store/store'

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

// Foto hero & kamar. Ganti URL ini kapan saja dengan foto hotel
// sungguhan milikmu (taruh filenya di src/assets/ lalu import,
// atau pakai path public/ kalau di-hosting statis).
const heroPhoto = 'https://picsum.photos/seed/nirwana-hero/700/900'
const roomPhotos = {
  1: 'https://picsum.photos/seed/kamar-rimba/600/400',
  2: 'https://picsum.photos/seed/suite-pesisir/600/400',
  3: 'https://picsum.photos/seed/kamar-sawah/600/400',
  4: 'https://picsum.photos/seed/villa-batu/600/400',
}
</script>

<template>
  <div>
    <section class="hero">
      <div>
        <h1>AUREA THE RESORT</h1>
        <p class="lede">
         Uluwatu, Bali Luxury Tropical Resort • Ocean View • Sunset
        </p>
        <div class="search">
          <div class="field"><label>Check-in</label><input type="date" /></div>
          <div class="field"><label>Check-out</label><input type="date" /></div>
          <div class="field">
            <label>Tamu</label>
            <select>
              <option>1 tamu</option>
              <option>2 tamu</option>
              <option>3 tamu</option>
              <option>4+ tamu</option>
            </select>
          </div>
          <button>Cari Kamar</button>
        </div>
      </div>
      <div class="hero-art">
        <img :src="heroPhoto" alt="Suasana Nirwana Stay" />
      </div>
    </section>

    <section class="section">
      <div class="section-head">
        <h2>Kamar &amp; Villa</h2>
        <span>{{ store.rooms.length }} pilihan tersedia</span>
      </div>
      <div class="rooms">
        <div class="room-card" v-for="(room, i) in store.rooms" :key="room.id">
          <div class="room-art">
            <img :src="roomPhotos[room.id]" :alt="room.name" />
          </div>
          <h3>{{ room.name }}</h3>
          <p class="desc">{{ room.desc }}</p>
          <p class="meta">Maks {{ room.capacity }} tamu</p>
          <div class="price-row">
            <div class="price">Rp{{ formatPrice(room.price) }}<small> / malam</small></div>
            <button class="book-btn" @click="openBooking(room)">Pesan Kamar</button>
          </div>
        </div>
      </div>
    </section>

    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal">
        <h3>Pesan {{ activeRoom.name }}</h3>
        <p class="sub">Rp{{ formatPrice(activeRoom.price) }} / malam</p>
        <div class="fieldset"><label>Nama tamu</label><input v-model="guestName" type="text" /></div>
        <div class="fieldset"><label>Check-in</label><input v-model="checkIn" type="date" /></div>
        <div class="fieldset"><label>Check-out</label><input v-model="checkOut" type="date" /></div>
        <div class="modal-actions">
          <button @click="showModal = false">Batal</button>
          <button class="primary" @click="confirmBooking">Konfirmasi</button>
        </div>
      </div>
    </div>

    <div v-if="toast" class="toast">{{ toast }}</div>

    <footer>
      <span>© 2026 Nirwana Stay</span>
      <span>Dibuat dengan Vue.js</span>
    </footer>
  </div>
</template>