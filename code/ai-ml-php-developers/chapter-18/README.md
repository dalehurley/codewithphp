# Chapter 18: Object Detection and Recognition

Complete code examples for object detection using YOLO, Cloud APIs, and OpenCV.

## Quick Start

### 1. Install Python Dependencies

```bash
pip3 install -r requirements.txt
```

This installs:

- **ultralytics** (YOLOv8)
- **opencv-python** (Face detection)
- **numpy** and **Pillow** (Image processing)

### 2. Install PHP Dependencies (Optional)

For cloud API examples:

```bash
composer install
```

This installs:

- **google/cloud-vision** (Google Vision API)
- **aws/aws-sdk-php** (AWS Rekognition)

### 3. Verify Setup

```bash
php verify-setup.php
```

Should show:

- ✓ PHP 8.6+
- ✓ GD extension
- ✓ Python 3.10+
- ✓ Ultralytics installed
- ✓ OpenCV installed

## Code Examples

> **Note**: This directory contains core files extracted from the tutorial. Additional examples (02-08) are fully documented in the chapter and can be copied from there if needed. All essential functionality is available in the files below.

### Detection Backends

**✅ Available Files** (Ready to run):

- **`verify-setup.php`** — Verify environment is ready
- **`detect_yolo.py`** — Python YOLO detection script
- **`detect_opencv.py`** — Python OpenCV face detection
- **`09-confidence-filter.php`** — Filter detections by confidence
- **`10-object-tracker.php`** — Track objects across frames
- **`solutions/exercise1-multi-object-counter.php`** — Count objects exercise
- **`solutions/exercise3-custom-filter.php`** — Custom filtering exercise

**📖 In Chapter** (Copy from tutorial as needed):

- `01-detect-yolo.php` — Full YOLO PHP client (Step 3 in chapter)
- `02-draw-boxes.php` — Bounding box drawing (Step 4)
- `03-google-vision-api.php` — Google Vision integration (Step 5)
- `04-aws-rekognition.php` — AWS Rekognition integration (Step 5)
- `05-opencv-faces.php` — OpenCV faces (Step 6)
- `06-batch-processor.php` — Batch processing (Step 8)
- `07-production-api.php` — REST API endpoint (Step 7)
- `08-compare-approaches.php` — Performance comparison (Step 9)
- `BoundingBoxDrawer.php` — Drawing class (Step 4)
- `CloudDetector.php` — Cloud API interface (Step 5)
- `DetectionService.php` — Production service (Step 7)

### Quick Start Without Full Setup

To test immediately with available files:

1. **Verify environment**:

   ```bash
   php verify-setup.php
   ```

2. **`02-draw-boxes.php`** — Draw bounding boxes with labels

   ```bash
   php 02-draw-boxes.php data/sample_images/street.jpg
   ```

3. **`03-google-vision-api.php`** — Google Vision API integration

   ```bash
   # Set credentials first
   export GOOGLE_APPLICATION_CREDENTIALS=/path/to/key.json
   php 03-google-vision-api.php data/sample_images/street.jpg
   ```

4. **`04-aws-rekognition.php`** — AWS Rekognition integration

   ```bash
   # Set credentials first
   export AWS_ACCESS_KEY_ID=your_key
   export AWS_SECRET_ACCESS_KEY=your_secret
   php 04-aws-rekognition.php data/sample_images/street.jpg
   ```

5. **`05-opencv-faces.php`** — OpenCV face detection
   ```bash
   php 05-opencv-faces.php data/sample_images/group_photo.jpg
   ```

### Production Features

6. **`06-batch-processor.php`** — Process multiple images

   ```bash
   php 06-batch-processor.php data/sample_images
   ```

7. **`07-production-api.php`** — REST API endpoint

   ```bash
   # Start server
   php -S localhost:8000 07-production-api.php

   # Test with curl
   curl -X POST http://localhost:8000 \
     -F "image=@data/sample_images/street.jpg" \
     -F "backend=yolo" \
     -F "draw_boxes=true"
   ```

8. **`08-compare-approaches.php`** — Benchmark all approaches
   ```bash
   php 08-compare-approaches.php
   ```

### Advanced Features

9. **`09-confidence-filter.php`** — Filter by confidence threshold

   ```bash
   php 09-confidence-filter.php data/sample_images/street.jpg 0.7
   ```

10. **`10-object-tracker.php`** — Track objects across frames
    ```bash
    php 10-object-tracker.php data/video_frames/
    ```

## Python Scripts

- **`detect_yolo.py`** — YOLOv8 detection (called by PHP)

  ```bash
  python3 detect_yolo.py image.jpg [model_name] [confidence]
  ```

- **`detect_opencv.py`** — OpenCV face detection (called by PHP)
  ```bash
  python3 detect_opencv.py image.jpg [scale_factor] [min_neighbors]
  ```

## Support Classes

- **`BoundingBoxDrawer.php`** — Draw annotated bounding boxes
- **`DetectionService.php`** — Production service with caching
- **`CloudDetector.php`** — Unified cloud API interface

## Directory Structure

```
chapter-18/
├── 01-detect-yolo.php           # YOLO detection client
├── 02-draw-boxes.php            # Draw bounding boxes
├── 03-google-vision-api.php     # Google Vision integration
├── 04-aws-rekognition.php       # AWS Rekognition integration
├── 05-opencv-faces.php          # Face detection
├── 06-batch-processor.php       # Batch processing
├── 07-production-api.php        # REST API endpoint
├── 08-compare-approaches.php    # Performance comparison
├── 09-confidence-filter.php     # Confidence filtering
├── 10-object-tracker.php        # Object tracking
├── detect_yolo.py               # Python YOLO script
├── detect_opencv.py             # Python OpenCV script
├── BoundingBoxDrawer.php        # Drawing class
├── DetectionService.php         # Production service
├── CloudDetector.php            # Cloud API interface
├── verify-setup.php             # Environment verification
├── requirements.txt             # Python dependencies
├── composer.json                # PHP dependencies
├── env.example                  # Environment template
├── data/
│   ├── sample_images/           # Test images
│   └── test_results/            # Annotated outputs
├── cache/                       # Detection cache
├── models/                      # Downloaded YOLO models
└── solutions/                   # Exercise solutions
```

## Configuration

### Cloud API Setup

#### Google Vision API

1. Create project at https://console.cloud.google.com/
2. Enable Vision API
3. Create service account
4. Download JSON key
5. Set environment variable:
   ```bash
   export GOOGLE_APPLICATION_CREDENTIALS=/path/to/key.json
   ```

#### AWS Rekognition

1. Create IAM user at https://console.aws.amazon.com/
2. Attach policy: `AmazonRekognitionFullAccess`
3. Generate access key
4. Set environment variables:
   ```bash
   export AWS_ACCESS_KEY_ID=your_key
   export AWS_SECRET_ACCESS_KEY=your_secret
   export AWS_REGION=us-east-1
   ```

### YOLO Models

YOLOv8 models download automatically on first use:

- **yolov8n.pt** (6 MB) — Nano, fastest
- **yolov8s.pt** (22 MB) — Small, balanced
- **yolov8m.pt** (52 MB) — Medium, more accurate
- **yolov8l.pt** (87 MB) — Large, high accuracy
- **yolov8x.pt** (136 MB) — Extra large, best accuracy

Models are cached in `~/.cache/torch/hub/ultralytics/`

## Exercises

### Exercise 1: Multi-Object Counter

Count objects by category across dataset:

```bash
php solutions/exercise1-multi-object-counter.php data/sample_images
```

### Exercise 2: Video Frame Detection

Extract frames and detect objects:

```bash
# Extract frames from video
ffmpeg -i video.mp4 -vf fps=1 data/video_frames/frame_%04d.jpg

# Detect and track
php solutions/exercise2-video-detection.php data/video_frames
```

### Exercise 3: Custom Filter

Filter detections by class, confidence, size, or region:

```bash
php solutions/exercise3-custom-filter.php data/sample_images/street.jpg
```

### Exercise 4: Detection Dashboard

Web interface for object detection:

```bash
# Start server
php -S localhost:8000 solutions/exercise4-dashboard.php

# Open browser
open http://localhost:8000
```

## Troubleshooting

### Python Issues

**Problem**: `ModuleNotFoundError: No module named 'ultralytics'`

**Solution**:

```bash
# Install to correct Python
python3 -m pip install -r requirements.txt

# Verify
python3 -c "import ultralytics; print('OK')"
```

### Model Download

**Problem**: YOLOv8 model download timeout

**Solution**:

```bash
# Download manually
wget https://github.com/ultralytics/assets/releases/download/v0.0.0/yolov8n.pt

# Place in cache
mkdir -p ~/.cache/torch/hub/ultralytics/
mv yolov8n.pt ~/.cache/torch/hub/ultralytics/
```

### Memory Issues

**Problem**: `Allowed memory size exhausted`

**Solution**:

```php
// Increase memory limit
ini_set('memory_limit', '512M');

// Or resize images before processing
// See verify-setup.php for resize function
```

### GD Extension

**Problem**: `Call to undefined function imagecreate()`

**Solution**:

```bash
# Ubuntu/Debian
sudo apt install php8.6-gd
sudo systemctl restart apache2

# macOS (usually bundled)
# Check php.ini for extension=gd
```

### Cloud API Errors

**Problem**: `Authentication failed`

**Solution**:

```bash
# Google Vision
echo $GOOGLE_APPLICATION_CREDENTIALS
cat $GOOGLE_APPLICATION_CREDENTIALS | python3 -m json.tool

# AWS
aws sts get-caller-identity
```

## Performance Tips

1. **Use appropriate model size**:

   - yolov8n for speed (1-2s per image)
   - yolov8m for balance (2-4s per image)
   - yolov8l for accuracy (4-8s per image)

2. **Resize large images**:

   ```php
   $resized = imagescale($image, 1920, -1, IMG_BICUBIC_FIXED);
   ```

3. **Enable caching**:

   ```php
   $service = new DetectionService(cacheDir: __DIR__ . '/cache');
   ```

4. **Batch processing**:

   - Process multiple images without reloading model
   - Use batch processor for efficiency

5. **Cloud API optimization**:
   - Cache results aggressively
   - Resize images before upload
   - Use appropriate regions

## Cost Estimates (as of 2024)

### Cloud APIs

- **Google Vision**: $1.50 per 1000 images (first 1000/month free)
- **AWS Rekognition**: $1.00 per 1000 images (5000 free first year)

### YOLO (Local)

- **Cost**: $0.00 per image
- **Hardware**: CPU sufficient for yolov8n/s
- **GPU**: Optional, speeds up by 5-10x

### Cost Comparison

| Volume     | YOLO | Google    | AWS       |
| ---------- | ---- | --------- | --------- |
| 1K/month   | $0   | $0 (free) | $0 (free) |
| 10K/month  | $0   | $13.50    | $5.00     |
| 100K/month | $0   | $148.50   | $95.00    |
| 1M/month   | $0   | $1,498.50 | $995.00   |

## Documentation & Development History

### Comprehensive Documentation

- **[COMPREHENSIVE-IMPROVEMENTS.md](COMPREHENSIVE-IMPROVEMENTS.md)** ⭐ — Complete gap analysis, all improvements, and impact summary
- **[FINAL-STATUS.md](FINAL-STATUS.md)** — Current chapter status and quality metrics
- **[IMPROVEMENTS-APPLIED.md](IMPROVEMENTS-APPLIED.md)** — Initial code extraction improvements
- **[REVIEW-AND-IMPROVEMENTS.md](REVIEW-AND-IMPROVEMENTS.md)** — Original review findings

### Recent Enhancements (October 2025)

Chapter 18 received comprehensive improvements addressing gaps not covered in other chapters:

1. **Detection vs. Segmentation** — Clear comparison of object detection, instance segmentation, and semantic segmentation with use case guidance
2. **Custom Training Guide** — Complete decision framework for when to train custom YOLO models
3. **Real-Time Video Streams** — Enhanced Exercise 2 with webcam/IP camera processing patterns
4. **Related CV Tasks** — Added 10+ advanced computer vision topics (pose estimation, OCR, 3D detection, etc.)

See [COMPREHENSIVE-IMPROVEMENTS.md](COMPREHENSIVE-IMPROVEMENTS.md) for complete details.

## Further Reading

- [YOLOv8 Documentation](https://docs.ultralytics.com/)
- [OpenCV Tutorials](https://docs.opencv.org/4.x/d9/df8/tutorial_root.html)
- [Google Vision API Docs](https://cloud.google.com/vision/docs)
- [AWS Rekognition Guide](https://docs.aws.amazon.com/rekognition/)

## Support

For issues or questions:

- Chapter tutorial: `/series/ai-ml-php-developers/chapters/18-object-detection`
- GitHub: [codewithphp/issues](https://github.com/dalehurley/codewithphp/issues)
- Community: [discussions](https://github.com/dalehurley/codewithphp/discussions)
