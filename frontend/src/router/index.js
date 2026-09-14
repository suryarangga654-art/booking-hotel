import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Login from '../views/Login.vue'
import Register from '../views/Register.vue'
import RoomsView from '../views/RoomsView.vue'
import BookingView from '../views/BookingView.vue'
import AdminDashboard from '../views/admin/AdminDashboard.vue'
import AdminKamar from '../views/admin/AdminKamar.vue'
import AdminPemesanan from '../views/admin/AdminPemesanan.vue'
import AdminTipeKamar from '../views/admin/AdminTipeKamar.vue'
import AdminPembayaran from '../views/admin/AdminPembayaran.vue'  
import AdminUlasan from '../views/admin/AdminUlasan.vue'
import AdminPengguna from '../views/admin/AdminPengguna.vue'

const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/login', name: 'login', component: Login },
  { path: '/register', name: 'register', component: Register },
  { path: '/rooms', name: 'rooms', component: RoomsView },
  { path: '/booking/:id', name: 'booking', component: BookingView },

  {
    path: '/admin',
    name: 'admin-dashboard',
    component: AdminDashboard,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/kamar',
    name: 'admin-kamar',
    component: AdminKamar,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/pemesanan',
    name: 'admin-pemesanan',
    component: AdminPemesanan,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/tipe-kamar',
    name: 'admin-tipe-kamar',
    component: AdminTipeKamar,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/pembayaran',
    name: 'admin-pembayaran',
    component: AdminPembayaran,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/ulasan',
    name: 'admin-ulasan',
    component: AdminUlasan,
    meta: { requiresAdmin: true },
  },
  {
    path: '/admin/pengguna',
    name: 'admin-pengguna',
    component: AdminPengguna,
    meta: { requiresAdmin: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior() {
    return { top: 0 }
  },
})

// Proteksi sederhana: cek token sebelum masuk halaman admin
router.beforeEach((to, from, next) => {
  if (to.meta.requiresAdmin) {
    const token = localStorage.getItem('token')
    if (!token) {
      next('/login')
      return
    }
  }
  next()
})

export default router