<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const saving = ref(false)
const toast = ref('')

const userList = ref([])
const searchQuery = ref('')
const showModal = ref(false)
const editingUserId = ref(null)

const userForm = ref({
  nama: '',
  email: '',
  telepon: '',
  role: 'tamu'
})

const menuItems = [
  { key: 'dashboard', label: 'Dashboard', icon: '📊', to: '/admin' },
  { key: 'pemesanan', label: 'Pemesanan', icon: '📋', to: '/admin/pemesanan' },
  { key: 'kamar', label: 'Kamar', icon: '🛏️', to: '/admin/kamar' },
  { key: 'tipe-kamar', label: 'Tipe Kamar', icon: '🏷️', to: '/admin/tipe-kamar' },
  { key: 'pembayaran', label: 'Pembayaran', icon: '💳', to: '/admin/pembayaran' },
  { key: 'ulasan', label: 'Ulasan', icon: '⭐', to: '/admin/ulasan' },
  { key: 'pengguna', label: 'Pengguna', icon: '👥', to: '/admin/pengguna' },
]

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('velora_token')
  localStorage.removeItem('velora_user')
  router.push('/login')
}

async function fetchData() {
  loading.value = true
  try {
    const res = await api.get('/admin/users')
    console.log('Response BE:', res.data) // Debugging struktur respons backend
    
    let rawData = []

    // Mendukung berbagai kemungkinan format JSON dari Laravel (Paginate / Collection / Array)
    if (Array.isArray(res.data)) {
      rawData = res.data
    } else if (Array.isArray(res.data?.data)) {
      rawData = res.data.data
    } else if (Array.isArray(res.data?.data?.data)) {
      // Format jika Backend memakai Paginate dalam Resource
      rawData = res.data.data.data
    } else if (res.data?.users && Array.isArray(res.data.users)) {
      rawData = res.data.users
    }

    // Map data murni dari backend tanpa mengalihkan ke dummy data
    userList.value = rawData.map(u => ({
      id: u.id,
      nama: u.name || u.nama || u.username || 'Tanpa Nama',
      email: u.email || '-',
      telepon: u.phone || u.telepon || u.no_hp || '-',
      role: u.role || 'tamu'
    }))

  } catch (err) {
    console.error('Gagal mengambil data dari BE:', err)
    showToast('Gagal memuat data dari server.')
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

function showToast(msg) {
  toast.value = msg
  setTimeout(() => (toast.value = ''), 2800)
}

const filteredUsers = computed(() => {
  if (!searchQuery.value.trim()) return userList.value
  const q = searchQuery.value.toLowerCase()
  return userList.value.filter(
    (u) => (u.nama || '').toLowerCase().includes(q) || (u.email || '').toLowerCase().includes(q)
  )
})

function openAddModal() {
  editingUserId.value = null
  userForm.value = { nama: '', email: '', telepon: '', role: 'tamu' }
  showModal.value = true
}

function openEditModal(u) {
  editingUserId.value = u.id
  userForm.value = { 
    nama: u.nama || '',
    email: u.email || '',
    telepon: u.telepon !== '-' ? u.telepon : '',
    role: u.role || 'tamu'
  }
  showModal.value = true
}

async function submitUser() {
  if (!userForm.value.nama || !userForm.value.email) {
    alert('Nama dan Email wajib diisi!')
    return
  }

  saving.value = true
  const payload = {
    name: userForm.value.nama,
    nama: userForm.value.nama,
    email: userForm.value.email,
    phone: userForm.value.telepon,
    telepon: userForm.value.telepon,
    role: userForm.value.role
  }

  try {
    if (editingUserId.value) {
      await api.put(`/admin/users/${editingUserId.value}`, payload)
    } else {
      await api.post('/admin/users', payload)
    }
    await fetchData() // Refresh data murni dari DB setelah simpan
    showModal.value = false
    showToast('Data pengguna berhasil disimpan.')
  } catch (e) {
    console.error('Gagal menyimpan:', e)
    const errorMsg = e.response?.data?.message || 'Gagal menyimpan data ke backend.'
    alert(errorMsg)
  } finally {
    saving.value = false
  }
}

async function deleteUser(id) {
  if (!confirm('Hapus pengguna ini?')) return
  try {
    await api.delete(`/admin/users/${id}`)
    await fetchData() // Refresh data murni dari DB setelah hapus
    showToast('Pengguna berhasil dihapus.')
  } catch (e) {
    console.error('Gagal menghapus:', e)
    const errorMsg = e.response?.data?.message || 'Gagal menghapus pengguna.'
    alert(errorMsg)
  }
}
</script>

<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <span class="logo-mark"></span>
        <span>VELORA</span>
      </div>

      <nav class="sidebar-nav">
        <router-link
          v-for="item in menuItems"
          :key="item.key"
          :to="item.to"
          class="nav-item"
          :class="{ active: route.path === item.to }"
        >
          <span class="nav-icon">{{ item.icon }}</span>
          <span>{{ item.label }}</span>
        </router-link>
      </nav>

      <button class="logout-btn" @click="logout">
        <span class="nav-icon">🚪</span>
        <span>Keluar</span>
      </button>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <h1>Kelola Pengguna</h1>
        <p>Manajemen akun staf resort & tamu terdaftar</p>
      </header>

      <div v-if="loading" class="loading-state">Memuat pengguna...</div>

      <template v-else>
        <section class="panel">
          <div class="panel-head">
            <input v-model="searchQuery" type="text" placeholder="Cari nama / email..." class="search-input" />
            <button class="btn-primary" @click="openAddModal">+ Tambah Pengguna</button>
          </div>

          <div class="table-wrapper">
            <table class="data-table">
              <thead>
                <tr>
                  <th>Nama</th>
                  <th>Email</th>
                  <th>No. Telepon</th>
                  <th>Role</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="filteredUsers.length === 0">
                  <td colspan="5" class="empty-state">
                    Tidak ada data pengguna ditemukan.
                  </td>
                </tr>
                <tr v-for="u in filteredUsers" :key="u.id">
                  <td class="font-bold">{{ u.nama }}</td>
                  <td>{{ u.email }}</td>
                  <td>{{ u.telepon }}</td>
                  <td>
                    <span class="role-badge" :class="u.role">{{ u.role }}</span>
                  </td>
                  <td>
                    <button class="btn-link" @click="openEditModal(u)">Edit</button>
                    <button class="btn-link danger" @click="deleteUser(u.id)">Hapus</button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </template>
    </main>

    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal">
        <h3>{{ editingUserId ? 'Edit Pengguna' : 'Tambah Pengguna' }}</h3>

        <div class="fieldset">
          <label>Nama Lengkap</label>
          <input v-model="userForm.nama" type="text" placeholder="John Doe" />
        </div>

        <div class="fieldset">
          <label>Email</label>
          <input v-model="userForm.email" type="email" placeholder="john@example.com" />
        </div>

        <div class="fieldset">
          <label>No. Telepon</label>
          <input v-model="userForm.telepon" type="text" placeholder="08123456789" />
        </div>

        <div class="fieldset">
          <label>Role Akses</label>
          <select v-model="userForm.role">
            <option value="tamu">Tamu</option>
            <option value="resepsionis">Resepsionis</option>
            <option value="admin">Admin</option>
          </select>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel" @click="showModal = false">Batal</button>
          <button class="btn-primary" :disabled="saving" @click="submitUser">
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="toast" class="toast">{{ toast }}</div>
  </div>
</template>

<style scoped>
.admin-layout { display: flex; min-height: 100vh; background: #f8fafc; font-family: system-ui, -apple-system, sans-serif; }
.sidebar { width: 250px; background: #0f172a; color: #f8fafc; display: flex; flex-direction: column; padding: 28px 18px; position: sticky; top: 0; height: 100vh; flex-shrink: 0; }
.sidebar-brand { display: flex; align-items: center; gap: 10px; padding: 0 10px 28px; margin-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); font-family: Georgia, serif; font-size: 19px; font-weight: 700; }
.logo-mark { width: 24px; height: 24px; border-radius: 50%; background: conic-gradient(from 200deg, #0284c7, #f8fafc, #0284c7); }
.sidebar-nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
.nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 8px; color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 500; }
.nav-item.active, .nav-item:hover { background: #0284c7; color: #fff; }
.logout-btn { display: flex; align-items: center; gap: 12px; padding: 11px 14px; background: none; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; color: #f87171; cursor: pointer; }
.main-content { flex: 1; padding: 32px 40px; min-width: 0; }
.topbar h1 { margin: 0 0 4px; color: #0f172a; font-family: Georgia, serif; font-size: 26px; }
.topbar p { margin: 0 0 24px; color: #64748b; font-size: 14px; }
.loading-state { text-align: center; color: #64748b; padding: 40px; }
.empty-state { text-align: center; color: #64748b; padding: 24px; }
.panel { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 15px rgba(15,23,42,0.04); }
.panel-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 20px; }
.search-input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; outline: none; }
.btn-primary { padding: 9px 16px; background: #0f172a; color: #fff; border: none; border-radius: 6px; font-size: 13.5px; font-weight: 600; cursor: pointer; }
.table-wrapper { overflow-x: auto; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
.data-table th, .data-table td { padding: 12px 14px; border-bottom: 1px solid #f1f5f9; }
.data-table th { background: #f8fafc; color: #475569; font-weight: 600; }
.font-bold { font-weight: 600; color: #0f172a; }
.role-badge { padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: capitalize; }
.role-badge.admin { background: #e0f2fe; color: #0369a1; }
.role-badge.resepsionis { background: #fef3c7; color: #b45309; }
.role-badge.tamu { background: #f1f5f9; color: #475569; }
.btn-link { background: none; border: none; color: #0284c7; font-size: 13px; font-weight: 600; cursor: pointer; margin-right: 10px; }
.btn-link.danger { color: #dc2626; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 100; backdrop-filter: blur(4px); }
.modal { background: #fff; border-radius: 12px; padding: 24px; width: 100%; max-width: 420px; }
.modal h3 { margin: 0 0 16px; font-family: Georgia, serif; color: #0f172a; }
.fieldset { margin-bottom: 14px; display: flex; flex-direction: column; gap: 6px; }
.fieldset label { font-size: 13px; font-weight: 600; color: #334155; }
.fieldset input, .fieldset select { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 14px; outline: none; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.btn-cancel { padding: 9px 16px; background: #e2e8f0; color: #334155; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
.toast { position: fixed; bottom: 24px; right: 24px; background: #10b981; color: #fff; padding: 12px 20px; border-radius: 8px; font-size: 14px; z-index: 200; }
</style>