<?php

declare(strict_types=1);

/**
 * Generate sample stock price and website traffic data for Chapter 19 examples.
 */

// Generate 2 years of stock prices with realistic patterns
function generateStockPrices(int $days = 503): array
{
    $data = [];
    $basePrice = 120.0;
    $trend = 0.05;  // Slight upward trend
    $volatility = 2.5;

    $startDate = new DateTime('2023-01-03');

    for ($i = 0; $i < $days; $i++) {
        // Skip weekends
        while ($startDate->format('N') >= 6) {
            $startDate->modify('+1 day');
        }

        // Price with trend + random walk
        $trendComponent = $trend * $i;
        $randomComponent = (mt_rand(-100, 100) / 100) * $volatility;
        $seasonalComponent = sin($i / 30) * 3;  // Monthly seasonality

        $close = $basePrice + $trendComponent + $randomComponent + $seasonalComponent;
        $high = $close + mt_rand(0, 300) / 100;
        $low = $close - mt_rand(0, 300) / 100;
        $open = $low + ($high - $low) * (mt_rand(20, 80) / 100);
        $volume = mt_rand(10000000, 25000000);

        $data[] = [
            'date' => $startDate->format('Y-m-d'),
            'open' => round($open, 2),
            'high' => round($high, 2),
            'low' => round($low, 2),
            'close' => round($close, 2),
            'volume' => $volume,
        ];

        $startDate->modify('+1 day');
    }

    return $data;
}

// Generate website traffic data with strong weekly seasonality
function generateWebsiteTraffic(int $days = 365): array
{
    $data = [];
    $baseTraffic = 5000;
    $trend = 10;  // Growing traffic

    $startDate = new DateTime('2023-01-01');

    for ($i = 0; $i < $days; $i++) {
        $dayOfWeek = (int)$startDate->format('N');  // 1=Monday, 7=Sunday

        // Weekly pattern: higher on weekends
        $seasonalMultiplier = match ($dayOfWeek) {
            6 => 1.5,   // Saturday
            7 => 1.6,   // Sunday
            1 => 0.9,   // Monday
            5 => 1.2,   // Friday
            default => 1.0,
        };

        $trendComponent = $trend * $i;
        $randomComponent = mt_rand(-500, 500);

        $visitors = (int)(($baseTraffic + $trendComponent) * $seasonalMultiplier + $randomComponent);
        $visitors = max(1000, $visitors);  // Minimum 1000 visitors

        $data[] = [
            'date' => $startDate->format('Y-m-d'),
            'visitors' => $visitors,
        ];

        $startDate->modify('+1 day');
    }

    return $data;
}

// Save to CSV
function saveCSV(string $filename, array $data, array $headers): void
{
    $handle = fopen($filename, 'w');

    // Write header
    fputcsv($handle, $headers);

    // Write data
    foreach ($data as $row) {
        fputcsv($handle, $row);
    }

    fclose($handle);
}

// Main execution
echo "Generating sample data for Chapter 19...\n\n";

// Generate stock prices
echo "1. Generating stock prices (503 trading days)...\n";
$stockData = generateStockPrices(503);
saveCSV(
    __DIR__ . '/data/sample_stock_prices.csv',
    $stockData,
    ['date', 'open', 'high', 'low', 'close', 'volume']
);
echo "   ✓ Created data/sample_stock_prices.csv\n";
printf("   Range: %s to %s\n", $stockData[0]['date'], $stockData[count($stockData) - 1]['date']);
printf(
    "   Price range: $%.2f - $%.2f\n",
    min(array_column($stockData, 'close')),
    max(array_column($stockData, 'close'))
);

// Generate website traffic
echo "\n2. Generating website traffic (365 days)...\n";
$trafficData = generateWebsiteTraffic(365);
saveCSV(
    __DIR__ . '/data/website_traffic.csv',
    $trafficData,
    ['date', 'visitors']
);
echo "   ✓ Created data/website_traffic.csv\n";
printf("   Range: %s to %s\n", $trafficData[0]['date'], $trafficData[count($trafficData) - 1]['date']);
printf(
    "   Visitors range: %d - %d\n",
    min(array_column($trafficData, 'visitors')),
    max(array_column($trafficData, 'visitors'))
);

echo "\n✓ Sample data generation complete!\n";
echo "  Run the examples with: php quick-start.php\n";
