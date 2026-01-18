import DefaultTheme from 'vitepress/theme'
import Quiz from './components/Quiz.vue'
import ProgressTracker from './components/ProgressTracker.vue'
import ChapterCheckbox from './components/ChapterCheckbox.vue'
import IconChevronRight from './components/IconChevronRight.vue'
import IconCheckmark from './components/IconCheckmark.vue'
import HeroSection from './components/HeroSection.vue'
import FeaturesSection from './components/FeaturesSection.vue'
import MissionSection from './components/MissionSection.vue'
import LearningPathsSection from './components/LearningPathsSection.vue'
import TestimonialsSection from './components/TestimonialsSection.vue'
import CTASection from './components/CTASection.vue'
import SocialProofSection from './components/SocialProofSection.vue'
import './custom.css'

export default {
  extends: DefaultTheme,
  enhanceApp({ app }) {
    app.component('Quiz', Quiz)
    app.component('ProgressTracker', ProgressTracker)
    app.component('ChapterCheckbox', ChapterCheckbox)
    app.component('IconChevronRight', IconChevronRight)
    app.component('IconCheckmark', IconCheckmark)
    app.component('HeroSection', HeroSection)
    app.component('FeaturesSection', FeaturesSection)
    app.component('MissionSection', MissionSection)
    app.component('LearningPathsSection', LearningPathsSection)
    app.component('TestimonialsSection', TestimonialsSection)
    app.component('CTASection', CTASection)
    app.component('SocialProofSection', SocialProofSection)
  }
}

