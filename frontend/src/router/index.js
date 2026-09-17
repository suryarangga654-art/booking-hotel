import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../../Register.vue'
import RoomsView from '../views/RoomsView.vue'
import BookingView from '../views/BookingView.vue'
import TulisUlasan from '../views/TulisUlasan.vue'
import MyBookings from '../views/MyBookings.vue'
import ProfileView from '../views/ProfileView.vue'

// Import Halaman Admin / Resepsionis
import AdminDashboard from '../views/admin/AdminDashboard.vue'
import AdminKamar from '../views/admin/AdminKamar.vue'
import AdminPemesanan from '../views/admin/AdminPemesanan.vue'
import AdminTipeKamar from '../views/admin/AdminTipeKamar.vue'
import AdminPembayaran from '../views/admin/AdminPembayaran.vue'
import AdminUlasan from '../views/admin/AdminUlasan.vue'
import AdminPengguna from '../views/admin/AdminPengguna.vue'

const routes = [
  /* =========================================================
     RUTE PUBLIK & TAMU
     ========================================================= */
  { 
    path: '/', 
    name: 'home', 
    component: Home 
  },
  { 
    path: '/login', 
    name: 'login', 
    component: Login,
    meta: { hideNavbar: true } // Sembunyikan Navbar di Login
  },
  { 
    path: '/register', 
    name: 'register', 
    component: Register,
    meta: { hideNavbar: true } // Sembunyikan Navbar di Register
  },
  { 
    path: '/rooms', 
    name: 'rooms', 
    component: RoomsView 
  },
  { 
    path: '/booking/:id', 
    name: 'booking', 
    component: BookingView, 
    meta: { requiresAuth: true, allowedRoles: ['tamu', 'admin', 'resepsionis'] } 
  },
  { 
    path: '/ulasan/:id', 
    name: 'ulasan', 
    component: TulisUlasan, 
    meta: { requiresAuth: true, allowedRoles: ['tamu', 'admin', 'resepsionis'] } 
  },
  { 
    path: '/my-bookings', 
    name: 'my-bookings', 
    component: MyBookings, 
    meta: { requiresAuth: true, allowedRoles: ['tamu', 'admin', 'resepsionis'] } 
  },
  { 
    path: '/profile', 
    name: 'profile', 
    component: ProfileView, 
    meta: { requiresAuth: true, allowedRoles: ['tamu', 'admin', 'resepsionis'] } 
  },
  {
    path: '/notifications',
    redirect: '/my-bookings',
  },

  /* =========================================================
     RUTE ADMIN / RESEPSIONIS (Navbar Utama Disembunyikan)
     ========================================================= */
  {
    path: '/admin',
    name: 'admin-dashboard',
    component: AdminDashboard,
    meta: { requiresAuth: true, allowedRoles: ['admin', 'resepsionis'], hideNavbar: true },
  },
  {
    path: '/admin/kamar',
    name: 'admin-kamar',
    component: AdminKamar,
    meta: { requiresAuth: true, allowedRoles: ['admin', 'resepsionis'], hideNavbar: true },
  },
  {
    path: '/admin/pemesanan',
    name: 'admin-pemesanan',
    component: AdminPemesanan,
    meta: { requiresAuth: true, allowedRoles: ['admin', 'resepsionis'], hideNavbar: true },
  },
  {
    path: '/admin/tipe-kamar',
    name: 'admin-tipe-kamar',
    component: AdminTipeKamar,
    meta: { requiresAuth: true, allowedRoles: ['admin', 'resepsionis'], hideNavbar: true },
  },
  {
    path: '/admin/pembayaran',
    name: 'admin-pembayaran',
    component: AdminPembayaran,
    meta: { requiresAuth: true, allowedRoles: ['admin', 'resepsionis'], hideNavbar: true },
  },
  {
    path: '/admin/ulasan',
    name: 'admin-ulasan',
    component: AdminUlasan,
    meta: { requiresAuth: true, allowedRoles: ['admin', 'resepsionis'], hideNavbar: true },
  },
  {
    path: '/admin/pengguna',
    name: 'admin-pengguna',
    component: AdminPengguna,
    meta: { requiresAuth: true, allowedRoles: ['admin'], hideNavbar: true }, // Khusus Admin
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

/* =========================================================
   NAVIGATION GUARD (Proteksi Rute berdasarkan Auth & Role)
   ========================================================= */
router.beforeEach((to, from, next) => {
  const rawUser = localStorage.getItem('velora_user')
  const currentUser = rawUser ? JSON.parse(rawUser) : null
  const token = localStorage.getItem('token') || localStorage.getItem('velora_token')
  const currentRole = String(currentUser?.role || '').trim().toLowerCase()

  // 1. Cek apakah halaman butuh autentikasi
  if (to.meta.requiresAuth) {
    if (!token || !currentUser) {
      next('/login')
      return
    }

    // 2. Cek hak akses role
    const allowedRoles = to.meta.allowedRoles || []
    if (allowedRoles.length && !allowedRoles.includes(currentRole)) {
      if (currentRole === 'admin' || currentRole === 'resepsionis') {
        next('/admin')
      } else {
        next('/')
      }
      return
    }
  }

  // 3. Jika sudah login tapi mencoba membuka halaman /login atau /register
  if ((to.name === 'login' || to.name === 'register') && token && currentUser) {
    if (currentRole === 'admin' || currentRole === 'resepsionis') {
      next('/admin')
    } else {
      next('/')
    }
    return
  }

  next()
})

export default router