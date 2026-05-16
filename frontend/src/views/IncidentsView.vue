<template>
  <DashboardLayout>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
      <h1>Incidents</h1>
      <router-link to="/upload" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
        New Incident
      </router-link>
    </div>

    <div class="card" style="margin-bottom: 1.5rem; display: flex; gap: 1rem; align-items: center;">
      <div style="flex: 1;">
        <input type="text" v-model="searchQuery" class="form-control" placeholder="Search by ID or Summary..." />
      </div>
      <div>
        <select v-model="filterDepartment" class="form-control" style="min-width: 150px;">
          <option value="">All Departments</option>
          <option value="IT">IT</option>
          <option value="Operations">Operations</option>
          <option value="HR">HR</option>
          <option value="Finance">Finance</option>
        </select>
      </div>
      <div>
        <select v-model="filterPriority" class="form-control" style="min-width: 150px;">
          <option value="">All Priorities</option>
          <option value="Low">Low</option>
          <option value="Medium">Medium</option>
          <option value="High">High</option>
          <option value="Critical">Critical</option>
        </select>
      </div>
    </div>

    <div class="table-container">
      <table class="table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Summary</th>
            <th>Department</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="isLoading">
            <td colspan="7" style="text-align: center; padding: 2rem;">Loading incidents...</td>
          </tr>
          <tr v-else-if="filteredIncidents.length === 0">
            <td colspan="7" style="text-align: center; padding: 2rem; color: var(--text-secondary)">No incidents found.</td>
          </tr>
          <tr v-for="inc in filteredIncidents" :key="inc.id">
            <td style="font-weight: 600;">{{ inc.incident_ref_id }}</td>
            <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
              {{ inc.issue_summary }}
            </td>
            <td>{{ inc.department }}</td>
            <td>
              <span :class="['badge', 'badge-' + inc.priority.toLowerCase()]">
                {{ inc.priority }}
              </span>
            </td>
            <td>
              <span class="badge" style="background: #e2e8f0; color: #475569;">{{ inc.status }}</span>
            </td>
            <td style="color: var(--text-secondary); font-size: 0.875rem;">
              {{ new Date(inc.created_at).toLocaleDateString() }}
            </td>
            <td>
              <button @click="viewDetails(inc)" class="btn-secondary" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">
                View
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal for Details -->
    <div v-if="selectedIncident" class="modal-backdrop" @click.self="selectedIncident = null">
      <div class="modal-content card">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
          <div>
            <h2 style="margin: 0;">{{ selectedIncident.incident_ref_id }}</h2>
            <span :class="['badge', 'badge-' + selectedIncident.priority.toLowerCase()]" style="margin-top: 0.5rem;">
              {{ selectedIncident.priority }} Priority
            </span>
          </div>
          <button @click="selectedIncident = null" style="background: none; border: none; font-size: 1.5rem; cursor: pointer;">&times;</button>
        </div>
        
        <div style="margin-bottom: 1.5rem;">
          <h4 style="color: var(--text-secondary)">Issue Summary</h4>
          <p>{{ selectedIncident.issue_summary }}</p>
        </div>

        <div style="margin-bottom: 1.5rem;">
          <h4 style="color: var(--text-secondary)">Recommended Action</h4>
          <p>{{ selectedIncident.recommended_action || 'None provided.' }}</p>
        </div>

        <div style="margin-bottom: 1.5rem;">
          <h4 style="color: var(--text-secondary)">Progress History</h4>
          <pre style="background: #f8fafc; padding: 1rem; border-radius: var(--radius-md); font-family: monospace; font-size: 0.875rem; white-space: pre-wrap; border: 1px solid var(--border-color);">{{ selectedIncident.progress_history }}</pre>
        </div>

        <form @submit.prevent="updateProgress" style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
          <h4 style="margin-bottom: 1rem;">Add Progress Update</h4>
          <div class="form-group">
            <textarea v-model="newProgress" class="form-control" rows="3" placeholder="Describe the latest updates or actions taken..." required></textarea>
          </div>
          <button type="submit" class="btn-primary" :disabled="isUpdating">
            {{ isUpdating ? 'Updating...' : 'Post Update' }}
          </button>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const incidents = ref([])
const isLoading = ref(true)

// Filters
const searchQuery = ref('')
const filterDepartment = ref('')
const filterPriority = ref('')

// Modal state
const selectedIncident = ref(null)
const newProgress = ref('')
const isUpdating = ref(false)

const fetchIncidents = async () => {
  try {
    const response = await fetch('http://localhost/DHL/backend/api/incidents.php', {
      headers: { 'Authorization': `Bearer ${authStore.token}` }
    })
    const data = await response.json()
    if (data.status === 'success') {
      incidents.value = data.data
    }
  } catch (err) {
    console.error("Failed to load incidents")
  } finally {
    isLoading.value = false
  }
}

onMounted(fetchIncidents)

const filteredIncidents = computed(() => {
  return incidents.value.filter(inc => {
    const matchQuery = inc.incident_ref_id.toLowerCase().includes(searchQuery.value.toLowerCase()) || 
                       inc.issue_summary.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchDept = filterDepartment.value ? inc.department === filterDepartment.value : true
    const matchPriority = filterPriority.value ? inc.priority === filterPriority.value : true
    return matchQuery && matchDept && matchPriority
  })
})

const viewDetails = (inc) => {
  selectedIncident.value = { ...inc }
  newProgress.value = ''
}

const updateProgress = async () => {
  isUpdating.value = true
  try {
    const response = await fetch('http://localhost/DHL/backend/api/incidents.php', {
      method: 'PUT',
      headers: { 
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify({
        incident_ref_id: selectedIncident.value.incident_ref_id,
        new_progress: newProgress.value
      })
    })
    const data = await response.json()
    if (data.status === 'success') {
      await fetchIncidents()
      // Update local selected state
      const updated = incidents.value.find(i => i.id === selectedIncident.value.id)
      if (updated) selectedIncident.value = { ...updated }
      newProgress.value = ''
    }
  } catch (err) {
    console.error("Failed to update progress")
  } finally {
    isUpdating.value = false
  }
}
</script>

<style>
.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(15, 23, 42, 0.5);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 50;
}

.modal-content {
  width: 100%;
  max-width: 600px;
  max-height: 90vh;
  overflow-y: auto;
  animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
</style>
