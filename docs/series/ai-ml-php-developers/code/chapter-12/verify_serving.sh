#!/bin/bash
#
# Verify TensorFlow Serving is running and model is loaded
#

echo "Verifying TensorFlow Serving..."
echo ""

# Check if container is running
if ! docker ps | grep -q tensorflow_serving; then
    echo "✗ TensorFlow Serving container is not running"
    echo "  Start it with: ./start_tensorflow_serving.sh"
    exit 1
fi

echo "✓ Container is running"
echo ""

# Check model status
echo "Checking model status..."
RESPONSE=$(curl -s http://localhost:8501/v1/models/mobilenet)

if [ $? -ne 0 ]; then
    echo "✗ Could not connect to TensorFlow Serving"
    echo "  Make sure port 8501 is not blocked"
    exit 1
fi

# Parse response (basic check for "AVAILABLE")
if echo "$RESPONSE" | grep -q '"state": "AVAILABLE"'; then
    echo "✓ Model is loaded and available"
    echo ""
    echo "$RESPONSE" | python3 -m json.tool 2>/dev/null || echo "$RESPONSE"
    echo ""
    echo "=" * 60
    echo "TensorFlow Serving is ready!"
    echo "=" * 60
    echo ""
    echo "Try these PHP examples:"
    echo "  php 01-simple-prediction.php"
    echo "  php 02-tensorflow-client.php"
    echo "  php 04-image-classifier.php"
    echo ""
else
    echo "✗ Model is not ready"
    echo ""
    echo "Response from server:"
    echo "$RESPONSE"
    echo ""
    echo "Check container logs: docker logs tensorflow_serving"
    exit 1
fi

