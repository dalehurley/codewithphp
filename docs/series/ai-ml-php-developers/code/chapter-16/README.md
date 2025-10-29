# Chapter 16: Computer Vision Essentials for PHP Developers

Complete, runnable code examples for Chapter 16 of the AI/ML for PHP Developers series.

## Prerequisites

- PHP 8.4 or higher
- GD extension (usually included with PHP)
- Command line access

### Check Your Setup

```bash
php 02-check-extensions.php
```

This will verify you have everything needed and create the output directory.

## Quick Start

1. **Generate sample images** (if needed):

```bash
php generate-sample-images.php
```

This creates three sample images in the `data/` directory.

2. **Run all examples** in order:

```bash
composer test-all
```

Or run individual examples:

```bash
php 01-image-representation.php
php 03-load-save-images.php
php 04-image-manipulations.php
# ... etc
```

## What's Included

### Core Classes

- **ImageLoader.php** - Load, save, and inspect images in various formats
- **ImageProcessor.php** - Resize, crop, rotate, and transform images
- **ColorConverter.php** - Color space conversions and color analysis
- **FeatureExtractor.php** - Extract numeric features for machine learning
- **ImageFilter.php** - Apply filters and effects
- **ImageAugmentor.php** - Generate training variations through data augmentation

### Numbered Examples (Following Chapter Steps)

1. **01-image-representation.php** - Understanding images as data (pixels, channels, dimensions)
2. **02-check-extensions.php** - Verify PHP image processing setup
3. **03-load-save-images.php** - Load and save images in multiple formats
4. **04-image-manipulations.php** - Resize, crop, rotate, flip, and scale
5. **05-color-conversions.php** - Grayscale, channel extraction, color analysis
6. **06-feature-extraction.php** - Extract features for ML (statistics, histograms, vectors)
7. **07-image-filters.php** - Apply blur, sharpen, edge detection, and effects
8. **08-ml-preparation.php** - Prepare images for machine learning (standardize, normalize, vectorize)
9. **09-data-augmentation.php** - Generate augmented training variations to expand datasets

### Supporting Files

- **helpers.php** - Utility functions for displaying results
- **generate-sample-images.php** - Create test images

### Directories

- **data/** - Sample images (sample.jpg, landscape.jpg, face.jpg)
- **output/** - Processed images are saved here
- **solutions/** - Exercise solution code

## Running Examples

Each example is self-contained and can be run independently:

```bash
# See how images are represented
php 01-image-representation.php

# Load and save in different formats
php 03-load-save-images.php

# Try various filters
php 07-image-filters.php
```

Output files are saved to the `output/` directory.

## Exercises

See the chapter for exercise descriptions. Solutions are in the `solutions/` directory:

- **exercise1-image-analyzer.php** - Comprehensive image analysis tool
- **exercise2-thumbnail-generator.php** - Multi-size thumbnail creator
- **exercise3-feature-comparison.php** - Compare image features

Run exercises:

```bash
php solutions/exercise1-image-analyzer.php data/landscape.jpg
```

## Common Tasks

### Generate Sample Images

```bash
composer generate-images
# or
php generate-sample-images.php
```

### Check Setup

```bash
composer check-setup
# or
php 02-check-extensions.php
```

### Run All Examples

```bash
composer test-all
```

### Clean Output Directory

```bash
rm -f output/*
```

## Notes

### GD Extension

All examples use PHP's built-in GD extension, which is included with most PHP installations. If you get an error about missing GD:

**Ubuntu/Debian:**

```bash
sudo apt-get install php8.4-gd
```

**macOS (Homebrew):**

```bash
brew install php@8.4
```

**Windows:**

Enable `extension=gd` in your `php.ini` file.

### Memory Limits

Processing large images can consume significant memory. If you encounter memory limit errors:

1. Increase PHP memory limit in `php.ini`:

   ```ini
   memory_limit = 256M
   ```

2. Or set it at runtime in scripts:
   ```php
   ini_set('memory_limit', '256M');
   ```

### Image Formats

Supported formats depend on your GD compilation:

- JPEG - Almost always supported
- PNG - Almost always supported
- GIF - Usually supported
- WEBP - Supported in PHP 7.0+

Run `02-check-extensions.php` to see what's available on your system.

## Learning Path

Work through the examples in order:

1. Start with **01-image-representation.php** to understand image data
2. Verify your setup with **02-check-extensions.php**
3. Learn basic I/O with **03-load-save-images.php**
4. Practice manipulations with **04-image-manipulations.php**
5. Explore colors with **05-color-conversions.php**
6. Extract features with **06-feature-extraction.php**
7. Apply filters with **07-image-filters.php**
8. Prepare for ML with **08-ml-preparation.php**
9. Generate augmented variations with **09-data-augmentation.php**

Then try the exercises to reinforce your learning!

## Troubleshooting

**"Call to undefined function imagecreatefromjpeg()"**

- GD extension is not installed. See GD Extension section above.

**"Allowed memory size exhausted"**

- Increase PHP memory limit. See Memory Limits section above.

**"Failed to create image from: [file]"**

- File may be corrupted or in an unsupported format
- Check file exists and is readable
- Verify format support with `02-check-extensions.php`

**Output images are blank or corrupted**

- Check file permissions on output directory
- Ensure output directory exists: `mkdir -p output`
- Verify image was destroyed after saving

## Further Exploration

After completing these examples:

- Try processing your own images
- Experiment with different filter combinations
- Build a batch image processor
- Create a thumbnail generation service
- Prepare a dataset for Chapter 17 (Image Classification)

## License

These examples are part of the Code with PHP tutorial series. Feel free to use and modify them for learning purposes.

## Questions?

If you encounter issues:

1. Run `02-check-extensions.php` to verify your setup
2. Check the chapter text for detailed explanations
3. Review the code comments for inline documentation
4. Visit the Code with PHP website for updates

Happy coding! 🚀
