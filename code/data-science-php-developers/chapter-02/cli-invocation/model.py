#!/usr/bin/env python3
"""
CLI Invocation Example - Python Model Script

Demonstrates a Python script that can be called from PHP.
"""

import sys
import json

def predict(features):
    """Simple prediction model."""
    age = features['age']
    income = features['income']
    
    # Simple scoring formula
    score = (age * 0.5) + (income / 1000)
    
    return {
        'score': round(score, 2),
        'age': age,
        'income': income
    }

if __name__ == '__main__':
    try:
        # Read input from command line argument
        if len(sys.argv) < 2:
            print(json.dumps({'error': 'Missing input argument'}), file=sys.stderr)
            sys.exit(1)
        
        input_json = sys.argv[1]
        features = json.loads(input_json)
        
        # Validate input
        if 'age' not in features or 'income' not in features:
            print(json.dumps({'error': 'Missing required fields'}), file=sys.stderr)
            sys.exit(1)
        
        # Make prediction
        result = predict(features)
        
        # Output JSON for PHP
        print(json.dumps(result))
        sys.exit(0)
        
    except json.JSONDecodeError as e:
        print(json.dumps({'error': f'Invalid JSON: {str(e)}'}), file=sys.stderr)
        sys.exit(1)
    except Exception as e:
        print(json.dumps({'error': str(e)}), file=sys.stderr)
        sys.exit(1)


