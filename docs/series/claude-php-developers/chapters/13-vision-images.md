---
title: "13: Vision - Working with Images"
description: "Master Claude's vision capabilities in PHP. Analyze images, extract text with OCR, interpret charts and diagrams, perform visual content moderation, and build multimodal applications."
series: "claude-php-developers"
chapter: 13
order: 13
difficulty: "Expert"
prerequisites:
  - "Understanding of image formats and encoding"
  - "Completed Chapters 00-05"
  - "Familiarity with base64 encoding"
---

![13: Vision - Working with Images](/images/claude-php/chapter-13-hero-full.webp)

<div class="breadcrumbs">
  <a href="/">Home</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series">Series</a>
  <span class="breadcrumbs-separator">›</span>
  <a href="/series/claude-php-developers">Claude for PHP Developers</a>
  <span class="breadcrumbs-separator">›</span>
  <span>Chapter 13</span>
</div>

# Chapter 13: Vision - Working with Images

## Overview

Claude's vision capabilities enable it to analyze, understand, and extract information from images. This opens up powerful use cases: automated content moderation, receipt processing, chart analysis, visual quality control, UI/UX feedback, and much more.

In this chapter, you'll learn how to send images to Claude, analyze visual content, extract text with OCR, interpret data visualizations, and build practical multimodal applications that combine text and images.

**What You'll Build**: An automated system that processes product images, extracts specifications from screenshots, analyzes charts, and moderates user-uploaded content.

## Prerequisites

Before starting, ensure you have:

- ✓ **Understanding of image formats** (JPEG, PNG, WebP)
- ✓ **Base64 encoding knowledge** for image transmission
- ✓ **File handling experience** in PHP
- ✓ **Completed Chapters 00-05** of this series

**Estimated Time**: 45-60 minutes

## Supported Image Formats

Claude supports these image formats:

- **JPEG** (image/jpeg) - Most common, good for photos
- **PNG** (image/png) - Supports transparency, good for screenshots
- **GIF** (image/gif) - Animated and static images
- **WebP** (image/webp) - Modern format, excellent compression

**Maximum size**: 5MB per image (after base64 encoding: ~3.75MB original)

## Step 1: Sending Your First Image

Images are sent as base64-encoded content blocks:

```php
<?php
# filename: examples/01-basic-image-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

// Load and encode image
$imagePath = __DIR__ . '/images/product-photo.jpg';
$imageData = file_get_contents($imagePath);
$base64Image = base64_encode($imageData);

// Detect MIME type
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $imagePath);
finfo_close($finfo);

// Send image to Claude
$response = $client->messages()->create([
    'model' => 'claude-sonnet-4-20250514',
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
]);

echo "Image Analysis:\n";
echo $response->content[0]->text . "\n";
```

## Step 2: Image Helper Class

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

## Step 3: OCR - Extracting Text from Images

Claude excels at extracting text from images:

```php
<?php
# filename: examples/02-ocr-text-extraction.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

class ReceiptProcessor
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function processReceipt(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
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
        ]);

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
```

## Step 4: Analyzing Charts and Graphs

Extract insights from data visualizations:

```php
<?php
# filename: examples/03-chart-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

class ChartAnalyzer
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function analyzeChart(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
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
        ]);

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

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 3000,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $content
                ]
            ]
        ]);

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
```

## Step 5: Content Moderation

Analyze images for inappropriate or unsafe content:

```php
<?php
# filename: examples/04-content-moderation.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

class ImageModerator
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function moderateImage(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
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
        ]);

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
```

## Step 6: Product Image Analysis

Extract product details from images:

```php
<?php
# filename: examples/05-product-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

class ProductImageAnalyzer
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function analyzeProductImage(string $imagePath): array
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
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
        ]);

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

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 1024,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        ]);

        return $response->content[0]->text;
    }

    public function generateProductTitle(string $imagePath): string
    {
        $imageContent = ImageHelper::prepareImage($imagePath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
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
        ]);

        return trim($response->content[0]->text);
    }
}

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$analyzer = new ProductImageAnalyzer($client);

// Analyze product
$productData = $analyzer->analyzeProductImage(__DIR__ . '/images/product.jpg');
echo "Product Analysis:\n";
echo json_encode($productData, JSON_PRETTY_PRINT) . "\n\n";

// Generate title
$title = $analyzer->generateProductTitle(__DIR__ . '/images/product.jpg');
echo "Generated Title: {$title}\n";
```

## Step 7: Screenshot and UI Analysis

Analyze screenshots and provide UX feedback:

```php
<?php
# filename: examples/06-screenshot-analysis.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

class UIAnalyzer
{
    public function __construct(
        private Anthropic $client
    ) {}

    public function analyzeUI(string $screenshotPath): array
    {
        $imageContent = ImageHelper::prepareImage($screenshotPath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
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
        ]);

        $text = $response->content[0]->text;

        if (preg_match('/```json\s*(\{.*?\})\s*```/s', $text, $matches)) {
            return json_decode($matches[1], true) ?? [];
        }

        return ['raw_analysis' => $text];
    }

    public function extractText(string $screenshotPath): array
    {
        $imageContent = ImageHelper::prepareImage($screenshotPath);

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
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
        ]);

        return [
            'extracted_text' => $response->content[0]->text,
            'source' => basename($screenshotPath)
        ];
    }
}

$client = Anthropic::factory()
    ->withApiKey(getenv('ANTHROPIC_API_KEY'))
    ->make();

$analyzer = new UIAnalyzer($client);

$uiAnalysis = $analyzer->analyzeUI(__DIR__ . '/images/app-screenshot.png');
echo "UI Analysis:\n";
echo json_encode($uiAnalysis, JSON_PRETTY_PRINT) . "\n";
```

## Step 8: Multi-Image Workflows

Process multiple images together:

```php
<?php
# filename: examples/07-multi-image-workflow.php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Anthropic\Anthropic;
use App\Vision\ImageHelper;

class MultiImageProcessor
{
    public function __construct(
        private Anthropic $client
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

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        ]);

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

        $response = $this->client->messages()->create([
            'model' => 'claude-sonnet-4-20250514',
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $content]
            ]
        ]);

        return [
            'analysis' => $response->content[0]->text,
            'images_analyzed' => count($imagePaths)
        ];
    }
}
```

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

## Key Takeaways

- ✓ Claude can analyze images in JPEG, PNG, GIF, and WebP formats
- ✓ Images must be base64-encoded and include MIME type
- ✓ Maximum image size is 5MB (3.75MB before encoding)
- ✓ OCR capabilities extract text from receipts, documents, screenshots
- ✓ Chart analysis extracts data and insights from visualizations
- ✓ Content moderation identifies inappropriate or unsafe images
- ✓ Product image analysis enables automated catalog creation
- ✓ Multiple images can be processed together for comparison
- ✓ Always resize large images to optimize performance and cost
- ✓ Combine vision with tool use for powerful multimodal agents

<ChapterCheckbox
  seriesId="claude-php-developers"
  chapterId="13"
  label="You've mastered Claude's vision capabilities!"
/>

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
