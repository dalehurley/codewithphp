<?php

declare(strict_types=1);

/**
 * ImageProcessor - Perform common image manipulations
 * 
 * This class provides methods for resizing, cropping, rotating,
 * and other basic image transformations.
 */
final class ImageProcessor
{
    /**
     * Resize an image to specified dimensions
     * 
     * @param \GdImage $image The source image
     * @param int $newWidth Target width
     * @param int $newHeight Target height
     * @param bool $maintainAspectRatio Whether to maintain aspect ratio
     * @return \GdImage The resized image
     */
    public function resize(
        \GdImage $image,
        int $newWidth,
        int $newHeight,
        bool $maintainAspectRatio = true
    ): \GdImage {
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        if ($maintainAspectRatio) {
            $aspectRatio = $originalWidth / $originalHeight;

            if ($newWidth / $newHeight > $aspectRatio) {
                $newWidth = (int)($newHeight * $aspectRatio);
            } else {
                $newHeight = (int)($newWidth / $aspectRatio);
            }
        }

        $resized = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG images
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled(
            $resized,
            $image,
            0,
            0,
            0,
            0,
            $newWidth,
            $newHeight,
            $originalWidth,
            $originalHeight
        );

        return $resized;
    }

    /**
     * Create a thumbnail with maximum dimensions
     * 
     * @param \GdImage $image The source image
     * @param int $maxWidth Maximum width
     * @param int $maxHeight Maximum height
     * @return \GdImage The thumbnail image
     */
    public function thumbnail(\GdImage $image, int $maxWidth, int $maxHeight): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        // Calculate scaling factor
        $scale = min($maxWidth / $width, $maxHeight / $height);

        if ($scale >= 1) {
            // Image is already smaller than max dimensions
            return $this->resize($image, $width, $height, false);
        }

        $newWidth = (int)($width * $scale);
        $newHeight = (int)($height * $scale);

        return $this->resize($image, $newWidth, $newHeight, false);
    }

    /**
     * Crop an image to specified dimensions
     * 
     * @param \GdImage $image The source image
     * @param int $x Starting X coordinate
     * @param int $y Starting Y coordinate
     * @param int $width Crop width
     * @param int $height Crop height
     * @return \GdImage The cropped image
     */
    public function crop(\GdImage $image, int $x, int $y, int $width, int $height): \GdImage
    {
        $cropped = imagecreatetruecolor($width, $height);

        // Preserve transparency
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);

        imagecopy($cropped, $image, 0, 0, $x, $y, $width, $height);

        return $cropped;
    }

    /**
     * Crop image to center
     * 
     * @param \GdImage $image The source image
     * @param int $width Target width
     * @param int $height Target height
     * @return \GdImage The center-cropped image
     */
    public function cropCenter(\GdImage $image, int $width, int $height): \GdImage
    {
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);

        $x = (int)(($originalWidth - $width) / 2);
        $y = (int)(($originalHeight - $height) / 2);

        // Ensure coordinates are within bounds
        $x = max(0, min($x, $originalWidth - $width));
        $y = max(0, min($y, $originalHeight - $height));

        return $this->crop($image, $x, $y, $width, $height);
    }

    /**
     * Rotate an image by specified degrees
     * 
     * @param \GdImage $image The source image
     * @param float $degrees Rotation angle (positive = counterclockwise)
     * @param int $bgColor Background color (default: transparent)
     * @return \GdImage The rotated image
     */
    public function rotate(\GdImage $image, float $degrees, int $bgColor = 0): \GdImage
    {
        $rotated = imagerotate($image, $degrees, $bgColor);

        if ($rotated === false) {
            throw new \RuntimeException("Failed to rotate image");
        }

        // Preserve transparency
        imagealphablending($rotated, false);
        imagesavealpha($rotated, true);

        return $rotated;
    }

    /**
     * Flip an image horizontally
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The flipped image
     */
    public function flipHorizontal(\GdImage $image): \GdImage
    {
        imageflip($image, IMG_FLIP_HORIZONTAL);
        return $image;
    }

    /**
     * Flip an image vertically
     * 
     * @param \GdImage $image The source image
     * @return \GdImage The flipped image
     */
    public function flipVertical(\GdImage $image): \GdImage
    {
        imageflip($image, IMG_FLIP_VERTICAL);
        return $image;
    }

    /**
     * Scale an image by a percentage
     * 
     * @param \GdImage $image The source image
     * @param float $percentage Scale percentage (e.g., 50 = half size, 200 = double size)
     * @return \GdImage The scaled image
     */
    public function scale(\GdImage $image, float $percentage): \GdImage
    {
        $width = imagesx($image);
        $height = imagesy($image);

        $newWidth = (int)($width * ($percentage / 100));
        $newHeight = (int)($height * ($percentage / 100));

        return $this->resize($image, $newWidth, $newHeight, false);
    }
}
