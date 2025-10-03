import axios from 'axios'
import { useAuthStore } from './stores/auth'

const api = axios.create({ baseURL: '/api' })

api.interceptors.request.use((config) => {
  const auth = useAuthStore()
  Object.assign(config.headers ??= {}, auth.authHeader)
  return config
})

export default api


