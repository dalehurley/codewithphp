#!/usr/bin/env python3
"""
Download and prepare MobileNetV2 model for TensorFlow Serving.

This script downloads the pre-trained MobileNetV2 model from TensorFlow/Keras
and saves it in the SavedModel format that TensorFlow Serving requires.
"""

import os
import sys
import tensorflow as tf

def main():
    """Download and save MobileNetV2 model."""
    print("Downloading MobileNetV2 from TensorFlow...")
    print("This may take a few minutes on first run.\n")
    
    # Download pre-trained MobileNetV2
    # - weights='imagenet': Use ImageNet pre-trained weights
    # - include_top=True: Include final classification layer (1000 classes)
    model = tf.keras.applications.MobileNetV2(
        weights='imagenet',
        include_top=True,
        input_shape=(224, 224, 3)
    )
    
    print(f"✓ Model downloaded successfully")
    print(f"  Architecture: MobileNetV2")
    print(f"  Parameters: {model.count_params():,}")
    print(f"  Input shape: (224, 224, 3)")
    print(f"  Output classes: 1000 (ImageNet)\n")
    
    # Save in TensorFlow SavedModel format
    # Version 1 - TensorFlow Serving can serve multiple versions
    export_path = '/tmp/mobilenet/1'
    os.makedirs(export_path, exist_ok=True)
    
    print(f"Saving model to {export_path}...")
    
    # Save the model
    # TensorFlow Serving will automatically detect and load this
    tf.saved_model.save(model, export_path)
    
    print(f"✓ Model saved successfully\n")
    
    # Verify the saved model
    print("Verifying saved model...")
    try:
        loaded = tf.saved_model.load(export_path)
        print("✓ Model verification successful\n")
    except Exception as e:
        print(f"✗ Model verification failed: {e}\n")
        sys.exit(1)
    
    print("=" * 60)
    print("Setup complete!")
    print("=" * 60)
    print("\nNext steps:")
    print("  1. Start TensorFlow Serving:")
    print("     ./start_tensorflow_serving.sh")
    print("\n  2. Verify it's running:")
    print("     ./verify_serving.sh")
    print("\n  3. Run PHP examples:")
    print("     php 01-simple-prediction.php")
    print()

if __name__ == '__main__':
    main()

