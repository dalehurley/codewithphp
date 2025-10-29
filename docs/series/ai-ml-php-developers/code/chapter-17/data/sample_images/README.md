# Sample Images Directory

This directory should contain test images for classification examples.

## Required Images

For the chapter examples to work properly, add at least 5-8 test images:

1. **cat.jpg** - Image of a cat
2. **dog.jpg** - Image of a dog
3. **car.jpg** - Image of a car
4. **bicycle.jpg** - Image of a bicycle
5. **coffee.jpg** - Image of coffee or food

## Where to Get Sample Images

### Free Stock Photo Sites

- **[Unsplash](https://unsplash.com/)** - High-quality, free-to-use images
- **[Pexels](https://www.pexels.com/)** - Free stock photos and videos
- **[Pixabay](https://pixabay.com/)** - Free images, no attribution required

### Quick Download Examples

Using `curl` to download sample images:

```bash
# Example: Download from Unsplash (replace with actual image URLs)
curl -L "https://images.unsplash.com/photo-cat-example" -o cat.jpg
curl -L "https://images.unsplash.com/photo-dog-example" -o dog.jpg
curl -L "https://images.unsplash.com/photo-car-example" -o car.jpg
```

### Image Requirements

- **Format**: JPG, JPEG, or PNG
- **Size**: Any reasonable size (will be resized automatically)
- **Quality**: Standard web quality is fine
- **Content**: Clear, well-lit images work best

### Using Your Own Images

You can also use your own photos! Just make sure:

- Images are in JPG or PNG format
- File names match what the examples expect (or update the code)
- Images are not copyrighted material if sharing

## Testing Your Images

After adding images, test with:

```bash
# Cloud API test
php 01-cloud-vision-setup.php

# Local ONNX test
php 04-onnx-setup-test.php

# Batch classification
php 03-classify-with-cloud.php
```

## File Naming

The examples expect these specific filenames:

- `cat.jpg`
- `dog.jpg`
- `car.jpg`
- `bicycle.jpg`
- `coffee.jpg`

You can add more images with any names - the batch processing scripts will automatically find all `.jpg` and `.png` files in this directory.
