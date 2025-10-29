<?php

declare(strict_types=1);

/**
 * PHP Image Preprocessor
 * 
 * Pure PHP image preprocessing for neural network input.
 * Useful for cloud APIs (reduce bandwidth) and local models without Python.
 */

/**
 * Image preprocessor using GD or Imagick
 */
final class PHPImagePreprocessor
{
    private string $extension;

    public function __construct()
    {
        // Determine which extension to use
        if (extension_loaded('imagick')) {
            $this->extension = 'imagick';
        } elseif (extension_loaded('gd')) {
            $this->extension = 'gd';
        } else {
            throw new RuntimeException('Neither GD nor Imagick extension is available');
        }
    }

    /**
     * Preprocess image for neural network input
     *
     * @param string $imagePath Path to input image
     * @param int $targetWidth Target width (default 224 for MobileNet)
     * @param int $targetHeight Target height (default 224 for MobileNet)
     * @param bool $normalize Whether to normalize pixel values to [0,1]
     * @return array{width: int, height: int, data: array, format: string}
     */
    public function preprocess(
        string $imagePath,
        int $targetWidth = 224,
        int $targetHeight = 224,
        bool $normalize = false
    ): array {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image not found: {$imagePath}");
        }

        return match ($this->extension) {
            'imagick' => $this->preprocessWithImagick($imagePath, $targetWidth, $targetHeight, $normalize),
            'gd' => $this->preprocessWithGD($imagePath, $targetWidth, $targetHeight, $normalize),
            default => throw new RuntimeException('No image processing extension available')
        };
    }

    /**
     * Resize and save image (useful for reducing API payload size)
     */
    public function resizeAndSave(
        string $inputPath,
        string $outputPath,
        int $maxWidth = 800,
        int $maxHeight = 800,
        int $quality = 85
    ): void {
        if ($this->extension === 'imagick') {
            $this->resizeWithImagick($inputPath, $outputPath, $maxWidth, $maxHeight, $quality);
        } else {
            $this->resizeWithGD($inputPath, $outputPath, $maxWidth, $maxHeight, $quality);
        }
    }

    /**
     * Preprocess using Imagick
     */
    private function preprocessWithImagick(
        string $imagePath,
        int $targetWidth,
        int $targetHeight,
        bool $normalize
    ): array {
        $imagick = new Imagick($imagePath);

        // Convert to RGB if needed
        if ($imagick->getImageColorspace() !== Imagick::COLORSPACE_RGB) {
            $imagick->transformImageColorspace(Imagick::COLORSPACE_RGB);
        }

        // Resize to target dimensions
        $imagick->resizeImage($targetWidth, $targetHeight, Imagick::FILTER_LANCZOS, 1);

        // Extract pixel data
        $pixels = [];
        for ($y = 0; $y < $targetHeight; $y++) {
            for ($x = 0; $x < $targetWidth; $x++) {
                $pixel = $imagick->getImagePixelColor($x, $y);
                $colors = $pixel->getColor();

                // Store as [R, G, B]
                $r = $colors['r'];
                $g = $colors['g'];
                $b = $colors['b'];

                if ($normalize) {
                    $r = $r / 255.0;
                    $g = $g / 255.0;
                    $b = $b / 255.0;
                }

                $pixels[] = [$r, $g, $b];
            }
        }

        $imagick->destroy();

        return [
            'width' => $targetWidth,
            'height' => $targetHeight,
            'data' => $pixels,
            'format' => 'rgb',
        ];
    }

    /**
     * Preprocess using GD
     */
    private function preprocessWithGD(
        string $imagePath,
        int $targetWidth,
        int $targetHeight,
        bool $normalize
    ): array {
        // Create image resource from file
        $imageInfo = getimagesize($imagePath);
        if ($imageInfo === false) {
            throw new RuntimeException("Failed to get image information");
        }

        $sourceImage = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_GIF => imagecreatefromgif($imagePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($imagePath),
            default => throw new RuntimeException("Unsupported image type")
        };

        if ($sourceImage === false) {
            throw new RuntimeException("Failed to load image");
        }

        // Create target image
        $targetImage = imagecreatetruecolor($targetWidth, $targetHeight);
        if ($targetImage === false) {
            imagedestroy($sourceImage);
            throw new RuntimeException("Failed to create target image");
        }

        // Resize
        imagecopyresampled(
            $targetImage,
            $sourceImage,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            imagesx($sourceImage),
            imagesy($sourceImage)
        );

        // Extract pixel data
        $pixels = [];
        for ($y = 0; $y < $targetHeight; $y++) {
            for ($x = 0; $x < $targetWidth; $x++) {
                $rgb = imagecolorat($targetImage, $x, $y);

                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                if ($normalize) {
                    $r = $r / 255.0;
                    $g = $g / 255.0;
                    $b = $b / 255.0;
                }

                $pixels[] = [$r, $g, $b];
            }
        }

        imagedestroy($sourceImage);
        imagedestroy($targetImage);

        return [
            'width' => $targetWidth,
            'height' => $targetHeight,
            'data' => $pixels,
            'format' => 'rgb',
        ];
    }

    /**
     * Resize image with Imagick
     */
    private function resizeWithImagick(
        string $inputPath,
        string $outputPath,
        int $maxWidth,
        int $maxHeight,
        int $quality
    ): void {
        $imagick = new Imagick($inputPath);

        // Calculate new dimensions maintaining aspect ratio
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();

        $ratio = min($maxWidth / $width, $maxHeight / $height);

        if ($ratio < 1) {
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            $imagick->resizeImage($newWidth, $newHeight, Imagick::FILTER_LANCZOS, 1);
        }

        $imagick->setImageCompressionQuality($quality);
        $imagick->writeImage($outputPath);
        $imagick->destroy();
    }

    /**
     * Resize image with GD
     */
    private function resizeWithGD(
        string $inputPath,
        string $outputPath,
        int $maxWidth,
        int $maxHeight,
        int $quality
    ): void {
        $imageInfo = getimagesize($inputPath);
        if ($imageInfo === false) {
            throw new RuntimeException("Failed to get image information");
        }

        $sourceImage = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($inputPath),
            IMAGETYPE_PNG => imagecreatefrompng($inputPath),
            IMAGETYPE_GIF => imagecreatefromgif($inputPath),
            IMAGETYPE_WEBP => imagecreatefromwebp($inputPath),
            default => throw new RuntimeException("Unsupported image type")
        };

        if ($sourceImage === false) {
            throw new RuntimeException("Failed to load image");
        }

        $width = imagesx($sourceImage);
        $height = imagesy($sourceImage);

        // Calculate new dimensions
        $ratio = min($maxWidth / $width, $maxHeight / $height);

        if ($ratio < 1) {
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);

            $targetImage = imagecreatetruecolor($newWidth, $newHeight);
            if ($targetImage === false) {
                imagedestroy($sourceImage);
                throw new RuntimeException("Failed to create target image");
            }

            imagecopyresampled(
                $targetImage,
                $sourceImage,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $width,
                $height
            );

            imagejpeg($targetImage, $outputPath, $quality);
            imagedestroy($targetImage);
        } else {
            imagejpeg($sourceImage, $outputPath, $quality);
        }

        imagedestroy($sourceImage);
    }

    /**
     * Get image information
     */
    public function getImageInfo(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new InvalidArgumentException("Image not found: {$imagePath}");
        }

        $info = getimagesize($imagePath);
        if ($info === false) {
            throw new RuntimeException("Failed to get image information");
        }

        return [
            'width' => $info[0],
            'height' => $info[1],
            'type' => image_type_to_mime_type($info[2]),
            'size_bytes' => filesize($imagePath),
            'size_kb' => round(filesize($imagePath) / 1024, 2),
        ];
    }

    /**
     * Get which extension is being used
     */
    public function getExtension(): string
    {
        return $this->extension;
    }
}

// Example usage
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'] ?? '')) {
    echo "PHP Image Preprocessor Demo\n";
    echo str_repeat('=', 60) . "\n\n";

    $preprocessor = new PHPImagePreprocessor();
    echo "Using: " . $preprocessor->getExtension() . "\n\n";

    $imagePath = __DIR__ . '/data/sample_images/cat.jpg';

    if (!file_exists($imagePath)) {
        die("Sample image not found: {$imagePath}\n");
    }

    // Get image info
    $info = $preprocessor->getImageInfo($imagePath);
    echo "Original Image:\n";
    echo "  Dimensions: {$info['width']}×{$info['height']}\n";
    echo "  Type: {$info['type']}\n";
    echo "  Size: {$info['size_kb']} KB\n\n";

    // Preprocess for neural network
    echo "Preprocessing for neural network (224×224)...\n";
    $startTime = microtime(true);
    $processed = $preprocessor->preprocess($imagePath, 224, 224, normalize: true);
    $duration = microtime(true) - $startTime;

    echo "  Processed dimensions: {$processed['width']}×{$processed['height']}\n";
    echo "  Pixel count: " . count($processed['data']) . "\n";
    echo "  Format: {$processed['format']}\n";
    echo "  Processing time: " . round($duration * 1000) . "ms\n\n";

    // Show sample pixel values
    echo "Sample pixel values (first 3 pixels):\n";
    foreach (array_slice($processed['data'], 0, 3) as $i => $pixel) {
        printf("  Pixel %d: R=%.3f, G=%.3f, B=%.3f\n", $i, $pixel[0], $pixel[1], $pixel[2]);
    }

    // Resize and save example
    $resizedPath = '/tmp/cat_resized.jpg';
    echo "\nResizing image to max 400×400...\n";
    $preprocessor->resizeAndSave($imagePath, $resizedPath, 400, 400, quality: 85);

    $resizedInfo = $preprocessor->getImageInfo($resizedPath);
    echo "  Resized: {$resizedInfo['width']}×{$resizedInfo['height']}\n";
    echo "  Size: {$resizedInfo['size_kb']} KB (saved " .
        round(($info['size_kb'] - $resizedInfo['size_kb']) / $info['size_kb'] * 100, 1) .
        "% bandwidth)\n";
    echo "  Saved to: {$resizedPath}\n";
}
