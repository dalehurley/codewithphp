import { ref, computed, onMounted } from 'vue'

export interface ChapterProgress {
  seriesId: string
  chapterId: string
  completed: boolean
  completedAt?: string
}

export interface SeriesProgress {
  seriesId: string
  totalChapters: number
  completedChapters: number
  chapters: Record<string, boolean>
}

const STORAGE_KEY = 'codewithphp_progress'

/**
 * Progress tracking composable using localStorage
 */
export function useProgress() {
  const progress = ref<Record<string, SeriesProgress>>({})

  // Initialize progress from localStorage
  onMounted(() => {
    loadProgress()
  })

  function loadProgress() {
    try {
      const stored = localStorage.getItem(STORAGE_KEY)
      if (stored) {
        progress.value = JSON.parse(stored)
      }
    } catch (error) {
      console.error('Failed to load progress:', error)
      progress.value = {}
    }
  }

  function saveProgress() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(progress.value))
    } catch (error) {
      console.error('Failed to save progress:', error)
    }
  }

  function toggleChapterCompletion(seriesId: string, chapterId: string) {
    if (!progress.value[seriesId]) {
      progress.value[seriesId] = {
        seriesId,
        totalChapters: 0,
        completedChapters: 0,
        chapters: {}
      }
    }

    const series = progress.value[seriesId]
    const wasCompleted = series.chapters[chapterId] || false
    series.chapters[chapterId] = !wasCompleted

    // Recalculate completed count
    series.completedChapters = Object.values(series.chapters).filter(Boolean).length

    saveProgress()
  }

  function markChapterComplete(seriesId: string, chapterId: string) {
    if (!progress.value[seriesId]) {
      progress.value[seriesId] = {
        seriesId,
        totalChapters: 0,
        completedChapters: 0,
        chapters: {}
      }
    }

    const series = progress.value[seriesId]
    if (!series.chapters[chapterId]) {
      series.chapters[chapterId] = true
      series.completedChapters = Object.values(series.chapters).filter(Boolean).length
      saveProgress()
    }
  }

  function markChapterIncomplete(seriesId: string, chapterId: string) {
    if (!progress.value[seriesId]) return

    const series = progress.value[seriesId]
    if (series.chapters[chapterId]) {
      series.chapters[chapterId] = false
      series.completedChapters = Object.values(series.chapters).filter(Boolean).length
      saveProgress()
    }
  }

  function getSeriesProgress(seriesId: string): SeriesProgress | null {
    return progress.value[seriesId] || null
  }

  function isChapterComplete(seriesId: string, chapterId: string): boolean {
    return progress.value[seriesId]?.chapters[chapterId] || false
  }

  function setTotalChapters(seriesId: string, total: number) {
    if (!progress.value[seriesId]) {
      progress.value[seriesId] = {
        seriesId,
        totalChapters: total,
        completedChapters: 0,
        chapters: {}
      }
    } else {
      progress.value[seriesId].totalChapters = total
    }
    saveProgress()
  }

  function resetSeriesProgress(seriesId: string) {
    if (progress.value[seriesId]) {
      progress.value[seriesId].chapters = {}
      progress.value[seriesId].completedChapters = 0
      saveProgress()
    }
  }

  function resetAllProgress() {
    if (confirm('Are you sure you want to reset all progress? This cannot be undone.')) {
      progress.value = {}
      saveProgress()
    }
  }

  function exportProgress(): string {
    return JSON.stringify(progress.value, null, 2)
  }

  function importProgress(data: string) {
    try {
      const imported = JSON.parse(data)
      progress.value = imported
      saveProgress()
      return true
    } catch (error) {
      console.error('Failed to import progress:', error)
      return false
    }
  }

  return {
    progress: computed(() => progress.value),
    toggleChapterCompletion,
    markChapterComplete,
    markChapterIncomplete,
    getSeriesProgress,
    isChapterComplete,
    setTotalChapters,
    resetSeriesProgress,
    resetAllProgress,
    exportProgress,
    importProgress,
    loadProgress
  }
}

