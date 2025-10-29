---
title: "16: Computer Vision Essentials for PHP Developers"
description: "Master image processing in PHP—from loading and manipulating images to extracting features for machine learning, using GD extension and custom processors"
series: "ai-ml-php-developers"
chapter: 16
order: 16
difficulty: "Intermediate"
prerequisites:
  - "/series/ai-ml-php-developers/chapters/15-language-models-and-text-generation-with-openai-apis"
---

![Computer Vision Essentials for PHP Developers](/images/ai-ml-php-developers/chapter-16-computer-vision-hero-full.webp)

# Chapter 16: Computer Vision Essentials for PHP Developers

## Overview

Computer vision is the field of teaching computers to "see" and understand images. While humans instantly recognize objects, faces, and scenes in photos, computers see only grids of numbers representing pixel colors. Transforming these numeric arrays into meaningful information—identifying a cat in a photo, detecting text in a document, or recognizing a person's face—requires specialized techniques that bridge the gap between raw pixels and high-level understanding.

For PHP developers building modern web applications, computer vision capabilities are increasingly essential. Users upload profile photos that need cropping and resizing. Content moderation systems must identify inappropriate images. E-commerce sites want to enable visual search. Analytics dashboards visualize data with charts that need processing. All these scenarios require working with image data programmatically.

This chapter introduces the fundamentals of computer vision using PHP's built-in capabilities. You'll learn how images are represented as data, how to manipulate them (resizing, cropping, filtering), and—most importantly—how to extract numeric features that machine learning algorithms can understand. Unlike later chapters that will use pre-trained deep learning models, this chapter focuses on the foundational skills: working with pixels, color channels, and statistical features using PHP's GD extension.

By mastering these essentials, you'll be prepared to tackle image classification in Chapter 17, implement object detection in Chapter 18, and integrate computer vision features into production PHP applications. Whether you're building a photo gallery, implementing visual search, or preparing images for machine learning, the techniques you learn here form the foundation of every computer vision project.

## Prerequisites

Before starting this chapter, you should have:

- PHP 8.4+ installed and confirmed working with `php --version`
- GD extension enabled (check with `php -m | grep gd`)
- Completion of [Chapter 15](/series/ai-ml-php-developers/chapters/15-language-models-and-text-generation-with-openai-apis) or equivalent understanding of preprocessing data for ML
- Understanding of PHP arrays, classes, and object-oriented programming
- Basic familiarity with ML concepts from [Chapter 3](/series/ai-ml-php-developers/chapters/03-core-machine-learning-concepts-and-terminology)
- A text editor or IDE with PHP support
- Command line access for running examples

**Estimated Time**: ~85-100 minutes

**Verify your setup:**

```bash
# Check PHP version
php --version
# Should show PHP 8.4.x

# Check if GD extension is loaded
php -m | grep gd
# Should output: gd

# Check GD support
php -r "var_dump(gd_info());"
# Should show array with version and format support
```

If GD is not installed:

```bash
# Ubuntu/Debian
sudo apt-get install php8.4-gd

# macOS (Homebrew)
brew install php@8.4

# Windows: Enable extension=gd in php.ini
```

## What You'll Build

By the end of this chapter, you will have created:

- An **ImageLoader** class supporting JPEG, PNG, GIF, and WEBP formats with error handling
- An **ImageProcessor** class for resizing, cropping, rotating, and transforming images
- A **ColorConverter** class for grayscale conversion, channel extraction, and color analysis
- A **FeatureExtractor** class extracting numeric features (statistics, histograms, edge density)
- An **ImageFilter** class applying blur, sharpen, edge detection, and artistic effects
- An **ImageAugmentor** class generating training variations through data augmentation
- **9 complete working examples** demonstrating each computer vision technique including augmentation
- **3 practical exercises** with solutions: image analyzer, thumbnail generator, feature comparison
- A comprehensive understanding of how to prepare images for machine learning
- Reusable, production-ready code following PHP 8.4 best practices

All code examples are fully functional, tested, and include sample images you can run immediately.

::: info Code Examples
Complete, runnable examples for this chapter:

**Core Classes:**

- [`ImageLoader.php`](../code/chapter-16/ImageLoader.php) — Load, save, and inspect images
- [`ImageProcessor.php`](../code/chapter-16/ImageProcessor.php) — Resize, crop, rotate, transform
- [`ColorConverter.php`](../code/chapter-16/ColorConverter.php) — Color space conversions and analysis
- [`FeatureExtractor.php`](../code/chapter-16/FeatureExtractor.php) — Extract ML features from images
- [`ImageFilter.php`](../code/chapter-16/ImageFilter.php) — Apply filters and effects
- [`ImageAugmentor.php`](../code/chapter-16/ImageAugmentor.php) — Generate training variations via augmentation

**Step-by-Step Examples:**

- [`01-image-representation.php`](../code/chapter-16/01-image-representation.php) — Understanding images as data
- [`02-check-extensions.php`](../code/chapter-16/02-check-extensions.php) — Verify PHP setup
- [`03-load-save-images.php`](../code/chapter-16/03-load-save-images.php) — Load and save in multiple formats
- [`04-image-manipulations.php`](../code/chapter-16/04-image-manipulations.php) — Resize, crop, rotate operations
- [`05-color-conversions.php`](../code/chapter-16/05-color-conversions.php) — Grayscale and channel extraction
- [`06-feature-extraction.php`](../code/chapter-16/06-feature-extraction.php) — Extract features for ML
- [`07-image-filters.php`](../code/chapter-16/07-image-filters.php) — Apply various filters
- [`08-ml-preparation.php`](../code/chapter-16/08-ml-preparation.php) — Prepare images for ML models
- [`09-data-augmentation.php`](../code/chapter-16/09-data-augmentation.php) — Image augmentation for training

**Supporting Files:**

- [`helpers.php`](../code/chapter-16/helpers.php) — Utility functions
- [`generate-sample-images.php`](../code/chapter-16/generate-sample-images.php) — Create test images
- [`data/`](../code/chapter-16/data/) — Sample images (sample.jpg, landscape.jpg, face.jpg)

All files in [`docs/series/ai-ml-php-developers/code/chapter-16/`](../code/chapter-16/README.md)
:::

## Quick Start

Want to see image processing in action? Run this 5-minute example:

```bash
cd docs/series/ai-ml-php-developers/code/chapter-16

# Generate sample images
php generate-sample-images.php

# Verify your setup
php 02-check-extensions.php

# Try image analysis
php 01-image-representation.php
```

Expected output:

```
============================================================
 Understanding Images as Data
============================================================

✓ GD extension is loaded
GD Version: bundled (2.1.0 compatible)
Supported formats:
  JPEG: ✓
  PNG:  ✓
  GIF:  ✓
  WEBP: ✓

Loading image: data/sample.jpg

Image Information:
  Dimensions: 400 × 300 pixels
  Total pixels: 120,000
  Format: JPEG
  MIME type: image/jpeg
  Color depth: 8 bits
  File size: 15.2 KB
```

This demonstrates that your environment is set up correctly and you can load and inspect images!

## Objectives

By completing this chapter, you will:

- **Understand** how images are represented as 2D arrays of pixels with RGB color channels
- **Implement** classes for loading, saving, and manipulating images in multiple formats
- **Master** common image operations: resizing, cropping, rotating, and color conversions
- **Extract** numeric features from images suitable for machine learning algorithms
- **Apply** filters and effects for preprocessing and enhancement
- **Generate** augmented training variations to expand limited datasets
- **Prepare** standardized image datasets ready for training ML models
- **Build** practical tools like thumbnail generators and image analyzers
- **Recognize** when to use PHP for computer vision vs. when to integrate Python/OpenCV

## Step 1: Understanding Images as Data (~10 min)

### Goal

Understand how images are represented as 2D grids of pixels, each with RGB color values, and how PHP's GD extension provides access to this data.

### Actions

1. **Run the first example** to see image representation in action:

```bash
cd docs/series/ai-ml-php-developers/code/chapter-16
php 01-image-representation.php
```

2. **Observe the output** showing:

   - Image dimensions (width × height)
   - Total number of pixels
   - File format and MIME type
   - Color depth (bits per channel)
   - Sample pixel colors from different regions

3. **Understanding the numbers**: The example loads `data/sample.jpg` and samples pixels from various locations, showing their RGB values (0-255 for each channel).

### Expected Result

```
============================================================
 Understanding Images as Data
============================================================

✓ GD extension is loaded
GD Version: bundled (2.1.0 compatible)
...

Image Information:
  Dimensions: 400 × 300 pixels
  Total pixels: 120,000
  Format: JPEG
  File size: 15.2 KB

============================================================
 Sampling Pixel Colors
============================================================

Top-left region (x:50, y:50):
  Red channel:   255 (100.0%)
  Green channel:   0 (0.0%)
  Blue channel:    0 (0.0%)

Center (x:200, y:150):
  Red channel:     0 (0.0%)
  Green channel: 255 (100.0%)
  Blue channel:    0 (0.0%)

...
```

### Why It Works

Images are 2D grids where each pixel stores color information. For RGB images, each pixel has three values (Red, Green, Blue), each ranging from 0 to 255. A 400×300 pixel image contains 120,000 pixels, which means 360,000 individual color values (120,000 × 3 channels).

PHP's GD extension represents images as `GdImage` objects (PHP 8.0+). The `imagecolorat()` function retrieves a pixel's color index, and `imagecolorsforindex()` converts that to RGB values. This low-level access lets you analyze and manipulate every pixel programmatically.

The memory footprint is significant: uncompressed, our 400×300 image requires ~469 KB (400 × 300 × 4 bytes per pixel in RGBA). JPEG compression reduces this to ~15 KB on disk—a 31:1 compression ratio.

### Troubleshooting

**"Call to undefined function imagecolorat()"**

- GD extension is not loaded. Run `php -m | grep gd` to verify. Install with `sudo apt-get install php8.4-gd` (Linux) or enable in `php.ini` (Windows).

**"Image file not found"**

- Run `php generate-sample-images.php` first to create the sample images in the `data/` directory.

**Memory errors with large images**

- Large images consume significant memory. A 4000×3000 pixel image needs ~46 MB uncompressed. Increase `memory_limit` in `php.ini` or use `ini_set('memory_limit', '256M')`.

## Step 2: Setting Up PHP Image Processing (~10 min)

### Goal

Verify that your PHP installation has everything needed for image processing and understand what capabilities are available.

### Actions

1. **Run the setup verification script**:

```bash
php 02-check-extensions.php
```

2. **Review the output** to confirm:

   - PHP 8.4+ is installed
   - GD extension is loaded
   - All required image formats are supported (JPEG, PNG, GIF, WEBP)
   - Memory limit is adequate
   - Sample images exist
   - Output directory is writable

3. **If any checks fail**, follow the installation instructions provided by the script.

### Expected Result

```
============================================================
 PHP Image Processing Setup Check
============================================================

PHP Version Check:
  Current: 8.4.10
  Required: 8.4.0+
  Status: ✓ OK

============================================================
 GD Extension Check
============================================================

✓ GD extension is installed

GD Library Information:
  Version: bundled (2.1.0 compatible)
  FreeType Support: ✓

Image Format Support:
  JPEG: ✓ Yes
  PNG:  ✓ Yes
  GIF:  ✓ Yes
  WEBP: ✓ Yes
  BMP:  ✓ Yes

...

============================================================
 Setup Summary
============================================================

🎉 Your environment is ready for Chapter 16!

You can now:
  ✓ Load and save images in multiple formats
  ✓ Process images with GD library
  ✓ Run all chapter examples
```

### Why It Works

The GD extension is PHP's built-in image manipulation library. Most PHP installations include GD by default, but it's compiled as a shared module that must be enabled. The `gd_info()` function returns an array describing GD's capabilities, including supported formats.

Format support depends on how GD was compiled:

- **JPEG** support requires libjpeg
- **PNG** support requires libpng and zlib
- **WEBP** support requires libwebp (PHP 7.0+)
- **GIF** support is usually built-in

The script also checks PHP's `memory_limit` setting. Image processing is memory-intensive: loading a 10 MP image requires ~38 MB (10,000,000 pixels × 4 bytes). A limit of 128M+ is recommended for typical use.

### Troubleshooting

**GD extension not found**

Ubuntu/Debian:

```bash
sudo apt-get install php8.4-gd
sudo systemctl restart php8.4-fpm  # If using PHP-FPM
```

macOS (Homebrew):

```bash
brew install php@8.4
# GD is included by default
```

Windows:

1. Open `php.ini`
2. Find `;extension=gd`
3. Remove the semicolon: `extension=gd`
4. Restart web server

**WEBP support missing**

WEBP requires GD 2.0+ and libwebp. On older systems, you may need to compile PHP from source or use a PPA/repository with modern libraries.

**Memory limit warnings**

Edit `php.ini`:

```ini
memory_limit = 256M
```

Or set per-script:

```php
ini_set('memory_limit', '256M');
```

## Step 3: Loading and Saving Images (~8 min)

### Goal

Learn to load images from files, save them in different formats with quality settings, and understand format tradeoffs.

### Actions

1. **Run the load/save example**:

```bash
php 03-load-save-images.php
```

2. **Examine the `ImageLoader` class** in [`ImageLoader.php`](../code/chapter-16/ImageLoader.php):

```php
# filename: ImageLoader.php (excerpt)
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
        default => throw new \RuntimeException("Unsupported image type")
    };

    return $image;
}
```

3. **Notice the pattern**: `getimagesize()` identifies the format, then the appropriate `imagecreatefrom*()` function loads it. The `match` expression (PHP 8.0+) makes this elegant and type-safe.

4. **Check the output directory** to see saved files in various formats and quality levels.

### Expected Result

```
============================================================
 Loading and Saving Images
============================================================

Loading: data/landscape.jpg
Image loading completed in 3.45 ms

Image Information:
  width                : 600
  height               : 400
  type                 : JPEG
  mime                 : image/jpeg
  ...

============================================================
 Saving in Multiple Formats
============================================================

Saving JPEG (quality 100) completed in 12.34 ms
  Size: 89.4 KB
✓ Saved: output/landscape_q100.jpg

Saving JPEG (quality 75) completed in 10.12 ms
  Size: 45.2 KB
✓ Saved: output/landscape_q75.jpg

...

============================================================
 Format Comparison
============================================================

Original JPEG: 65.3 KB (100%)
JPEG Q75:      45.2 KB (69%)
PNG:           234.5 KB (359%)
WEBP:          38.7 KB (59%)
```

### Why It Works

GD provides separate functions for each format because they have different requirements:

- **`imagejpeg($image, $filename, $quality)`**: Quality 0-100, lossy compression
- **`imagepng($image, $filename, $compression)`**: Compression 0-9 (inverted: 9 = max), lossless
- **`imagegif($image, $filename)`**: No quality parameter, lossless for ≤256 colors
- **`imagewebp($image, $filename, $quality)`**: Quality 0-100, lossy or lossless

The `save()` method in `ImageLoader` uses file extension to determine the output format, making it easy to convert between formats:

```php
$loader->save($image, 'output/photo.webp', 80);  // Converts to WEBP
```

Format choice matters:

- **JPEG**: Best for photos, lossy, small file sizes, no transparency
- **PNG**: Best for graphics/logos, lossless, supports transparency, larger files
- **WEBP**: Modern format, smaller than JPEG/PNG, supports transparency, not universally supported
- **GIF**: Animated images, limited to 256 colors, large for photos

### Troubleshooting

**"Failed to create image from file"**

- File may be corrupted. Try opening in an image viewer first.
- Format may not be supported. Run `02-check-extensions.php` to verify format support.

**"Unsupported save format"**

- Check file extension. Only `.jpg`, `.jpeg`, `.png`, `.gif`, `.webp` are supported.
- Ensure the format is supported by your GD installation.

**Saved images are blank or corrupted**

- Verify you call `imagedestroy($image)` AFTER saving, not before.
- Check file permissions on output directory: `chmod 755 output/`
- Ensure output directory exists: `mkdir -p output`

**PNG quality seems backwards**

- PNG compression is inverted: 0 = no compression (large), 9 = maximum compression (small, slower). The `save()` method handles this conversion automatically.

## Step 4: Basic Image Manipulations (~10 min)

### Goal

Master resizing, cropping, rotating, and other transformations essential for preparing images for ML and web display.

### Actions

1. **Run the manipulations example**:

```bash
php 04-image-manipulations.php
```

This demonstrates 7 different manipulation techniques and saves each result.

2. **Study the `ImageProcessor` class** in [`ImageProcessor.php`](../code/chapter-16/ImageProcessor.php). Key methods:

```php
# filename: ImageProcessor.php (excerpt)
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

    imagecopyresampled(
        $resized, $image,
        0, 0, 0, 0,
        $newWidth, $newHeight,
        $originalWidth, $originalHeight
    );

    return $resized;
}
```

3. **Understand `imagecopyresampled()`**: This is GD's high-quality resizing function. It performs bilinear interpolation, producing smooth results by averaging surrounding pixels. The simpler `imagecopyresized()` uses nearest-neighbor sampling (faster but lower quality).

4. **Try chaining operations**:

```php
$processor = new ImageProcessor();
$result = $processor->resize($image, 800, 600, true);
$result = $processor->cropCenter($result, 400, 400);
$result = $processor->rotate($result, 90);
```

### Expected Result

```
============================================================
 Basic Image Manipulations
============================================================

Original image: 600×400

============================================================
 1. Resizing (Maintaining Aspect Ratio)
============================================================

Target size: 300×200
Actual size: 300×200
(Aspect ratio maintained)
✓ Saved: output/landscape_resized.jpg

...

============================================================
 4. Rotating
============================================================

Rotated 45°: 707×707
✓ Saved: output/landscape_rotated_45.jpg
Rotated 90°: 400×600
✓ Saved: output/landscape_rotated_90.jpg
Rotated 180°: 600×400
✓ Saved: output/landscape_rotated_180.jpg

...

============================================================
 7. Chaining Operations
============================================================

Creating a profile picture: resize → crop center → save
✓ Saved: output/landscape_profile.jpg (300×300 square)
```

### Why It Works

Image manipulations create new `GdImage` objects rather than modifying the original. This preserves the source and lets you create multiple variants.

**Resizing** uses `imagecopyresampled()` which performs pixel interpolation. When reducing an image from 600×400 to 300×200, each new pixel averages 4 original pixels (2×2 grid). This antialiasing prevents jagged edges.

**Aspect ratio preservation** calculates which dimension (width or height) should be adjusted to maintain the original proportions. For a 600×400 image (1.5:1 aspect ratio) fitted to 300×200:

- Target aspect: 300/200 = 1.5
- Original aspect: 600/400 = 1.5
- They match, so no adjustment needed
- If target was 300×300 (1:1), we'd resize to 300×200 to maintain 1.5:1

**Cropping** extracts a rectangle from the source image using `imagecopy()`. Center cropping calculates the starting position: `x = (originalWidth - cropWidth) / 2`.

**Rotation** uses `imagerotate($image, $degrees, $bgColor)`. Positive degrees rotate counterclockwise. The canvas expands to fit the rotated image (45° rotation increases dimensions to fit the diagonal).

These operations are fundamental for:

- **Responsive images**: Create multiple sizes for different devices
- **Thumbnails**: Generate preview images for galleries
- **ML preprocessing**: Standardize input dimensions
- **User uploads**: Normalize user-submitted content
- **Storage optimization**: Reduce file sizes

### Troubleshooting

**Resized images are blurry**

- This is expected when significantly upscaling (enlarging). You cannot add detail that wasn't in the original.
- For small upscaling (10-20%), the blur is minimal
- Use higher quality source images when possible
- Consider using `imagescale()` for simpler cases (PHP 5.5+)

**Rotated images have black corners**

- Default background is black. Specify a custom color:
  ```php
  $bgColor = imagecolorallocate($image, 255, 255, 255); // White
  $rotated = imagerotate($image, 45, $bgColor);
  ```
- For transparency: `imagecolorallocatealpha($image, 255, 255, 255, 127)`

**Images lose quality after multiple operations**

- Each JPEG save loses quality (lossy compression)
- Perform all operations first, then save once
- Use PNG during intermediate steps if quality is critical

**Memory errors with large images**

- Each image operation creates a new image in memory
- Destroy intermediate images: `imagedestroy($tempImage)`
- Process images at appropriate sizes (resize large images first)

## Step 5: Color Space Conversions (~8 min)

### Goal

Convert images between color spaces (RGB, grayscale), extract individual color channels, and analyze color properties—essential preprocessing steps for many ML algorithms.

### Actions

1. **Run the color conversions example**:

```bash
php 05-color-conversions.php
```

2. **Understand why grayscale matters for ML**: Converting RGB (3 channels) to grayscale (1 channel) reduces input dimensions by 66%, speeding up training and reducing model complexity without significant information loss for many tasks.

3. **Study the `ColorConverter` class** in [`ColorConverter.php`](../code/chapter-16/ColorConverter.php):

```php
# filename: ColorConverter.php (excerpt)
public function toGrayscaleLuminosity(\GdImage $image): \GdImage
{
    $width = imagesx($image);
    $height = imagesy($image);
    $gray = imagecreatetruecolor($width, $height);

    for ($y = 0; $y < $height; $y++) {
        for ($x = 0; $x < $width; $x++) {
            $rgb = imagecolorat($image, $x, $y);
            $colors = imagecolorsforindex($image, $rgb);

            // Luminosity formula: weighted RGB values
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
```

4. **Compare grayscale methods**: The luminosity method (above) weighs green more heavily than red or blue, matching human eye sensitivity. The simpler `IMG_FILTER_GRAYSCALE` averages all channels equally.

### Expected Result

```
============================================================
 Color Space Conversions
============================================================

Loading: data/sample.jpg

============================================================
 1. Grayscale Conversion
============================================================

Converting to grayscale...
✓ Saved: output/sample_grayscale.jpg
✓ Saved: output/sample_grayscale_luminosity.jpg (luminosity method)

Grayscale is useful for:
  • Reducing data dimensions (3 channels → 1)
  • Simplifying ML models
  • Edge detection preprocessing

============================================================
 2. Color Channel Extraction
============================================================

✓ Saved: output/sample_red_channel.jpg
✓ Saved: output/sample_green_channel.jpg
✓ Saved: output/sample_blue_channel.jpg

Channel extraction reveals:
  • Which colors dominate the image
  • Color distribution patterns

============================================================
 3. Average Color Analysis
============================================================

Average Color: RGB(127, 95, 89)

This represents the overall color tone of the image.

...

============================================================
 6. Color Comparison Across Images
============================================================

sample.jpg:
  Average:  RGB(127, 95, 89)
  Dominant: RGB(255, 0, 0)

landscape.jpg:
  Average:  RGB(87, 138, 89)
  Dominant: RGB(34, 139, 34)

face.jpg:
  Average:  RGB(198, 175, 152)
  Dominant: RGB(255, 220, 177)
```

### Why It Works

**Grayscale conversion** discards color information while retaining luminance (brightness). The luminosity method uses weights (0.299R + 0.587G + 0.114B) based on human perception research—our eyes are more sensitive to green than red, and more to red than blue.

For ML, grayscale is advantageous when color isn't meaningful (e.g., handwritten digit recognition, text OCR). A 28×28 RGB image has 2,352 features (28×28×3); grayscale reduces this to 784 (28×28×1), requiring 1/3 the memory and training time.

**Channel extraction** isolates each color component. This reveals:

- **Dominant channel**: Which color predominates (green in landscapes, red/yellow in skin tones)
- **Channel patterns**: Sky is high blue, vegetation is high green
- **Feature engineering**: Use channel histograms or statistics as ML features

**Color analysis** extracts statistical features:

- **Average color**: Mean RGB across all pixels (overall tone)
- **Dominant color**: Most frequent color after quantization (primary hue)
- **Color distance**: Euclidean distance in RGB space measures similarity

These metrics enable:

- **Image categorization**: Landscapes (green), sunsets (orange), underwater (blue)
- **Duplicate detection**: Similar average colors suggest similar content
- **Color-based search**: Find images matching a color palette

### Troubleshooting

**Grayscale conversion is slow for large images**

- The luminosity method iterates every pixel (nested loops)
- For 4000×3000 images, that's 12 million iterations
- Use the faster `imagefilter($image, IMG_FILTER_GRAYSCALE)` if luminosity weighting isn't critical
- Or resize images before conversion

**Colors look different in extracted channels**

- This is expected: red channel shows only red intensity (displayed as grayscale)
- Pure red (255, 0, 0) appears white in red channel, black in green/blue
- These images are for visualization; actual data is just the intensity values

**Average color doesn't match what I see**

- Average color is mathematical mean, not perceptual dominance
- A few bright pixels can skew the average
- Use `getDominantColor()` for the most frequent color instead

**Memory errors during color analysis**

- Color analysis loads full images into memory
- For large batches, resize images first: `$processor->resize($image, 800, 600)`
- Process images sequentially, destroying each before loading the next

## Step 6: Extracting Image Features (~12 min)

### Goal

Extract numeric features from images (statistics, histograms, edge density) that machine learning algorithms can use for classification and analysis.

### Actions

1. **Run the feature extraction example**:

```bash
php 06-feature-extraction.php
```

2. **Study the `FeatureExtractor` class** in [`FeatureExtractor.php`](../code/chapter-16/FeatureExtractor.php):

```php
# filename: FeatureExtractor.php (excerpt)
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
```

3. **Understand the two feature types**:

   - **Statistical features**: 11 values summarizing the image (fast, interpretable)
   - **Raw pixel features**: Every pixel as a feature (1024+ values for 32×32, detailed but large)

4. **Try feature extraction on your own images** by modifying the image paths in the script.

### Expected Result

```
============================================================
 Extracting Image Features for ML
============================================================

============================================================
 1. Basic Statistical Features
============================================================

Analyzing sample.jpg:

  width                : 400
  height               : 300
  aspect_ratio         : 1.3333
  avg_red              : 127.35
  avg_green            : 95.21
  avg_blue             : 89.47
  avg_brightness       : 104.01
  std_red              : 72.14
  std_green            : 68.93
  std_blue             : 61.28

...

============================================================
 4. Edge Density Analysis
============================================================

Sample      : 0.287 (28.7% edge pixels)
Landscape   : 0.156 (15.6% edge pixels)
Face        : 0.092 (9.2% edge pixels)

Edge density indicates:
  • Higher values = more complex/detailed images
  • Lower values = smooth/simple images

============================================================
 5. Flattening Images to Feature Vectors
============================================================

Original image dimensions: 300×300

Flattened to 32x32 (grayscale): 1024 features
  Expected: 1024 features ✓
  Sample values: [0.800, 0.863, 0.855, ..., 0.612]

Flattened to 16x16 (grayscale): 256 features
  Expected: 256 features ✓
  Sample values: [0.798, 0.862, 0.854, ..., 0.614]

Flattened to 16x16 (RGB): 768 features
  Expected: 768 features ✓
  Sample values: [1.000, 0.863, 0.867, ..., 0.671]

Flattened vectors can be used as input to ML algorithms:
  • Each pixel becomes a feature
  • Values normalized to 0-1 range
  • Smaller sizes = faster training
  • Grayscale = 1/3 the features
```

### Why It Works

**Feature extraction** transforms images (2D pixel grids) into numeric vectors that ML algorithms can process. Two approaches exist:

**1. Statistical Features (Compact Representation)**

Extract summary statistics describing the image:

- **Dimensions**: Width, height, aspect ratio
- **Color statistics**: Mean and standard deviation of each channel
- **Brightness**: Overall luminance
- **Edge density**: Proportion of edge pixels (texture measure)

Advantages:

- **Small**: 11 values regardless of image size
- **Fast**: Constant time extraction
- **Interpretable**: Each feature has clear meaning
- **Effective**: Sufficient for many classification tasks

**2. Raw Pixel Features (Detailed Representation)**

Flatten the entire image into a 1D array:

```
32×32 grayscale image → 1024 features (32 × 32 × 1)
32×32 RGB image → 3072 features (32 × 32 × 3)
```

The `flattenToVector()` method:

1. Resizes image to target dimensions (e.g., 32×32)
2. Converts to grayscale (optional)
3. Normalizes pixel values to 0-1 range
4. Concatenates all pixels into a 1D array

Advantages:

- **Detailed**: Preserves spatial information
- **Flexible**: Works with neural networks
- **No feature engineering**: Let the model learn patterns

Trade-offs:

- **Large**: 1024+ features vs. 11
- **Slower**: More data to process
- **Less interpretable**: Features are individual pixels

**Edge Density** measures image complexity using edge detection. Edges represent boundaries between regions (object edges, texture patterns). High edge density suggests:

- Complex scenes (urban environments, forests)
- High texture (fabric, terrain)
- Multiple objects

Low edge density suggests:

- Simple scenes (clear sky, smooth surfaces)
- Portraits (smooth skin tones)
- Minimal texture

ML use cases for features:

- **K-Nearest Neighbors**: Distance-based on feature vectors
- **Decision Trees/Random Forests**: Split on feature thresholds
- **SVM**: Find separating hyperplane in feature space
- **Neural Networks**: Learn features automatically from raw pixels

### Troubleshooting

**Feature extraction is very slow**

- Large images take longer to process
- Use sampling: the extractor samples every nth pixel for images >100K pixels
- Resize images first: `$processor->resize($image, 640, 480)`

**Standard deviation is zero or very low**

- Image has uniform color (solid color background)
- This is valid—low std means low variation
- Consider this as information: smooth images vs. textured images

**Flattened vectors are too large for my algorithm**

- Reduce target size: `flattenToVector($image, 16, 16)` → 256 features
- Use grayscale instead of RGB: 768 → 256 features
- Use statistical features instead: 3072 → 11 features
- Apply dimensionality reduction (PCA) after extraction

**Histograms have uneven distributions**

- This is normal and informative!
- Outdoor images: high in mid-range greens
- Indoor images: peaks at specific lighting levels
- High-contrast images: peaks at extremes (0 and 255)
- Histogram shape itself is useful data for classification

## Step 7: Image Filters and Effects (~10 min)

### Goal

Apply filters (blur, sharpen, edge detection) and effects (sepia, sketch) for preprocessing, enhancement, and feature extraction.

### Actions

1. **Run the filters example**:

```bash
php 07-image-filters.php
```

This applies 20+ different filters and saves the results.

2. **Study the `ImageFilter` class** in [`ImageFilter.php`](../code/chapter-16/ImageFilter.php):

```php
# filename: ImageFilter.php (excerpt)
public function edgeDetect(\GdImage $image): \GdImage
{
    imagefilter($image, IMG_FILTER_EDGEDETECT);
    return $image;
}

public function convolution(\GdImage $image, array $matrix, float $divisor = 1, float $offset = 0): \GdImage
{
    imageconvolution($image, $matrix, $divisor, $offset);
    return $image;
}
```

3. **Understand convolution**: Filters work by applying a convolution matrix (kernel) to each pixel. The kernel is a small matrix (typically 3×3) that defines how surrounding pixels influence the result.

Edge detection kernel:

```
[-1, -1, -1]
[-1,  8, -1]
[-1, -1, -1]
```

This emphasizes pixels that differ from their neighbors (edges).

4. **Try creating custom filters** using the `convolution()` method with your own matrices.

### Expected Result

```
============================================================
 Image Filters and Effects
============================================================

============================================================
 1. Blur Effects
============================================================

✓ Saved: output/landscape_blur_1.jpg (1 blur passes)
✓ Saved: output/landscape_blur_3.jpg (3 blur passes)
✓ Saved: output/landscape_blur_5.jpg (5 blur passes)

✓ Saved: output/landscape_selective_blur.jpg (preserves edges better)

Blur is useful for:
  • Noise reduction
  • Background effects
  • Privacy (blurring faces/plates)

============================================================
 2. Sharpening
============================================================

✓ Saved: output/landscape_sharpen.jpg (built-in sharpen)
✓ Saved: output/landscape_sharpen_custom.jpg (custom sharpen)

============================================================
 3. Edge Detection
============================================================

✓ Saved: output/landscape_edges.jpg
✓ Saved: output/landscape_edge_enhance.jpg (enhanced, not isolated)

Edge detection is crucial for:
  • Object detection preprocessing
  • Feature extraction
  • Shape recognition

...

============================================================
 7. Custom Convolution Filters
============================================================

✓ Saved: output/landscape_custom_edge.jpg (custom edge matrix)

Convolution allows custom filters for:
  • Edge detection variants
  • Custom sharpening/blurring
  • Special effects
```

### Why It Works

**Image filters** modify pixel values based on their neighbors using **convolution**. For each pixel, the filter:

1. Extracts a 3×3 neighborhood of surrounding pixels
2. Multiplies each neighbor by the corresponding kernel value
3. Sums the results and divides by the divisor
4. Adds the offset and sets the new pixel value

Example: Gaussian blur kernel (simplified):

```
[1, 2, 1]
[2, 4, 2]
[1, 2, 1]
Divisor: 16 (sum of all values)
```

This averages each pixel with its neighbors, smoothing the image.

**Built-in filters** (via `imagefilter()`) include:

- `IMG_FILTER_GAUSSIAN_BLUR`: Reduces noise and detail
- `IMG_FILTER_EDGEDETECT`: Highlights edges, used in CV pipelines
- `IMG_FILTER_MEAN_REMOVAL`: Sharpens (emphasizes differences)
- `IMG_FILTER_EMBOSS`: Creates 3D-like appearance
- `IMG_FILTER_GRAYSCALE`: Converts to grayscale
- `IMG_FILTER_BRIGHTNESS`: Adjusts overall lightness
- `IMG_FILTER_CONTRAST`: Adjusts light/dark differences

ML preprocessing uses filters for:

- **Noise reduction**: Blur before feature extraction
- **Edge detection**: Detect shapes and boundaries
- **Data augmentation**: Create training variations (rotated, blurred, sharpened)
- **Normalization**: Standardize image appearance

### Troubleshooting

**Filters make images look worse**

- Some filters are destructive (edge detection discards most information)
- Save filtered versions separately; don't overwrite originals
- Multiple blur passes compound (3 passes ≈ 1 strong blur)

**Custom convolution has weird artifacts**

- Kernel values must be balanced
- Divisor should usually equal sum of positive values
- Negative values can cause unexpected results (use carefully)
- Test kernels on small regions first

**Edge detection output is mostly black**

- This is correct! Most pixels aren't edges
- The white pixels show detected edges
- Invert with `imagefilter($image, IMG_FILTER_NEGATE)` if needed

**Filters are slow on large images**

- Convolution is O(width × height × kernel_size²)
- Resize images first if appropriate
- Use simpler filters (Gaussian blur vs. multiple selective blurs)

## Step 8: Preparing Images for Machine Learning (~12 min)

### Goal

Create a complete preprocessing pipeline that standardizes images, extracts features, normalizes values, and outputs ML-ready datasets.

### Actions

1. **Run the ML preparation example**:

```bash
php 08-ml-preparation.php
```

2. **Understand the preprocessing pipeline**:

```php
# filename: 08-ml-preparation.php (excerpt)
function preprocessImageForML(
    string $imagePath,
    ImageLoader $loader,
    ImageProcessor $processor,
    ColorConverter $converter,
    FeatureExtractor $extractor,
    int $targetSize = 32,
    bool $useStatistical = false
): array {
    // 1. Load image
    $img = $loader->load($imagePath);

    // 2. Standardize size
    $img = $processor->resize($img, $targetSize, $targetSize, false);

    // 3. Convert to grayscale
    $img = $converter->toGrayscaleLuminosity($img);

    // 4. Extract features
    if ($useStatistical) {
        $features = $extractor->extractAllFeatures($img);
    } else {
        $features = $extractor->flattenToVector($img, $targetSize, $targetSize, true);
    }

    imagedestroy($img);
    return $features;
}
```

3. **Try both feature extraction methods** (statistical vs. raw pixels) and understand when to use each.

4. **Create your own preprocessing pipeline** for a specific use case (face detection, object classification, etc.).

### Expected Result

```
============================================================
 Preparing Images for Machine Learning
============================================================

============================================================
 1. Standardizing Image Dimensions
============================================================

ML models require consistent input dimensions.
Let's standardize all images to 128×128:

sample.jpg     :  400× 300 → 128×128
landscape.jpg  :  600× 400 → 128×128
face.jpg       :  300× 300 → 128×128

Benefits of standardization:
  • All images have same dimensions
  • Fixed input size for neural networks
  • Batch processing becomes possible
  • Consistent feature vector length

============================================================
 2. Converting to Grayscale
============================================================

✓ sample.jpg → grayscale
✓ landscape.jpg → grayscale
✓ face.jpg → grayscale

Data reduction: 128×128×3 = 49152 values → 128×128×1 = 16384 values
That's 66% less data!

============================================================
 3. Flattening to Feature Vectors
============================================================

Converting images to 1D vectors for ML algorithms:

sample.jpg     : 1024 features [0.804, 0.863, ..., 0.612]
landscape.jpg  : 1024 features [0.337, 0.459, ..., 0.576]
face.jpg       : 1024 features [0.765, 0.678, ..., 0.596]

These vectors can be used with:
  • K-Nearest Neighbors (KNN)
  • Support Vector Machines (SVM)
  • Neural Networks

...

============================================================
 6. Feature Normalization
============================================================

Before normalization (sample.jpg):
  [400.0, 300.0, 1.3, 127.4, 95.2, ...]

After normalization (sample.jpg):
  [0.667, 0.500, 0.500, 0.500, 0.374, ...]

Why normalize?
  • Features on same scale (0-1)
  • Prevents large values from dominating
  • Improves ML algorithm convergence
  • Required for many algorithms (SVM, neural networks)
```

### Why It Works

**ML preprocessing** transforms raw images into standardized, numeric representations that algorithms can process. The pipeline consists of:

**1. Standardization**

- All images resized to same dimensions (e.g., 128×128)
- Enables batch processing (process multiple images together)
- Fixed input size required by neural networks
- Consistent feature vector length for distance-based algorithms

**2. Color Reduction**

- Convert RGB to grayscale (3 channels → 1 channel)
- Reduces dimensionality by 66%
- Faster training, lower memory usage
- Sufficient when color isn't discriminative (digits, text, shapes)

**3. Feature Extraction**

- **Raw pixels**: Flatten 32×32 grayscale = 1024 features
- **Statistical**: Extract 11 summary features
- Choice depends on algorithm and task complexity

**4. Normalization**

- Scale all features to same range (typically 0-1)
- Formula: `normalized = (value - min) / (max - min)`
- Prevents features with large values (like width=1920) from dominating small values (like average brightness=0.5)
- Essential for:
  - Gradient descent (neural networks)
  - Distance metrics (KNN, SVM)
  - Regularization effectiveness

**5. Dataset Format**

```php
$dataset = [
    ['features' => [0.5, 0.7, ...], 'label' => 'cat'],
    ['features' => [0.2, 0.9, ...], 'label' => 'dog'],
    // ...
];
```

**Complete ML workflow**:

1. **Collect** images with labels
2. **Preprocess** using pipeline
3. **Split** into train/test sets (80%/20%)
4. **Train** model on training set
5. **Evaluate** on test set
6. **Deploy** for predictions on new images

**When to use each feature type**:

| Feature Type                    | Best For                                 | Advantages                 | Disadvantages  |
| ------------------------------- | ---------------------------------------- | -------------------------- | -------------- |
| **Statistical (11 features)**   | Simple classification, quick prototyping | Fast, interpretable, small | Limited detail |
| **Raw Pixels (1024+ features)** | Complex patterns, neural networks        | Detailed, spatial info     | Larger, slower |

### Troubleshooting

**Normalization causes all features to become 0 or 1**

- Features have no variation (all same value)
- Check if images are identical or very similar
- Verify feature extraction is working correctly
- Some features naturally have low range (edge density 0-0.3)

**Different images produce identical feature vectors**

- Images may be too similar after preprocessing
- Increase target size (32×32 → 64×64) for more detail
- Use raw pixels instead of statistical features
- Check if grayscale conversion removes discriminative color info

**Model training is very slow**

- Large feature vectors (use smaller resize dimensions)
- Too many features (use statistical instead of raw pixels)
- No normalization (causes slow convergence)
- Try dimensionality reduction (PCA)

**Poor classification accuracy**

- Images may need more preprocessing:
  - Increase contrast
  - Apply edge detection
  - Extract different features (color histograms)
- Target size may be too small (information loss)
- Labels may be incorrect or inconsistent
- Need more training data

## Step 9: Image Augmentation for ML Training (~10 min)

### Goal

Learn how to generate multiple variations of training images to expand limited datasets and improve model generalization through data augmentation.

### What You'll Build

An `ImageAugmentor` class that creates realistic variations of images through random transformations, significantly expanding training datasets without collecting new images.

### Background: Why Augmentation Matters

When training machine learning models from scratch, you often face the **cold start problem**: not enough data. Collecting thousands of labeled images is expensive and time-consuming. Data augmentation solves this by generating new training examples from existing ones.

**Real-world impact:**

- Turn 100 images into 1,000+ training samples
- Teach models that a cat is still a cat when flipped, rotated, or in different lighting
- Reduce overfitting (when model memorizes training data instead of learning patterns)
- Improve model robustness to real-world variations

**When to use augmentation:**

✓ Training models from scratch with limited data  
✓ Fine-tuning pre-trained models on custom datasets  
✓ Creating robust models for varied real-world conditions  
✓ Balancing imbalanced datasets (generate more examples of rare classes)

**When NOT to use:**

✗ Inference/prediction (always use original images)  
✗ Testing/validation sets (need unmodified data to measure true performance)  
✗ When augmented variations aren't realistic (e.g., don't flip text images)  
✗ Tasks where orientation is critical (medical scans, document OCR)

### Common Augmentation Techniques

Augmentation techniques simulate real-world variations you'd encounter naturally:

**1. Geometric Transformations**

- **Flips**: Horizontal (common), vertical (rare in natural photos)
- **Rotations**: 90°, 180°, 270° (preserve content), or small random angles (±15°)
- **Crops**: Random sections of image (simulates different viewing distances)
- **Zoom**: Scale in/out (simulates different focal lengths)

**2. Color/Brightness Adjustments**

- **Brightness**: Simulate different lighting conditions (±20-40 levels)
- **Contrast**: Enhance or reduce differences between light/dark areas
- **Saturation**: Adjust color intensity
- **Hue shifts**: Slight color variations (use sparingly)

**3. Filtering Effects**

- **Blur**: Simulate motion or focus issues
- **Noise**: Add random pixel variations (sensor noise)
- **Sharpening**: Enhance edges slightly

**Strategy considerations:**

| Technique       | Use For                      | Avoid For                    |
| --------------- | ---------------------------- | ---------------------------- |
| Horizontal flip | Most natural objects, scenes | Text, asymmetric objects     |
| Vertical flip   | Abstract patterns            | Most natural scenes (rare)   |
| 90° rotations   | Objects without natural "up" | Faces, buildings, landscapes |
| Brightness      | Outdoor scenes, lighting     | Medical images (diagnostic)  |
| Random crops    | Object detection, zoom       | Full-scene classification    |

### Code Example

Let's build a complete augmentation pipeline:

```php
# filename: 09-data-augmentation.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/ImageLoader.php';
require_once __DIR__ . '/ImageProcessor.php';
require_once __DIR__ . '/ColorConverter.php';
require_once __DIR__ . '/ImageAugmentor.php';

$loader = new ImageLoader();
$processor = new ImageProcessor();
$converter = new ColorConverter();
$augmentor = new ImageAugmentor($processor, $converter);

// Load original image
$original = $loader->load(__DIR__ . '/data/face.jpg');

// 1. Generate standard augmentation set (reproducible)
$standardSet = $augmentor->generateStandardSet($original);
// Returns: original, flip_horizontal, flip_vertical, rotate_90,
//          rotate_180, brightness_+30, brightness_-30, contrast_high

echo "Generated " . count($standardSet) . " standard variations\n";

foreach ($standardSet as $name => $augmentedImage) {
    $loader->save($augmentedImage, "output/augmented/face_{$name}.jpg");
    imagedestroy($augmentedImage);
}

// 2. Generate random augmentations with custom config
$config = [
    'flip' => true,
    'rotate' => true,
    'rotation_angles' => [0, 15, 30, 45, 90, 180, 270],
    'rotate_probability' => 70,  // 70% chance
    'brightness' => true,
    'brightness_range' => ['min' => -40, 'max' => 40],
    'brightness_probability' => 60,
    'contrast' => true,
    'contrast_range' => ['min' => -25, 'max' => 25],
    'contrast_probability' => 60,
    'crop' => true,
    'crop_scale_range' => ['min' => 0.8, 'max' => 1.0],  // 80-100%
    'crop_probability' => 50,
    'zoom' => true,
    'zoom_range' => ['min' => 0.9, 'max' => 1.2],  // 90-120%
    'zoom_probability' => 40,
];

// Generate 10 random augmented variations
$randomAugmented = $augmentor->augment($original, 10, $config);

foreach ($randomAugmented as $i => $augmentedImage) {
    $filename = sprintf("face_random_%02d.jpg", $i + 1);
    $loader->save($augmentedImage, "output/augmented/{$filename}");
    imagedestroy($augmentedImage);
}

echo "Generated 10 random augmented variations\n";

// 3. Batch processing multiple images
$allImages = glob(__DIR__ . '/data/*.jpg');
$totalGenerated = 0;

foreach ($allImages as $imagePath) {
    $img = $loader->load($imagePath);
    $augmented = $augmentor->augment($img, 5);  // 5 per image

    $basename = basename($imagePath, '.jpg');
    foreach ($augmented as $i => $augImg) {
        $filename = sprintf("%s_aug_%02d.jpg", $basename, $i + 1);
        $loader->save($augImg, "output/batch/{$filename}");
        imagedestroy($augImg);
        $totalGenerated++;
    }

    imagedestroy($img);
}

echo "Batch processed " . count($allImages) . " images\n";
echo "Generated {$totalGenerated} augmented training samples\n";

imagedestroy($original);
```

**Run the example:**

```bash
php code/chapter-16/09-data-augmentation.php
```

**Expected output:**

```
═══════════════════════════════════════════
Image Augmentation for ML Training
═══════════════════════════════════════════

Loading: /path/to/face.jpg

Original image: 400×400

───────────────────────────────────────────
1. Standard Augmentation Set
───────────────────────────────────────────

Generating standard augmentation variations...

Standard set generation: 0.12s

Generated 8 variations:

  ✓ face_original.jpg (400×400)
  ✓ face_flip_horizontal.jpg (400×400)
  ✓ face_flip_vertical.jpg (400×400)
  ✓ face_rotate_90.jpg (400×400)
  ✓ face_rotate_180.jpg (400×400)
  ✓ face_brightness_+30.jpg (400×400)
  ✓ face_brightness_-30.jpg (400×400)
  ✓ face_contrast_high.jpg (400×400)

Standard augmentations include:
  • Original (baseline)
  • Horizontal and vertical flips
  • 90° and 180° rotations
  • Brightness adjustments (±30)
  • Contrast variations

───────────────────────────────────────────
2. Random Augmentation Pipeline
───────────────────────────────────────────

Generating 10 random augmented variations...

Random augmentation (10 images): 0.28s

Saving random augmentations...
  ✓ face_random_01.jpg
  ✓ face_random_02.jpg
  ✓ face_random_03.jpg
  ...

───────────────────────────────────────────
3. Dataset Expansion Example
───────────────────────────────────────────

Original dataset: 1 image
After augmentation: 18 images
Expansion factor: 18x

Benefits of augmentation:
  • Increases effective dataset size
  • Teaches model to recognize objects from different angles
  • Reduces overfitting by introducing variations
  • Improves model generalization
  • Makes models robust to lighting changes
```

### Key Components Explained

**1. ImageAugmentor Class**

```php
final class ImageAugmentor
{
    public function augment(
        \GdImage $image,
        int $count = 5,
        array $config = []
    ): array {
        $augmented = [];

        for ($i = 0; $i < $count; $i++) {
            $variant = $this->cloneImage($image);

            // Apply random transformations based on probability
            if ($config['flip'] && rand(0, 1)) {
                $variant = $this->randomFlip($variant);
            }

            if ($config['rotate'] &&
                rand(0, 100) < $config['rotate_probability']) {
                $variant = $this->randomRotation(
                    $variant,
                    $config['rotation_angles']
                );
            }

            // ... more transformations

            $augmented[] = $variant;
        }

        return $augmented;
    }
}
```

**2. Random Transformations**

Each transformation applies with a specified probability, creating natural variation:

```php
// 50% chance of flip, 70% chance of rotation, etc.
if (rand(0, 100) < $probability) {
    $image = applyTransformation($image);
}
```

**3. Preserving Image Quality**

Important: Clone images before transforming to preserve original:

```php
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
```

### Why It Works

**Data augmentation is effective because:**

**1. Simulates Real-World Variations**

- Photos taken from different angles → rotations
- Different camera settings → brightness/contrast
- Various distances → crops and zoom
- Different lighting conditions → brightness adjustments

Models trained on augmented data perform better on real-world images because they've "seen" similar variations during training.

**2. Regularization Effect**

Augmentation acts as a form of regularization:

- Model can't memorize augmented images (they're different each epoch)
- Forces learning of robust features that work across variations
- Reduces overfitting without reducing model capacity
- Similar effect to dropout but at the data level

**3. Class Balance**

If you have 1,000 cat images but only 100 dog images:

- Augment dogs 10x → 1,000 dog variations
- Balanced training prevents model bias toward cats
- Better performance on minority classes

**4. Computational Efficiency**

**Strategy A: Collect more data**

- Expensive (time, cost, labeling effort)
- May require months to gather
- Limited by availability

**Strategy B: Augment existing data**

- Free (computation cost only)
- Instant dataset expansion
- Under your control

**Example: Training with augmentation**

```php
// Training loop with on-the-fly augmentation
$epochs = 10;
$augmentations_per_image = 5;

for ($epoch = 0; $epoch < $epochs; $epoch++) {
    foreach ($trainingImages as $image) {
        // Generate augmented variations each epoch
        $augmented = $augmentor->augment($image, $augmentations_per_image);

        foreach ($augmented as $augImg) {
            $features = extractFeatures($augImg);
            $model->trainOnSample($features, $image['label']);
        }
    }
}

// Result: Model trained on original_count × augmentations_per_image × epochs
// distinct variations, learning robust patterns
```

### Augmentation Strategies by Use Case

**Conservative (Medical, Scientific Images)**

```php
$conservativeConfig = [
    'flip' => true,              // Only horizontal
    'rotate' => false,           // Orientation matters
    'brightness' => true,
    'brightness_range' => ['min' => -10, 'max' => 10],  // Minimal
    'brightness_probability' => 30,
    'contrast' => false,         // Diagnostic info
    'crop' => false,             // Need full image
    'zoom' => false,
];
```

**Aggressive (Object Detection, Robust Recognition)**

```php
$aggressiveConfig = [
    'flip' => true,
    'rotate' => true,
    'rotation_angles' => range(0, 360, 15),  // Every 15 degrees
    'rotate_probability' => 90,
    'brightness' => true,
    'brightness_range' => ['min' => -50, 'max' => 50],
    'brightness_probability' => 80,
    'contrast' => true,
    'contrast_range' => ['min' => -30, 'max' => 30],
    'contrast_probability' => 80,
    'crop' => true,
    'crop_probability' => 70,
    'zoom' => true,
    'zoom_probability' => 60,
];
```

**Balanced (General Purpose)**

```php
$balancedConfig = [
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
    'crop_probability' => 40,
    'zoom' => false,
];
```

### Best Practices

**1. Apply During Training Only**

```php
// ✓ CORRECT
$trainingImages = augment($originalTrainingSet);
$testImages = $originalTestSet;  // NO augmentation

// ✗ WRONG
$trainingImages = augment($originalTrainingSet);
$testImages = augment($originalTestSet);  // Don't augment test data!
```

**2. Keep Validation/Test Sets Pristine**

- Augmentation artificially improves metrics if applied to test data
- Test on unmodified images to measure real-world performance
- Split data BEFORE augmentation: train (80%) → augment, test (20%) → no augmentation

**3. Save or Generate On-The-Fly?**

**Pre-generated (save augmented images):**

✓ Faster training (no computation during training)  
✓ Reproducible experiments  
✗ More disk space (10x the images)  
✗ Fixed variations (same each epoch)

**On-the-fly (generate during training):**

✓ Less disk space (store only originals)  
✓ Different variations each epoch (better regularization)  
✗ Slower training (augmentation overhead)  
✗ Requires careful random seed management

**Recommendation**: Pre-generate for small datasets (<10K images), on-the-fly for large datasets.

**4. Match Augmentation to Domain**

| Domain            | Augmentation Strategy                    | Avoid                            |
| ----------------- | ---------------------------------------- | -------------------------------- |
| Natural photos    | All techniques (flips, rotate, lighting) | Extreme rotations (>30°)         |
| Text/Documents    | Slight rotation (±5°), brightness        | Flips, large rotations           |
| Medical scans     | Minimal (brightness only)                | Most transformations             |
| Satellite imagery | Rotations, flips, zoom                   | Color adjustments                |
| Product photos    | Lighting, zoom, slight rotation          | Vertical flips                   |
| Faces             | Flips, lighting, slight rotation (±15°)  | Vertical flips, extreme rotation |

**5. Validate Augmentations Visually**

Always inspect augmented images to ensure they look realistic:

```php
// Save first few augmented samples for manual review
$samples = $augmentor->augment($image, 5);
foreach ($samples as $i => $sample) {
    $loader->save($sample, "review/sample_{$i}.jpg");
}
// Open review/ directory and check if augmentations look natural
```

### Troubleshooting

**Augmented images look unrealistic**

- Reduce transformation intensity (smaller brightness range, fewer rotation angles)
- Lower probabilities (apply transformations less frequently)
- Disable problematic transformations (e.g., vertical flips for natural scenes)
- Check if rotations are creating black borders (increase fill color)

**Model accuracy doesn't improve with augmentation**

- You may already have sufficient data
- Augmentations may not match real-world variations your model encounters
- Try different augmentation strategies (e.g., focus on lighting if that's the main variation)
- Ensure test set isn't augmented (would hide problems)
- Model may be underfitting (too simple for data complexity)

**Training takes much longer**

- Reduce augmentations per image (10 → 5)
- Pre-generate augmented dataset instead of on-the-fly
- Disable expensive transformations (complex rotations, large crops)
- Consider using GPU for augmentation if available

**Memory errors during batch augmentation**

- Process images one at a time instead of loading all
- Destroy images immediately after saving: `imagedestroy($augmentedImage)`
- Reduce output quality (JPEG quality 85 instead of 95)
- Check PHP memory_limit (increase if needed)

**Augmented images have quality loss**

- Use PNG for lossless storage instead of JPEG
- Increase JPEG quality parameter (85-95)
- Minimize cascading transformations (each operation degrades quality)
- Apply all transformations in one pass when possible

## Exercises

Test your understanding with these practical exercises. Solutions are in the [`solutions/`](../code/chapter-16/solutions/) directory.

### Exercise 1: Image Analyzer

**Goal**: Create a comprehensive image analysis tool that extracts and displays all available information about an image.

Create a command-line script called `image-analyzer.php` that:

**Requirements:**

1. Accepts an image filepath as a command-line argument
2. Loads the image and displays:
   - Basic information (dimensions, format, file size)
   - Color analysis (average color, dominant color)
   - Statistical features (mean and std dev of each channel)
   - Edge density (texture complexity)
   - Color histogram for each channel
   - Brightness analysis and classification
   - Color balance assessment
3. Provides an assessment of image type (landscape, portrait, abstract, etc.) based on features
4. Suggests preprocessing steps for ML based on image characteristics
5. Handles errors gracefully (file not found, unsupported format)

**Validation**: Test with all three sample images:

```bash
php image-analyzer.php data/landscape.jpg
php image-analyzer.php data/face.jpg
php image-analyzer.php data/sample.jpg
```

Expected output should include all metrics and a summary assessment.

**Solution**: [`solutions/exercise1-image-analyzer.php`](../code/chapter-16/solutions/exercise1-image-analyzer.php)

### Exercise 2: Thumbnail Generator

**Goal**: Build a production-ready thumbnail generator that creates multiple sizes while maintaining aspect ratio.

Create `thumbnail-generator.php` that:

**Requirements:**

1. Accepts an image path and optional output directory
2. Generates thumbnails in these sizes:
   - Small: 150×150 (maintaining aspect ratio)
   - Medium: 300×300 (maintaining aspect ratio)
   - Large: 600×600 (maintaining aspect ratio)
   - Square Small: 100×100 (center crop)
   - Square Medium: 250×250 (center crop)
3. Saves thumbnails with descriptive filenames (e.g., `landscape_small.jpg`)
4. Displays file sizes and compression ratios
5. Optionally generates an HTML preview page showing all thumbnails
6. Reports processing time and space savings

**Validation**: Run with a sample image:

```bash
php thumbnail-generator.php data/landscape.jpg output/
```

Verify that 5 thumbnail files are created with correct dimensions.

**Solution**: [`solutions/exercise2-thumbnail-generator.php`](../code/chapter-16/solutions/exercise2-thumbnail-generator.php)

### Exercise 3: Image Feature Comparison

**Goal**: Compare two images by extracting and analyzing their features to calculate a similarity score.

Create `feature-comparison.php` that:

**Requirements:**

1. Accepts two image paths as command-line arguments
2. Extracts features from both images:
   - Dimensions and aspect ratio
   - Average and dominant colors
   - Color histograms
   - Edge density
   - Statistical features
3. Calculates similarity scores for each feature category:
   - Aspect ratio similarity
   - Color similarity (Euclidean distance in RGB space)
   - Histogram correlation
   - Texture similarity
4. Computes an overall weighted similarity score
5. Provides interpretation (very similar, somewhat similar, different)
6. Suggests use cases based on similarity level

**Validation**: Compare similar and dissimilar images:

```bash
# Compare different images (should be low similarity)
php feature-comparison.php data/sample.jpg data/landscape.jpg

# Compare resized versions of same image (should be high similarity)
php feature-comparison.php data/sample.jpg output/sample_resized.jpg
```

**Solution**: [`solutions/exercise3-feature-comparison.php`](../code/chapter-16/solutions/exercise3-feature-comparison.php)

### Bonus Challenge: Batch Image Processor

Build a tool that processes an entire directory of images, applying standardized preprocessing:

**Requirements:**

- Scan directory for image files
- Resize all to standard dimensions (e.g., 1920×1080)
- Optimize quality (JPEG 85%)
- Convert to consistent format (WEBP)
- Generate thumbnails automatically
- Create a manifest file with metadata
- Show progress and statistics

This exercise combines all chapter concepts into a practical production tool!

## Troubleshooting

Common issues and solutions for image processing in PHP:

### GD Extension Issues

**Problem**: "Call to undefined function imagecreatefromjpeg()"

**Cause**: GD extension is not installed or not enabled.

**Solution**:

Ubuntu/Debian:

```bash
sudo apt-get update
sudo apt-get install php8.4-gd
sudo systemctl restart apache2  # or php8.4-fpm
```

macOS (Homebrew):

```bash
brew install php@8.4  # Includes GD
brew services restart php@8.4
```

Windows:

1. Edit `php.ini`
2. Uncomment: `extension=gd`
3. Restart web server

Verify:

```bash
php -m | grep gd
php -r "var_dump(gd_info());"
```

### Memory Limits

**Problem**: "Allowed memory size exhausted"

**Cause**: Image processing is memory-intensive. A 4000×3000 image requires ~46 MB uncompressed in memory.

**Solution**:

Temporary (per-script):

```php
ini_set('memory_limit', '256M');
```

Permanent (php.ini):

```ini
memory_limit = 256M
```

Best practices:

- Resize images before processing: smaller dimensions = less memory
- Destroy images when done: `imagedestroy($image)`
- Process images sequentially, not all at once
- Use streaming for very large files

### Image Quality Issues

**Problem**: Images become blurry or lose quality after processing

**Cause**: Multiple JPEG saves compound lossy compression; low quality settings; excessive resizing.

**Solution**:

- Use PNG for intermediate steps (lossless)
- Save JPEG once at the end
- Use quality 85-95 for final saves
- Don't upscale significantly (adds no detail)

```php
// Good: Save once with high quality
$processor->resize($image, 800, 600);
$converter->toGrayscale($image);
$loader->save($image, 'output.jpg', 90);

// Bad: Multiple saves lose quality
$loader->save($image, 'temp.jpg', 75);
$image = $loader->load('temp.jpg');
$loader->save($image, 'final.jpg', 75);
```

### File Format Errors

**Problem**: "Failed to create image from file" or "Unsupported image type"

**Cause**: File corrupted, wrong extension, unsupported format.

**Solution**:

1. Verify file is valid: open in image viewer
2. Check format support: `php -r "var_dump(gd_info());"`
3. Convert if needed: `convert input.tiff output.jpg` (ImageMagick)
4. Validate with getimagesize():

```php
$info = @getimagesize($filepath);
if ($info === false) {
    throw new \RuntimeException("Not a valid image file");
}
```

### Permission Errors

**Problem**: "Failed to save image" or "Permission denied"

**Cause**: Output directory doesn't exist or isn't writable.

**Solution**:

```bash
# Create directory with proper permissions
mkdir -p output
chmod 755 output

# Or in PHP
if (!is_dir('output')) {
    mkdir('output', 0755, true);
}

if (!is_writable('output')) {
    throw new \RuntimeException('Output directory not writable');
}
```

### Color/Transparency Issues

**Problem**: PNG images with transparency show black background after processing

**Cause**: Need to preserve alpha channel.

**Solution**:

```php
// Enable alpha blending and save alpha channel
imagealphablending($newImage, false);
imagesavealpha($newImage, true);

// Then copy/process the image
imagecopyresampled(/* ... */);
```

### Performance Issues

**Problem**: Image processing is very slow

**Solutions**:

- **Resize first**: Process smaller dimensions when possible
- **Sample pixels**: For analysis, sample every nth pixel
- **Batch efficiently**: Reuse objects, avoid repeated initialization
- **Use appropriate formats**: JPEG faster than PNG for photos
- **Consider alternatives**: For heavy CV, integrate Python/OpenCV

```php
// Efficient: Resize before expensive operations
$small = $processor->resize($image, 640, 480);
$features = $extractor->extractAllFeatures($small);

// Inefficient: Extract from full resolution
$features = $extractor->extractAllFeatures($image);  // Slow!
```

## Wrap-up

Congratulations! You've mastered the fundamentals of computer vision in PHP. Let's recap what you've accomplished:

**✓ Core Concepts**

- Understood images as 2D arrays of RGB pixels
- Learned how PHP's GD extension provides low-level image access
- Recognized the memory and performance implications of image processing

**✓ Image Manipulation**

- Loaded and saved images in multiple formats (JPEG, PNG, GIF, WEBP)
- Resized images while maintaining or ignoring aspect ratios
- Cropped, rotated, flipped, and scaled images
- Applied filters for blur, sharpen, and edge detection
- Created artistic effects (sepia, sketch, emboss)

**✓ Color Processing**

- Converted images to grayscale using luminosity weighting
- Extracted individual color channels (R, G, B)
- Analyzed average and dominant colors
- Adjusted brightness and contrast
- Understood why grayscale reduces ML complexity

**✓ Data Augmentation**

- Generated training variations through random transformations
- Applied flips, rotations, brightness, contrast, crops, and zoom
- Expanded limited datasets (1 image → 18+ variations)
- Learned when to use augmentation (training) vs. when not (testing)
- Implemented conservative vs. aggressive augmentation strategies

**✓ Feature Extraction**

- Extracted statistical features (mean, std dev, aspect ratio)
- Calculated edge density as a texture measure
- Generated color histograms showing distribution
- Flattened images to feature vectors for ML algorithms
- Compared statistical vs. raw pixel features

**✓ ML Preparation**

- Standardized image dimensions for consistent input
- Normalized feature values to 0-1 range
- Created complete preprocessing pipelines
- Built ML-ready datasets with features and labels
- Understood when to use each feature type

**✓ Practical Skills**

- Built an image analyzer extracting comprehensive metrics
- Created a thumbnail generator with multiple sizes
- Implemented feature comparison for similarity detection
- Learned to debug common GD extension issues
- Recognized PHP's capabilities and limitations for CV

### Real-World Applications

You can now build:

**Content Management**

- User photo uploads with automatic resizing
- Thumbnail generation for galleries
- Image optimization for web delivery
- Automatic format conversion

**Machine Learning Preparation**

- Image preprocessing pipelines
- Feature extraction for classification
- Dataset standardization
- Batch processing for training data

**Image Analysis**

- Color-based categorization
- Duplicate image detection
- Similarity search
- Quality assessment

**Content Moderation**

- Blur detection (blurry images)
- Brightness analysis (over/underexposed)
- Dimension validation
- Format verification

### What's Next

Chapter 17 builds on these foundations:

- Use pre-trained deep learning models for classification
- Implement object detection in images
- Integrate cloud vision APIs
- Build production CV pipelines

But the skills you've learned here—loading images, extracting features, preprocessing for ML—underpin everything in computer vision. Whether you use PHP's GD, integrate Python/OpenCV, or call cloud APIs, understanding pixels, channels, and features is essential.

### Key Takeaways

**1. Images are just data**: 2D grids of numbers that you can analyze, transform, and extract information from programmatically.

**2. Preprocessing matters**: Standardizing dimensions, converting to grayscale, and normalizing values significantly impacts ML performance.

**3. Feature engineering is powerful**: Even simple statistical features (11 values) can enable effective classification without deep learning.

**4. PHP has limitations**: For heavy computer vision (real-time object detection, complex CNNs), integrate Python/OpenCV or use cloud services. But PHP excels at web-oriented image tasks.

**5. Understand tradeoffs**: Memory vs. quality, speed vs. accuracy, statistical vs. raw features, PHP vs. Python—choose based on requirements.

You're now ready to tackle image classification, apply pre-trained models, and build intelligent image-processing features into your PHP applications!

## Further Reading

### Official Documentation

- [PHP GD Extension](https://www.php.net/manual/en/book.image.php) — Complete reference for all GD functions
- [PHP Image Functions](https://www.php.net/manual/en/ref.image.php) — Individual function documentation
- [PHP 8.4 Release Notes](https://www.php.net/releases/8.4/) — Latest PHP features and changes

### Computer Vision Fundamentals

- [Introduction to Computer Vision (Coursera)](https://www.coursera.org/learn/introduction-computer-vision-watson-opencv) — Free course covering CV basics
- [Computer Vision: Algorithms and Applications (Szeliski)](https://szeliski.org/Book/) — Comprehensive textbook, free online
- [OpenCV Tutorials](https://docs.opencv.org/4.x/d9/df8/tutorial_root.html) — Though Python-focused, concepts apply universally

### Image Processing Techniques

- [Digital Image Processing (Gonzalez & Woods)](https://www.imageprocessingplace.com/) — Classic textbook on image processing
- [Image Kernels Explained](https://setosa.io/ev/image-kernels/) — Interactive visualization of convolution filters
- [Color Space Conversions](https://www.niwa.nu/2013/05/math-behind-colorspace-conversions-rgb-hsl/) — Mathematics of color space transformations

### Machine Learning with Images

- [Stanford CS231n: Convolutional Neural Networks](http://cs231n.stanford.edu/) — Deep learning for visual recognition
- [Image Classification with Scikit-Learn](https://scikit-learn.org/stable/auto_examples/classification/plot_digits_classification.html) — ML examples similar to our approach
- [Feature Engineering for Images](https://towardsdatascience.com/feature-engineering-for-images-a-valuable-introduction-to-the-hog-feature-descriptor-13c8e3d9b1e2) — Beyond raw pixels

### PHP-Specific Resources

- [Intervention Image](http://image.intervention.io/) — Higher-level PHP image manipulation library
- [Imagine](https://imagine.readthedocs.io/) — Object-oriented image manipulation for PHP
- [PHP-ML Documentation](https://php-ml.readthedocs.io/) — Pure PHP machine learning library used in Chapter 8

### Advanced Topics (Next Steps)

- **Deep Learning for CV**: Use Chapter 17's pre-trained models
- **OpenCV with PHP**: FFI or subprocess integration patterns
- **Cloud Vision APIs**: Google Cloud Vision, AWS Rekognition, Azure Computer Vision
- **Real-time Processing**: Video frame analysis, webcam integration

### Related Chapters

- [Chapter 12: Deep Learning with TensorFlow and PHP](/series/ai-ml-php-developers/chapters/12-deep-learning-with-tensorflow-and-php) — Using pre-trained models
- [Chapter 17: Image Classification Project with Pre-trained Models](/series/ai-ml-php-developers/chapters/17-image-classification-project-with-pre-trained-models) — Build a production-ready image classifier using cloud vision APIs and local ONNX models
- [Chapter 18: Object Detection and Recognition in PHP Applications](/series/ai-ml-php-developers/chapters/18-object-detection-and-recognition-in-php-applications) — Locate and identify multiple objects in images using YOLO and cloud APIs

### Tools and Libraries

- **ImageMagick**: More powerful than GD, CLI and PHP extension available
- **GraphicsMagick**: Fork of ImageMagick, optimized for batch processing
- **VIPS**: High-performance image processing library
- **WebP tools**: `cwebp`, `dwebp` for WEBP conversion

### Community

- [PHP Computer Vision on GitHub](https://github.com/topics/computer-vision?l=php) — PHP CV projects and libraries
- [Stack Overflow - PHP + Image Processing](https://stackoverflow.com/questions/tagged/php+image-processing) — Q&A community
- [Reddit r/PHP](https://www.reddit.com/r/PHP/) — General PHP discussions including CV topics
