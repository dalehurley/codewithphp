<?php

declare(strict_types=1);

require_once '02-tensorflow-client.php';
require_once '03-image-preprocessor.php';
require_once '04-image-classifier.php';

/**
 * Web interface for image classification.
 * 
 * Allows users to upload images and see real-time classification results
 * with confidence scores and visual feedback.
 */

// Handle image upload and classification
$predictions = null;
$uploadedImagePath = null;
$uploadedImageData = null;
$error = null;
$processingTime = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        // Validate upload
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('File upload failed: ' . $_FILES['image']['error']);
        }

        // Validate file size (max 10MB)
        if ($_FILES['image']['size'] > 10 * 1024 * 1024) {
            throw new RuntimeException('File too large (max 10MB)');
        }

        // Validate file type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            throw new RuntimeException('Invalid file type. Please upload a JPEG, PNG, GIF, or WebP image.');
        }

        // Save uploaded file temporarily
        $uploadedImagePath = '/tmp/uploaded_' . uniqid() . '_' . basename($_FILES['image']['name']);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadedImagePath)) {
            throw new RuntimeException('Failed to save uploaded file');
        }

        // Read image data for display
        $uploadedImageData = base64_encode(file_get_contents($uploadedImagePath));

        // Initialize classifier
        $client = new TensorFlowClient();
        $preprocessor = new ImagePreprocessor();
        $classifier = new ImageClassifier(
            client: $client,
            preprocessor: $preprocessor,
            labelsPath: __DIR__ . '/data/imagenet_labels.json'
        );

        // Classify the image
        $startTime = microtime(true);
        $predictions = $classifier->classify($uploadedImagePath, topK: 5);
        $processingTime = microtime(true) - $startTime;
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 16px;
        }

        .content {
            padding: 40px;
        }

        .upload-section {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s ease;
        }

        .upload-section:hover {
            border-color: #667eea;
            background: #f0f2ff;
        }

        .upload-section label {
            display: block;
            font-size: 18px;
            color: #495057;
            margin-bottom: 20px;
            cursor: pointer;
        }

        input[type="file"] {
            display: none;
        }

        .file-label {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background 0.3s ease;
        }

        .file-label:hover {
            background: #5568d3;
        }

        button {
            background: #28a745;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
            transition: background 0.3s ease;
        }

        button:hover {
            background: #218838;
        }

        button:disabled {
            background: #6c757d;
            cursor: not-allowed;
        }

        .results {
            margin-top: 40px;
        }

        .uploaded-image {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            margin: 20px 0;
        }

        .predictions-title {
            font-size: 24px;
            color: #212529;
            margin: 30px 0 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e9ecef;
        }

        .prediction {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 20px;
            margin: 15px 0;
            transition: all 0.3s ease;
        }

        .prediction:hover {
            border-color: #667eea;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
        }

        .prediction.top {
            border-color: #28a745;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        }

        .prediction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .prediction-label {
            font-size: 20px;
            font-weight: 600;
            color: #212529;
        }

        .prediction.top .prediction-label {
            color: #155724;
        }

        .confidence {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }

        .prediction.top .confidence {
            color: #28a745;
        }

        .confidence-bar {
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            margin-top: 10px;
        }

        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.5s ease;
        }

        .prediction.top .confidence-fill {
            background: linear-gradient(90deg, #28a745 0%, #20c997 100%);
        }

        .class-id {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }

        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }

        .error strong {
            display: block;
            margin-bottom: 10px;
        }

        .meta-info {
            display: flex;
            justify-content: space-between;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            color: #6c757d;
        }

        .footer {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            color: #6c757d;
            font-size: 14px;
        }

        .selected-file {
            margin-top: 15px;
            color: #28a745;
            font-weight: 500;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🖼️ AI Image Classification</h1>
            <p>Powered by TensorFlow MobileNetV2 and PHP 8.4</p>
        </div>

        <div class="content">
            <form method="POST" enctype="multipart/form-data" id="upload-form">
                <div class="upload-section">
                    <label for="image">📸 Choose an image to classify</label>
                    <label for="image" class="file-label">Select Image File</label>
                    <input type="file" name="image" id="image" accept="image/*" required onchange="showFileName(this)">
                    <div class="selected-file" id="selected-file"></div>
                    <button type="submit" id="submit-btn">Classify Image</button>
                </div>
            </form>

            <?php if ($error): ?>
                <div class="error">
                    <strong>⚠️ Error:</strong>
                    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if ($predictions): ?>
                <div class="results">
                    <?php if ($uploadedImageData): ?>
                        <img src="data:image/jpeg;base64,<?= $uploadedImageData ?>"
                            alt="Uploaded image"
                            class="uploaded-image">
                    <?php endif; ?>

                    <h2 class="predictions-title">Classification Results</h2>

                    <?php foreach ($predictions as $i => $pred): ?>
                        <div class="prediction <?= $i === 0 ? 'top' : '' ?>">
                            <div class="prediction-header">
                                <div>
                                    <div class="prediction-label">
                                        <?= $i === 0 ? '🏆 ' : '' ?><?= htmlspecialchars($pred['label'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <div class="class-id">Class ID: <?= $pred['class'] ?></div>
                                </div>
                                <div class="confidence">
                                    <?= round($pred['confidence'] * 100, 1) ?>%
                                </div>
                            </div>
                            <div class="confidence-bar">
                                <div class="confidence-fill" style="width: <?= $pred['confidence'] * 100 ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="meta-info">
                        <div>⏱️ Processing time: <?= round($processingTime * 1000, 2) ?> ms</div>
                        <div>🧠 Model: MobileNetV2 (ImageNet)</div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="footer">
            Deep Learning with TensorFlow and PHP • Chapter 12 Example
        </div>
    </div>

    <script>
        function showFileName(input) {
            const fileDisplay = document.getElementById('selected-file');
            if (input.files && input.files[0]) {
                fileDisplay.textContent = '✓ Selected: ' + input.files[0].name;
            }
        }

        // Disable submit button during upload
        document.getElementById('upload-form').addEventListener('submit', function() {
            const btn = document.getElementById('submit-btn');
            btn.textContent = 'Classifying...';
            btn.disabled = true;
        });
    </script>
</body>

</html>