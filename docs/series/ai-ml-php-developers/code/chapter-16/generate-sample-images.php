<?php

declare(strict_types=1);

/**
 * Generate sample images for Chapter 16 examples
 * This script creates test images if you don't have any available
 */

// Create output directory if it doesn't exist
$dataDir = __DIR__ . '/data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}

// Generate sample.jpg - colorful test pattern
$width = 400;
$height = 300;
$img = imagecreatetruecolor($width, $height);

// Create color bands
$red = imagecolorallocate($img, 255, 0, 0);
$green = imagecolorallocate($img, 0, 255, 0);
$blue = imagecolorallocate($img, 0, 0, 255);
$yellow = imagecolorallocate($img, 255, 255, 0);

imagefilledrectangle($img, 0, 0, $width / 2, $height / 2, $red);
imagefilledrectangle($img, $width / 2, 0, $width, $height / 2, $green);
imagefilledrectangle($img, 0, $height / 2, $width / 2, $height, $blue);
imagefilledrectangle($img, $width / 2, $height / 2, $width, $height, $yellow);

imagejpeg($img, $dataDir . '/sample.jpg', 90);
imagedestroy($img);
echo "✓ Created sample.jpg\n";

// Generate landscape.jpg - gradient landscape
$width = 600;
$height = 400;
$img = imagecreatetruecolor($width, $height);

// Sky gradient (blue to light blue)
for ($y = 0; $y < $height / 2; $y++) {
    $ratio = $y / ($height / 2);
    $r = (int)(135 + (70 * $ratio));
    $g = (int)(206 + (49 * $ratio));
    $b = 235;
    $color = imagecolorallocate($img, $r, $g, $b);
    imagefilledellipse($img, 0, $y, $width, 2, $color);
}

// Ground (green to dark green)
for ($y = $height / 2; $y < $height; $y++) {
    $ratio = ($y - $height / 2) / ($height / 2);
    $r = (int)(34 + (20 * $ratio));
    $g = (int)(139 - (40 * $ratio));
    $b = (int)(34 - (10 * $ratio));
    $color = imagecolorallocate($img, $r, $g, $b);
    imagefilledrectangle($img, 0, $y, $width, $y + 1, $color);
}

// Add a simple "sun"
$sunColor = imagecolorallocate($img, 255, 220, 0);
imagefilledellipse($img, $width - 100, 80, 60, 60, $sunColor);

imagejpeg($img, $dataDir . '/landscape.jpg', 90);
imagedestroy($img);
echo "✓ Created landscape.jpg\n";

// Generate face.jpg - simple face representation
$width = 300;
$height = 300;
$img = imagecreatetruecolor($width, $height);

// Background
$bg = imagecolorallocate($img, 240, 240, 240);
imagefilledrectangle($img, 0, 0, $width, $height, $bg);

// Face (skin tone)
$skin = imagecolorallocate($img, 255, 220, 177);
imagefilledellipse($img, $width / 2, $height / 2, 200, 240, $skin);

// Eyes
$eyeWhite = imagecolorallocate($img, 255, 255, 255);
$eyeBlack = imagecolorallocate($img, 0, 0, 0);

// Left eye
imagefilledellipse($img, $width / 2 - 40, $height / 2 - 30, 40, 30, $eyeWhite);
imagefilledellipse($img, $width / 2 - 40, $height / 2 - 30, 20, 20, $eyeBlack);

// Right eye
imagefilledellipse($img, $width / 2 + 40, $height / 2 - 30, 40, 30, $eyeWhite);
imagefilledellipse($img, $width / 2 + 40, $height / 2 - 30, 20, 20, $eyeBlack);

// Smile
imagearc($img, $width / 2, $height / 2 + 10, 100, 80, 0, 180, $eyeBlack);

imagejpeg($img, $dataDir . '/face.jpg', 90);
imagedestroy($img);
echo "✓ Created face.jpg\n";

echo "\nAll sample images generated successfully in {$dataDir}/\n";
