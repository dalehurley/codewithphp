<?php

declare(strict_types=1);

/**
 * FeatureExtractor - Extract numeric features from images for ML
 * 
 * This class extracts statistical features from images that can be used
 * as input to machine learning algorithms.
 */
final class FeatureExtractor
{
    /**
     * Extract basic statistical features from an image
     * 
     * @param \GdImage $image The source image
     * @return array{
     *   width: int,
     *   height: int,
     *   aspect_ratio: float,
     *   avg_red: float,
     *   avg_green: float,
     *   avg_blue: float,
     *   avg_brightness: float,
     *   std_red: float,
     *   std_green: float,
     *   std_blue: float
     * }
     */
    public function extractBasicFeatures(\GdImage $image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $stats = $this->calculateColorStatistics($image);

        return [
            'width' => $width,
            'height' => $height,
            'aspect_ratio' => $width / $height,
            'avg_red' => $stats['avg_red'],
            'avg_green' => $stats['avg_green'],
            'avg_blue' => $stats['avg_blue'],
            'avg_brightness' => $stats['avg_brightness'],
            'std_red' => $stats['std_red'],
            'std_green' => $stats['std_green'],
            'std_blue' => $stats['std_blue'],
        ];
    }

    /**
     * Calculate color channel statistics
     * 
     * @param \GdImage $image The source image
     * @return array{
     *   avg_red: float,
     *   avg_green: float,
     *   avg_blue: float,
     *   avg_brightness: float,
     *   std_red: float,
     *   std_green: float,
     *   std_blue: float
     * }
     */
    public function calculateColorStatistics(\GdImage $image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $redValues = [];
        $greenValues = [];
        $blueValues = [];

        // Sample pixels (for large images, sample every nth pixel)
        $sampleRate = max(1, (int)sqrt(($width * $height) / 10000));

        for ($y = 0; $y < $height; $y += $sampleRate) {
            for ($x = 0; $x < $width; $x += $sampleRate) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                $redValues[] = $colors['red'];
                $greenValues[] = $colors['green'];
                $blueValues[] = $colors['blue'];
            }
        }

        $avgRed = array_sum($redValues) / count($redValues);
        $avgGreen = array_sum($greenValues) / count($greenValues);
        $avgBlue = array_sum($blueValues) / count($blueValues);

        return [
            'avg_red' => $avgRed,
            'avg_green' => $avgGreen,
            'avg_blue' => $avgBlue,
            'avg_brightness' => ($avgRed + $avgGreen + $avgBlue) / 3,
            'std_red' => $this->calculateStdDev($redValues, $avgRed),
            'std_green' => $this->calculateStdDev($greenValues, $avgGreen),
            'std_blue' => $this->calculateStdDev($blueValues, $avgBlue),
        ];
    }

    /**
     * Extract a color histogram
     * 
     * @param \GdImage $image The source image
     * @param int $bins Number of bins per channel (default: 16)
     * @return array{red: array<int>, green: array<int>, blue: array<int>}
     */
    public function extractColorHistogram(\GdImage $image, int $bins = 16): array
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $binSize = 256 / $bins;

        $redHistogram = array_fill(0, $bins, 0);
        $greenHistogram = array_fill(0, $bins, 0);
        $blueHistogram = array_fill(0, $bins, 0);

        $sampleRate = max(1, (int)sqrt(($width * $height) / 10000));

        for ($y = 0; $y < $height; $y += $sampleRate) {
            for ($x = 0; $x < $width; $x += $sampleRate) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                $redBin = min($bins - 1, (int)($colors['red'] / $binSize));
                $greenBin = min($bins - 1, (int)($colors['green'] / $binSize));
                $blueBin = min($bins - 1, (int)($colors['blue'] / $binSize));

                $redHistogram[$redBin]++;
                $greenHistogram[$greenBin]++;
                $blueHistogram[$blueBin]++;
            }
        }

        return [
            'red' => $redHistogram,
            'green' => $greenHistogram,
            'blue' => $blueHistogram,
        ];
    }

    /**
     * Flatten an image to a 1D feature vector (for ML models)
     * 
     * @param \GdImage $image The source image
     * @param int $targetWidth Resize to this width before flattening
     * @param int $targetHeight Resize to this height before flattening
     * @param bool $grayscale Convert to grayscale first
     * @return array<float> Flattened pixel values (normalized 0-1)
     */
    public function flattenToVector(
        \GdImage $image,
        int $targetWidth = 32,
        int $targetHeight = 32,
        bool $grayscale = true
    ): array {
        // Resize image to target dimensions
        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            imagesx($image),
            imagesy($image)
        );

        if ($grayscale) {
            imagefilter($resized, IMG_FILTER_GRAYSCALE);
        }

        $vector = [];

        for ($y = 0; $y < $targetHeight; $y++) {
            for ($x = 0; $x < $targetWidth; $x++) {
                $rgb = imagecolorat($resized, $x, $y);
                $colors = imagecolorsforindex($resized, $rgb);

                if ($grayscale) {
                    // Single value for grayscale (normalized)
                    $vector[] = $colors['red'] / 255.0;
                } else {
                    // Three values for RGB (normalized)
                    $vector[] = $colors['red'] / 255.0;
                    $vector[] = $colors['green'] / 255.0;
                    $vector[] = $colors['blue'] / 255.0;
                }
            }
        }

        imagedestroy($resized);

        return $vector;
    }

    /**
     * Extract edge density feature (indicates complexity/texture)
     * 
     * @param \GdImage $image The source image
     * @return float Edge density score (0-1)
     */
    public function extractEdgeDensity(\GdImage $image): float
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Create a grayscale copy
        $gray = imagecreatetruecolor($width, $height);
        imagecopy($gray, $image, 0, 0, 0, 0, $width, $height);
        imagefilter($gray, IMG_FILTER_GRAYSCALE);

        // Apply edge detection
        imagefilter($gray, IMG_FILTER_EDGEDETECT);

        // Count edge pixels
        $edgePixels = 0;
        $totalPixels = 0;

        $sampleRate = max(1, (int)sqrt(($width * $height) / 10000));

        for ($y = 0; $y < $height; $y += $sampleRate) {
            for ($x = 0; $x < $width; $x += $sampleRate) {
                $rgb = imagecolorat($gray, $x, $y);
                $colors = imagecolorsforindex($gray, $rgb);

                // Consider bright pixels as edges
                if ($colors['red'] > 64) {
                    $edgePixels++;
                }
                $totalPixels++;
            }
        }

        imagedestroy($gray);

        return $totalPixels > 0 ? $edgePixels / $totalPixels : 0.0;
    }

    /**
     * Get the brightness level of an image
     * 
     * @param \GdImage $image The source image
     * @return float Brightness level (0-255)
     */
    public function getBrightness(\GdImage $image): float
    {
        $stats = $this->calculateColorStatistics($image);
        return $stats['avg_brightness'];
    }

    /**
     * Calculate standard deviation
     * 
     * @param array<int|float> $values
     * @param float $mean
     * @return float
     */
    private function calculateStdDev(array $values, float $mean): float
    {
        $variance = 0.0;
        $count = count($values);

        foreach ($values as $value) {
            $variance += ($value - $mean) ** 2;
        }

        return sqrt($variance / $count);
    }

    /**
     * Extract comprehensive features suitable for classification
     * 
     * @param \GdImage $image The source image
     * @return array<float> Feature vector
     */
    public function extractAllFeatures(\GdImage $image): array
    {
        $basic = $this->extractBasicFeatures($image);
        $edgeDensity = $this->extractEdgeDensity($image);

        return [
            $basic['width'],
            $basic['height'],
            $basic['aspect_ratio'],
            $basic['avg_red'],
            $basic['avg_green'],
            $basic['avg_blue'],
            $basic['avg_brightness'],
            $basic['std_red'],
            $basic['std_green'],
            $basic['std_blue'],
            $edgeDensity,
        ];
    }
}
