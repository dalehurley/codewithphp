<?php

declare(strict_types=1);

/**
 * ColorConverter - Convert images between color spaces
 * 
 * This class provides methods for converting images to grayscale,
 * extracting color channels, and other color space operations.
 */
final class ColorConverter
{
    /**
     * Convert an image to grayscale
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The grayscale image
     */
    public function toGrayscale(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $gray = imagecreatetruecolor($width, $height);

        // Use GD's built-in grayscale filter
        imagefilter($gray, IMG_FILTER_GRAYSCALE);
        imagecopy($gray, $image, 0, 0, 0, 0, $width, $height);
        imagefilter($gray, IMG_FILTER_GRAYSCALE);

        return $gray;
    }

    /**
     * Convert image to grayscale using luminosity method
     * This is more accurate than GD's built-in filter
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The grayscale image
     */
    public function toGrayscaleLuminosity(\GdImage $image): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $gray = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                // Luminosity formula: 0.299*R + 0.587*G + 0.114*B
                $luminosity = (int)(
                    $colors['red'] * 0.299 +
                    $colors['green'] * 0.587 +
                    $colors['blue'] * 0.114
                );

                $grayColor = imagecolorallocate($gray, $luminosity, $luminosity, $luminosity);
                imagesetpixel($gray, $x, $y, $grayColor);
            }
        }

        return $gray;
    }

    /**
     * Extract the red channel from an image
     * 
     * @param \GdImage $image The source image
     * @return \GdImage Image showing only red channel
     */
    public function extractRedChannel(\GdImage $image): \GdImage
    {
        return $this->extractChannel($image, 'red');
    }

    /**
     * Extract the green channel from an image
     * 
     * @param \GdImage $image The source image
     * @return \GdImage Image showing only green channel
     */
    public function extractGreenChannel(\GdImage $image): \GdImage
    {
        return $this->extractChannel($image, 'green');
    }

    /**
     * Extract the blue channel from an image
     * 
     * @param \GdImage $image The source image
     * @return \GdImage Image showing only blue channel
     */
    public function extractBlueChannel(\GdImage $image): \GdImage
    {
        return $this->extractChannel($image, 'blue');
    }

    /**
     * Extract a specific color channel
     * 
     * @param \GdImage $image The source image
     * @param string $channel Channel name: 'red', 'green', or 'blue'
     * @return \GdImage Image showing only specified channel
     */
    private function extractChannel(\GdImage $image, string $channel): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $result = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                $value = $colors[$channel];

                $newColor = match ($channel) {
                    'red' => imagecolorallocate($result, $value, 0, 0),
                    'green' => imagecolorallocate($result, 0, $value, 0),
                    'blue' => imagecolorallocate($result, 0, 0, $value),
                    default => throw new \InvalidArgumentException("Invalid channel: {$channel}")
                };

                imagesetpixel($result, $x, $y, $newColor);
            }
        }

        return $result;
    }

    /**
     * Get average color of an image
     * 
     * @param \GdImage $image The source image
     * @return array{r: int, g: int, b: int}
     */
    public function getAverageColor(\GdImage $image): array
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $totalR = 0;
        $totalG = 0;
        $totalB = 0;
        $pixelCount = $width * $height;

        // Sample every nth pixel for performance on large images
        $sampleRate = max(1, (int)sqrt($pixelCount / 10000));

        $sampledPixels = 0;
        for ($y = 0; $y < $height; $y += $sampleRate) {
            for ($x = 0; $x < $width; $x += $sampleRate) {
                $rgb = imagecolorat($image, $x, $y);
                $colors = imagecolorsforindex($image, $rgb);

                $totalR += $colors['red'];
                $totalG += $colors['green'];
                $totalB += $colors['blue'];
                $sampledPixels++;
            }
        }

        return [
            'r' => (int)($totalR / $sampledPixels),
            'g' => (int)($totalG / $sampledPixels),
            'b' => (int)($totalB / $sampledPixels),
        ];
    }

    /**
     * Get dominant color of an image using color quantization
     * 
     * @param \GdImage $image The source image
     * @param int $colorCount Number of colors to reduce to
     * @return array{r: int, g: int, b: int}
     */
    public function getDominantColor(\GdImage $image, int $colorCount = 5): array
    {
        // Create a smaller version for faster processing
        $tempWidth = 150;
        $tempHeight = 150;
        $temp = imagecreatetruecolor($tempWidth, $tempHeight);

        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        imagecopyresampled(
            $temp,
            $image,
            0,
            0,
            0,
            0,
            $tempWidth,
            $tempHeight,
            $originalWidth,
            $originalHeight
        );

        // Reduce colors to find dominant
        imagetruecolortopalette($temp, false, $colorCount);

        // Get the most common color
        $colorCounts = [];
        for ($y = 0; $y < $tempHeight; $y++) {
            for ($x = 0; $x < $tempWidth; $x++) {
                $colorIndex = imagecolorat($temp, $x, $y);
                $colorCounts[$colorIndex] = ($colorCounts[$colorIndex] ?? 0) + 1;
            }
        }

        arsort($colorCounts);
        $dominantColorIndex = array_key_first($colorCounts);
        $colors = imagecolorsforindex($temp, $dominantColorIndex);

        imagedestroy($temp);

        return [
            'r' => $colors['red'],
            'g' => $colors['green'],
            'b' => $colors['blue'],
        ];
    }

    /**
     * Adjust brightness of an image
     * 
     * @param \GdImage $image The source image
     * @param int $level Brightness level (-255 to 255)
     * @return \GdImage The adjusted image
     */
    public function adjustBrightness(\GdImage $image, int $level): \GdImage
    {
        $level = max(-255, min(255, $level));
        imagefilter($image, IMG_FILTER_BRIGHTNESS, $level);
        return $image;
    }

    /**
     * Adjust contrast of an image
     * 
     * @param \GdImage $image The source image
     * @param int $level Contrast level (-100 to 100)
     * @return \GdImage The adjusted image
     */
    public function adjustContrast(\GdImage $image, int $level): \GdImage
    {
        $level = max(-100, min(100, $level));
        imagefilter($image, IMG_FILTER_CONTRAST, -$level);
        return $image;
    }
}
