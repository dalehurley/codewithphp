#!/usr/bin/env python3
"""
YOLOv8 Object Detection Script

Accepts image path as argument, runs detection, outputs JSON results.
Returns array of detections with bounding boxes, classes, and confidence scores.
"""

import sys
import json
from pathlib import Path
from ultralytics import YOLO

def detect_objects(image_path: str, model_name: str = 'yolov8n.pt', confidence_threshold: float = 0.25):
    """
    Detect objects in image using YOLO.

    Args:
        image_path: Path to image file
        model_name: YOLO model to use (yolov8n/s/m/l/x)
        confidence_threshold: Minimum confidence for detections

    Returns:
        List of detections with format:
        {
            'class': 'person',
            'confidence': 0.95,
            'bbox': {'x': 100, 'y': 50, 'width': 200, 'height': 300}
        }
    """
    try:
        # Load YOLO model (downloads on first run)
        model = YOLO(model_name)

        # Run inference
        results = model(image_path, conf=confidence_threshold, verbose=False)

        # Parse results
        detections = []

        for result in results:
            boxes = result.boxes

            for i in range(len(boxes)):
                # Get bounding box (xyxy format)
                x1, y1, x2, y2 = boxes.xyxy[i].tolist()

                # Convert to xywh format
                x = int(x1)
                y = int(y1)
                width = int(x2 - x1)
                height = int(y2 - y1)

                # Get class and confidence
                class_id = int(boxes.cls[i])
                confidence = float(boxes.conf[i])
                class_name = model.names[class_id]

                detections.append({
                    'class': class_name,
                    'confidence': confidence,
                    'bbox': {
                        'x': x,
                        'y': y,
                        'width': width,
                        'height': height
                    }
                })

        return {
            'success': True,
            'detections': detections,
            'count': len(detections),
            'image_path': str(image_path),
            'model': model_name
        }

    except FileNotFoundError:
        return {
            'success': False,
            'error': f'Image not found: {image_path}'
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
            'error': 'Usage: python3 detect_yolo.py <image_path> [model_name] [confidence]'
        }))
        sys.exit(1)

    image_path = sys.argv[1]
    model_name = sys.argv[2] if len(sys.argv) > 2 else 'yolov8n.pt'
    confidence = float(sys.argv[3]) if len(sys.argv) > 3 else 0.25

    result = detect_objects(image_path, model_name, confidence)
    print(json.dumps(result, indent=2))

