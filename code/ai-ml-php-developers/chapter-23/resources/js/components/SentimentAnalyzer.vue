<template>
  <div class="sentiment-analyzer">
    <div class="analyzer-card">
      <div class="analyzer-header">
        <h3>{{ title }}</h3>
        <span v-if="analyzed" class="analyzed-count">
          {{ analyzedCount }} analyzed
        </span>
      </div>

      <div class="analyzer-body">
        <textarea
          v-model="text"
          :placeholder="placeholder"
          :rows="rows"
          class="analyzer-textarea"
          @input="onInput"
        />

        <div v-if="error" class="analyzer-error">
          {{ error }}
        </div>

        <div class="analyzer-actions">
          <button
            @click="analyze"
            :disabled="!canAnalyze || loading"
            class="btn btn-primary"
          >
            <span v-if="!loading">{{ analyzeButtonText }}</span>
            <span v-else class="spinner"></span>
          </button>

          <button
            v-if="text"
            @click="clear"
            class="btn btn-secondary"
          >
            Clear
          </button>
        </div>

        <transition name="fade">
          <div
            v-if="result"
            class="analyzer-result"
            :class="`result-${result.sentiment}`"
          >
            <div class="result-header">
              <span class="result-emoji">{{ result.emoji }}</span>
              <div class="result-info">
                <div class="result-sentiment">
                  {{ capitalize(result.sentiment) }}
                </div>
                <div class="result-confidence">
                  Confidence: {{ confidencePercent }}%
                </div>
              </div>
            </div>

            <div class="confidence-bar">
              <div
                class="confidence-fill"
                :style="{ width: confidencePercent + '%' }"
              />
            </div>

            <div v-if="showTimestamp" class="result-timestamp">
              Analyzed at {{ formattedTimestamp }}
            </div>
          </div>
        </transition>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'SentimentAnalyzer',

  props: {
    title: {
      type: String,
      default: 'Sentiment Analysis'
    },
    placeholder: {
      type: String,
      default: 'Enter text to analyze (minimum 10 characters)...'
    },
    rows: {
      type: Number,
      default: 4
    },
    apiEndpoint: {
      type: String,
      default: '/api/ml/sentiment'
    },
    autoAnalyze: {
      type: Boolean,
      default: false
    },
    autoAnalyzeDelay: {
      type: Number,
      default: 1000
    },
    showTimestamp: {
      type: Boolean,
      default: false
    },
    analyzeButtonText: {
      type: String,
      default: 'Analyze Sentiment'
    }
  },

  data() {
    return {
      text: '',
      result: null,
      loading: false,
      error: null,
      analyzedCount: 0,
      autoAnalyzeTimeout: null
    }
  },

  computed: {
    canAnalyze() {
      return this.text.trim().length >= 10
    },

    analyzed() {
      return this.analyzedCount > 0
    },

    confidencePercent() {
      return this.result ? Math.round(this.result.confidence * 100) : 0
    },

    formattedTimestamp() {
      if (!this.result?.timestamp) return ''
      return new Date(this.result.timestamp).toLocaleTimeString()
    }
  },

  methods: {
    onInput() {
      this.error = null

      if (this.autoAnalyze && this.canAnalyze) {
        clearTimeout(this.autoAnalyzeTimeout)
        this.autoAnalyzeTimeout = setTimeout(() => {
          this.analyze()
        }, this.autoAnalyzeDelay)
      }
    },

    async analyze() {
      if (!this.canAnalyze) {
        this.error = 'Text must be at least 10 characters'
        return
      }

      this.loading = true
      this.error = null

      try {
        const response = await fetch(this.apiEndpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
          },
          body: JSON.stringify({ text: this.text })
        })

        const data = await response.json()

        if (!response.ok) {
          throw new Error(data.error || data.message || 'Analysis failed')
        }

        this.result = data.data
        this.analyzedCount++
        this.$emit('analyzed', this.result)
      } catch (err) {
        this.error = err.message
        this.result = null
        this.$emit('error', err)
      } finally {
        this.loading = false
      }
    },

    clear() {
      this.text = ''
      this.result = null
      this.error = null
      clearTimeout(this.autoAnalyzeTimeout)
      this.$emit('cleared')
    },

    capitalize(str) {
      return str.charAt(0).toUpperCase() + str.slice(1)
    }
  },

  beforeUnmount() {
    clearTimeout(this.autoAnalyzeTimeout)
  }
}
</script>

<style scoped>
.sentiment-analyzer {
  width: 100%;
}

.analyzer-card {
  background: white;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
  overflow: hidden;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.analyzer-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e2e8f0;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.analyzer-header h3 {
  margin: 0;
  font-size: 1.25rem;
  font-weight: 600;
}

.analyzed-count {
  font-size: 0.9rem;
  opacity: 0.9;
}

.analyzer-body {
  padding: 20px;
}

.analyzer-textarea {
  width: 100%;
  padding: 12px;
  border: 2px solid #e2e8f0;
  border-radius: 8px;
  font-size: 1rem;
  font-family: inherit;
  resize: vertical;
  transition: border-color 0.2s;
}

.analyzer-textarea:focus {
  outline: none;
  border-color: #667eea;
}

.analyzer-error {
  margin-top: 12px;
  padding: 12px;
  background: #fef2f2;
  border: 1px solid #fca5a5;
  border-radius: 6px;
  color: #991b1b;
  font-size: 0.9rem;
}

.analyzer-actions {
  margin-top: 16px;
  display: flex;
  gap: 12px;
}

.btn {
  padding: 10px 20px;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-primary {
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  color: white;
}

.btn-primary:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

.btn-secondary {
  background: #e2e8f0;
  color: #4a5568;
}

.btn-secondary:hover {
  background: #cbd5e0;
}

.spinner {
  display: inline-block;
  width: 16px;
  height: 16px;
  border: 2px solid #ffffff33;
  border-top-color: white;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.analyzer-result {
  margin-top: 20px;
  padding: 20px;
  border-radius: 8px;
  border: 2px solid;
}

.result-positive {
  background: #f0fdf4;
  border-color: #86efac;
}

.result-negative {
  background: #fef2f2;
  border-color: #fca5a5;
}

.result-neutral {
  background: #f8fafc;
  border-color: #cbd5e0;
}

.result-header {
  display: flex;
  align-items: center;
  gap: 16px;
}

.result-emoji {
  font-size: 3rem;
  line-height: 1;
}

.result-info {
  flex: 1;
}

.result-sentiment {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a202c;
}

.result-confidence {
  font-size: 0.95rem;
  color: #4a5568;
  margin-top: 4px;
}

.confidence-bar {
  margin-top: 16px;
  height: 8px;
  background: #e2e8f0;
  border-radius: 4px;
  overflow: hidden;
}

.confidence-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  transition: width 0.5s ease;
}

.result-timestamp {
  margin-top: 12px;
  font-size: 0.85rem;
  color: #718096;
  text-align: right;
}

.fade-enter-active, .fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter-from, .fade-leave-to {
  opacity: 0;
}
</style>

