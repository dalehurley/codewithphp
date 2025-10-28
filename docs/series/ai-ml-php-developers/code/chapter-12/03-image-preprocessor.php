<?php

declare(strict_types=1);

/**
 * Image preprocessor for deep learning models.
 * 
 * Handles loading, resizing, normalizing, and converting images
 * to the format expected by TensorFlow models.
 */
final class ImagePreprocessor
{
    public function __construct(
        private int $targetWidth = 224,
        private int $targetHeight = 224,
    ) {}

    /**
     * Load and preprocess an image file.
     *
     * @param string $imagePath Path to image file
     * @return array<array<float>> Preprocessed image as flat array of [R,G,B] pixels
     * @throws RuntimeException If image cannot be loaded or processed
     */
    public function preprocessImage(string $imagePath): array
    {
        // Validate file exists
        if (!file_exists($imagePath)) {
            throw new RuntimeException("Image file not found: $imagePath");
        }

        // Validate it's actually an image
        $info = getimagesize($imagePath);
        if ($info === false) {
            throw new RuntimeException("Invalid or corrupted image file: $imagePath");
        }

        // Load image based on MIME type
        $image = match ($info['mime']) {
            'image/jpeg' => imagecreatefromjpeg($imagePath),
            'image/png' => imagecreatefrompng($imagePath),
            'image/gif' => imagecreatefromgif($imagePath),
            'image/webp' => imagecreatefromwebp($imagePath),
            default => throw new RuntimeException("Unsupported image format: {$info['mime']}"),
        };

        if ($image === false) {
            throw new RuntimeException("Failed to load image: $imagePath");
        }

        // Resize to target dimensions
        $resized = imagescale($image, $this->targetWidth, $this->targetHeight);
        imagedestroy($image);

        if ($resized === false) {
            throw new RuntimeException("Failed to resize image");
        }

        // Convert to normalized pixel array
        $pixels = $this->imageToArray($resized);
        imagedestroy($resized);

        return $pixels;
    }

    /**
     * Convert GD image resource to normalized pixel array.
     *
     * @param \GdImage $image GD image resource
     * @return array<array<float>> Flat array of [R,G,B] pixels, normalized to 0-1
     */
    private function imageToArray(\GdImage $image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $pixels = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);

                // Extract RGB components and normalize to 0-1 range
                // MobileNetV2 expects values in [0, 1]
                $pixels[] = [
                    (($rgb >> 16) & 0xFF) / 255.0,  // Red channel
                    (($rgb >> 8) & 0xFF) / 255.0,   // Green channel
                    ($rgb & 0xFF) / 255.0            // Blue channel
                ];
            }
        }

        return $pixels;
    }

    /**
     * Preprocess multiple images in batch.
     *
     * @param array<string> $imagePaths Array of image file paths
     * @return array<array<array<float>>> Array of preprocessed images
     */
    public function preprocessBatch(array $imagePaths): array
    {
        $batch = [];
        $errors = [];

        foreach ($imagePaths as $path) {
            try {
                $batch[] = $this->preprocessImage($path);
            } catch (RuntimeException $e) {
                $errors[] = "Failed to process $path: " . $e->getMessage();
            }
        }

        if (!empty($errors) && empty($batch)) {
            throw new RuntimeException(
                "All images failed to preprocess:\n" . implode("\n", $errors)
            );
        }

        return $batch;
    }

    /**
     * Get image dimensions before preprocessing.
     *
     * @param string $imagePath Path to image file
     * @return array{width: int, height: int, type: string}
     */
    public function getImageInfo(string $imagePath): array
    {
        $info = getimagesize($imagePath);
        if ($info === false) {
            throw new RuntimeException("Cannot read image info: $imagePath");
        }

        return [
            'width' => $info[0],
            'height' => $info[1],
            'type' => $info['mime'],
        ];
    }
}

// Example usage
if (PHP_SAPI === 'cli') {
    echo "Image Preprocessor Test\n";
    echo "========================\n\n";

    $preprocessor = new ImagePreprocessor();

    // Create a test image if none exists
    $testImagePath = '/tmp/test_preprocessor.jpg';
    if (!file_exists($testImagePath)) {
        echo "Creating test image...\n";
        $img = imagecreatetruecolor(400, 300);

        // Create a gradient from blue to yellow
        for ($x = 0; $x < 400; $x++) {
            for ($y = 0; $y < 300; $y++) {
                $r = (int) ($x / 400 * 255);
                $g = (int) ($y / 300 * 255);
                $b = 255 - (int) ($x / 400 * 255);
                $color = imagecolorallocate($img, $r, $g, $b);
                imagesetpixel($img, $x, $y, $color);
            }
        }

        imagejpeg($img, $testImagePath, 90);
        imagedestroy($img);
        echo "✓ Test image created\n\n";
    }

    try {
        // Get original image info
        echo "Original image info:\n";
        $info = $preprocessor->getImageInfo($testImagePath);
        echo "  Dimensions: {$info['width']}x{$info['height']}\n";
        echo "  Type: {$info['type']}\n\n";

        // Preprocess image
        echo "Preprocessing image...\n";
        $startTime = microtime(true);
        $pixels = $preprocessor->preprocessImage($testImagePath);
        $duration = microtime(true) - $startTime;

        $totalPixels = count($pixels);
        $expectedPixels = 224 * 224;

        echo "✓ Image preprocessed successfully\n";
        echo "  Total pixels: $totalPixels (expected: $expectedPixels)\n";
        echo "  Channels per pixel: " . count($pixels[0]) . " (RGB)\n";
        echo "  Processing time: " . round($duration * 1000, 2) . " ms\n\n";

        // Sample some pixel values
        echo "Sample pixel values:\n";
        echo "  First pixel: [" .
            round($pixels[0][0], 3) . ", " .
            round($pixels[0][1], 3) . ", " .
            round($pixels[0][2], 3) . "]\n";
        echo "  Middle pixel: [" .
            round($pixels[28000][0], 3) . ", " .
            round($pixels[28000][1], 3) . ", " .
            round($pixels[28000][2], 3) . "]\n";
        echo "  Last pixel: [" .
            round($pixels[$totalPixels - 1][0], 3) . ", " .
            round($pixels[$totalPixels - 1][1], 3) . ", " .
            round($pixels[$totalPixels - 1][2], 3) . "]\n\n";

        // Verify normalization
        $allPixels = array_merge(...$pixels);
        $min = min($allPixels);
        $max = max($allPixels);

        echo "Value range check:\n";
        echo "  Min value: $min (should be >= 0.0)\n";
        echo "  Max value: $max (should be <= 1.0)\n";

        if ($min >= 0.0 && $max <= 1.0) {
            echo "  ✓ Normalization correct\n";
        } else {
            echo "  ✗ Normalization error!\n";
        }
    } catch (Exception $e) {
        echo "✗ Error: " . $e->getMessage() . "\n";
        exit(1);
    }
}
