"""
Exercise 1 Solution: Multi-Model Sentiment Analyzer

This script trains three different classifiers and selects the best one:
- Naive Bayes (baseline)
- Logistic Regression
- Linear SVM (LinearSVC)

Usage:
    python3 exercise1-train.py <data_path>
"""

import sys
import json
import pandas as pd
from pathlib import Path
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.naive_bayes import MultinomialNB
from sklearn.linear_model import LogisticRegression
from sklearn.svm import LinearSVC
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import classification_report, accuracy_score, precision_recall_fscore_support
import joblib
import warnings
warnings.filterwarnings('ignore')


def train_and_evaluate_model(name, model, X_train, X_test, y_train, y_test):
    """Train a model and return its performance metrics."""
    print(f"\n{'='*60}")
    print(f"Training {name}")
    print('='*60)
    
    # Train
    model.fit(X_train, y_train)
    
    # Predict
    y_pred = model.predict(X_test)
    
    # Calculate metrics
    accuracy = accuracy_score(y_test, y_pred)
    precision, recall, f1, _ = precision_recall_fscore_support(
        y_test, y_pred, average='weighted', zero_division=0
    )
    
    # Cross-validation
    cv_scores = cross_val_score(model, X_train, y_train, cv=5, scoring='accuracy')
    cv_mean = cv_scores.mean()
    cv_std = cv_scores.std()
    
    print(f"Test Accuracy: {accuracy:.4f}")
    print(f"Precision: {precision:.4f}")
    print(f"Recall: {recall:.4f}")
    print(f"F1-Score: {f1:.4f}")
    print(f"CV Accuracy: {cv_mean:.4f} (+/- {cv_std*2:.4f})")
    
    print(f"\nDetailed Classification Report:")
    print(classification_report(y_test, y_pred))
    
    return {
        'name': name,
        'model': model,
        'accuracy': float(accuracy),
        'precision': float(precision),
        'recall': float(recall),
        'f1': float(f1),
        'cv_mean': float(cv_mean),
        'cv_std': float(cv_std)
    }


def main():
    if len(sys.argv) < 2:
        print(json.dumps({'error': 'Usage: python exercise1-train.py <data_path>'}))
        sys.exit(1)
    
    data_path = sys.argv[1]
    model_dir = 'models'
    
    try:
        # Create model directory
        Path(model_dir).mkdir(parents=True, exist_ok=True)
        
        # Load data
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
            ngram_range=(1, 2),
            min_df=2,
            stop_words='english'
        )
        
        X_train_vec = vectorizer.fit_transform(X_train)
        X_test_vec = vectorizer.transform(X_test)
        
        # Define models to compare
        models = [
            ('Naive Bayes', MultinomialNB(alpha=0.1)),
            ('Logistic Regression', LogisticRegression(max_iter=1000, random_state=42)),
            ('Linear SVM', LinearSVC(max_iter=2000, random_state=42))
        ]
        
        # Train and evaluate each model
        results = []
        for name, model in models:
            result = train_and_evaluate_model(
                name, model, X_train_vec, X_test_vec, y_train, y_test
            )
            results.append(result)
        
        # Find best model
        print(f"\n{'='*60}")
        print("MODEL COMPARISON")
        print('='*60)
        print(f"{'Model':<25} {'Accuracy':<12} {'F1-Score':<12} {'CV Accuracy':<15}")
        print('-'*60)
        
        best_model = None
        best_score = 0
        
        for result in results:
            print(f"{result['name']:<25} {result['accuracy']:<12.4f} "
                  f"{result['f1']:<12.4f} {result['cv_mean']:<15.4f}")
            
            # Use F1-score as primary metric (balances precision/recall)
            if result['f1'] > best_score:
                best_score = result['f1']
                best_model = result
        
        print('='*60)
        print(f"\n🏆 Best Model: {best_model['name']}")
        print(f"   F1-Score: {best_model['f1']:.4f}")
        print(f"   Accuracy: {best_model['accuracy']:.4f}")
        print(f"   CV Accuracy: {best_model['cv_mean']:.4f} (+/- {best_model['cv_std']*2:.4f})")
        
        # Save best model
        model_path = Path(model_dir) / 'sentiment_model.pkl'
        vectorizer_path = Path(model_dir) / 'vectorizer.pkl'
        
        print(f"\nSaving best model ({best_model['name']}) to {model_path}")
        joblib.dump(best_model['model'], model_path)
        joblib.dump(vectorizer, vectorizer_path)
        
        # Save model comparison results
        comparison_path = Path(model_dir) / 'model_comparison.json'
        with open(comparison_path, 'w') as f:
            comparison_data = {
                'best_model': best_model['name'],
                'results': [
                    {k: v for k, v in r.items() if k != 'model'}
                    for r in results
                ]
            }
            json.dump(comparison_data, f, indent=2)
        
        print(f"Saved model comparison to {comparison_path}")
        print("\n✅ Training complete!")
        
        # Output result for PHP
        output = {
            'best_model': best_model['name'],
            'accuracy': best_model['accuracy'],
            'f1_score': best_model['f1'],
            'cv_mean': best_model['cv_mean'],
            'model_path': str(model_path),
            'all_results': [
                {'name': r['name'], 'accuracy': r['accuracy'], 'f1': r['f1']}
                for r in results
            ]
        }
        print(json.dumps(output))
        
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)


if __name__ == '__main__':
    main()




