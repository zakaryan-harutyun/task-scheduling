<template>
  <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:grid;place-items:center;padding:16px;">
    <form @submit.prevent="submit" style="background:white;width:100%;max-width:520px;border-radius:8px;padding:16px;display:grid;gap:8px;">
      <header style="display:flex;justify-content:space-between;align-items:center;">
        <h3>Create Task</h3>
        <button type="button" @click="$emit('close')">×</button>
      </header>
      <input v-model="title" placeholder="Title" required />
      <textarea v-model="description" placeholder="Description"></textarea>
      <label>Start <input v-model="start" type="datetime-local" required /></label>
      <label>End <input v-model="end" type="datetime-local" required /></label>
      <label>Assignee
        <select v-model.number="userId" required>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
      </label>
      <label>Status
        <select v-model="status" required>
          <option v-for="s in statuses" :key="s.id" :value="s.name">{{ s.name }}</option>
        </select>
      </label>
      <p v-if="error" style="color:#b91c1c">{{ error }}</p>
      <footer style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" @click="$emit('close')">Cancel</button>
        <button type="submit">Create</button>
      </footer>
    </form>
  </div>
  
</template>

<script setup lang="ts">
import axios from 'axios'
import { useAuthStore } from '../stores/auth'
import { ref, watch } from 'vue'

const props = defineProps<{ users: any[]; statuses: any[] }>()
const emit = defineEmits<{ (e:'close'): void; (e:'created'): void }>()

const auth = useAuthStore()
const title = ref('')
const description = ref('')
const start = ref('')
const end = ref('')
const userId = ref<number>(0)
const status = ref('todo')
const error = ref('')

watch(() => userId.value, () => { error.value = '' })
watch([start, end], () => { error.value = '' })

async function submit() {
  error.value = ''
  try {
    await axios.post('/api/tasks', {
      title: title.value,
      description: description.value || null,
      start_date: new Date(start.value).toISOString(),
      end_date: new Date(end.value).toISOString(),
      status: status.value,
      user_id: userId.value,
    }, { headers: auth.authHeader })
    emit('created')
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Cannot create task'
  }
}
</script>


