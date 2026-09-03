<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { register } from '../store/store'

const router = useRouter()
const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')

function submit() {
  error.value = ''
  if (password.value.length < 4) {
    error.value = 'Kata sandi minimal 4 karakter.'
    return
  }
  if (password.value !== confirmPassword.value) {
    error.value = 'Konfirmasi kata sandi tidak cocok.'
    return
  }
  const res = register({ name: name.value, email: email.value, password: password.value })
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
      <h1>Buat akun baru</h1>
      <p class="sub">Daftar untuk mulai memesan kamar di Nirwana Stay.</p>

      <form @submit.prevent="submit">
        <div class="fieldset">
          <label>Nama lengkap</label>
          <input v-model="name" type="text" required />
        </div>
        <div class="fieldset">
          <label>Email</label>
          <input v-model="email" type="email" required />
        </div>
        <div class="two-col">
          <div class="fieldset">
            <label>Kata sandi</label>
            <input v-model="password" type="password" required />
          </div>
          <div class="fieldset">
            <label>Ulangi kata sandi</label>
            <input v-model="confirmPassword" type="password" required />
          </div>
        </div>
        <button class="auth-submit" type="submit">Daftar</button>
        <p v-if="error" class="error-msg">{{ error }}</p>
      </form>

      <p class="switch-line">
        Sudah punya akun? <router-link to="/login">Masuk di sini</router-link>
      </p>
    </div>
  </div>
</template>