<?php

declare(strict_types=1);

/**
 * Exercise 3 Solution: Using ResNet50 Model
 * 
 * Demonstrates using a different model (ResNet50) and comparing
 * performance and accuracy against MobileNetV2.
 * 
 * Note: This requires downloading and serving ResNet50 separately.
 * See comments below for setup instructions.
 */

/*
 * Setup Instructions:
 * 
 * 1. Download ResNet50:
 *    python3 -c "
 *    import tensorflow as tf
 *    model = tf.keras.applications.ResNet50(weights='imagenet')
 *    tf.saved_model.save(model, '/tmp/resnet50/1')
 *    "
 * 
 * 2. Start TensorFlow Serving on port 8502:
 *    docker run -d --name tf_serving_resnet \
 *      -p 8502:8501 \
 *      --mount type=bind,source=/tmp/resnet50,target=/models/resnet50 \
 *      -e MODEL_NAME=resnet50 \
 *      tensorflow/serving
 * 
 * 3. Run this script:
 *    php exercise3-resnet.php
 */

require_once __DIR__ . '/../02-tensorflow-client.php';
require_once __DIR__ . '/../03-image-preprocessor.php';
require_once __DIR__ . '/../04-image-classifier.php';

echo "Exercise 3: Model Comparison (MobileNetV2 vs ResNet50)\n";
echo "=======================================================\n\n";

// Initialize both classifiers
$preprocessor = new ImagePreprocessor();
$labelsPath = __DIR__ . '/../data/imagenet_labels.json';

$mobileNetClient = new TensorFlowClient(baseUrl: 'http://localhost:8501');
$mobileNetClassifier = new ImageClassifier(
    client: $mobileNetClient,
    preprocessor: $preprocessor,
    modelName: 'mobilenet',
    labelsPath: $labelsPath
);

try {
    $resNetClient = new TensorFlowClient(baseUrl: 'http://localhost:8502');
    $resNetClassifier = new ImageClassifier(
        client: $resNetClient,
        preprocessor: $preprocessor,
        modelName: 'resnet50',
        labelsPath: $labelsPath
    );
    $resNetAvailable = true;
} catch (Exception $e) {
    echo "⚠ ResNet50 not available on port 8502\n";
    echo "  See script comments for setup instructions\n\n";
    $resNetAvailable = false;
}

// Get test images
$sampleDir = __DIR__ . '/../data/sample_images';
$imagePaths = glob("$sampleDir/*.{jpg,jpeg,png}", GLOB_BRACE);

if (empty($imagePaths)) {
    // Create test images
    echo "Creating test images...\n";
    mkdir($sampleDir, 0755, true);

    for ($i = 1; $i <= 5; $i++) {
        $path = "$sampleDir/test_$i.jpg";
        $img = imagecreatetruecolor(300, 300);
        $color = imagecolorallocate($img, rand(0, 255), rand(0, 255), rand(0, 255));
        imagefill($img, 0, 0, $color);
        imagejpeg($img, $path, 90);
        imagedestroy($img);
    }
    $imagePaths = glob("$sampleDir/*.jpg");
    echo "✓ Created " . count($imagePaths) . " test images\n\n";
}

// Compare models
$comparison = [];
$agreement = 0;

foreach (array_slice($imagePaths, 0, 10) as $path) {
    $filename = basename($path);

    echo "Image: $filename\n\n";

    try {
        // MobileNetV2
        $mobileStart = microtime(true);
        $mobilePred = $mobileNetClassifier->classify($path, topK: 1);
        $mobileDuration = (microtime(true) - $mobileStart) * 1000;

        $mobileLabel = $mobilePred[0]['label'];
        $mobileConf = $mobilePred[0]['confidence'] * 100;

        echo "MobileNetV2: $mobileLabel (" . round($mobileConf, 1) . "%) - ";
        echo round($mobileDuration) . "ms\n";

        // ResNet50 (if available)
        if ($resNetAvailable) {
            $resNetStart = microtime(true);
            $resNetPred = $resNetClassifier->classify($path, topK: 1);
            $resNetDuration = (microtime(true) - $resNetStart) * 1000;

            $resNetLabel = $resNetPred[0]['label'];
            $resNetConf = $resNetPred[0]['confidence'] * 100;

            echo "ResNet50: $resNetLabel (" . round($resNetConf, 1) . "%) - ";
            echo round($resNetDuration) . "ms\n";

            if ($mobileLabel === $resNetLabel) {
                echo "✓ Models agree\n";
                $agreement++;
            } else {
                echo "✗ Models disagree\n";
            }

            $comparison[] = [
                'file' => $filename,
                'mobile_conf' => $mobileConf,
                'mobile_time' => $mobileDuration,
                'resnet_conf' => $resNetConf,
                'resnet_time' => $resNetDuration,
            ];
        }

        echo "\n";
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n\n";
    }
}

// Summary statistics
if ($resNetAvailable && !empty($comparison)) {
    echo "\nComparison Summary\n";
    echo "==================\n\n";

    $totalImages = count($comparison);
    $agreementRate = ($agreement / $totalImages) * 100;

    $avgMobileConf = array_sum(array_column($comparison, 'mobile_conf')) / $totalImages;
    $avgResNetConf = array_sum(array_column($comparison, 'resnet_conf')) / $totalImages;

    $avgMobileTime = array_sum(array_column($comparison, 'mobile_time')) / $totalImages;
    $avgResNetTime = array_sum(array_column($comparison, 'resnet_time')) / $totalImages;

    echo "Agreement: $agreement/$totalImages images (" . round($agreementRate) . "%)\n";
    echo "ResNet50 avg confidence: " . round($avgResNetConf, 1) . "% ";
    echo "(+" . round($avgResNetConf - $avgMobileConf, 1) . " percentage points)\n";
    echo "ResNet50 avg time: " . round($avgResNetTime) . "ms ";

    $slowdown = (($avgResNetTime - $avgMobileTime) / $avgMobileTime) * 100;
    echo "(+" . round($slowdown) . "% slower)\n";
}

echo "\n✓ Comparison complete!\n";
