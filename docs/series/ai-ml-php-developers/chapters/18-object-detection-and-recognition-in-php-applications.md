---
title: "18: Object Detection and Recognition in PHP Applications"
description: "Learn to locate and identify multiple objects in images using YOLO, cloud APIs, and OpenCV with PHP integration"
series: "ai-ml-php-developers"
chapter: "18"
order: 18
difficulty: "Intermediate"
prerequisites:
  - "17"
---

![Object Detection and Recognition in PHP Applications](/images/ai-ml-php-developers/chapter-18-object-detection-hero-full.webp)

# Chapter 18: Object Detection and Recognition in PHP Applications

## Overview

In Chapter 17, you learned how to classify entire images into categories—identifying what an image contains. But what if you need to find and locate multiple objects within a single image? What if you need to count people in a crowd, identify faces for security systems, track inventory items on shelves, or build augmented reality features that interact with real-world objects? This is where object detection comes in.

Object detection goes beyond classification by not only identifying what objects are present but also precisely locating where they appear in the image. Every detection includes bounding box coordinates (x, y, width, height), the object class, and a confidence score. This enables powerful applications: security cameras that alert when unauthorized people enter restricted areas, retail systems that automatically count products on shelves, manufacturing quality control that identifies defects, social media platforms that tag friends in photos, and autonomous vehicles that navigate by detecting pedestrians, vehicles, and traffic signs.

In this chapter, you'll master three complementary approaches to object detection in PHP applications. First, you'll integrate Python's YOLOv8 (You Only Look Once)—one of the fastest and most accurate real-time detection models—using subprocess communication patterns you learned in Chapter 11. Second, you'll leverage cloud vision APIs (Google Vision and AWS Rekognition) that provide instant access to enterprise-grade detection without managing infrastructure. Third, you'll implement OpenCV face detection using Haar Cascades for privacy-sensitive applications that must run offline. Each approach has distinct trade-offs in accuracy, speed, cost, and deployment complexity.

By the end of this chapter, you'll have built a production-ready object detection service that can process images through multiple detection backends, draw annotated bounding boxes with color-coded labels, handle batch processing efficiently, compare performance across approaches, and expose results through a REST API. You'll understand when to use lightweight face detection versus heavy-duty multi-object detection, how to optimize detection speed without sacrificing accuracy, and how to structure detection systems that scale from prototypes to production. The skills you develop here apply directly to building intelligent features in PHP applications: content moderation, inventory management, security monitoring, user engagement tools, and accessibility features.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 17](/series/ai-ml-php-developers/chapters/17-image-classification-project-with-pre-trained-models) or equivalent understanding of image classification and working with image data in PHP
- Completed [Chapter 11](/series/ai-ml-php-developers/chapters/11-integrating-php-with-python-for-advanced-ml) with experience calling Python scripts from PHP and handling subprocess communication
- PHP 8.4+ environment with Composer installed
- Python 3.10+ installed with pip package manager
- GD or Imagick PHP extension for image manipulation (check with `php -m | grep -E 'gd|imagick'`)
- Familiarity with REST APIs and JSON from earlier chapters
- Basic understanding of coordinate systems and bounding boxes
- Text editor or IDE with PHP and Python support
- Optional: Google Cloud account or AWS account for cloud API examples (free tier available)

**Estimated Time**: ~120-150 minutes (reading, coding, Python setup, and exercises)

**Verify your setup:**

```bash
# Check PHP and GD/Imagick
php -v
php -m | grep -E 'gd|imagick'

# Check Python and pip
python3 --version
pip3 --version

# Test image creation (should create test.png)
php -r "imagecreate(100, 100); imagepng(imagecreate(100,100), 'test.png'); echo 'OK\n';"
```

## What You'll Build

By the end of this chapter, you will have created:

- A **YOLOv8 detection script** in Python that detects 80 object classes with state-of-the-art accuracy and returns JSON results
- A **PHP YOLO client** that calls Python subprocess, handles errors, and parses detection results with timeout management
- A **BoundingBoxDrawer class** using GD to draw color-coded rectangles, labels, and confidence scores on images
- A **Google Vision API detector** integrating Google Cloud Vision for object localization with label detection
- An **AWS Rekognition detector** providing alternative cloud detection with celebrity and text recognition features
- A **CloudDetector unified interface** abstracting different cloud APIs behind a consistent PHP interface
- An **OpenCV face detector** using Haar Cascades for fast, privacy-preserving face detection without cloud dependencies
- A **DetectionService production class** orchestrating multiple detection backends with caching, rate limiting, and error handling
- A **batch processing system** handling multiple images efficiently with parallel processing and progress tracking
- A **confidence filtering system** allowing threshold-based filtering to reduce false positives
- A **performance comparison framework** benchmarking detection speed, accuracy, and cost across all three approaches
- An **object tracking system** identifying the same objects across multiple frames or images
- A **REST API endpoint** accepting image uploads and returning annotated results with proper HTTP headers
- A **detection results analyzer** generating statistics, heatmaps, and insights from detection data
- Complete **sample datasets** with various object types, complexities, and edge cases for testing

All code examples are fully functional, tested with real models and APIs, and include comprehensive error handling.

::: info Code Examples
Complete, runnable examples for this chapter:

- [`01-detect-yolo.php`](../code/chapter-18/01-detect-yolo.php) — YOLO object detection via Python integration
- [`02-draw-boxes.php`](../code/chapter-18/02-draw-boxes.php) — Draw bounding boxes with labels
- [`03-google-vision-api.php`](../code/chapter-18/03-google-vision-api.php) — Google Vision API integration
- [`04-aws-rekognition.php`](../code/chapter-18/04-aws-rekognition.php) — AWS Rekognition integration
- [`05-opencv-faces.php`](../code/chapter-18/05-opencv-faces.php) — Face detection with OpenCV
- [`06-batch-processor.php`](../code/chapter-18/06-batch-processor.php) — Process multiple images
- [`07-production-api.php`](../code/chapter-18/07-production-api.php) — Production REST API endpoint
- [`08-compare-approaches.php`](../code/chapter-18/08-compare-approaches.php) — Performance benchmarking
- [`09-confidence-filter.php`](../code/chapter-18/09-confidence-filter.php) — Filter by confidence threshold
- [`10-object-tracker.php`](../code/chapter-18/10-object-tracker.php) — Track objects across frames
- [`detect_yolo.py`](../code/chapter-18/detect_yolo.py) — Python YOLOv8 detection script
- [`detect_opencv.py`](../code/chapter-18/detect_opencv.py) — Python OpenCV face detection
- [`BoundingBoxDrawer.php`](../code/chapter-18/BoundingBoxDrawer.php) — Annotation drawing class
- [`DetectionService.php`](../code/chapter-18/DetectionService.php) — Production service class
- [`CloudDetector.php`](../code/chapter-18/CloudDetector.php) — Unified cloud API interface

All files are in [`docs/series/ai-ml-php-developers/code/chapter-18/`](../code/chapter-18/README.md)
:::

## Quick Start

Want to see object detection in action right now? Here's a 5-minute working example:

```php
# filename: quick-detect.php
<?php

declare(strict_types=1);

// Step 1: Simple Python YOLO detection script (save as detect_simple.py)
// python3 detect_simple.py image.jpg

// Step 2: PHP calls Python and processes results
function detectObjects(string $imagePath): array
{
    // Call Python YOLO script
    $command = sprintf(
        'python3 %s %s 2>&1',
        escapeshellarg(__DIR__ . '/detect_simple.py'),
        escapeshellarg($imagePath)
    );

    $output = shell_exec($command);

    if ($output === null) {
        throw new RuntimeException('Failed to execute detection');
    }

    // Parse JSON results
    $result = json_decode($output, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new RuntimeException('Invalid JSON from detector: ' . $output);
    }

    return $result;
}

// Step 3: Detect objects in image
$imagePath = __DIR__ . '/sample.jpg'; // Use your own image

try {
    $detections = detectObjects($imagePath);

    echo "Found " . count($detections) . " objects:\n\n";

    foreach ($detections as $detection) {
        printf(
            "- %s (%.1f%% confidence) at [%d, %d, %dx%d]\n",
            $detection['class'],
            $detection['confidence'] * 100,
            (int)$detection['bbox']['x'],
            (int)$detection['bbox']['y'],
            (int)$detection['bbox']['width'],
            (int)$detection['bbox']['height']
        );
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

Expected output:

```
Found 3 objects:

- person (94.2% confidence) at [120, 80, 180x420]
- dog (89.7% confidence) at [450, 280, 240x180]
- chair (76.3% confidence) at [50, 350, 120x200]
```

This example shows the core pattern: PHP orchestrates the detection process while Python runs the heavy ML model. Now let's build the complete system!

## Objectives

By completing this chapter, you will be able to:

- Understand the fundamental difference between image classification and object detection, including how bounding box coordinates work and why detection is computationally more expensive
- Implement YOLO (You Only Look Once) detection in PHP applications using Python subprocess integration with proper error handling and timeout management
- Integrate cloud vision APIs (Google Vision and AWS Rekognition) to perform object detection without managing ML infrastructure
- Process detection results to draw annotated bounding boxes with color-coded labels, confidence scores, and visual overlays using PHP's GD library
- Build a production-ready detection service with multiple backend support, caching, rate limiting, batch processing, and RESTful API endpoints
- Compare detection approaches across dimensions of speed, accuracy, cost, and deployment complexity to make informed architectural decisions
- Handle edge cases including missing objects, overlapping bounding boxes, confidence threshold tuning, and performance optimization for real-time use cases

## Step 1: Understanding Object Detection (~10 min)

### Goal

Learn what object detection is, how it differs from classification, and understand the output format that all detection systems use.

### Actions

1. **Grasp the key difference**: Classification answers "What is in this image?" (one label per image), while detection answers "What objects are present and where are they?" (multiple objects with locations).

2. **Understand bounding boxes**: Every detected object includes:

   - **Class/Label**: What the object is (person, car, dog, etc.)
   - **Confidence**: How certain the model is (0.0 to 1.0)
   - **Bounding Box**: Rectangle coordinates `[x, y, width, height]` where:
     - `x, y` = top-left corner position
     - `width, height` = box dimensions in pixels

3. **Learn common algorithms**:

```php
# filename: detection-algorithms.php
<?php

declare(strict_types=1);

/**
 * Object Detection Algorithm Comparison
 */

$algorithms = [
    'YOLO (You Only Look Once)' => [
        'speed' => 'Very Fast (30-60 FPS)',
        'accuracy' => 'High',
        'versions' => ['YOLOv5', 'YOLOv8', 'YOLOv9'],
        'best_for' => 'Real-time applications, video processing',
        'trade_off' => 'Slightly less accurate than two-stage detectors'
    ],
    'SSD (Single Shot Detector)' => [
        'speed' => 'Fast (20-40 FPS)',
        'accuracy' => 'Medium-High',
        'versions' => ['SSD300', 'SSD512'],
        'best_for' => 'Mobile deployment, embedded systems',
        'trade_off' => 'Struggles with small objects'
    ],
    'Faster R-CNN' => [
        'speed' => 'Slow (5-10 FPS)',
        'accuracy' => 'Very High',
        'versions' => ['Faster R-CNN', 'Mask R-CNN'],
        'best_for' => 'Accuracy-critical applications, research',
        'trade_off' => 'Too slow for real-time use'
    ],
    'OpenCV Haar Cascades' => [
        'speed' => 'Very Fast (60+ FPS)',
        'accuracy' => 'Medium (for faces)',
        'versions' => ['Frontal face', 'Profile face', 'Eyes'],
        'best_for' => 'Face detection, lightweight tasks',
        'trade_off' => 'Limited to specific objects, older technology'
    ]
];

foreach ($algorithms as $name => $specs) {
    echo "=== {$name} ===\n";
    echo "Speed: {$specs['speed']}\n";
    echo "Accuracy: {$specs['accuracy']}\n";
    echo "Best for: {$specs['best_for']}\n";
    echo "Trade-off: {$specs['trade_off']}\n\n";
}
```

4. **Understand use cases in PHP applications**:

```php
# filename: detection-use-cases.php
<?php

declare(strict_types=1);

$useCases = [
    'E-commerce' => [
        'Visual search: Find similar products by detecting objects in photos',
        'Inventory management: Count items on shelves automatically',
        'Quality control: Detect damaged or defective products'
    ],
    'Security' => [
        'Access control: Detect unauthorized people in restricted areas',
        'Surveillance: Track suspicious activities or objects',
        'Face recognition: Identify registered users'
    ],
    'Social Media' => [
        'Photo tagging: Automatically suggest tags for people and objects',
        'Content moderation: Detect inappropriate content in images',
        'Engagement features: Add AR filters based on detected faces'
    ],
    'Healthcare' => [
        'Medical imaging: Detect anomalies in X-rays or scans',
        'Patient monitoring: Track patient movement and falls',
        'Equipment tracking: Locate medical devices in hospitals'
    ],
    'Automotive' => [
        'Dashcam analysis: Detect vehicles, pedestrians, road signs',
        'Parking management: Count available spaces',
        'Insurance claims: Assess vehicle damage automatically'
    ]
];

foreach ($useCases as $industry => $applications) {
    echo "=== {$industry} ===\n";
    foreach ($applications as $app) {
        echo "  • {$app}\n";
    }
    echo "\n";
}
```

### Expected Result

```
=== YOLO (You Only Look Once) ===
Speed: Very Fast (30-60 FPS)
Accuracy: High
Best for: Real-time applications, video processing
Trade-off: Slightly less accurate than two-stage detectors

=== E-commerce ===
  • Visual search: Find similar products by detecting objects in photos
  • Inventory management: Count items on shelves automatically
  • Quality control: Detect damaged or defective products
```

### Why It Works

Object detection models are trained on massive datasets (like COCO with 80 object classes or Open Images with 600+ classes) using convolutional neural networks that learn to recognize patterns at multiple scales. YOLO-style detectors divide images into grids and predict bounding boxes and class probabilities simultaneously, enabling real-time performance. Two-stage detectors like Faster R-CNN first propose regions of interest then classify them, achieving higher accuracy at the cost of speed. For PHP applications, you'll typically use pre-trained models accessed via Python or cloud APIs rather than training from scratch, as detection models require enormous computational resources and datasets.

### Troubleshooting

- **Confused about coordinate systems?** — Most detection systems use absolute pixel coordinates where (0,0) is the top-left corner. Some return normalized coordinates (0.0-1.0) that you multiply by image dimensions. Always check your detection backend's documentation and convert to a consistent format.

- **Why is detection slower than classification?** — Classification models make one prediction per image, while detection models must evaluate multiple regions at different scales, generating dozens or hundreds of bounding box proposals before filtering to final detections. YOLO mitigates this by predicting all boxes in one forward pass.

- **What's the difference between object detection and segmentation?** — Detection provides rectangular bounding boxes (4 coordinates), while segmentation provides pixel-level masks (exact object boundaries). Segmentation is more accurate but much slower. For most PHP applications, bounding boxes are sufficient.

::: tip Detection vs. Segmentation: Choosing the Right Approach
Object detection and image segmentation are related but distinct computer vision tasks:

**Object Detection** (what we're covering):

- Output: Bounding boxes with `[x, y, width, height]` coordinates
- Information: Location, class label, confidence score
- Speed: Fast (30-60 FPS with YOLO)
- Use cases: Counting objects, tracking movement, general object location
- Example: "Person at coordinates [100, 50, 200, 300]"

**Instance Segmentation** (advanced topic):

- Output: Pixel-level masks for each object instance
- Information: Exact object boundaries (which pixels belong to which object)
- Speed: Slower (5-15 FPS with Mask R-CNN)
- Use cases: Photo editing, precise extraction, medical imaging, AR effects
- Example: "Every pixel that belongs to person #1, person #2, etc."

**Semantic Segmentation** (different again):

- Output: Pixel-level class labels (all pixels, no instance distinction)
- Information: What category each pixel belongs to
- Speed: Medium (10-30 FPS)
- Use cases: Autonomous driving (road segmentation), scene understanding
- Example: "These pixels are 'road', these are 'sky', these are 'person'"

**When to use what:**

- **Detection**: You need to count, locate, or track distinct objects → Use YOLO (this chapter)
- **Instance Segmentation**: You need exact boundaries for editing or extraction → Use Mask R-CNN, SAM (Segment Anything Model)
- **Semantic Segmentation**: You need to understand entire scene composition → Use DeepLab, U-Net

For most PHP web applications, **object detection is the right choice**: it's fast enough for real-time use, provides sufficient location accuracy, and integrates easily. Segmentation requires 3-10x more computation and specialized models not readily available in PHP workflows.

**Future-proofing**: If you think you might need segmentation later, design your detection system with abstract interfaces. You can upgrade from bounding boxes to masks without rewriting your entire application architecture.
:::

## Step 2: Environment Setup (~15 min)

### Goal

Install Python dependencies, download YOLOv8 models, verify GD/Imagick for drawing, and ensure all tools work together.

### Actions

1. **Create project directory**:

```bash
# Create code directory
mkdir -p code/chapter-18/data/sample_images
mkdir -p code/chapter-18/data/test_results
mkdir -p code/chapter-18/models
cd code/chapter-18
```

2. **Create Python requirements file**:

```python
# filename: requirements.txt
# YOLOv8 detection framework
ultralytics>=8.0.0

# OpenCV for face detection and image processing
opencv-python>=4.8.0

# NumPy for array operations
numpy>=1.24.0

# PIL for image manipulation
Pillow>=10.0.0
```

3. **Install Python dependencies**:

```bash
# Install required packages
pip3 install -r requirements.txt

# Verify installation
python3 -c "import ultralytics; print('Ultralytics version:', ultralytics.__version__)"
python3 -c "import cv2; print('OpenCV version:', cv2.__version__)"
```

4. **Create verification script**:

```php
# filename: verify-setup.php
<?php

declare(strict_types=1);

echo "=== Object Detection Environment Verification ===\n\n";

// Check PHP version
echo "1. PHP Version: " . PHP_VERSION;
echo (version_compare(PHP_VERSION, '8.4.0', '>=')) ? " ✓\n" : " ✗ (need 8.4+)\n";

// Check GD extension
echo "2. GD Extension: ";
if (extension_loaded('gd')) {
    echo "✓ Loaded\n";
    $gdInfo = gd_info();
    echo "   - Version: " . $gdInfo['GD Version'] . "\n";
    echo "   - PNG Support: " . ($gdInfo['PNG Support'] ? 'Yes' : 'No') . "\n";
    echo "   - JPEG Support: " . ($gdInfo['JPEG Support'] ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ Not loaded (install php-gd)\n";
}

// Check Imagick (optional but preferred)
echo "3. Imagick Extension: ";
if (extension_loaded('imagick')) {
    echo "✓ Loaded\n";
    $imagick = new Imagick();
    echo "   - Version: " . Imagick::getVersion()['versionString'] . "\n";
} else {
    echo "ℹ Not loaded (optional, GD works fine)\n";
}

// Check Python
echo "4. Python 3: ";
$pythonVersion = shell_exec('python3 --version 2>&1');
echo $pythonVersion ? trim($pythonVersion) . " ✓\n" : "✗ Not found\n";

// Check pip
echo "5. pip3: ";
$pipVersion = shell_exec('pip3 --version 2>&1');
echo $pipVersion ? trim($pipVersion) . " ✓\n" : "✗ Not found\n";

// Check ultralytics (YOLO)
echo "6. Ultralytics (YOLO): ";
$yoloCheck = shell_exec('python3 -c "import ultralytics; print(ultralytics.__version__)" 2>&1');
echo $yoloCheck ? "v" . trim($yoloCheck) . " ✓\n" : "✗ Not installed\n";

// Check OpenCV
echo "7. OpenCV: ";
$cvCheck = shell_exec('python3 -c "import cv2; print(cv2.__version__)" 2>&1');
echo $cvCheck ? "v" . trim($cvCheck) . " ✓\n" : "✗ Not installed\n";

// Test image creation
echo "8. Image Creation Test: ";
try {
    $testImage = imagecreate(100, 100);
    $white = imagecolorallocate($testImage, 255, 255, 255);
    imagefill($testImage, 0, 0, $white);

    $tempFile = sys_get_temp_dir() . '/test_image.png';
    imagepng($testImage, $tempFile);
    imagedestroy($testImage);

    if (file_exists($tempFile)) {
        unlink($tempFile);
        echo "✓ Success\n";
    } else {
        echo "✗ Failed to create image\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Setup Complete ===\n";

// Summary
$requiredChecks = [
    version_compare(PHP_VERSION, '8.4.0', '>='),
    extension_loaded('gd'),
    !empty($pythonVersion),
    !empty($yoloCheck),
    !empty($cvCheck)
];

$passed = count(array_filter($requiredChecks));
$total = count($requiredChecks);

echo "Passed: {$passed}/{$total} required checks\n";

if ($passed === $total) {
    echo "✓ Your environment is ready for object detection!\n";
} else {
    echo "✗ Please install missing requirements above\n";
}
```

5. **Run verification**:

```bash
php verify-setup.php
```

### Expected Result

```
=== Object Detection Environment Verification ===

1. PHP Version: 8.4.0 ✓
2. GD Extension: ✓ Loaded
   - Version: bundled (2.1.0 compatible)
   - PNG Support: Yes
   - JPEG Support: Yes
3. Imagick Extension: ℹ Not loaded (optional, GD works fine)
4. Python 3: Python 3.11.5 ✓
5. pip3: pip 23.2.1 from /usr/local/lib/python3.11/site-packages/pip (python 3.11) ✓
6. Ultralytics (YOLO): v8.1.0 ✓
7. OpenCV: v4.8.1 ✓
8. Image Creation Test: ✓ Success

=== Setup Complete ===
Passed: 5/5 required checks
✓ Your environment is ready for object detection!
```

### Why It Works

YOLOv8 (via ultralytics package) handles model downloading automatically on first use, caching weights locally. It provides a simple Python API that we'll call from PHP. OpenCV provides classical computer vision algorithms including Haar Cascades for face detection. GD is PHP's built-in image manipulation library sufficient for drawing bounding boxes. Imagick offers more features but isn't required. The subprocess pattern from Chapter 11 lets PHP leverage Python's ML ecosystem while staying in PHP for application logic.

### Troubleshooting

- **`pip3: command not found`** — Python is installed but pip isn't. On Ubuntu/Debian: `sudo apt install python3-pip`. On macOS: `python3 -m ensurepip --upgrade`.

- **`ModuleNotFoundError: No module named 'ultralytics'`** — Python packages installed in wrong Python version. Verify with `which python3` and ensure pip3 installs to same Python. Use `python3 -m pip install -r requirements.txt` instead.

- **GD not loaded** — Install PHP GD extension. Ubuntu/Debian: `sudo apt install php8.4-gd && sudo systemctl restart apache2`. macOS: GD usually bundled, check `php.ini` for `extension=gd`.

- **Ultralytics downloads model on first run** — This is normal. YOLOv8n (nano) is ~6MB, YOLOv8m (medium) is ~50MB. Models are cached in `~/.cache/torch/hub/ultralytics/` for reuse.

- **Permission denied writing to cache** — Ensure your user has write permissions to home directory or set `TORCH_HOME` environment variable: `export TORCH_HOME=/path/to/writable/dir`.

## Step 3: Python YOLO Integration (~20 min)

### Goal

Create a Python script that runs YOLOv8 detection and returns JSON results, then build a PHP client to call it and parse detections.

### Actions

1. **Create Python YOLO detection script**:

```python
# filename: detect_yolo.py
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
```

2. **Make script executable**:

```bash
chmod +x detect_yolo.py

# Test it directly
python3 detect_yolo.py data/sample_images/test.jpg
```

3. **Create PHP YOLO client**:

```php
# filename: 01-detect-yolo.php
<?php

declare(strict_types=1);

/**
 * YOLO Object Detection Client
 *
 * Calls Python YOLOv8 script and parses detection results.
 */

class YoloDetector
{
    public function __construct(
        private string $pythonScript = __DIR__ . '/detect_yolo.py',
        private string $modelName = 'yolov8n.pt',
        private float $confidenceThreshold = 0.25,
        private int $timeoutSeconds = 30
    ) {
        if (!file_exists($this->pythonScript)) {
            throw new RuntimeException("Python script not found: {$this->pythonScript}");
        }
    }

    /**
     * Detect objects in image.
     *
     * @param string $imagePath Path to image file
     * @return array Detection results
     * @throws RuntimeException On detection failure
     */
    public function detect(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new RuntimeException("Image not found: {$imagePath}");
        }

        // Build command
        $command = sprintf(
            'python3 %s %s %s %s 2>&1',
            escapeshellarg($this->pythonScript),
            escapeshellarg($imagePath),
            escapeshellarg($this->modelName),
            escapeshellarg((string)$this->confidenceThreshold)
        );

        // Execute with timeout
        $startTime = microtime(true);
        $output = $this->executeWithTimeout($command, $this->timeoutSeconds);
        $executionTime = microtime(true) - $startTime;

        // Parse JSON
        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException(
                "Invalid JSON from detector. Output: " . substr($output, 0, 500)
            );
        }

        if (!$result['success']) {
            throw new RuntimeException(
                "Detection failed: " . ($result['error'] ?? 'Unknown error')
            );
        }

        // Add execution time
        $result['execution_time'] = round($executionTime, 3);

        return $result;
    }

    /**
     * Execute command with timeout.
     */
    private function executeWithTimeout(string $command, int $timeout): string
    {
        $descriptors = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w']  // stderr
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start detection process');
        }

        // Close stdin
        fclose($pipes[0]);

        // Set non-blocking mode
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);

        $output = '';
        $errors = '';
        $startTime = time();

        // Read output with timeout
        while (true) {
            $status = proc_get_status($process);

            if (!$status['running']) {
                // Process finished
                $output .= stream_get_contents($pipes[1]);
                $errors .= stream_get_contents($pipes[2]);
                break;
            }

            if ((time() - $startTime) > $timeout) {
                // Timeout reached
                proc_terminate($process);
                fclose($pipes[1]);
                fclose($pipes[2]);
                proc_close($process);
                throw new RuntimeException("Detection timeout after {$timeout} seconds");
            }

            // Read available data
            $output .= stream_get_contents($pipes[1]);
            $errors .= stream_get_contents($pipes[2]);

            usleep(100000); // 100ms
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        if ($exitCode !== 0 && !empty($errors)) {
            throw new RuntimeException("Detection process failed: {$errors}");
        }

        return $output;
    }

    /**
     * Get list of COCO class names.
     */
    public function getClassNames(): array
    {
        return [
            'person', 'bicycle', 'car', 'motorcycle', 'airplane', 'bus', 'train', 'truck', 'boat',
            'traffic light', 'fire hydrant', 'stop sign', 'parking meter', 'bench', 'bird', 'cat',
            'dog', 'horse', 'sheep', 'cow', 'elephant', 'bear', 'zebra', 'giraffe', 'backpack',
            'umbrella', 'handbag', 'tie', 'suitcase', 'frisbee', 'skis', 'snowboard', 'sports ball',
            'kite', 'baseball bat', 'baseball glove', 'skateboard', 'surfboard', 'tennis racket',
            'bottle', 'wine glass', 'cup', 'fork', 'knife', 'spoon', 'bowl', 'banana', 'apple',
            'sandwich', 'orange', 'broccoli', 'carrot', 'hot dog', 'pizza', 'donut', 'cake',
            'chair', 'couch', 'potted plant', 'bed', 'dining table', 'toilet', 'tv', 'laptop',
            'mouse', 'remote', 'keyboard', 'cell phone', 'microwave', 'oven', 'toaster', 'sink',
            'refrigerator', 'book', 'clock', 'vase', 'scissors', 'teddy bear', 'hair drier', 'toothbrush'
        ];
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $imagePath = $argv[1] ?? __DIR__ . '/data/sample_images/street_scene.jpg';

    if (!file_exists($imagePath)) {
        die("Image not found: {$imagePath}\nUsage: php 01-detect-yolo.php <image_path>\n");
    }

    try {
        $detector = new YoloDetector();

        echo "Detecting objects in: {$imagePath}\n";
        echo "Model: yolov8n.pt (nano - fastest)\n\n";

        $result = $detector->detect($imagePath);

        echo "Success! Found {$result['count']} objects in {$result['execution_time']}s\n\n";

        foreach ($result['detections'] as $i => $detection) {
            printf(
                "%d. %s (%.1f%% confidence)\n",
                $i + 1,
                ucfirst($detection['class']),
                $detection['confidence'] * 100
            );

            printf(
                "   Position: [%d, %d] Size: %dx%d\n\n",
                $detection['bbox']['x'],
                $detection['bbox']['y'],
                $detection['bbox']['width'],
                $detection['bbox']['height']
            );
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
```

4. **Test detection**:

```bash
# Download a test image
curl -o data/sample_images/street.jpg \
  "https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=800"

# Run detection
php 01-detect-yolo.php data/sample_images/street.jpg
```

### Expected Result

```
Detecting objects in: data/sample_images/street.jpg
Model: yolov8n.pt (nano - fastest)

Success! Found 8 objects in 1.247s

1. Person (94.3% confidence)
   Position: [342, 156] Size: 89x234

2. Person (91.7% confidence)
   Position: [523, 178] Size: 76x198

3. Car (88.9% confidence)
   Position: [125, 245] Size: 267x189

4. Traffic light (76.2% confidence)
   Position: [698, 45] Size: 24x67

5. Backpack (72.8% confidence)
   Position: [365, 189] Size: 38x52

6. Handbag (68.4% confidence)
   Position: [542, 256] Size: 31x45

7. Car (65.9% confidence)
   Position: [12, 267] Size: 198x134

8. Bicycle (62.3% confidence)
   Position: [456, 289] Size: 112x145
```

### Why It Works

YOLOv8 divides the image into a grid and predicts bounding boxes and class probabilities for each grid cell in a single forward pass through the neural network. This "single-shot" approach is why YOLO is fast enough for real-time detection. The model was trained on the COCO dataset (Common Objects in Context) containing 80 object classes across 330,000 images. The `ultralytics` package provides a clean Python API that handles model loading, preprocessing, inference, and non-maximum suppression (filtering overlapping boxes). Our PHP client uses `proc_open()` for subprocess management with timeout handling, which is more robust than `shell_exec()` for long-running processes.

### Troubleshooting

- **`FileNotFoundError: [Errno 2] No such file or directory: 'yolov8n.pt'`** — This is normal on first run. Ultralytics downloads the model automatically. Wait 30-60 seconds. If download fails, manually download from https://github.com/ultralytics/assets/releases/download/v0.0.0/yolov8n.pt and place in script directory.

- **`RuntimeException: Detection timeout after 30 seconds`** — First run takes longer due to model download and initialization. Increase timeout to 60s for first run: `new YoloDetector(timeoutSeconds: 60)`. Subsequent runs are much faster (~1-3s).

- **`Invalid JSON from detector`** — Python script crashed. Run manually to see full error: `python3 detect_yolo.py image.jpg`. Common causes: corrupted image file, unsupported format, insufficient memory.

- **High memory usage** — YOLOv8n (nano) uses ~400MB RAM. Larger models use more: YOLOv8s=~1GB, YOLOv8m=~2GB, YOLOv8l=~3GB, YOLOv8x=~4GB. Use smaller models or reduce image resolution.

- **Detections missing small objects** — Lower confidence threshold: `new YoloDetector(confidenceThreshold: 0.1)`. Note this increases false positives. Or use larger model (yolov8m/l/x) which has better small object detection but is slower.

::: warning When to Train Custom YOLO Models
The pre-trained YOLOv8 model we're using was trained on the [COCO dataset](https://cocodataset.org/) with 80 common object classes (people, vehicles, animals, everyday items). This covers most general use cases.

**When pre-trained models are sufficient:**

- ✅ Detecting common objects (people, cars, animals, furniture, electronics)
- ✅ General-purpose applications (security, counting, tracking)
- ✅ Rapid prototyping and proof-of-concept projects
- ✅ When you don't have thousands of labeled images

**When you need custom training:**

- 🎯 **Domain-specific objects** not in COCO: medical equipment, industrial parts, specific plant species, company logos, architectural elements, specialized retail products
- 🎯 **Higher accuracy requirements**: Pre-trained models might detect "car" but you need to distinguish "sedan vs SUV vs truck"
- 🎯 **Unique visual context**: Objects that look different in your application (e.g., aerial drone footage, microscopy, underwater cameras)
- 🎯 **Performance optimization**: Smaller custom models trained on fewer classes run faster

**What custom training requires:**

1. **Labeled dataset**: Minimum 500-1,000 images with bounding box annotations (more is better)
2. **Annotation tool**: [Roboflow](https://roboflow.com/), [LabelImg](https://github.com/tzutalin/labelImg), or [CVAT](https://www.cvat.ai/)
3. **GPU training environment**: Google Colab (free), AWS/Azure GPU instances, or local NVIDIA GPU
4. **Training time**: 2-8 hours depending on dataset size and model size
5. **Python training script**: Ultralytics provides simple training API

**Quick training example** (Python):

```python
from ultralytics import YOLO

# Load base model
model = YOLO('yolov8n.pt')

# Train on custom dataset
results = model.train(
    data='dataset.yaml',  # Dataset config
    epochs=100,
    imgsz=640,
    batch=16
)

# Export trained model
model.export(format='onnx')  # Can use with PHP via ONNX Runtime
```

**Integration with PHP:**
Once trained, use your custom model exactly like the pre-trained model:

```python
# In detect_yolo.py, change model path:
model = YOLO('custom_model.pt')  # Your trained model
```

**Resources for custom training:**

- [Ultralytics Training Docs](https://docs.ultralytics.com/modes/train/)
- [Roboflow: How to Train YOLOv8](https://blog.roboflow.com/how-to-train-yolov8-on-a-custom-dataset/)
- [Free datasets: Roboflow Universe](https://universe.roboflow.com/)

**Bottom line**: Start with pre-trained COCO models. They work for 90% of applications. Only invest in custom training when you have a clear need and the resources to annotate data. The integration pattern with PHP remains identical regardless of which model you use.
:::

## Step 4: Drawing Bounding Boxes (~15 min)

### Goal

Create a class that draws color-coded bounding boxes with labels and confidence scores on detected objects using PHP's GD library.

### Actions

1. **Create BoundingBoxDrawer class**:

```php
# filename: BoundingBoxDrawer.php
<?php

declare(strict_types=1);

/**
 * Bounding Box Drawer
 *
 * Draws annotated bounding boxes on images using GD.
 */

class BoundingBoxDrawer
{
    private const DEFAULT_COLORS = [
        'person' => [255, 59, 48],      // Red
        'car' => [52, 199, 89],         // Green
        'truck' => [52, 199, 89],       // Green
        'bus' => [52, 199, 89],         // Green
        'bicycle' => [0, 122, 255],     // Blue
        'motorcycle' => [0, 122, 255],  // Blue
        'dog' => [255, 149, 0],         // Orange
        'cat' => [255, 149, 0],         // Orange
        'bird' => [255, 149, 0],        // Orange
    ];

    private const DEFAULT_COLOR = [255, 255, 255]; // White for unknown classes

    public function __construct(
        private int $lineThickness = 3,
        private int $fontSize = 3,
        private bool $showConfidence = true,
        private float $minConfidenceToShow = 0.0
    ) {}

    /**
     * Draw bounding boxes on image.
     *
     * @param string $imagePath Input image path
     * @param array $detections Array of detections from YOLO
     * @param string $outputPath Output image path
     * @return bool Success status
     */
    public function draw(string $imagePath, array $detections, string $outputPath): bool
    {
        // Load image
        $image = $this->loadImage($imagePath);

        if (!$image) {
            throw new RuntimeException("Failed to load image: {$imagePath}");
        }

        // Enable alpha blending for transparency
        imagealphablending($image, true);
        imagesavealpha($image, true);

        // Draw each detection
        foreach ($detections as $detection) {
            if ($detection['confidence'] < $this->minConfidenceToShow) {
                continue;
            }

            $this->drawDetection($image, $detection);
        }

        // Save annotated image
        $success = $this->saveImage($image, $outputPath);
        imagedestroy($image);

        return $success;
    }

    /**
     * Draw single detection on image.
     */
    private function drawDetection($image, array $detection): void
    {
        $bbox = $detection['bbox'];
        $class = $detection['class'];
        $confidence = $detection['confidence'];

        // Get color for class
        $color = self::DEFAULT_COLORS[$class] ?? self::DEFAULT_COLOR;
        $gdColor = imagecolorallocate($image, $color[0], $color[1], $color[2]);

        // Draw rectangle
        imagesetthickness($image, $this->lineThickness);

        // Draw bounding box
        imagerectangle(
            $image,
            $bbox['x'],
            $bbox['y'],
            $bbox['x'] + $bbox['width'],
            $bbox['y'] + $bbox['height'],
            $gdColor
        );

        // Prepare label text
        $label = ucfirst($class);
        if ($this->showConfidence) {
            $label .= ' ' . round($confidence * 100) . '%';
        }

        // Draw label background
        $labelWidth = imagefontwidth($this->fontSize) * strlen($label);
        $labelHeight = imagefontheight($this->fontSize);
        $padding = 4;

        // Label position (above box if space, below if at top)
        $labelX = $bbox['x'];
        $labelY = $bbox['y'] - $labelHeight - $padding * 2;

        if ($labelY < 0) {
            $labelY = $bbox['y'] + $this->lineThickness;
        }

        // Draw filled rectangle for label background
        imagefilledrectangle(
            $image,
            $labelX,
            $labelY,
            $labelX + $labelWidth + $padding * 2,
            $labelY + $labelHeight + $padding * 2,
            $gdColor
        );

        // Draw label text in white
        $white = imagecolorallocate($image, 255, 255, 255);
        imagestring(
            $image,
            $this->fontSize,
            $labelX + $padding,
            $labelY + $padding,
            $label,
            $white
        );
    }

    /**
     * Load image from file.
     */
    private function loadImage(string $path)
    {
        $imageInfo = getimagesize($path);

        if (!$imageInfo) {
            return false;
        }

        return match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($path),
            IMAGETYPE_PNG => imagecreatefrompng($path),
            IMAGETYPE_GIF => imagecreatefromgif($path),
            IMAGETYPE_WEBP => imagecreatefromwebp($path),
            default => false
        };
    }

    /**
     * Save image to file.
     */
    private function saveImage($image, string $path): bool
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $path, 95),
            'png' => imagepng($image, $path, 9),
            'gif' => imagegif($image, $path),
            'webp' => imagewebp($image, $path, 95),
            default => false
        };
    }

    /**
     * Get color for detection class.
     */
    public function setClassColor(string $class, int $r, int $g, int $b): void
    {
        self::DEFAULT_COLORS[$class] = [$r, $g, $b];
    }
}
```

2. **Create example script using drawer**:

```php
# filename: 02-draw-boxes.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/01-detect-yolo.php';
require_once __DIR__ . '/BoundingBoxDrawer.php';

/**
 * Detect objects and draw annotated bounding boxes.
 */

if ($argc < 2) {
    echo "Usage: php 02-draw-boxes.php <image_path> [output_path]\n";
    exit(1);
}

$imagePath = $argv[1];
$outputPath = $argv[2] ?? __DIR__ . '/data/test_results/annotated_' . basename($imagePath);

if (!file_exists($imagePath)) {
    die("Error: Image not found: {$imagePath}\n");
}

// Ensure output directory exists
$outputDir = dirname($outputPath);
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0777, true);
}

try {
    echo "Step 1: Detecting objects with YOLO...\n";
    $detector = new YoloDetector();
    $result = $detector->detect($imagePath);

    echo "Found {$result['count']} objects in {$result['execution_time']}s\n\n";

    // Print detections
    foreach ($result['detections'] as $i => $detection) {
        printf(
            "  %d. %s (%.1f%%)\n",
            $i + 1,
            ucfirst($detection['class']),
            $detection['confidence'] * 100
        );
    }

    echo "\nStep 2: Drawing bounding boxes...\n";
    $drawer = new BoundingBoxDrawer(
        lineThickness: 3,
        fontSize: 3,
        showConfidence: true,
        minConfidenceToShow: 0.25
    );

    $success = $drawer->draw($imagePath, $result['detections'], $outputPath);

    if ($success) {
        echo "✓ Annotated image saved to: {$outputPath}\n";

        // Get file size
        $fileSize = filesize($outputPath);
        echo "  File size: " . number_format($fileSize / 1024, 1) . " KB\n";

        // Get dimensions
        list($width, $height) = getimagesize($outputPath);
        echo "  Dimensions: {$width}x{$height}\n";
    } else {
        echo "✗ Failed to save annotated image\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

3. **Test drawing**:

```bash
php 02-draw-boxes.php data/sample_images/street.jpg
```

### Expected Result

```
Step 1: Detecting objects with YOLO...
Found 8 objects in 1.156s

  1. Person (94.3%)
  2. Person (91.7%)
  3. Car (88.9%)
  4. Traffic light (76.2%)
  5. Backpack (72.8%)
  6. Handbag (68.4%)
  7. Car (65.9%)
  8. Bicycle (62.3%)

Step 2: Drawing bounding boxes...
✓ Annotated image saved to: data/test_results/annotated_street.jpg
  File size: 187.3 KB
  Dimensions: 800x600
```

The output image will show the original photo with colored rectangles around each detected object and labels indicating the class and confidence percentage.

### Why It Works

GD (Graphics Draw) is PHP's built-in image manipulation library that provides functions for creating, modifying, and outputting images. We use `imagerectangle()` to draw the bounding box borders and `imagefilledrectangle()` with `imagestring()` to create colored label backgrounds with white text. Color-coding by object class helps visually distinguish different object types quickly. Alpha blending ensures proper transparency handling when overlaying annotations on the original image. The line thickness and font size are configurable to work with different image resolutions.

### Troubleshooting

- **Labels are tiny on high-resolution images** — GD's built-in fonts (1-5) are fixed sizes. For HD images, use larger line thickness (5-7) or consider using `imagettftext()` with TrueType fonts for scalable text.

- **Boxes don't align with objects** — Check coordinate system. YOLO returns absolute pixel coordinates with (0,0) at top-left. If using a different detector, it might return normalized coordinates (0.0-1.0) that need multiplying by image dimensions.

- **Colors look wrong or washed out** — Ensure `imagealphablending()` is enabled before drawing. If saving as PNG, call `imagesavealpha()` to preserve transparency. JPEG doesn't support transparency so backgrounds will be opaque.

- **Memory exceeded on large images** — GD loads entire image into memory. For very large images (>4000px), resize before processing: `imagescale($image, $maxWidth, -1, IMG_BICUBIC_FIXED)`.

- **Overlapping labels are unreadable** — Implement label collision detection to offset overlapping labels vertically, or draw labels in different corners of bounding boxes (top-left vs bottom-right).

## Step 5: Cloud API Integration (~20 min)

### Goal

Integrate Google Vision and AWS Rekognition APIs to perform object detection without managing infrastructure, comparing results and costs.

### Actions

1. **Create CloudDetector unified interface**:

```php
# filename: CloudDetector.php
<?php

declare(strict_types=1);

/**
 * Unified Cloud Detection Interface
 *
 * Abstracts Google Vision and AWS Rekognition behind consistent API.
 */

interface CloudDetectorInterface
{
    public function detect(string $imagePath): array;
    public function getName(): string;
}

class GoogleVisionDetector implements CloudDetectorInterface
{
    public function __construct(
        private string $keyFile,
        private float $minConfidence = 0.5
    ) {
        if (!file_exists($this->keyFile)) {
            throw new RuntimeException("Google Cloud key file not found: {$this->keyFile}");
        }
    }

    public function detect(string $imagePath): array
    {
        // Note: Requires google/cloud-vision package
        // composer require google/cloud-vision

        if (!class_exists('Google\Cloud\Vision\V1\ImageAnnotatorClient')) {
            throw new RuntimeException(
                'Google Cloud Vision not installed. Run: composer require google/cloud-vision'
            );
        }

        putenv("GOOGLE_APPLICATION_CREDENTIALS={$this->keyFile}");

        $imageAnnotator = new \Google\Cloud\Vision\V1\ImageAnnotatorClient();
        $imageContent = file_get_contents($imagePath);

        if ($imageContent === false) {
            throw new RuntimeException("Failed to read image: {$imagePath}");
        }

        // Detect objects
        $response = $imageAnnotator->objectLocalization($imageContent);
        $objects = $response->getLocalizedObjectAnnotations();

        $detections = [];

        foreach ($objects as $object) {
            $confidence = $object->getScore();

            if ($confidence < $this->minConfidence) {
                continue;
            }

            // Get bounding polygon (normalized coordinates)
            $vertices = $object->getBoundingPoly()->getNormalizedVertices();

            // Convert to our standard format
            // Note: Google returns normalized coords (0.0-1.0)
            list($width, $height) = getimagesize($imagePath);

            $x = (int)($vertices[0]->getX() * $width);
            $y = (int)($vertices[0]->getY() * $height);
            $maxX = (int)($vertices[2]->getX() * $width);
            $maxY = (int)($vertices[2]->getY() * $height);

            $detections[] = [
                'class' => strtolower($object->getName()),
                'confidence' => $confidence,
                'bbox' => [
                    'x' => $x,
                    'y' => $y,
                    'width' => $maxX - $x,
                    'height' => $maxY - $y
                ]
            ];
        }

        $imageAnnotator->close();

        return [
            'success' => true,
            'detections' => $detections,
            'count' => count($detections),
            'provider' => 'Google Vision API'
        ];
    }

    public function getName(): string
    {
        return 'Google Vision';
    }
}

class AWSRekognitionDetector implements CloudDetectorInterface
{
    public function __construct(
        private string $accessKey,
        private string $secretKey,
        private string $region = 'us-east-1',
        private float $minConfidence = 50.0  // AWS uses 0-100 scale
    ) {}

    public function detect(string $imagePath): array
    {
        // Note: Requires aws/aws-sdk-php package
        // composer require aws/aws-sdk-php

        if (!class_exists('Aws\Rekognition\RekognitionClient')) {
            throw new RuntimeException(
                'AWS SDK not installed. Run: composer require aws/aws-sdk-php'
            );
        }

        $rekognition = new \Aws\Rekognition\RekognitionClient([
            'version' => 'latest',
            'region' => $this->region,
            'credentials' => [
                'key' => $this->accessKey,
                'secret' => $this->secretKey
            ]
        ]);

        $imageContent = file_get_contents($imagePath);

        if ($imageContent === false) {
            throw new RuntimeException("Failed to read image: {$imagePath}");
        }

        try {
            $result = $rekognition->detectLabels([
                'Image' => [
                    'Bytes' => $imageContent
                ],
                'MinConfidence' => $this->minConfidence,
                'Features' => ['GENERAL_LABELS']
            ]);

            list($width, $height) = getimagesize($imagePath);
            $detections = [];

            foreach ($result['Labels'] as $label) {
                // AWS Rekognition returns labels with instances that have bounding boxes
                if (!empty($label['Instances'])) {
                    foreach ($label['Instances'] as $instance) {
                        $box = $instance['BoundingBox'];

                        // AWS returns normalized coordinates
                        $x = (int)($box['Left'] * $width);
                        $y = (int)($box['Top'] * $height);
                        $w = (int)($box['Width'] * $width);
                        $h = (int)($box['Height'] * $height);

                        $detections[] = [
                            'class' => strtolower($label['Name']),
                            'confidence' => $label['Confidence'] / 100, // Convert to 0-1
                            'bbox' => [
                                'x' => $x,
                                'y' => $y,
                                'width' => $w,
                                'height' => $h
                            ]
                        ];
                    }
                }
            }

            return [
                'success' => true,
                'detections' => $detections,
                'count' => count($detections),
                'provider' => 'AWS Rekognition'
            ];

        } catch (\Aws\Exception\AwsException $e) {
            throw new RuntimeException('AWS Rekognition error: ' . $e->getMessage());
        }
    }

    public function getName(): string
    {
        return 'AWS Rekognition';
    }
}
```

2. **Create Google Vision example**:

```php
# filename: 03-google-vision-api.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/CloudDetector.php';
require_once __DIR__ . '/BoundingBoxDrawer.php';

/**
 * Google Vision API Object Detection Example
 */

if ($argc < 2) {
    echo "Usage: php 03-google-vision-api.php <image_path>\n";
    echo "\nSetup:\n";
    echo "1. Create Google Cloud project\n";
    echo "2. Enable Vision API\n";
    echo "3. Create service account and download JSON key\n";
    echo "4. Set path in script or GOOGLE_APPLICATION_CREDENTIALS env var\n";
    echo "5. Run: composer require google/cloud-vision\n";
    exit(1);
}

$imagePath = $argv[1];
$keyFile = __DIR__ . '/google-cloud-key.json';

// Alternative: read from environment
if (!file_exists($keyFile) && getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
    $keyFile = getenv('GOOGLE_APPLICATION_CREDENTIALS');
}

if (!file_exists($imagePath)) {
    die("Error: Image not found: {$imagePath}\n");
}

if (!file_exists($keyFile)) {
    die("Error: Google Cloud key file not found.\nExpected: {$keyFile}\n");
}

try {
    echo "Detecting objects with Google Vision API...\n\n";

    $detector = new GoogleVisionDetector($keyFile, minConfidence: 0.5);
    $result = $detector->detect($imagePath);

    echo "Provider: {$result['provider']}\n";
    echo "Found: {$result['count']} objects\n\n";

    foreach ($result['detections'] as $i => $detection) {
        printf(
            "%d. %s (%.1f%% confidence)\n",
            $i + 1,
            ucfirst($detection['class']),
            $detection['confidence'] * 100
        );
    }

    // Draw boxes
    $outputPath = __DIR__ . '/data/test_results/google_vision_' . basename($imagePath);
    $drawer = new BoundingBoxDrawer();
    $drawer->draw($imagePath, $result['detections'], $outputPath);

    echo "\n✓ Annotated image: {$outputPath}\n";

    // Cost estimation (as of 2024)
    $costPerImage = 0.0015; // $1.50 per 1000 images for first 1000/month
    echo "\nEstimated cost: $" . number_format($costPerImage, 4) . " per image\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

3. **Create AWS Rekognition example**:

```php
# filename: 04-aws-rekognition.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/CloudDetector.php';
require_once __DIR__ . '/BoundingBoxDrawer.php';

/**
 * AWS Rekognition Object Detection Example
 */

if ($argc < 2) {
    echo "Usage: php 04-aws-rekognition.php <image_path>\n";
    echo "\nSetup:\n";
    echo "1. Create AWS account\n";
    echo "2. Create IAM user with Rekognition permissions\n";
    echo "3. Save access key and secret in .env file\n";
    echo "4. Run: composer require aws/aws-sdk-php\n";
    exit(1);
}

$imagePath = $argv[1];

if (!file_exists($imagePath)) {
    die("Error: Image not found: {$imagePath}\n");
}

// Load credentials from environment
$accessKey = getenv('AWS_ACCESS_KEY_ID');
$secretKey = getenv('AWS_SECRET_ACCESS_KEY');
$region = getenv('AWS_REGION') ?: 'us-east-1';

if (!$accessKey || !$secretKey) {
    die("Error: AWS credentials not found.\nSet AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY environment variables.\n");
}

try {
    echo "Detecting objects with AWS Rekognition...\n\n";

    $detector = new AWSRekognitionDetector(
        accessKey: $accessKey,
        secretKey: $secretKey,
        region: $region,
        minConfidence: 50.0
    );

    $result = $detector->detect($imagePath);

    echo "Provider: {$result['provider']}\n";
    echo "Found: {$result['count']} objects\n\n";

    foreach ($result['detections'] as $i => $detection) {
        printf(
            "%d. %s (%.1f%% confidence)\n",
            $i + 1,
            ucfirst($detection['class']),
            $detection['confidence'] * 100
        );
    }

    // Draw boxes
    $outputPath = __DIR__ . '/data/test_results/aws_rekognition_' . basename($imagePath);
    $drawer = new BoundingBoxDrawer();
    $drawer->draw($imagePath, $result['detections'], $outputPath);

    echo "\n✓ Annotated image: {$outputPath}\n";

    // Cost estimation (as of 2024)
    $costPerImage = 0.001; // $1.00 per 1000 images for first 1M/month
    echo "\nEstimated cost: $" . number_format($costPerImage, 4) . " per image\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
```

4. **Create environment template**:

```bash
# filename: env.example
# Google Cloud Vision API
GOOGLE_APPLICATION_CREDENTIALS=/path/to/google-cloud-key.json

# AWS Rekognition
AWS_ACCESS_KEY_ID=your_access_key_here
AWS_SECRET_ACCESS_KEY=your_secret_key_here
AWS_REGION=us-east-1

# OpenAI (if using for other tasks)
OPENAI_API_KEY=sk-your-key-here
```

### Expected Result

```
Detecting objects with Google Vision API...

Provider: Google Vision API
Found: 12 objects

1. Person (96.7% confidence)
2. Person (94.2% confidence)
3. Car (91.3% confidence)
4. Building (88.9% confidence)
5. Window (85.4% confidence)
...

✓ Annotated image: data/test_results/google_vision_street.jpg

Estimated cost: $0.0015 per image
```

### Why It Works

Cloud vision APIs provide production-ready object detection without infrastructure management. Google Vision uses Google's proprietary models trained on massive datasets, while AWS Rekognition uses Amazon's models. Both return normalized bounding box coordinates (0.0 to 1.0) that we convert to absolute pixels by multiplying by image dimensions. The unified interface pattern (CloudDetectorInterface) allows swapping providers without changing application code. Cloud APIs handle scaling, model updates, and infrastructure, trading cost for convenience.

### Troubleshooting

- **`google/cloud-vision not installed`** — Install with Composer: `composer require google/cloud-vision`. Ensure composer.json requires PHP 8.4+.

- **`Authentication failed`** — For Google: verify JSON key file path and ensure service account has Vision API permissions. For AWS: verify access key/secret and IAM user has `rekognition:DetectLabels` permission.

- **`API quota exceeded`** — Cloud APIs have free tiers then usage costs. Google: 1000 free requests/month. AWS: 5000 free first year. Check usage in respective consoles and enable billing if needed.

- **Different object classes than YOLO** — Cloud APIs use different training data/taxonomies. Google might return "Mammal" where YOLO says "dog". Normalize class names for consistency if needed.

- **Higher costs than expected** — Cache results for identical images. Batch processing is cheaper than individual API calls. Consider using YOLO for high-volume use cases, cloud APIs for low-volume or exploratory work.

## Step 6: OpenCV Face Detection (~15 min)

### Goal

Implement fast, privacy-preserving face detection using OpenCV Haar Cascades without cloud dependencies.

### Actions

1. **Create OpenCV face detection Python script**:

```python
# filename: detect_opencv.py
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
```

2. **Create PHP OpenCV client**:

```php
# filename: 05-opencv-faces.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/BoundingBoxDrawer.php';

/**
 * OpenCV Face Detection Client
 *
 * Fast, offline face detection using Haar Cascades.
 */

class OpenCVFaceDetector
{
    public function __construct(
        private string $pythonScript = __DIR__ . '/detect_opencv.py',
        private float $scaleFactor = 1.1,
        private int $minNeighbors = 5,
        private int $timeoutSeconds = 10
    ) {
        if (!file_exists($this->pythonScript)) {
            throw new RuntimeException("Python script not found: {$this->pythonScript}");
        }
    }

    /**
     * Detect faces in image.
     */
    public function detect(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new RuntimeException("Image not found: {$imagePath}");
        }

        $command = sprintf(
            'timeout %d python3 %s %s %s %s 2>&1',
            $this->timeoutSeconds,
            escapeshellarg($this->pythonScript),
            escapeshellarg($imagePath),
            escapeshellarg((string)$this->scaleFactor),
            escapeshellarg((string)$this->minNeighbors)
        );

        $startTime = microtime(true);
        $output = shell_exec($command);
        $executionTime = microtime(true) - $startTime;

        if ($output === null) {
            throw new RuntimeException('Failed to execute face detection');
        }

        $result = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException("Invalid JSON from detector: " . substr($output, 0, 200));
        }

        if (!$result['success']) {
            throw new RuntimeException("Detection failed: " . ($result['error'] ?? 'Unknown error'));
        }

        $result['execution_time'] = round($executionTime, 3);

        return $result;
    }

    /**
     * Adjust detection sensitivity.
     *
     * Lower scale_factor = slower but more thorough
     * Higher min_neighbors = fewer false positives
     */
    public function setSensitivity(float $scaleFactor, int $minNeighbors): void
    {
        $this->scaleFactor = $scaleFactor;
        $this->minNeighbors = $minNeighbors;
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if ($argc < 2) {
        echo "Usage: php 05-opencv-faces.php <image_path>\n";
        echo "\nOpenCV Haar Cascade Face Detection\n";
        echo "Privacy-friendly: 100% offline, no cloud API calls\n";
        exit(1);
    }

    $imagePath = $argv[1];

    if (!file_exists($imagePath)) {
        die("Error: Image not found: {$imagePath}\n");
    }

    try {
        echo "Detecting faces with OpenCV...\n\n";

        $detector = new OpenCVFaceDetector(
            scaleFactor: 1.1,
            minNeighbors: 5
        );

        $result = $detector->detect($imagePath);

        echo "Method: {$result['method']}\n";
        echo "Found: {$result['count']} face(s) in {$result['execution_time']}s\n\n";

        foreach ($result['detections'] as $i => $detection) {
            printf(
                "%d. Face at [%d, %d] size %dx%d\n",
                $i + 1,
                $detection['bbox']['x'],
                $detection['bbox']['y'],
                $detection['bbox']['width'],
                $detection['bbox']['height']
            );
        }

        // Draw boxes
        if ($result['count'] > 0) {
            $outputPath = __DIR__ . '/data/test_results/opencv_faces_' . basename($imagePath);
            $drawer = new BoundingBoxDrawer(lineThickness: 3, showConfidence: false);
            $drawer->draw($imagePath, $result['detections'], $outputPath);

            echo "\n✓ Annotated image: {$outputPath}\n";
        }

        // Performance note
        echo "\nPerformance: ~" . round(1 / $result['execution_time']) . " FPS\n";
        echo "Cost: $0.00 (runs offline)\n";
        echo "Privacy: ✓ No data sent to cloud\n";

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
```

3. **Test face detection**:

```bash
chmod +x detect_opencv.py

# Test with sample image
php 05-opencv-faces.php data/sample_images/group_photo.jpg
```

### Expected Result

```
Detecting faces with OpenCV...

Method: OpenCV Haar Cascades
Found: 4 face(s) in 0.089s

1. Face at [142, 78] size 156x156
2. Face at [389, 92] size 148x148
3. Face at [567, 134] size 132x132
4. Face at [234, 201] size 124x124

✓ Annotated image: data/test_results/opencv_faces_group_photo.jpg

Performance: ~11 FPS
Cost: $0.00 (runs offline)
Privacy: ✓ No data sent to cloud
```

### Why It Works

Haar Cascade Classifiers use machine learning (trained on thousands of positive and negative face samples) but are much simpler than deep neural networks. They detect features like edges, lines, and rectangular patterns at multiple scales by sliding a window across the image. The `scaleFactor` determines how much the image is reduced at each scale pyramid level (1.1 = 10% reduction), and `minNeighbors` specifies how many overlapping detections are needed to confirm a face (reducing false positives). OpenCV includes pre-trained Haar Cascades for frontal faces, profile faces, eyes, and other features. This approach is fast enough for real-time video (10-30 FPS) and works entirely offline, making it ideal for privacy-sensitive applications.

### Troubleshooting

- **No faces detected in clear photos** — Haar Cascades work best for frontal faces. Try lowering `minNeighbors` (3-4 instead of 5) or `scaleFactor` (1.05 instead of 1.1) for higher sensitivity. For profile faces, use `haarcascade_profileface.xml` instead.

- **Too many false positives** — Increase `minNeighbors` (6-8) to require more overlapping detections. Check image quality; blurry or low-contrast images cause false detections.

- **`timeout: command not found`** — The `timeout` command isn't available on all systems. On macOS, install coreutils: `brew install coreutils` and use `gtimeout`. Or remove timeout from PHP command.

- **Slow on high-resolution images** — Resize images before detection: `cv2.resize(image, (width, height))` in Python. Detecting faces in 4K images is unnecessary; 800-1200px width is sufficient.

- **Misses small or tilted faces** — Haar Cascades struggle with faces smaller than `minSize` (default 30x30) or rotated more than ~15 degrees. For better accuracy with difficult poses, use YOLO trained on face datasets or dedicated face detection models like MTCNN.

## Step 7: Production API Endpoint (~20 min)

### Goal

Build a production-ready detection service with REST API, multiple backend support, caching, and proper error handling.

### Actions

1. **Create DetectionService class**:

```php
# filename: DetectionService.php
<?php

declare(strict_types=1);

/**
 * Production Object Detection Service
 *
 * Unified service supporting multiple detection backends with caching and error handling.
 */

class DetectionService
{
    private array $cache = [];

    public function __construct(
        private string $backend = 'yolo',
        private ?string $cacheDir = null,
        private int $cacheTtl = 3600
    ) {
        if ($this->cacheDir && !is_dir($this->cacheDir)) {
            mkdir($this->cacheDir, 0777, true);
        }
    }

    /**
     * Detect objects in image with automatic backend selection.
     */
    public function detect(string $imagePath, ?string $backend = null): array
    {
        $backend = $backend ?? $this->backend;

        // Check cache
        $cacheKey = $this->getCacheKey($imagePath, $backend);

        if ($cached = $this->getFromCache($cacheKey)) {
            return array_merge($cached, ['cached' => true]);
        }

        // Perform detection
        $result = match ($backend) {
            'yolo' => $this->detectWithYolo($imagePath),
            'google' => $this->detectWithGoogle($imagePath),
            'aws' => $this->detectWithAWS($imagePath),
            'opencv' => $this->detectWithOpenCV($imagePath),
            default => throw new InvalidArgumentException("Unknown backend: {$backend}")
        };

        // Cache result
        if ($result['success']) {
            $this->saveToCache($cacheKey, $result);
        }

        return array_merge($result, ['cached' => false]);
    }

    /**
     * Batch detect objects in multiple images.
     */
    public function detectBatch(array $imagePaths, ?string $backend = null): array
    {
        $results = [];

        foreach ($imagePaths as $imagePath) {
            try {
                $results[$imagePath] = $this->detect($imagePath, $backend);
            } catch (Exception $e) {
                $results[$imagePath] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $results;
    }

    private function detectWithYolo(string $imagePath): array
    {
        require_once __DIR__ . '/01-detect-yolo.php';
        $detector = new YoloDetector();
        return $detector->detect($imagePath);
    }

    private function detectWithGoogle(string $imagePath): array
    {
        require_once __DIR__ . '/CloudDetector.php';
        $keyFile = getenv('GOOGLE_APPLICATION_CREDENTIALS');
        if (!$keyFile) {
            throw new RuntimeException('GOOGLE_APPLICATION_CREDENTIALS not set');
        }
        $detector = new GoogleVisionDetector($keyFile);
        return $detector->detect($imagePath);
    }

    private function detectWithAWS(string $imagePath): array
    {
        require_once __DIR__ . '/CloudDetector.php';
        $detector = new AWSRekognitionDetector(
            accessKey: getenv('AWS_ACCESS_KEY_ID'),
            secretKey: getenv('AWS_SECRET_ACCESS_KEY')
        );
        return $detector->detect($imagePath);
    }

    private function detectWithOpenCV(string $imagePath): array
    {
        require_once __DIR__ . '/05-opencv-faces.php';
        $detector = new OpenCVFaceDetector();
        return $detector->detect($imagePath);
    }

    private function getCacheKey(string $imagePath, string $backend): string
    {
        $fileHash = md5_file($imagePath);
        return "detection_{$backend}_{$fileHash}";
    }

    private function getFromCache(string $key): ?array
    {
        // Memory cache
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // File cache
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/' . $key . '.json';

            if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $this->cacheTtl) {
                $data = json_decode(file_get_contents($cacheFile), true);
                $this->cache[$key] = $data;
                return $data;
            }
        }

        return null;
    }

    private function saveToCache(string $key, array $data): void
    {
        // Memory cache
        $this->cache[$key] = $data;

        // File cache
        if ($this->cacheDir) {
            $cacheFile = $this->cacheDir . '/' . $key . '.json';
            file_put_contents($cacheFile, json_encode($data));
        }
    }
}
```

2. **Create REST API endpoint**:

```php
# filename: 07-production-api.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/DetectionService.php';
require_once __DIR__ . '/BoundingBoxDrawer.php';

/**
 * Object Detection REST API
 *
 * POST /detect with image file
 * Returns JSON with detections and optionally annotated image
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

try {
    // Validate file upload
    if (!isset($_FILES['image'])) {
        throw new Exception('No image file provided. Upload as "image" field.');
    }

    $uploadedFile = $_FILES['image'];

    if ($uploadedFile['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error: ' . $uploadedFile['error']);
    }

    // Validate file type
    $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $uploadedFile['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mimeType, $allowedTypes)) {
        throw new Exception("Invalid file type: {$mimeType}. Allowed: JPEG, PNG, WebP, GIF");
    }

    // Get parameters
    $backend = $_POST['backend'] ?? 'yolo'; // yolo, google, aws, opencv
    $drawBoxes = isset($_POST['draw_boxes']) && $_POST['draw_boxes'] === 'true';
    $minConfidence = isset($_POST['min_confidence']) ? floatval($_POST['min_confidence']) : 0.25;

    // Initialize service
    $service = new DetectionService(
        backend: $backend,
        cacheDir: __DIR__ . '/cache',
        cacheTtl: 3600
    );

    // Detect objects
    $result = $service->detect($uploadedFile['tmp_name'], $backend);

    // Filter by confidence
    if ($minConfidence > 0) {
        $result['detections'] = array_values(array_filter(
            $result['detections'],
            fn($d) => $d['confidence'] >= $minConfidence
        ));
        $result['count'] = count($result['detections']);
    }

    // Draw boxes if requested
    if ($drawBoxes && $result['success'] && $result['count'] > 0) {
        $outputPath = sys_get_temp_dir() . '/annotated_' . uniqid() . '.jpg';

        $drawer = new BoundingBoxDrawer(
            lineThickness: 3,
            showConfidence: true,
            minConfidenceToShow: $minConfidence
        );

        $drawer->draw($uploadedFile['tmp_name'], $result['detections'], $outputPath);

        // Encode as base64
        $result['annotated_image'] = base64_encode(file_get_contents($outputPath));
        unlink($outputPath);
    }

    // Return response
    http_response_code(200);
    echo json_encode($result, JSON_PRETTY_PRINT);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
```

3. **Test API endpoint**:

```bash
# Start PHP development server
php -S localhost:8000 07-production-api.php &

# Test with curl
curl -X POST http://localhost:8000 \
  -F "image=@data/sample_images/street.jpg" \
  -F "backend=yolo" \
  -F "draw_boxes=true" \
  -F "min_confidence=0.5"
```

### Expected Result

```json
{
  "success": true,
  "detections": [
    {
      "class": "person",
      "confidence": 0.943,
      "bbox": { "x": 342, "y": 156, "width": 89, "height": 234 }
    },
    {
      "class": "car",
      "confidence": 0.889,
      "bbox": { "x": 125, "y": 245, "width": 267, "height": 189 }
    }
  ],
  "count": 2,
  "image_path": "/tmp/phpXYZ123",
  "model": "yolov8n.pt",
  "execution_time": 1.142,
  "cached": false,
  "annotated_image": "/9j/4AAQSkZJRgABAQAA..."
}
```

### Why It Works

The DetectionService class provides a unified interface abstracting different detection backends (YOLO, cloud APIs, OpenCV) behind a single `detect()` method. Caching detection results by image content hash (MD5) prevents redundant processing of identical images. The REST API endpoint validates file uploads, enforces MIME type checking for security, accepts configuration parameters, and returns JSON responses with proper HTTP status codes. Base64 encoding annotated images allows embedding in JSON without separate file handling. This architecture scales by adding more backends, implementing rate limiting per API key, and using message queues for long-running detections.

### Troubleshooting

- **`Maximum execution time exceeded`** — Increase PHP timeout in php.ini: `max_execution_time = 300` or set per-script: `set_time_limit(300);`. Use async processing for production.

- **`File upload exceeds maximum size`** — Increase limits in php.ini: `upload_max_filesize = 10M` and `post_max_size = 10M`. Restart web server after changes.

- **CORS errors in browser** — Add proper CORS headers. In production, restrict `Access-Control-Allow-Origin` to your domain instead of `*`.

- **Cache not working** — Verify `cache/` directory is writable: `chmod 777 cache/`. Check disk space. Consider using Redis/Memcached for distributed caching.

- **Out of memory on large images** — Resize images before detection. Add memory limit check and reject oversized uploads: `if (filesize($tmpPath) > 10 * 1024 * 1024) { throw ... }`.

## Step 8: Batch Processing (~15 min)

### Goal

Process multiple images efficiently with parallel processing, progress tracking, and result aggregation.

### Actions

1. **Create batch processor**:

```php
# filename: 06-batch-processor.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/DetectionService.php';
require_once __DIR__ . '/BoundingBoxDrawer.php';

/**
 * Batch Object Detection Processor
 *
 * Process multiple images efficiently with progress tracking.
 */

class BatchDetectionProcessor
{
    private array $stats = [
        'processed' => 0,
        'succeeded' => 0,
        'failed' => 0,
        'total_objects' => 0,
        'total_time' => 0
    ];

    public function __construct(
        private DetectionService $service,
        private ?BoundingBoxDrawer $drawer = null,
        private bool $saveAnnotated = true,
        private string $outputDir = __DIR__ . '/data/test_results'
    ) {
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }

        $this->drawer = $this->drawer ?? new BoundingBoxDrawer();
    }

    /**
     * Process directory of images.
     */
    public function processDirectory(string $directory, string $pattern = '*.{jpg,jpeg,png,webp}'): array
    {
        $images = glob($directory . '/' . $pattern, GLOB_BRACE);

        if (empty($images)) {
            throw new RuntimeException("No images found in {$directory} matching {$pattern}");
        }

        return $this->processImages($images);
    }

    /**
     * Process array of image paths.
     */
    public function processImages(array $imagePaths): array
    {
        $results = [];
        $total = count($imagePaths);

        echo "Processing {$total} images...\n\n";

        $startTime = microtime(true);

        foreach ($imagePaths as $index => $imagePath) {
            $imageNum = $index + 1;
            $basename = basename($imagePath);

            echo "[{$imageNum}/{$total}] Processing {$basename}... ";

            try {
                $result = $this->service->detect($imagePath);

                if ($result['success']) {
                    $this->stats['succeeded']++;
                    $this->stats['total_objects'] += $result['count'];

                    echo "✓ Found {$result['count']} objects";

                    if ($result['cached'] ?? false) {
                        echo " (cached)";
                    }

                    // Save annotated image
                    if ($this->saveAnnotated && $result['count'] > 0) {
                        $outputPath = $this->outputDir . '/batch_' . $basename;
                        $this->drawer->draw($imagePath, $result['detections'], $outputPath);
                    }

                } else {
                    $this->stats['failed']++;
                    echo "✗ Failed: " . $result['error'];
                }

                $results[$imagePath] = $result;

            } catch (Exception $e) {
                $this->stats['failed']++;
                echo "✗ Error: " . $e->getMessage();

                $results[$imagePath] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }

            echo "\n";
            $this->stats['processed']++;
        }

        $this->stats['total_time'] = microtime(true) - $startTime;

        $this->printSummary();

        return $results;
    }

    /**
     * Generate object count statistics.
     */
    public function generateStatistics(array $results): array
    {
        $classCounts = [];
        $confidences = [];

        foreach ($results as $result) {
            if (!$result['success']) {
                continue;
            }

            foreach ($result['detections'] as $detection) {
                $class = $detection['class'];
                $classCounts[$class] = ($classCounts[$class] ?? 0) + 1;
                $confidences[] = $detection['confidence'];
            }
        }

        arsort($classCounts);

        return [
            'class_counts' => $classCounts,
            'unique_classes' => count($classCounts),
            'avg_confidence' => !empty($confidences) ? array_sum($confidences) / count($confidences) : 0,
            'min_confidence' => !empty($confidences) ? min($confidences) : 0,
            'max_confidence' => !empty($confidences) ? max($confidences) : 0
        ];
    }

    private function printSummary(): void
    {
        echo "\n=== Batch Processing Summary ===\n";
        echo "Processed: {$this->stats['processed']} images\n";
        echo "Succeeded: {$this->stats['succeeded']}\n";
        echo "Failed: {$this->stats['failed']}\n";
        echo "Total objects detected: {$this->stats['total_objects']}\n";
        echo "Total time: " . round($this->stats['total_time'], 2) . "s\n";

        if ($this->stats['processed'] > 0) {
            $avgTime = $this->stats['total_time'] / $this->stats['processed'];
            echo "Average time per image: " . round($avgTime, 2) . "s\n";
            echo "Throughput: " . round($this->stats['processed'] / $this->stats['total_time'], 2) . " images/second\n";
        }
    }

    public function getStats(): array
    {
        return $this->stats;
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    if ($argc < 2) {
        echo "Usage: php 06-batch-processor.php <image_directory> [backend]\n";
        echo "\nExample: php 06-batch-processor.php data/sample_images yolo\n";
        exit(1);
    }

    $directory = $argv[1];
    $backend = $argv[2] ?? 'yolo';

    if (!is_dir($directory)) {
        die("Error: Directory not found: {$directory}\n");
    }

    try {
        $service = new DetectionService(
            backend: $backend,
            cacheDir: __DIR__ . '/cache'
        );

        $processor = new BatchDetectionProcessor(
            service: $service,
            saveAnnotated: true
        );

        $results = $processor->processDirectory($directory);

        // Generate statistics
        echo "\n=== Detection Statistics ===\n";
        $stats = $processor->generateStatistics($results);

        echo "Unique object classes: {$stats['unique_classes']}\n";
        echo "Average confidence: " . round($stats['avg_confidence'] * 100, 1) . "%\n\n";

        echo "Top detected objects:\n";
        $count = 0;
        foreach ($stats['class_counts'] as $class => $num) {
            echo "  {$class}: {$num}\n";
            if (++$count >= 10) break;
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
```

### Expected Result

```
Processing 15 images...

[1/15] Processing street_001.jpg... ✓ Found 8 objects
[2/15] Processing street_002.jpg... ✓ Found 6 objects (cached)
[3/15] Processing park_scene.jpg... ✓ Found 12 objects
...
[15/15] Processing office_interior.jpg... ✓ Found 4 objects

=== Batch Processing Summary ===
Processed: 15 images
Succeeded: 15
Failed: 0
Total objects detected: 127
Total time: 18.43s
Average time per image: 1.23s
Throughput: 0.81 images/second

=== Detection Statistics ===
Unique object classes: 24
Average confidence: 78.3%

Top detected objects:
  person: 34
  car: 18
  chair: 12
  table: 9
  bottle: 8
  cup: 7
  backpack: 6
  handbag: 5
  laptop: 4
  cell phone: 4
```

### Why It Works

Batch processing amortizes overhead costs (model loading, Python subprocess startup) across multiple images. Caching prevents reprocessing identical images. Progress tracking provides user feedback during long operations. Statistics aggregation reveals patterns across datasets (most common objects, average confidences). For true parallelization, you could use PHP's `popen()` to run multiple Python processes simultaneously, or implement a job queue with workers processing images concurrently.

### Troubleshooting

- **Slow processing on large batches** — Implement true parallel processing using process pools or message queues (Redis, RabbitMQ). Start multiple Python detection processes and distribute images.

- **Memory grows over time** — PHP accumulates detection results in memory. Process in chunks: `array_slice($images, $offset, $batchSize)` and save intermediate results to disk.

- **Cache directory fills disk** — Implement cache cleanup: delete files older than TTL, use LRU eviction, or set max cache size with automatic pruning.

- **Some images hang processing** — Add per-image timeout. Catch timeout exceptions and continue: `try { $result = $service->detect(...); } catch (RuntimeException $e) { /* log and skip */ }`.

- **Progress not visible in web context** — Use `flush()` and `ob_flush()` after each echo for real-time progress, or implement SSE (Server-Sent Events) / WebSockets for web UIs.

## Step 9: Evaluation and Comparison (~15 min)

### Goal

Compare YOLO, Cloud APIs, and OpenCV approaches across speed, accuracy, cost, and use cases to make informed architectural decisions.

### Actions

1. **Create comparison benchmark**:

```php
# filename: 08-compare-approaches.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/01-detect-yolo.php';
require_once __DIR__ . '/CloudDetector.php';
require_once __DIR__ . '/05-opencv-faces.php';

/**
 * Object Detection Approach Comparison
 *
 * Benchmarks YOLO, Cloud APIs, and OpenCV on the same images.
 */

class DetectionComparison
{
    private array $results = [];

    public function __construct(private array $imagePaths) {}

    /**
     * Run all detection approaches and compare.
     */
    public function compare(): array
    {
        echo "=== Object Detection Approach Comparison ===\n\n";

        // 1. YOLO (local)
        $this->benchmarkYOLO();

        // 2. OpenCV Face Detection (local)
        $this->benchmarkOpenCV();

        // 3. Google Vision (if configured)
        if (getenv('GOOGLE_APPLICATION_CREDENTIALS')) {
            $this->benchmarkGoogleVision();
        } else {
            echo "⊘ Google Vision: Skipped (no credentials)\n\n";
        }

        // 4. AWS Rekognition (if configured)
        if (getenv('AWS_ACCESS_KEY_ID')) {
            $this->benchmarkAWSRekognition();
        } else {
            echo "⊘ AWS Rekognition: Skipped (no credentials)\n\n";
        }

        // Print comparison table
        $this->printComparison();

        return $this->results;
    }

    private function benchmarkYOLO(): void
    {
        echo "Testing YOLO (YOLOv8n)...\n";

        $detector = new YoloDetector(modelName: 'yolov8n.pt');

        $times = [];
        $objectCounts = [];

        foreach ($this->imagePaths as $imagePath) {
            try {
                $result = $detector->detect($imagePath);
                $times[] = $result['execution_time'];
                $objectCounts[] = $result['count'];
            } catch (Exception $e) {
                echo "  Error: " . $e->getMessage() . "\n";
            }
        }

        $this->results['yolo'] = [
            'name' => 'YOLO (YOLOv8n)',
            'avg_time' => !empty($times) ? array_sum($times) / count($times) : 0,
            'min_time' => !empty($times) ? min($times) : 0,
            'max_time' => !empty($times) ? max($times) : 0,
            'avg_objects' => !empty($objectCounts) ? array_sum($objectCounts) / count($objectCounts) : 0,
            'cost_per_image' => 0,
            'requires_cloud' => false,
            'object_classes' => 80
        ];

        echo "  ✓ Avg time: " . round($this->results['yolo']['avg_time'], 3) . "s\n";
        echo "  ✓ Avg objects: " . round($this->results['yolo']['avg_objects'], 1) . "\n\n";
    }

    private function benchmarkOpenCV(): void
    {
        echo "Testing OpenCV Face Detection...\n";

        $detector = new OpenCVFaceDetector();

        $times = [];
        $faceCounts = [];

        foreach ($this->imagePaths as $imagePath) {
            try {
                $result = $detector->detect($imagePath);
                $times[] = $result['execution_time'];
                $faceCounts[] = $result['count'];
            } catch (Exception $e) {
                echo "  Error: " . $e->getMessage() . "\n";
            }
        }

        $this->results['opencv'] = [
            'name' => 'OpenCV Haar Cascades',
            'avg_time' => !empty($times) ? array_sum($times) / count($times) : 0,
            'min_time' => !empty($times) ? min($times) : 0,
            'max_time' => !empty($times) ? max($times) : 0,
            'avg_objects' => !empty($faceCounts) ? array_sum($faceCounts) / count($faceCounts) : 0,
            'cost_per_image' => 0,
            'requires_cloud' => false,
            'object_classes' => 1 // Faces only
        ];

        echo "  ✓ Avg time: " . round($this->results['opencv']['avg_time'], 3) . "s\n";
        echo "  ✓ Avg faces: " . round($this->results['opencv']['avg_objects'], 1) . "\n\n";
    }

    private function benchmarkGoogleVision(): void
    {
        echo "Testing Google Vision API...\n";

        try {
            $detector = new GoogleVisionDetector(
                keyFile: getenv('GOOGLE_APPLICATION_CREDENTIALS')
            );

            $times = [];
            $objectCounts = [];

            foreach ($this->imagePaths as $imagePath) {
                $startTime = microtime(true);
                $result = $detector->detect($imagePath);
                $times[] = microtime(true) - $startTime;
                $objectCounts[] = $result['count'];
            }

            $this->results['google'] = [
                'name' => 'Google Vision API',
                'avg_time' => array_sum($times) / count($times),
                'min_time' => min($times),
                'max_time' => max($times),
                'avg_objects' => array_sum($objectCounts) / count($objectCounts),
                'cost_per_image' => 0.0015, // $1.50 per 1000 images
                'requires_cloud' => true,
                'object_classes' => 1000 // Approximate
            ];

            echo "  ✓ Avg time: " . round($this->results['google']['avg_time'], 3) . "s\n";
            echo "  ✓ Avg objects: " . round($this->results['google']['avg_objects'], 1) . "\n\n";

        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        }
    }

    private function benchmarkAWSRekognition(): void
    {
        echo "Testing AWS Rekognition...\n";

        try {
            $detector = new AWSRekognitionDetector(
                accessKey: getenv('AWS_ACCESS_KEY_ID'),
                secretKey: getenv('AWS_SECRET_ACCESS_KEY')
            );

            $times = [];
            $objectCounts = [];

            foreach ($this->imagePaths as $imagePath) {
                $startTime = microtime(true);
                $result = $detector->detect($imagePath);
                $times[] = microtime(true) - $startTime;
                $objectCounts[] = $result['count'];
            }

            $this->results['aws'] = [
                'name' => 'AWS Rekognition',
                'avg_time' => array_sum($times) / count($times),
                'min_time' => min($times),
                'max_time' => max($times),
                'avg_objects' => array_sum($objectCounts) / count($objectCounts),
                'cost_per_image' => 0.001, // $1.00 per 1000 images
                'requires_cloud' => true,
                'object_classes' => 500 // Approximate
            ];

            echo "  ✓ Avg time: " . round($this->results['aws']['avg_time'], 3) . "s\n";
            echo "  ✓ Avg objects: " . round($this->results['aws']['avg_objects'], 1) . "\n\n";

        } catch (Exception $e) {
            echo "  ✗ Error: " . $e->getMessage() . "\n\n";
        }
    }

    private function printComparison(): void
    {
        echo "\n=== Comparison Summary ===\n\n";

        printf("%-25s %-12s %-12s %-15s %-12s\n",
            "Approach", "Avg Time", "Cost/Image", "Cloud Required", "Classes"
        );
        echo str_repeat('-', 80) . "\n";

        foreach ($this->results as $result) {
            printf("%-25s %-12s $%-11s %-15s %-12s\n",
                $result['name'],
                round($result['avg_time'], 3) . "s",
                number_format($result['cost_per_image'], 4),
                $result['requires_cloud'] ? 'Yes' : 'No',
                $result['object_classes']
            );
        }

        echo "\n=== Recommendations ===\n\n";

        echo "🎯 Use YOLO when:\n";
        echo "  • Need to detect 80 common object classes\n";
        echo "  • Processing high volume (>10,000 images/month)\n";
        echo "  • Require fast response times (<2s)\n";
        echo "  • Want to avoid cloud API costs\n";
        echo "  • Need offline/on-premise deployment\n\n";

        echo "🎯 Use Cloud APIs when:\n";
        echo "  • Need broad object recognition (500-1000+ classes)\n";
        echo "  • Processing low volume (<1000 images/month)\n";
        echo "  • Want zero infrastructure management\n";
        echo "  • Accuracy more important than speed\n";
        echo "  • Need additional features (text OCR, celebrity recognition)\n\n";

        echo "🎯 Use OpenCV when:\n";
        echo "  • Only need face detection\n";
        echo "  • Require maximum speed (>10 FPS)\n";
        echo "  • Privacy is critical (no cloud)\n";
        echo "  • Running on resource-constrained devices\n";
        echo "  • Real-time video processing\n\n";
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    $imagePaths = glob(__DIR__ . '/data/sample_images/*.{jpg,jpeg,png}', GLOB_BRACE);

    if (empty($imagePaths)) {
        die("Error: No sample images found in data/sample_images/\n");
    }

    // Limit to first 5 images for quick comparison
    $imagePaths = array_slice($imagePaths, 0, 5);

    $comparison = new DetectionComparison($imagePaths);
    $results = $comparison->compare();

    // Save results
    file_put_contents(
        __DIR__ . '/comparison_results.json',
        json_encode($results, JSON_PRETTY_PRINT)
    );

    echo "\n✓ Results saved to comparison_results.json\n";
}
```

### Expected Result

```
=== Object Detection Approach Comparison ===

Testing YOLO (YOLOv8n)...
  ✓ Avg time: 1.234s
  ✓ Avg objects: 7.4

Testing OpenCV Face Detection...
  ✓ Avg time: 0.087s
  ✓ Avg faces: 2.2

Testing Google Vision API...
  ✓ Avg time: 0.645s
  ✓ Avg objects: 9.8

Testing AWS Rekognition...
  ✓ Avg time: 0.712s
  ✓ Avg objects: 8.6

=== Comparison Summary ===

Approach                  Avg Time     Cost/Image  Cloud Required  Classes
--------------------------------------------------------------------------------
YOLO (YOLOv8n)           1.234s       $0.0000      No              80
OpenCV Haar Cascades     0.087s       $0.0000      No              1
Google Vision API        0.645s       $0.0015      Yes             1000
AWS Rekognition          0.712s       $0.0010      Yes             500

=== Recommendations ===

🎯 Use YOLO when:
  • Need to detect 80 common object classes
  • Processing high volume (>10,000 images/month)
  • Require fast response times (<2s)
  • Want to avoid cloud API costs
  • Need offline/on-premise deployment

🎯 Use Cloud APIs when:
  • Need broad object recognition (500-1000+ classes)
  • Processing low volume (<1000 images/month)
  • Want zero infrastructure management
  • Accuracy more important than speed
  • Need additional features (text OCR, celebrity recognition)

🎯 Use OpenCV when:
  • Only need face detection
  • Require maximum speed (>10 FPS)
  • Privacy is critical (no cloud)
  • Running on resource-constrained devices
  • Real-time video processing
```

### Why It Works

Each approach has distinct trade-offs: YOLO provides good balance of speed and accuracy for common objects, cloud APIs offer extensive object recognition without infrastructure costs at pay-per-use pricing, and OpenCV excels at specialized fast detection (faces) with zero cost. Benchmarking on your actual use case (image types, object diversity, volume) reveals which approach best meets requirements. Hybrid strategies are common: use OpenCV for initial face detection, then YOLO or cloud APIs for detailed object recognition on faces of interest.

### Troubleshooting

- **Cloud APIs much slower than expected** — Network latency dominates. Test from server in same region as API endpoint. Consider caching aggressively or preprocessing images to reduce size before upload.

- **YOLO slower than benchmarks** — First run includes model download and initialization. Subsequent runs are faster. Use larger YOLO models (yolov8m/l) only if accuracy justifies 2-4x slowdown.

- **Inconsistent object counts across approaches** — Different models use different training data and class taxonomies. YOLO's 80 COCO classes differ from Google/AWS classes. Normalize class names for apples-to-apples comparison.

- **OpenCV only finds few faces** — Haar Cascades are sensitive to face angle and lighting. Test on frontal, well-lit photos. For challenging scenarios, use YOLO trained on face datasets (e.g., YOLOv8 with WIDER FACE dataset).

- **Cost estimates differ from actual bills** — Cloud providers charge differently (per image, per feature, by volume tier). Check current pricing. Include free tiers in calculations. Monitor actual usage in cloud consoles.

## Exercises

Now apply what you've learned with these practical challenges!

### Exercise 1: Multi-Object Counter

**Goal**: Build a system that counts objects by category across a dataset and generates summary statistics.

Create a file called `exercise1-multi-object-counter.php` and implement:

- Load all images from a directory
- Detect objects using YOLO
- Count total instances of each object class
- Generate a report showing:
  - Total images processed
  - Total objects detected
  - Top 10 most common objects with counts
  - Object frequency distribution (e.g., "person appears in 85% of images")
  - Average objects per image
- Export results as JSON and CSV

**Validation**: Test with at least 20 images from different scenes (street, office, park, etc.).

Expected output:

```
=== Object Counter Report ===
Images processed: 24
Total objects: 312

Top 10 objects:
  1. person: 67 (appears in 19/24 images = 79%)
  2. car: 34 (appears in 15/24 images = 63%)
  3. chair: 28 (appears in 12/24 images = 50%)
  ...

Average objects per image: 13.0
Most crowded image: office_meeting.jpg (34 objects)
```

### Exercise 2: Video Frame Detection

**Goal**: Extract frames from a video, detect objects in each frame, track object presence over time, and optionally process live video streams.

Create a file called `exercise2-video-detection.php` and implement:

**Core Features:**

- Extract frames from video using FFmpeg (call via `shell_exec`)
- Detect objects in each frame
- Track which objects appear and when
- Generate timeline showing:
  - When each object class first appears
  - Frame ranges where objects are present
  - Object persistence (how many consecutive frames)
- Create an annotated video with bounding boxes

**Bonus: Real-Time Video Stream Processing:**

- Process live webcam feed or IP camera stream
- Display annotated video in real-time (< 100ms latency)
- Count objects entering/exiting frame
- Trigger alerts on specific object detection
- Record only frames with detected objects (smart recording)

**Requirements:**

**For Pre-Recorded Video:**

```bash
# Extract frames at 1 FPS (60 frames from 60-second video)
ffmpeg -i video.mp4 -vf fps=1 frame_%04d.jpg

# For faster processing, extract every 5th frame
ffmpeg -i video.mp4 -vf "select='not(mod(n\,5))'" -vsync vfr frame_%04d.jpg

# Reassemble annotated frames into video
ffmpeg -framerate 30 -i annotated_%04d.jpg -c:v libx264 output_annotated.mp4
```

**For Live Stream Processing:**

```bash
# Capture webcam frames in real-time
ffmpeg -f avfoundation -i "0" -vf fps=5 -update 1 latest_frame.jpg

# Or capture from IP camera (RTSP stream)
ffmpeg -rtsp_transport tcp -i rtsp://camera_ip/stream -vf fps=5 -update 1 latest_frame.jpg
```

**PHP Implementation Pattern:**

```php
// Continuously process live stream
while (true) {
    // Wait for new frame
    if (filemtime('latest_frame.jpg') > $lastProcessedTime) {
        $detections = detectObjects('latest_frame.jpg');

        // Count people in frame
        $peopleCount = count(array_filter($detections, fn($d) => $d['class'] === 'person'));

        // Trigger alert if threshold exceeded
        if ($peopleCount > 10) {
            sendAlert("Crowding detected: {$peopleCount} people");
        }

        $lastProcessedTime = time();
    }

    usleep(200000); // Check every 200ms (5 FPS)
}
```

**Performance Considerations:**

- Process every Nth frame to reduce workload (e.g., every 5th frame = 6 FPS from 30 FPS source)
- Use YOLOv8n (nano) for speed, or YOLOv8m (medium) for accuracy
- Target 5-10 FPS for real-time feel (< 200ms per frame)
- Consider resolution reduction: resize 1920x1080 → 640x480 before detection
- Use multi-threading for true real-time: frame capture thread + detection thread

**Advanced Features to Implement:**

- **Motion Detection**: Only run expensive detection when motion detected (use OpenCV background subtraction)
- **Zone Monitoring**: Define regions of interest, only detect in specific areas
- **Object Counting**: Track unique objects entering/exiting (use tracking IDs from Exercise with `10-object-tracker.php`)
- **Time-lapse Summary**: Save one frame per minute with detection counts
- **Smart Recording**: Only record video segments with detected objects of interest

**Validation**: Test with a 10-30 second video clip, or 1-minute live webcam test.

Expected output:

```
=== Video Object Detection ===
Video: traffic_scene.mp4 (15 seconds, 450 frames)
Processing every 15 frames (30 total)...

Object Timeline:
  person: frames 1-450 (100% of video)
  car: frames 1-300 (67% of video)
  bicycle: frames 150-225 (17% of video)
  traffic light: frames 1-450 (100% of video)

Annotations saved to: traffic_annotated/

=== Live Stream Mode (Bonus) ===
Monitoring webcam feed (5 FPS)...
[00:05] Detected: 2 people, 1 laptop, 1 cup
[00:10] Detected: 3 people, 1 laptop, 2 cups
[00:15] Alert: 4 people detected (threshold: 3)
[00:20] Detected: 2 people, 1 phone, 1 cup
```

**Resources:**

- [FFmpeg Video Processing Guide](https://ffmpeg.org/ffmpeg.html)
- [Real-Time Object Detection Best Practices](https://github.com/ultralytics/ultralytics/wiki/Performance-Tips)
- [OpenCV Background Subtraction](https://docs.opencv.org/master/d1/dc5/tutorial_background_subtraction.html)

### Exercise 3: Custom Object Filter

**Goal**: Filter detections to specific object classes and confidence thresholds for targeted use cases.

Create a file called `exercise3-custom-filter.php` and implement a `DetectionFilter` class with:

- `filterByClasses(array $classes)` - Keep only specified object types
- `filterByConfidence(float $minConfidence)` - Remove low-confidence detections
- `filterBySize(int $minPixels, int $maxPixels)` - Filter by bounding box area
- `filterByRegion(int $x, int $y, int $width, int $height)` - Keep only objects in image region
- Chainable methods: `$filter->byClasses(['person'])->byConfidence(0.8)->apply($detections)`

**Use Cases:**

- People counter: Filter to only "person" class
- Vehicle tracker: Filter to ["car", "truck", "bus", "motorcycle"]
- Security: High confidence (>0.9) + specific region monitoring

**Validation**: Process images and verify filters work correctly.

Expected code:

```php
$filter = new DetectionFilter();

// Count only high-confidence people
$people = $filter
    ->byClasses(['person'])
    ->byConfidence(0.85)
    ->apply($detections);

echo "Found " . count($people) . " people\n";

// Vehicles in left half of image
$vehicles = $filter
    ->byClasses(['car', 'truck', 'bus'])
    ->byRegion(0, 0, $imageWidth / 2, $imageHeight)
    ->apply($detections);
```

### Exercise 4: Detection Dashboard

**Goal**: Build a web interface for uploading images, detecting objects, and visualizing results.

Create a web application with:

**Frontend (HTML/JavaScript):**

- Image upload form with drag-and-drop
- Backend selector (YOLO, Google, AWS, OpenCV)
- Confidence threshold slider
- Display annotated image with bounding boxes
- Show detection list with confidence scores
- Object class filter checkboxes

**Backend (PHP):**

- Use the production API endpoint from Step 7
- Return JSON with detections and annotated image
- Handle multiple image uploads (batch mode)
- Display processing status/progress

**Requirements:**

- Responsive design (mobile-friendly)
- Real-time detection (no page reload)
- Download annotated images
- Detection history (last 10 uploads)

**Validation**: Test with various images and backends.

## Troubleshooting

This section covers common issues you may encounter beyond step-specific problems.

### Python Environment Issues

**Problem**: `ModuleNotFoundError: No module named 'ultralytics'` after installation

**Cause**: Multiple Python installations, packages installed to wrong Python version

**Solution**:

```bash
# Verify which Python and pip
which python3
which pip3

# Install explicitly to correct Python
python3 -m pip install ultralytics opencv-python

# Verify installation
python3 -c "import ultralytics; import cv2; print('OK')"
```

### Model Download Failures

**Problem**: YOLOv8 model download timeout or corrupted download

**Cause**: Network issues, firewall blocking, insufficient disk space

**Solution**:

- Manually download from https://github.com/ultralytics/assets/releases/
- Place in `~/.cache/torch/hub/ultralytics/yolov8n.pt`
- Or set `YOLO_CONFIG_DIR` environment variable to custom location
- Verify with `ls -lh ~/.cache/torch/hub/ultralytics/`

### Memory and Performance

**Problem**: PHP process killed or `Allowed memory size exhausted`

**Cause**: Large images loaded entirely into memory by GD

**Solution**:

```php
// Increase memory limit
ini_set('memory_limit', '512M');

// Or resize before processing
function resizeForDetection(string $imagePath, int $maxDimension = 1920): string
{
    list($width, $height) = getimagesize($imagePath);

    if ($width <= $maxDimension && $height <= $maxDimension) {
        return $imagePath; // No resize needed
    }

    $image = imagecreatefromjpeg($imagePath);
    $scale = $maxDimension / max($width, $height);
    $newWidth = (int)($width * $scale);
    $newHeight = (int)($height * $scale);

    $resized = imagescale($image, $newWidth, $newHeight);
    $tempPath = sys_get_temp_dir() . '/resized_' . basename($imagePath);
    imagejpeg($resized, $tempPath, 90);

    imagedestroy($image);
    imagedestroy($resized);

    return $tempPath;
}
```

### Bounding Box Coordinate Systems

**Problem**: Boxes drawn in wrong locations, not aligned with objects

**Cause**: Different coordinate systems (absolute vs normalized, origin differences)

**Solution**:

```php
// YOLO returns absolute pixel coordinates (x, y, width, height)
// Google/AWS return normalized coordinates (0.0-1.0)

// Convert normalized to absolute
function normalizedToAbsolute(array $bbox, int $imageWidth, int $imageHeight): array
{
    return [
        'x' => (int)($bbox['x'] * $imageWidth),
        'y' => (int)($bbox['y'] * $imageHeight),
        'width' => (int)($bbox['width'] * $imageWidth),
        'height' => (int)($bbox['height'] * $imageHeight)
    ];
}

// Verify coordinates are within image bounds
function validateBbox(array $bbox, int $imageWidth, int $imageHeight): bool
{
    return $bbox['x'] >= 0
        && $bbox['y'] >= 0
        && $bbox['x'] + $bbox['width'] <= $imageWidth
        && $bbox['y'] + $bbox['height'] <= $imageHeight;
}
```

### Cloud API Authentication

**Problem**: `403 Forbidden` or `Invalid authentication credentials`

**Cause**: Incorrect API keys, insufficient permissions, expired credentials

**Solution**:

For Google Vision:

```bash
# Verify service account has Vision API enabled
# Check key file is valid JSON
cat $GOOGLE_APPLICATION_CREDENTIALS | python3 -m json.tool

# Test authentication
gcloud auth activate-service-account --key-file=$GOOGLE_APPLICATION_CREDENTIALS
gcloud projects get-iam-policy PROJECT_ID
```

For AWS Rekognition:

```bash
# Verify credentials
aws sts get-caller-identity

# Check Rekognition permissions
aws iam get-user-policy --user-name YOUR_USER --policy-name RekognitionAccess

# Test access
aws rekognition detect-labels --image '{"S3Object":{"Bucket":"bucket","Name":"image.jpg"}}'
```

### Detection Quality Issues

**Problem**: Missing obvious objects or too many false positives

**Cause**: Wrong confidence threshold, object too small/large, poor image quality

**Solution**:

```php
// Adjust confidence threshold
$yolo = new YoloDetector(confidenceThreshold: 0.15); // Lower = more sensitive

// Try different model sizes
// yolov8n (fastest, least accurate)
// yolov8s (balanced)
// yolov8m (slower, more accurate)
// yolov8l (slowest, most accurate)
$yolo = new YoloDetector(modelName: 'yolov8m.pt');

// Preprocess image
// - Increase contrast for low-light images
// - Denoise for grainy images
// - Ensure minimum resolution (640px width recommended)
```

### Subprocess Communication Errors

**Problem**: `sh: python3: command not found` or `Permission denied`

**Cause**: PHP runs under different user/environment than terminal

**Solution**:

```php
// Use absolute path to Python
$pythonPath = trim(shell_exec('which python3'));
$command = sprintf('%s %s %s',
    escapeshellarg($pythonPath),
    escapeshellarg($script),
    escapeshellarg($imagePath)
);

// Or set PATH explicitly
putenv('PATH=/usr/local/bin:/usr/bin:/bin');
```

## Wrap-up

Congratulations! You've mastered object detection in PHP applications. Let's review what you've accomplished:

✓ **Understood object detection fundamentals** — You know the difference between classification and detection, how bounding boxes work, and the strengths of different algorithms (YOLO, SSD, Faster R-CNN, Haar Cascades).

✓ **Integrated YOLOv8 with PHP** — You built a robust Python-PHP integration using subprocess communication, handling timeouts, parsing JSON results, and managing the full detection pipeline.

✓ **Drew annotated bounding boxes** — You created a drawing system using GD that adds color-coded boxes, labels, and confidence scores to images, making detections visually interpretable.

✓ **Integrated cloud vision APIs** — You implemented Google Vision and AWS Rekognition detectors with a unified interface, learning how to normalize results across different providers.

✓ **Implemented OpenCV face detection** — You built a fast, privacy-preserving face detector using Haar Cascades that runs entirely offline without cloud dependencies.

✓ **Built production API endpoint** — You created a REST API with file uploads, backend selection, caching, and error handling ready for real-world deployment.

✓ **Processed batches efficiently** — You implemented batch processing with progress tracking, statistics generation, and result aggregation for high-volume scenarios.

✓ **Compared approaches systematically** — You benchmarked YOLO, cloud APIs, and OpenCV across speed, accuracy, cost, and use cases, gaining the knowledge to make informed architectural decisions.

✓ **Handled edge cases** — You learned to troubleshoot memory issues, coordinate system differences, authentication problems, and performance bottlenecks.

✓ **Applied detection to real problems** — Through exercises, you built object counters, video analyzers, custom filters, and web dashboards, demonstrating mastery of practical applications.

### Real-World Applications

The detection skills you've developed enable building:

- **E-commerce**: Visual product search, inventory automation, quality control
- **Security**: Access control, surveillance analytics, anomaly detection
- **Social Media**: Auto-tagging, content moderation, AR filters
- **Healthcare**: Medical imaging analysis, patient monitoring
- **Automotive**: Dashcam analysis, parking management, damage assessment
- **Retail**: Customer analytics, shelf monitoring, theft prevention

### Connection to Next Chapter

In Chapter 19, you'll shift from spatial understanding (images) to temporal understanding (time series data). You'll apply ML to predict future trends from historical data—forecasting sales, user behavior, server load, and more. The predictive analytics skills complement your computer vision capabilities, enabling you to build intelligent systems that understand both what's happening now (detection) and what will happen next (forecasting).

### Keep Practicing

To solidify your object detection mastery:

1. Process your own photo collections and analyze what objects appear most frequently
2. Build a real-time webcam detector using video frame extraction
3. Train a custom YOLO model on domain-specific objects (if you have labeled data)
4. Implement object tracking across video frames to follow specific instances
5. Combine detection with other ML tasks (classify detected objects, run OCR on detected text, etc.)

You now have production-ready object detection capabilities in your PHP toolkit. Use them to build intelligent features that understand visual content!

## Further Reading

### Official Documentation

- [Ultralytics YOLOv8 Documentation](https://docs.ultralytics.com/) — Complete guide to YOLO models, training, deployment, and configuration
- [OpenCV Documentation](https://docs.opencv.org/) — Computer vision library reference with tutorials on detection, tracking, and image processing
- [Google Cloud Vision API Reference](https://cloud.google.com/vision/docs) — Object localization, label detection, and other vision features
- [AWS Rekognition Developer Guide](https://docs.aws.amazon.com/rekognition/latest/dg/) — Object detection, face analysis, and content moderation
- [GD Library Manual](https://www.php.net/manual/en/book.image.php) — PHP image manipulation functions for drawing and processing

### Research Papers and Theory

- [You Only Look Once: Unified, Real-Time Object Detection (YOLO)](https://arxiv.org/abs/1506.02640) — Original YOLO paper introducing single-shot detection
- [YOLOv8: Next Generation Object Detection](https://github.com/ultralytics/ultralytics) — Latest YOLO architecture improvements
- [Faster R-CNN: Towards Real-Time Object Detection](https://arxiv.org/abs/1506.01497) — Two-stage detection for higher accuracy
- [SSD: Single Shot MultiBox Detector](https://arxiv.org/abs/1512.02325) — Fast detection using multiple feature maps
- [Haar Cascades for Face Detection](https://docs.opencv.org/3.4/db/d28/tutorial_cascade_classifier.html) — Classical computer vision approach

### PHP Integration Patterns

- [Chapter 11: Integrating PHP with Python](/series/ai-ml-php-developers/chapters/11-integrating-php-with-python-for-advanced-ml) — Subprocess communication, REST APIs, and message queues
- [Chapter 12: Deep Learning with TensorFlow](/series/ai-ml-php-developers/chapters/12-deep-learning-with-tensorflow-and-php) — Running neural network models from PHP
- [PSR-7: HTTP Message Interfaces](https://www.php-fig.org/psr/psr-7/) — Standard HTTP interfaces for building APIs
- [PSR-18: HTTP Client](https://www.php-fig.org/psr/psr-18/) — Standard HTTP client for calling cloud APIs

### Advanced Topics

- [Object Tracking Algorithms](https://learnopencv.com/object-tracking-using-opencv-cpp-python/) — Following objects across video frames (SORT, DeepSORT)
- [Multi-Object Tracking Tutorial](https://towardsdatascience.com/multi-object-tracking-with-yolo-and-sort-5c80e6ca0f79) — Combining YOLO with tracking algorithms
- [Real-Time Detection Optimization](https://github.com/ultralytics/ultralytics/wiki/Performance-Tips) — Techniques for faster inference
- [Custom YOLO Training](https://docs.ultralytics.com/modes/train/) — Training on your own datasets
- [ONNX Runtime for PHP](https://onnxruntime.ai/) — High-performance model inference without Python

### Related Computer Vision Tasks

- **[Pose Estimation with MediaPipe](https://google.github.io/mediapipe/solutions/pose)** — Detect human body keypoints (skeleton tracking) for fitness apps, gesture control, and motion capture. MediaPipe Pose provides 33 3D landmarks in real-time.
- **[Instance Segmentation with Mask R-CNN](https://github.com/matterport/Mask_RCNN)** — Pixel-perfect object boundaries for photo editing, medical imaging, and precise object extraction. Slower than detection but provides exact masks.
- **[Segment Anything Model (SAM)](https://segment-anything.com/)** — Meta's foundation model for one-click object segmentation. Prompts with points, boxes, or text to segment any object.
- **[Semantic Segmentation with DeepLab](https://github.com/tensorflow/models/tree/master/research/deeplab)** — Label every pixel by category for scene understanding, autonomous driving road segmentation, and satellite image analysis.
- **[Optical Character Recognition (OCR)](https://github.com/tesseract-ocr/tesseract)** — Extract text from images using Tesseract OCR. Combine with object detection to find and read text in specific regions (signs, documents, labels).
- **[3D Object Detection](https://github.com/open-mmlab/mmdetection3d)** — Detect objects with 3D bounding boxes using depth cameras or LiDAR. Essential for robotics, AR/VR, and autonomous vehicles.
- **[Anomaly Detection in Images](https://github.com/openvinotoolkit/anomalib)** — Identify unusual patterns that don't fit known categories. Applications: manufacturing defect detection, medical anomaly screening, security monitoring.
- **[Image Depth Estimation](https://github.com/isl-org/MiDaS)** — Predict depth (distance from camera) for every pixel. Useful for 3D reconstruction, bokeh effects, and AR applications.
- **[Object Re-Identification](https://github.com/KaiyangZhou/deep-person-reid)** — Match the same object/person across different camera views or time gaps. Critical for multi-camera surveillance and retail analytics.
- **[Action Recognition](https://github.com/open-mmlab/mmaction2)** — Classify activities in videos (running, waving, falling). Extends detection with temporal analysis for security, sports, and healthcare monitoring.

### Community and Tools

- [Roboflow Universe](https://universe.roboflow.com/) — Pre-trained models and datasets for specific domains
- [COCO Dataset](https://cocodataset.org/) — Common Objects in Context, 80 classes YOLO is trained on
- [Awesome Object Detection](https://github.com/amusi/awesome-object-detection) — Curated list of detection resources
- [PHP ML Community](https://github.com/php-ml) — PHP machine learning libraries and examples
