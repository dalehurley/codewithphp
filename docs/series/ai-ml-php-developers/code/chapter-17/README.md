# Chapter 17: Image Classification with Pre-trained Models

Complete code examples demonstrating both cloud-based (Google Cloud Vision API) and local (ONNX MobileNetV2) image classification approaches.

## Quick Start

```bash
# 1. Clone or navigate to this directory
cd chapter-17

# 2. Setup environment variables
cp env.example .env
# Edit .env and add your Google Cloud Vision API key

# 3. Download local model (optional)
chmod +x download_model.sh
./download_model.sh

# 4. Install Python dependencies (for local model)
pip3 install onnxruntime pillow numpy

# 5. Add sample images
# Place test images in data/sample_images/

# 6. Test cloud API
php 01-cloud-vision-setup.php

# 7. Test local model
php 04-onnx-setup-test.php
```

## Prerequisites

### Required

- PHP 8.4+
- Extensions: `curl`, `json`, `gd` or `imagick`
- Composer (for dependencies)

### For Cloud Classification

- Google Cloud account with Vision API enabled
- Vision API key (get from [Google Cloud Console](https://console.cloud.google.com/apis/credentials))

### For Local Classification

- Python 3.10+
- Python packages: `onnxruntime`, `pillow`, `numpy`
- ONNX model file (~14MB, auto-downloaded)

## File Overview

### Setup & Configuration

| File                | Purpose                                  |
| ------------------- | ---------------------------------------- |
| `composer.json`     | PHP dependencies                         |
| `env.example`       | Environment variables template           |
| `download_model.sh` | Downloads ONNX model and ImageNet labels |
| `onnx_inference.py` | Python script for local ONNX inference   |

### Cloud Vision API Examples

| File                         | Description                            |
| ---------------------------- | -------------------------------------- |
| `01-cloud-vision-setup.php`  | Test API connection and authentication |
| `02-cloud-vision-client.php` | Reusable CloudVisionClient class       |
| `03-classify-with-cloud.php` | Batch classification with cloud API    |

### Local ONNX Model Examples

| File                     | Description                              |
| ------------------------ | ---------------------------------------- |
| `04-onnx-setup-test.php` | Verify ONNX Runtime setup                |
| `05-onnx-classifier.php` | ONNXClassifier class for local inference |

### Production Integration

| File                              | Description                           |
| --------------------------------- | ------------------------------------- |
| `06-model-comparison.php`         | Compare cloud vs local performance    |
| `07-unified-service.php`          | Strategy pattern with fallback        |
| `08-batch-classifier.php`         | Batch processing multiple images      |
| `09-caching-layer.php`            | Result caching to reduce costs        |
| `10-php-image-preprocessor.php`   | PHP-native image preprocessing        |
| `11-web-upload-with-security.php` | Secure web interface with file upload |

### Exercise Solutions

| File                                      | Description                    |
| ----------------------------------------- | ------------------------------ |
| `solutions/exercise1-aws-rekognition.php` | AWS Rekognition integration    |
| `solutions/exercise2-top-k.php`           | Confidence threshold filtering |
| `solutions/exercise3-custom-labels.php`   | Label mapping system           |
| `solutions/exercise4-hybrid.php`          | Hybrid cloud/local strategy    |

## Setup Instructions

### 1. Google Cloud Vision API

1. Create project at [console.cloud.google.com](https://console.cloud.google.com)
2. Enable Vision API:
   ```bash
   # Visit: https://console.cloud.google.com/apis/library/vision.googleapis.com
   # Click "Enable"
   ```
3. Create API key:
   - Go to Credentials → Create Credentials → API Key
   - Restrict to Vision API only (recommended)
4. Add to `.env` file:
   ```
   GOOGLE_CLOUD_VISION_API_KEY=AIzaSyD...
   ```

### 2. Local ONNX Model

```bash
# Download model and labels
./download_model.sh

# Install Python dependencies
pip3 install onnxruntime pillow numpy

# Verify installation
php 04-onnx-setup-test.php
```

### 3. Sample Images

Add test images to `data/sample_images/`:

```bash
mkdir -p data/sample_images
# Add your own images or download sample images:
# - Cat image
# - Dog image
# - Car image
# - Bicycle image
# - Coffee/food image
```

You can use royalty-free images from:

- [Unsplash](https://unsplash.com) - Free high-quality images
- [Pexels](https://www.pexels.com) - Free stock photos
- [Pixabay](https://pixabay.com) - Free images and videos

## Usage Examples

### Cloud Classification

```php
<?php
require_once 'CloudVisionClient.php';

$client = new CloudVisionClient(apiKey: 'YOUR_API_KEY');
$results = $client->classifyImage('image.jpg');

foreach ($results as $result) {
    echo "{$result['label']}: {$result['confidence']}\n";
}
```

### Local Classification

```php
<?php
require_once 'ONNXClassifier.php';

$classifier = new ONNXClassifier(
    modelPath: 'models/mobilenetv2-7.onnx',
    labelsPath: 'data/imagenet_labels.json',
    pythonScript: 'onnx_inference.py'
);

$results = $classifier->classifyImage('image.jpg');
```

### Unified Service with Fallback

```php
<?php
require_once '07-unified-service.php';

// Create service with local primary, cloud fallback
$service = new ImageClassificationService(
    primaryClassifier: $localClassifier,
    fallbackClassifier: $cloudClassifier
);

$result = $service->classify('image.jpg');
// Automatically uses fallback if primary fails
```

## Performance Comparison

Based on typical results:

| Metric           | Cloud API           | Local ONNX  |
| ---------------- | ------------------- | ----------- |
| Latency          | 200-500ms           | 40-100ms    |
| Setup Time       | 5 minutes           | 15 minutes  |
| Cost (1000 imgs) | $1.50               | $0.00       |
| Categories       | 10,000+             | 1,000       |
| Offline Support  | No                  | Yes         |
| Privacy          | Data sent to Google | Fully local |

**Recommendation:**

- **Use Cloud** for: Quick prototyping, variable traffic, <30K images/month
- **Use Local** for: High volume, privacy needs, low latency requirements
- **Use Hybrid** for: Best of both worlds with fallback strategy

## Troubleshooting

### Cloud API Issues

**"API key not valid"**

```bash
# Check your API key in .env file
# Ensure it starts with "AIza" and is ~39 characters
# Verify key restrictions allow Vision API
```

**"Quota exceeded"**

```bash
# You've hit the free tier (1000 images/month)
# Either enable billing or wait for next month
# Consider switching to local model
```

**Slow response times**

```bash
# Network latency varies (expect 200-500ms)
# Check internet connection
# Consider using local model for better latency
```

### Local Model Issues

**"ModuleNotFoundError: No module named 'onnxruntime'"**

```bash
# Option 1: Install individually
pip3 install onnxruntime pillow numpy

# Option 2: Use requirements file
pip3 install -r requirements.txt
```

**"Neither GD nor Imagick extension is available"**

```bash
# Check installed extensions
php -m | grep -E "gd|imagick"

# GD is usually included with PHP
# Install Imagick if needed
pecl install imagick
```

### Web Interface Issues

**"Upload failed" or "File too large"**

```bash
# Check PHP configuration
php -i | grep -E "upload_max_filesize|post_max_size"

# Adjust in php.ini if needed
upload_max_filesize = 10M
post_max_size = 12M
```

**CSRF token errors**

```bash
# Ensure sessions are enabled
php -i | grep session.save_path

# Make sure session directory is writable
# Clear browser cookies and try again
```

**"Model file not found"**

```bash
chmod +x download_model.sh
./download_model.sh
```

**Slow inference (>500ms)**

```bash
# First run loads model (expect 500-1500ms)
# Subsequent runs should be 40-100ms
# Check CPU supports AVX2: lscpu | grep avx2 (Linux)
```

**Different predictions than cloud**

- Local model (MobileNetV2): 1,000 ImageNet classes
- Cloud API: 10,000+ categories including brands, logos
- Both accurate for common objects (80-100% agreement)

## Cost Analysis

### Google Cloud Vision Pricing (2024)

- First 1,000 images/month: **Free**
- 1,001-5,000,000: **$1.50 per 1,000 images**

**Monthly cost examples:**

- 10,000 images: $13.50
- 50,000 images: $73.50
- 100,000 images: $148.50

### Local Model Costs

- Setup: Free (open-source software)
- Per image: $0.00
- Infrastructure: Your server costs only
- **Break-even**: ~30,000 images/month

## Project Structure

```
chapter-17/
├── README.md                        # This file
├── composer.json                    # PHP dependencies
├── env.example                      # Environment template
├── download_model.sh                # Model setup script
├── onnx_inference.py                # Python inference bridge
├── requirements.txt                 # Python dependencies
│
├── 01-cloud-vision-setup.php        # Cloud API test
├── 02-cloud-vision-client.php       # Cloud client class
├── 03-classify-with-cloud.php       # Cloud batch example
├── 04-onnx-setup-test.php           # ONNX setup verification
├── 05-onnx-classifier.php           # ONNX classifier class
├── 06-model-comparison.php          # Cloud vs local comparison
├── 07-unified-service.php           # Strategy pattern integration
├── 08-batch-classifier.php          # Batch processing
├── 09-caching-layer.php             # Result caching
├── 10-php-image-preprocessor.php    # PHP image preprocessing
├── 11-web-upload-with-security.php  # Secure web interface
│
├── data/
│   ├── imagenet_labels.json         # 1,000 class labels
│   └── sample_images/               # Your test images
│
├── models/
│   └── mobilenetv2-7.onnx           # ONNX model (~14MB)
│
└── solutions/                       # Exercise solutions
    ├── exercise1-aws-rekognition.php
    ├── exercise2-top-k.php
    ├── exercise3-custom-labels.php
    └── exercise4-hybrid.php
```

## Next Steps

1. **Read Chapter 17** in the AI/ML for PHP Developers series
2. **Try both approaches** to understand trade-offs
3. **Complete exercises** to deepen understanding
4. **Build your application** using the patterns learned
5. **Explore Chapter 18** for object detection (next topic)

## Resources

- [Chapter 17 Tutorial](../../chapters/17-image-classification-project-with-pre-trained-models.md)
- [Google Cloud Vision API Docs](https://cloud.google.com/vision/docs)
- [ONNX Model Zoo](https://github.com/onnx/models)
- [MobileNetV2 Paper](https://arxiv.org/abs/1801.04381)
- [ImageNet Dataset](https://www.image-net.org/)

## License

Code examples are provided for educational purposes as part of the "Code with PHP" tutorial series.
