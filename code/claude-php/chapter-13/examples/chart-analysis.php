<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use ClaudePHP\Vision\VisionHelper;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

echo "Claude Vision - Chart Analysis\n\n";

$vision = new VisionHelper($_ENV['ANTHROPIC_API_KEY']);

// Create a simple bar chart
$imagePath = __DIR__ . '/chart.png';
$img = imagecreatetruecolor(300, 200);
$bg = imagecolorallocate($img, 255, 255, 255);
$bar = imagecolorallocate($img, 0, 120, 255);
$text = imagecolorallocate($img, 0, 0, 0);

imagefilledrectangle($img, 0, 0, 300, 200, $bg);
imagefilledrectangle($img, 50, 100, 100, 180, $bar);
imagefilledrectangle($img, 120, 80, 170, 180, $bar);
imagefilledrectangle($img, 190, 60, 240, 180, $bar);
imagestring($img, 3, 60, 185, 'Jan', $text);
imagestring($img, 3, 130, 185, 'Feb', $text);
imagestring($img, 3, 200, 185, 'Mar', $text);
imagepng($img, $imagePath);
imagedestroy($img);

echo "Analyzing chart...\n";
$result = $vision->analyzeImage($imagePath, 'Describe the trends shown in this chart');

echo "\nAnalysis:\n";
echo $result->content[0]['text'] . "\n";

unlink($imagePath);
