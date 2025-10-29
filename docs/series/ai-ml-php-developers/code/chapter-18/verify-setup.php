<?php

declare(strict_types=1);

echo "=== Object Detection Environment Verification ===\n\n";

// Check PHP version
echo "1. PHP Version: " . PHP_VERSION;
echo (version_compare(PHP_VERSION, '8.4.0', '>=')) ? " ✓\n" : " ✗ (need 8.4+)\n";

// Check GD extension
echo "2. GD Extension: ";
if (extension_loaded('gd')) {
    echo "✓ Loaded\n";
    $gdInfo = gd_info();
    echo "   - Version: " . $gdInfo['GD Version'] . "\n";
    echo "   - PNG Support: " . ($gdInfo['PNG Support'] ? 'Yes' : 'No') . "\n";
    echo "   - JPEG Support: " . ($gdInfo['JPEG Support'] ? 'Yes' : 'No') . "\n";
} else {
    echo "✗ Not loaded (install php-gd)\n";
}

// Check Imagick (optional but preferred)
echo "3. Imagick Extension: ";
if (extension_loaded('imagick')) {
    echo "✓ Loaded\n";
    $imagick = new Imagick();
    echo "   - Version: " . Imagick::getVersion()['versionString'] . "\n";
} else {
    echo "ℹ Not loaded (optional, GD works fine)\n";
}

// Check Python
echo "4. Python 3: ";
$pythonVersion = shell_exec('python3 --version 2>&1');
echo $pythonVersion ? trim($pythonVersion) . " ✓\n" : "✗ Not found\n";

// Check pip
echo "5. pip3: ";
$pipVersion = shell_exec('pip3 --version 2>&1');
echo $pipVersion ? trim($pipVersion) . " ✓\n" : "✗ Not found\n";

// Check ultralytics (YOLO)
echo "6. Ultralytics (YOLO): ";
$yoloCheck = shell_exec('python3 -c "import ultralytics; print(ultralytics.__version__)" 2>&1');
echo $yoloCheck ? "v" . trim($yoloCheck) . " ✓\n" : "✗ Not installed\n";

// Check OpenCV
echo "7. OpenCV: ";
$cvCheck = shell_exec('python3 -c "import cv2; print(cv2.__version__)" 2>&1');
echo $cvCheck ? "v" . trim($cvCheck) . " ✓\n" : "✗ Not installed\n";

// Test image creation
echo "8. Image Creation Test: ";
try {
    $testImage = imagecreate(100, 100);
    $white = imagecolorallocate($testImage, 255, 255, 255);
    imagefill($testImage, 0, 0, $white);

    $tempFile = sys_get_temp_dir() . '/test_image.png';
    imagepng($testImage, $tempFile);
    imagedestroy($testImage);

    if (file_exists($tempFile)) {
        unlink($tempFile);
        echo "✓ Success\n";
    } else {
        echo "✗ Failed to create image\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Setup Complete ===\n";

// Summary
$requiredChecks = [
    version_compare(PHP_VERSION, '8.4.0', '>='),
    extension_loaded('gd'),
    !empty($pythonVersion),
    !empty($yoloCheck),
    !empty($cvCheck)
];

$passed = count(array_filter($requiredChecks));
$total = count($requiredChecks);

echo "Passed: {$passed}/{$total} required checks\n";

if ($passed === $total) {
    echo "✓ Your environment is ready for object detection!\n";
} else {
    echo "✗ Please install missing requirements above\n";
}
