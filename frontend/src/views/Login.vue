<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../utils/api'
import { store } from '../store/store'

const router = useRouter()

const email = ref('')
const password = ref('')
const errorMsg = ref('')
const loading = ref(false)

function getRole(user, responseData, responseBody) {
  const roleValue = user?.role || user?.role_name || user?.nama_role ||
    user?.jabatan || user?.jenis_user ||
    responseData?.role || responseData?.role_name || responseData?.nama_role ||
    responseData?.jabatan || responseData?.jenis_user ||
    responseBody?.role || responseBody?.role_name || responseBody?.nama_role

  if (typeof roleValue === 'object') {
    return String(roleValue.name || roleValue.nama || roleValue.slug || roleValue.role || '').trim().toLowerCase()
  }

  return String(roleValue || 'tamu').trim().toLowerCase()
}

const login = async () => {
  loading.value = true
  errorMsg.value = ''
  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value
    })

    const responseBody = response.data || {}
    const responseData = responseBody.data || responseBody
    const accessToken = String(
      responseData.token ||
      responseData.access_token ||
      responseData.accessToken ||
      responseData.authorization?.token ||
      responseBody.token ||
      responseBody.access_token ||
      responseBody.accessToken ||
      responseBody.authorization?.token ||
      ''
    ).replace(/^Bearer\s+/i, '').trim()

    if (accessToken) {
      // 1. Ambil data user dari respon API
      const rawUser = responseData.user || responseBody.user || {
        email: email.value,
        name: email.value.split('@')[0],
        role: responseData.role || responseBody.role || 'tamu'
      }
      const role = getRole(rawUser, responseData, responseBody)
      
      const user = {
        ...rawUser,
        name: rawUser.nama || rawUser.name || email.value.split('@')[0],
        role: role
      }

      // 2. Update Store & LocalStorage secara reaktif sekaligus
      store.setUser(user, accessToken)

      // 3. Redirect sesuai role
      if (['admin', 'resepsionis'].includes(user.role)) {
        await router.push('/admin')
      } else if (user.role === 'tamu') {
        router.push('/')
      } else {
        errorMsg.value = `Login berhasil, tetapi role akun tidak punya akses admin (${user.role || 'tidak diketahui'}).`
      }
    } else {
      errorMsg.value = "Login gagal. Token tidak ditemukan dari server."
    }
  } catch (error) {
    const responseErrors = error.response?.data?.errors
    const validationMessage = responseErrors
      ? Object.values(responseErrors).flat().join(', ')
      : ''
    errorMsg.value = validationMessage || error.response?.data?.message || "Email atau password salah."
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

        <h1>Welcome Back</h1>

        <p>
          Masuk ke akun Velora Hotel Anda
        </p>
      </div>

      <p v-if="errorMsg" class="error-message">
        {{ errorMsg }}
      </p>

      <form @submit.prevent="login">

        <div class="form-group">
          <label for="email">Email Address</label>

          <input
            id="email"
            v-model="email"
            type="email"
            placeholder="nama@example.com"
            autocomplete="email"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>

          <input
            id="password"
            v-model="password"
            type="password"
            placeholder="••••••••"
            autocomplete="current-password"
            required
          />
        </div>

        <button
          class="auth-button"
          type="submit"
          :disabled="loading"
        >
          {{ loading ? 'Sedang Login...' : 'Login' }}
        </button>

      </form>

      <div class="auth-switch">
        <router-link to="/">
          &larr; Kembali ke Beranda
        </router-link>
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
  margin-bottom: 18px;
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
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
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
  transition: background 0.2s ease, transform 0.2s ease;
}

.auth-button:hover {
  background: #1e293b;
}

.auth-button:active {
  transform: scale(0.99);
}

.auth-button:disabled {
  background: #94a3b8;
  cursor: not-allowed;
  transform: none;
}

.error-message {
  margin: 0 0 18px;
  padding: 10px 13px;
  border-radius: 6px;
  background: rgba(220, 38, 38, 0.08);
  border: 1px solid rgba(220, 38, 38, 0.2);
  color: #dc2626;
  font-size: 13px;
}

.auth-switch {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 23px;
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