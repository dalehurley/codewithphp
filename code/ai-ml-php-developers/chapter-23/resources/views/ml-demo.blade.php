<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ML Demo - Sentiment Analysis</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            text-align: center;
        }

        .header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .content {
            padding: 40px;
        }

        .form-group {
            margin-bottom: 24px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        textarea {
            width: 100%;
            min-height: 150px;
            padding: 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            font-family: inherit;
            transition: border-color 0.2s;
            resize: vertical;
        }

        textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .button-group {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        button {
            flex: 1;
            min-width: 150px;
            padding: 14px 24px;
            font-size: 1rem;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e2e8f0;
            color: #4a5568;
        }

        .btn-secondary:hover {
            background: #cbd5e0;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
        }

        .examples {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-top: 20px;
        }

        .example-btn {
            padding: 12px;
            background: #f7fafc;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            text-align: left;
        }

        .example-btn:hover {
            border-color: #667eea;
            background: #edf2f7;
        }

        .result {
            margin-top: 32px;
            padding: 24px;
            border-radius: 8px;
            display: none;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result.positive {
            background: #f0fdf4;
            border: 2px solid #86efac;
        }

        .result.negative {
            background: #fef2f2;
            border: 2px solid #fca5a5;
        }

        .result.neutral {
            background: #f8fafc;
            border: 2px solid #cbd5e0;
        }

        .result.loading {
            background: #f7fafc;
            border: 2px solid #e2e8f0;
        }

        .result-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .result-emoji {
            font-size: 3rem;
        }

        .result-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a202c;
        }

        .result-details {
            display: grid;
            gap: 12px;
        }

        .result-item {
            display: flex;
            justify-content: space-between;
            padding: 12px;
            background: white;
            border-radius: 6px;
        }

        .result-label {
            font-weight: 600;
            color: #4a5568;
        }

        .result-value {
            color: #1a202c;
            font-weight: 500;
        }

        .confidence-bar {
            width: 100%;
            height: 8px;
            background: #e2e8f0;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 8px;
        }

        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.5s ease;
        }

        .spinner {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid #e2e8f0;
            border-top-color: #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        .error {
            background: #fef2f2;
            border: 2px solid #fca5a5;
            color: #991b1b;
            padding: 16px;
            border-radius: 8px;
            margin-top: 16px;
        }

        .footer {
            padding: 24px 40px;
            background: #f7fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            color: #718096;
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🤖 ML Sentiment Analyzer</h1>
            <p>Real-time product review sentiment analysis powered by PHP-ML</p>
        </div>

        <div class="content">
            <div class="form-group">
                <label for="review-text">Enter Product Review</label>
                <textarea id="review-text" placeholder="Type or paste a product review here... (minimum 10 characters)">This product is absolutely amazing! The quality exceeded my expectations and I highly recommend it to everyone.</textarea>
            </div>

            <div class="button-group">
                <button class="btn-primary" onclick="analyzeReview()">
                    <span id="analyze-text">Analyze Sentiment</span>
                    <span id="analyze-spinner" style="display: none;" class="spinner"></span>
                </button>
                <button class="btn-secondary" onclick="clearResults()">Clear</button>
            </div>

            <div class="form-group">
                <label>Try These Examples:</label>
                <div class="examples">
                    <button class="example-btn" onclick="setExample(0)">
                        ✅ Positive Review
                    </button>
                    <button class="example-btn" onclick="setExample(1)">
                        ❌ Negative Review
                    </button>
                    <button class="example-btn" onclick="setExample(2)">
                        ➖ Neutral Review
                    </button>
                </div>
            </div>

            <div id="result" class="result"></div>
        </div>

        <div class="footer">
            Built with Laravel 11 + PHP-ML | Chapter 23: Integrating AI Models into Web Applications
        </div>
    </div>

    <script>
        const examples = [
            "This product is absolutely fantastic! The quality exceeded my expectations and I'm extremely satisfied. Highly recommend to everyone!",
            "Terrible waste of money. Poor quality, horrible customer service, and completely disappointed. Would not recommend to anyone.",
            "The product arrived on time and matches the description. It's okay for the price, nothing special but does the job."
        ];

        function setExample(index) {
            document.getElementById('review-text').value = examples[index];
            clearResults();
        }

        function clearResults() {
            const result = document.getElementById('result');
            result.style.display = 'none';
            result.innerHTML = '';
            result.className = 'result';
        }

        async function analyzeReview() {
            const textarea = document.getElementById('review-text');
            const text = textarea.value.trim();
            const result = document.getElementById('result');
            const analyzeText = document.getElementById('analyze-text');
            const analyzeSpinner = document.getElementById('analyze-spinner');

            // Validation
            if (text.length < 10) {
                result.className = 'result error';
                result.innerHTML = '<strong>Error:</strong> Please enter at least 10 characters.';
                result.style.display = 'block';
                return;
            }

            // Show loading state
            analyzeText.style.display = 'none';
            analyzeSpinner.style.display = 'inline-block';
            result.className = 'result loading';
            result.innerHTML =
                '<div class="result-header"><span class="spinner"></span><span>Analyzing sentiment...</span></div>';
            result.style.display = 'block';

            try {
                const response = await fetch('/api/ml/sentiment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        text
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.error || `HTTP ${response.status}`);
                }

                displayResult(data.data);
            } catch (error) {
                result.className = 'result error';
                result.innerHTML = `<strong>Error:</strong> ${error.message}`;
            } finally {
                analyzeText.style.display = 'inline';
                analyzeSpinner.style.display = 'none';
            }
        }

        function displayResult(data) {
            const result = document.getElementById('result');
            const confidence = Math.round(data.confidence * 100);

            result.className = `result ${data.sentiment}`;
            result.innerHTML = `
                <div class="result-header">
                    <span class="result-emoji">${data.emoji}</span>
                    <div>
                        <div class="result-title">${capitalizeFirst(data.sentiment)} Sentiment</div>
                        <div style="color: #718096; font-size: 0.9rem;">Confidence: ${confidence}%</div>
                    </div>
                </div>
                <div class="result-details">
                    <div class="result-item">
                        <span class="result-label">Sentiment:</span>
                        <span class="result-value">${capitalizeFirst(data.sentiment)}</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Confidence Score:</span>
                        <span class="result-value">${data.confidence.toFixed(2)} / 1.00</span>
                    </div>
                    <div class="result-item">
                        <span class="result-label">Analysis Time:</span>
                        <span class="result-value">${new Date(data.timestamp).toLocaleTimeString()}</span>
                    </div>
                </div>
                <div style="margin-top: 16px;">
                    <div class="result-label" style="margin-bottom: 8px;">Confidence Visualization:</div>
                    <div class="confidence-bar">
                        <div class="confidence-fill" style="width: ${confidence}%"></div>
                    </div>
                </div>
            `;
            result.style.display = 'block';
        }

        function capitalizeFirst(str) {
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // Allow Enter to submit (Shift+Enter for newline)
        document.getElementById('review-text').addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                analyzeReview();
            }
        });
    </script>
</body>

</html>
