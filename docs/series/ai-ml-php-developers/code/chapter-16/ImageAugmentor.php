<?php

declare(strict_types=1);

/**
 * ImageAugmentor - Generate training variations through augmentation
 * 
 * This class provides data augmentation techniques to expand limited training
 * datasets by creating realistic variations of existing images.
 */
final class ImageAugmentor
{
    public function __construct(
        private ImageProcessor $processor,
        private ColorConverter $converter,
    ) {}

    /**
     * Generate multiple augmented variations of an image
     * 
     * @param \GdImage $image The source image
     * @param int $count Number of variations to generate
     * @param array<string, mixed> $config Augmentation configuration
     * @return array<\GdImage> Array of augmented images
     */
    public function augment(\GdImage $image, int $count = 5, array $config = []): array
    {
        $config = array_merge($this->getDefaultConfig(), $config);
        $augmented = [];

        for ($i = 0; $i < $count; $i++) {
            $variant = $this->cloneImage($image);

            // Apply random augmentations based on config
            if ($config['flip'] && rand(0, 1)) {
                $variant = $this->randomFlip($variant);
            }

            if ($config['rotate'] && rand(0, 100) < $config['rotate_probability']) {
                $variant = $this->randomRotation($variant, $config['rotation_angles']);
            }

            if ($config['brightness'] && rand(0, 100) < $config['brightness_probability']) {
                $variant = $this->randomBrightness($variant, $config['brightness_range']);
            }

            if ($config['contrast'] && rand(0, 100) < $config['contrast_probability']) {
                $variant = $this->randomContrast($variant, $config['contrast_range']);
            }

            if ($config['crop'] && rand(0, 100) < $config['crop_probability']) {
                $variant = $this->randomCrop($variant, $config['crop_scale_range']);
            }

            if ($config['zoom'] && rand(0, 100) < $config['zoom_probability']) {
                $variant = $this->randomZoom($variant, $config['zoom_range']);
            }

            $augmented[] = $variant;
        }

        return $augmented;
    }

    /**
     * Apply horizontal or vertical flip randomly
     */
    private function randomFlip(\GdImage $image): \GdImage
    {
        $type = rand(0, 2); // 0=horizontal, 1=vertical, 2=both

        if ($type === 0 || $type === 2) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        if ($type === 1 || $type === 2) {
            imageflip($image, IMG_FLIP_VERTICAL);
        }

        return $image;
    }

    /**
     * Apply random rotation from specified angles
     * 
     * @param array<int> $angles Allowed rotation angles
     */
    private function randomRotation(\GdImage $image, array $angles): \GdImage
    {
        $angle = $angles[array_rand($angles)];

        if ($angle === 0) {
            return $image;
        }

        $rotated = imagerotate($image, $angle, 0);

        if ($rotated === false) {
            return $image;
        }

        imagedestroy($image);
        return $rotated;
    }

    /**
     * Apply random brightness adjustment
     * 
     * @param array{min: int, max: int} $range Brightness range (-255 to 255)
     */
    private function randomBrightness(\GdImage $image, array $range): \GdImage
    {
        $level = rand($range['min'], $range['max']);
        imagefilter($image, IMG_FILTER_BRIGHTNESS, $level);
        return $image;
    }

    /**
     * Apply random contrast adjustment
     * 
     * @param array{min: int, max: int} $range Contrast range (-100 to 100)
     */
    private function randomContrast(\GdImage $image, array $range): \GdImage
    {
        $level = rand($range['min'], $range['max']);
        imagefilter($image, IMG_FILTER_CONTRAST, -$level); // Note: negative for GD
        return $image;
    }

    /**
     * Apply random crop and resize back to original dimensions
     * 
     * @param array{min: float, max: float} $scaleRange Crop scale range (0.8 = 80% of image)
     */
    private function randomCrop(\GdImage $image, array $scaleRange): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $scale = $scaleRange['min'] +
            (mt_rand() / mt_getrandmax()) * ($scaleRange['max'] - $scaleRange['min']);

        $cropWidth = (int)($width * $scale);
        $cropHeight = (int)($height * $scale);

        $maxX = $width - $cropWidth;
        $maxY = $height - $cropHeight;

        $x = $maxX > 0 ? rand(0, $maxX) : 0;
        $y = $maxY > 0 ? rand(0, $maxY) : 0;

        $cropped = imagecreatetruecolor($cropWidth, $cropHeight);
        imagecopy($cropped, $image, 0, 0, $x, $y, $cropWidth, $cropHeight);

        // Resize back to original dimensions
        $resized = imagecreatetruecolor($width, $height);
        imagecopyresampled(
            $resized,
            $cropped,
            0,
            0,
            0,
            0,
            $width,
            $height,
            $cropWidth,
            $cropHeight
        );

        imagedestroy($cropped);
        imagedestroy($image);

        return $resized;
    }

    /**
     * Apply random zoom (scale and crop center or scale with padding)
     * 
     * @param array{min: float, max: float} $zoomRange Zoom range (1.1 = 110%)
     */
    private function randomZoom(\GdImage $image, array $zoomRange): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $zoom = $zoomRange['min'] +
            (mt_rand() / mt_getrandmax()) * ($zoomRange['max'] - $zoomRange['min']);

        if ($zoom > 1.0) {
            // Zoom in: scale up and crop center
            $newWidth = (int)($width * $zoom);
            $newHeight = (int)($height * $zoom);

            $scaled = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled(
                $scaled,
                $image,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $width,
                $height
            );

            // Crop center to original size
            $x = (int)(($newWidth - $width) / 2);
            $y = (int)(($newHeight - $height) / 2);

            $result = imagecreatetruecolor($width, $height);
            imagecopy($result, $scaled, 0, 0, $x, $y, $width, $height);

            imagedestroy($scaled);
            imagedestroy($image);

            return $result;
        } else {
            // Zoom out: scale down
            $newWidth = (int)($width * $zoom);
            $newHeight = (int)($height * $zoom);

            $result = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled(
                $result,
                $image,
                0,
                0,
                0,
                0,
                $newWidth,
                $newHeight,
                $width,
                $height
            );

            imagedestroy($image);
            return $result;
        }
    }

    /**
     * Clone an image resource
     */
    private function cloneImage(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $clone = imagecreatetruecolor($width, $height);

        // Preserve transparency
        imagealphablending($clone, false);
        imagesavealpha($clone, true);

        imagecopy($clone, $image, 0, 0, 0, 0, $width, $height);

        return $clone;
    }

    /**
     * Get default augmentation configuration
     * 
     * @return array<string, mixed>
     */
    private function getDefaultConfig(): array
    {
        return [
            'flip' => true,
            'rotate' => true,
            'rotation_angles' => [0, 90, 180, 270],
            'rotate_probability' => 50,
            'brightness' => true,
            'brightness_range' => ['min' => -30, 'max' => 30],
            'brightness_probability' => 50,
            'contrast' => true,
            'contrast_range' => ['min' => -20, 'max' => 20],
            'contrast_probability' => 50,
            'crop' => true,
            'crop_scale_range' => ['min' => 0.8, 'max' => 1.0],
            'crop_probability' => 50,
            'zoom' => true,
            'zoom_range' => ['min' => 0.9, 'max' => 1.1],
            'zoom_probability' => 30,
        ];
    }

    /**
     * Generate a fixed set of augmentations (for reproducibility)
     * 
     * @param \GdImage $image Source image
     * @return array<string, \GdImage> Named augmentations
     */
    public function generateStandardSet(\GdImage $image): array
    {
        $set = [];

        // Original
        $set['original'] = $this->cloneImage($image);

        // Flips
        $flippedH = $this->cloneImage($image);
        imageflip($flippedH, IMG_FLIP_HORIZONTAL);
        $set['flip_horizontal'] = $flippedH;

        $flippedV = $this->cloneImage($image);
        imageflip($flippedV, IMG_FLIP_VERTICAL);
        $set['flip_vertical'] = $flippedV;

        // Rotations
        $rotated90 = imagerotate($this->cloneImage($image), 90, 0);
        if ($rotated90) $set['rotate_90'] = $rotated90;

        $rotated180 = imagerotate($this->cloneImage($image), 180, 0);
        if ($rotated180) $set['rotate_180'] = $rotated180;

        // Brightness variations
        $brighter = $this->cloneImage($image);
        imagefilter($brighter, IMG_FILTER_BRIGHTNESS, 30);
        $set['brightness_+30'] = $brighter;

        $darker = $this->cloneImage($image);
        imagefilter($darker, IMG_FILTER_BRIGHTNESS, -30);
        $set['brightness_-30'] = $darker;

        // Contrast variations
        $highContrast = $this->cloneImage($image);
        imagefilter($highContrast, IMG_FILTER_CONTRAST, -20);
        $set['contrast_high'] = $highContrast;

        return $set;
    }
}
