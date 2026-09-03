import { reactive } from 'vue'

// Store sementara di memori (frontend-only).
// Nanti fungsi login/register/addBooking di bawah ini tinggal
// diganti isinya dengan pemanggilan fetch/axios ke backend.
export const store = reactive({
  user: null,     // { name, email }
  users: [],       // hasil register, sementara di memori
  rooms: [
    { id: 1, name: 'Kamar Rimba', desc: 'Kamar tenang menghadap taman tropis dengan tempat tidur kayu jati dan balkon pribadi.', capacity: 2, price: 650000 },
    { id: 2, name: 'Suite Pesisir', desc: 'Suite luas berpemandangan laut, area duduk terpisah, dan kamar mandi terbuka.', capacity: 3, price: 1250000 },
    { id: 3, name: 'Kamar Sawah', desc: 'Kamar hangat dengan jendela besar menghadap hamparan sawah, cocok untuk pasangan.', capacity: 2, price: 480000 },
    { id: 4, name: 'Villa Batu', desc: 'Villa satu lantai dengan kolam pribadi dan dapur kecil, ideal untuk keluarga.', capacity: 4, price: 2100000 },
  ],
  bookings: [],
  nextBookingId: 1,
})

export function login(email, password) {
  const found = store.users.find(u => u.email === email && u.password === password)
  if (found) {
    store.user = { name: found.name, email: found.email }
    return { ok: true }
  }
  return { ok: false, error: 'Email atau kata sandi salah. Belum punya akun? Daftar dulu.' }
}

export function register({ name, email, password }) {
  if (store.users.some(u => u.email === email)) {
    return { ok: false, error: 'Email ini sudah terdaftar. Coba masuk saja.' }
  }
  store.users.push({ name, email, password })
  store.user = { name, email }
  return { ok: true }
}

export function logout() {
  store.user = null
}

export function addBooking({ roomId, guestName, checkIn, checkOut }) {
  const room = store.rooms.find(r => r.id === roomId)
  store.bookings.unshift({
    id: store.nextBookingId++,
    roomName: room.name,
    guestName,
    checkIn,
    checkOut,
    status: 'pending',
    email: store.user ? store.user.email : '-',
  })
}