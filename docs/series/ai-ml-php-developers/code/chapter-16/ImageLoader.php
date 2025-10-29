<?php

declare(strict_types=1);

/**
 * ImageLoader - Load and save images in various formats
 * 
 * This class provides a simple interface for loading images from files,
 * getting image information, and saving images to disk.
 */
final class ImageLoader
{
    /**
     * Load an image from a file
     * 
     * @param string $filepath Path to the image file
     * @return \GdImage The loaded image resource
     * @throws \RuntimeException if the file doesn't exist or can't be loaded
     */
    public function load(string $filepath): \GdImage
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Image file not found: {$filepath}");
        }

        $imageInfo = getimagesize($filepath);
        if ($imageInfo === false) {
            throw new \RuntimeException("Invalid image file: {$filepath}");
        }

        [$width, $height, $type] = $imageInfo;

        $image = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($filepath),
            IMAGETYPE_PNG => imagecreatefrompng($filepath),
            IMAGETYPE_GIF => imagecreatefromgif($filepath),
            IMAGETYPE_WEBP => imagecreatefromwebp($filepath),
            default => throw new \RuntimeException("Unsupported image type: {$filepath}")
        };

        if ($image === false) {
            throw new \RuntimeException("Failed to create image from: {$filepath}");
        }

        return $image;
    }

    /**
     * Get information about an image file
     * 
     * @param string $filepath Path to the image file
     * @return array{width: int, height: int, type: string, mime: string, bits: int}
     */
    public function getInfo(string $filepath): array
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Image file not found: {$filepath}");
        }

        $info = getimagesize($filepath);
        if ($info === false) {
            throw new \RuntimeException("Invalid image file: {$filepath}");
        }

        $width = $info[0];
        $height = $info[1];
        $type = $info[2];
        $mime = $info['mime'] ?? '';
        $bits = $info['bits'] ?? 8;
        $channels = $info['channels'] ?? 3;

        return [
            'width' => $width,
            'height' => $height,
            'type' => $this->getTypeName($type),
            'mime' => $mime,
            'bits' => $bits,
            'channels' => $channels,
            'pixels' => $width * $height,
            'filesize' => filesize($filepath),
        ];
    }

    /**
     * Save an image to a file
     * 
     * @param \GdImage $image The image resource to save
     * @param string $filepath Path where to save the image
     * @param int $quality Quality for JPEG/WEBP (0-100), compression for PNG (0-9)
     * @return bool True on success
     */
    public function save(\GdImage $image, string $filepath, int $quality = 90): bool
    {
        $extension = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $filepath, $quality),
            'png' => imagepng($image, $filepath, (int)(9 - ($quality / 11))),
            'gif' => imagegif($image, $filepath),
            'webp' => imagewebp($image, $filepath, $quality),
            default => throw new \RuntimeException("Unsupported save format: {$extension}")
        };
    }

    /**
     * Get dimensions of an image resource
     * 
     * @param \GdImage $image The image resource
     * @return array{width: int, height: int}
     */
    public function getDimensions(\GdImage $image): array
    {
        return [
            'width' => imagesx($image),
            'height' => imagesy($image),
        ];
    }

    /**
     * Get the type name from IMAGETYPE constant
     */
    private function getTypeName(int $type): string
    {
        return match ($type) {
            IMAGETYPE_JPEG => 'JPEG',
            IMAGETYPE_PNG => 'PNG',
            IMAGETYPE_GIF => 'GIF',
            IMAGETYPE_WEBP => 'WEBP',
            IMAGETYPE_BMP => 'BMP',
            default => 'Unknown'
        };
    }

    /**
     * Get pixel color at specific coordinates
     * 
     * @param \GdImage $image The image resource
     * @param int $x X coordinate
     * @param int $y Y coordinate
     * @return array{r: int, g: int, b: int, alpha: int}
     */
    public function getPixelColor(\GdImage $image, int $x, int $y): array
    {
        $colorIndex = imagecolorat($image, $x, $y);
        $colors = imagecolorsforindex($image, $colorIndex);

        return [
            'r' => $colors['red'],
            'g' => $colors['green'],
            'b' => $colors['blue'],
            'alpha' => $colors['alpha'],
        ];
    }
}
