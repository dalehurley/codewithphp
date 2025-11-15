<?php

declare(strict_types=1);

class TemperatureConverter
{
    public function celsiusToFahrenheit(float $celsius): float
    {
        return ($celsius * 9/5) + 32;
    }

    public function fahrenheitToCelsius(float $fahrenheit): float
    {
        return ($fahrenheit - 32) * 5/9;
    }

    public function isAbsoluteZero(float $celsius): bool
    {
        return $celsius <= -273.15;
    }
}

// Test the converter
$converter = new TemperatureConverter();

echo "=== Temperature Conversions ===\n";
echo "0°C = " . $converter->celsiusToFahrenheit(0) . "°F\n";
echo "100°C = " . $converter->celsiusToFahrenheit(100) . "°F\n";
echo "32°F = " . $converter->fahrenheitToCelsius(32) . "°C\n";
echo "212°F = " . $converter->fahrenheitToCelsius(212) . "°C\n";

echo "\n=== Absolute Zero Test ===\n";
var_dump($converter->isAbsoluteZero(-300));  // true
var_dump($converter->isAbsoluteZero(-200));  // true
var_dump($converter->isAbsoluteZero(0));     // false
