<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Vision\VisionHelper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Claude Vision - Content Moderation\n\n";

$vision = new VisionHelper($_ENV['ANTHROPIC_API_KEY']);

// Create test image
$imagePath = __DIR__ . '/content.png';
$img = imagecreatetruecolor(200, 100);
$bg = imagecolorallocate($img, 200, 255, 200);
imagefilledrectangle($img, 0, 0, 200, 100, $bg);
imagepng($img, $imagePath);
imagedestroy($img);

$moderationPrompt = 'Analyze this image for: 1) Inappropriate content, 2) Violence, 3) Adult content. Rate each as: safe, warning, or unsafe. Provide reasoning.';

echo "Running content moderation...\n";
$result = $vision->analyzeImage($imagePath, $moderationPrompt);

echo "\nModeration result:\n";
echo $result->content[0]['text'] . "\n";

unlink($imagePath);
