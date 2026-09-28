import axios from 'axios'

const apiBaseURL = import.meta.env.VITE_API_BASE_URL || `http://${window.location.hostname}:8001/api`

const api = axios.create({
  baseURL: apiBaseURL,
  headers: {
    'Accept': 'application/json'
  }
})
// Interceptor Request: Menyisipkan Token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token') || localStorage.getItem('velora_token')
    if (token) {
      config.headers.Authorization = `Bearer ${token.trim()}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor Response: Tangani jika Token Hangus/Invalid
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      // Hapus token rusak agar tidak memicu loop
      localStorage.removeItem('token')
      localStorage.removeItem('velora_token')
    }
    return Promise.reject(error)
  }
)

export default api