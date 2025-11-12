<template>
  <div class="progress-tracker">
    <div class="progress-header">
      <strong>{{ title }}</strong>
      <span class="progress-count">{{ completedCount }} / {{ totalChapters }} chapters</span>
    </div>
    
    <div class="progress-bar">
      <div 
        class="progress-fill" 
        :style="{ width: progressPercentage + '%' }"
        :class="{ 'progress-complete': progressPercentage === 100 }"
      ></div>
    </div>
    
    <div class="progress-footer">
      <small>{{ footerText }}</small>
      <button 
        v-if="completedCount > 0" 
        @click="handleReset" 
        class="reset-button"
        title="Reset progress for this series"
      >
        Reset
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, watch } from 'vue'
import { useProgress } from '../composables/useProgress'

const props = defineProps<{
  seriesId: string
  totalChapters: number
  title?: string
}>()

const { getSeriesProgress, setTotalChapters, resetSeriesProgress, loadProgress } = useProgress()

// Initialize total chapters when component mounts
onMounted(() => {
  loadProgress()
  setTotalChapters(props.seriesId, props.totalChapters)
})

// Watch for changes in totalChapters prop
watch(() => props.totalChapters, (newTotal) => {
  setTotalChapters(props.seriesId, newTotal)
})

const seriesProgress = computed(() => getSeriesProgress(props.seriesId))

const completedCount = computed(() => {
  return seriesProgress.value?.completedChapters || 0
})

const progressPercentage = computed(() => {
  if (props.totalChapters === 0) return 0
  return Math.round((completedCount.value / props.totalChapters) * 100)
})

const footerText = computed(() => {
  if (progressPercentage.value === 100) {
    return '🎉 Congratulations! Series completed!'
  }
  if (progressPercentage.value > 0) {
    return `Keep going! You're ${progressPercentage.value}% done.`
  }
  return 'Progress is saved locally in your browser'
})

function handleReset() {
  if (confirm(`Reset progress for ${props.seriesId}? This cannot be undone.`)) {
    resetSeriesProgress(props.seriesId)
  }
}
</script>

<style scoped>
.progress-tracker {
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
  border: 1px solid rgba(13, 148, 136, 0.2);
  border-radius: 8px;
  padding: 1.25rem;
  margin: 1.5rem 0;
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.progress-header strong {
  color: var(--vp-c-text-1);
  font-size: 1rem;
}

.progress-count {
  color: var(--vp-c-text-2);
  font-size: 0.95rem;
  font-weight: 500;
}

.progress-bar {
  height: 8px;
  background: rgba(0, 0, 0, 0.08);
  border-radius: 4px;
  overflow: hidden;
  position: relative;
  margin-bottom: 0.75rem;
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #0d9488 0%, #14b8a6 100%);
  border-radius: 4px;
  transition: width 0.5s ease, background 0.3s ease;
}

.progress-fill.progress-complete {
  background: linear-gradient(90deg, #10b981 0%, #34d399 100%);
  animation: pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}

.progress-footer {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}

.progress-footer small {
  color: var(--vp-c-text-2);
  font-size: 0.85rem;
  flex: 1;
}

.reset-button {
  background: transparent;
  border: 1px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
  padding: 0.25rem 0.75rem;
  border-radius: 4px;
  font-size: 0.8rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.reset-button:hover {
  background: rgba(239, 68, 68, 0.1);
  border-color: #ef4444;
}

/* Dark mode */
.dark .progress-tracker {
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(0, 0, 0, 0.2) 100%);
  border-color: rgba(13, 148, 136, 0.3);
}

.dark .progress-bar {
  background: rgba(255, 255, 255, 0.08);
}

@media (max-width: 640px) {
  .progress-header {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .progress-footer {
    flex-direction: column;
    align-items: flex-start;
  }
  
  .reset-button {
    align-self: flex-end;
  }
}
</style>

