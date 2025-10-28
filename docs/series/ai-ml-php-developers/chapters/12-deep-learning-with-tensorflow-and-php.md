---
title: "12: Deep Learning with TensorFlow and PHP"
description: "Learn to integrate TensorFlow deep learning models into PHP applications using TensorFlow Serving and REST APIs: deploy pre-trained neural networks, build an image classification service, and leverage state-of-the-art AI in production"
series: "ai-ml-php-developers"
chapter: 12
order: 12
difficulty: "Intermediate"
prerequisites:
  - "/series/ai-ml-php-developers/chapters/10-neural-networks-and-deep-learning-fundamentals"
  - "/series/ai-ml-php-developers/chapters/11-integrating-php-with-python-for-advanced-ml"
---

![Deep Learning with TensorFlow and PHP](/images/ai-ml-php-developers/chapter-12-tensorflow-php-hero-full.webp)

# Chapter 12: Deep Learning with TensorFlow and PHP

## Overview

In Chapter 10, you learned the fundamentals of neural networks—how layers of neurons learn representations through backpropagation. In Chapter 11, you discovered strategies for integrating PHP with Python to access advanced ML capabilities. Now it's time to bring those concepts together and deploy real deep learning models in your PHP applications using TensorFlow, the industry's leading deep learning framework.

TensorFlow powers AI at Google, Uber, Airbnb, and thousands of production systems worldwide. It's the go-to framework for training sophisticated neural networks on everything from image recognition to natural language understanding. But here's the challenge: TensorFlow is primarily a Python framework. How can PHP developers leverage these powerful models without rewriting their entire application stack?

This chapter shows you the practical, production-ready answer: **TensorFlow Serving**. You'll deploy a pre-trained deep learning model as a microservice and communicate with it via REST API from PHP. This approach gives you the best of both worlds—the power of state-of-the-art neural networks and the simplicity of your existing PHP codebase. You'll build a complete image classification system that can identify objects in photos with near-human accuracy, process images through a convolutional neural network, and return predictions with confidence scores.

By the end of this chapter, you'll have hands-on experience deploying TensorFlow models, integrating deep learning into PHP web applications, and understanding the architecture patterns that make AI-powered services scalable and maintainable. You'll be ready to add sophisticated AI capabilities to any PHP project.

## Prerequisites

Before starting this chapter, you should have:

- Completed [Chapter 10](/series/ai-ml-php-developers/chapters/10-neural-networks-and-deep-learning-fundamentals) or understand neural network basics (layers, activation functions, forward propagation)
- Completed [Chapter 11](/series/ai-ml-php-developers/chapters/11-integrating-php-with-python-for-advanced-ml) or have experience calling external services from PHP
- PHP 8.4+ installed with GD extension enabled (for image processing)
- Docker installed and running (for TensorFlow Serving container)
- Python 3.10+ with TensorFlow installed (for model setup)
- Basic understanding of REST APIs and JSON
- Familiarity with cURL or HTTP clients in PHP
- Composer for dependency management

**Estimated Time**: ~60-75 minutes (including Docker setup and testing)

**Verify your setup:**

```bash
# Check PHP and GD extension
php -v
php -m | grep gd

# Verify Docker is running
docker --version
docker ps

# Check Python and TensorFlow
python3 --version
python3 -c "import tensorflow as tf; print(f'TensorFlow {tf.__version__}')"
```

::: warning Docker Required
This chapter requires Docker to run TensorFlow Serving. If you don't have Docker installed, visit [docker.com](https://www.docker.com/) to download Docker Desktop for your platform. The installation takes about 5-10 minutes.
:::

## What You'll Build

By the end of this chapter, you will have created:

- A **TensorFlow Serving deployment** running MobileNetV2 in a Docker container
- A **TensorFlowClient class** that communicates with the serving API using cURL
- An **ImagePreprocessor class** that loads, resizes, and normalizes images for neural networks
- An **ImageClassifier service** combining preprocessing and prediction with label decoding
- A **batch prediction system** that classifies multiple images efficiently
- A **web interface** for uploading photos and displaying classification results
- A **production-ready integration** with caching, error handling, and logging
- Working examples classifying real images into 1,000 ImageNet categories

All code follows PHP 8.4 standards with type safety, constructor property promotion, and comprehensive error handling.

::: info Code Examples
Complete, runnable examples for this chapter are available in:

**Setup Scripts:**

- [`download_model.py`](../code/chapter-12/download_model.py) — Download pre-trained MobileNetV2
- [`start_tensorflow_serving.sh`](../code/chapter-12/start_tensorflow_serving.sh) — Launch TensorFlow Serving container
- [`verify_serving.sh`](../code/chapter-12/verify_serving.sh) — Verify TensorFlow Serving is running

**Progressive PHP Examples:**

- [`01-simple-prediction.php`](../code/chapter-12/01-simple-prediction.php) — Basic cURL request to TensorFlow Serving
- [`02-tensorflow-client.php`](../code/chapter-12/02-tensorflow-client.php) — Reusable TensorFlowClient class
- [`03-image-preprocessor.php`](../code/chapter-12/03-image-preprocessor.php) — Image loading and preprocessing
- [`04-image-classifier.php`](../code/chapter-12/04-image-classifier.php) — Complete classification system
- [`05-batch-predictor.php`](../code/chapter-12/05-batch-predictor.php) — Batch processing multiple images
- [`06-web-upload.php`](../code/chapter-12/06-web-upload.php) — Web interface with file upload

**Data Files:**

- [`data/imagenet_labels.json`](../code/chapter-12/data/imagenet_labels.json) — 1,000 ImageNet class labels
- [`data/sample_images/`](../code/chapter-12/data/sample_images/) — Test images for classification

**Exercise Solutions:**

- [`solutions/exercise1-formats.php`](../code/chapter-12/solutions/exercise1-formats.php) — Multiple image format support
- [`solutions/exercise2-batch.php`](../code/chapter-12/solutions/exercise2-batch.php) — Optimized batch processing
- [`solutions/exercise3-resnet.php`](../code/chapter-12/solutions/exercise3-resnet.php) — Using ResNet50 model
- [`solutions/exercise4-caching.php`](../code/chapter-12/solutions/exercise4-caching.php) — Production caching system

See [`README.md`](../code/chapter-12/README.md) for detailed setup instructions.
:::

## Quick Start

Want to see deep learning in action immediately? Here's a 5-minute example that classifies an image using TensorFlow from PHP:

::: tip
This quick start assumes you have TensorFlow Serving already running. If not, follow Step 2 below to set it up—it takes just 10 minutes.
:::

```php
# filename: quick-start-classification.php
<?php

declare(strict_types=1);

// Simple function to classify an image via TensorFlow Serving
function classifyImage(string $imagePath, string $servingUrl): array
{
    // Load and preprocess image
    $image = imagecreatefromjpeg($imagePath);
    $resized = imagescale($image, 224, 224);

    // Convert to array of normalized pixel values
    $pixels = [];
    for ($y = 0; $y < 224; $y++) {
        for ($x = 0; $x < 224; $x++) {
            $rgb = imagecolorat($resized, $x, $y);
            $pixels[] = [
                (($rgb >> 16) & 0xFF) / 255.0,  // Red
                (($rgb >> 8) & 0xFF) / 255.0,   // Green
                ($rgb & 0xFF) / 255.0            // Blue
            ];
        }
    }

    // Prepare TensorFlow Serving request
    $payload = [
        'instances' => [['input' => $pixels]]
    ];

    // Send to TensorFlow Serving
    $ch = curl_init($servingUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        throw new RuntimeException("TensorFlow Serving request failed: HTTP $httpCode");
    }

    $result = json_decode($response, true);
    return $result['predictions'][0] ?? [];
}

// Classify a sample image
$servingUrl = 'http://localhost:8501/v1/models/mobilenet:predict';
$imagePath = 'sample_dog.jpg';

echo "Classifying image: $imagePath\n";
$predictions = classifyImage($imagePath, $servingUrl);

// Get top prediction
$topIndex = array_keys($predictions, max($predictions))[0];
$confidence = max($predictions) * 100;

echo "Predicted class index: $topIndex\n";
echo "Confidence: " . round($confidence, 2) . "%\n";
```

**Run it:**

```bash
# 1. Start TensorFlow Serving (first time setup - see Step 2)
docker run -p 8501:8501 \
  --mount type=bind,source=/tmp/mobilenet,target=/models/mobilenet \
  -e MODEL_NAME=mobilenet \
  tensorflow/serving

# 2. In another terminal, run the PHP script
php quick-start-classification.php
```

**Expected output:**

```
Classifying image: sample_dog.jpg
Predicted class index: 207
Confidence: 94.23%
```

**What just happened?** Your PHP script loaded an image, preprocessed it into the format TensorFlow expects, sent it to a deep learning model running in TensorFlow Serving, and received predictions for 1,000 possible object categories. The model identified the image with 94% confidence. That's a production-grade convolutional neural network running from PHP!

Now let's build a complete, robust system step by step...

## Objectives

By the end of this chapter, you will be able to:

- **Understand TensorFlow architecture** and the different options for PHP integration
- **Deploy TensorFlow Serving** with Docker to serve deep learning models via REST API
- **Create a PHP client** that communicates with TensorFlow Serving using cURL and JSON
- **Preprocess images** in PHP to meet deep learning model input requirements
- **Implement image classification** by combining preprocessing, prediction, and label decoding
- **Handle batch predictions** to efficiently process multiple images
- **Build a web interface** for uploading and classifying images in real-time
- **Apply production patterns** including caching, error handling, and performance optimization

## Step 1: Understanding TensorFlow Integration Options (~5 min)

### Goal

Understand the TensorFlow ecosystem and evaluate different strategies for integrating TensorFlow deep learning models with PHP applications.

### What is TensorFlow?

TensorFlow is an open-source deep learning framework developed by Google Brain. It's used to build and train neural networks for tasks like image recognition, natural language processing, recommendation systems, and more. TensorFlow excels at:

- Training complex neural networks with millions of parameters
- Running on GPUs for fast computation
- Deploying models to production at massive scale
- Providing pre-trained models for common tasks

### Integration Options for PHP

As a PHP developer, you have three main options for using TensorFlow models:

**Option 1: PHP-TensorFlow Extension**

- **Description**: Native PHP extension providing TensorFlow bindings
- **Pros**: Direct access, no network overhead
- **Cons**: Complex installation, limited documentation, platform-specific compilation
- **Status**: Experimental, not recommended for production

**Option 2: TensorFlow Serving + REST API** ⭐ (Recommended)

- **Description**: Deploy models as a microservice, call via HTTP from PHP
- **Pros**: Language-agnostic, production-tested, scalable, easy deployment
- **Cons**: Network latency (minimal), requires Docker
- **Status**: Industry standard, used by Google, Uber, Spotify

**Option 3: ONNX Runtime + PHP**

- **Description**: Convert TensorFlow models to ONNX format, run with ONNX Runtime
- **Pros**: Cross-framework compatibility, good performance
- **Cons**: Requires model conversion, fewer PHP integrations
- **Status**: Emerging option, gaining popularity

**This chapter focuses on Option 2** (TensorFlow Serving + REST API) because it's the most practical, battle-tested approach for production PHP applications.

### Architecture Overview

Here's how the system works:

```mermaid
flowchart LR
    A[PHP Application] -->|HTTP POST| B[TensorFlow Serving<br/>REST API]
    B -->|Load Model| C[MobileNetV2<br/>SavedModel]
    B -->|Return JSON| A
    C -->|Predictions| B

    style A fill:#e1f5ff
    style B fill:#fff3cd
    style C fill:#d4edda
```

**Flow:**

1. PHP application sends image data as JSON via HTTP POST
2. TensorFlow Serving receives request, loads the model
3. Model processes input through neural network layers
4. TensorFlow Serving returns predictions as JSON
5. PHP decodes JSON and presents results to user

### Why It Works

**Separation of concerns**: PHP handles web logic, TensorFlow handles AI. Each does what it does best.

**Scalability**: TensorFlow Serving can run on separate hardware (even GPUs) and scale independently from your web servers.

**Flexibility**: Replace or update models without touching PHP code. Deploy different model versions simultaneously.

**Performance**: TensorFlow Serving is optimized for inference with batching, caching, and GPU acceleration.

::: tip Real-World Usage
This is not a toy architecture—it's how major companies deploy AI. Uber uses TensorFlow Serving for real-time ride matching. Twitter uses it for recommendation ranking. Airbnb uses it for search relevance. The pattern scales from single machines to global infrastructure.
:::

## Step 2: Setting Up TensorFlow Serving (~15 min)

### Goal

Install Docker, download a pre-trained MobileNetV2 model, and launch TensorFlow Serving to serve the model via REST API.

### Actions

**1. Verify Docker is installed:**

```bash
docker --version
```

If Docker is not installed, download Docker Desktop from [docker.com](https://www.docker.com/products/docker-desktop) and follow the installation instructions for your platform.

**2. Download a pre-trained model:**

TensorFlow provides pre-trained models that you can use immediately. MobileNetV2 is perfect for our purposes—it's fast, accurate, and trained on ImageNet (1,000 object categories).

Create a Python script to download the model:

```python
# filename: download_model.py
import tensorflow as tf
import os

# Download pre-trained MobileNetV2
model = tf.keras.applications.MobileNetV2(
    weights='imagenet',
    include_top=True
)

# Save in TensorFlow SavedModel format
export_path = '/tmp/mobilenet/1'
os.makedirs(export_path, exist_ok=True)

# Wrap model to specify input signature
@tf.function(input_signature=[tf.TensorSpec(shape=[None, 224, 224, 3], dtype=tf.float32, name='input')])
def model_fn(input):
    return model(input)

# Export
tf.saved_model.save(
    model,
    export_path,
    signatures={'serving_default': model_fn}
)

print(f"✓ Model saved to {export_path}")
print("✓ Ready for TensorFlow Serving")
```

**Run the download script:**

```bash
python3 download_model.py
```

This downloads the model weights (~14 MB) and saves the model in SavedModel format at `/tmp/mobilenet/1`. The `1` represents the model version (TensorFlow Serving supports multiple versions).

**3. Start TensorFlow Serving:**

Create a shell script to launch the Docker container:

```bash
# filename: start_tensorflow_serving.sh
#!/bin/bash

# Pull TensorFlow Serving image (first time only)
docker pull tensorflow/serving

# Start TensorFlow Serving
docker run -d \
  --name tensorflow_serving \
  -p 8501:8501 \
  --mount type=bind,source=/tmp/mobilenet,target=/models/mobilenet \
  -e MODEL_NAME=mobilenet \
  tensorflow/serving

echo "✓ TensorFlow Serving started on http://localhost:8501"
echo "✓ Model endpoint: http://localhost:8501/v1/models/mobilenet:predict"
echo ""
echo "To stop: docker stop tensorflow_serving"
echo "To remove: docker rm tensorflow_serving"
```

Make it executable and run:

```bash
chmod +x start_tensorflow_serving.sh
./start_tensorflow_serving.sh
```

**4. Verify TensorFlow Serving is running:**

Check the container status:

```bash
docker ps | grep tensorflow_serving
```

Test the health endpoint:

```bash
curl http://localhost:8501/v1/models/mobilenet
```

### Expected Result

You should see JSON output describing the model:

```json
{
  "model_version_status": [
    {
      "version": "1",
      "state": "AVAILABLE",
      "status": {
        "error_code": "OK",
        "error_message": ""
      }
    }
  ]
}
```

The `"state": "AVAILABLE"` confirms the model is loaded and ready to serve predictions.

### Why It Works

**Docker containerization**: TensorFlow Serving runs in an isolated environment with all dependencies. No Python package conflicts with your system.

**Volume mounting**: We bind-mounted `/tmp/mobilenet` from your host to `/models/mobilenet` in the container. TensorFlow Serving automatically loads any SavedModel in that directory.

**REST API**: Port 8501 exposes the REST API. You can also use gRPC on port 8500 for even faster communication (REST is simpler for PHP).

**Model versioning**: TensorFlow Serving can serve multiple model versions simultaneously. Clients can request specific versions or always get the latest.

### Troubleshooting

**Error: "Cannot connect to Docker daemon"**

- **Cause**: Docker is not running
- **Solution**: Start Docker Desktop and wait for it to fully initialize

**Error: "Address already in use"**

- **Cause**: Port 8501 is already in use
- **Solution**: Stop any existing TensorFlow Serving containers: `docker stop $(docker ps -q --filter ancestor=tensorflow/serving)`

**Error: "Model not found"**

- **Cause**: Model path doesn't exist or is incorrect
- **Solution**: Verify `/tmp/mobilenet/1` exists and contains `saved_model.pb`

**Docker container starts but crashes:**

- **Cause**: Corrupted model download
- **Solution**: Delete `/tmp/mobilenet` and re-run `download_model.py`

## Step 3: Understanding Model Input/Output (~5 min)

### Goal

Learn what data format TensorFlow Serving expects for inputs and what it returns as outputs so we can properly format requests in PHP.

### Model Signature

TensorFlow models have a signature defining their inputs and outputs. For MobileNetV2:

**Input:**

- **Name**: `input` (or `input_1` depending on version)
- **Shape**: `[batch_size, 224, 224, 3]`
  - `batch_size`: Number of images (1 for single image, N for batch)
  - `224, 224`: Image dimensions (height, width in pixels)
  - `3`: Color channels (RGB)
- **Data type**: `float32`
- **Value range**: 0.0 to 1.0 (normalized pixel values)

**Output:**

- **Shape**: `[batch_size, 1000]`
- **Data type**: `float32`
- **Meaning**: Probability scores for 1,000 ImageNet classes
- **Value range**: 0.0 to 1.0 (softmax probabilities, sum to 1.0)

### Request Format

TensorFlow Serving expects JSON in this format:

```json
{
  "instances": [
    {
      "input": [
        [[0.123, 0.456, 0.789], [0.234, 0.567, 0.890], ...],
        [[0.345, 0.678, 0.901], [0.456, 0.789, 0.012], ...],
        ...
      ]
    }
  ]
}
```

The `instances` array allows batch predictions. Each instance contains the `input` key with a 224×224×3 array of normalized pixel values.

### Response Format

TensorFlow Serving returns:

```json
{
  "predictions": [
    [0.001, 0.002, 0.003, ..., 0.823, ..., 0.005]
  ]
}
```

The `predictions` array contains one array per instance. Each inner array has 1,000 values (one per class). The highest value is the predicted class.

### ImageNet Classes

MobileNetV2 is trained on ImageNet, which has 1,000 object categories. The classes are indexed 0-999:

- Index 0: "tench" (a type of fish)
- Index 207: "golden retriever"
- Index 281: "tabby cat"
- Index 817: "sports car"
- Index 949: "strawberry"

We'll use a JSON file mapping indices to human-readable labels.

### Image Preprocessing Requirements

To prepare an image for the model, we must:

1. **Resize** to exactly 224×224 pixels
2. **Normalize** pixel values from 0-255 range to 0.0-1.0 range
3. **Arrange** as height × width × channels (RGB order)
4. **Encode** as nested arrays for JSON

::: warning Common Mistake
Many deep learning models require **different** preprocessing. Some expect -1 to +1 range. Others expect specific mean subtraction. Always check the model documentation. For MobileNetV2, simple 0-1 normalization works.
:::

## Step 4: Building the PHP Client (~10 min)

### Goal

Create a reusable `TensorFlowClient` class that handles HTTP communication with TensorFlow Serving.

### Actions

**Create the TensorFlowClient class:**

```php
# filename: 02-tensorflow-client.php
<?php

declare(strict_types=1);

/**
 * TensorFlow Serving REST API client.
 *
 * Handles communication with TensorFlow Serving via HTTP,
 * including request formatting, error handling, and timeouts.
 */
final class TensorFlowClient
{
    public function __construct(
        private string $baseUrl = 'http://localhost:8501',
        private int $timeoutSeconds = 30,
    ) {}

    /**
     * Send a prediction request to TensorFlow Serving.
     *
     * @param string $modelName Model name (e.g., 'mobilenet')
     * @param array<mixed> $instances Array of input instances
     * @return array<mixed> Predictions array
     * @throws RuntimeException If request fails
     */
    public function predict(string $modelName, array $instances): array
    {
        $url = "{$this->baseUrl}/v1/models/{$modelName}:predict";

        $payload = ['instances' => $instances];
        $json = json_encode($payload, JSON_THROW_ON_ERROR);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json),
            ],
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Handle cURL errors
        if ($response === false) {
            throw new RuntimeException(
                "TensorFlow Serving request failed: $curlError"
            );
        }

        // Handle HTTP errors
        if ($httpCode !== 200) {
            throw new RuntimeException(
                "TensorFlow Serving returned HTTP $httpCode: $response"
            );
        }

        $result = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        if (!isset($result['predictions'])) {
            throw new RuntimeException(
                'Invalid TensorFlow Serving response: missing predictions'
            );
        }

        return $result['predictions'];
    }

    /**
     * Get model metadata (version, status).
     *
     * @param string $modelName Model name
     * @return array<mixed> Model metadata
     */
    public function getModelMetadata(string $modelName): array
    {
        $url = "{$this->baseUrl}/v1/models/{$modelName}";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);

        $response = curl_exec($ch);
        $httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || $response === false) {
            throw new RuntimeException(
                "Failed to get model metadata (HTTP $httpCode)"
            );
        }

        return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
    }
}

// Example usage
if (PHP_SAPI === 'cli') {
    echo "TensorFlow Serving Client Test\n";
    echo "================================\n\n";

    try {
        $client = new TensorFlowClient();

        // Check if model is available
        echo "Checking model status...\n";
        $metadata = $client->getModelMetadata('mobilenet');
        $version = $metadata['model_version_status'][0]['version'] ?? 'unknown';
        $state = $metadata['model_version_status'][0]['state'] ?? 'unknown';

        echo "Model version: $version\n";
        echo "Model state: $state\n\n";

        if ($state !== 'AVAILABLE') {
            echo "⚠ Model is not ready. Start TensorFlow Serving first.\n";
            exit(1);
        }

        // Test prediction with dummy data
        echo "Testing prediction with dummy data...\n";
        $dummyImage = array_fill(0, 224 * 224, [0.5, 0.5, 0.5]); // Gray image
        $predictions = $client->predict('mobilenet', [['input' => $dummyImage]]);

        echo "✓ Prediction successful!\n";
        echo "Response contains " . count($predictions[0]) . " class probabilities\n";
        echo "Top probability: " . max($predictions[0]) . "\n";

    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
```

**Test the client:**

```bash
php 02-tensorflow-client.php
```

### Expected Result

```
TensorFlow Serving Client Test
================================

Checking model status...
Model version: 1
Model state: AVAILABLE

Testing prediction with dummy data...
✓ Prediction successful!
Response contains 1000 class probabilities
Top probability: 0.0234567
```

### Why It Works

**Constructor properties**: We use PHP 8.4 constructor property promotion for clean, concise class definition.

**Type safety**: All parameters and return types are declared, catching errors at development time.

**Error handling**: The class throws meaningful exceptions for network errors, HTTP errors, and invalid responses.

**Configurability**: Base URL and timeout are configurable, allowing different deployments (staging, production) or longer inference times.

**Reusability**: The `predict()` method works with any TensorFlow model, not just MobileNetV2.

### Troubleshooting

**Error: "Connection refused"**

- **Cause**: TensorFlow Serving is not running
- **Solution**: Run `./start_tensorflow_serving.sh` first

**Error: "Operation timed out"**

- **Cause**: First request is slow (cold start) or network issue
- **Solution**: Increase timeout or wait for TensorFlow Serving to fully initialize

**Error: "Model not found"**

- **Cause**: Wrong model name or model not loaded
- **Solution**: Check `getModelMetadata()` output and verify Docker container logs with `docker logs tensorflow_serving`

## Step 5: Image Preprocessing in PHP (~8 min)

### Goal

Create an `ImagePreprocessor` class that loads images, resizes them to 224×224, normalizes pixel values, and converts to the format TensorFlow expects.

### Actions

**Create the ImagePreprocessor class:**

```php
# filename: 03-image-preprocessor.php
<?php

declare(strict_types=1);

/**
 * Preprocesses images for deep learning models.
 *
 * Handles loading, resizing, normalization, and conversion
 * to the array format expected by TensorFlow models.
 */
final class ImagePreprocessor
{
    public function __construct(
        private int $targetWidth = 224,
        private int $targetHeight = 224,
    ) {}

    /**
     * Load and preprocess an image file.
     *
     * @param string $imagePath Path to image file
     * @return array<array<array<float>>> Preprocessed image as [height][width][channels]
     * @throws RuntimeException If image cannot be loaded or processed
     */
    public function preprocessImage(string $imagePath): array
    {
        // Validate file exists
        if (!file_exists($imagePath)) {
            throw new RuntimeException("Image file not found: $imagePath");
        }

        // Load image based on extension
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));
        $image = match ($extension) {
            'jpg', 'jpeg' => imagecreatefromjpeg($imagePath),
            'png' => imagecreatefrompng($imagePath),
            'gif' => imagecreatefromgif($imagePath),
            'webp' => imagecreatefromwebp($imagePath),
            default => throw new RuntimeException("Unsupported image format: $extension"),
        };

        if ($image === false) {
            throw new RuntimeException("Failed to load image: $imagePath");
        }

        // Resize to target dimensions
        $resized = imagescale($image, $this->targetWidth, $this->targetHeight);
        imagedestroy($image);

        if ($resized === false) {
            throw new RuntimeException("Failed to resize image");
        }

        // Convert to normalized pixel array
        $pixels = $this->imageToArray($resized);
        imagedestroy($resized);

        return $pixels;
    }

    /**
     * Convert GD image resource to normalized pixel array.
     *
     * @param \GdImage $image GD image resource
     * @return array<array<array<float>>> Pixel array [height][width][RGB]
     */
    private function imageToArray(\GdImage $image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $pixels = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);

                // Extract RGB components and normalize to 0-1
                $pixels[] = [
                    (($rgb >> 16) & 0xFF) / 255.0,  // Red channel
                    (($rgb >> 8) & 0xFF) / 255.0,   // Green channel
                    ($rgb & 0xFF) / 255.0            // Blue channel
                ];
            }
        }

        return $pixels;
    }

    /**
     * Preprocess multiple images in batch.
     *
     * @param array<string> $imagePaths Array of image file paths
     * @return array<array<array<array<float>>>> Array of preprocessed images
     */
    public function preprocessBatch(array $imagePaths): array
    {
        $batch = [];
        foreach ($imagePaths as $path) {
            $batch[] = $this->preprocessImage($path);
        }
        return $batch;
    }
}

// Example usage
if (PHP_SAPI === 'cli') {
    echo "Image Preprocessor Test\n";
    echo "========================\n\n";

    $preprocessor = new ImagePreprocessor();

    // Create a test image if none exists
    $testImagePath = '/tmp/test_image.jpg';
    if (!file_exists($testImagePath)) {
        echo "Creating test image...\n";
        $img = imagecreatetruecolor(400, 300);
        $blue = imagecolorallocate($img, 0, 0, 255);
        imagefill($img, 0, 0, $blue);
        imagejpeg($img, $testImagePath);
        imagedestroy($img);
    }

    try {
        echo "Preprocessing image: $testImagePath\n";
        $pixels = $preprocessor->preprocessImage($testImagePath);

        $totalPixels = count($pixels);
        $expectedPixels = 224 * 224;

        echo "✓ Image preprocessed successfully\n";
        echo "Total pixels: $totalPixels (expected: $expectedPixels)\n";
        echo "Channels per pixel: " . count($pixels[0]) . " (expected: 3 for RGB)\n";
        echo "Sample pixel values: [" .
            round($pixels[0][0], 3) . ", " .
            round($pixels[0][1], 3) . ", " .
            round($pixels[0][2], 3) . "]\n";
        echo "Value range: 0.0 to 1.0 (normalized) ✓\n";

    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
```

**Test the preprocessor:**

```bash
php 03-image-preprocessor.php
```

### Expected Result

```
Image Preprocessor Test
========================

Creating test image...
Preprocessing image: /tmp/test_image.jpg
✓ Image preprocessed successfully
Total pixels: 50176 (expected: 50176)
Channels per pixel: 3 (expected: 3 for RGB)
Sample pixel values: [0.0, 0.0, 1.0]
Value range: 0.0 to 1.0 (normalized) ✓
```

### Why It Works

**Format detection**: The `match` expression automatically handles JPEG, PNG, GIF, and WebP based on file extension.

**Aspect ratio**: `imagescale()` resizes to exact dimensions. For production, you might want to preserve aspect ratio by cropping or padding.

**Normalization**: We divide RGB values (0-255) by 255.0 to get the 0.0-1.0 range TensorFlow expects.

**Flattened structure**: Images are stored as a flat list of pixels (not a 2D array) because that's easier to serialize to JSON. TensorFlow Serving understands both formats.

**Resource cleanup**: We call `imagedestroy()` to free memory after processing each image.

### Troubleshooting

**Error: "Call to undefined function imagecreatefromjpeg()"**

- **Cause**: GD extension not enabled
- **Solution**: Install PHP GD: `apt-get install php-gd` (Linux) or enable in `php.ini`

**Error: "Unsupported image format"**

- **Cause**: Image format not handled
- **Solution**: Convert image to JPEG/PNG or add support for the format

**Error: "Failed to resize image"**

- **Cause**: Corrupted image or insufficient memory
- **Solution**: Validate image with `getimagesize()` before processing

**Memory issues with large images:**

- **Cause**: PHP memory limit
- **Solution**: Increase `memory_limit` in `php.ini` or use Imagick instead of GD for better memory handling

## Step 6: Creating the Classification Service (~7 min)

### Goal

Combine the TensorFlowClient and ImagePreprocessor into a unified `ImageClassifier` service that handles the complete classification pipeline.

### Actions

**Create the ImageClassifier class:**

```php
# filename: 04-image-classifier.php
<?php

declare(strict_types=1);

require_once '02-tensorflow-client.php';
require_once '03-image-preprocessor.php';

/**
 * Complete image classification service.
 *
 * Combines preprocessing, prediction, and label decoding
 * for end-to-end image classification.
 */
final class ImageClassifier
{
    private array $labels = [];

    public function __construct(
        private TensorFlowClient $client,
        private ImagePreprocessor $preprocessor,
        private string $modelName = 'mobilenet',
        ?string $labelsPath = null,
    ) {
        // Load ImageNet labels if provided
        if ($labelsPath && file_exists($labelsPath)) {
            $this->labels = json_decode(
                file_get_contents($labelsPath),
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        }
    }

    /**
     * Classify a single image.
     *
     * @param string $imagePath Path to image file
     * @param int $topK Number of top predictions to return
     * @return array<array{class: int, label: string, confidence: float}> Top predictions
     */
    public function classify(string $imagePath, int $topK = 5): array
    {
        // Preprocess image
        $pixels = $this->preprocessor->preprocessImage($imagePath);

        // Prepare request
        $instances = [['input' => $pixels]];

        // Get predictions from TensorFlow Serving
        $predictions = $this->client->predict($this->modelName, $instances);
        $probabilities = $predictions[0];

        // Get top K predictions
        return $this->getTopPredictions($probabilities, $topK);
    }

    /**
     * Classify multiple images in batch.
     *
     * @param array<string> $imagePaths Array of image file paths
     * @param int $topK Number of top predictions per image
     * @return array<array<array{class: int, label: string, confidence: float}>> Predictions per image
     */
    public function classifyBatch(array $imagePaths, int $topK = 5): array
    {
        // Preprocess all images
        $batch = $this->preprocessor->preprocessBatch($imagePaths);

        // Prepare batch request
        $instances = array_map(fn($pixels) => ['input' => $pixels], $batch);

        // Get predictions
        $predictions = $this->client->predict($this->modelName, $instances);

        // Process each prediction
        $results = [];
        foreach ($predictions as $probabilities) {
            $results[] = $this->getTopPredictions($probabilities, $topK);
        }

        return $results;
    }

    /**
     * Extract top K predictions from probability array.
     *
     * @param array<float> $probabilities Probability scores for all classes
     * @param int $topK Number of top predictions to return
     * @return array<array{class: int, label: string, confidence: float}> Sorted predictions
     */
    private function getTopPredictions(array $probabilities, int $topK): array
    {
        // Sort probabilities in descending order, keeping indices
        arsort($probabilities);

        // Take top K
        $topIndices = array_slice(array_keys($probabilities), 0, $topK, true);

        $results = [];
        foreach ($topIndices as $classIndex) {
            $confidence = $probabilities[$classIndex];
            $label = $this->labels[$classIndex] ?? "Class $classIndex";

            $results[] = [
                'class' => $classIndex,
                'label' => $label,
                'confidence' => $confidence,
            ];
        }

        return $results;
    }
}

// Example usage
if (PHP_SAPI === 'cli') {
    echo "Image Classifier Test\n";
    echo "======================\n\n";

    try {
        // Initialize components
        $client = new TensorFlowClient();
        $preprocessor = new ImagePreprocessor();
        $labelsPath = __DIR__ . '/data/imagenet_labels.json';

        $classifier = new ImageClassifier(
            client: $client,
            preprocessor: $preprocessor,
            labelsPath: $labelsPath
        );

        // Create a simple test image (solid color for testing)
        $testImage = '/tmp/test_classification.jpg';
        if (!file_exists($testImage)) {
            $img = imagecreatetruecolor(300, 300);
            $color = imagecolorallocate($img, 128, 128, 128);
            imagefill($img, 0, 0, $color);
            imagejpeg($img, $testImage, 90);
            imagedestroy($img);
        }

        echo "Classifying image: $testImage\n\n";

        $startTime = microtime(true);
        $predictions = $classifier->classify($testImage, topK: 3);
        $duration = microtime(true) - $startTime;

        echo "Top 3 Predictions:\n";
        echo "==================\n\n";

        foreach ($predictions as $i => $pred) {
            $rank = $i + 1;
            $confidence = round($pred['confidence'] * 100, 2);
            echo "$rank. {$pred['label']}\n";
            echo "   Class: {$pred['class']}\n";
            echo "   Confidence: $confidence%\n\n";
        }

        echo "Classification time: " . round($duration * 1000, 2) . " ms\n";

    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        echo "\nMake sure:\n";
        echo "  1. TensorFlow Serving is running\n";
        echo "  2. data/imagenet_labels.json exists\n";
        exit(1);
    }
}
```

**Test the classifier:**

```bash
php 04-image-classifier.php
```

### Expected Result

```
Image Classifier Test
======================

Classifying image: /tmp/test_classification.jpg

Top 3 Predictions:
==================

1. gray whale
   Class: 147
   Confidence: 12.34%

2. loafer
   Class: 849
   Confidence: 8.92%

3. photocopier
   Class: 713
   Confidence: 7.65%

Classification time: 245.67 ms
```

::: info Why Random Results?
The test image is solid gray, so the model's predictions are essentially random guesses. With real photos of objects, you'll see much higher confidence scores (often >90%) for the correct class.
:::

### Why It Works

**Dependency injection**: The classifier receives its dependencies (client, preprocessor) via constructor, making it testable and flexible.

**Named parameters**: PHP 8.4 named parameters make the constructor call self-documenting.

**Top-K predictions**: Instead of just the best prediction, we return the top K, which is useful for showing alternative possibilities.

**Batch support**: The `classifyBatch()` method processes multiple images in one API call, dramatically improving throughput.

**Graceful degradation**: If labels aren't available, we fall back to class indices rather than failing.

### Troubleshooting

**Error: "data/imagenet_labels.json not found"**

- **Cause**: Labels file missing
- **Solution**: Download from code repository or create it (see next section for sample)

**Very slow classification (>2 seconds):**

- **Cause**: Cold start or network latency
- **Solution**: First request is always slower. Subsequent requests should be <500ms

**Low confidence scores (<50%) on clear images:**

- **Cause**: Incorrect preprocessing or model mismatch
- **Solution**: Verify image is being resized correctly and normalized to 0-1 range

## Step 7: Building a Web Interface (~5 min)

### Goal

Create a simple web interface where users can upload images and see classification results in real-time.

### Actions

**Create the web interface:**

```php
# filename: 06-web-upload.php
<?php

declare(strict_types=1);

require_once '02-tensorflow-client.php';
require_once '03-image-preprocessor.php';
require_once '04-image-classifier.php';

// Handle image upload and classification
$predictions = null;
$uploadedImagePath = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        // Validate upload
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed');
        }

        // Save uploaded file temporarily
        $uploadedImagePath = '/tmp/uploaded_' . uniqid() . '.jpg';
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadedImagePath)) {
            throw new RuntimeException('Failed to save uploaded file');
        }

        // Initialize classifier
        $client = new TensorFlowClient();
        $preprocessor = new ImagePreprocessor();
        $classifier = new ImageClassifier(
            client: $client,
            preprocessor: $preprocessor,
            labelsPath: __DIR__ . '/data/imagenet_labels.json'
        );

        // Classify the image
        $predictions = $classifier->classify($uploadedImagePath, topK: 5);

    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Image Classification with TensorFlow</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-top: 0;
        }
        .upload-form {
            margin: 20px 0;
        }
        input[type="file"] {
            margin: 10px 0;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        button:hover {
            background: #0056b3;
        }
        .results {
            margin-top: 30px;
        }
        .uploaded-image {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin: 20px 0;
        }
        .prediction {
            padding: 12px;
            margin: 10px 0;
            background: #f8f9fa;
            border-left: 4px solid #007bff;
            border-radius: 4px;
        }
        .prediction:first-child {
            border-left-color: #28a745;
            background: #d4edda;
        }
        .confidence {
            font-weight: bold;
            color: #007bff;
        }
        .error {
            padding: 15px;
            background: #f8d7da;
            color: #721c24;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Image Classification</h1>
        <p>Upload an image to classify it using TensorFlow deep learning.</p>

        <form method="POST" enctype="multipart/form-data" class="upload-form">
            <div>
                <label for="image">Choose an image:</label><br>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>
            <button type="submit">Classify Image</button>
        </form>

        <?php if ($error): ?>
            <div class="error">
                <strong>Error:</strong> <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($predictions): ?>
            <div class="results">
                <h2>Results</h2>

                <?php if ($uploadedImagePath && file_exists($uploadedImagePath)): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents($uploadedImagePath)) ?>"
                         alt="Uploaded image"
                         class="uploaded-image">
                <?php endif; ?>

                <h3>Top Predictions:</h3>

                <?php foreach ($predictions as $i => $pred): ?>
                    <div class="prediction">
                        <strong>#<?= $i + 1 ?>: <?= htmlspecialchars($pred['label']) ?></strong><br>
                        <span class="confidence">
                            Confidence: <?= round($pred['confidence'] * 100, 2) ?>%
                        </span><br>
                        <small>Class ID: <?= $pred['class'] ?></small>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <hr style="margin: 30px 0;">
        <p style="color: #666; font-size: 14px;">
            Powered by TensorFlow MobileNetV2 and PHP 8.4
        </p>
    </div>
</body>
</html>
```

**Run the web interface:**

```bash
# Start PHP built-in server
php -S localhost:8000 06-web-upload.php
```

Open your browser to `http://localhost:8000` and upload an image.

### Expected Result

You'll see a clean web interface with:

- File upload form
- Uploaded image preview
- Top 5 predictions with confidence scores
- Green highlight on the top prediction

Try uploading photos of common objects (dog, cat, car, food) to see impressive accuracy!

### Why It Works

**File upload handling**: Standard PHP `$_FILES` handling with validation.

**Base64 encoding**: We embed the uploaded image directly in HTML using base64, avoiding the need to serve static files.

**Responsive design**: Simple CSS that works on mobile and desktop.

**Error handling**: User-friendly error messages for upload failures or classification errors.

**Security**: We use `htmlspecialchars()` to prevent XSS attacks on user-provided data.

### Troubleshooting

**Error: "Failed to save uploaded file"**

- **Cause**: Permission issues on /tmp directory
- **Solution**: Change upload directory or fix permissions: `chmod 777 /tmp`

**Blank page after upload:**

- **Cause**: PHP error_reporting disabled
- **Solution**: Check error logs or enable display_errors in php.ini

**Very large images cause timeout:**

- **Cause**: PHP max_execution_time too low
- **Solution**: Increase `max_execution_time` in php.ini or add `set_time_limit(60);`

**Upload size limit:**

- **Cause**: `upload_max_filesize` in php.ini
- **Solution**: Increase to 10M or more: `upload_max_filesize = 10M`

## Performance Considerations

### Caching Predictions

For production systems, cache predictions to avoid redundant API calls:

```php
public function classifyWithCache(string $imagePath, int $topK = 5): array
{
    $cacheKey = md5_file($imagePath);
    $cacheFile = "/tmp/predictions_cache_{$cacheKey}.json";

    // Check cache
    if (file_exists($cacheFile)) {
        return json_decode(file_get_contents($cacheFile), true);
    }

    // Classify and cache
    $predictions = $this->classify($imagePath, $topK);
    file_put_contents($cacheFile, json_encode($predictions));

    return $predictions;
}
```

### Batch Processing

Process multiple images in one request for 3-5x throughput improvement:

```php
// Instead of 100 separate requests:
foreach ($images as $image) {
    $results[] = $classifier->classify($image);  // Slow!
}

// Use batch processing:
$results = $classifier->classifyBatch($images);  // Fast!
```

### Asynchronous Processing

For web applications, use background jobs for classification:

```php
// Queue classification job
$queue->push(new ClassifyImageJob($imagePath));

// Return immediately to user
return response()->json(['status' => 'processing', 'job_id' => $jobId]);
```

The user polls for results or receives a webhook callback when complete.

### Model Optimization

- **Quantization**: Use INT8 models instead of FP32 for 4x smaller size and faster inference
- **Model selection**: MobileNet is fast but less accurate; ResNet50 is slower but more accurate
- **GPU acceleration**: TensorFlow Serving can use GPUs for 10-100x speedup on large models

### Scaling TensorFlow Serving

For high-traffic applications:

1. **Horizontal scaling**: Run multiple TensorFlow Serving containers behind a load balancer
2. **Request batching**: TensorFlow Serving automatically batches concurrent requests
3. **Model versions**: Deploy new model versions without downtime
4. **Dedicated hardware**: Run TensorFlow Serving on GPU instances for maximum performance

## Exercises

Test your understanding with these hands-on exercises.

### Exercise 1: Multiple Image Format Support

**Goal**: Extend the ImagePreprocessor to handle additional image formats and provide detailed format information.

Create a file called `exercise1-formats.php` that:

- Adds support for BMP and TIFF formats (using Imagick if available)
- Validates image dimensions before processing (reject images <10px or >5000px)
- Returns metadata about the original image (dimensions, format, file size)
- Handles animated GIFs by extracting only the first frame

**Validation**: Test with various image formats and verify all are processed correctly.

Expected output:

```
Original: 1920x1080 JPEG (234 KB)
Preprocessed: 224x224 array with 50,176 pixels
Format: Successfully converted JPEG → TensorFlow format
```

### Exercise 2: Batch Prediction Optimization

**Goal**: Implement efficient batch processing with progress tracking and error handling.

Create a file called `exercise2-batch.php` that:

- Processes a directory of images (use `glob()` to find all .jpg files)
- Shows progress bar or percentage as images are processed
- Handles individual image failures without stopping the batch
- Generates a summary report (total processed, average confidence, most common predictions)
- Measures and reports total time vs. sequential processing time

**Validation**: Process 10-20 images and verify batch is faster than individual requests.

Expected output:

```
Processing 15 images...
Progress: [████████████████████] 100% (15/15)
✓ 14 successful, 1 failed
Average confidence: 87.3%
Most predicted: golden retriever (6 images)
Batch time: 2.34s vs Sequential: 8.92s (3.8x faster)
```

### Exercise 3: Using a Different Model

**Goal**: Deploy and use ResNet50 instead of MobileNetV2 to compare accuracy and performance.

Create a file called `exercise3-resnet.php` that:

- Downloads ResNet50 using a Python script (similar to MobileNetV2)
- Starts a second TensorFlow Serving container on port 8502 with ResNet50
- Classifies the same test images with both models
- Compares predictions, confidence scores, and inference time
- Displays side-by-side results showing when models agree/disagree

**Validation**: Verify ResNet50 generally has higher confidence but slower inference.

Expected output:

```
Image: golden_retriever.jpg

MobileNetV2: golden retriever (94.2%) - 245ms
ResNet50: golden retriever (98.7%) - 512ms

Image: tabby_cat.jpg

MobileNetV2: tabby cat (89.3%) - 238ms
ResNet50: Egyptian cat (91.2%) - 498ms

Agreement: 8/10 images
ResNet50 avg confidence: +5.3 percentage points
ResNet50 avg time: +108% slower
```

### Exercise 4: Production Caching System

**Goal**: Build a production-ready caching layer with Redis or file-based caching.

Create a file called `exercise4-caching.php` that:

- Implements a `CachedImageClassifier` wrapper class
- Uses Redis if available, falls back to file-based cache
- Caches by image content hash (not filename)
- Implements cache expiration (e.g., 24 hours)
- Tracks cache hit rate and reports statistics
- Provides cache warming for common images

**Validation**: First request is slow, subsequent requests for same image are instant.

Expected output:

```
Classifying 20 images (5 unique, 15 duplicates)...

First pass (cold cache):
  Total time: 4.56s
  Cache hits: 0/20 (0%)

Second pass (warm cache):
  Total time: 0.12s
  Cache hits: 20/20 (100%)

Speed improvement: 38x faster with cache
```

## Troubleshooting

### Docker and TensorFlow Serving Issues

**Problem**: Container exits immediately after starting

**Symptoms**: `docker ps` shows no tensorflow_serving container

**Causes**:

- Model path doesn't exist
- Incorrect model format
- Port already in use

**Solutions**:

```bash
# Check container logs
docker logs tensorflow_serving

# Verify model directory structure
ls -R /tmp/mobilenet

# Should show:
# /tmp/mobilenet/1/saved_model.pb
# /tmp/mobilenet/1/variables/

# Remove existing container and restart
docker rm -f tensorflow_serving
./start_tensorflow_serving.sh
```

**Problem**: "Connection refused" when calling API

**Symptoms**: cURL error in PHP

**Causes**:

- TensorFlow Serving not running
- Wrong port number
- Docker network issues

**Solutions**:

```bash
# Verify container is running
docker ps | grep tensorflow_serving

# Test endpoint directly
curl http://localhost:8501/v1/models/mobilenet

# Check Docker port mapping
docker port tensorflow_serving
```

### Image Processing Issues

**Problem**: "Failed to load image" error

**Symptoms**: `imagecreatefromjpeg()` returns false

**Causes**:

- Corrupted image file
- Unsupported format
- Missing GD extension

**Solutions**:

```php
// Validate image before processing
$info = getimagesize($imagePath);
if ($info === false) {
    throw new RuntimeException("Invalid or corrupted image file");
}

// Check GD support
if (!extension_loaded('gd')) {
    throw new RuntimeException("GD extension not installed");
}
```

**Problem**: Classifications are random/nonsensical

**Symptoms**: Very low confidence (<20%) on clear images

**Causes**:

- Incorrect normalization
- Wrong color channel order
- Model mismatch

**Solutions**:

```php
// Verify pixel values are in 0-1 range
$pixels = $preprocessor->preprocessImage($imagePath);
$min = min(array_map('min', array_map(fn($p) => $p, $pixels)));
$max = max(array_map('max', array_map(fn($p) => $p, $pixels)));

echo "Pixel value range: $min to $max (should be 0.0 to 1.0)\n";

// Verify image is RGB (not BGR)
// Verify image dimensions are exactly 224x224
```

### Performance Issues

**Problem**: Very slow predictions (>3 seconds)

**Symptoms**: Long wait times for classification

**Causes**:

- Cold start (first request)
- CPU-only inference
- Large images
- Network latency

**Solutions**:

```php
// Add timing instrumentation
$start = microtime(true);
$preprocessTime = 0;
$requestTime = 0;

// Measure preprocessing
$startPreprocess = microtime(true);
$pixels = $preprocessor->preprocessImage($imagePath);
$preprocessTime = microtime(true) - $startPreprocess;

// Measure request
$startRequest = microtime(true);
$predictions = $client->predict('mobilenet', [['input' => $pixels]]);
$requestTime = microtime(true) - $startRequest;

$total = microtime(true) - $start;

echo "Preprocess: {$preprocessTime}s\n";
echo "Request: {$requestTime}s\n";
echo "Total: {$total}s\n";

// If preprocessing is slow: reduce image size before loading
// If request is slow: check TensorFlow Serving logs, consider GPU
```

**Problem**: Out of memory errors

**Symptoms**: PHP fatal error or Docker container crash

**Causes**:

- Processing too many large images simultaneously
- Memory leaks in image processing
- Insufficient Docker memory

**Solutions**:

```php
// Process images in smaller batches
$batchSize = 10;
$batches = array_chunk($imagePaths, $batchSize);

foreach ($batches as $batch) {
    $results = array_merge($results, $classifier->classifyBatch($batch));

    // Force garbage collection
    gc_collect_cycles();
}

// Increase Docker memory limit
docker run --memory=4g --memory-swap=4g ...
```

### JSON Encoding Errors

**Problem**: "Malformed JSON" from TensorFlow Serving

**Symptoms**: `json_decode()` fails

**Causes**:

- Incorrect request format
- NaN or Infinity values in input
- Model returned error message

**Solutions**:

```php
// Add detailed error logging
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode !== 200) {
    error_log("TensorFlow Serving Error (HTTP $httpCode): $response");
    throw new RuntimeException("Prediction failed - see error log");
}

// Validate JSON before decoding
if (!json_validate($response)) {  // PHP 8.3+
    throw new RuntimeException("Invalid JSON response from TensorFlow Serving");
}
```

## Wrap-up

Congratulations! You've successfully integrated TensorFlow deep learning into PHP. Let's recap what you've accomplished:

✓ **Understood TensorFlow integration options** and chose the production-ready approach (TensorFlow Serving + REST API)

✓ **Deployed TensorFlow Serving** using Docker with a pre-trained MobileNetV2 model

✓ **Created a reusable TensorFlowClient class** that handles HTTP communication with proper error handling

✓ **Built an ImagePreprocessor** that loads, resizes, and normalizes images for deep learning models

✓ **Implemented a complete ImageClassifier service** combining preprocessing, prediction, and label decoding

✓ **Developed a web interface** for real-time image classification with file uploads

✓ **Applied performance optimization** techniques including batch processing and caching

✓ **Gained hands-on experience** with production-grade deep learning deployment patterns

### Real-World Applications

The architecture you've learned enables powerful use cases:

- **Content moderation**: Automatically detect inappropriate images in user uploads
- **Product tagging**: E-commerce sites can auto-tag product photos for search
- **Visual search**: Find similar products by uploading a photo
- **Quality control**: Manufacturing systems detecting defects in photos
- **Medical imaging**: Preliminary screening of X-rays or scans (with specialized models)
- **Security**: Facial recognition or object detection in surveillance feeds

### Key Takeaways

**Microservice architecture works**: Separating AI inference into a dedicated service (TensorFlow Serving) allows PHP to focus on web logic while TensorFlow handles heavy computation. This scales better than trying to run everything in PHP.

**Pre-trained models are powerful**: MobileNetV2 achieves impressive accuracy without training. For many tasks, transfer learning or fine-tuning pre-trained models is sufficient.

**Preprocessing matters**: Deep learning models are sensitive to input format. Correct preprocessing (resizing, normalization) is critical for accurate predictions.

**Batch processing is essential**: For high-throughput applications, batch processing provides 3-5x speedup over sequential requests.

**Error handling is crucial**: Network issues, model failures, and invalid inputs happen. Production systems need comprehensive error handling and fallbacks.

### What's Next?

In [Chapter 13](/series/ai-ml-php-developers/chapters/13-natural-language-processing-nlp-fundamentals), you'll apply similar integration patterns to natural language processing (NLP). You'll move from images to text, learning how to process language data, extract features, and build text classification systems in PHP. The skills you've learned with TensorFlow Serving transfer directly to NLP models.

You've crossed an important milestone—you can now leverage state-of-the-art deep learning in any PHP application. Deep learning is no longer just for Python developers!

## Further Reading

### TensorFlow & TensorFlow Serving

- [TensorFlow Serving Documentation](https://www.tensorflow.org/tfx/guide/serving) — Official guide to deploying models
- [TensorFlow SavedModel Format](https://www.tensorflow.org/guide/saved_model) — Understanding model serialization
- [TensorFlow Serving REST API](https://www.tensorflow.org/tfx/serving/api_rest) — Complete API reference
- [TensorFlow Model Optimization](https://www.tensorflow.org/model_optimization) — Quantization and pruning

### Deep Learning Concepts

- [ImageNet Dataset](https://www.image-net.org/) — The dataset MobileNetV2 was trained on
- [MobileNetV2 Paper](https://arxiv.org/abs/1801.04381) — Original research paper
- [Convolutional Neural Networks Explained](https://cs231n.github.io/convolutional-networks/) — Stanford CS231n course notes

### PHP Image Processing

- [PHP GD Documentation](https://www.php.net/manual/en/book.image.php) — Official GD reference
- [Imagick Documentation](https://www.php.net/manual/en/book.imagick.php) — Alternative image library

### Alternative Approaches

- [ONNX Runtime](https://onnxruntime.ai/) — Cross-platform ML inference
- [TensorFlow Lite](https://www.tensorflow.org/lite) — Lightweight models for mobile/embedded
- [PyTorch Serving with TorchServe](https://pytorch.org/serve/) — Similar to TensorFlow Serving for PyTorch models

### Production Deployment

- [Kubernetes for TensorFlow](https://www.tensorflow.org/tfx/serving/serving_kubernetes) — Deploying at scale
- [Docker Compose for Multi-Model Serving](https://docs.docker.com/compose/) — Managing multiple containers
- [Load Balancing ML Services](https://eng.uber.com/michelangelo-machine-learning-platform/) — Uber's ML infrastructure (case study)

## Knowledge Check

<Quiz
  title="Chapter 12 Quiz: Deep Learning with TensorFlow and PHP"
  :questions="[
    {
      question: 'Why is TensorFlow Serving + REST API the recommended approach for integrating TensorFlow with PHP?',
      options: [
        { 
          text: 'It provides a production-tested, language-agnostic, scalable solution', 
          correct: true, 
          explanation: 'TensorFlow Serving is used by Google, Uber, and other major companies in production. The REST API allows any language (including PHP) to access models without platform-specific extensions or complex compilation.' 
        },
        { 
          text: 'It is faster than native PHP extensions', 
          correct: false, 
          explanation: 'Native extensions would theoretically be faster by avoiding network overhead, but they are experimental, difficult to install, and not production-ready. TensorFlow Serving\'s optimization and batching make it fast enough for production use.' 
        },
        { 
          text: 'It requires less memory than other approaches', 
          correct: false, 
          explanation: 'TensorFlow Serving runs in a separate process/container, so total memory usage is actually higher. The benefit is scalability and separation of concerns, not lower memory usage.' 
        },
        { 
          text: 'It eliminates the need for image preprocessing', 
          correct: false, 
          explanation: 'Regardless of how you communicate with TensorFlow, you still need to preprocess images to the format the model expects (resizing, normalization, etc.).' 
        }
      ]
    },
    {
      question: 'What is the correct pixel value range for MobileNetV2 input?',
      options: [
        { 
          text: '0.0 to 1.0 (normalized)', 
          correct: true, 
          explanation: 'MobileNetV2 expects pixel values normalized to the 0.0-1.0 range. We divide raw pixel values (0-255) by 255.0 to achieve this normalization.' 
        },
        { 
          text: '0 to 255 (raw RGB values)', 
          correct: false, 
          explanation: 'While raw images have 0-255 pixel values, MobileNetV2 expects normalized 0.0-1.0 values. Forgetting to normalize is a common mistake that results in poor predictions.' 
        },
        { 
          text: '-1.0 to +1.0 (centered)', 
          correct: false, 
          explanation: 'Some models (like Inception) use -1 to +1 normalization, but MobileNetV2 uses 0-1. Always check the specific model\'s preprocessing requirements.' 
        },
        { 
          text: '0 to 1000 (class indices)', 
          correct: false, 
          explanation: 'Class indices (0-999) are for the output, not the input. Input is pixel values, output is class probabilities.' 
        }
      ]
    },
    {
      question: 'Why is batch processing faster than sequential prediction?',
      options: [
        { 
          text: 'TensorFlow Serving can process multiple images in parallel with shared overhead', 
          correct: true, 
          explanation: 'Batch processing amortizes the fixed costs of model loading and inference across multiple samples. TensorFlow can also optimize matrix operations for batches using SIMD instructions and GPU parallelization.' 
        },
        { 
          text: 'Batch processing skips image preprocessing', 
          correct: false, 
          explanation: 'Batch processing still requires preprocessing each image individually. The speedup comes from combining multiple predictions into one API request.' 
        },
        { 
          text: 'The model uses lower precision for batch predictions', 
          correct: false, 
          explanation: 'Batch and single predictions use the same model precision. The accuracy is identical, only the throughput differs.' 
        },
        { 
          text: 'PHP can process multiple images simultaneously using threads', 
          correct: false, 
          explanation: 'PHP (in typical web deployments) doesn\'t use threading for this. The speedup comes from TensorFlow Serving\'s ability to batch process on the server side, not PHP threading.' 
        }
      ]
    },
    {
      question: 'What happens if you send an image with dimensions other than 224x224 to MobileNetV2?',
      options: [
        { 
          text: 'TensorFlow Serving returns an error about mismatched tensor shapes', 
          correct: true, 
          explanation: 'MobileNetV2 expects exactly 224x224x3 input. If you send a different size, TensorFlow will reject it with a shape mismatch error. This is why preprocessing (resizing) is mandatory.' 
        },
        { 
          text: 'The model automatically resizes the image', 
          correct: false, 
          explanation: 'TensorFlow Serving does not automatically resize images. You must preprocess images to the exact dimensions the model expects before sending them.' 
        },
        { 
          text: 'The model crops or pads the image to fit', 
          correct: false, 
          explanation: 'The model itself doesn\'t crop or pad. If you want those behaviors, you must implement them in your preprocessing code before sending to TensorFlow Serving.' 
        },
        { 
          text: 'Predictions are less accurate but still work', 
          correct: false, 
          explanation: 'The prediction would fail entirely with an error, not produce inaccurate results. The model cannot process mismatched input shapes.' 
        }
      ]
    },
    {
      question: 'Which caching strategy is most effective for image classification?',
      options: [
        { 
          text: 'Cache by image content hash (e.g., MD5), not filename', 
          correct: true, 
          explanation: 'Caching by content hash ensures that identical images are recognized even if they have different filenames. If the same image is uploaded multiple times (even with different names), you get a cache hit.' 
        },
        { 
          text: 'Cache by filename since it uniquely identifies images', 
          correct: false, 
          explanation: 'Filenames can change while content stays the same (copy of the same image with different name). Content hashing is more reliable.' 
        },
        { 
          text: 'Cache the preprocessed image data to skip preprocessing', 
          correct: false, 
          explanation: 'While you could cache preprocessed data, it\'s much larger than caching just the predictions. Preprocessing is fast compared to inference, so caching predictions is more efficient.' 
        },
        { 
          text: 'Don\'t cache anything; classifications are always unique', 
          correct: false, 
          explanation: 'In many applications (e.g., product tagging), the same images are classified repeatedly. Caching provides significant performance improvements.' 
        }
      ]
    }
  ]"
/>
