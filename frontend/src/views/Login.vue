<template>
  <div style="display:grid;place-items:center;height:100vh;padding:16px;">
    <form @submit.prevent="submit" style="width:100%;max-width:360px;display:grid;gap:12px;">
      <h2>Login</h2>
      <input v-model="email" type="email" placeholder="Email" required />
      <input v-model="password" type="password" placeholder="Password" required />
      <button type="submit">Sign in</button>
      <p v-if="error" style="color:#b91c1c">{{ error }}</p>
    </form>
  </div>
  
</template>

<script setup lang="ts">
import axios from 'axios'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'
import { ref } from 'vue'

const router = useRouter()
const auth = useAuthStore()
const email = ref('admin@example.com')
const password = ref('password')
const error = ref('')

async function submit() {
  error.value = ''
  try {
    const res = await axios.post('http://localhost:8000/api/login', { email: email.value, password: password.value })
    auth.setAuth(res.data.token, res.data.user)
    router.push({ name: 'dashboard' })
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Login failed'
  }
}
</script>


