# Chapter 12: Deep Learning with TensorFlow and PHP

Complete code examples for integrating TensorFlow deep learning models with PHP using TensorFlow Serving.

## Prerequisites

- PHP 8.4+ with GD extension enabled
- Docker installed and running
- Python 3.10+ with TensorFlow installed
- Composer (for dependencies, though minimal in this chapter)

## Quick Setup

### 1. Install TensorFlow (Python)

```bash
pip3 install tensorflow
```

### 2. Download the Pre-trained Model

```bash
python3 download_model.py
```

This downloads MobileNetV2 and saves it to `/tmp/mobilenet/1/`.

### 3. Start TensorFlow Serving

```bash
./start_tensorflow_serving.sh
```

This starts a Docker container serving the model on port 8501.

### 4. Verify TensorFlow Serving

```bash
./verify_serving.sh
```

You should see `Model state: AVAILABLE`.

### 5. Run PHP Examples

```bash
# Simple prediction example
php 01-simple-prediction.php

# TensorFlow client class
php 02-tensorflow-client.php

# Image preprocessor
php 03-image-preprocessor.php

# Complete image classifier
php 04-image-classifier.php

# Batch prediction
php 05-batch-predictor.php

# Web interface (requires browser)
php -S localhost:8000 06-web-upload.php
# Then open http://localhost:8000 in your browser
```

## Automated Setup and Testing

For a faster setup experience, use these helper scripts:

### One-Command Setup

```bash
# Complete setup validation (recommended first run)
./test-setup.sh
```

This script will:

- ✓ Verify PHP, Python, and Docker installation
- ✓ Check required extensions
- ✓ Download model if needed
- ✓ Start TensorFlow Serving
- ✓ Run health checks
- ✓ Test a simple prediction
- ✓ Report any issues found

### Docker Compose (Alternative)

```bash
# Using docker-compose for easier management
docker-compose up -d

# Check status
docker-compose ps

# View logs
docker-compose logs -f

# Stop services
docker-compose down
```

### Diagnostic Tool

If you encounter issues:

```bash
# Generate comprehensive diagnostic report
./diagnose.sh
```

This creates a detailed report including:

- System information
- PHP and Python configuration
- Docker status
- Model files verification
- Network connectivity
- Error logs
- Test prediction results

### Performance Benchmarking

Test and optimize your setup:

```bash
# Run performance benchmarks
php benchmark.php
```

Benchmarks include:

- Cold start vs warm cache
- Single vs batch predictions
- Image size impact
- Throughput testing

### Cleanup

Remove temporary files and caches:

```bash
# Clean up test files and caches
./cleanup.sh
```

Options to:

- Remove temporary prediction files
- Clear cache directories
- Delete test images
- Optionally remove downloaded models

## File Structure

### Setup Scripts

- **download_model.py** — Downloads pre-trained MobileNetV2 from TensorFlow Hub
- **start_tensorflow_serving.sh** — Launches TensorFlow Serving Docker container
- **verify_serving.sh** — Health check for TensorFlow Serving
- **stop_tensorflow_serving.sh** — Stops and removes the container
- **docker-compose.yml** — Docker Compose configuration for multi-container setup

### Utility Scripts

- **test-setup.sh** — Complete setup validation and testing
- **diagnose.sh** — Comprehensive diagnostic tool for troubleshooting
- **cleanup.sh** — Remove temporary files and caches
- **benchmark.php** — Performance benchmarking suite

### Progressive PHP Examples

- **01-simple-prediction.php** — Basic cURL request demonstrating the API
- **02-tensorflow-client.php** — Reusable `TensorFlowClient` class
- **03-image-preprocessor.php** — `ImagePreprocessor` class for image handling
- **04-image-classifier.php** — Complete `ImageClassifier` service
- **05-batch-predictor.php** — Batch processing multiple images
- **06-web-upload.php** — Web interface with file upload and results display

### Data Files

- **data/imagenet_labels.json** — 1,000 ImageNet class labels
- **data/sample_images/** — Test images for classification (6 placeholder images included)

### Exercise Solutions

- **solutions/exercise1-formats.php** — Extended format support and metadata
- **solutions/exercise2-batch.php** — Optimized batch processing with progress tracking
- **solutions/exercise3-resnet.php** — Using ResNet50 model comparison
- **solutions/exercise4-caching.php** — Production caching implementation

### Configuration Files

- **composer.json** — PHP dependencies (minimal: GD, cURL, JSON extensions)
- **.gitignore** — Excludes temporary files, caches, and models from version control
- **.env.example** — Environment variable template

## Troubleshooting

### Docker Issues

**Problem**: `Cannot connect to Docker daemon`

```bash
# Start Docker Desktop (macOS/Windows)
# or start Docker service (Linux):
sudo systemctl start docker
```

**Problem**: Port 8501 already in use

```bash
# Find and stop the conflicting container
docker ps
docker stop <container_id>
```

### TensorFlow Serving Issues

**Problem**: Container exits immediately

```bash
# Check logs for errors
docker logs tensorflow_serving

# Verify model files exist
ls -R /tmp/mobilenet/
```

**Problem**: Model not found

```bash
# Re-download the model
python3 download_model.py

# Restart TensorFlow Serving
./stop_tensorflow_serving.sh
./start_tensorflow_serving.sh
```

### PHP Issues

**Problem**: `Call to undefined function imagecreatefromjpeg()`

```bash
# Install GD extension
# Ubuntu/Debian:
sudo apt-get install php-gd

# macOS (with Homebrew):
brew install php@8.4

# Verify installation:
php -m | grep gd
```

**Problem**: Upload errors in web interface

```bash
# Check permissions
chmod 777 /tmp

# Or change upload directory in 06-web-upload.php
```

## Testing Classification with Sample Images

### Using the Web Interface

1. Start the server: `php -S localhost:8000 06-web-upload.php`
2. Open http://localhost:8000 in your browser
3. Upload an image (photo of dog, cat, car, food, etc.)
4. View the top 5 predictions with confidence scores

### Using Command Line

```bash
# Classify a single image
php 04-image-classifier.php path/to/image.jpg

# Classify multiple images
php 05-batch-predictor.php data/sample_images/*.jpg
```

## Performance Optimization

### Caching

Implement caching to avoid redundant predictions:

```php
$cacheKey = md5_file($imagePath);
// Store predictions in Redis or filesystem
```

### Batch Processing

Process multiple images in one request:

```php
// 3-5x faster than individual requests
$results = $classifier->classifyBatch($imagePaths);
```

### Model Selection

- **MobileNetV2**: Fast, lightweight (recommended for this chapter)
- **ResNet50**: More accurate, slower (Exercise 3)
- **EfficientNet**: Best accuracy-to-speed ratio (advanced)

## Production Deployment

### Scaling TensorFlow Serving

```bash
# Run multiple instances behind a load balancer
docker run -d -p 8501:8501 --name tf_serving_1 ...
docker run -d -p 8502:8501 --name tf_serving_2 ...
```

### Using Docker Compose

Use the included `docker-compose.yml` for easier multi-container management:

```bash
# Start all services
docker-compose up -d

# Scale TensorFlow Serving (requires additional configuration)
# docker-compose up -d --scale tensorflow-serving=3

# Monitor logs
docker-compose logs -f tensorflow-serving
```

See `docker-compose.yml` for configuration options including health checks, resource limits, and multi-model serving.

### Monitoring

```bash
# Check model metrics
curl http://localhost:8501/v1/models/mobilenet/metadata

# View container stats
docker stats tensorflow_serving
```

## Additional Resources

- [TensorFlow Serving Documentation](https://www.tensorflow.org/tfx/guide/serving)
- [MobileNetV2 Paper](https://arxiv.org/abs/1801.04381)
- [ImageNet Dataset](https://www.image-net.org/)
- [PHP GD Documentation](https://www.php.net/manual/en/book.image.php)

## License

Code examples are provided for educational purposes. MobileNetV2 weights are licensed under Apache 2.0 by Google.
