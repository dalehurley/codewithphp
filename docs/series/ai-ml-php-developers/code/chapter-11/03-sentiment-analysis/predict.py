"""
Use trained sentiment model to predict sentiment of new text.

This script:
1. Loads the trained model and vectorizer
2. Receives text from PHP
3. Transforms text using TF-IDF
4. Predicts sentiment
5. Returns prediction with confidence scores
"""

import sys
import json
from pathlib import Path
import joblib
import numpy as np


def load_model(model_dir: str = 'models'):
    """Load trained model and vectorizer."""
    model_path = Path(model_dir) / 'sentiment_model.pkl'
    vectorizer_path = Path(model_dir) / 'vectorizer.pkl'
    
    if not model_path.exists() or not vectorizer_path.exists():
        raise FileNotFoundError(
            f"Model files not found in {model_dir}. "
            "Run train_model.py first."
        )
    
    classifier = joblib.load(model_path)
    vectorizer = joblib.load(vectorizer_path)
    
    return classifier, vectorizer


def predict_sentiment(text: str, classifier, vectorizer):
    """Predict sentiment for given text."""
    # Transform text to TF-IDF features
    text_vec = vectorizer.transform([text])
    
    # Predict sentiment
    prediction = classifier.predict(text_vec)[0]
    
    # Get probability scores for all classes
    probabilities = classifier.predict_proba(text_vec)[0]
    classes = classifier.classes_
    
    # Build confidence scores
    confidence_scores = {
        cls: float(prob) 
        for cls, prob in zip(classes, probabilities)
    }
    
    return {
        'text': text,
        'sentiment': prediction,
        'confidence': float(probabilities.max()),
        'all_scores': confidence_scores
    }


def main():
    try:
        # Parse input from PHP
        if len(sys.argv) < 2:
            raise ValueError('No input data provided')
        
        input_data = json.loads(sys.argv[1])
        text = input_data.get('text', '')
        
        if not text:
            raise ValueError('Text field is required')
        
        # Load model
        classifier, vectorizer = load_model()
        
        # Make prediction
        result = predict_sentiment(text, classifier, vectorizer)
        
        # Return result
        print(json.dumps(result))
        
    except Exception as e:
        error_result = {
            'error': str(e),
            'type': type(e).__name__
        }
        print(json.dumps(error_result))
        sys.exit(1)


if __name__ == '__main__':
    main()



