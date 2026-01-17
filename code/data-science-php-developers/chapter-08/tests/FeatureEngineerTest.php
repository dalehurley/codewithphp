<?php

declare(strict_types=1);

namespace Tests\Chapter08;

use PHPUnit\Framework\TestCase;
use DataScience\ML\FeatureEngineer;

class FeatureEngineerTest extends TestCase
{
    private FeatureEngineer $engineer;

    protected function setUp(): void
    {
        $this->engineer = new FeatureEngineer();
    }

    public function test_min_max_scaling(): void
    {
        $features = [0, 50, 100];
        $scaled = $this->engineer->minMaxScale($features);

        $this->assertEquals([0.0, 0.5, 1.0], $scaled);
    }

    public function test_min_max_scaling_with_identical_values(): void
    {
        $features = [5, 5, 5];
        $scaled = $this->engineer->minMaxScale($features);

        // Should return 0.5 for all values
        $this->assertEquals([0.5, 0.5, 0.5], $scaled);
    }

    public function test_standardization(): void
    {
        $features = [1, 2, 3, 4, 5];
        $standardized = $this->engineer->standardize($features);

        // Mean should be ~0
        $mean = array_sum($standardized) / count($standardized);
        $this->assertEqualsWithDelta(0.0, $mean, 0.001);

        // Std dev should be ~1
        $variance = array_sum(array_map(fn($x) => $x ** 2, $standardized)) / count($standardized);
        $stdDev = sqrt($variance);
        $this->assertEqualsWithDelta(1.0, $stdDev, 0.1);
    }

    public function test_one_hot_encoding(): void
    {
        $categories = ['red', 'blue', 'red', 'green'];
        $result = $this->engineer->oneHotEncode($categories);

        $encoded = $result['encoded'];
        $categoryList = $result['categories'];

        // Should have 3 categories (blue, green, red alphabetically)
        $this->assertCount(4, $encoded);
        $this->assertCount(3, $encoded[0]);
        $this->assertCount(3, $categoryList);

        // Each vector should have exactly one 1
        foreach ($encoded as $vector) {
            $this->assertEquals(1, array_sum($vector));
        }
    }

    public function test_date_features_extraction(): void
    {
        $features = $this->engineer->dateFeatures('2026-01-17');

        $this->assertEquals(2026, $features['year']);
        $this->assertEquals(1, $features['month']);
        $this->assertEquals(17, $features['day']);
        $this->assertEquals(1, $features['quarter']);
        $this->assertContains($features['is_weekend'], [0, 1]);
        $this->assertEquals(5, $features['day_of_week']); // Friday
    }

    public function test_text_features_extraction(): void
    {
        $text = "Hello World";
        $features = $this->engineer->textFeatures($text);

        $this->assertEquals(2, $features['word_count']);
        $this->assertEquals(11, $features['char_count']);
        $this->assertGreaterThan(0, $features['avg_word_length']);
        $this->assertGreaterThan(0, $features['uppercase_ratio']);
    }

    public function test_polynomial_features(): void
    {
        $x = [1, 2];
        $poly = $this->engineer->polynomialFeatures($x, degree: 2);

        // Should create x and x²
        $this->assertCount(4, $poly);
        $this->assertEquals([1, 2, 1, 4], $poly);
    }

    public function test_interaction_features(): void
    {
        $features1 = [1, 2, 3];
        $features2 = [4, 5, 6];

        $interactions = $this->engineer->interactionFeatures($features1, $features2);

        $this->assertEquals([4, 10, 18], $interactions);
    }

    public function test_binning(): void
    {
        $values = [10, 25, 35, 55, 75];
        $bins = [30, 50, 70];

        $binned = $this->engineer->binFeature($values, $bins);

        $this->assertEquals([0, 0, 1, 2, 3], $binned);
    }

    public function test_lag_features(): void
    {
        $values = [1, 2, 3, 4, 5];
        $lagged = $this->engineer->lagFeatures($values, lag: 2);

        $this->assertCount(5, $lagged);
        $this->assertNull($lagged[0]);
        $this->assertNull($lagged[1]);
        $this->assertEquals(1, $lagged[2]);
        $this->assertEquals(2, $lagged[3]);
        $this->assertEquals(3, $lagged[4]);
    }

    public function test_rolling_window_mean(): void
    {
        $values = [1, 2, 3, 4, 5];
        $rolling = $this->engineer->rollingWindow($values, window: 2, function: 'mean');

        $this->assertCount(5, $rolling);
        $this->assertEquals(1.0, $rolling[0]);
        $this->assertEquals(1.5, $rolling[1]);
        $this->assertEquals(2.5, $rolling[2]);
    }

    public function test_target_encoding(): void
    {
        $categories = ['A', 'B', 'A', 'B', 'A'];
        $targets = [10, 20, 15, 25, 12];

        $result = $this->engineer->targetEncode($categories, $targets);
        $encoded = $result['encoded'];
        $mapping = $result['mapping'];

        $this->assertCount(5, $encoded);
        $this->assertArrayHasKey('A', $mapping);
        $this->assertArrayHasKey('B', $mapping);

        // A should have mean of (10 + 15 + 12) / 3 = 12.33
        $this->assertEqualsWithDelta(12.33, $mapping['A'], 0.1);

        // B should have mean of (20 + 25) / 2 = 22.5
        $this->assertEqualsWithDelta(22.5, $mapping['B'], 0.1);
    }

    public function test_throws_exception_on_empty_array(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot scale empty array');

        $this->engineer->minMaxScale([]);
    }

    public function test_throws_exception_on_non_numeric(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All features must be numeric');

        $this->engineer->minMaxScale([1, 'two', 3]);
    }

    public function test_throws_exception_on_invalid_date(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid date format');

        $this->engineer->dateFeatures('not a date');
    }

    public function test_throws_exception_on_mismatched_interaction_lengths(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Feature arrays must have same length');

        $this->engineer->interactionFeatures([1, 2], [1, 2, 3]);
    }
}
