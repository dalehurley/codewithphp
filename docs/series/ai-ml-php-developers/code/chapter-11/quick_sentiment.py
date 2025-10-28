#!/usr/bin/env python3
"""
Quick Start: Python Sentiment Analyzer

This standalone script demonstrates how Python can process data from PHP.
It performs simple sentiment analysis using word matching.

Usage (called from PHP):
    python3 quick_sentiment.py '{"text":"This is great!"}'

For real production use, you'd use scikit-learn or similar ML library.
"""

import sys
import json


def analyze_sentiment(text):
    """
    Simple sentiment analysis using keyword matching.
    
    In production, use scikit-learn, TensorFlow, or similar ML library.
    This is just for demonstration purposes.
    """
    positive_words = ['amazing', 'excellent', 'great', 'love', 'recommend', 
                      'fantastic', 'wonderful', 'perfect', 'best', 'awesome']
    negative_words = ['terrible', 'awful', 'hate', 'worst', 'disappointing',
                      'horrible', 'bad', 'poor', 'waste', 'useless']
    
    text_lower = text.lower()
    
    # Count positive and negative words
    positive_count = sum(1 for word in positive_words if word in text_lower)
    negative_count = sum(1 for word in negative_words if word in text_lower)
    
    # Determine sentiment
    if positive_count > negative_count:
        return {
            'sentiment': 'positive',
            'confidence': min(0.85 + (positive_count * 0.05), 0.99)
        }
    elif negative_count > positive_count:
        return {
            'sentiment': 'negative',
            'confidence': min(0.85 + (negative_count * 0.05), 0.99)
        }
    else:
        return {
            'sentiment': 'neutral',
            'confidence': 0.60
        }


def main():
    """Main entry point for the script."""
    try:
        # Check if data was provided
        if len(sys.argv) < 2:
            print(json.dumps({'error': 'No data provided'}))
            sys.exit(1)
        
        # Parse JSON input from PHP
        input_data = json.loads(sys.argv[1])
        
        # Validate input
        if 'text' not in input_data:
            print(json.dumps({'error': 'Missing "text" field'}))
            sys.exit(1)
        
        text = input_data['text']
        
        if not text or not text.strip():
            print(json.dumps({'error': 'Text cannot be empty'}))
            sys.exit(1)
        
        # Analyze sentiment
        result = analyze_sentiment(text)
        
        # Return result as JSON (PHP will parse this)
        print(json.dumps(result))
        
    except json.JSONDecodeError as e:
        print(json.dumps({'error': f'Invalid JSON: {str(e)}'}))
        sys.exit(1)
    except Exception as e:
        print(json.dumps({'error': str(e)}))
        sys.exit(1)


if __name__ == '__main__':
    main()



