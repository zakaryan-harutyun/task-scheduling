<template>
  <div style="position:fixed;inset:0;background:rgba(0,0,0,.5);display:grid;place-items:center;padding:16px;">
    <form @submit.prevent="submit" style="background:white;width:100%;max-width:420px;border-radius:8px;padding:16px;display:grid;gap:8px;">
      <header style="display:flex;justify-content:space-between;align-items:center;">
        <h3>Reassign Task</h3>
        <button type="button" @click="$emit('close')">×</button>
      </header>
      <div style="font-weight:600">{{ task.title }}</div>
      <label>Assignee
        <select v-model.number="userId" required>
          <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
        </select>
      </label>
      <p v-if="error" style="color:#b91c1c">{{ error }}</p>
      <footer style="display:flex;gap:8px;justify-content:flex-end;">
        <button type="button" @click="$emit('close')">Cancel</button>
        <button type="submit">Save</button>
      </footer>
    </form>
  </div>
</template>

<script setup lang="ts">
import axios from 'axios'
import { ref } from 'vue'

const props = defineProps<{ task: any, users: any[] }>()
const emit = defineEmits<{ (e:'close'): void; (e:'saved'): void }>()

const userId = ref<number>(props.task.user_id)
const error = ref('')

async function submit() {
  error.value = ''
  try {
    await axios.post(`/api/tasks/${props.task.id}/reassign`, { user_id: userId.value })
    emit('saved')
  } catch (e: any) {
    error.value = e?.response?.data?.message || 'Cannot reassign task'
  }
}
</script>


