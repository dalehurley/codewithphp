<template>
  <div class="chapter-checkbox-container">
    <label class="chapter-checkbox">
      <input 
        type="checkbox" 
        :checked="isCompleted"
        @change="handleToggle"
      />
      <span class="checkbox-label">
        <svg v-if="isCompleted" class="check-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
        {{ label }}
      </span>
    </label>
    <transition name="fade">
      <div v-if="showConfirmation" class="confirmation-message">
        ✓ Progress saved!
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useProgress } from '../composables/useProgress'

const props = defineProps<{
  seriesId: string
  chapterId: string
  label?: string
}>()

const { isChapterComplete, toggleChapterCompletion, loadProgress } = useProgress()
const showConfirmation = ref(false)

onMounted(() => {
  loadProgress()
})

const isCompleted = computed(() => {
  return isChapterComplete(props.seriesId, props.chapterId)
})

function handleToggle() {
  toggleChapterCompletion(props.seriesId, props.chapterId)
  
  // Show confirmation message
  showConfirmation.value = true
  setTimeout(() => {
    showConfirmation.value = false
  }, 2000)
}
</script>

<style scoped>
.chapter-checkbox-container {
  position: relative;
  margin: 1.5rem 0;
  padding: 1rem;
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.05) 0%, rgba(255, 255, 255, 0.02) 100%);
  border: 1px solid rgba(13, 148, 136, 0.2);
  border-radius: 8px;
}

.chapter-checkbox {
  display: flex;
  align-items: center;
  cursor: pointer;
  user-select: none;
}

.chapter-checkbox input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #0d9488;
  margin-right: 0.75rem;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 500;
  color: var(--vp-c-text-1);
  transition: color 0.2s ease;
}

.chapter-checkbox:hover .checkbox-label {
  color: var(--vp-c-brand);
}

.check-icon {
  width: 18px;
  height: 18px;
  color: #10b981;
  animation: checkmark 0.3s ease-in-out;
}

@keyframes checkmark {
  0% {
    transform: scale(0);
    opacity: 0;
  }
  50% {
    transform: scale(1.2);
  }
  100% {
    transform: scale(1);
    opacity: 1;
  }
}

.confirmation-message {
  position: absolute;
  top: 50%;
  right: 1rem;
  transform: translateY(-50%);
  background: #10b981;
  color: white;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 500;
  box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}

/* Dark mode */
.dark .chapter-checkbox-container {
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(0, 0, 0, 0.2) 100%);
  border-color: rgba(13, 148, 136, 0.3);
}

@media (max-width: 640px) {
  .confirmation-message {
    position: static;
    margin-top: 0.75rem;
    transform: none;
  }
}
</style>

