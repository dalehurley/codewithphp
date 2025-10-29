#!/usr/bin/env python3
"""
OpenCV Face Detection Script

Uses Haar Cascades for fast face detection without ML models.
Privacy-friendly: runs completely offline.
"""

import sys
import json
import cv2
from pathlib import Path

def detect_faces(image_path: str, scale_factor: float = 1.1, min_neighbors: int = 5):
    """
    Detect faces using OpenCV Haar Cascades.

    Args:
        image_path: Path to image file
        scale_factor: How much image size is reduced at each scale (1.1 = 10%)
        min_neighbors: Minimum neighbors for detection (higher = fewer false positives)

    Returns:
        Detection results in JSON format
    """
    try:
        # Load image
        image = cv2.imread(image_path)

        if image is None:
            return {
                'success': False,
                'error': f'Failed to load image: {image_path}'
            }

        # Convert to grayscale (Haar Cascades work on grayscale)
        gray = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)

        # Load Haar Cascade classifier for frontal faces
        cascade_path = cv2.data.haarcascades + 'haarcascade_frontalface_default.xml'
        face_cascade = cv2.CascadeClassifier(cascade_path)

        if face_cascade.empty():
            return {
                'success': False,
                'error': 'Failed to load Haar Cascade classifier'
            }

        # Detect faces
        faces = face_cascade.detectMultiScale(
            gray,
            scaleFactor=scale_factor,
            minNeighbors=min_neighbors,
            minSize=(30, 30)
        )

        # Format results
        detections = []
        for (x, y, w, h) in faces:
            detections.append({
                'class': 'face',
                'confidence': 0.85,  # Haar Cascades don't provide confidence scores
                'bbox': {
                    'x': int(x),
                    'y': int(y),
                    'width': int(w),
                    'height': int(h)
                }
            })

        return {
            'success': True,
            'detections': detections,
            'count': len(detections),
            'image_path': str(image_path),
            'method': 'OpenCV Haar Cascades'
        }

    except Exception as e:
        return {
            'success': False,
            'error': str(e)
        }

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({
            'success': False,
            'error': 'Usage: python3 detect_opencv.py <image_path> [scale_factor] [min_neighbors]'
        }))
        sys.exit(1)

    image_path = sys.argv[1]
    scale_factor = float(sys.argv[2]) if len(sys.argv) > 2 else 1.1
    min_neighbors = int(sys.argv[3]) if len(sys.argv) > 3 else 5

    result = detect_faces(image_path, scale_factor, min_neighbors)
    print(json.dumps(result, indent=2))

