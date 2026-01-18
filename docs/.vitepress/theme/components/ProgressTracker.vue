<template>
  <div class="bg-[linear-gradient(135deg,rgba(13,148,136,0.08)_0%,rgba(5,150,105,0.05)_100%)] dark:bg-[linear-gradient(135deg,rgba(13,148,136,0.12)_0%,rgba(5,150,105,0.08)_100%)] border-2 border-[rgba(13,148,136,0.25)] dark:border-[rgba(13,148,136,0.35)] rounded-xl p-6 my-8 shadow-[0_2px_8px_rgba(0,0,0,0.05)] dark:shadow-[0_2px_8px_rgba(0,0,0,0.3)] transition-all duration-300 hover:shadow-[0_4px_12px_rgba(13,148,136,0.15)] dark:hover:shadow-[0_4px_12px_rgba(13,148,136,0.25)] hover:border-[rgba(13,148,136,0.35)] dark:hover:border-[rgba(13,148,136,0.45)] sm:p-5">
    <div class="flex justify-between items-start mb-4 gap-4 sm:flex-col sm:gap-3">
      <div class="flex flex-col gap-[0.35rem] flex-1">
        <strong class="text-[var(--vp-c-text-1)] text-[1.1rem] font-semibold">📊 Your Progress</strong>
        <span class="text-[var(--vp-c-text-2)] text-sm font-medium">{{ completedCount }} / {{ totalChapters }} chapters completed</span>
      </div>
      <span class="text-[var(--vp-c-brand-1)] text-[1.75rem] sm:text-2xl font-bold leading-none min-w-[4rem] text-right sm:self-end sm:text-2xl">{{ progressPercentage }}%</span>
    </div>
    
    <div class="h-3 bg-[rgba(0,0,0,0.06)] dark:bg-[rgba(255,255,255,0.08)] rounded-md overflow-hidden relative mb-4 shadow-[inset_0_1px_3px_rgba(0,0,0,0.1)] dark:shadow-[inset_0_1px_3px_rgba(0,0,0,0.3)]">
      <div 
        class="progress-fill h-full rounded-md transition-[width] duration-[0.6s] ease-[cubic-bezier(0.4,0,0.2,1)]"
        :class="{ 'progress-complete': progressPercentage === 100 }"
        :style="{ width: progressPercentage + '%' }"
      ></div>
    </div>
    
    <div class="flex justify-between items-start gap-4 sm:flex-col sm:items-stretch sm:gap-3">
      <div class="flex flex-col gap-1 flex-1">
        <p class="text-[var(--vp-c-text-1)] text-[0.95rem] font-medium m-0 leading-[1.5]">{{ footerText }}</p>
        <p class="text-[var(--vp-c-text-2)] text-[0.85rem] m-0 leading-[1.5]">{{ hintText }}</p>
      </div>
      <button 
        v-if="completedCount > 0" 
        @click="handleReset" 
        class="bg-transparent border-[1.5px] border-[rgba(239,68,68,0.3)] dark:border-[rgba(239,68,68,0.4)] text-[#ef4444] dark:text-[#fca5a5] py-[0.4rem] px-4 rounded-md text-[0.85rem] font-medium cursor-pointer transition-all duration-200 whitespace-nowrap flex-shrink-0 hover:bg-[rgba(239,68,68,0.1)] dark:hover:bg-[rgba(239,68,68,0.15)] hover:border-[#ef4444] hover:-translate-y-0.5 hover:shadow-[0_2px_4px_rgba(239,68,68,0.2)] active:translate-y-0 sm:self-end"
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
.progress-fill {
  background: linear-gradient(90deg, #0d9488 0%, #14b8a6 50%, #0d9488 100%);
  background-size: 200% 100%;
  animation: shimmer 3s ease-in-out infinite;
}

.progress-fill.progress-complete {
  background: linear-gradient(90deg, #10b981 0%, #34d399 50%, #10b981 100%);
  background-size: 200% 100%;
  animation: shimmer 3s ease-in-out infinite, pulse 1.5s ease-in-out infinite;
}

@keyframes shimmer {
  0% { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

@keyframes pulse {
  0%, 100% { opacity: 1; transform: scaleY(1); }
  50% { opacity: 0.9; transform: scaleY(0.95); }
}
</style>

