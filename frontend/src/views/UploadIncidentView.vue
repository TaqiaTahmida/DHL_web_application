<template>
  <DashboardLayout>
    <div style="max-width: 800px; margin: 0 auto;">
      <h1 style="margin-bottom: 2rem;">Upload Incident</h1>
      
      <div class="card" style="margin-bottom: 2rem;">
        <h2>Submit Unstructured Data</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">
          Paste raw, disordered text from an email, log, or document. The AI will analyze it to extract the core issue, assign the department, and set priority.
        </p>
        
        <form @submit.prevent="simulateAIProcessing">
          <div class="form-group">
            <label class="form-label">Raw Incident Text</label>
            <textarea v-model="rawText" class="form-control" rows="6" placeholder="E.g., Customer from Berlin called saying their package #1234 is delayed and the tracker shows it's stuck in transit. They are very angry..." required></textarea>
          </div>
          
          <div v-if="analyzeError" style="color: var(--dhl-red); margin-bottom: 1rem; font-weight: 500;">
            {{ analyzeError }}
          </div>

          <button type="submit" class="btn-primary" :disabled="isAnalyzing">
            <svg v-if="!isAnalyzing" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m21.64 3.64-1.28-1.28a1.21 1.21 0 0 0-1.72 0L2.36 18.64a1.21 1.21 0 0 0 0 1.72l1.28 1.28a1.2 1.2 0 0 0 1.72 0L21.64 5.36a1.2 1.2 0 0 0 0-1.72Z"/><path d="m14 7 3 3"/><path d="M5 6v4"/><path d="M19 14v4"/><path d="M10 2v2"/><path d="M7 8H5"/><path d="M14 20h-2"/></svg>
            {{ isAnalyzing ? 'AI is analyzing...' : 'Analyze with AI' }}
          </button>
        </form>
      </div>

      <div v-if="structuredData" class="card" style="animation: slideUp 0.4s ease;">
        <h2>AI Extracted Information</h2>
        <p style="color: var(--text-secondary); margin-bottom: 1.5rem;">Please review the extracted details before saving to the system.</p>
        
        <form @submit.prevent="saveIncident">
          <div class="form-group">
            <label class="form-label">Issue Summary</label>
            <input type="text" v-model="structuredData.issue_summary" class="form-control" required />
          </div>
          
          <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
              <label class="form-label">Department</label>
              <select v-model="structuredData.department" class="form-control" required>
                <option value="IT">IT</option>
                <option value="Operations">Operations</option>
                <option value="HR">HR</option>
                <option value="Finance">Finance</option>
                <option value="Customer Service">Customer Service</option>
              </select>
            </div>
            <div>
              <label class="form-label">Priority</label>
              <select v-model="structuredData.priority" class="form-control" required>
                <option value="Low">Low</option>
                <option value="Medium">Medium</option>
                <option value="High">High</option>
                <option value="Critical">Critical</option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Recommended Action</label>
            <textarea v-model="structuredData.recommended_action" class="form-control" rows="3"></textarea>
          </div>

          <div v-if="message" :style="{ color: isError ? 'var(--dhl-red)' : 'var(--status-low)', marginBottom: '1rem', fontWeight: '500' }">
            {{ message }}
          </div>

          <button type="submit" class="btn-secondary" :disabled="isSaving" style="width: 100%">
            {{ isSaving ? 'Saving...' : 'Confirm & Save Incident' }}
          </button>
        </form>
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref } from 'vue'
import DashboardLayout from '../components/DashboardLayout.vue'
import { useAuthStore } from '../stores/auth'
import { useRouter } from 'vue-router'

const authStore = useAuthStore()
const router = useRouter()

const rawText = ref('')
const isAnalyzing = ref(false)
const analyzeError = ref('')
const structuredData = ref(null)

const isSaving = ref(false)
const message = ref('')
const isError = ref(false)

const simulateAIProcessing = async () => {
  isAnalyzing.value = true
  analyzeError.value = ''
  structuredData.value = null
  
  try {
    const response = await fetch('http://localhost/DHL/backend/api/analyze.php', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify({ raw_text: rawText.value })
    })
    
    const data = await response.json()
    
    if (data.status === 'success') {
      structuredData.value = data.data
      
      // Ensure default values are valid for dropdowns if AI outputs something unexpected
      const validDepts = ['IT', 'Operations', 'HR', 'Finance', 'Customer Service']
      const validPriorities = ['Low', 'Medium', 'High', 'Critical']
      
      if (!validDepts.includes(structuredData.value.department)) {
        structuredData.value.department = 'Customer Service'
      }
      if (!validPriorities.includes(structuredData.value.priority)) {
        structuredData.value.priority = 'Medium'
      }
      
    } else {
      analyzeError.value = data.message || 'AI Analysis failed.'
    }
  } catch (err) {
    analyzeError.value = 'Network error during AI analysis. Ensure XAMPP is running.'
  } finally {
    isAnalyzing.value = false
  }
}

const saveIncident = async () => {
  isSaving.value = true
  message.value = ''
  
  try {
    const response = await fetch('http://localhost/DHL/backend/api/incidents.php', {
      method: 'POST',
      headers: { 
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${authStore.token}`
      },
      body: JSON.stringify(structuredData.value)
    })
    const data = await response.json()
    
    if (data.status === 'success') {
      message.value = `Incident saved successfully! Ref: ${data.incident_ref_id}`
      isError.value = false
      setTimeout(() => router.push('/incidents'), 1500)
    } else {
      message.value = data.message || 'Failed to save'
      isError.value = true
    }
  } catch (err) {
    message.value = 'Network error'
    isError.value = true
  } finally {
    isSaving.value = false
  }
}
</script>
