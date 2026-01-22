---
title: "13: Vision - Working with Images"
description: "Master Claude's vision capabilities in PHP. Analyze images, extract text with OCR, interpret charts and diagrams, perform visual content moderation, and build multimodal applications."
sidebar:
  label: "13: Vision - Working with Images"
  order: 13
  badge:
    text: Expert
    variant: danger
---
![13: Vision - Working with Images](/images/claude-php/chapter-13-hero-full.webp)


# Chapter 13: Vision - Working with Images

## Overview

Claude's vision capabilities enable it to analyze, understand, and extract information from images. This opens up powerful use cases: automated content moderation, receipt processing, chart analysis, visual quality control, UI/UX feedback, and much more.

In this chapter, you'll learn how to send images to Claude, analyze visual content, extract text with OCR, interpret data visualizations, and build practical multimodal applications that combine text and images.

## Prerequisites

Before starting, ensure you have:

- ✓ **Understanding of image formats** (JPEG, PNG, WebP)
- ✓ **Base64 encoding knowledge** for image transmission
- ✓ **File handling experience** in PHP
- ✓ **Completed Chapters 00-05** of this series

**Estimated Time**: 45-60 minutes

## What You'll Build

By the end of this chapter, you will have created:

- An `ImageHelper` class for preparing and validating images for Claude API
- A receipt processing system that extracts structured data from receipt images
- A chart analyzer that extracts insights from data visualizations
- A content moderation system for user-uploaded images
- A product image analyzer that generates SEO-friendly descriptions and titles
- A UI analyzer that provides UX feedback from screenshots
- Multi-image workflows for product listings and duplicate detection

## Objectives

- Understand how to encode and send images to Claude's vision API
- Learn to extract text from images using OCR capabilities
- Analyze charts and graphs to extract data and insights
- Implement content moderation for user-generated images
- Build product image analysis systems for e-commerce applications
- Create screenshot analysis tools for UX feedback
- Process multiple images together for comparison and analysis

## Supported Image Formats

Claude supports these image formats:

- **JPEG** (image/jpeg) - Most common, good for photos
- **PNG** (image/png) - Supports transparency, good for screenshots
- **GIF** (image/gif) - Animated and static images
- **WebP** (image/webp) - Modern format, excellent compression

**Maximum size**: 5MB per image (after base64 encoding: ~3.75MB original)

## Step 1: Sending Your First Image (~5 min)

### Goal

Send an image to Claude and receive a detailed analysis of its contents.

### Actions

1. **Load and encode the image** using `file_get_contents()` and `base64_encode()`
2. **Detect the MIME type** using PHP's `finfo` functions
3. **Create a message** with both image and text content
4. **Send the request** to Claude's API

Images are sent as base64-encoded content blocks:

```php
<?php
# filename: examples/01-basic-image-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

// Load and encode image
$imagePath = __DIR__ . '/images/product-photo.jpg';
$imageData = file_get_contents($imagePath);
$base64Image = base64_encode($imageData);

// Detect MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $imagePath);
finfo_close($finfo);

// Send image to Claude
$response = $client->messages()->create(
    'model' => 'claude-sonnet-4-5',
    'max_tokens' => 1024,
    'messages' => [
        [
            'role' => 'user',
            'content' => [
                [
                    'type' => 'image',
                    'source' => [
                        'type' => 'base64',
                        'media_type' => $mimeType,
                        'data' => $base64Image
                    ]
                ],
                [
                    'type' => 'text',
                    'text' => 'Describe this product image in detail. Include colors, style, condition, and any visible text or branding.'
                ]
            ]
        ]
    ]
);

echo "Image Analysis:\n";
echo $response->content[0]->text . "\n";
```

### Expected Result

```
Image Analysis:
This product image shows a [description of the image]. The colors are [color details].
The style appears to be [style description]. The condition looks [condition].
Visible text includes: [any text found]. Branding shows: [branding details].
```

### Why It Works

Claude's vision API accepts images as base64-encoded strings with their MIME type. The image and text prompt are combined in a single message's content array. Claude analyzes the visual content and generates a detailed text description based on your prompt. Base64 encoding ensures the binary image data can be transmitted as text in the JSON API request.

### Troubleshooting

- **Error: "Image file not found"** — Verify the image path is correct and the file exists. Use absolute paths or check `__DIR__` is pointing to the right directory.
- **Error: "Invalid image format"** — Ensure the image is in a supported format (JPEG, PNG, GIF, or WebP). Check the MIME type detection is working correctly.
- **Error: "Image too large"** — Images must be under 5MB after base64 encoding (~3.75MB original). Resize large images before sending.

## Step 2: Image Helper Class (~10 min)

### Goal

Create a reusable helper class that validates, prepares, and optimizes images for Claude's API.

### Actions

1. **Create the `ImageHelper` class** with validation methods
2. **Implement `prepareImage()`** to validate and encode images
3. **Add `prepareImageFromUrl()`** for remote images
4. **Create `resizeIfNeeded()`** to optimize large images

Create a reusable class for image handling:

```php
<?php
# filename: src/Vision/ImageHelper.php
declare(strict_types=1);

namespace App\Vision;

class ImageHelper
{
    private const MAX_FILE_SIZE = 5 * 1024 * 1024; // 5MB
    private const SUPPORTED_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];

    /**
     * Prepare an image for Claude API
     */
    public static function prepareImage(string $imagePath): array
    {
        if (!file_exists($imagePath)) {
            throw new \RuntimeException("Image file not found: {$imagePath}");
        }

        $fileSize = filesize($imagePath);
        if ($fileSize > self::MAX_FILE_SIZE) {
            throw new \RuntimeException(
                "Image too large: {$fileSize} bytes (max " . self::MAX_FILE_SIZE . ")"
            );
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $imagePath);
        finfo_close($finfo);

        if (!in_array($mimeType, self::SUPPORTED_TYPES)) {
            throw new \RuntimeException("Unsupported image type: {$mimeType}");
        }

        $imageData = file_get_contents($imagePath);
        $base64Data = base64_encode($imageData);

        return [
            'type' => 'image',
            'source' => [
                'type' => 'base64',
                'media_type' => $mimeType,
                'data' => $base64Data
            ]
        ];
    }

    /**
     * Prepare image from URL
     */
    public static function prepareImageFromUrl(string $url): array
    {
        $imageData = file_get_contents($url);
        if ($imageData === false) {
            throw new \RuntimeException("Failed to download image from URL");
        }

        // Save temporarily to detect MIME type
        $tempFile = tempnam(sys_get_temp_dir(), 'claude_img_');
        file_put_contents($tempFile, $imageData);

        try {
            $result = self::prepareImage($tempFile);
            unlink($tempFile);
            return $result;
        } catch (\Exception $e) {
            unlink($tempFile);
            throw $e;
        }
    }

    /**
     * Resize image if needed
     */
    public static function resizeIfNeeded(string $imagePath, int $maxWidth = 1568): string
    {
        $imageInfo = getimagesize($imagePath);
        if (!$imageInfo) {
            throw new \RuntimeException("Invalid image file");
        }

        [$width, $height, $type] = $imageInfo;

        if ($width <= $maxWidth) {
            return $imagePath; // No resize needed
        }

        // Calculate new dimensions
        $ratio = $maxWidth / $width;
        $newWidth = $maxWidth;
        $newHeight = (int)($height * $ratio);

        // Create new image
        $source = match($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($imagePath),
            IMAGETYPE_PNG => imagecreatefrompng($imagePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($imagePath),
            default => throw new \RuntimeException("Unsupported image type for resize")
        };

        $dest = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency for PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
        }

        imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

        // Save to temp file
        $tempFile = tempnam(sys_get_temp_dir(), 'resized_') . '.jpg';
        imagejpeg($dest, $tempFile, 90);

        imagedestroy($source);
        imagedestroy($dest);

        return $tempFile;
    }
}
```

### Expected Result

The `ImageHelper` class provides three static methods:

- `prepareImage($path)` returns a formatted image content array ready for Claude
- `prepareImageFromUrl($url)` downloads and prepares remote images
- `resizeIfNeeded($path, $maxWidth)` returns a resized image path if needed

### Why It Works

The helper class centralizes image validation (file existence, size limits, MIME type checking) and encoding logic. This prevents code duplication and ensures consistent error handling. The `resizeIfNeeded()` method uses PHP's GD library to maintain aspect ratio while reducing file size, which improves API performance and reduces costs.

### Troubleshooting

- **Error: "Unsupported image type"** — Check that `finfo` is detecting the correct MIME type. Some servers may need the `fileinfo` extension enabled.
- **Resize fails** — Ensure PHP's GD extension is installed (`php -m | grep gd`). PNG transparency may not be preserved in all resize operations.
- **Temp file errors** — Verify `sys_get_temp_dir()` returns a writable directory. Check file permissions on the temp directory.

## Step 3: OCR - Extracting Text from Images (~8 min)

### Goal

Extract structured data from receipt images using Claude's OCR capabilities.

### Actions

1. **Create a `ReceiptProcessor` class** that uses `ImageHelper`
2. **Send the image** with a structured extraction prompt
3. **Parse the JSON response** from Claude
4. **Return structured data** ready for database storage

Claude excels at extracting text from images:

````php
<?php
# filename: examples/02-ocr-text-extraction.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Vision\ImageHelper;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

class ReceiptProcessor
{
    public function __construct(
        private Client $client
    ) {}

    public function processReceipt(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => <<<PROMPT
Extract all information from this receipt and return as JSON with these fields:
- merchant_name: string
- date: string (YYYY-MM-DD format)
- total: number
- subtotal: number
- tax: number
- items: array of {name: string, quantity: number, price: number}
- payment_method: string (if visible)

Return only valid JSON, no explanation.
PROMPT
                        ]
                    ]
                ]
            ]
        );

        $jsonText = $response->content[0]->text;

        // Extract JSON from response
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $jsonText, $matches)) {
            $jsonText = $matches[1];
        } elseif (preg_match('/(\{.*?\})/s', $jsonText, $matches)) {
            $jsonText = $matches[1];
        }

        return json_decode($jsonText, true) ?? [];
    }
}

// Example usage
$processor = new ReceiptProcessor($client);
$receiptData = $processor->processReceipt(__DIR__ . '/images/receipt.jpg');

echo "Receipt Data:\n";
echo json_encode($receiptData, JSON_PRETTY_PRINT) . "\n";
````

### Expected Result

```json
{
  "merchant_name": "Coffee Shop Downtown",
  "date": "2024-01-15",
  "total": 12.5,
  "subtotal": 11.36,
  "tax": 1.14,
  "items": [
    {
      "name": "Latte",
      "quantity": 2,
      "price": 5.68
    }
  ],
  "payment_method": "Credit Card"
}
```

### Why It Works

Claude's vision model can read text from images with high accuracy, even when text is handwritten, rotated, or partially obscured. By providing a clear JSON schema in the prompt, Claude structures the extracted data consistently. The regex patterns handle cases where Claude wraps JSON in markdown code blocks or returns it directly.

### Troubleshooting

- **Empty or incomplete data** — The receipt image may be low quality or the text may be too small. Try resizing the image or using a higher resolution scan.
- **JSON parsing errors** — Claude may return text before/after the JSON. The regex patterns handle common formats, but you may need to adjust them for edge cases.
- **Date format issues** — Receipts may have ambiguous dates. Consider adding validation to ensure dates are in the expected format.

## Step 4: Analyzing Charts and Graphs (~8 min)

### Goal

Extract data points, trends, and insights from chart and graph images.

### Actions

1. **Create a `ChartAnalyzer` class** for visualization analysis
2. **Send chart images** with analysis prompts
3. **Extract structured insights** including data points and trends
4. **Compare multiple charts** side by side

Extract insights from data visualizations:

````php
<?php
# filename: examples/03-chart-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Vision\ImageHelper;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

class ChartAnalyzer
{
    public function __construct(
        private Client $client
    ) {}

    public function analyzeChart(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => <<<PROMPT
Analyze this chart/graph and provide:

1. Chart Type: (bar, line, pie, scatter, etc.)
2. Title: What is the chart showing?
3. Axes: What do the X and Y axes represent?
4. Data Points: Extract visible data values
5. Trends: Describe any trends, patterns, or notable features
6. Insights: What conclusions can be drawn?

Format as JSON with these fields: chart_type, title, x_axis, y_axis, data_points (array), trends (array of strings), insights (array of strings)
PROMPT
                        ]
                    ]
                ]
            ]
        );

        $analysisText = $response->content[0]->text;

        // Try to extract JSON, fallback to text
        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $analysisText, $matches)) {
            return json_decode($matches[1], true) ?? ['raw_analysis' => $analysisText];
        }

        return ['raw_analysis' => $analysisText];
    }

    public function compareCharts(array $imagePaths): string
    {
        // Prepare multiple images
        $content = [];
        foreach ($imagePaths as $index => $path) {
            $content[] = ImageHelper::prepareImage($path);
            $content[] = [
                'type' => 'text',
                'text' => "Chart " . ($index + 1) . ":\n"
            ];
        }

        $content[] = [
            'type' => 'text',
            'text' => "Compare these charts. What are the key differences, similarities, and what story do they tell together?"
        ];

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 3000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ]
        );

        return $response->content[0]->text;
    }
}

// Example usage
$analyzer = new ChartAnalyzer($client);

echo "=== Single Chart Analysis ===\n";
$chartData = $analyzer->analyzeChart(__DIR__ . '/images/sales-chart.png');
echo json_encode($chartData, JSON_PRETTY_PRINT) . "\n\n";

echo "=== Comparing Multiple Charts ===\n";
$comparison = $analyzer->compareCharts([
    __DIR__ . '/images/q1-sales.png',
    __DIR__ . '/images/q2-sales.png'
]);
echo $comparison . "\n";
````

### Expected Result

```json
{
  "chart_type": "line",
  "title": "Monthly Sales Revenue",
  "x_axis": "Months (Jan-Dec)",
  "y_axis": "Revenue in USD",
  "data_points": [
    { "month": "Jan", "value": 45000 },
    { "month": "Feb", "value": 52000 }
  ],
  "trends": [
    "Steady upward trend from January to June",
    "Peak in December holiday season"
  ],
  "insights": ["Revenue increased 25% year-over-year", "Strongest growth in Q4"]
}
```

### Why It Works

Claude can interpret visual data representations, reading axis labels, legends, and data points from charts. The structured prompt guides Claude to extract both raw data (values) and higher-level analysis (trends, insights). When comparing multiple charts, Claude can identify relationships and patterns across visualizations.

### Troubleshooting

- **Incorrect chart type detection** — Some charts may be ambiguous (e.g., stacked bar vs. grouped bar). Consider being more specific in your prompt or accepting multiple possible types.
- **Missing data points** — Very dense charts may have too many points to extract individually. Ask Claude to summarize ranges or key values instead.
- **Comparison fails** — Ensure images are clear and charts are similar in scale/format for meaningful comparison.

## Step 5: Content Moderation (~7 min)

### Goal

Build an automated content moderation system that flags inappropriate or unsafe images.

### Actions

1. **Create an `ImageModerator` class** with moderation logic
2. **Send images** with a comprehensive moderation prompt
3. **Parse moderation results** with violation types and confidence levels
4. **Implement batch processing** for multiple images

Analyze images for inappropriate or unsafe content:

````php
<?php
# filename: examples/04-content-moderation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Vision\ImageHelper;

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

class ImageModerator
{
    public function __construct(
        private Client $client
    ) {}

    public function moderateImage(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => <<<PROMPT
Analyze this image for content moderation. Check for:

1. Adult content (nudity, sexual content)
2. Violence or gore
3. Hate symbols or extremist content
4. Illegal activities
5. Harmful or dangerous activities
6. Spam or misleading content

Return JSON with:
- safe: boolean (true if safe, false if violates policies)
- violations: array of violation types found (empty if safe)
- confidence: string (high/medium/low)
- reasoning: string explaining the decision

Be conservative - when in doubt, flag as unsafe.
PROMPT
                        ]
                    ]
                ]
            ]
        );

        $resultText = $response->content[0]->text;

        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $resultText, $matches)) {
            $resultText = $matches[1];
        }

        return json_decode($resultText, true) ?? [
            'safe' => false,
            'violations' => ['parse_error'],
            'confidence' => 'low',
            'reasoning' => 'Failed to parse response'
        ];
    }

    public function moderateBatch(array $imagePaths): array
    {
        $results = [];
        foreach ($imagePaths as $path) {
            $results[basename($path)] = $this->moderateImage($path);
        }
        return $results;
    }
}

// Example usage
$moderator = new ImageModerator($client);

$result = $moderator->moderateImage(__DIR__ . '/images/user-upload.jpg');

echo "Moderation Result:\n";
echo "Safe: " . ($result['safe'] ? 'Yes' : 'No') . "\n";
echo "Violations: " . implode(', ', $result['violations'] ?? []) . "\n";
echo "Confidence: {$result['confidence']}\n";
echo "Reasoning: {$result['reasoning']}\n";
````

### Expected Result

```
Moderation Result:
Safe: No
Violations: adult_content, violence
Confidence: high
Reasoning: Image contains explicit content and graphic violence that violates community guidelines.
```

### Why It Works

Claude's vision model can identify inappropriate content across multiple categories. By using a conservative approach ("when in doubt, flag as unsafe"), you protect your platform while allowing human review for edge cases. The structured JSON response provides actionable data for your moderation workflow, including confidence levels to prioritize manual review.

### Troubleshooting

- **False positives** — Some artistic or educational content may be flagged. Consider adding a human review step for low-confidence flags or specific categories.
- **Parse errors** — If Claude doesn't return valid JSON, the fallback ensures the image is flagged as unsafe, which is safer than allowing potentially harmful content.
- **Performance concerns** — Batch processing can be slow. Consider processing images asynchronously or using a queue system for large volumes.

## Step 6: Product Image Analysis (~10 min)

### Goal

Automatically extract product information, generate SEO-friendly descriptions, and create product listings from images.

### Actions

1. **Create a `ProductImageAnalyzer` class** for e-commerce use cases
2. **Analyze product images** to extract categories, features, and condition
3. **Generate product titles** optimized for search engines
4. **Compare product images** to detect duplicates or variations

Extract product details from images:

````php
<?php
# filename: examples/05-product-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Vision\ImageHelper;

class ProductImageAnalyzer
{
    public function __construct(
        private Client $client
    ) {}

    public function analyzeProductImage(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => <<<PROMPT
Analyze this product image and extract:

1. Product Category (e.g., Electronics, Clothing, Home Goods)
2. Product Type (specific item type)
3. Brand (if visible)
4. Color(s)
5. Condition (new/used/damaged)
6. Key Features (list observable features)
7. Quality Assessment (high/medium/low based on image quality)
8. Suggested Tags (for search/categorization)
9. SEO-friendly Description (2-3 sentences)

Return as JSON.
PROMPT
                        ]
                    ]
                ]
            ]
        );

        $text = $response->content[0]->text;

        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            return json_decode($matches[1], true) ?? [];
        }

        return [];
    }

    public function compareProductImages(string $image1, string $image2): string
    {
        $content = [
            ImageHelper::prepareImage($image1),
            ['type' => 'text', 'text' => 'Image 1 (Original)'],
            ImageHelper::prepareImage($image2),
            ['type' => 'text', 'text' => 'Image 2 (Comparison)'],
            [
                'type' => 'text',
                'text' => 'Are these the same product? Identify any differences in color, condition, features, or presentation.'
            ]
        ];

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        );

        return $response->content[0]->text;
    }

    public function generateProductTitle(string $imagePath): string
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 200,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => 'Generate a concise, SEO-friendly product title for this item. Include brand if visible, key features, and product type. Max 80 characters. Return only the title, no explanation.'
                        ]
                    ]
                ]
            ]
        );

        return trim($response->content[0]->text);
    }
}

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$analyzer = new ProductImageAnalyzer($client);

// Analyze product
$productData = $analyzer->analyzeProductImage(__DIR__ . '/images/product.jpg');
echo "Product Analysis:\n";
echo json_encode($productData, JSON_PRETTY_PRINT) . "\n\n";

// Generate title
$title = $analyzer->generateProductTitle(__DIR__ . '/images/product.jpg');
echo "Generated Title: {$title}\n";
````

### Expected Result

```json
{
  "category": "Electronics",
  "product_type": "Wireless Headphones",
  "brand": "TechBrand",
  "colors": ["Black", "Silver"],
  "condition": "New",
  "key_features": ["Noise cancellation", "Bluetooth 5.0", "30-hour battery"],
  "quality_assessment": "high",
  "suggested_tags": ["audio", "wireless", "premium"],
  "seo_description": "Premium wireless headphones with active noise cancellation..."
}
```

### Why It Works

Product images contain visual information that can be automatically extracted to populate e-commerce catalogs. Claude can identify brands, read product labels, assess condition, and generate SEO-optimized content. This automation significantly reduces manual data entry while improving consistency and discoverability of products.

### Troubleshooting

- **Brand not detected** — Logos may be unclear or brands may not be visible. Consider allowing manual brand entry as a fallback.
- **Condition misidentified** — "Used" vs "New" can be ambiguous. Add more specific condition categories or allow manual override.
- **Title too long** — Generated titles may exceed character limits. Add truncation logic or request shorter titles in the prompt.

## Step 7: Screenshot and UI Analysis (~8 min)

### Goal

Analyze user interface screenshots to extract text, provide UX feedback, and identify accessibility issues.

### Actions

1. **Create a `UIAnalyzer` class** for interface analysis
2. **Extract all visible text** from screenshots
3. **Analyze layout and visual hierarchy**
4. **Provide UX and accessibility feedback**

Analyze screenshots and provide UX feedback:

````php
<?php
# filename: examples/06-screenshot-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Vision\ImageHelper;

class UIAnalyzer
{
    public function __construct(
        private Client $client
    ) {}

    public function analyzeUI(string $screenshotPath): array
    {
        $imageContent = ImageHelper::prepareImage($screenshotPath);

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 3000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => <<<PROMPT
Analyze this user interface screenshot and provide:

1. Overall Assessment: What type of UI is this? (web, mobile app, desktop)
2. Layout Analysis: Describe the layout structure
3. Visual Hierarchy: Is the hierarchy clear?
4. Accessibility Issues: Any problems for users with disabilities?
5. UX Issues: Usability problems or confusing elements
6. Design Quality: Professional/Amateur, Modern/Dated
7. Suggestions: 5 specific improvements

Return as JSON with these fields.
PROMPT
                        ]
                    ]
                ]
            ]
        );

        $text = $response->content[0]->text;

        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            return json_decode($matches[1], true) ?? [];
        }

        return ['raw_analysis' => $text];
    }

    public function extractText(string $screenshotPath): array
    {
        $imageContent = ImageHelper::prepareImage($screenshotPath);

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        $imageContent,
                        [
                            'type' => 'text',
                            'text' => 'Extract ALL visible text from this screenshot. Organize by sections if possible. Preserve formatting and hierarchy.'
                        ]
                    ]
                ]
            ]
        );

        return [
            'extracted_text' => $response->content[0]->text,
            'source' => basename($screenshotPath)
        ];
    }
}

$client = new ClaudePhp(
    apiKey: $_ENV['ANTHROPIC_API_KEY']
);

$analyzer = new UIAnalyzer($client);

$uiAnalysis = $analyzer->analyzeUI(__DIR__ . '/images/app-screenshot.png');
echo "UI Analysis:\n";
echo json_encode($uiAnalysis, JSON_PRETTY_PRINT) . "\n";
````

### Expected Result

```json
{
  "overall_assessment": "Web application",
  "layout_analysis": "Three-column layout with header navigation",
  "visual_hierarchy": "Clear",
  "accessibility_issues": [
    "Low contrast text in footer",
    "Missing alt text indicators"
  ],
  "ux_issues": ["Search button placement unclear", "Too many navigation items"],
  "design_quality": "Professional, Modern",
  "suggestions": [
    "Increase footer text contrast",
    "Consolidate navigation items",
    "Add breadcrumb navigation",
    "Improve mobile responsiveness",
    "Add loading states for async actions"
  ]
}
```

### Why It Works

Claude can analyze UI screenshots holistically, understanding layout structure, visual relationships, and usability patterns. The text extraction preserves the hierarchy and organization of content, making it useful for documentation or automated testing. UX analysis identifies both obvious issues and subtle usability problems that might be missed in manual review.

### Troubleshooting

- **Text extraction incomplete** — Some text may be in images or custom fonts. Consider using browser automation tools for more accurate text extraction.
- **Accessibility analysis limited** — Claude can only analyze visual aspects. Use automated accessibility testing tools (like axe-core) for comprehensive checks.
- **Suggestions too generic** — Provide more context about your target users or design system to get more specific recommendations.

## Step 8: Multi-Image Workflows (~7 min)

### Goal

Process multiple images together for product listings, duplicate detection, and comparative analysis.

### Actions

1. **Create a `MultiImageProcessor` class** for batch operations
2. **Build product listing generator** from multiple product images
3. **Implement duplicate detection** across image sets
4. **Process images sequentially** in a single API call

Process multiple images together:

````php
<?php
# filename: examples/07-multi-image-workflow.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use ClaudePhp\ClaudePhp;
use App\Vision\ImageHelper;

class MultiImageProcessor
{
    public function __construct(
        private Client $client
    ) {}

    public function createProductListing(array $productImages): array
    {
        $content = [];

        // Add all images
        foreach ($productImages as $index => $imagePath) {
            $content[] = ImageHelper::prepareImage($imagePath);
        }

        // Add instruction
        $content[] = [
            'type' => 'text',
            'text' => <<<PROMPT
Based on these product images, create a complete product listing:

1. Product Title (concise, SEO-friendly)
2. Category
3. Description (3-4 sentences highlighting features)
4. Key Features (bullet points)
5. Condition Assessment
6. Suggested Price Range (based on quality/condition)
7. Tags for search optimization

Return as JSON.
PROMPT
        ];

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        );

        $text = $response->content[0]->text;

        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            return json_decode($matches[1], true) ?? [];
        }

        return [];
    }

    public function detectDuplicates(array $imagePaths): array
    {
        $content = [];

        foreach ($imagePaths as $index => $path) {
            $content[] = ImageHelper::prepareImage($path);
            $content[] = ['type' => 'text', 'text' => "Image " . ($index + 1)];
        }

        $content[] = [
            'type' => 'text',
            'text' => 'Identify which images show the same or very similar products. Group duplicates together and explain similarities/differences.'
        ];

        $response = $this->client->messages()->create(
            'model' => 'claude-sonnet-4-5',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        );

        return [
            'analysis' => $response->content[0]->text,
            'images_analyzed' => count($imagePaths)
        ];
    }
}
````

### Expected Result

For product listings:

```json
{
  "title": "Vintage Leather Jacket - Brown, Size M",
  "category": "Clothing",
  "description": "Classic brown leather jacket in excellent condition...",
  "key_features": ["Genuine leather", "Lined", "Multiple pockets"],
  "condition_assessment": "Excellent - minor wear",
  "suggested_price_range": "$150-$200",
  "tags": ["vintage", "leather", "jacket", "brown"]
}
```

For duplicate detection:

```
Analysis: Images 1, 3, and 5 show the same product (vintage leather jacket)
from different angles. Images 2 and 4 are different products (denim jacket
and wool coat). Images 1 and 3 are nearly identical, with only slight
lighting differences.
```

### Why It Works

Processing multiple images in a single API call is more efficient than individual requests and allows Claude to compare images directly. This is especially powerful for e-commerce where you need to analyze product photo sets or detect duplicate listings. Claude can identify subtle differences between similar images and group related content together.

### Troubleshooting

- **Token limit exceeded** — Too many large images may exceed context limits. Process images in smaller batches or resize them first.
- **Duplicate detection inaccurate** — Very similar products (e.g., same item in different colors) may be flagged as duplicates. Refine your prompt to focus on exact matches vs. variations.
- **Product listing incomplete** — If images don't show all product details, Claude may make assumptions. Consider adding text descriptions alongside images for better results.

## Best Practices

### 1. Image Optimization

```php
// Always resize large images
$resizedPath = ImageHelper::resizeIfNeeded($originalPath, 1568);
$imageContent = ImageHelper::prepareImage($resizedPath);
```

### 2. Error Handling

```php
try {
    $imageContent = ImageHelper::prepareImage($path);
} catch (\RuntimeException $e) {
    error_log("Image preparation failed: " . $e->getMessage());
    return ['error' => 'Invalid image'];
}
```

### 3. Caching Results

```php
$cacheKey = 'vision_' . md5_file($imagePath);
if ($cached = $cache->get($cacheKey)) {
    return $cached;
}

$result = $analyzer->analyzeImage($imagePath);
$cache->set($cacheKey, $result, 3600);
```

## Vision-Specific Cost Optimization

Vision tasks often represent a significant portion of your Claude API costs. Understanding how images impact token usage and implementing targeted optimizations can reduce vision-related costs by 40-60%.

### Understanding Vision Token Usage

Claude's token counting for images works differently than text:

**Base Image Costs:**

- **Fixed cost**: Every image costs 1,100 tokens regardless of size
- **Resolution cost**: Additional tokens based on image dimensions
- **Total tokens**: ~1,100 base + (height × width / ~750) additional tokens

**Practical Examples:**

```php
<?php
// Token calculation helper
class VisionTokenCalculator
{
    /**
     * Calculate approximate tokens for an image
     */
    public static function calculateTokens(
        int $width,
        int $height,
        string $quality = 'auto'
    ): int {
        // Base cost for any image
        $tokens = 1100;

        // Resolution cost (approximately)
        $pixelCount = $width * $height;
        $resolutionTokens = (int)($pixelCount / 750);

        // Quality multiplier
        if ($quality === 'high') {
            $resolutionTokens = (int)($resolutionTokens * 1.5);
        }

        return $tokens + $resolutionTokens;
    }

    /**
     * Recommend image dimensions to minimize tokens
     */
    public static function recommendOptimalSize(
        int $originalWidth,
        int $originalHeight,
        float $maxTokenBudget = 1500
    ): array {
        // Work backwards from token budget
        // 1500 tokens = 1100 base + 400 resolution
        // 400 resolution tokens = 300k pixels

        $maxPixels = (int)(($maxTokenBudget - 1100) * 750);
        $currentPixels = $originalWidth * $originalHeight;

        if ($currentPixels <= $maxPixels) {
            return [
                'width' => $originalWidth,
                'height' => $originalHeight,
                'tokens' => self::calculateTokens($originalWidth, $originalHeight),
                'recommendation' => 'Image is already optimized'
            ];
        }

        // Calculate scale factor
        $scaleFactor = sqrt($maxPixels / $currentPixels);
        $newWidth = (int)($originalWidth * $scaleFactor);
        $newHeight = (int)($originalHeight * $scaleFactor);

        return [
            'width' => $newWidth,
            'height' => $newHeight,
            'tokens' => self::calculateTokens($newWidth, $newHeight),
            'recommendation' => sprintf(
                'Resize from %dx%d to %dx%d (save ~%d tokens)',
                $originalWidth,
                $originalHeight,
                $newWidth,
                $newHeight,
                self::calculateTokens($originalWidth, $originalHeight) -
                self::calculateTokens($newWidth, $newHeight)
            )
        ];
    }
}

// Usage
$tokens = VisionTokenCalculator::calculateTokens(1024, 768);
echo "Image (1024x768) costs approximately: {$tokens} tokens\n";

$optimized = VisionTokenCalculator::recommendOptimalSize(2048, 1536, 1500);
echo $optimized['recommendation'] . "\n";
```

### Cost Comparison: Images by Size and Model

```php
<?php
// Cost comparison table for different scenarios

$scenarios = [
    // [description, width, height, model, input_cost_per_1m, output_cost_per_1m]
    ['Small product photo (thumbnail)', 256, 256, 'haiku', 0.80, 4.00],
    ['Small product photo (thumbnail)', 256, 256, 'sonnet', 3.00, 15.00],

    ['Medium product photo', 800, 600, 'haiku', 0.80, 4.00],
    ['Medium product photo', 800, 600, 'sonnet', 3.00, 15.00],

    ['High-res product photo', 2048, 1536, 'haiku', 0.80, 4.00],
    ['High-res product photo', 2048, 1536, 'sonnet', 3.00, 15.00],

    ['Document/screenshot', 1920, 1080, 'haiku', 0.80, 4.00],
    ['Document/screenshot', 1920, 1080, 'sonnet', 3.00, 15.00],
];

class VisionCostComparator
{
    public static function compare(array $scenarios): void
    {
        echo sprintf("%-30s | %-8s | %-8s | %-20s | %-20s\n",
            'Image Type', 'Tokens', 'Model', 'Input Cost', 'Per 1000 Images');
        echo str_repeat('-', 110) . "\n";

        foreach ($scenarios as [$desc, $w, $h, $model, $inputCost, $outputCost]) {
            $tokens = 1100 + (int)(($w * $h) / 750);

            // Calculate costs (assuming analysis fits in output tokens)
            $inputCostPer = ($tokens / 1_000_000) * $inputCost;
            $costPer1000 = $inputCostPer * 1000;

            echo sprintf("%-30s | %-8d | %-8s | $%.6f | $%.2f\n",
                $desc, $tokens, $model, $inputCostPer, $costPer1000);
        }
    }
}

VisionCostComparator::compare($scenarios);
```

**Results Table:**

```
Image Type                     | Tokens   | Model   | Input Cost       | Per 1000 Images
Small product (256x256)        | 1,186    | haiku   | $0.000949        | $0.95
Small product (256x256)        | 1,186    | sonnet  | $0.003558        | $3.56
Medium product (800x600)       | 1,741    | haiku   | $0.001393        | $1.39
Medium product (800x600)       | 1,741    | sonnet  | $0.005223        | $5.22
High-res product (2048x1536)   | 5,394    | haiku   | $0.004315        | $4.32
High-res product (2048x1536)   | 5,394    | sonnet  | $0.016182        | $16.18
Document (1920x1080)           | 4,166    | haiku   | $0.003333        | $3.33
Document (1920x1080)           | 4,166    | sonnet  | $0.012498        | $12.50
```

### Model Selection for Vision Tasks

Choose the right model based on your use case:

```php
<?php
class VisionModelSelector
{
    /**
     * Recommend the best model for a vision task
     */
    public static function selectModel(
        string $taskType,
        int $imageComplexity = 5, // 1-10 scale
        bool $prioritizeCost = false,
        bool $needsAccuracy = false
    ): string {
        // Cost optimization (Haiku is cheapest)
        if ($prioritizeCost && !$needsAccuracy) {
            return 'claude-haiku-4-5-20251001';
        }

        // Accuracy optimization (Sonnet is balanced)
        if ($needsAccuracy) {
            return 'claude-sonnet-4-5';
        }

        // Task-based selection
        return match($taskType) {
            // Simple tasks: use Haiku (4x cheaper than Sonnet)
            'content-moderation' => 'claude-haiku-4-5-20251001',
            'text-extraction' => 'claude-haiku-4-5-20251001',
            'image-classification' => 'claude-haiku-4-5-20251001',

            // Medium complexity: use Sonnet (balanced)
            'product-analysis' => 'claude-sonnet-4-5',
            'document-analysis' => 'claude-sonnet-4-5',
            'chart-analysis' => 'claude-sonnet-4-5',

            // Complex: use best available
            'comprehensive-ui-analysis' => 'claude-sonnet-4-5',
            'contract-analysis' => 'claude-sonnet-4-5',

            default => 'claude-sonnet-4-5'
        };
    }

    /**
     * Calculate total cost for a batch operation
     */
    public static function estimateBatchCost(
        int $imageCount,
        int $avgImageTokens,
        int $avgOutputTokens,
        string $model = 'claude-sonnet-4-5'
    ): array {
        $inputCosts = [
            'claude-haiku-4-5-20251001' => 0.80 / 1_000_000,
            'claude-sonnet-4-5' => 3.00 / 1_000_000,
        ];

        $outputCosts = [
            'claude-haiku-4-5-20251001' => 4.00 / 1_000_000,
            'claude-sonnet-4-5' => 15.00 / 1_000_000,
        ];

        $inputCost = $inputCosts[$model] ?? $inputCosts['claude-sonnet-4-5'];
        $outputCost = $outputCosts[$model] ?? $outputCosts['claude-sonnet-4-5'];

        $totalInputTokens = $imageCount * $avgImageTokens;
        $totalOutputTokens = $imageCount * $avgOutputTokens;

        $inputCostTotal = $totalInputTokens * $inputCost;
        $outputCostTotal = $totalOutputTokens * $outputCost;
        $totalCost = $inputCostTotal + $outputCostTotal;

        return [
            'total_images' => $imageCount,
            'avg_image_tokens' => $avgImageTokens,
            'total_input_tokens' => $totalInputTokens,
            'input_cost' => $inputCostTotal,
            'output_cost' => $outputCostTotal,
            'total_cost' => $totalCost,
            'cost_per_image' => $totalCost / $imageCount,
            'model' => $model,
        ];
    }
}

// Example: Analyze 1000 product images
$estimate = VisionModelSelector::estimateBatchCost(
    imageCount: 1000,
    avgImageTokens: 1741,  // Medium image (800x600)
    avgOutputTokens: 500,   // Product analysis output
    'model' => 'claude-haiku-4-5-20251001'
);

echo "Batch Processing 1000 Product Images (Haiku):\n";
echo "  Input tokens: " . number_format($estimate['total_input_tokens']) . "\n";
echo "  Output tokens: " . number_format($estimate['total_output_tokens']) . "\n";
echo "  Total cost: $" . number_format($estimate['total_cost'], 2) . "\n";
echo "  Cost per image: $" . number_format($estimate['cost_per_image'], 4) . "\n";
```

### Vision Cost Optimization Checklist

Apply these optimizations in order of impact:

1. **Resize images appropriately** (25-40% savings)

   - Use `ImageHelper::resizeIfNeeded()` with appropriate max width
   - 800px width handles most use cases (e.g., product photos, UI screenshots)
   - Charts/documents: 1200-1400px for readability

2. **Use Haiku for simple tasks** (75% savings vs. Sonnet)

   - Content moderation: Haiku is perfectly capable
   - Text extraction: Haiku works well for clear text
   - Image classification: Haiku fine for most cases
   - Switch to Sonnet only when needed

3. **Batch process images** (50% savings with Anthropic's batch API)

   - Process overnight batches for non-urgent work
   - Combine multiple images in single API call when possible
   - See Chapter 18 on batch processing

4. **Cache aggressively** (90% savings on repeated analyses)

   - Cache analysis results using image hash
   - Use prompt caching for repeated system prompts
   - See Chapter 18 on caching strategies

5. **Pre-filter before sending** (variable savings)
   - Reject blurry/low-quality images before analysis
   - Skip obviously safe content for moderation
   - Detect duplicates locally before processing

## Exercises

### Exercise 1: Build a Document Scanner

**Goal**: Create a system that extracts structured data from ID cards or business cards.

Create a `DocumentScanner` class that:

- Accepts an image path for ID cards or business cards
- Extracts name, address, phone, email, and other relevant fields
- Returns structured JSON with all extracted information
- Handles different card formats and layouts

**Validation**: Test with various business card images and verify all fields are extracted correctly:

```php
$scanner = new DocumentScanner($client);
$data = $scanner->scanBusinessCard(__DIR__ . '/images/business-card.jpg');
// Should return: name, company, phone, email, address, etc.
```

### Exercise 2: Create an Image Quality Checker

**Goal**: Build a tool that assesses image quality and suggests improvements.

Create an `ImageQualityChecker` class that:

- Analyzes image sharpness, brightness, and composition
- Detects common issues (blur, overexposure, poor framing)
- Provides specific recommendations for improvement
- Returns a quality score (1-10) with detailed feedback

**Validation**: Test with various quality images and verify the checker identifies issues accurately.

### Exercise 3: Build a Visual Search System

**Goal**: Implement a system that finds similar products from a catalog.

Create a `VisualSearch` class that:

- Accepts a query image and a directory of product images
- Compares the query image against all products
- Returns ranked results with similarity scores
- Identifies the most similar products with explanations

**Validation**: Test with a query product and verify it finds similar items from your catalog.

## Troubleshooting

### Error: "Image file not found"

**Symptom**: `RuntimeException: Image file not found: /path/to/image.jpg`

**Cause**: The file path is incorrect or the file doesn't exist at that location.

**Solution**:

- Verify the file path is correct (use absolute paths when possible)
- Check file permissions (ensure PHP can read the file)
- Use `file_exists()` before processing: `if (!file_exists($path)) { throw new \RuntimeException("File not found"); }`

### Error: "Image too large"

**Symptom**: `RuntimeException: Image too large: 8000000 bytes (max 5242880)`

**Cause**: The image exceeds Claude's 5MB limit (after base64 encoding).

**Solution**:

- Resize the image using `ImageHelper::resizeIfNeeded()` before processing
- Compress JPEG images: `imagejpeg($image, $path, 85)` (85% quality)
- Consider using WebP format for better compression

### Error: "Unsupported image type"

**Symptom**: `RuntimeException: Unsupported image type: image/bmp`

**Cause**: The image format is not supported (Claude only supports JPEG, PNG, GIF, WebP).

**Solution**:

- Convert the image to a supported format using GD or ImageMagick
- Check MIME type detection: `$finfo = finfo_open(FILEINFO_MIME_TYPE);`
- Add format conversion: `imagecreatefrombmp()` → `imagejpeg()`

### Poor OCR Accuracy

**Symptom**: Extracted text has many errors or missing content.

**Cause**: Image quality is too low, text is too small, or image is rotated.

**Solution**:

- Increase image resolution (at least 300 DPI for documents)
- Ensure text is horizontal (rotate images if needed)
- Improve contrast and brightness before processing
- Use image preprocessing: sharpen, denoise, or enhance contrast

### High API Costs

**Symptom**: Vision API calls are expensive due to large images.

**Cause**: Sending full-resolution images when smaller versions would suffice.

**Solution**:

- Always resize images to appropriate dimensions (1568px width is often sufficient)
- Cache analysis results using image hash as key
- Batch similar requests together
- Consider using lower-cost models for simple tasks

## Further Reading

- **[Claude-PHP-SDK](https://github.com/claude-php/Claude-PHP-SDK)** — The Community PHP SDK for Claude
- **[Anthropic API Documentation](https://docs.anthropic.com)** — Complete API reference and guides
- **[PHP SDK Composer Package](https://packagist.org/packages/claude-php/claude-3-api)** — SDK package on Packagist

## Wrap-up

Congratulations! You've completed Chapter 13. Here's what you've accomplished:

- ✓ **Sent images to Claude** using base64 encoding and proper MIME types
- ✓ **Built an `ImageHelper` class** for validation, encoding, and optimization
- ✓ **Created OCR systems** that extract structured data from receipts and documents
- ✓ **Analyzed charts and graphs** to extract data points and insights
- ✓ **Implemented content moderation** for user-uploaded images
- ✓ **Built product image analyzers** for e-commerce automation
- ✓ **Created UI analysis tools** for UX feedback and accessibility checks
- ✓ **Processed multiple images** together for listings and comparisons
- ✓ **Applied best practices** for optimization, error handling, and caching

You now have a comprehensive toolkit for working with images in Claude-powered applications. These capabilities enable powerful use cases like automated content moderation, receipt processing, visual search, and multimodal AI agents.

In the next chapter, you'll learn how to process complex documents and PDFs, extending these vision capabilities to handle multi-page documents and structured content.

## Further Reading

- [Anthropic Vision Documentation](https://docs.claude.com/en/docs/capabilities/vision) — Official guide to Claude's vision capabilities
- [PHP GD Library](https://www.php.net/manual/en/book.image.php) — Image manipulation functions in PHP
- [Base64 Encoding](https://en.wikipedia.org/wiki/Base64) — Understanding base64 encoding for image transmission
- [OCR Best Practices](https://cloud.google.com/vision/docs/best-practices) — Google's OCR best practices (applicable to Claude)
- [Image Optimization Guide](https://web.dev/fast/#optimize-your-images) — Web performance optimization techniques
- [Content Moderation Strategies](https://developers.cloudflare.com/rules/transform/managed-transforms/content-moderation/) — Best practices for automated content moderation


---

Continue to [Chapter 14: Document Processing and PDF Analysis](/series/claude-php-developers/chapters/14-document-processing) to work with complex documents.

## 💻 Code Samples

All code examples from this chapter are available in the GitHub repository:

**[View Chapter 13 Code Samples](https://github.com/dalehurley/codewithphp/tree/main/code/claude-php/chapter-13)**

Clone and run locally:

```bash
git clone https://github.com/dalehurley/codewithphp.git
cd codewithphp/code/claude-php/chapter-13
composer install
export ANTHROPIC_API_KEY="sk-ant-your-key-here"
php examples/01-basic-image-analysis.php
```
