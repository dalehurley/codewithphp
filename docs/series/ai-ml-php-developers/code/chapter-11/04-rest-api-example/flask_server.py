"""
Flask REST API for sentiment analysis.

This approach is more scalable than shell execution:
- Python process stays alive (no startup overhead)
- Can handle concurrent requests
- Standard HTTP protocol
- Easy to deploy separately and scale horizontally
"""

from flask import Flask, request, jsonify
from pathlib import Path
import joblib
import sys
import os

# Add parent directory to path to import model loading logic
sys.path.insert(0, os.path.join(os.path.dirname(__file__), '..', '03-sentiment-analysis'))

app = Flask(__name__)

# Load model at startup (not per request!)
MODEL_DIR = '../03-sentiment-analysis/models'
classifier = None
vectorizer = None


def load_models():
    """Load trained models into memory."""
    global classifier, vectorizer
    
    model_path = Path(MODEL_DIR) / 'sentiment_model.pkl'
    vectorizer_path = Path(MODEL_DIR) / 'vectorizer.pkl'
    
    if not model_path.exists():
        raise FileNotFoundError(
            f"Model not found at {model_path}. "
            "Run training first: cd ../03-sentiment-analysis && php analyze.php"
        )
    
    classifier = joblib.load(model_path)
    vectorizer = joblib.load(vectorizer_path)
    print("✅ Models loaded successfully")


@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint."""
    return jsonify({
        'status': 'healthy',
        'model_loaded': classifier is not None
    })


@app.route('/predict', methods=['POST'])
def predict():
    """Predict sentiment for given text."""
    try:
        # Parse request
        data = request.get_json()
        
        if not data or 'text' not in data:
            return jsonify({'error': 'Missing "text" field'}), 400
        
        text = data['text']
        
        if not text or not text.strip():
            return jsonify({'error': 'Text cannot be empty'}), 400
        
        # Transform and predict
        text_vec = vectorizer.transform([text])
        prediction = classifier.predict(text_vec)[0]
        probabilities = classifier.predict_proba(text_vec)[0]
        
        # Build response
        confidence_scores = {
            cls: float(prob)
            for cls, prob in zip(classifier.classes_, probabilities)
        }
        
        return jsonify({
            'text': text,
            'sentiment': prediction,
            'confidence': float(probabilities.max()),
            'all_scores': confidence_scores
        })
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500


@app.route('/predict/batch', methods=['POST'])
def predict_batch():
    """Predict sentiments for multiple texts."""
    try:
        data = request.get_json()
        
        if not data or 'texts' not in data:
            return jsonify({'error': 'Missing "texts" array'}), 400
        
        texts = data['texts']
        
        if not isinstance(texts, list):
            return jsonify({'error': '"texts" must be an array'}), 400
        
        # Transform all texts at once (efficient)
        texts_vec = vectorizer.transform(texts)
        predictions = classifier.predict(texts_vec)
        probabilities = classifier.predict_proba(texts_vec)
        
        # Build results
        results = []
        for text, pred, probs in zip(texts, predictions, probabilities):
            results.append({
                'text': text,
                'sentiment': pred,
                'confidence': float(probs.max())
            })
        
        return jsonify({'predictions': results})
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500


if __name__ == '__main__':
    print("Starting Flask ML API...")
    load_models()
    app.run(host='127.0.0.1', port=5000, debug=True)



