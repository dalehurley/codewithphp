<?php

declare(strict_types=1);

/**
 * Secure Web Interface for Image Classification
 * 
 * Production-ready upload form with:
 * - File size validation
 * - MIME type checking
 * - Security headers
 * - XSS protection
 * - Both cloud and local classification options
 */

require_once __DIR__ . '/02-cloud-vision-client.php';
require_once __DIR__ . '/05-onnx-classifier.php';
require_once __DIR__ . '/10-php-image-preprocessor.php';
require_once __DIR__ . '/.env.php';

// Security configuration
const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
const ALLOWED_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
const UPLOAD_DIR = '/tmp/image-uploads/';

// Initialize variables
$results = null;
$error = null;
$uploadedImagePath = null;
$processingTime = 0;
$classifierUsed = null;

// Create upload directory if needed
if (!is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    try {
        // Validate CSRF token (simple implementation)
        session_start();
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            throw new RuntimeException('Invalid security token. Please refresh and try again.');
        }

        // Validate upload
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException(match ($_FILES['image']['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File too large (max 10MB)',
                UPLOAD_ERR_PARTIAL => 'File upload was interrupted',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                default => 'Upload failed with error code: ' . $_FILES['image']['error']
            });
        }

        // Validate file size
        if ($_FILES['image']['size'] > MAX_FILE_SIZE) {
            throw new RuntimeException('File too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB');
        }

        // Validate MIME type using finfo (more secure than checking extension)
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $_FILES['image']['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, ALLOWED_MIME_TYPES, true)) {
            throw new RuntimeException(
                'Invalid file type: ' . htmlspecialchars($mimeType) . '. ' .
                    'Allowed types: JPEG, PNG, GIF, WebP'
            );
        }

        // Generate secure filename
        $extension = match ($mimeType) {
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'image/webp' => '.webp',
            default => ''
        };

        $safeFilename = 'upload_' . bin2hex(random_bytes(16)) . '_' . time() . $extension;
        $uploadedImagePath = UPLOAD_DIR . $safeFilename;

        // Move uploaded file
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $uploadedImagePath)) {
            throw new RuntimeException('Failed to save uploaded file');
        }

        // Determine which classifier to use
        $classifierChoice = $_POST['classifier'] ?? 'auto';

        // Initialize classifiers
        $cloudClient = null;
        $localClassifier = null;

        if (!empty($_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '')) {
            $cloudClient = new CloudVisionClient(
                apiKey: $_ENV['GOOGLE_CLOUD_VISION_API_KEY'],
                maxResults: 5
            );
        }

        if (file_exists(__DIR__ . '/models/mobilenetv2-7.onnx')) {
            $localClassifier = new ONNXClassifier(
                modelPath: __DIR__ . '/models/mobilenetv2-7.onnx',
                labelsPath: __DIR__ . '/data/imagenet_labels.json',
                pythonScript: __DIR__ . '/onnx_inference.py',
                maxResults: 5
            );
        }

        // Classify based on user choice
        $startTime = microtime(true);

        if ($classifierChoice === 'cloud' && $cloudClient !== null) {
            $results = $cloudClient->classifyImage($uploadedImagePath);
            $classifierUsed = 'Google Cloud Vision API';
        } elseif ($classifierChoice === 'local' && $localClassifier !== null) {
            $results = $localClassifier->classifyImage($uploadedImagePath);
            $classifierUsed = 'Local ONNX (MobileNetV2)';
        } elseif ($classifierChoice === 'auto') {
            // Try local first, fallback to cloud
            if ($localClassifier !== null) {
                $results = $localClassifier->classifyImage($uploadedImagePath);
                $classifierUsed = 'Local ONNX (MobileNetV2)';
            } elseif ($cloudClient !== null) {
                $results = $cloudClient->classifyImage($uploadedImagePath);
                $classifierUsed = 'Google Cloud Vision API';
            } else {
                throw new RuntimeException('No classifiers available. Configure API key or ONNX model.');
            }
        } else {
            throw new RuntimeException('Selected classifier not available');
        }

        $processingTime = microtime(true) - $startTime;
    } catch (Exception $e) {
        $error = $e->getMessage();

        // Clean up uploaded file on error
        if ($uploadedImagePath && file_exists($uploadedImagePath)) {
            unlink($uploadedImagePath);
            $uploadedImagePath = null;
        }
    }
}

// Generate CSRF token
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    <meta name="referrer" content="no-referrer">
    <title>Image Classification - AI/ML for PHP Developers</title>
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
            font-size: 28px;
            margin-bottom: 10px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .content {
            padding: 30px;
        }

        .upload-section {
            border: 3px dashed #ddd;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            background: #f9f9f9;
        }

        .upload-section:hover {
            border-color: #667eea;
            background: #f0f0ff;
        }

        .upload-section.dragover {
            border-color: #764ba2;
            background: #f0e8ff;
        }

        .upload-icon {
            font-size: 48px;
            margin-bottom: 20px;
            color: #667eea;
        }

        .file-input {
            display: none;
        }

        .classifier-select {
            margin: 20px 0;
            padding: 15px;
            background: #f5f5f5;
            border-radius: 8px;
        }

        .classifier-select label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }

        .classifier-select select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            background: white;
            cursor: pointer;
        }

        .btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .error-message {
            background: #ffe0e0;
            border-left: 4px solid #ff4444;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            color: #cc0000;
        }

        .results-section {
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 12px;
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ddd;
        }

        .results-header h2 {
            font-size: 20px;
            color: #333;
        }

        .meta-info {
            font-size: 12px;
            color: #666;
        }

        .image-preview {
            max-width: 100%;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .prediction-item {
            background: white;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .prediction-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }

        .prediction-label {
            font-weight: 600;
            font-size: 16px;
            color: #333;
        }

        .prediction-confidence {
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
        }

        .confidence-bar {
            height: 6px;
            background: #e0e0e0;
            border-radius: 3px;
            overflow: hidden;
        }

        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            transition: width 0.5s ease;
        }

        .rank-badge {
            display: inline-block;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 50%;
            font-size: 12px;
            font-weight: 600;
            margin-right: 8px;
        }

        .rank-1 {
            background: #ffd700;
            color: #333;
        }

        .rank-2 {
            background: #c0c0c0;
            color: #333;
        }

        .rank-3 {
            background: #cd7f32;
            color: white;
        }

        .rank-other {
            background: #e0e0e0;
            color: #666;
        }

        .security-note {
            margin-top: 20px;
            padding: 15px;
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            border-radius: 6px;
            font-size: 13px;
            color: #2e7d32;
        }

        .security-note strong {
            display: block;
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <h1>🖼️ Image Classification Demo</h1>
            <p>Chapter 17: Image Classification with Pre-trained Models</p>
        </div>

        <div class="content">
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <div class="upload-section" id="uploadArea">
                    <div class="upload-icon">📸</div>
                    <h3>Upload an Image</h3>
                    <p>Click to browse or drag and drop an image here</p>
                    <p style="font-size: 12px; color: #999; margin-top: 10px;">
                        Supported: JPEG, PNG, GIF, WebP • Max 10MB
                    </p>
                    <input type="file" name="image" id="imageInput" class="file-input" accept="image/jpeg,image/png,image/gif,image/webp" required>
                </div>

                <div class="classifier-select">
                    <label for="classifier">Choose Classifier:</label>
                    <select name="classifier" id="classifier">
                        <option value="auto">Auto (Local first, then Cloud)</option>
                        <?php if (file_exists(__DIR__ . '/models/mobilenetv2-7.onnx')): ?>
                            <option value="local">Local ONNX (Free, Fast)</option>
                        <?php endif; ?>
                        <?php if (!empty($_ENV['GOOGLE_CLOUD_VISION_API_KEY'] ?? '')): ?>
                            <option value="cloud">Google Cloud Vision (More Categories)</option>
                        <?php endif; ?>
                    </select>
                </div>

                <button type="submit" class="btn" id="submitBtn" disabled>Classify Image</button>
            </form>

            <?php if ($error): ?>
                <div class="error-message">
                    <strong>❌ Error:</strong> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($results && $uploadedImagePath): ?>
                <div class="results-section">
                    <div class="results-header">
                        <h2>Classification Results</h2>
                        <div class="meta-info">
                            <div>Classifier: <strong><?= htmlspecialchars($classifierUsed) ?></strong></div>
                            <div>Processing: <strong><?= round($processingTime * 1000) ?>ms</strong></div>
                        </div>
                    </div>

                    <img src="data:image/jpeg;base64,<?= base64_encode(file_get_contents($uploadedImagePath)) ?>"
                        alt="Uploaded image" class="image-preview">

                    <?php foreach ($results as $index => $result): ?>
                        <div class="prediction-item">
                            <div class="prediction-header">
                                <div>
                                    <span class="rank-badge rank-<?= $index < 3 ? $index + 1 : 'other' ?>"><?= $index + 1 ?></span>
                                    <span class="prediction-label"><?= htmlspecialchars($result['label']) ?></span>
                                </div>
                                <span class="prediction-confidence"><?= round($result['confidence'] * 100, 1) ?>%</span>
                            </div>
                            <div class="confidence-bar">
                                <div class="confidence-fill" style="width: <?= $result['confidence'] * 100 ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="margin-top: 20px; text-align: center;">
                        <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" class="btn">Classify Another Image</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="security-note">
                <strong>🔒 Security Features:</strong>
                • CSRF protection • File size validation • MIME type verification
                • Secure filename generation • XSS prevention
            </div>
        </div>
    </div>

    <script>
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('imageInput');
        const submitBtn = document.getElementById('submitBtn');

        // Click to upload
        uploadArea.addEventListener('click', () => fileInput.click());

        // Enable submit when file selected
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                submitBtn.disabled = false;
                uploadArea.querySelector('h3').textContent = '✓ ' + e.target.files[0].name;
            }
        });

        // Drag and drop
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.classList.remove('dragover');
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');

            if (e.dataTransfer.files.length > 0) {
                fileInput.files = e.dataTransfer.files;
                submitBtn.disabled = false;
                uploadArea.querySelector('h3').textContent = '✓ ' + e.dataTransfer.files[0].name;
            }
        });

        // Prevent default drag behavior on document
        document.addEventListener('dragover', (e) => e.preventDefault());
        document.addEventListener('drop', (e) => e.preventDefault());
    </script>
</body>

</html>