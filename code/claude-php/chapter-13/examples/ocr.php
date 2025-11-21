<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Vision\VisionHelper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Claude Vision - OCR (Text Extraction)\n\n";

$vision = new VisionHelper($_ENV['ANTHROPIC_API_KEY']);

// Create image with text
$imagePath = __DIR__ . '/text-image.png';
$img = imagecreatetruecolor(400, 200);
$bg = imagecolorallocate($img, 255, 255, 255);
$text = imagecolorallocate($img, 0, 0, 0);
imagefilledrectangle($img, 0, 0, 400, 200, $bg);
imagestring($img, 5, 20, 50, 'Invoice #12345', $text);
imagestring($img, 5, 20, 80, 'Amount: $299.99', $text);
imagestring($img, 5, 20, 110, 'Date: 2025-01-15', $text);
imagepng($img, $imagePath);
imagedestroy($img);

echo "Extracting text from image...\n";
$result = $vision->analyzeImage($imagePath, 'Extract all text from this image');

echo "\nExtracted text:\n";
echo $result->content[0]['text'] . "\n";

unlink($imagePath);
