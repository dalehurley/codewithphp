#!/bin/bash

# Download MobileNetV2 ONNX model and ImageNet labels
# This script sets up everything needed for local image classification

set -e

echo "Image Classification Model Setup"
echo "================================="
echo

# Create directories
mkdir -p models data/sample_images

# Download MobileNetV2 model (converted to ONNX, ~14MB)
if [ ! -f models/mobilenetv2-7.onnx ]; then
    echo "📥 Downloading MobileNetV2 ONNX model..."
    curl -L -o models/mobilenetv2-7.onnx \
        "https://github.com/onnx/models/raw/main/validated/vision/classification/mobilenet/model/mobilenetv2-7.onnx"
    echo "✓ Model downloaded"
else
    echo "✓ Model already exists"
fi

# Download ImageNet class labels
if [ ! -f data/imagenet_labels.json ]; then
    echo "📥 Downloading ImageNet labels..."
    curl -L -o data/imagenet_labels.txt \
        "https://raw.githubusercontent.com/pytorch/hub/master/imagenet_classes.txt"
    
    # Convert to JSON format using Python
    python3 << 'EOF'
import json

with open('data/imagenet_labels.txt', 'r') as f:
    labels = [line.strip() for line in f]

with open('data/imagenet_labels.json', 'w') as f:
    json.dump(labels, f, indent=2)

print("✓ Labels converted to JSON")
EOF
    
    # Clean up text file
    rm data/imagenet_labels.txt
else
    echo "✓ Labels already exist"
fi

echo
echo "Setup Complete!"
echo "==============="
echo
echo "Model file: models/mobilenetv2-7.onnx ($(ls -lh models/mobilenetv2-7.onnx 2>/dev/null | awk '{print $5}' || echo 'N/A'))"
echo "Labels file: data/imagenet_labels.json ($(cat data/imagenet_labels.json 2>/dev/null | jq '. | length' || echo 0) classes)"
echo
echo "Next steps:"
echo "1. Install Python dependencies: pip3 install onnxruntime pillow numpy"
echo "2. Add sample images to: data/sample_images/"
echo "3. Run test: php 04-onnx-setup-test.php"

