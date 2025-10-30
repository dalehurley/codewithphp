@props(['text' => '', 'inline' => false])

<div class="sentiment-widget" x-data="sentimentWidget('{{ $text }}')" x-init="init()">
    @if ($inline)
        <!-- Inline compact version -->
        <div class="sentiment-inline">
            <input type="text" x-model="text" @input.debounce.500ms="analyze()" placeholder="Enter text to analyze..."
                class="sentiment-input">
            <div x-show="loading" class="sentiment-loading">
                <span class="spinner"></span>
            </div>
            <div x-show="result && !loading" class="sentiment-badge" :class="result.sentiment">
                <span x-text="result.emoji"></span>
                <span x-text="result.sentiment"></span>
            </div>
        </div>
    @else
        <!-- Full widget version -->
        <div class="sentiment-card">
            <div class="sentiment-header">
                <h3>Sentiment Analysis</h3>
                <button @click="analyze()" :disabled="loading || text.length < 10" class="analyze-btn">
                    <span x-show="!loading">Analyze</span>
                    <span x-show="loading" class="spinner"></span>
                </button>
            </div>

            <div class="sentiment-body">
                <textarea x-model="text" placeholder="Enter text to analyze (minimum 10 characters)..." rows="4"
                    class="sentiment-textarea"></textarea>

                <div x-show="error" x-text="error" class="sentiment-error"></div>

                <div x-show="result && !loading" class="sentiment-result" :class="result.sentiment">
                    <div class="sentiment-result-header">
                        <span class="sentiment-emoji" x-text="result.emoji"></span>
                        <div>
                            <div class="sentiment-label" x-text="result.sentiment"></div>
                            <div class="sentiment-confidence"
                                x-text="`Confidence: ${(result.confidence * 100).toFixed(0)}%`"></div>
                        </div>
                    </div>
                    <div class="sentiment-bar">
                        <div class="sentiment-bar-fill" :style="`width: ${result.confidence * 100}%`"></div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<style>
    .sentiment-widget {
        width: 100%;
    }

    /* Inline version styles */
    .sentiment-inline {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sentiment-input {
        flex: 1;
        padding: 8px 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.95rem;
    }

    .sentiment-badge {
        padding: 6px 12px;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .sentiment-badge.positive {
        background: #dcfce7;
        color: #166534;
    }

    .sentiment-badge.negative {
        background: #fee2e2;
        color: #991b1b;
    }

    .sentiment-badge.neutral {
        background: #f3f4f6;
        color: #374151;
    }

    /* Full widget styles */
    .sentiment-card {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }

    .sentiment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
    }

    .sentiment-header h3 {
        margin: 0;
        font-size: 1.1rem;
        color: #1a202c;
    }

    .analyze-btn {
        padding: 8px 16px;
        background: #667eea;
        color: white;
        border: none;
        border-radius: 6px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }

    .analyze-btn:hover:not(:disabled) {
        background: #5a67d8;
    }

    .analyze-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .sentiment-body {
        padding: 16px;
    }

    .sentiment-textarea {
        width: 100%;
        padding: 12px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        font-size: 0.95rem;
        resize: vertical;
        font-family: inherit;
    }

    .sentiment-error {
        margin-top: 12px;
        padding: 12px;
        background: #fef2f2;
        border: 1px solid #fca5a5;
        border-radius: 6px;
        color: #991b1b;
        font-size: 0.9rem;
    }

    .sentiment-result {
        margin-top: 16px;
        padding: 16px;
        border-radius: 8px;
    }

    .sentiment-result.positive {
        background: #f0fdf4;
        border: 2px solid #86efac;
    }

    .sentiment-result.negative {
        background: #fef2f2;
        border: 2px solid #fca5a5;
    }

    .sentiment-result.neutral {
        background: #f8fafc;
        border: 2px solid #cbd5e0;
    }

    .sentiment-result-header {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sentiment-emoji {
        font-size: 2rem;
    }

    .sentiment-label {
        font-size: 1.2rem;
        font-weight: 700;
        text-transform: capitalize;
    }

    .sentiment-confidence {
        font-size: 0.9rem;
        color: #4a5568;
    }

    .sentiment-bar {
        margin-top: 12px;
        height: 6px;
        background: #e2e8f0;
        border-radius: 3px;
        overflow: hidden;
    }

    .sentiment-bar-fill {
        height: 100%;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transition: width 0.5s ease;
    }

    .sentiment-loading {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .spinner {
        display: inline-block;
        width: 16px;
        height: 16px;
        border: 2px solid #e2e8f0;
        border-top-color: #667eea;
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }
</style>

<script>
    function sentimentWidget(initialText = '') {
        return {
            text: initialText,
            loading: false,
            result: null,
            error: null,

            init() {
                if (this.text && this.text.length >= 10) {
                    this.analyze();
                }
            },

            async analyze() {
                if (this.text.length < 10) {
                    this.error = 'Text must be at least 10 characters';
                    return;
                }

                this.loading = true;
                this.error = null;

                try {
                    const response = await fetch('/api/ml/sentiment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            text: this.text
                        })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Analysis failed');
                    }

                    this.result = data.data;
                } catch (err) {
                    this.error = err.message;
                    this.result = null;
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
