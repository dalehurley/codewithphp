"""
Train a sentiment analysis model using scikit-learn.

This script:
1. Loads training data from CSV
2. Extracts text features using TF-IDF
3. Trains a Naive Bayes classifier
4. Saves the trained model and vectorizer for later use
"""

import sys
import json
import pandas as pd
from pathlib import Path
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import classification_report, accuracy_score
import joblib


def train_sentiment_model(data_path: str, model_dir: str = 'models'):
    """Train and save sentiment analysis model."""
    
    # Create model directory if it doesn't exist
    Path(model_dir).mkdir(parents=True, exist_ok=True)
    
    # Load training data
    print("Loading training data...")
    df = pd.read_csv(data_path)
    print(f"Loaded {len(df)} reviews")
    print(f"Sentiment distribution:\n{df['sentiment'].value_counts()}\n")
    
    # Split data
    X_train, X_test, y_train, y_test = train_test_split(
        df['text'],
        df['sentiment'],
        test_size=0.2,
        random_state=42,
        stratify=df['sentiment']
    )
    
    # Create TF-IDF vectorizer
    print("Creating TF-IDF features...")
    vectorizer = TfidfVectorizer(
        max_features=1000,
        ngram_range=(1, 2),  # unigrams and bigrams
        min_df=2,
        stop_words='english'
    )
    
    X_train_vec = vectorizer.fit_transform(X_train)
    X_test_vec = vectorizer.transform(X_test)
    
    # Train classifier
    print("Training Naive Bayes classifier...")
    classifier = MultinomialNB(alpha=0.1)
    classifier.fit(X_train_vec, y_train)
    
    # Evaluate
    print("\n=== Model Evaluation ===")
    y_pred = classifier.predict(X_test_vec)
    accuracy = accuracy_score(y_test, y_pred)
    print(f"Test Accuracy: {accuracy:.2%}")
    
    print("\nClassification Report:")
    print(classification_report(y_test, y_pred))
    
    # Cross-validation
    cv_scores = cross_val_score(
        classifier,
        X_train_vec,
        y_train,
        cv=5,
        scoring='accuracy'
    )
    print(f"Cross-validation scores: {cv_scores}")
    print(f"Mean CV accuracy: {cv_scores.mean():.2%} (+/- {cv_scores.std() * 2:.2%})")
    
    # Save model and vectorizer
    model_path = Path(model_dir) / 'sentiment_model.pkl'
    vectorizer_path = Path(model_dir) / 'vectorizer.pkl'
    
    print(f"\nSaving model to {model_path}")
    joblib.dump(classifier, model_path)
    joblib.dump(vectorizer, vectorizer_path)
    
    print("✅ Training complete!")
    
    return {
        'accuracy': float(accuracy),
        'cv_mean': float(cv_scores.mean()),
        'cv_std': float(cv_scores.std()),
        'model_path': str(model_path),
        'vectorizer_path': str(vectorizer_path)
    }


def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Usage: python train_model.py <data_path>'}))
        sys.exit(1)
    
    data_path = sys.argv[1]
    
    try:
        result = train_sentiment_model(data_path)
        print(json.dumps(result))
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)


if __name__ == '__main__':
    main()




