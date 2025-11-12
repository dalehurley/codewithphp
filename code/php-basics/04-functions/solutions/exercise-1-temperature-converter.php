<?php

declare(strict_types=1);

/**
 * Exercise 1: Temperature Converter
 * 
 * Create functions to convert between Celsius, Fahrenheit, and Kelvin.
 * 
 * Requirements:
 * - celsiusToFahrenheit(float $celsius): float
 * - fahrenheitToCelsius(float $fahrenheit): float
 * - celsiusToKelvin(float $celsius): float
 * - kelvinToCelsius(float $kelvin): float
 * 
 * Formulas:
 * - C to F: (C × 9/5) + 32
 * - F to C: (F - 32) × 5/9
 * - C to K: C + 273.15
 * - K to C: K - 273.15
 */

// Solution:

function celsiusToFahrenheit(float $celsius): float
{
    return ($celsius * 9 / 5) + 32;
}

function fahrenheitToCelsius(float $fahrenheit): float
{
    return ($fahrenheit - 32) * 5 / 9;
}

function celsiusToKelvin(float $celsius): float
{
    return $celsius + 273.15;
}

function kelvinToCelsius(float $kelvin): float
{
    return $kelvin - 273.15;
}

// Testing the functions
echo "=== Temperature Converter ===" . PHP_EOL . PHP_EOL;

// Test conversions
$tempC = 25;
echo "Temperature: {$tempC}°C" . PHP_EOL;
echo "  = " . round(celsiusToFahrenheit($tempC), 2) . "°F" . PHP_EOL;
echo "  = " . round(celsiusToKelvin($tempC), 2) . "K" . PHP_EOL;
echo PHP_EOL;

$tempF = 77;
echo "Temperature: {$tempF}°F" . PHP_EOL;
echo "  = " . round(fahrenheitToCelsius($tempF), 2) . "°C" . PHP_EOL;
echo PHP_EOL;

$tempK = 298.15;
echo "Temperature: {$tempK}K" . PHP_EOL;
echo "  = " . round(kelvinToCelsius($tempK), 2) . "°C" . PHP_EOL;
echo PHP_EOL;

// Bonus: Create a universal converter
function convertTemperature(float $value, string $from, string $to): float
{
    // First convert to Celsius as intermediate
    $celsius = match ($from) {
        'C' => $value,
        'F' => fahrenheitToCelsius($value),
        'K' => kelvinToCelsius($value),
        default => throw new InvalidArgumentException("Unknown unit: $from")
    };

    // Then convert from Celsius to target
    return match ($to) {
        'C' => $celsius,
        'F' => celsiusToFahrenheit($celsius),
        'K' => celsiusToKelvin($celsius),
        default => throw new InvalidArgumentException("Unknown unit: $to")
    };
}

echo "=== Universal Converter ===" . PHP_EOL;
echo "100°C = " . round(convertTemperature(100, 'C', 'F'), 2) . "°F (boiling point)" . PHP_EOL;
echo "32°F = " . round(convertTemperature(32, 'F', 'C'), 2) . "°C (freezing point)" . PHP_EOL;
echo "273.15K = " . round(convertTemperature(273.15, 'K', 'C'), 2) . "°C (absolute zero)" . PHP_EOL;
