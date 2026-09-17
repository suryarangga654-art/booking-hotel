import { reactive, ref } from 'vue'

export const bookingListStore = ref([])
export const defaultUserAvatar = 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=200&q=80'

function safeRead(key, fallback) {
  try {
    const raw = localStorage.getItem(key)
    return raw ? JSON.parse(raw) : fallback
  } catch {
    return fallback
  }
}

function safeWrite(key, value) {
  try {
    if (value === null || value === undefined) {
      localStorage.removeItem(key)
    } else {
      localStorage.setItem(key, JSON.stringify(value))
    }
  } catch {
    // ignore quota error
  }
}

export const store = reactive({
  user: safeRead('velora_user', null),
  users: safeRead('velora_users', []),
  token: localStorage.getItem('velora_token') || localStorage.getItem('token') || '',
  rooms: [
    { id: 1, name: 'Kamar Rimba', desc: 'Kamar tenang menghadap taman tropis dengan tempat tidur kayu jati dan balkon pribadi.', capacity: 2, price: 650000, status: 'tersedia' },
    { id: 2, name: 'Suite Pesisir', desc: 'Suite luas berpemandangan laut, area duduk terpisah, dan kamar mandi terbuka.', capacity: 3, price: 1250000, status: 'tersedia' },
    { id: 3, name: 'Kamar Sawah', desc: 'Kamar hangat dengan jendela besar menghadap hamparan sawah, cocok untuk pasangan.', capacity: 2, price: 480000, status: 'dibersihkan' },
    { id: 4, name: 'Villa Batu', desc: 'Villa satu lantai dengan kolam pribadi dan dapur kecil, ideal untuk keluarga.', capacity: 4, price: 2100000, status: 'perbaikan' },
  ],
  bookings: [],
  nextBookingId: 1,

  // Method reaktif untuk memperbarui user saat login
  setUser(userData, tokenData = '') {
    this.user = userData
    if (tokenData) {
      this.token = tokenData
      localStorage.setItem('token', tokenData)
      localStorage.setItem('velora_token', tokenData)
    }
    safeWrite('velora_user', userData)
  },

  // Method reaktif untuk hapus user saat logout
  clearUser() {
    this.user = null
    this.token = ''
    localStorage.removeItem('velora_user')
    localStorage.removeItem('velora_token')
    localStorage.removeItem('token')
  }
})

export const roomStatusInfo = {
  tersedia: { label: 'Tersedia', className: 'status-tersedia', bookable: true },
  terisi: { label: 'Sedang Terisi', className: 'status-terisi', bookable: false },
  perbaikan: { label: 'Dalam Perbaikan', className: 'status-perbaikan', bookable: false },
  dibersihkan: { label: 'Sedang Dibersihkan', className: 'status-dibersihkan', bookable: false },
}

export function login(email, password) {
  const found = store.users.find(u => u.email === email && u.password === password)
  if (found) {
    const userData = {
      name: found.name,
      email: found.email,
      avatar: found.avatar || defaultUserAvatar,
    }
    store.setUser(userData)
    return { ok: true }
  }
  return { ok: false, error: 'Email atau kata sandi salah. Belum punya akun? Daftar dulu.' }
}

export function register({ name, email, password, avatar }) {
  if (store.users.some(u => u.email === email)) {
    return { ok: false, error: 'Email ini sudah terdaftar. Coba masuk saja.' }
  }
  const userData = { name, email, password, avatar: avatar || defaultUserAvatar }
  store.users.push(userData)
  safeWrite('velora_users', store.users)

  store.setUser({ name, email, avatar: userData.avatar })
  return { ok: true }
}

export function logout() {
  store.clearUser()
}

export function addBooking({ roomId, guestName, checkIn, checkOut, status_pemesanan = 'menunggu', status_pembayaran = 'menunggu_verifikasi' }) {
  const room = store.rooms.find(r => r.id === roomId)

  if (!room || room.status !== 'tersedia') {
    return { ok: false, error: 'Kamar ini sedang tidak tersedia untuk dipesan.' }
  }

  const newBooking = {
    id: store.nextBookingId++,
    roomId: room.id,
    roomName: room.name,
    guestName,
    checkIn,
    checkOut,
    status: 'pending',
    status_pemesanan,
    status_pembayaran,
    tipe_kamar: room.name,
    jumlah_total: room.price * Math.max(1, Math.ceil((new Date(checkOut) - new Date(checkIn)) / (1000 * 60 * 60 * 24))),
    kode_pemesanan: `VLR-${new Date().getFullYear()}${String(new Date().getMonth() + 1).padStart(2, '0')}${String(new Date().getDate()).padStart(2, '0')}-${String(store.nextBookingId).padStart(3, '0')}`,
    email: store.user ? store.user.email : '-',
  }

  store.bookings.unshift(newBooking)
  bookingListStore.value.unshift({
    ...newBooking,
    tanggal_check_in: checkIn,
    tanggal_check_out: checkOut,
    kode_pemesanan: newBooking.kode_pemesanan,
    jumlah_total: newBooking.jumlah_total,
    status_pemesanan,
    status_pembayaran,
  })

  room.status = 'terisi'

  return { ok: true }
}