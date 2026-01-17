#!/usr/bin/env python3
"""
API Service Example - Flask Prediction Server

Demonstrates a Python microservice that PHP can call via HTTP.

To run: python3 api-server.py
Then test with: php api-client.php
"""

from flask import Flask, request, jsonify
import numpy as np

app = Flask(__name__)

@app.route('/health', methods=['GET'])
def health():
    """Health check endpoint."""
    return jsonify({'status': 'healthy', 'service': 'prediction-api'})

@app.route('/predict', methods=['POST'])
def predict():
    """Prediction endpoint."""
    try:
        data = request.get_json()
        
        # Validate input
        if not data or 'age' not in data or 'income' not in data:
            return jsonify({'error': 'Missing required fields: age, income'}), 400
        
        age = float(data['age'])
        income = float(data['income'])
        
        # Simple model
        score = (age * 0.5) + (income / 1000)
        
        # Return prediction
        return jsonify({
            'score': round(score, 2),
            'input': {'age': age, 'income': income},
            'model': 'simple-linear'
        })
        
    except ValueError as e:
        return jsonify({'error': f'Invalid data type: {str(e)}'}), 400
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/batch', methods=['POST'])
def batch_predict():
    """Batch prediction endpoint."""
    try:
        data = request.get_json()
        
        if not data or 'records' not in data:
            return jsonify({'error': 'Missing records array'}), 400
        
        results = []
        for record in data['records']:
            age = float(record['age'])
            income = float(record['income'])
            score = (age * 0.5) + (income / 1000)
            results.append({'score': round(score, 2)})
        
        return jsonify({'predictions': results, 'count': len(results)})
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting prediction API server...")
    print("Health check: http://localhost:5000/health")
    print("Prediction: POST http://localhost:5000/predict")
    print("Batch: POST http://localhost:5000/batch")
    app.run(host='0.0.0.0', port=5000, debug=True)


