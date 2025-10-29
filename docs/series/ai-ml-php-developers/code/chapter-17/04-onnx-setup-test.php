<?php

declare(strict_types=1);

/**
 * ONNX Runtime Setup Verification
 * 
 * Checks that Python, ONNX Runtime, and model files are properly configured
 */

echo "ONNX Runtime Setup Verification\n";
echo str_repeat('=', 50) . "\n\n";

// Check Python
$pythonVersion = trim((string) shell_exec('python3 --version 2>&1'));
echo "Python: {$pythonVersion}\n";

// Check ONNX Runtime
$onnxCheck = trim((string) shell_exec('python3 -c "import onnxruntime; print(f\'v{onnxruntime.__version__}\')" 2>&1'));
if (str_contains($onnxCheck, 'ModuleNotFoundError')) {
    echo "ONNX Runtime: ✗ Not installed\n";
    echo "   Install with: pip3 install onnxruntime pillow numpy\n\n";
} else {
    echo "ONNX Runtime: {$onnxCheck}\n";
}

// Check PIL/Pillow
$pilCheck = trim((string) shell_exec('python3 -c "import PIL; print(f\'v{PIL.__version__}\')" 2>&1'));
if (str_contains($pilCheck, 'ModuleNotFoundError')) {
    echo "Pillow (PIL): ✗ Not installed\n";
} else {
    echo "Pillow (PIL): {$pilCheck}\n";
}

// Check NumPy
$numpyCheck = trim((string) shell_exec('python3 -c "import numpy; print(f\'v{numpy.__version__}\')" 2>&1'));
if (str_contains($numpyCheck, 'ModuleNotFoundError')) {
    echo "NumPy: ✗ Not installed\n";
} else {
    echo "NumPy: {$numpyCheck}\n";
}

echo "\n";

// Check model file
$modelPath = __DIR__ . '/models/mobilenetv2-7.onnx';
if (file_exists($modelPath)) {
    $size = round(filesize($modelPath) / 1024 / 1024, 1);
    echo "✓ Model file found: {$size} MB\n";
} else {
    echo "✗ Model file not found. Run: ./download_model.sh\n";
}

// Check labels file
$labelsPath = __DIR__ . '/data/imagenet_labels.json';
if (file_exists($labelsPath)) {
    $labels = json_decode(file_get_contents($labelsPath), true);
    echo "✓ Labels file found: " . count($labels) . " classes\n";
} else {
    echo "✗ Labels file not found. Run: ./download_model.sh\n";
}

// Check Python inference script
$scriptPath = __DIR__ . '/onnx_inference.py';
if (file_exists($scriptPath)) {
    echo "✓ Python inference script found\n";
} else {
    echo "✗ Python inference script not found\n";
}

echo "\n";

// Test classification if everything is ready
if (
    file_exists($modelPath) &&
    file_exists($labelsPath) &&
    file_exists($scriptPath) &&
    !str_contains($onnxCheck, 'ModuleNotFoundError')
) {
    echo "Running test classification...\n";
    $testImage = __DIR__ . '/data/sample_images/cat.jpg';

    if (!file_exists($testImage)) {
        echo "⚠️  Test image not found: {$testImage}\n";
        echo "   Please add sample images to data/sample_images/\n";
    } else {
        $command = sprintf(
            'python3 %s %s %s %s 3 2>&1',
            escapeshellarg($scriptPath),
            escapeshellarg($modelPath),
            escapeshellarg($testImage),
            escapeshellarg($labelsPath)
        );

        $startTime = microtime(true);
        $output = shell_exec($command);
        $duration = microtime(true) - $startTime;

        $result = json_decode($output, true);

        if (isset($result['error'])) {
            echo "✗ Error: " . $result['error'] . "\n";
        } else if (is_array($result)) {
            echo "✓ Classification successful!\n\n";
            foreach ($result as $prediction) {
                printf(
                    "  %-20s %5.1f%%\n",
                    $prediction['label'],
                    $prediction['confidence'] * 100
                );
            }
            echo "\nInference time: " . round($duration * 1000) . "ms\n";
        } else {
            echo "✗ Invalid response:\n{$output}\n";
        }
    }
} else {
    echo "⚠️  Setup incomplete. Complete the steps above and try again.\n";
}
