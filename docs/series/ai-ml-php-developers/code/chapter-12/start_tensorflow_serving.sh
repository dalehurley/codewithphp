#!/bin/bash
#
# Start TensorFlow Serving with MobileNetV2 model
#

set -e

echo "Starting TensorFlow Serving..."
echo ""

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "✗ Docker is not running"
    echo "  Please start Docker Desktop and try again"
    exit 1
fi

# Check if model exists
if [ ! -d "/tmp/mobilenet/1" ]; then
    echo "✗ Model not found at /tmp/mobilenet/1"
    echo "  Please run: python3 download_model.py"
    exit 1
fi

# Stop existing container if running
if docker ps -a | grep -q tensorflow_serving; then
    echo "Stopping existing tensorflow_serving container..."
    docker stop tensorflow_serving > /dev/null 2>&1 || true
    docker rm tensorflow_serving > /dev/null 2>&1 || true
fi

# Pull TensorFlow Serving image (only if not already present)
if ! docker images | grep -q tensorflow/serving; then
    echo "Pulling TensorFlow Serving Docker image..."
    docker pull tensorflow/serving
    echo ""
fi

# Start TensorFlow Serving
echo "Starting TensorFlow Serving container..."
docker run -d \
  --name tensorflow_serving \
  -p 8501:8501 \
  --mount type=bind,source=/tmp/mobilenet,target=/models/mobilenet \
  -e MODEL_NAME=mobilenet \
  tensorflow/serving > /dev/null

# Wait for it to start
echo "Waiting for TensorFlow Serving to start..."
sleep 3

# Check if it's running
if ! docker ps | grep -q tensorflow_serving; then
    echo "✗ TensorFlow Serving failed to start"
    echo "  Check logs: docker logs tensorflow_serving"
    exit 1
fi

echo ""
echo "=" * 60
echo "✓ TensorFlow Serving started successfully!"
echo "=" * 60
echo ""
echo "Model endpoint:"
echo "  http://localhost:8501/v1/models/mobilenet:predict"
echo ""
echo "Health check:"
echo "  http://localhost:8501/v1/models/mobilenet"
echo ""
echo "Verify it's working:"
echo "  ./verify_serving.sh"
echo ""
echo "To stop TensorFlow Serving:"
echo "  ./stop_tensorflow_serving.sh"
echo ""

