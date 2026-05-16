<template>
  <div class="auth-layout">
    <div class="card auth-card">
      <div class="auth-header">
        <h1 style="color: var(--dhl-red); font-size: 2rem; font-weight: 800;">DHL</h1>
        <h2>Welcome Back</h2>
        <p style="color: var(--text-secondary)">Sign in to your account</p>
      </div>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" v-model="email" class="form-control" placeholder="john@dhl.com" required />
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" v-model="password" class="form-control" placeholder="••••••••" required />
        </div>

        <div v-if="apiError" class="form-group">
          <span class="form-error">{{ apiError }}</span>
        </div>

        <button type="submit" class="btn-primary" style="width: 100%" :disabled="isLoading">
          {{ isLoading ? 'Signing In...' : 'Sign In' }}
        </button>

        <p style="text-align: center; margin-top: 1.5rem; font-size: 0.875rem;">
          Don't have an account? <router-link to="/register">Register here</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const email = ref('')
const password = ref('')
const apiError = ref('')
const isLoading = ref(false)

const router = useRouter()
const authStore = useAuthStore()

const handleLogin = async () => {
  apiError.value = ''
  isLoading.value = true

  try {
    const response = await fetch('http://localhost/DHL/backend/api/auth.php?action=login', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ email: email.value, password: password.value })
    })

    const data = await response.json()
    
    if (data.status === 'success') {
      authStore.login(data.token, data.user)
      router.push('/dashboard')
    } else {
      apiError.value = data.message || 'Login failed'
    }
  } catch (err) {
    apiError.value = 'Network error. Make sure XAMPP is running.'
  } finally {
    isLoading.value = false
  }
}
</script>
