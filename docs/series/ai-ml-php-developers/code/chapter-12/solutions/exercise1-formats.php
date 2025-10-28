<?php

declare(strict_types=1);

/**
 * Exercise 1 Solution: Extended Image Format Support
 * 
 * Adds support for additional formats, validation, and metadata extraction.
 */

final class ExtendedImagePreprocessor
{
    public function __construct(
        private int $targetWidth = 224,
        private int $targetHeight = 224,
        private int $minDimension = 10,
        private int $maxDimension = 5000,
    ) {}

    /**
     * Preprocess image with extensive validation and metadata.
     *
     * @param string $imagePath Path to image file
     * @return array{pixels: array, metadata: array} Preprocessed data and metadata
     */
    public function preprocessWithMetadata(string $imagePath): array
    {
        // Validate file exists
        if (!file_exists($imagePath)) {
            throw new RuntimeException("Image file not found: $imagePath");
        }

        // Get image info
        $info = getimagesize($imagePath);
        if ($info === false) {
            throw new RuntimeException("Invalid or corrupted image: $imagePath");
        }

        [$originalWidth, $originalHeight, $type, $attr] = $info;
        $mimeType = $info['mime'];

        // Validate dimensions
        if ($originalWidth < $this->minDimension || $originalHeight < $this->minDimension) {
            throw new RuntimeException(
                "Image too small: {$originalWidth}x{$originalHeight} " .
                    "(minimum: {$this->minDimension}px)"
            );
        }

        if ($originalWidth > $this->maxDimension || $originalHeight > $this->maxDimension) {
            throw new RuntimeException(
                "Image too large: {$originalWidth}x{$originalHeight} " .
                    "(maximum: {$this->maxDimension}px)"
            );
        }

        // Load image based on format
        $image = $this->loadImage($imagePath, $mimeType);

        // For animated GIFs, extract first frame only
        if ($type === IMAGETYPE_GIF && $this->isAnimatedGif($imagePath)) {
            // imagecreatefromgif already loads only the first frame
            // No special handling needed
        }

        // Resize
        $resized = imagescale($image, $this->targetWidth, $this->targetHeight);
        imagedestroy($image);

        if ($resized === false) {
            throw new RuntimeException("Failed to resize image");
        }

        // Convert to pixel array
        $pixels = $this->imageToArray($resized);
        imagedestroy($resized);

        // Collect metadata
        $metadata = [
            'original_width' => $originalWidth,
            'original_height' => $originalHeight,
            'format' => image_type_to_extension($type, false),
            'mime_type' => $mimeType,
            'file_size' => filesize($imagePath),
            'target_width' => $this->targetWidth,
            'target_height' => $this->targetHeight,
            'pixel_count' => count($pixels),
        ];

        return [
            'pixels' => $pixels,
            'metadata' => $metadata,
        ];
    }

    /**
     * Load image from file, supporting multiple formats.
     */
    private function loadImage(string $imagePath, string $mimeType): \GdImage
    {
        $image = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($imagePath),
            'image/png' => imagecreatefrompng($imagePath),
            'image/gif' => imagecreatefromgif($imagePath),
            'image/webp' => imagecreatefromwebp($imagePath),
            'image/bmp', 'image/x-ms-bmp' => $this->loadBmp($imagePath),
            default => throw new RuntimeException("Unsupported format: $mimeType"),
        };

        if ($image === false) {
            throw new RuntimeException("Failed to load image: $imagePath");
        }

        return $image;
    }

    /**
     * Load BMP images (requires Imagick or manual parsing).
     */
    private function loadBmp(string $imagePath): \GdImage
    {
        // Try using imagecreatefrombmp (PHP 7.2+)
        if (function_exists('imagecreatefrombmp')) {
            return imagecreatefrombmp($imagePath);
        }

        // Fallback: use Imagick if available
        if (extension_loaded('imagick')) {
            $imagick = new \Imagick($imagePath);
            $imagick->setImageFormat('png');
            $tempPng = tempnam(sys_get_temp_dir(), 'bmp_') . '.png';
            $imagick->writeImage($tempPng);
            $image = imagecreatefrompng($tempPng);
            unlink($tempPng);
            return $image;
        }

        throw new RuntimeException('BMP support requires PHP 7.2+ or Imagick extension');
    }

    /**
     * Check if GIF is animated.
     */
    private function isAnimatedGif(string $imagePath): bool
    {
        $fileContents = file_get_contents($imagePath);

        // Count occurrences of GIF image separator (0x21 0xF9)
        $imageCount = preg_match_all('/\x00\x21\xF9/', $fileContents);

        return $imageCount > 1;
    }

    /**
     * Convert GD image to normalized pixel array.
     */
    private function imageToArray(\GdImage $image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $pixels = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $pixels[] = [
                    (($rgb >> 16) & 0xFF) / 255.0,
                    (($rgb >> 8) & 0xFF) / 255.0,
                    ($rgb & 0xFF) / 255.0
                ];
            }
        }

        return $pixels;
    }
}

// Test the extended preprocessor
if (PHP_SAPI === 'cli') {
    echo "Exercise 1: Extended Format Support\n";
    echo "====================================\n\n";

    $preprocessor = new ExtendedImagePreprocessor();

    // Create test images in different formats
    $testImages = [];

    // JPEG
    $jpegPath = '/tmp/test_extended.jpg';
    $img = imagecreatetruecolor(300, 200);
    $color = imagecolorallocate($img, 100, 150, 200);
    imagefill($img, 0, 0, $color);
    imagejpeg($img, $jpegPath, 90);
    imagedestroy($img);
    $testImages[] = ['JPEG', $jpegPath];

    // PNG
    $pngPath = '/tmp/test_extended.png';
    $img = imagecreatetruecolor(400, 300);
    $color = imagecolorallocate($img, 200, 100, 50);
    imagefill($img, 0, 0, $color);
    imagepng($img, $pngPath);
    imagedestroy($img);
    $testImages[] = ['PNG', $pngPath];

    // WebP (if supported)
    if (function_exists('imagewebp')) {
        $webpPath = '/tmp/test_extended.webp';
        $img = imagecreatetruecolor(350, 250);
        $color = imagecolorallocate($img, 50, 200, 100);
        imagefill($img, 0, 0, $color);
        imagewebp($img, $webpPath);
        imagedestroy($img);
        $testImages[] = ['WebP', $webpPath];
    }

    foreach ($testImages as [$format, $path]) {
        try {
            echo "Processing $format image...\n";
            $result = $preprocessor->preprocessWithMetadata($path);

            $meta = $result['metadata'];
            echo "✓ Success!\n";
            echo "  Original: {$meta['original_width']}x{$meta['original_height']} ";
            echo strtoupper($meta['format']) . " ";
            echo "(" . round($meta['file_size'] / 1024, 2) . " KB)\n";
            echo "  Preprocessed: {$meta['target_width']}x{$meta['target_height']} ";
            echo "array with {$meta['pixel_count']} pixels\n";
            echo "  Format: Successfully converted {$meta['format']} → TensorFlow format\n\n";
        } catch (Exception $e) {
            echo "✗ Error: " . $e->getMessage() . "\n\n";
        }
    }

    // Test dimension validation
    echo "Testing dimension validation...\n\n";

    // Too small
    $tinyPath = '/tmp/test_tiny.jpg';
    $img = imagecreatetruecolor(5, 5);
    imagejpeg($img, $tinyPath);
    imagedestroy($img);

    try {
        $preprocessor->preprocessWithMetadata($tinyPath);
        echo "✗ Should have rejected tiny image\n";
    } catch (RuntimeException $e) {
        echo "✓ Correctly rejected tiny image: " . $e->getMessage() . "\n";
    }

    echo "\n✓ All format tests completed!\n";
}
