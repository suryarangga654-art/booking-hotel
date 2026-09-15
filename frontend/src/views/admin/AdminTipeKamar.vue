<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const saving = ref(false)
const toast = ref('')

const tipeKamarList = ref([])
const kamarList = ref([])

// Search & Filter
const searchQuery = ref('')
const filterTipe = ref('')

// ============================================
// FETCH DATA
// ============================================
// ============================================
// FETCH DATA (Perbaikan Parsing Response)
// ============================================
async function fetchData() {
  loading.value = true
  try {
    const [tipeRes, kamarRes] = await Promise.all([
      api.get('/admin/tipe-kamar'),
      api.get('/admin/kamar')
    ])

    // Mengambil data murni jika dibungkus oleh Laravel
    let rawTipe = tipeRes.data?.data || tipeRes.data
    let rawKamar = kamarRes.data?.data || kamarRes.data

    // Jika Laravel mengirimkan Object berformat { '0': {...}, '1': {...} }
    if (rawTipe && typeof rawTipe === 'object' && !Array.isArray(rawTipe)) {
      rawTipe = Object.values(rawTipe)
    }
    if (rawKamar && typeof rawKamar === 'object' && !Array.isArray(rawKamar)) {
      rawKamar = Object.values(rawKamar)
    }

    // Memastikan nilai akhir SELALU Array
    tipeKamarList.value = Array.isArray(rawTipe) ? rawTipe : []
    kamarList.value = Array.isArray(rawKamar) ? rawKamar : []
  } catch (error) {
    console.error('Gagal memuat data kamar:', error)
    loadDummyData()
  } finally {
    loading.value = false
  }
}

// ============================================
// HELPER (Perbaikan Proteksi Array)
// ============================================
function tipeKamarNama(tipeId) {
  // Cek jika tipeKamarList bukan Array atau kosong
  if (!Array.isArray(tipeKamarList.value) || tipeKamarList.value.length === 0) {
    return 'Tidak Diketahui'
  }
  
  const tipe = tipeKamarList.value.find((t) => Number(t.id) === Number(tipeId))
  return tipe ? tipe.nama : 'Tidak Diketahui'
}

function showToast(msg) {
  toast.value = msg
  setTimeout(() => (toast.value = ''), 2800)
}

const filteredKamar = computed(() => {
  if (!Array.isArray(kamarList.value)) return []
  return kamarList.value.filter((k) => {
    const matchQuery = !searchQuery.value.trim() || k.nomor_kamar.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchTipe = !filterTipe.value || k.tipe_kamar_id === Number(filterTipe.value)
    return matchQuery && matchTipe
  })
})

// ============================================
// MODAL TAMBAH / EDIT
// ============================================
const showModal = ref(false)
const editingId = ref(null)
const form = ref({
  nomor_kamar: '',
  tipe_kamar_id: '',
  status: 'Tersedia'
})

function openAdd() {
  editingId.value = null
  form.value = { nomor_kamar: '', tipe_kamar_id: tipeKamarList.value[0]?.id || '', status: 'Tersedia' }
  showModal.value = true
}

function openEdit(item) {
  editingId.value = item.id
  form.value = { ...item }
  showModal.value = true
}

async function submitForm() {
  if (!form.value.nomor_kamar || !form.value.tipe_kamar_id) return
  saving.value = true
  try {
    if (editingId.value) {
      await api.put(`/admin/kamar/${editingId.value}`, form.value)
      const idx = kamarList.value.findIndex((k) => k.id === editingId.value)
      if (idx !== -1) kamarList.value[idx] = { ...form.value, id: editingId.value }
    } else {
      const res = await api.post('/admin/kamar', form.value)
      const newKamar = res.data?.data || res.data
      kamarList.value.push(newKamar)
    }
    showToast('Data kamar berhasil disimpan.')
  } catch (error) {
    console.error('Gagal menyimpan di server:', error)
    if (editingId.value) {
      const idx = kamarList.value.findIndex((k) => k.id === editingId.value)
      if (idx !== -1) kamarList.value[idx] = { ...form.value, id: editingId.value }
    } else {
      const newId = Math.max(0, ...kamarList.value.map((k) => k.id || 0)) + 1
      kamarList.value.push({ ...form.value, id: newId })
    }
    showToast('Data kamar disimpan (lokal).')
  } finally {
    saving.value = false
    showModal.value = false
  }
}

async function deleteKamar(id) {
  if (!confirm('Hapus unit kamar ini?')) return
  try {
    await api.delete(`/admin/kamar/${id}`)
  } catch (error) {
    console.error('Gagal menghapus di server:', error)
  }
  kamarList.value = kamarList.value.filter((k) => k.id !== id)
  showToast('Unit kamar dihapus.')
}

function logout() {
  localStorage.removeItem('token')
  router.push('/login')
}

const menuItems = [
  { key: 'dashboard', label: 'Dashboard', icon: '📊', to: '/admin' },
  { key: 'pemesanan', label: 'Pemesanan', icon: '📋', to: '/admin/pemesanan' },
  { key: 'kamar', label: 'Kamar', icon: '🛏️', to: '/admin/kamar' },
  { key: 'tipe-kamar', label: 'Tipe Kamar', icon: '🏷️', to: '/admin/tipe-kamar' },
  { key: 'pembayaran', label: 'Pembayaran', icon: '💳', to: '/admin/pembayaran' },
  { key: 'ulasan', label: 'Ulasan', icon: '⭐', to: '/admin/ulasan' },
  { key: 'pengguna', label: 'Pengguna', icon: '👥', to: '/admin/pengguna' },
]
</script>

<template>
  <div class="admin-layout">
    <!-- SIDEBAR -->
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
          :class="{ active: route.path === item.to || route.path.startsWith(item.to + '/') }"
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

    <!-- MAIN CONTENT -->
    <main class="main-content">
      <header class="topbar">
        <div>
          <h1>Kelola Unit Kamar</h1>
          <p>Atur penomoran dan ketersediaan fisik kamar</p>
        </div>
      </header>

      <div v-if="loading" class="loading-state">
        Memuat data kamar...
      </div>

      <template v-else>
        <section class="panel">
          <div class="panel-head">
            <h2>Daftar Unit</h2>
            <div class="panel-actions">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari nomor kamar..."
                class="search-input"
              />
              <button class="btn-primary" @click="openAdd">+ Tambah Kamar</button>
            </div>
          </div>

          <table class="data-table">
            <thead>
              <tr>
                <th>No. Kamar</th>
                <th>Tipe Kamar</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="k in filteredKamar" :key="k.id">
                <td><strong>{{ k.nomor_kamar }}</strong></td>
                <td>{{ tipeKamarNama(k.tipe_kamar_id) }}</td>
                <td>
                  <span class="status-badge" :class="k.status?.toLowerCase()">
                    {{ k.status || 'Tersedia' }}
                  </span>
                </td>
                <td>
                  <button class="btn-link" @click="openEdit(k)">Edit</button>
                  <button class="btn-link danger" @click="deleteKamar(k.id)">Hapus</button>
                </td>
              </tr>
              <tr v-if="filteredKamar.length === 0">
                <td colspan="4" class="empty-state">Data kamar tidak ditemukan.</td>
              </tr>
            </tbody>
          </table>
        </section>
      </template>
    </main>

    <!-- MODAL FORM -->
    <div v-if="showModal" class="modal-backdrop" @click.self="showModal = false">
      <div class="modal">
        <h3>{{ editingId ? 'Edit Kamar' : 'Tambah Kamar' }}</h3>
        <div class="fieldset">
          <label>Nomor Kamar</label>
          <input v-model="form.nomor_kamar" type="text" placeholder="Contoh: 101" />
        </div>
        <div class="fieldset">
          <label>Tipe Kamar</label>
          <select v-model="form.tipe_kamar_id">
            <option v-for="t in tipeKamarList" :key="t.id" :value="t.id">
              {{ t.nama }}
            </option>
          </select>
        </div>
        <div class="modal-actions">
          <button class="btn-cancel" @click="showModal = false">Batal</button>
          <button class="btn-primary" :disabled="saving" @click="submitForm">
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <!-- TOAST -->
    <div v-if="toast" class="toast">{{ toast }}</div>
  </div>
</template>

<style scoped>
* { box-sizing: border-box; }
.admin-layout { display: flex; min-height: 100vh; background: #f8fafc; font-family: system-ui, sans-serif; }
.sidebar { width: 250px; background: #0f172a; color: #f8fafc; display: flex; flex-direction: column; padding: 28px 18px; position: sticky; top: 0; height: 100vh; }
.sidebar-brand { display: flex; align-items: center; gap: 10px; padding: 0 10px 28px; margin-bottom: 20px; border-bottom: 1px solid rgba(255, 255, 255, 0.1); font-family: Georgia, serif; font-size: 19px; font-weight: 700; }
.logo-mark { width: 24px; height: 24px; border-radius: 50%; background: conic-gradient(from 200deg, #0284c7, #f8fafc, #0284c7); }
.sidebar-nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
.nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 8px; color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 500; }
.nav-item.active { background: #0284c7; color: #ffffff; }
.logout-btn { display: flex; align-items: center; gap: 12px; padding: 11px 14px; background: none; border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 8px; color: #f87171; font-weight: 600; cursor: pointer; }
.main-content { flex: 1; padding: 32px 40px; }
.topbar h1 { margin: 0 0 4px; color: #0f172a; font-family: Georgia, serif; font-size: 26px; }
.topbar p { margin: 0 0 24px; color: #64748b; font-size: 14px; }
.loading-state { padding: 60px 0; text-align: center; color: #64748b; }
.panel { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; }
.panel-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
.panel-actions { display: flex; gap: 10px; }
.search-input { padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; }
.btn-primary { padding: 9px 16px; background: #0f172a; color: #ffffff; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
.data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
.data-table th, .data-table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; }
.data-table th { background: #f8fafc; color: #475569; font-weight: 600; }
.btn-link { background: none; border: none; color: #0284c7; font-weight: 600; cursor: pointer; margin-right: 10px; }
.btn-link.danger { color: #dc2626; }
.empty-state { text-align: center; color: #94a3b8; padding: 30px 0; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); display: flex; align-items: center; justify-content: center; z-index: 100; }
.modal { background: #ffffff; border-radius: 12px; padding: 28px; width: 100%; max-width: 400px; }
.fieldset { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
.fieldset input, .fieldset select { padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px; }
.modal-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 20px; }
.btn-cancel { padding: 9px 16px; background: #e2e8f0; border: none; border-radius: 6px; cursor: pointer; }
.toast { position: fixed; bottom: 24px; right: 24px; background: #10b981; color: #ffffff; padding: 14px 24px; border-radius: 8px; z-index: 200; }
</style>