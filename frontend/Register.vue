```vue
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { store } from './src/store/store'
import api from './src/utils/api'

const router = useRouter()
const name = ref('')
const email = ref('')
const password = ref('')
const confirmPassword = ref('')
const error = ref('')
const loading = ref(false)

async function submit() {
  error.value = ''

  if (!name.value.trim()) {
    error.value = 'Nama lengkap harus diisi.'
    return
  }

  if (password.value.length < 4) {
    error.value = 'Kata sandi minimal 4 karakter.'
    return
  }

  if (password.value !== confirmPassword.value) {
    error.value = 'Konfirmasi kata sandi tidak cocok.'
    return
  }

  loading.value = true
  try {
    const credentials = {
      name: name.value.trim(),
      email: email.value.trim(),
      password: password.value,
      password_confirmation: confirmPassword.value,
      role: 'tamu',
    }

    const registerResponse = await api.post('/register', credentials)
    const responseBody = registerResponse.data || {}
    const responseData = responseBody.data || responseBody
    const loginResponse = responseData.token || responseData.access_token
      ? registerResponse
      : await api.post('/login', {
          email: credentials.email,
          password: credentials.password,
        })
    const loginBody = loginResponse.data || {}
    const loginData = loginBody.data || loginBody
    const token = String(
      loginData.token || loginData.access_token || loginData.accessToken ||
      loginBody.token || loginBody.access_token || loginBody.accessToken || ''
    ).replace(/^Bearer\s+/i, '').trim()

    if (!token) {
      throw new Error('Token login tidak ditemukan dari server.')
    }

    const user = loginData.user || loginBody.user || {
      name: credentials.name,
      email: credentials.email,
      role: 'tamu',
    }
    user.role = String(user.role || 'tamu').trim().toLowerCase()
    localStorage.setItem('token', token)
    localStorage.setItem('velora_token', token)
    localStorage.setItem('velora_user', JSON.stringify(user))
    store.user = user
    router.push('/')
  } catch (err) {
    const validationErrors = err.response?.data?.errors
    error.value = validationErrors
      ? Object.values(validationErrors).flat()[0]
      : err.response?.data?.message || err.message || 'Pendaftaran gagal. Coba lagi nanti.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="auth-page">
    <div class="auth-card">
      <div class="auth-header">
        <div class="auth-logo">
          <span class="logo-mark"></span>
          <span>VELORA</span>
        </div>
        <h1>Buat akun baru</h1>
        <p>Daftar untuk mulai memesan kamar di Velora Resort.</p>
      </div>

      <form @submit.prevent="submit">
        <div class="form-group">
          <label for="name">Nama lengkap</label>
          <input id="name" v-model="name" type="text" placeholder="Masukkan nama lengkap" autocomplete="name" required />
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input id="email" v-model="email" type="email" placeholder="Masukkan email" autocomplete="email" required />
        </div>

        <div class="form-group">
          <label for="password">Kata sandi</label>
          <input id="password" v-model="password" type="password" placeholder="Minimal 4 karakter" autocomplete="new-password" required />
        </div>

        <div class="form-group">
          <label for="confirmPassword">Ulangi kata sandi</label>
          <input id="confirmPassword" v-model="confirmPassword" type="password" placeholder="Ulangi kata sandi" autocomplete="new-password" required />
        </div>

        <p v-if="error" class="error-message">{{ error }}</p>

        <button class="auth-button" type="submit" :disabled="loading">
          {{ loading ? 'Mendaftarkan...' : 'Daftar' }}
        </button>
      </form>

      <div class="auth-switch">
        <span>Sudah punya akun?</span>
        <router-link to="/login">Masuk di sini</router-link>
      </div>
    </div>
  </div>
</template>

<style scoped>
.auth-page {
  min-height: calc(100vh - 80px);
  background: #f8fafc;

  display: flex;
  align-items: center;
  justify-content: center;

  padding: 50px 20px;
}

.auth-card {
  width: 100%;
  max-width: 420px;

  background: #ffffff;

  border: 1px solid #e2e8f0;
  border-radius: 12px;

  padding: 40px;

  box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
}

.auth-header {
  text-align: center;
  margin-bottom: 30px;
}

.auth-logo {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 9px;

  margin-bottom: 28px;

  color: #0f172a;

  font-family: Georgia, serif;
  font-size: 20px;
  font-weight: 700;
}

.logo-mark {
  width: 25px;
  height: 25px;

  border-radius: 50%;

  background: conic-gradient(
    from 200deg,
    #0284c7,
    #0f172a,
    #0284c7
  );
}

.auth-header h1 {
  margin: 0 0 8px;

  color: #0f172a;

  font-family: Georgia, serif;
  font-size: 30px;
  font-weight: 500;

  line-height: 1.2;
}

.auth-header p {
  margin: 0;

  color: #64748b;

  font-size: 14px;
}

.form-group {
  margin-bottom: 17px;
}

.form-group label {
  display: block;

  margin-bottom: 7px;

  color: #334155;

  font-size: 13px;
  font-weight: 600;
}

.form-group input {
  width: 100%;

  padding: 12px 13px;

  border: 1px solid #cbd5e1;
  border-radius: 6px;

  background: #ffffff;

  color: #0f172a;

  font-family: inherit;
  font-size: 15px;

  outline: none;

  transition:
    border-color 0.2s ease,
    box-shadow 0.2s ease;
}

.form-group input::placeholder {
  color: #94a3b8;
}

.form-group input:focus {
  border-color: #0284c7;

  box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.12);
}

.auth-button {
  width: 100%;

  margin-top: 5px;

  padding: 13px;

  border: none;
  border-radius: 6px;

  background: #0f172a;
  color: #ffffff;

  font-family: inherit;
  font-size: 15px;
  font-weight: 600;

  cursor: pointer;

  transition: background 0.2s ease;
}

.auth-button:hover {
  background: #1e293b;
}

.error-message {
  margin: 0 0 12px;

  color: #dc2626;

  font-size: 13px;
}

.auth-switch {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 5px;

  margin-top: 23px;

  color: #64748b;

  font-size: 14px;
}

.auth-switch a {
  color: #0284c7;

  font-weight: 600;

  text-decoration: none;
}

.auth-switch a:hover {
  color: #0369a1;
  text-decoration: underline;
}

@media (max-width: 480px) {
  .auth-page {
    padding: 30px 16px;
  }

  .auth-card {
    padding: 30px 24px;
  }

  .auth-header h1 {
    font-size: 26px;
  }
}
</style>