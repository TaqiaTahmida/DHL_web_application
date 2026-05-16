<template>
  <DashboardLayout>
    <div style="max-width: 800px; margin: 0 auto;">
      <h1 style="margin-bottom: 2rem;">Profile Management</h1>
      
      <div class="card" style="margin-bottom: 2rem;">
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Update your personal information below.</p>
        
        <form @submit.prevent="updateProfile">
          <div class="form-group">
            <label class="form-label">Full Name</label>
            <input type="text" v-model="profile.name" class="form-control" required />
          </div>
          
          <div class="form-group">
            <label class="form-label">Email Address</label>
            <input type="email" v-model="profile.email" class="form-control" disabled style="background: #f1f5f9; cursor: not-allowed;" />
            <small style="color: var(--text-secondary)">Email address cannot be changed.</small>
          </div>
          
          <div v-if="message" :style="{ color: isError ? 'var(--dhl-red)' : 'var(--status-low)', marginBottom: '1rem', fontWeight: '500' }">
            {{ message }}
          </div>
          
          <button type="submit" class="btn-primary" :disabled="isUpdating">
            {{ isUpdating ? 'Saving...' : 'Save Changes' }}
          </button>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()
const profile = ref({ name: '', email: '' })
const isUpdating = ref(false)
const message = ref('')
const isError = ref(false)

onMounted(async () => {
  try {
    const response = await fetch('http://localhost/DHL/backend/api/profile.php', {
      headers: { 'Authorization': `Bearer ${authStore.token}` }
    })
    const data = await response.json()
    if (data.status === 'success') {
      profile.value = data.data
    }
  } catch (err) {
    console.error("Failed to fetch profile")
  }
})

const updateProfile = async () => {
  isUpdating.value = true
  message.value = ''
  
  try {
    const response = await fetch('http://localhost/DHL/backend/api/profile.php', {
      method: 'PUT',
      headers: { 
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify({ name: profile.value.name })
    })
    const data = await response.json()
    
    if (data.status === 'success') {
      message.value = 'Profile updated successfully!'
      isError.value = false
      authStore.updateUser({ ...authStore.user, name: profile.value.name })
    } else {
      message.value = data.message || 'Update failed'
      isError.value = true
    }
  } catch (err) {
    message.value = 'Network error'
    isError.value = true
  } finally {
    isUpdating.value = false
  }
}
</script>
