<template>
  <div style="padding:16px;display:grid;gap:16px;">
    <header style="display:flex;justify-content:space-between;align-items:center;">
      <h2>Task Board</h2>
      <div>
        <button @click="logout">Logout</button>
      </div>
    </header>

    <section style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
      <input v-model="q" placeholder="Search title/description" />
      <select v-model="status">
        <option value="">All statuses</option>
        <option v-for="s in statuses" :key="s.id" :value="s.name">{{ s.name }}</option>
      </select>
      <select v-model.number="assignee">
        <option :value="0">All assignees</option>
        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
      </select>
      <button @click="fetchTasks">Filter</button>
      <button @click="openCreate">+ New Task</button>
    </section>

    <section style="display:grid;grid-template-columns:repeat(3,minmax(250px,1fr));gap:12px;">
      <div v-for="column in columns" :key="column.key" style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;">
        <div style="background:#f3f4f6;padding:8px 12px;font-weight:600;">{{ column.title }}</div>
        <div style="display:grid;gap:8px;padding:12px;min-height:200px;">
          <article v-for="t in tasksByStatus[column.key]" :key="t.id" style="border:1px solid #e5e7eb;padding:8px;border-radius:6px;display:grid;gap:6px;">
            <div style="font-weight:600;">{{ t.title }}</div>
            <div style="font-size:12px;color:#6b7280">{{ fmt(t.start_date) }} → {{ fmt(t.end_date) }}</div>
            <div style="font-size:12px;color:#6b7280">{{ t.user?.name }}</div>
            <div style="display:flex;gap:6px;flex-wrap:wrap;">
              <select v-model="t.status" @change="updateStatus(t)">
                <option v-for="s in statuses" :key="s.id" :value="s.name">{{ s.name }}</option>
              </select>
              <button @click="openReassign(t)">Reassign</button>
              <button @click="removeTask(t)">Delete</button>
            </div>
          </article>
        </div>
      </div>
    </section>

    <TaskModal v-if="showModal" :users="users" :statuses="statuses" @close="closeModal" @created="onCreated" />
    <ReassignModal v-if="reassignTask" :task="reassignTask" :users="users" @close="closeReassign" @saved="onReassigned" />
  </div>
  
</template>

<script setup lang="ts">
import axios from 'axios'
import { computed, onMounted, ref } from 'vue'
import { useAuthStore } from '../stores/auth'
import TaskModal from '../widgets/TaskModal.vue'
import ReassignModal from '../widgets/ReassignModal.vue'

const auth = useAuthStore()
axios.interceptors.request.use((config) => {
  Object.assign(config.headers ??= {}, auth.authHeader)
  return config
})

const q = ref('')
const status = ref('')
const assignee = ref<number>(0)
const tasks = ref<any[]>([])
const users = ref<any[]>([])
const statuses = ref<any[]>([])
const showModal = ref(false)

const columns = [
  { key: 'todo', title: 'To Do' },
  { key: 'in_progress', title: 'In Progress' },
  { key: 'done', title: 'Done' },
]

const tasksByStatus = computed<Record<string, any[]>>(() => {
  const map: Record<string, any[]> = { todo: [], in_progress: [], done: [] }
  for (const t of tasks.value) {
    if (map[t.status]) map[t.status].push(t)
  }
  return map
})

function fmt(d: string) {
  return new Date(d).toLocaleString()
}

async function fetchMeta() {
  const [u, s] = await Promise.all([
    axios.get('/api/meta/users'),
    axios.get('/api/meta/statuses'),
  ])
  users.value = u.data
  statuses.value = s.data
}

async function fetchTasks() {
  const params: any = {}
  if (q.value) params.q = q.value
  if (status.value) params.status = status.value
  if (assignee.value) params.assignee = assignee.value
  const res = await axios.get('/api/tasks', { params })
  tasks.value = res.data.data || res.data
}

function openCreate() { showModal.value = true }
function closeModal() { showModal.value = false }
async function onCreated() { showModal.value = false; await fetchTasks() }

async function updateStatus(t: any) {
  await axios.put(`/api/tasks/${t.id}`, { status: t.status })
  await fetchTasks()
}

async function removeTask(t: any) {
  await axios.delete(`/api/tasks/${t.id}`)
  await fetchTasks()
}

const reassignTask = ref<any|null>(null)
function openReassign(t: any) { reassignTask.value = t }
function closeReassign() { reassignTask.value = null }
async function onReassigned() { reassignTask.value = null; await fetchTasks() }

function logout() {
  auth.clear()
  location.href = '/login'
}

onMounted(async () => {
  await fetchMeta()
  await fetchTasks()
})
</script>


