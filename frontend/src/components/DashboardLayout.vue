<template>
  <div class="dashboard-layout">
    <aside class="sidebar">
      <div class="sidebar-brand">
        <span style="color: var(--dhl-red)">DHL</span>
        <span style="color: var(--text-primary); font-size: 1.25rem;">Incidents</span>
      </div>
      
      <nav class="sidebar-nav">
        <router-link to="/dashboard" class="nav-item">Dashboard</router-link>
        <router-link to="/incidents" class="nav-item">All Incidents</router-link>
        <router-link to="/upload" class="nav-item">Upload Incident</router-link>
      </nav>
      
      <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color);">
        <button @click="logout" class="nav-item" style="width: 100%; border: none; background: transparent; cursor: pointer; text-align: left;">
          Sign Out
        </button>
      </div>
    </aside>
    
    <main class="main-content">
      <header class="topbar" style="display: flex; justify-content: space-between; align-items: center;">
        <div style="font-weight: 500;">
          Welcome, {{ authStore.user?.name || 'User' }}
        </div>
        <div>
          <router-link to="/profile" title="Profile Management" style="text-decoration: none; color: var(--text-primary); display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background-color: #f1f5f9; transition: background-color 0.2s;">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
              <circle cx="12" cy="7" r="4"></circle>
            </svg>
          </router-link>
        </div>
      </header>
      
      <div class="content-area">
        <slot></slot>
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const logout = () => {
  authStore.logout()
  router.push('/login')
}
</script>
