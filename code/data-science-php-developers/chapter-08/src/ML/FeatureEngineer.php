<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Feature Engineering Toolkit
 *
 * Collection of methods for transforming and creating features from raw data.
 * This is an educational implementation for learning ML concepts.
 */
class FeatureEngineer
{
    /**
     * Normalize features to 0-1 range (Min-Max scaling)
     *
     * @param array $features Array of numeric values
     * @return array Scaled values in [0, 1] range
     * @throws \InvalidArgumentException
     */
    public function minMaxScale(array $features): array
    {
        if (empty($features)) {
            throw new \InvalidArgumentException('Cannot scale empty array');
        }

        // Validate all numeric
        foreach ($features as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All features must be numeric');
            }
        }

        $min = min($features);
        $max = max($features);
        $range = $max - $min;

        if ($range == 0) {
            // All values are identical - return midpoint
            return array_fill(0, count($features), 0.5);
        }

        return array_map(fn($x) => ($x - $min) / $range, $features);
    }

    /**
     * Standardize features (mean=0, std=1) using Z-score normalization
     *
     * @param array $features Array of numeric values
     * @return array Standardized values
     * @throws \InvalidArgumentException
     */
    public function standardize(array $features): array
    {
        if (empty($features)) {
            throw new \InvalidArgumentException('Cannot standardize empty array');
        }

        // Validate all numeric
        foreach ($features as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All features must be numeric');
            }
        }

        $n = count($features);
        $mean = array_sum($features) / $n;
        $variance = array_sum(array_map(fn($x) => ($x - $mean) ** 2, $features)) / $n;
        $std = sqrt($variance);

        if ($std == 0) {
            return array_fill(0, $n, 0);
        }

        return array_map(fn($x) => ($x - $mean) / $std, $features);
    }

    /**
     * Create polynomial features (x, x², x³, ...)
     *
     * @param array $x Input values
     * @param int $degree Maximum polynomial degree
     * @return array Polynomial features
     * @throws \InvalidArgumentException
     */
    public function polynomialFeatures(array $x, int $degree = 2): array
    {
        if ($degree < 1) {
            throw new \InvalidArgumentException('Degree must be at least 1');
        }

        $features = [];

        for ($i = 1; $i <= $degree; $i++) {
            $features = array_merge($features, array_map(fn($val) => $val ** $i, $x));
        }

        return $features;
    }

    /**
     * Create interaction features (x₁ × x₂)
     *
     * @param array $features1 First feature array
     * @param array $features2 Second feature array
     * @return array Interaction features
     * @throws \InvalidArgumentException
     */
    public function interactionFeatures(array $features1, array $features2): array
    {
        if (count($features1) !== count($features2)) {
            throw new \InvalidArgumentException('Feature arrays must have same length');
        }

        $interactions = [];

        for ($i = 0; $i < count($features1); $i++) {
            $interactions[] = $features1[$i] * $features2[$i];
        }

        return $interactions;
    }

    /**
     * Bin continuous features into discrete categories
     *
     * @param array $values Values to bin
     * @param array $bins Bin boundaries (sorted ascending)
     * @return array Bin indices
     */
    public function binFeature(array $values, array $bins): array
    {
        return array_map(function($value) use ($bins) {
            for ($i = 0; $i < count($bins); $i++) {
                if ($value <= $bins[$i]) {
                    return $i;
                }
            }
            return count($bins);
        }, $values);
    }

    /**
     * One-hot encode categorical features
     *
     * @param array $categories Category values
     * @return array ['encoded' => array, 'categories' => array]
     * @throws \InvalidArgumentException
     */
    public function oneHotEncode(array $categories): array
    {
        if (empty($categories)) {
            throw new \InvalidArgumentException('Cannot encode empty array');
        }

        // Get unique categories and sort for consistency
        $uniqueCategories = array_values(array_unique($categories));
        sort($uniqueCategories);

        // Create lookup for faster encoding
        $categoryIndex = array_flip($uniqueCategories);

        $encoded = [];
        foreach ($categories as $category) {
            if (!isset($categoryIndex[$category])) {
                throw new \InvalidArgumentException("Unknown category: {$category}");
            }

            $vector = array_fill(0, count($uniqueCategories), 0);
            $vector[$categoryIndex[$category]] = 1;
            $encoded[] = $vector;
        }

        return [
            'encoded' => $encoded,
            'categories' => $uniqueCategories,  // Return mapping for reference
        ];
    }

    /**
     * Create date-based features from date string
     *
     * @param string $date Date string (any format strtotime() accepts)
     * @return array Date-based features
     * @throws \InvalidArgumentException
     */
    public function dateFeatures(string $date): array
    {
        $timestamp = strtotime($date);

        if ($timestamp === false) {
            throw new \InvalidArgumentException("Invalid date format: {$date}");
        }

        return [
            'year' => (int)date('Y', $timestamp),
            'month' => (int)date('m', $timestamp),
            'day' => (int)date('d', $timestamp),
            'day_of_week' => (int)date('N', $timestamp),
            'day_of_year' => (int)date('z', $timestamp),
            'quarter' => (int)ceil(date('m', $timestamp) / 3),
            'is_weekend' => in_array(date('N', $timestamp), [6, 7]) ? 1 : 0,
        ];
    }

    /**
     * Create basic text features
     *
     * @param string $text Input text
     * @return array Text features
     */
    public function textFeatures(string $text): array
    {
        $words = str_word_count(strtolower($text), 1);
        $wordCount = count($words);

        return [
            'word_count' => $wordCount,
            'char_count' => strlen($text),
            'avg_word_length' => $wordCount > 0 ? strlen($text) / $wordCount : 0.0,
            'uppercase_ratio' => $this->uppercaseRatio($text),
            'digit_count' => preg_match_all('/\d/', $text, $matches) ?: 0,
            'special_char_count' => preg_match_all('/[^a-zA-Z0-9\s]/', $text, $matches) ?: 0,
        ];
    }

    /**
     * Calculate ratio of uppercase letters
     *
     * @param string $text Input text
     * @return float Uppercase ratio (0.0 to 1.0)
     */
    private function uppercaseRatio(string $text): float
    {
        $letters = preg_match_all('/[a-zA-Z]/', $text, $matches);
        if ($letters == 0) {
            return 0.0;
        }

        $uppercase = preg_match_all('/[A-Z]/', $text, $matches);
        return $uppercase / $letters;
    }

    /**
     * Create lagged features (for time series)
     *
     * @param array $values Time series values
     * @param int $lag Lag period
     * @return array Lagged values (padded with null)
     */
    public function lagFeatures(array $values, int $lag = 1): array
    {
        if ($lag < 1) {
            throw new \InvalidArgumentException('Lag must be at least 1');
        }

        $lagged = array_fill(0, $lag, null);
        for ($i = 0; $i < count($values) - $lag; $i++) {
            $lagged[] = $values[$i];
        }

        return $lagged;
    }

    /**
     * Create rolling window features (moving average, etc.)
     *
     * @param array $values Input values
     * @param int $window Window size
     * @param string $function 'mean', 'sum', 'min', 'max'
     * @return array Rolling window values
     * @throws \InvalidArgumentException
     */
    public function rollingWindow(array $values, int $window, string $function = 'mean'): array
    {
        if ($window < 1 || $window > count($values)) {
            throw new \InvalidArgumentException('Invalid window size');
        }

        $result = [];

        for ($i = 0; $i < count($values); $i++) {
            $start = max(0, $i - $window + 1);
            $windowValues = array_slice($values, $start, $i - $start + 1);

            $result[] = match($function) {
                'mean' => array_sum($windowValues) / count($windowValues),
                'sum' => array_sum($windowValues),
                'min' => min($windowValues),
                'max' => max($windowValues),
                default => throw new \InvalidArgumentException("Unknown function: {$function}"),
            };
        }

        return $result;
    }

    /**
     * Target encoding (mean encoding) for categorical features
     *
     * @param array $categories Category values
     * @param array $targets Target values
     * @return array Encoded values (category mean)
     * @throws \InvalidArgumentException
     */
    public function targetEncode(array $categories, array $targets): array
    {
        if (count($categories) !== count($targets)) {
            throw new \InvalidArgumentException('Categories and targets must have same length');
        }

        // Calculate mean target for each category
        $categoryStats = [];
        foreach ($categories as $i => $category) {
            if (!isset($categoryStats[$category])) {
                $categoryStats[$category] = ['sum' => 0, 'count' => 0];
            }
            $categoryStats[$category]['sum'] += $targets[$i];
            $categoryStats[$category]['count']++;
        }

        $categoryMeans = [];
        foreach ($categoryStats as $category => $stats) {
            $categoryMeans[$category] = $stats['sum'] / $stats['count'];
        }

        // Encode each category with its mean
        $encoded = [];
        foreach ($categories as $category) {
            $encoded[] = $categoryMeans[$category];
        }

        return [
            'encoded' => $encoded,
            'mapping' => $categoryMeans,
        ];
    }
}
