<template>
  <div class="progress-tracker">
    <div class="progress-header">
      <div class="title-section">
        <strong>📊 Your Progress</strong>
        <span class="progress-count">{{ completedCount }} / {{ totalChapters }} chapters completed</span>
      </div>
      <span class="progress-percentage">{{ progressPercentage }}%</span>
    </div>
    
    <div class="progress-bar">
      <div 
        class="progress-fill" 
        :style="{ width: progressPercentage + '%' }"
        :class="{ 'progress-complete': progressPercentage === 100 }"
      ></div>
    </div>
    
    <div class="progress-footer">
      <div class="footer-content">
        <p class="footer-message">{{ footerText }}</p>
        <p class="footer-hint">{{ hintText }}</p>
      </div>
      <button 
        v-if="completedCount > 0" 
        @click="handleReset" 
        class="reset-button"
        title="Reset progress for this series"
      >
        🔄 Reset
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
    return '🎉 Congratulations! You\'ve completed this series!'
  }
  if (progressPercentage.value > 0) {
    return `Great progress! Keep going — you're ${progressPercentage.value}% through the series.`
  }
  return 'Track your learning journey as you work through each chapter.'
})

const hintText = computed(() => {
  if (progressPercentage.value === 100) {
    return 'Your progress is saved locally in your browser.'
  }
  if (progressPercentage.value > 0) {
    return 'Tick the checkbox at the bottom of each chapter to mark it complete.'
  }
  return '✓ Tick the checkbox at the bottom of each chapter to mark it as complete and track your progress.'
})

function handleReset() {
  if (confirm(`Reset progress for ${props.seriesId}? This cannot be undone.`)) {
    resetSeriesProgress(props.seriesId)
  }
}
</script>

<style scoped>
.progress-tracker {
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.08) 0%, rgba(5, 150, 105, 0.05) 100%);
  border: 2px solid rgba(13, 148, 136, 0.25);
  border-radius: 12px;
  padding: 1.5rem;
  margin: 2rem 0;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
}

.progress-tracker:hover {
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15);
  border-color: rgba(13, 148, 136, 0.35);
}

.progress-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  gap: 1rem;
}

.title-section {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  flex: 1;
}

.progress-header strong {
  color: var(--vp-c-text-1);
  font-size: 1.1rem;
  font-weight: 600;
}

.progress-count {
  color: var(--vp-c-text-2);
  font-size: 0.9rem;
  font-weight: 500;
}

.progress-percentage {
  color: var(--vp-c-brand-1);
  font-size: 1.75rem;
  font-weight: 700;
  line-height: 1;
  min-width: 4rem;
  text-align: right;
}

.progress-bar {
  height: 12px;
  background: rgba(0, 0, 0, 0.06);
  border-radius: 6px;
  overflow: hidden;
  position: relative;
  margin-bottom: 1rem;
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
}

.progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #0d9488 0%, #14b8a6 50%, #0d9488 100%);
  background-size: 200% 100%;
  border-radius: 6px;
  transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1), background 0.3s ease;
  animation: shimmer 3s ease-in-out infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

.progress-fill.progress-complete {
  background: linear-gradient(90deg, #10b981 0%, #34d399 50%, #10b981 100%);
  background-size: 200% 100%;
  animation: shimmer 3s ease-in-out infinite, pulse 1.5s ease-in-out infinite;
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scaleY(1); }
  50% { opacity: 0.9; transform: scaleY(0.95); }
}

.progress-footer {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 1rem;
}

.footer-content {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.footer-message {
  color: var(--vp-c-text-1);
  font-size: 0.95rem;
  font-weight: 500;
  margin: 0;
  line-height: 1.5;
}

.footer-hint {
  color: var(--vp-c-text-2);
  font-size: 0.85rem;
  margin: 0;
  line-height: 1.5;
}

.reset-button {
  background: transparent;
  border: 1.5px solid rgba(239, 68, 68, 0.3);
  color: #ef4444;
  padding: 0.4rem 1rem;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
  white-space: nowrap;
  flex-shrink: 0;
}

.reset-button:hover {
  background: rgba(239, 68, 68, 0.1);
  border-color: #ef4444;
  transform: translateY(-1px);
  box-shadow: 0 2px 4px rgba(239, 68, 68, 0.2);
}

.reset-button:active {
  transform: translateY(0);
}

/* Dark mode */
.dark .progress-tracker {
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.12) 0%, rgba(5, 150, 105, 0.08) 100%);
  border-color: rgba(13, 148, 136, 0.35);
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
}

.dark .progress-tracker:hover {
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.25);
  border-color: rgba(13, 148, 136, 0.45);
}

.dark .progress-bar {
  background: rgba(255, 255, 255, 0.08);
  box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.3);
}

.dark .reset-button {
  border-color: rgba(239, 68, 68, 0.4);
  color: #fca5a5;
}

.dark .reset-button:hover {
  background: rgba(239, 68, 68, 0.15);
  border-color: #ef4444;
}

@media (max-width: 768px) {
  .progress-percentage {
    font-size: 1.5rem;
    min-width: 3.5rem;
  }
}

@media (max-width: 640px) {
  .progress-tracker {
    padding: 1.25rem;
  }
  
  .progress-header {
    flex-direction: column;
    gap: 0.75rem;
  }
  
  .progress-percentage {
    align-self: flex-end;
    font-size: 1.5rem;
  }
  
  .progress-footer {
    flex-direction: column;
    align-items: stretch;
    gap: 0.75rem;
  }
  
  .reset-button {
    align-self: flex-end;
  }
}
</style>

