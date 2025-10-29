#!/usr/bin/env python3
"""
ONNX Image Classification Inference Script
Performs image classification using ONNX Runtime and MobileNetV2
Called from PHP via shell_exec for local model inference
"""

import sys
import json
import numpy as np
from PIL import Image
import onnxruntime as ort

def preprocess_image(image_path):
    """
    Preprocess image for MobileNetV2 model:
    - Resize to 224x224
    - Convert to RGB
    - Normalize with ImageNet mean/std
    - Transpose to CHW format (channels first)
    - Add batch dimension
    
    Args:
        image_path: Path to input image file
        
    Returns:
        numpy.ndarray: Preprocessed image tensor
    """
    try:
        img = Image.open(image_path).convert('RGB')
    except Exception as e:
        raise ValueError(f"Failed to load image: {e}")
    
    # Resize to model input size
    img = img.resize((224, 224), Image.BILINEAR)
    
    # Convert to numpy array and normalize to [0, 1]
    img_array = np.array(img).astype(np.float32) / 255.0
    
    # Apply ImageNet normalization
    mean = np.array([0.485, 0.456, 0.406])
    std = np.array([0.229, 0.224, 0.225])
    img_array = (img_array - mean) / std
    
    # Transpose from HWC (Height, Width, Channels) to CHW
    img_array = np.transpose(img_array, (2, 0, 1))
    
    # Add batch dimension: (C, H, W) -> (1, C, H, W)
    img_array = np.expand_dims(img_array, axis=0)
    
    return img_array

def softmax(x):
    """
    Compute softmax values for array x
    
    Args:
        x: Input array
        
    Returns:
        numpy.ndarray: Softmax probabilities
    """
    exp_x = np.exp(x - np.max(x))
    return exp_x / exp_x.sum()

def classify_image(model_path, image_path, labels_path, top_k=5):
    """
    Classify an image using ONNX model
    
    Args:
        model_path: Path to ONNX model file
        image_path: Path to image to classify
        labels_path: Path to JSON file containing class labels
        top_k: Number of top predictions to return
        
    Returns:
        list: List of dicts with 'label' and 'confidence' keys
    """
    # Load ONNX model
    try:
        session = ort.InferenceSession(
            model_path,
            providers=['CPUExecutionProvider']
        )
    except Exception as e:
        raise RuntimeError(f"Failed to load ONNX model: {e}")
    
    # Preprocess image
    try:
        input_data = preprocess_image(image_path)
    except Exception as e:
        raise RuntimeError(f"Failed to preprocess image: {e}")
    
    # Get input name from model
    input_name = session.get_inputs()[0].name
    
    # Run inference
    try:
        outputs = session.run(None, {input_name: input_data})
        predictions = outputs[0][0]  # Remove batch dimension
    except Exception as e:
        raise RuntimeError(f"Inference failed: {e}")
    
    # Apply softmax to get probabilities
    probabilities = softmax(predictions)
    
    # Get top-K predictions
    top_indices = np.argsort(probabilities)[-top_k:][::-1]
    
    # Load class labels
    try:
        with open(labels_path, 'r') as f:
            labels = json.load(f)
    except Exception as e:
        raise RuntimeError(f"Failed to load labels: {e}")
    
    # Build results
    results = []
    for idx in top_indices:
        if idx < len(labels):
            results.append({
                'label': labels[idx],
                'confidence': float(probabilities[idx])
            })
    
    return results

def main():
    """Main entry point for CLI usage"""
    if len(sys.argv) < 4:
        error_response = {
            'error': 'Usage: python3 onnx_inference.py <model_path> <image_path> <labels_path> [top_k]'
        }
        print(json.dumps(error_response))
        sys.exit(1)
    
    model_path = sys.argv[1]
    image_path = sys.argv[2]
    labels_path = sys.argv[3]
    top_k = int(sys.argv[4]) if len(sys.argv) > 4 else 5
    
    try:
        results = classify_image(model_path, image_path, labels_path, top_k)
        print(json.dumps(results, indent=2))
        sys.exit(0)
    except Exception as e:
        error_response = {'error': str(e)}
        print(json.dumps(error_response))
        sys.exit(1)

if __name__ == '__main__':
    main()

