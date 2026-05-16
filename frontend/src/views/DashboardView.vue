<template>
  <DashboardLayout>
    <div style="max-width: 800px; margin: 0 auto;">
      <h1 style="margin-bottom: 2rem;">Dashboard</h1>

      <div class="card">
        <h2>System Overview</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Quick stats for the incident management system.</p>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
          <div style="padding: 1.5rem; background: rgba(255, 204, 0, 0.1); border-radius: var(--radius-md); border-left: 4px solid var(--dhl-yellow);">
            <h3 style="font-size: 2rem; margin: 0; color: var(--dhl-black)">--</h3>
            <p style="color: var(--text-secondary); margin: 0; font-weight: 500;">Total Incidents</p>
          </div>
          <div style="padding: 1.5rem; background: rgba(239, 68, 68, 0.1); border-radius: var(--radius-md); border-left: 4px solid var(--dhl-red);">
            <h3 style="font-size: 2rem; margin: 0; color: var(--dhl-red)">--</h3>
            <p style="color: var(--text-secondary); margin: 0; font-weight: 500;">Critical Issues</p>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '../components/DashboardLayout.vue'
import { useAuthStore } from '../stores/auth'

const authStore = useAuthStore()

</script>
