<?php

declare(strict_types=1);

/**
 * ImageFilter - Apply filters and effects to images
 * 
 * This class provides various image filtering operations including
 * blur, sharpen, edge detection, and other artistic effects.
 */
final class ImageFilter
{
    /**
     * Apply Gaussian blur to an image
     * 
     * @param \GdImage $image The source image
     * @param int $passes Number of blur passes (higher = more blur)
     * @return \GdImage The blurred image
     */
    public function blur(\GdImage $image, int $passes = 1): \GdImage
    {
        for ($i = 0; $i < $passes; $i++) {
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);
        }
        return $image;
    }

    /**
     * Apply selective blur to an image
     * 
     * @param \GdImage $image The source image
     * @param int $passes Number of blur passes
     * @return \GdImage The blurred image
     */
    public function selectiveBlur(\GdImage $image, int $passes = 1): \GdImage
    {
        for ($i = 0; $i < $passes; $i++) {
            imagefilter($image, IMG_FILTER_SELECTIVE_BLUR);
        }
        return $image;
    }

    /**
     * Sharpen an image
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The sharpened image
     */
    public function sharpen(\GdImage $image): \GdImage
    {
        imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
        return $image;
    }

    /**
     * Apply edge detection to an image
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The edge-detected image
     */
    public function edgeDetect(\GdImage $image): \GdImage
    {
        imagefilter($image, IMG_FILTER_EDGEDETECT);
        return $image;
    }

    /**
     * Emboss an image
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The embossed image
     */
    public function emboss(\GdImage $image): \GdImage
    {
        imagefilter($image, IMG_FILTER_EMBOSS);
        return $image;
    }

    /**
     * Apply a smooth filter to an image
     * 
     * @param \GdImage $image The source image
     * @param float $level Smoothness level (higher = smoother)
     * @return \GdImage The smoothed image
     */
    public function smooth(\GdImage $image, float $level = 5.0): \GdImage
    {
        imagefilter($image, IMG_FILTER_SMOOTH, (int)$level);
        return $image;
    }

    /**
     * Apply a pixelation effect
     * 
     * @param \GdImage $image The source image
     * @param int $blockSize Size of pixel blocks
     * @return \GdImage The pixelated image
     */
    public function pixelate(\GdImage $image, int $blockSize = 10): \GdImage
    {
        imagefilter($image, IMG_FILTER_PIXELATE, $blockSize, true);
        return $image;
    }

    /**
     * Apply a negate filter (color inversion)
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The negated image
     */
    public function negate(\GdImage $image): \GdImage
    {
        imagefilter($image, IMG_FILTER_NEGATE);
        return $image;
    }

    /**
     * Convert image to sepia tone
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The sepia-toned image
     */
    public function sepia(\GdImage $image): \GdImage
    {
        // First convert to grayscale
        imagefilter($image, IMG_FILTER_GRAYSCALE);

        // Then apply a warm color overlay for sepia effect
        imagefilter($image, IMG_FILTER_COLORIZE, 90, 60, 30);

        return $image;
    }

    /**
     * Adjust image colorization
     * 
     * @param \GdImage $image The source image
     * @param int $red Red component (-255 to 255)
     * @param int $green Green component (-255 to 255)
     * @param int $blue Blue component (-255 to 255)
     * @return \GdImage The colorized image
     */
    public function colorize(\GdImage $image, int $red, int $green, int $blue): \GdImage
    {
        $red = max(-255, min(255, $red));
        $green = max(-255, min(255, $green));
        $blue = max(-255, min(255, $blue));

        imagefilter($image, IMG_FILTER_COLORIZE, $red, $green, $blue);
        return $image;
    }

    /**
     * Apply a custom convolution filter
     * 
     * @param \GdImage $image The source image
     * @param array<array<float>> $matrix 3x3 convolution matrix
     * @param float $divisor Divisor for the convolution
     * @param float $offset Offset value
     * @return \GdImage The filtered image
     */
    public function convolution(\GdImage $image, array $matrix, float $divisor = 1, float $offset = 0): \GdImage
    {
        if (count($matrix) !== 3 || count($matrix[0]) !== 3) {
            throw new \InvalidArgumentException('Matrix must be 3x3');
        }

        imageconvolution($image, $matrix, $divisor, $offset);
        return $image;
    }

    /**
     * Apply a custom sharpening filter
     * More control than the built-in sharpen
     * 
     * @param \GdImage $image The source image
     * @param float $amount Sharpening amount (0-1)
     * @return \GdImage The sharpened image
     */
    public function sharpenCustom(\GdImage $image, float $amount = 0.5): \GdImage
    {
        $amount = max(0, min(1, $amount));

        // Unsharp mask matrix
        $matrix = [
            [-1, -1, -1],
            [-1, 16, -1],
            [-1, -1, -1],
        ];

        $divisor = 8 + (8 * $amount);

        return $this->convolution($image, $matrix, $divisor, 0);
    }

    /**
     * Apply edge enhancement
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The edge-enhanced image
     */
    public function edgeEnhance(\GdImage $image): \GdImage
    {
        $matrix = [
            [0, -1, 0],
            [-1, 5, -1],
            [0, -1, 0],
        ];

        return $this->convolution($image, $matrix, 1, 0);
    }

    /**
     * Apply a sketch effect
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The sketched image
     */
    public function sketch(\GdImage $image): \GdImage
    {
        // Convert to grayscale first
        imagefilter($image, IMG_FILTER_GRAYSCALE);

        // Apply edge detection
        imagefilter($image, IMG_FILTER_EDGEDETECT);

        // Invert to get sketch-like appearance
        imagefilter($image, IMG_FILTER_NEGATE);

        return $image;
    }

    /**
     * Apply multiple filters in sequence
     * 
     * @param \GdImage $image The source image
     * @param array<callable> $filters Array of filter functions
     * @return \GdImage The filtered image
     */
    public function applyMultiple(\GdImage $image, array $filters): \GdImage
    {
        foreach ($filters as $filter) {
            if (!is_callable($filter)) {
                throw new \InvalidArgumentException('Each filter must be callable');
            }
            $image = $filter($image);
        }
        return $image;
    }
}
