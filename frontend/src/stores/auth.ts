import { defineStore } from 'pinia'

interface User { id: number; name: string; email: string }

export const useAuthStore = defineStore('auth', {
  state: () => ({
    token: localStorage.getItem('token') || '',
    user: (localStorage.getItem('user') ? JSON.parse(localStorage.getItem('user') as string) : null) as User | null,
  }),
  getters: {
    isAuthenticated: (s) => !!s.token,
    authHeader: (s) => s.token ? { Authorization: `Bearer ${s.token}` } : {},
  },
  actions: {
    setAuth(token: string, user: User) {
      this.token = token
      this.user = user
      localStorage.setItem('token', token)
      localStorage.setItem('user', JSON.stringify(user))
    },
    clear() {
      this.token = ''
      this.user = null
      localStorage.removeItem('token')
      localStorage.removeItem('user')
    }
  }
})


