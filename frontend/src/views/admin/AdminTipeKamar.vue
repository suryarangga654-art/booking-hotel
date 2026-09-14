<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const toast = ref('')

const tipeKamarList = ref([])
const kamarList = ref([])

// Search
const searchQuery = ref('')

// ============================================
// FETCH DATA
// ============================================
async function fetchData() {
  loading.value = true
  try {
    const [tipeRes, kamarRes] = await Promise.all([
      api.get('/admin/tipe-kamar'),
      api.get('/admin/kamar')
    ])
    tipeKamarList.value = tipeRes.data
    kamarList.value = kamarRes.data
  } catch (error) {
    console.error('Gagal memuat data tipe kamar:', error)
    loadDummyData()
  } finally {
    loading.value = false
  }
}

function loadDummyData() {
  tipeKamarList.value = [
    { id: 1, nama: 'Kamar Rimba', harga_dasar: 650000, kapasitas: 2, deskripsi: 'Kamar tenang menghadap taman tropis.' },
    { id: 2, nama: 'Suite Pesisir', harga_dasar: 1250000, kapasitas: 3, deskripsi: 'Suite luas berpemandangan laut.' },
    { id: 3, nama: 'Kamar Sawah', harga_dasar: 480000, kapasitas: 2, deskripsi: 'Kamar hangat menghadap sawah.' },
    { id: 4, nama: 'Villa Batu', harga_dasar: 2100000, kapasitas: 4, deskripsi: 'Villa satu lantai dengan kolam pribadi.' },
  ]

  kamarList.value = [
    { id: 1, nomor_kamar: '101', tipe_kamar_id: 1 },
    { id: 2, nomor_kamar: '102', tipe_kamar_id: 1 },
    { id: 3, nomor_kamar: '201', tipe_kamar_id: 2 },
  ]
}

onMounted(fetchData)

// ============================================
// HELPER FUNCTIONS
// ============================================
function formatRupiah(n) {
  return 'Rp' + Number(n).toLocaleString('id-ID')
}

function showToast(msg) {
  toast.value = msg
  setTimeout(() => (toast.value = ''), 2800)
}

function getJumlahUnit(tipeId) {
  return kamarList.value.filter((k) => k.tipe_kamar_id === tipeId).length
}

const filteredTipeKamar = computed(() => {
  if (!searchQuery.value.trim()) return tipeKamarList.value
  const query = searchQuery.value.toLowerCase()
  return tipeKamarList.value.filter(
    (t) =>
      t.nama.toLowerCase().includes(query) ||
      (t.deskripsi && t.deskripsi.toLowerCase().includes(query))
  )
})

// ============================================
// MODAL: TAMBAH / EDIT TIPE KAMAR
// ============================================
const showTipeModal = ref(false)
const editingTipeId = ref(null)
const tipeForm = ref({
  nama: '',
  harga_dasar: 0,
  kapasitas: 2,
  deskripsi: '',
})

function openAddTipe() {
  editingTipeId.value = null
  tipeForm.value = { nama: '', harga_dasar: 0, kapasitas: 2, deskripsi: '' }
  showTipeModal.value = true
}

function openEditTipe(tipe) {
  editingTipeId.value = tipe.id
  tipeForm.value = { ...tipe }
  showTipeModal.value = true
}

async function submitTipe() {
  if (!tipeForm.value.nama || !tipeForm.value.harga_dasar) return
  saving.value = true
  try {
    if (editingTipeId.value) {
      await api.put(`/admin/tipe-kamar/${editingTipeId.value}`, tipeForm.value)
      const idx = tipeKamarList.value.findIndex((t) => t.id === editingTipeId.value)
      if (idx !== -1) tipeKamarList.value[idx] = { ...tipeForm.value, id: editingTipeId.value }
    } else {
      const res = await api.post('/admin/tipe-kamar', tipeForm.value)
      tipeKamarList.value.push(res.data)
    }
    showToast('Tipe kamar tersimpan.')
  } catch (error) {
    if (editingTipeId.value) {
      const idx = tipeKamarList.value.findIndex((t) => t.id === editingTipeId.value)
      if (idx !== -1) tipeKamarList.value[idx] = { ...tipeForm.value, id: editingTipeId.value }
    } else {
      const newId = Math.max(0, ...tipeKamarList.value.map((t) => t.id)) + 1
      tipeKamarList.value.push({ ...tipeForm.value, id: newId })
    }
    showToast('Tipe kamar tersimpan (lokal).')
  } finally {
    saving.value = false
    showTipeModal.value = false
  }
}

async function deleteTipe(id) {
  const dipakai = kamarList.value.some((k) => k.tipe_kamar_id === id)
  if (dipakai) {
    alert('Tipe kamar ini masih digunakan oleh kamar tertentu. Ubah/hapus unit kamarnya dulu.')
    return
  }
  if (!confirm('Hapus tipe kamar ini?')) return
  try {
    await api.delete(`/admin/tipe-kamar/${id}`)
  } catch (error) {
    // Fallback delete lokal
  }
  tipeKamarList.value = tipeKamarList.value.filter((t) => t.id !== id)
  showToast('Tipe kamar dihapus.')
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
          :class="{ active: item.key === 'tipe-kamar' }"
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
          <h1>Kelola Tipe Kamar</h1>
          <p>Atur kategori, harga dasar, dan kapasitas kamar Velora Resort</p>
        </div>
      </header>

      <div v-if="loading" class="loading-state">
        Memuat data tipe kamar...
      </div>

      <template v-else>
        <section class="panel">
          <div class="panel-head">
            <h2>Kategori & Spesifikasi</h2>
            <div class="panel-actions">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Cari tipe kamar..."
                class="search-input"
              />
              <button class="btn-primary" @click="openAddTipe">+ Tambah Tipe</button>
            </div>
          </div>

          <!-- GRID TIPE KAMAR -->
          <div class="tipe-grid">
            <div class="tipe-card" v-for="t in filteredTipeKamar" :key="t.id">
              <div class="tipe-card-head">
                <h3>{{ t.nama }}</h3>
                <span class="tipe-price">{{ formatRupiah(t.harga_dasar) }}<small>/malam</small></span>
              </div>
              <p class="tipe-desc">{{ t.deskripsi || '-' }}</p>
              <div class="tipe-meta">
                <span>👤 Maks {{ t.kapasitas }} tamu</span>
                <span>🚪 {{ getJumlahUnit(t.id) }} unit kamar</span>
              </div>
              <div class="tipe-actions">
                <button class="btn-link" @click="openEditTipe(t)">Edit</button>
                <button class="btn-link danger" @click="deleteTipe(t.id)">Hapus</button>
              </div>
            </div>

            <div v-if="filteredTipeKamar.length === 0" class="empty-state">
              Tidak ada data tipe kamar yang cocok.
            </div>
          </div>
        </section>
      </template>

    </main>

    <!-- MODAL TAMBAH/EDIT -->
    <div v-if="showTipeModal" class="modal-backdrop" @click.self="showTipeModal = false">
      <div class="modal">
        <h3>{{ editingTipeId ? 'Edit Tipe Kamar' : 'Tambah Tipe Kamar' }}</h3>

        <div class="fieldset">
          <label>Nama Tipe</label>
          <input v-model="tipeForm.nama" type="text" placeholder="Contoh: Suite Pesisir" />
        </div>

        <div class="fieldset">
          <label>Harga Dasar / Malam (Rp)</label>
          <input v-model.number="tipeForm.harga_dasar" type="number" min="0" placeholder="1000000" />
        </div>

        <div class="fieldset">
          <label>Kapasitas Tamu</label>
          <input v-model.number="tipeForm.kapasitas" type="number" min="1" placeholder="2" />
        </div>

        <div class="fieldset">
          <label>Deskripsi</label>
          <textarea v-model="tipeForm.deskripsi" rows="3" placeholder="Deskripsi fasilitas & keunggulan tipe kamar"></textarea>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel" @click="showTipeModal = false">Batal</button>
          <button class="btn-primary" :disabled="saving" @click="submitTipe">
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
* {
  box-sizing: border-box;
}

.admin-layout {
  display: flex;
  min-height: 100vh;
  background: #f8fafc;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.sidebar {
  width: 250px;
  flex-shrink: 0;
  background: #0f172a;
  color: #f8fafc;
  display: flex;
  flex-direction: column;
  padding: 28px 18px;
  position: sticky;
  top: 0;
  height: 100vh;
}

.sidebar-brand {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 0 10px 28px;
  margin-bottom: 20px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.1);
  font-family: Georgia, serif;
  font-size: 19px;
  font-weight: 700;
  letter-spacing: 1px;
}

.logo-mark {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  background: conic-gradient(from 200deg, #0284c7, #f8fafc, #0284c7);
  flex-shrink: 0;
}

.sidebar-nav {
  display: flex;
  flex-direction: column;
  gap: 4px;
  flex: 1;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 14px;
  background: none;
  border: none;
  border-radius: 8px;
  color: #cbd5e1;
  text-decoration: none;
  font-family: inherit;
  font-size: 14px;
  font-weight: 500;
  text-align: left;
  cursor: pointer;
  transition: background 0.2s ease, color 0.2s ease;
}

.nav-item:hover {
  background: rgba(255, 255, 255, 0.06);
  color: #ffffff;
}

.nav-item.active {
  background: #0284c7;
  color: #ffffff;
}

.nav-icon {
  font-size: 16px;
  width: 18px;
  text-align: center;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 11px 14px;
  margin-top: 12px;
  background: none;
  border: 1px solid rgba(255, 255, 255, 0.15);
  border-radius: 8px;
  color: #f87171;
  font-family: inherit;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.main-content {
  flex: 1;
  padding: 32px 40px;
  min-width: 0;
}

.topbar h1 {
  margin: 0 0 4px;
  color: #0f172a;
  font-family: Georgia, serif;
  font-size: 26px;
  font-weight: 600;
}

.topbar p {
  margin: 0 0 24px;
  color: #64748b;
  font-size: 14px;
}

.loading-state {
  padding: 60px 0;
  text-align: center;
  color: #64748b;
}

.panel {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 20px;
  padding-bottom: 14px;
  border-bottom: 2px solid #f1f5f9;
}

.panel-head h2 {
  margin: 0;
  color: #0f172a;
  font-size: 17px;
  font-weight: 700;
}

.panel-actions {
  display: flex;
  align-items: center;
  gap: 10px;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 13.5px;
  outline: none;
}

.search-input:focus {
  border-color: #0284c7;
}

.btn-primary {
  padding: 9px 16px;
  background: #0f172a;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
}

.btn-primary:hover {
  background: #1e293b;
}

.btn-primary:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.tipe-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 20px;
}

.tipe-card {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 20px;
  background: #ffffff;
}

.tipe-card-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 10px;
}

.tipe-card-head h3 {
  margin: 0;
  font-size: 16px;
  color: #0f172a;
  font-family: Georgia, serif;
}

.tipe-price {
  font-size: 14px;
  font-weight: 700;
  color: #0284c7;
}

.tipe-price small {
  font-weight: 400;
  color: #94a3b8;
}

.tipe-desc {
  font-size: 13px;
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 14px;
  min-height: 38px;
}

.tipe-meta {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  font-weight: 500;
  color: #475569;
  margin-bottom: 14px;
  padding-top: 12px;
  border-top: 1px solid #f1f5f9;
}

.tipe-actions {
  display: flex;
  gap: 14px;
}

.btn-link {
  background: none;
  border: none;
  color: #0284c7;
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
  padding: 0;
}

.btn-link.danger {
  color: #dc2626;
}

.empty-state {
  grid-column: 1 / -1;
  text-align: center;
  color: #94a3b8;
  padding: 40px 0;
  font-size: 14px;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 100;
  backdrop-filter: blur(4px);
  padding: 20px;
}

.modal {
  background: #ffffff;
  border-radius: 12px;
  padding: 28px;
  width: 100%;
  max-width: 420px;
}

.modal h3 {
  margin: 0 0 20px;
  color: #0f172a;
  font-family: Georgia, serif;
}

.fieldset {
  margin-bottom: 16px;
  display: flex;
  flex-direction: column;
  gap: 6px;
}

.fieldset label {
  font-size: 13px;
  font-weight: 600;
  color: #334155;
}

.fieldset input,
.fieldset textarea {
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  outline: none;
  font-family: inherit;
  font-size: 14px;
  color: #0f172a;
}

.fieldset input:focus,
.fieldset textarea:focus {
  border-color: #0284c7;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 12px;
  margin-top: 22px;
}

.btn-cancel {
  padding: 9px 16px;
  background: #e2e8f0;
  color: #334155;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 13.5px;
  cursor: pointer;
}

.toast {
  position: fixed;
  bottom: 24px;
  right: 24px;
  background: #10b981;
  color: #ffffff;
  padding: 14px 24px;
  border-radius: 8px;
  font-weight: 500;
  font-size: 14px;
  z-index: 200;
}
</style>