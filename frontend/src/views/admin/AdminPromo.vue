<script setup>
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../utils/api'

const router = useRouter()
const route = useRoute()
const promos = ref([])
const loading = ref(true)
const saving = ref(false)
const error = ref('')
const editingId = ref(null)
const showForm = ref(false)
const form = ref({ kode: '', jenis_diskon: 'persen', nilai: 10, tanggal_mulai: '', tanggal_selesai: '', aktif: true })

async function fetchPromos() {
  loading.value = true
  error.value = ''
  try {
    const response = await api.get('/admin/promo')
    promos.value = response.data?.data || []
  } catch (err) {
    error.value = err.response?.data?.message || 'Promo gagal dimuat.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchPromos)

function openCreate() {
  editingId.value = null
  form.value = { kode: '', jenis_diskon: 'persen', nilai: 10, tanggal_mulai: '', tanggal_selesai: '', aktif: true }
  showForm.value = true
}

function openEdit(promo) {
  editingId.value = promo.id
  form.value = { ...promo }
  showForm.value = true
}

async function savePromo() {
  saving.value = true
  try {
    const payload = { ...form.value, kode: form.value.kode.trim().toUpperCase(), nilai: Number(form.value.nilai) }
    if (editingId.value) await api.put(`/admin/promo/${editingId.value}`, payload)
    else await api.post('/admin/promo', payload)
    showForm.value = false
    await fetchPromos()
  } catch (err) {
    error.value = err.response?.data?.errors
      ? Object.values(err.response.data.errors).flat().join(' ')
      : err.response?.data?.message || 'Promo gagal disimpan.'
  } finally {
    saving.value = false
  }
}

async function deletePromo(promo) {
  if (!confirm(`Hapus promo ${promo.kode}?`)) return
  try {
    await api.delete(`/admin/promo/${promo.id}`)
    await fetchPromos()
  } catch (err) {
    error.value = err.response?.data?.message || 'Promo gagal dihapus.'
  }
}

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
    <main class="content">
      <header><div><h1>Promo</h1><p>Kelola kode promo dan periode berlakunya.</p></div><button class="primary" @click="openCreate">+ Tambah Promo</button></header>
      <p v-if="error" class="notice">{{ error }}</p>
      <p v-if="loading" class="state">Memuat promo...</p>
      <div v-else class="table-wrap">
        <table>
          <thead><tr><th>Kode</th><th>Diskon</th><th>Periode</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
            <tr v-for="promo in promos" :key="promo.id">
              <td><strong>{{ promo.kode }}</strong></td>
              <td>{{ promo.jenis_diskon === 'persen' ? `${promo.nilai}%` : `Rp${Number(promo.nilai).toLocaleString('id-ID')}` }}</td>
              <td>{{ promo.tanggal_mulai }} – {{ promo.tanggal_selesai }}</td>
              <td>{{ promo.aktif ? 'Aktif' : 'Nonaktif' }}</td>
              <td class="actions"><button @click="openEdit(promo)">Edit</button><button class="danger" @click="deletePromo(promo)">Hapus</button></td>
            </tr>
            <tr v-if="!promos.length"><td colspan="5" class="state">Belum ada promo.</td></tr>
          </tbody>
        </table>
      </div>
    </main>
    <div v-if="showForm" class="backdrop" @click.self="showForm = false">
      <form class="modal" @submit.prevent="savePromo">
        <h2>{{ editingId ? 'Edit Promo' : 'Tambah Promo' }}</h2>
        <label>Kode<input v-model="form.kode" required maxlength="40" autocomplete="off" /></label>
        <label>Jenis Diskon<select v-model="form.jenis_diskon"><option value="persen">Persentase</option><option value="nominal">Nominal Rupiah</option></select></label>
        <label>Nilai<input v-model.number="form.nilai" type="number" min="1" :max="form.jenis_diskon === 'persen' ? 100 : undefined" required /></label>
        <div class="dates"><label>Mulai<input v-model="form.tanggal_mulai" type="date" required /></label><label>Selesai<input v-model="form.tanggal_selesai" type="date" required /></label></div>
        <label class="toggle"><input v-model="form.aktif" type="checkbox" /> Promo aktif</label>
        <div class="actions"><button type="button" @click="showForm = false">Batal</button><button class="primary" :disabled="saving">{{ saving ? 'Menyimpan...' : 'Simpan' }}</button></div>
      </form>
    </div>
  </div>
</template>

<style scoped>
.admin-layout{display:flex;min-height:100vh;background:#f5f7f6;color:#172b27;font-family:system-ui,sans-serif}.sidebar{width:230px;flex:none;background:#152d28;color:#fff;padding:24px 16px;display:flex;flex-direction:column}.brand{font:700 20px Georgia,serif;padding:0 10px 20px}.sidebar nav{display:grid;gap:4px}.sidebar a,.logout{color:#d7e3de;text-decoration:none;padding:10px;border:0;background:none;text-align:left;border-radius:4px}.sidebar a.active,.sidebar a:hover{background:#31594c;color:#fff}.logout{margin-top:auto;cursor:pointer}.content{min-width:0;flex:1;padding:32px clamp(16px,4vw,48px)}header{display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:24px}h1{font:500 28px Georgia,serif;margin:0}header p{margin:6px 0;color:#60716c}.primary{border:0;border-radius:4px;background:#21574a;color:#fff;padding:10px 15px;cursor:pointer}.table-wrap{overflow:auto;background:#fff;border:1px solid #dce5e1}table{width:100%;border-collapse:collapse;min-width:650px}th,td{text-align:left;padding:13px 15px;border-bottom:1px solid #e6ece9}th{font-size:12px;text-transform:uppercase;color:#61716d}td{font-size:14px}.actions{display:flex;gap:12px}.actions button{background:none;border:0;color:#17634f;cursor:pointer}.actions .danger,.notice{color:#b42318}.notice,.state{padding:16px}.backdrop{position:fixed;inset:0;background:#10221eb8;display:grid;place-items:center;padding:16px}.modal{width:min(100%,480px);max-height:90vh;overflow:auto;background:#fff;padding:24px;display:grid;gap:14px;border-radius:4px}.modal h2{font:500 23px Georgia,serif;margin:0 0 4px}.modal label{display:grid;gap:6px;font-size:13px;color:#42544e}.modal input,.modal select{min-width:0;padding:10px;border:1px solid #cbd8d2;border-radius:3px;font:inherit;color:#172b27}.dates{display:grid;grid-template-columns:1fr 1fr;gap:12px}.modal .toggle{display:flex;align-items:center;grid-template-columns:auto 1fr}.toggle input{width:17px;height:17px}.modal .actions{justify-content:flex-end}.actions button{padding:9px 12px}.state{color:#61716d}@media(max-width:760px){.admin-layout{display:block}.sidebar{width:100%;padding:12px;position:static}.brand{padding:4px 8px 10px}.sidebar nav{display:flex;overflow:auto}.sidebar a{white-space:nowrap}.logout{position:absolute;right:12px;top:8px}.content{padding:18px 14px}header{align-items:flex-start;flex-direction:column}.dates{grid-template-columns:1fr}}
.admin-layout { background: #f8fafc; color: #0f172a; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
.sidebar { width: 250px; flex-shrink: 0; position: sticky; top: 0; height: 100vh; background: #0f172a; color: #f8fafc; padding: 28px 18px; }
.sidebar-brand { display: flex; align-items: center; gap: 10px; padding: 0 10px 28px; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,.1); font: 700 19px Georgia,serif; letter-spacing: 1px; }
.logo-mark { width: 24px; height: 24px; flex-shrink: 0; border-radius: 50%; background: conic-gradient(from 200deg,#0284c7,#f8fafc,#0284c7); }
.sidebar-nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
.nav-item { display: flex; align-items: center; gap: 12px; padding: 11px 14px; border-radius: 8px; color: #cbd5e1; text-decoration: none; font-size: 14px; font-weight: 500; transition: background .2s ease,color .2s ease; }
.sidebar .nav-item { color: #cbd5e1; text-decoration: none; padding: 11px 14px; border-radius: 8px; background: none; }
.sidebar .nav-item:hover { background: rgba(255,255,255,.06); color: #fff; }
.sidebar .nav-item.active { background: #0284c7; color: #fff; }
.nav-icon { width: 18px; flex-shrink: 0; text-align: center; font-size: 16px; }
.logout-btn { display: flex; align-items: center; gap: 12px; padding: 11px 14px; margin-top: 12px; border: 1px solid rgba(255,255,255,.15); border-radius: 8px; background: none; color: #f87171; font: 600 14px inherit; cursor: pointer; }
.logout-btn:hover { background: rgba(248,113,113,.1); }
.content { flex: 1; min-width: 0; padding: 32px 40px; }
.content > header { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 24px; }
.content h1 { color: #0f172a; font: 600 26px Georgia,serif; }
.content header p { margin: 4px 0 0; color: #64748b; font-size: 14px; }
.primary { background: #0f172a; color: #fff; font-weight: 600; }
.primary:hover { background: #1e293b; }
.table-wrap { background: #fff; border-color: #e2e8f0; }
th,td { border-color: #e2e8f0; }
th { color: #64748b; }
.actions button { color: #0284c7; }
.backdrop { background: rgba(15,23,42,.6); }
.notice { color: #b42318; }
@media (max-width: 900px) {
  .admin-layout { flex-direction: column; }
  .sidebar { width: 100%; height: auto; position: relative; flex-direction: row; align-items: center; padding: 14px 18px; overflow-x: auto; }
  .sidebar-brand { flex: none; padding: 0 16px 0 0; margin: 0; border-bottom: 0; border-right: 1px solid rgba(255,255,255,.1); }
  .sidebar-nav { flex-direction: row; flex: none; margin-left: 14px; }
  .nav-item span:last-child,.logout-btn span:last-child { display: none; }
  .logout-btn { flex: none; margin: 0 0 0 10px; }
  .content { padding: 20px; }
}
@media (max-width: 560px) {
  .sidebar { padding: 12px; }
  .sidebar-brand { padding-right: 10px; font-size: 16px; }
  .sidebar-nav { margin-left: 8px; }
  .nav-item { padding: 10px; }
  .logout-btn { padding: 10px; margin-left: 8px; }
  .content { padding: 16px 14px; }
  .content > header { align-items: flex-start; flex-direction: column; }
  .content > header .primary { width: 100%; }
  .dates { grid-template-columns: 1fr; }
}
</style>