<template>
  <div 
    ref="containerRef"
    class="chapter-checkbox-container" 
    :class="{ 
      'completed': isCompleted,
      'auto-completed': wasAutoCompleted 
    }"
  >
    <div class="checkbox-icon-container">
      <svg v-if="isCompleted" class="trophy-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M5.166 2.621v.858c-1.035.148-2.059.33-3.071.543a.75.75 0 00-.584.859 6.753 6.753 0 006.138 5.6 6.73 6.73 0 002.743 1.346A6.707 6.707 0 019.279 15H8.54c-1.036 0-1.875.84-1.875 1.875V19.5h-.75a2.25 2.25 0 00-2.25 2.25c0 .414.336.75.75.75h15a.75.75 0 00.75-.75 2.25 2.25 0 00-2.25-2.25h-.75v-2.625c0-1.036-.84-1.875-1.875-1.875h-.739a6.706 6.706 0 01-1.112-3.173 6.73 6.73 0 002.743-1.347 6.753 6.753 0 006.139-5.6.75.75 0 00-.585-.858 47.077 47.077 0 00-3.07-.543V2.62a.75.75 0 00-.658-.744 49.22 49.22 0 00-6.093-.377c-2.063 0-4.096.128-6.093.377a.75.75 0 00-.657.744z" />
      </svg>
      <svg v-else class="book-icon" viewBox="0 0 24 24" fill="currentColor">
        <path d="M11.25 4.533A9.707 9.707 0 006 3a9.735 9.735 0 00-3.25.555.75.75 0 00-.5.707v14.25a.75.75 0 001 .707A8.237 8.237 0 016 18.75c1.995 0 3.823.707 5.25 1.886V4.533zM12.75 20.636A8.214 8.214 0 0118 18.75c.966 0 1.89.166 2.75.47a.75.75 0 001-.708V4.262a.75.75 0 00-.5-.707A9.735 9.735 0 0018 3a9.707 9.707 0 00-5.25 1.533v16.103z" />
      </svg>
    </div>
    
    <label class="chapter-checkbox">
      <input 
        type="checkbox" 
        :checked="isCompleted"
        @change="handleToggle"
      />
      <span class="checkbox-content">
        <span class="checkbox-label">
          <svg v-if="isCompleted" class="check-icon" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
          <span class="label-text">{{ effectiveLabel }}</span>
        </span>
        <span class="checkbox-hint">{{ hintText }}</span>
      </span>
    </label>
    
    <transition name="fade">
      <div v-if="showConfirmation" class="confirmation-message">
        <svg class="confirm-icon" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        {{ confirmationText }}
      </div>
    </transition>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useProgress } from '../composables/useProgress'

const props = defineProps<{
  seriesId: string
  chapterId: string
  label?: string
}>()

const { isChapterComplete, toggleChapterCompletion, loadProgress } = useProgress()
const showConfirmation = ref(false)
const containerRef = ref<HTMLElement | null>(null)
const wasAutoCompleted = ref(false)
let observer: IntersectionObserver | null = null

onMounted(() => {
  loadProgress()
  setupAutoCompletion()
})

onUnmounted(() => {
  if (observer) {
    observer.disconnect()
  }
})

const isCompleted = computed(() => {
  return isChapterComplete(props.seriesId, props.chapterId)
})

const effectiveLabel = computed(() => {
  return props.label || 'Mark this chapter as complete'
})

const hintText = computed(() => {
  if (isCompleted.value) {
    if (wasAutoCompleted.value) {
      return '✓ Auto-completed — You scrolled through the entire chapter! Progress saved locally.'
    }
    return '✓ Completed — Great work! Your progress is saved locally.'
  }
  return 'Check the box when you\'ve finished reading, or scroll to the bottom to auto-complete.'
})

const confirmationText = computed(() => {
  if (isCompleted.value) {
    if (wasAutoCompleted.value) {
      return '🎉 Chapter auto-completed!'
    }
    return 'Chapter completed!'
  }
  return 'Chapter unmarked'
})

function setupAutoCompletion() {
  if (!containerRef.value || typeof IntersectionObserver === 'undefined') {
    return
  }

  // Create observer that triggers when the checkbox is fully in view
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        // Check if element is fully in view (100% visible)
        if (entry.isIntersecting && entry.intersectionRatio >= 0.95) {
          // Only auto-complete if not already completed
          if (!isCompleted.value) {
            // Wait a bit to ensure user has actually read the content
            setTimeout(() => {
              // Double check they're still viewing it and haven't already manually checked
              if (entry.isIntersecting && !isCompleted.value) {
                wasAutoCompleted.value = true
                toggleChapterCompletion(props.seriesId, props.chapterId)
                
                // Show confirmation message
                showConfirmation.value = true
                setTimeout(() => {
                  showConfirmation.value = false
                }, 3000) // Show for 3 seconds for auto-completion
              }
            }, 1500) // Wait 1.5 seconds of viewing
          }
        }
      })
    },
    {
      threshold: [0.95], // Trigger when 95% visible
      rootMargin: '0px'
    }
  )

  observer.observe(containerRef.value)
}

function handleToggle() {
  wasAutoCompleted.value = false // Reset auto-complete flag on manual toggle
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
  margin: 3rem 0;
  padding: 1.5rem 1.75rem 1.5rem 5rem;
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.1) 0%, rgba(5, 150, 105, 0.08) 100%);
  border: 3px solid rgba(13, 148, 136, 0.3);
  border-left: 6px solid rgba(13, 148, 136, 0.5);
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  animation: pulse-border 3s ease-in-out infinite;
}

@keyframes pulse-border {
  0%, 100% {
    border-color: rgba(13, 148, 136, 0.3);
    box-shadow: 0 4px 12px rgba(13, 148, 136, 0.15), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  }
  50% {
    border-color: rgba(13, 148, 136, 0.5);
    box-shadow: 0 6px 16px rgba(13, 148, 136, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  }
}

.chapter-checkbox-container:hover {
  border-color: rgba(13, 148, 136, 0.6);
  border-left-color: rgba(13, 148, 136, 0.8);
  box-shadow: 0 8px 20px rgba(13, 148, 136, 0.25), inset 0 1px 0 rgba(255, 255, 255, 0.15);
  transform: translateY(-2px) scale(1.01);
  animation: none;
}

.chapter-checkbox-container.completed {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(5, 150, 105, 0.12) 100%);
  border: 3px solid rgba(16, 185, 129, 0.4);
  border-left: 6px solid rgba(16, 185, 129, 0.7);
  animation: pulse-complete 2s ease-in-out infinite;
}

@keyframes pulse-complete {
  0%, 100% {
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2), inset 0 1px 0 rgba(255, 255, 255, 0.1);
  }
  50% {
    box-shadow: 0 6px 16px rgba(16, 185, 129, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.15);
  }
}

.chapter-checkbox-container.completed:hover {
  border-color: rgba(16, 185, 129, 0.6);
  border-left-color: rgba(16, 185, 129, 0.9);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.2);
}

.chapter-checkbox-container.auto-completed {
  animation: celebrate 0.6s ease-out;
}

@keyframes celebrate {
  0% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.05);
  }
  100% {
    transform: scale(1);
  }
}

.checkbox-icon-container {
  position: absolute;
  left: 1.25rem;
  top: 50%;
  transform: translateY(-50%);
  width: 48px;
  height: 48px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.15) 0%, rgba(5, 150, 105, 0.1) 100%);
  border: 2px solid rgba(13, 148, 136, 0.25);
  transition: all 0.3s ease;
}

.completed .checkbox-icon-container {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.2) 0%, rgba(5, 150, 105, 0.15) 100%);
  border-color: rgba(16, 185, 129, 0.4);
  animation: icon-spin 0.6s ease-out;
}

@keyframes icon-spin {
  0% {
    transform: translateY(-50%) rotate(0deg) scale(1);
  }
  50% {
    transform: translateY(-50%) rotate(180deg) scale(1.2);
  }
  100% {
    transform: translateY(-50%) rotate(360deg) scale(1);
  }
}

.book-icon {
  width: 28px;
  height: 28px;
  color: rgba(13, 148, 136, 0.7);
  transition: all 0.3s ease;
}

.chapter-checkbox-container:hover .book-icon {
  color: rgba(13, 148, 136, 1);
  transform: scale(1.1);
}

.trophy-icon {
  width: 32px;
  height: 32px;
  color: #f59e0b;
  filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.3));
  animation: trophy-shine 2s ease-in-out infinite;
}

@keyframes trophy-shine {
  0%, 100% {
    filter: drop-shadow(0 2px 4px rgba(245, 158, 11, 0.3));
  }
  50% {
    filter: drop-shadow(0 4px 8px rgba(245, 158, 11, 0.6));
  }
}

.chapter-checkbox {
  display: flex;
  align-items: flex-start;
  cursor: pointer;
  user-select: none;
  gap: 0.75rem;
}

.chapter-checkbox input[type="checkbox"] {
  width: 24px;
  height: 24px;
  min-width: 24px;
  cursor: pointer;
  accent-color: #0d9488;
  margin-top: 0.1rem;
  transition: transform 0.2s ease;
}

.chapter-checkbox input[type="checkbox"]:hover {
  transform: scale(1.15);
}

.chapter-checkbox input[type="checkbox"]:checked {
  accent-color: #10b981;
}

.checkbox-content {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
  flex: 1;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-weight: 700;
  font-size: 1.15rem;
  color: var(--vp-c-text-1);
  transition: color 0.2s ease;
  text-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.label-text {
  line-height: 1.4;
}

.chapter-checkbox:hover .checkbox-label {
  color: var(--vp-c-brand-1);
}

.checkbox-hint {
  font-size: 0.9rem;
  color: var(--vp-c-text-2);
  line-height: 1.5;
  margin-left: 0;
  font-weight: 500;
}

.completed .checkbox-hint {
  color: #059669;
  font-weight: 600;
}

.check-icon {
  width: 20px;
  height: 20px;
  min-width: 20px;
  color: #10b981;
  animation: checkmark 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes checkmark {
  0% {
    transform: scale(0) rotate(-45deg);
    opacity: 0;
  }
  50% {
    transform: scale(1.3) rotate(10deg);
  }
  100% {
    transform: scale(1) rotate(0deg);
    opacity: 1;
  }
}

.confirmation-message {
  position: absolute;
  top: 50%;
  right: 1.5rem;
  transform: translateY(-50%);
  background: linear-gradient(135deg, #10b981 0%, #059669 100%);
  color: white;
  padding: 0.65rem 1.25rem;
  border-radius: 6px;
  font-size: 0.9rem;
  font-weight: 600;
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
  display: flex;
  align-items: center;
  gap: 0.5rem;
  z-index: 10;
}

.confirm-icon {
  width: 20px;
  height: 20px;
  min-width: 20px;
}

.fade-enter-active {
  transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.fade-leave-active {
  transition: all 0.2s ease;
}

.fade-enter-from {
  opacity: 0;
  transform: translateY(-50%) scale(0.8);
}

.fade-leave-to {
  opacity: 0;
  transform: translateY(-50%) scale(0.9);
}

/* Dark mode */
.dark .chapter-checkbox-container {
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.15) 0%, rgba(5, 150, 105, 0.1) 100%);
  border-color: rgba(13, 148, 136, 0.4);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.4), inset 0 1px 0 rgba(255, 255, 255, 0.05);
}

.dark .chapter-checkbox-container:hover {
  border-color: rgba(13, 148, 136, 0.6);
  box-shadow: 0 8px 20px rgba(13, 148, 136, 0.3), inset 0 1px 0 rgba(255, 255, 255, 0.08);
}

.dark .chapter-checkbox-container.completed {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.18) 0%, rgba(5, 150, 105, 0.12) 100%);
  border-color: rgba(16, 185, 129, 0.5);
  border-left-color: rgba(16, 185, 129, 0.7);
}

.dark .chapter-checkbox-container.completed:hover {
  border-color: rgba(16, 185, 129, 0.7);
  border-left-color: rgba(16, 185, 129, 0.9);
  box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35), inset 0 1px 0 rgba(255, 255, 255, 0.1);
}

.dark .checkbox-icon-container {
  background: linear-gradient(135deg, rgba(13, 148, 136, 0.2) 0%, rgba(5, 150, 105, 0.15) 100%);
  border-color: rgba(13, 148, 136, 0.35);
}

.dark .completed .checkbox-icon-container {
  background: linear-gradient(135deg, rgba(16, 185, 129, 0.25) 0%, rgba(5, 150, 105, 0.2) 100%);
  border-color: rgba(16, 185, 129, 0.5);
}

.dark .book-icon {
  color: rgba(13, 148, 136, 0.9);
}

.dark .chapter-checkbox-container:hover .book-icon {
  color: rgba(20, 184, 166, 1);
}

.dark .completed .checkbox-hint {
  color: #6ee7b7;
}

.dark .confirmation-message {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

@media (max-width: 768px) {
  .chapter-checkbox-container {
    padding: 1.5rem 1.5rem 1.5rem 4.5rem;
  }
  
  .checkbox-icon-container {
    left: 1rem;
    width: 42px;
    height: 42px;
  }
  
  .book-icon {
    width: 24px;
    height: 24px;
  }
  
  .trophy-icon {
    width: 28px;
    height: 28px;
  }
  
  .checkbox-label {
    font-size: 1.05rem;
  }
  
  .confirmation-message {
    right: 1rem;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
  }
  
  .confirm-icon {
    width: 18px;
    height: 18px;
  }
}

@media (max-width: 640px) {
  .chapter-checkbox-container {
    padding: 1.25rem 1rem 1.25rem 4rem;
    margin: 2rem 0;
  }
  
  .checkbox-icon-container {
    left: 0.75rem;
    width: 38px;
    height: 38px;
  }
  
  .book-icon {
    width: 22px;
    height: 22px;
  }
  
  .trophy-icon {
    width: 26px;
    height: 26px;
  }
  
  .checkbox-label {
    font-size: 1rem;
  }
  
  .checkbox-hint {
    font-size: 0.825rem;
  }
  
  .confirmation-message {
    position: static;
    margin-top: 1rem;
    transform: none;
    width: 100%;
    justify-content: center;
  }
  
  .fade-enter-from {
    transform: scale(0.9);
  }
  
  .fade-leave-to {
    transform: scale(0.9);
  }
}
</style>

