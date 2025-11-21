<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Vision\VisionHelper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Claude Vision - Image Upload and Analysis\n\n";

$vision = new VisionHelper($_ENV['ANTHROPIC_API_KEY']);

// Create a sample image for testing
$imagePath = __DIR__ . '/test-image.png';
if (!file_exists($imagePath)) {
    // Create a simple test image
    $img = imagecreatetruecolor(200, 100);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $text = imagecolorallocate($img, 0, 0, 0);
    imagefilledrectangle($img, 0, 0, 200, 100, $bg);
    imagestring($img, 5, 50, 40, 'Hello Claude!', $text);
    imagepng($img, $imagePath);
    imagedestroy($img);
    echo "Created test image: {$imagePath}\n\n";
}

echo "Analyzing image...\n";
$result = $vision->analyzeImage($imagePath, 'What do you see in this image?');

echo "\nClaude's analysis:\n";
echo $result->content[0]['text'] . "\n\n";
echo "Tokens used: {$result->usage->inputTokens} in, {$result->usage->outputTokens} out\n";
