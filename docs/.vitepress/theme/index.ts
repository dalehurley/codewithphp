import DefaultTheme from 'vitepress/theme'
import Quiz from './components/Quiz.vue'
import ProgressTracker from './components/ProgressTracker.vue'
import ChapterCheckbox from './components/ChapterCheckbox.vue'
import './custom.css'

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    app.component('Quiz', Quiz)
    app.component('ProgressTracker', ProgressTracker)
    app.component('ChapterCheckbox', ChapterCheckbox)
  }
}

