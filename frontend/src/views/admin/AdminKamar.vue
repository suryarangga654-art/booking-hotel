<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()

const activeTab = ref('kamar') // 'kamar' | 'tipe-kamar' | 'layanan'
const loading = ref(true)
const saving = ref(false)
const toast = ref('')
const loadError = ref('')

const kamarList = ref([])
const tipeKamarList = ref([])
const layananList = ref([])
const fotoFiles = ref([])
const fotoUploadError = ref('')
const API_ORIGIN = (api.defaults.baseURL || '').replace(/\/api\/?$/, '')

function normalizeListPayload(payload) {
  if (Array.isArray(payload)) return payload
  if (Array.isArray(payload?.data)) return payload.data
  if (Array.isArray(payload?.items)) return payload.items
  if (payload && typeof payload === 'object') {
    const nested = payload.data ?? payload.items ?? payload.result ?? payload.results ?? []
    if (Array.isArray(nested)) return nested
  }
  return []
}

// Helper untuk ekstrak objek tunggal dari respons API
function extractObjectPayload(payload) {
  if (payload && typeof payload === 'object' && payload.data && typeof payload.data === 'object' && !Array.isArray(payload.data)) {
    return payload.data
  }
  return payload
}

// ============================================
// FETCH DATA
// ============================================
async function fetchData() {
  loading.value = true
  loadError.value = ''
  try {
    const [kamarRes, tipeRes, layananRes] = await Promise.all([
      api.get('/admin/kamar'),
      api.get('/admin/tipe-kamar'),
      api.get('/layanan-tambahan'),
    ])

    kamarList.value = normalizeListPayload(kamarRes.data)
    tipeKamarList.value = normalizeListPayload(tipeRes.data)
    layananList.value = normalizeListPayload(layananRes.data)
  } catch (error) {
    console.error('Gagal memuat data kamar:', error)
    loadError.value = error.response?.data?.message || 'Data kamar gagal dimuat dari server.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

function tipeKamarNama(id) {
  const t = tipeKamarList.value.find((t) => Number(t.id) === Number(id))
  return t ? t.nama : '-'
}

function hitungKamarTipe(id, status) {
  return kamarList.value.filter((k) => Number(k.tipe_kamar_id) === Number(id) && (!status || k.status === status)).length
}

function formatRupiah(n) {
  return 'Rp' + Number(n).toLocaleString('id-ID')
}

function showToast(msg) {
  toast.value = msg
  setTimeout(() => (toast.value = ''), 2800)
}

// ============================================
// MODAL: TAMBAH / EDIT KAMAR
// ============================================
const showKamarModal = ref(false)
const editingKamarId = ref(null)
const kamarNumberError = ref('')
const kamarForm = ref({
  nomor_kamar: '',
  lantai: 1,
  tipe_kamar_id: '',
  status: 'tersedia',
})
const isDuplicateRoomNumber = computed(() => {
  const roomNumber = String(kamarForm.value.nomor_kamar || '').trim().toLocaleLowerCase('id-ID')
  if (!roomNumber) return false
  return kamarList.value.some((room) =>
    String(room.nomor_kamar || '').trim().toLocaleLowerCase('id-ID') === roomNumber &&
    Number(room.id) !== Number(editingKamarId.value),
  )
})

function openAddKamar() {
  editingKamarId.value = null
  kamarNumberError.value = ''
  kamarForm.value = {
    nomor_kamar: '',
    lantai: 1,
    tipe_kamar_id: tipeKamarList.value.length ? tipeKamarList.value[0].id : '',
    status: 'tersedia',
  }
  showKamarModal.value = true
}

function openEditKamar(kamar) {
  editingKamarId.value = kamar.id
  kamarNumberError.value = ''
  kamarForm.value = { ...kamar }
  showKamarModal.value = true
}

async function submitKamar() {
  kamarNumberError.value = ''
  if (!kamarForm.value.nomor_kamar || !kamarForm.value.tipe_kamar_id) {
    kamarNumberError.value = 'Nomor kamar dan tipe kamar wajib diisi.'
    return
  }
  if (isDuplicateRoomNumber.value) {
    kamarNumberError.value = `Nomor kamar ${kamarForm.value.nomor_kamar.trim()} sudah digunakan.`
    return
  }

  saving.value = true
  
  // Pastikan tipe data sesuai (number)
  const payload = {
    ...kamarForm.value,
    lantai: Number(kamarForm.value.lantai),
    tipe_kamar_id: Number(kamarForm.value.tipe_kamar_id)
  }

  try {
    if (editingKamarId.value) {
      await api.put(`/admin/kamar/${editingKamarId.value}`, payload)
      const idx = kamarList.value.findIndex((k) => k.id === editingKamarId.value)
      if (idx !== -1) kamarList.value[idx] = { ...payload, id: editingKamarId.value }
    } else {
      const res = await api.post('/admin/kamar', payload)
      const savedData = extractObjectPayload(res.data)
      if (!savedData.id) throw new Error('Server tidak mengembalikan ID kamar yang tersimpan.')
      kamarList.value.push(savedData)
    }
    showToast('Data kamar tersimpan.')
    showKamarModal.value = false
  } catch (error) {
    console.error('Gagal menyimpan kamar ke API:', error)
    const numberError = error.response?.data?.errors?.nomor_kamar?.[0]
    if (numberError) {
      kamarNumberError.value = numberError
    } else {
      showToast(error.response?.data?.message || error.message || 'Gagal menyimpan data kamar ke server.')
    }
  } finally {
    saving.value = false
  }
}

async function deleteKamar(id) {
  if (!confirm('Hapus kamar ini?')) return
  try {
    await api.delete(`/admin/kamar/${id}`)
    kamarList.value = kamarList.value.filter((k) => k.id !== id)
    showToast('Kamar dihapus.')
  } catch (error) {
    showToast(error.response?.data?.message || 'Gagal menghapus kamar.')
  }
}

async function updateKamarStatus(kamar, newStatus) {
  const prevStatus = kamar.status
  kamar.status = newStatus
  try {
    await api.patch(`/admin/kamar/${kamar.id}/status`, { status: newStatus })
    showToast(`Status kamar ${kamar.nomor_kamar} diubah ke ${kamarStatusLabel[newStatus]}`)
  } catch (error) {
    console.error('Gagal update status via API:', error)
    kamar.status = prevStatus
    showToast(error.response?.data?.message || 'Gagal mengubah status kamar di server.')
  }
}

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
const fotoCount = computed(() => Number(tipeForm.value.foto?.length || 0))
const remainingPhotoSlots = computed(() => Math.max(0, 8 - fotoCount.value))
const showLayananModal = ref(false)
const editingLayananId = ref(null)
const layananForm = ref({ nama: '', harga: 0, satuan: 'sekali_pakai' })
const satuanLayanan = [
  { value: 'per_tamu', label: 'Per tamu' },
  { value: 'per_kamar', label: 'Per kamar' },
  { value: 'per_hari', label: 'Per hari' },
  { value: 'sekali_pakai', label: 'Sekali pakai' },
]

function openLayananModal(item = null) {
  editingLayananId.value = item?.id || null
  layananForm.value = item ? { ...item } : { nama: '', harga: 0, satuan: 'sekali_pakai' }
  showLayananModal.value = true
}

async function submitLayanan() {
  saving.value = true
  try {
    const payload = { ...layananForm.value, harga: Number(layananForm.value.harga) }
    if (editingLayananId.value) await api.put(`/admin/layanan-tambahan/${editingLayananId.value}`, payload)
    else await api.post('/admin/layanan-tambahan', payload)
    await fetchData()
    showLayananModal.value = false
    showToast('Layanan tambahan tersimpan.')
  } catch (error) {
    const message = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join(' ')
      : error.response?.data?.message || 'Gagal menyimpan layanan tambahan.'
    showToast(message)
  } finally {
    saving.value = false
  }
}

async function deleteLayanan(id) {
  if (!confirm('Hapus layanan tambahan ini?')) return
  try {
    await api.delete(`/admin/layanan-tambahan/${id}`)
    layananList.value = layananList.value.filter((item) => item.id !== id)
    showToast('Layanan tambahan dihapus.')
  } catch (error) {
    showToast(error.response?.data?.message || 'Gagal menghapus layanan tambahan.')
  }
}

function openAddTipe() {
  editingTipeId.value = null
  tipeForm.value = { nama: '', harga_dasar: 0, kapasitas: 2, deskripsi: '' }
  fotoFiles.value = []
  fotoUploadError.value = ''
  showTipeModal.value = true
}

function openEditTipe(tipe) {
  editingTipeId.value = tipe.id
  tipeForm.value = { ...tipe }
  fotoFiles.value = []
  fotoUploadError.value = ''
  showTipeModal.value = true
}

function setFotoFiles(event) {
  const selectedFiles = Array.from(event.target.files || [])
  if (selectedFiles.length > remainingPhotoSlots.value) {
    fotoFiles.value = []
    fotoUploadError.value = `Tipe kamar ini hanya bisa ditambah ${remainingPhotoSlots.value} foto lagi.`
    event.target.value = ''
    return
  }
  fotoUploadError.value = ''
  fotoFiles.value = selectedFiles
}

function fotoUrl(foto) {
  const path = foto.path || foto.url || ''
  return /^https?:\/\//i.test(path) ? path : `${API_ORIGIN}/storage/${path}`
}

async function hapusFoto(fotoId) {
  try {
    await api.delete(`/admin/tipe-kamar/${editingTipeId.value}/foto/${fotoId}`)
    tipeForm.value.foto = (tipeForm.value.foto || []).filter((foto) => foto.id !== fotoId)
    const tipe = tipeKamarList.value.find((item) => item.id === editingTipeId.value)
    if (tipe) tipe.foto = tipeForm.value.foto
    showToast('Foto kamar dihapus.')
  } catch (error) {
    showToast(error.response?.data?.message || 'Gagal menghapus foto kamar.')
  }
}

async function submitTipe() {
  if (!tipeForm.value.nama || !tipeForm.value.harga_dasar) {
    alert('Nama dan Harga dasar wajib diisi!')
    return
  }

  saving.value = true
  const payload = {
    ...tipeForm.value,
    harga_dasar: Number(tipeForm.value.harga_dasar),
    kapasitas: Number(tipeForm.value.kapasitas),
  }

  try {
    let savedTipeId = editingTipeId.value
    if (editingTipeId.value) {
      await api.put(`/admin/tipe-kamar/${editingTipeId.value}`, payload)
      const idx = tipeKamarList.value.findIndex((t) => t.id === editingTipeId.value)
      if (idx !== -1) tipeKamarList.value[idx] = { ...payload, id: editingTipeId.value }
    } else {
      const res = await api.post('/admin/tipe-kamar', payload)
      const savedData = extractObjectPayload(res.data)
      savedTipeId = savedData.id
      if (!savedTipeId) throw new Error('ID tipe kamar tidak ditemukan dari server.')
      tipeKamarList.value.push({ ...savedData, foto: [] })
    }
    if (fotoFiles.value.length) {
      const formData = new FormData()
      fotoFiles.value.forEach((file) => formData.append('fotos[]', file))
      const fotoResponse = await api.post(`/admin/tipe-kamar/${savedTipeId}/foto`, formData)
      const updatedTipe = extractObjectPayload(fotoResponse.data)
      const index = tipeKamarList.value.findIndex((item) => Number(item.id) === Number(savedTipeId))
      if (index !== -1) tipeKamarList.value[index] = updatedTipe
    }
    showToast('Tipe kamar tersimpan.')
    showTipeModal.value = false
  } catch (error) {
    console.error('Gagal menyimpan tipe kamar ke API:', error)
    const message = error.response?.data?.errors
      ? Object.values(error.response.data.errors).flat().join(' ')
      : error.response?.data?.message || 'Gagal menyimpan tipe kamar ke server.'
    showToast(message)
  } finally {
    saving.value = false
  }
}

async function deleteTipe(id) {
  const dipakai = kamarList.value.some((k) => Number(k.tipe_kamar_id) === Number(id))
  if (dipakai) {
    alert('Tipe kamar ini masih dipakai oleh kamar tertentu. Ubah/hapus kamarnya dulu.')
    return
  }
  if (!confirm('Hapus tipe kamar ini?')) return
  try {
    await api.delete(`/admin/tipe-kamar/${id}`)
    tipeKamarList.value = tipeKamarList.value.filter((t) => t.id !== id)
    showToast('Tipe kamar dihapus.')
  } catch (error) {
    showToast(error.response?.data?.message || 'Gagal menghapus tipe kamar.')
  }
}

const kamarStatusOptions = ['tersedia', 'terisi', 'perbaikan', 'dibersihkan']

const kamarStatusLabel = {
  tersedia: 'Tersedia',
  terisi: 'Terisi',
  perbaikan: 'Perbaikan',
  dibersihkan: 'Dibersihkan',
}

const filterStatus = ref('semua')
const filteredKamar = computed(() => {
  if (filterStatus.value === 'semua') return kamarList.value
  return kamarList.value.filter((k) => k.status === filterStatus.value)
})

function logout() {
  localStorage.removeItem('token')
  localStorage.removeItem('velora_token')
  localStorage.removeItem('velora_user')
  router.push('/login')
}

const menuItems = [
  { key: 'dashboard', label: 'Dashboard', icon: '📊', to: '/admin' },
  { key: 'pemesanan', label: 'Pemesanan', icon: '📋', to: '/admin/pemesanan' },
  { key: 'kamar', label: 'Kamar', icon: '🛏️', to: '/admin/kamar' },
  { key: 'tipe-kamar', label: 'Tipe Kamar', icon: '🏷️', to: '/admin/tipe-kamar' },
  { key: 'promo', label: 'Promo', icon: '🏷️', to: '/admin/promo' },
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
          :class="{ active: item.key === 'kamar' }"
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
          <h1>Kelola Kamar</h1>
          <p>Atur data kamar dan tipe kamar Velora Resort</p>
        </div>
      </header>

      <!-- TABS -->
      <div class="tabs">
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'kamar' }"
          @click="activeTab = 'kamar'"
        >
          Daftar Kamar
        </button>
        <button
          class="tab-btn"
          :class="{ active: activeTab === 'tipe-kamar' }"
          @click="activeTab = 'tipe-kamar'"
        >
          Tipe Kamar
        </button>
        <button class="tab-btn" :class="{ active: activeTab === 'layanan' }" @click="activeTab = 'layanan'">
          Add-on &amp; Layanan
        </button>
      </div>

      <div v-if="loading" class="loading-state">
        Memuat data kamar...
      </div>
      <div v-else-if="loadError" class="loading-state error-state">
        {{ loadError }}
        <button class="btn-primary" @click="fetchData">Coba Lagi</button>
      </div>

      <template v-else>

        <!-- TAB: DAFTAR KAMAR -->
        <section v-if="activeTab === 'kamar'" class="panel">
          <div class="panel-head">
            <h2>Daftar Kamar</h2>
            <div class="panel-actions">
              <select v-model="filterStatus" class="filter-select">
                <option value="semua">Semua Status</option>
                <option v-for="s in kamarStatusOptions" :key="s" :value="s">
                  {{ kamarStatusLabel[s] }}
                </option>
              </select>
              <button class="btn-primary" @click="openAddKamar">+ Tambah Kamar</button>
            </div>
          </div>

          <div class="table-wrapper">
            <table>
              <thead>
                <tr>
                  <th>No. Kamar</th>
                  <th>Lantai</th>
                  <th>Tipe Kamar</th>
                  <th>Status</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="k in filteredKamar" :key="k.id">
                  <td class="code">{{ k.nomor_kamar }}</td>
                  <td>{{ k.lantai }}</td>
                  <td>{{ tipeKamarNama(k.tipe_kamar_id) }}</td>
                  <td>
                    <select
                      class="status-select"
                      :class="'room-' + k.status"
                      :value="k.status"
                      @change="updateKamarStatus(k, $event.target.value)"
                    >
                      <option v-for="s in kamarStatusOptions" :key="s" :value="s">
                        {{ kamarStatusLabel[s] }}
                      </option>
                    </select>
                  </td>
                  <td class="actions">
                    <button class="btn-link" @click="openEditKamar(k)">Edit</button>
                    <button class="btn-link danger" @click="deleteKamar(k.id)">Hapus</button>
                  </td>
                </tr>
                <tr v-if="filteredKamar.length === 0">
                  <td colspan="5" class="empty-row">Tidak ada kamar dengan status ini.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>

        <!-- TAB: TIPE KAMAR -->
        <section v-if="activeTab === 'tipe-kamar'" class="panel">
          <div class="panel-head">
            <h2>Tipe Kamar</h2>
            <div class="panel-actions">
              <button class="btn-primary" @click="openAddTipe">+ Tambah Tipe</button>
            </div>
          </div>

          <div class="tipe-grid">
            <div class="tipe-card" v-for="t in tipeKamarList" :key="t.id">
              <div class="tipe-card-head">
                <h3>{{ t.nama }}</h3>
                <span class="tipe-price">{{ formatRupiah(t.harga_dasar) }}<small>/malam</small></span>
              </div>
              <div v-if="t.foto?.length" class="tipe-photos">
                <img v-for="foto in t.foto" :key="foto.id" :src="fotoUrl(foto)" :alt="t.nama" />
              </div>
              <p class="tipe-desc">{{ t.deskripsi || '-' }}</p>
              <div class="tipe-meta">
                <span>Maks {{ t.kapasitas }} tamu</span>
                <span>{{ hitungKamarTipe(t.id) }} unit · {{ hitungKamarTipe(t.id, 'tersedia') }} tersedia</span>
              </div>
              <div class="tipe-actions">
                <button class="btn-link" @click="openEditTipe(t)">Edit</button>
                <button class="btn-link danger" @click="deleteTipe(t.id)">Hapus</button>
              </div>
            </div>
          </div>
        </section>

        <section v-if="activeTab === 'layanan'" class="panel">
          <div class="panel-head">
            <h2>Add-on &amp; Layanan</h2>
            <button class="btn-primary" @click="openLayananModal()">+ Tambah Add-on</button>
          </div>
          <div class="table-wrapper">
            <table>
              <thead><tr><th>Nama</th><th>Harga</th><th>Satuan</th><th>Aksi</th></tr></thead>
              <tbody>
                <tr v-for="item in layananList" :key="item.id">
                  <td>{{ item.nama }}</td><td>{{ formatRupiah(item.harga) }}</td>
                  <td>{{ satuanLayanan.find((satuan) => satuan.value === item.satuan)?.label || item.satuan }}</td>
                  <td class="actions"><button class="btn-link" @click="openLayananModal(item)">Edit</button><button class="btn-link danger" @click="deleteLayanan(item.id)">Hapus</button></td>
                </tr>
                <tr v-if="!layananList.length"><td colspan="4" class="empty-row">Belum ada add-on. Tambahkan Extra Bed dan tetapkan tarifnya.</td></tr>
              </tbody>
            </table>
          </div>
        </section>

      </template>

    </main>

    <!-- MODAL TAMBAH/EDIT KAMAR -->
    <div v-if="showKamarModal" class="modal-backdrop" @click.self="showKamarModal = false">
      <div class="modal">
        <h3>{{ editingKamarId ? 'Edit Kamar' : 'Tambah Kamar' }}</h3>

        <div class="fieldset">
          <label>Nomor Kamar</label>
          <input
            v-model="kamarForm.nomor_kamar"
            type="text"
            maxlength="20"
            placeholder="Contoh: 101"
            :class="{ 'input-invalid': kamarNumberError || isDuplicateRoomNumber }"
            @input="kamarNumberError = ''"
          />
          <small v-if="kamarNumberError" class="field-error">{{ kamarNumberError }}</small>
          <small v-else-if="isDuplicateRoomNumber" class="field-error">
            Nomor kamar ini sudah digunakan.
          </small>
        </div>

        <div class="fieldset">
          <label>Lantai</label>
          <input v-model.number="kamarForm.lantai" type="number" min="1" />
        </div>

        <div class="fieldset">
          <label>Tipe Kamar</label>
          <select v-model="kamarForm.tipe_kamar_id">
            <option value="" disabled>Pilih tipe kamar</option>
            <option v-for="t in tipeKamarList" :key="t.id" :value="t.id">
              {{ t.nama }}
            </option>
          </select>
        </div>

        <div class="fieldset">
          <label>Status</label>
          <select v-model="kamarForm.status">
            <option v-for="s in kamarStatusOptions" :key="s" :value="s">
              {{ kamarStatusLabel[s] }}
            </option>
          </select>
        </div>

        <div class="modal-actions">
          <button class="btn-cancel" @click="showKamarModal = false">Batal</button>
          <button class="btn-primary" :disabled="saving" @click="submitKamar">
            {{ saving ? 'Menyimpan...' : 'Simpan' }}
          </button>
        </div>
      </div>
    </div>

    <div v-if="showLayananModal" class="modal-backdrop" @click.self="showLayananModal = false">
      <form class="modal" @submit.prevent="submitLayanan">
        <h3>{{ editingLayananId ? 'Edit Add-on' : 'Tambah Add-on' }}</h3>
        <div class="fieldset"><label>Nama</label><input v-model="layananForm.nama" required maxlength="100" placeholder="Extra Bed" /></div>
        <div class="fieldset"><label>Harga</label><input v-model.number="layananForm.harga" type="number" min="0" required /></div>
        <div class="fieldset"><label>Satuan</label><select v-model="layananForm.satuan"><option v-for="satuan in satuanLayanan" :key="satuan.value" :value="satuan.value">{{ satuan.label }}</option></select></div>
        <div class="modal-actions"><button type="button" class="btn-cancel" @click="showLayananModal = false">Batal</button><button class="btn-primary" :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button></div>
      </form>
    </div>

    <!-- MODAL TAMBAH/EDIT TIPE KAMAR -->
    <div v-if="showTipeModal" class="modal-backdrop" @click.self="showTipeModal = false">
      <div class="modal">
        <h3>{{ editingTipeId ? 'Edit Tipe Kamar' : 'Tambah Tipe Kamar' }}</h3>

        <div class="fieldset">
          <label>Nama Tipe</label>
          <input v-model="tipeForm.nama" type="text" placeholder="Contoh: Suite Pesisir" />
        </div>

        <div class="fieldset">
          <label>Harga Dasar / Malam</label>
          <input v-model.number="tipeForm.harga_dasar" type="number" min="0" />
        </div>

        <div class="fieldset">
          <label>Kapasitas Tamu</label>
          <input v-model.number="tipeForm.kapasitas" type="number" min="1" />
        </div>

        <div class="fieldset">
          <label>Deskripsi</label>
          <textarea v-model="tipeForm.deskripsi" rows="3" placeholder="Deskripsi singkat tipe kamar"></textarea>
        </div>

        <div v-if="editingTipeId && tipeForm.foto?.length" class="fieldset">
          <label>Foto Saat Ini</label>
          <div class="photo-manager">
            <div v-for="foto in tipeForm.foto" :key="foto.id" class="photo-item">
              <img :src="fotoUrl(foto)" :alt="tipeForm.nama" />
              <button type="button" class="btn-link danger" @click="hapusFoto(foto.id)">Hapus</button>
            </div>
          </div>
        </div>

        <div class="fieldset">
          <label>Tambah Foto ({{ remainingPhotoSlots }} slot tersisa, JPG/PNG/WebP, 5 MB per file)</label>
          <input type="file" accept="image/jpeg,image/png,image/webp" multiple :disabled="remainingPhotoSlots === 0" @change="setFotoFiles" />
          <small v-if="fotoUploadError" class="field-error">{{ fotoUploadError }}</small>
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

/* =========================================
   SIDEBAR
   ========================================= */
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
  text-align: left;

  cursor: pointer;
  transition: background 0.2s ease;
}

.logout-btn:hover {
  background: rgba(248, 113, 113, 0.1);
}

/* =========================================
   MAIN CONTENT
   ========================================= */
.main-content {
  flex: 1;
  padding: 32px 40px;
  min-width: 0;
}

.topbar {
  margin-bottom: 20px;
}

.topbar h1 {
  margin: 0 0 4px;
  color: #0f172a;
  font-family: Georgia, serif;
  font-size: 26px;
  font-weight: 600;
}

.topbar p {
  margin: 0;
  color: #64748b;
  font-size: 14px;
}

.loading-state {
  padding: 60px 0;
  text-align: center;
  color: #64748b;
  font-size: 14px;
}

/* =========================================
   TABS
   ========================================= */
.tabs {
  display: flex;
  gap: 6px;
  margin-bottom: 20px;
  border-bottom: 1px solid #e2e8f0;
}

.tab-btn {
  padding: 10px 18px;
  background: none;
  border: none;
  border-bottom: 2px solid transparent;

  color: #64748b;
  font-family: inherit;
  font-size: 14px;
  font-weight: 600;

  cursor: pointer;
  transition: color 0.2s ease, border-color 0.2s ease;
}

.tab-btn:hover {
  color: #0f172a;
}

.tab-btn.active {
  color: #0284c7;
  border-bottom-color: #0284c7;
}

/* =========================================
   PANEL / TABLE
   ========================================= */
.panel {
  background: #ffffff;
  border: 1px solid #e2e8f0;
  border-radius: 12px;

  padding: 24px;
  margin-bottom: 24px;

  box-shadow: 0 4px 15px rgba(15, 23, 42, 0.04);
}

.panel-head {
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 12px;

  margin-bottom: 18px;
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

.filter-select {
  padding: 8px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  font-size: 13px;
  color: #334155;
  background: #ffffff;
  outline: none;
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
  transition: background 0.2s;
  white-space: nowrap;
}

.btn-primary:hover {
  background: #1e293b;
}

.btn-primary:disabled {
  background: #94a3b8;
  cursor: not-allowed;
}

.table-wrapper {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
  font-size: 13.5px;
}

thead th {
  text-align: left;
  padding: 10px 12px;
  color: #64748b;
  font-weight: 600;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: 0.4px;
  border-bottom: 1px solid #e2e8f0;
  white-space: nowrap;
}

tbody td {
  padding: 12px;
  color: #334155;
  border-bottom: 1px solid #f1f5f9;
  white-space: nowrap;
}

tbody tr:last-child td {
  border-bottom: none;
}

tbody tr:hover {
  background: #f8fafc;
}

.code {
  font-family: 'Courier New', monospace;
  font-weight: 600;
  color: #0284c7;
}

.empty-row {
  text-align: center;
  color: #94a3b8;
  padding: 30px 0;
}

.actions {
  display: flex;
  gap: 12px;
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

.btn-link:hover {
  text-decoration: underline;
}

.btn-link.danger {
  color: #dc2626;
}

/* STATUS SELECT (dropdown warna sesuai status) */
.status-select {
  padding: 5px 10px;
  border-radius: 999px;
  border: none;
  font-size: 11.5px;
  font-weight: 700;
  cursor: pointer;
  outline: none;
}

.room-tersedia { background: #dcfce7; color: #166534; }
.room-terisi { background: #dbeafe; color: #1e40af; }
.room-perbaikan { background: #fee2e2; color: #991b1b; }
.room-dibersihkan { background: #fef3c7; color: #92400e; }

/* =========================================
   TIPE KAMAR GRID
   ========================================= */
.tipe-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 18px;
}

.tipe-card {
  border: 1px solid #e2e8f0;
  border-radius: 10px;
  padding: 18px;
  background: #f8fafc;
}

.tipe-card-head {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 10px;
  margin-bottom: 8px;
}

.tipe-card-head h3 {
  margin: 0;
  font-size: 16px;
  color: #0f172a;
  font-family: Georgia, serif;
}

.tipe-price {
  font-size: 13px;
  font-weight: 700;
  color: #0284c7;
  white-space: nowrap;
}

.tipe-price small {
  font-weight: 400;
  color: #94a3b8;
}

.tipe-desc {
  font-size: 13px;
  color: #64748b;
  line-height: 1.5;
  margin: 0 0 12px;
}

.tipe-meta {
  display: flex;
  justify-content: space-between;
  font-size: 12px;
  color: #475569;
  margin-bottom: 12px;
  padding-top: 10px;
  border-top: 1px solid #e2e8f0;
}

.tipe-actions {
  display: flex;
  gap: 14px;
}

.tipe-photos, .photo-manager { display: flex; gap: 8px; overflow-x: auto; margin: 10px 0; }
.tipe-photos img, .photo-item img { width: 76px; height: 58px; object-fit: cover; border-radius: 4px; }
.photo-item { display: grid; gap: 4px; justify-items: center; }
.error-state { display: grid; justify-items: center; gap: 12px; color: #b91c1c; }

/* =========================================
   MODAL
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
  padding: 20px;
}

.modal {
  background: #ffffff;
  border-radius: 12px;
  padding: 28px;
  width: 100%;
  max-width: 420px;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.2);
  max-height: 90vh;
  overflow-y: auto;
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
.fieldset select,
.fieldset textarea {
  padding: 10px 12px;
  border: 1px solid #cbd5e1;
  border-radius: 6px;
  outline: none;
  font-family: inherit;
  font-size: 14px;
  color: #0f172a;
  resize: vertical;
}

.fieldset input:focus,
.fieldset select:focus,
.fieldset textarea:focus {
  border-color: #0284c7;
  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

.fieldset .input-invalid { border-color: #dc2626; }
.fieldset .field-error { color: #b91c1c; font-size: 12px; }

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

.btn-cancel:hover {
  background: #cbd5e1;
}

/* =========================================
   TOAST
   ========================================= */
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
  font-size: 14px;
}

/* =========================================
   RESPONSIVE
   ========================================= */
@media (max-width: 900px) {
  .admin-layout {
    flex-direction: column;
  }

  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    flex-direction: row;
    align-items: center;
    padding: 14px 18px;
    overflow-x: auto;
  }

  .sidebar-brand {
    padding: 0 16px 0 0;
    margin-bottom: 0;
    border-bottom: none;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
  }

  .sidebar-nav {
    flex-direction: row;
    flex: none;
    margin-left: 14px;
  }

  .nav-item span:last-child {
    display: none;
  }

  .logout-btn {
    margin-top: 0;
    margin-left: 10px;
  }

  .logout-btn span:last-child {
    display: none;
  }

  .main-content {
    padding: 20px;
  }
}

@media (max-width: 560px) {
  .main-content { padding: 14px; }
  .panel { padding: 14px; }
  .panel-head, .panel-actions { align-items: stretch; width: 100%; }
  .panel-actions { flex-direction: column; }
  .panel-actions select, .panel-actions button { width: 100%; }
  .modal { padding: 20px; }
  .tipe-grid { grid-template-columns: minmax(0, 1fr); }
  .tipe-meta { flex-wrap: wrap; gap: 6px; }
}
</style>