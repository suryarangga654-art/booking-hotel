<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { login } from '../store/store'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref('')

function submit() {
  const res = login(email.value, password.value)
  if (!res.ok) {
    error.value = res.error
    return
  }
  router.push('/')
}
</script>

<template>
  <div class="auth-wrap">
    <div class="auth-card">
      <h1>Selamat datang kembali</h1>
      <p class="sub">Masuk untuk melanjutkan pemesanan kamar.</p>

      <form @submit.prevent="submit">
        <div class="fieldset">
          <label>Email</label>
          <input v-model="email" type="email" required />
        </div>
        <div class="fieldset">
          <label>Kata sandi</label>
          <input v-model="password" type="password" required />
        </div>
        <button class="auth-submit" type="submit">Masuk</button>
        <p v-if="error" class="error-msg">{{ error }}</p>
      </form>

      <p class="switch-line">
        Belum punya akun? <router-link to="/register">Daftar di sini</router-link>
      </p>

      <div class="hint">
        Catatan: ini masih versi frontend. Akun tersimpan sementara di memori
        browser dan akan hilang saat halaman dimuat ulang — nanti disambungkan
        ke backend sungguhan (ganti isi fungsi <code>login()</code> di
        <code>src/store/store.js</code> dengan pemanggilan API).
      </div>
    </div>
  </div>
</template>