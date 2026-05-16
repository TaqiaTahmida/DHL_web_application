<template>
  <div class="auth-layout">
    <div class="card auth-card">
      <div class="auth-header">
        <h1 style="color: var(--dhl-red); font-size: 2rem; font-weight: 800;">DHL</h1>
        <h2>Create an Account</h2>
        <p style="color: var(--text-secondary)">Incident Management System</p>
      </div>

      <form @submit.prevent="handleRegister">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" v-model="name" class="form-control" placeholder="John Doe" required />
        </div>

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" v-model="email" class="form-control" placeholder="john@dhl.com" required />
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" v-model="password" class="form-control" placeholder="••••••••" required />
          <span v-if="passwordError" class="form-error">{{ passwordError }}</span>
        </div>

        <div v-if="apiError" class="form-group">
          <span class="form-error">{{ apiError }}</span>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%" :disabled="isLoading">
          {{ isLoading ? 'Creating Account...' : 'Register' }}
        </button>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem;">
          Already have an account? <router-link to="/login">Sign in</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const name = ref('')
const email = ref('')
const password = ref('')
const passwordError = ref('')
const apiError = ref('')
const isLoading = ref(false)

const router = useRouter()
const authStore = useAuthStore()

const handleRegister = async () => {
  passwordError.value = ''
  apiError.value = ''

  if (password.value.length < 6) {
    passwordError.value = 'Password must be at least 6 characters long.'
    return
  }

  isLoading.value = true
  try {
    const response = await fetch('http://localhost/DHL/backend/api/auth.php?action=register', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        name: name.value,
        email: email.value,
        password: password.value
      })
    })

    const data = await response.json()
    
    if (data.status === 'success') {
      authStore.login(data.token, { name: name.value, email: email.value })
      router.push('/dashboard')
    } else {
      apiError.value = data.message || 'Registration failed'
    }
  } catch (err) {
    apiError.value = 'Network error. Make sure XAMPP is running.'
  } finally {
    isLoading.value = false
  }
}
</script>
