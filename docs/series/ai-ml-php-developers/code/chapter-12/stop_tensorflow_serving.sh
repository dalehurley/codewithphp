#!/bin/bash
#
# Stop TensorFlow Serving container
#

echo "Stopping TensorFlow Serving..."

if docker ps | grep -q tensorflow_serving; then
    docker stop tensorflow_serving
    docker rm tensorflow_serving
    echo "✓ TensorFlow Serving stopped and removed"
else
    echo "TensorFlow Serving is not running"
fi

