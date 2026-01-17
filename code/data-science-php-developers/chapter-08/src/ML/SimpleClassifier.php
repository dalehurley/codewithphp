<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Simple K-Nearest Neighbors (KNN) Classifier
 *
 * Predicts class based on majority vote of K nearest neighbors.
 * This is an educational implementation for learning ML concepts.
 */
class SimpleClassifier
{
    private array $trainingData = [];
    private array $trainingLabels = [];

    /**
     * Train the model with labeled data
     *
     * @param array $data Training data (array of feature arrays)
     * @param array $labels Training labels (array of strings)
     * @throws \InvalidArgumentException
     */
    public function train(array $data, array $labels): void
    {
        if (count($data) !== count($labels)) {
            throw new \InvalidArgumentException('Data and labels must have same length');
        }

        if (empty($data)) {
            throw new \InvalidArgumentException('Training data cannot be empty');
        }

        $this->trainingData = $data;
        $this->trainingLabels = $labels;
    }

    /**
     * Predict class for new data point
     *
     * @param array $point Feature vector to classify
     * @param int $k Number of nearest neighbors to consider
     * @return string Predicted class label
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function predict(array $point, int $k = 3): string
    {
        if (empty($this->trainingData)) {
            throw new \RuntimeException('Model not trained');
        }

        // Validate input dimensions match training data
        $expectedDimensions = count($this->trainingData[0]);
        if (count($point) !== $expectedDimensions) {
            throw new \InvalidArgumentException(
                "Input has " . count($point) . " features but model expects {$expectedDimensions}"
            );
        }

        // Validate k is reasonable
        if ($k < 1 || $k > count($this->trainingData)) {
            throw new \InvalidArgumentException(
                "K must be between 1 and " . count($this->trainingData)
            );
        }

        // Calculate distances to all training points
        $distances = [];

        foreach ($this->trainingData as $i => $trainPoint) {
            $distance = $this->euclideanDistance($point, $trainPoint);
            $distances[] = [
                'distance' => $distance,
                'label' => $this->trainingLabels[$i],
            ];
        }

        // Sort by distance
        usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);

        // Get K nearest neighbors
        $neighbors = array_slice($distances, 0, $k);

        // Count votes
        $votes = [];
        foreach ($neighbors as $neighbor) {
            $label = $neighbor['label'];
            $votes[$label] = ($votes[$label] ?? 0) + 1;
        }

        // Return majority vote
        arsort($votes);
        return array_key_first($votes);
    }

    /**
     * Predict with confidence scores
     *
     * @param array $point Feature vector to classify
     * @param int $k Number of nearest neighbors to consider
     * @return array ['prediction' => string, 'probabilities' => array]
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function predictWithProbability(array $point, int $k = 3): array
    {
        if (empty($this->trainingData)) {
            throw new \RuntimeException('Model not trained');
        }

        // Validate input dimensions
        $expectedDimensions = count($this->trainingData[0]);
        if (count($point) !== $expectedDimensions) {
            throw new \InvalidArgumentException(
                "Input has " . count($point) . " features but model expects {$expectedDimensions}"
            );
        }

        // Validate k
        if ($k < 1 || $k > count($this->trainingData)) {
            throw new \InvalidArgumentException(
                "K must be between 1 and " . count($this->trainingData)
            );
        }

        // Calculate distances
        $distances = [];
        foreach ($this->trainingData as $i => $trainPoint) {
            $distances[] = [
                'distance' => $this->euclideanDistance($point, $trainPoint),
                'label' => $this->trainingLabels[$i],
            ];
        }

        usort($distances, fn($a, $b) => $a['distance'] <=> $b['distance']);
        $neighbors = array_slice($distances, 0, $k);

        // Calculate probabilities
        $votes = [];
        foreach ($neighbors as $neighbor) {
            $label = $neighbor['label'];
            $votes[$label] = ($votes[$label] ?? 0) + 1;
        }

        $probabilities = [];
        foreach ($votes as $label => $count) {
            $probabilities[$label] = $count / $k;
        }

        arsort($probabilities);

        return [
            'prediction' => array_key_first($probabilities),
            'probabilities' => $probabilities,
        ];
    }

    /**
     * Calculate Euclidean distance between two points
     *
     * @param array $point1 First point
     * @param array $point2 Second point
     * @return float Distance
     * @throws \InvalidArgumentException
     */
    private function euclideanDistance(array $point1, array $point2): float
    {
        if (count($point1) !== count($point2)) {
            throw new \InvalidArgumentException('Points must have same dimensions');
        }

        $sum = 0;
        for ($i = 0; $i < count($point1); $i++) {
            // Add type safety
            if (!is_numeric($point1[$i]) || !is_numeric($point2[$i])) {
                throw new \InvalidArgumentException('All point values must be numeric');
            }
            $sum += ($point1[$i] - $point2[$i]) ** 2;
        }

        return sqrt($sum);
    }

    /**
     * Evaluate model accuracy
     *
     * @param array $testData Test data points
     * @param array $testLabels True labels
     * @return array Evaluation metrics
     */
    public function evaluate(array $testData, array $testLabels): array
    {
        $correct = 0;
        $total = count($testData);
        $predictions = [];

        foreach ($testData as $i => $point) {
            $prediction = $this->predict($point);
            $predictions[] = $prediction;

            if ($prediction === $testLabels[$i]) {
                $correct++;
            }
        }

        return [
            'accuracy' => $correct / $total,
            'correct' => $correct,
            'total' => $total,
            'predictions' => $predictions,
        ];
    }

    /**
     * Calculate precision, recall, and F1-score
     *
     * @param array $testData Test data points
     * @param array $testLabels True labels
     * @return array Detailed evaluation metrics
     */
    public function evaluateDetailed(array $testData, array $testLabels): array
    {
        $predictions = [];
        $classes = array_unique($testLabels);

        // Get predictions
        foreach ($testData as $point) {
            $predictions[] = $this->predict($point);
        }

        // Calculate per-class metrics
        $metrics = [];
        foreach ($classes as $class) {
            $tp = 0; // True positives
            $fp = 0; // False positives
            $fn = 0; // False negatives

            for ($i = 0; $i < count($testLabels); $i++) {
                $actual = $testLabels[$i];
                $predicted = $predictions[$i];

                if ($predicted === $class && $actual === $class) {
                    $tp++;
                } elseif ($predicted === $class && $actual !== $class) {
                    $fp++;
                } elseif ($predicted !== $class && $actual === $class) {
                    $fn++;
                }
            }

            $precision = ($tp + $fp) > 0 ? $tp / ($tp + $fp) : 0.0;
            $recall = ($tp + $fn) > 0 ? $tp / ($tp + $fn) : 0.0;
            $f1 = ($precision + $recall) > 0 ? 2 * ($precision * $recall) / ($precision + $recall) : 0.0;

            $metrics[$class] = [
                'precision' => $precision,
                'recall' => $recall,
                'f1_score' => $f1,
                'support' => $tp + $fn,
            ];
        }

        // Calculate macro averages
        $avgPrecision = array_sum(array_column($metrics, 'precision')) / count($classes);
        $avgRecall = array_sum(array_column($metrics, 'recall')) / count($classes);
        $avgF1 = array_sum(array_column($metrics, 'f1_score')) / count($classes);

        // Calculate overall accuracy
        $correct = 0;
        for ($i = 0; $i < count($testLabels); $i++) {
            if ($predictions[$i] === $testLabels[$i]) {
                $correct++;
            }
        }
        $accuracy = $correct / count($testLabels);

        return [
            'accuracy' => $accuracy,
            'per_class' => $metrics,
            'macro_avg' => [
                'precision' => $avgPrecision,
                'recall' => $avgRecall,
                'f1_score' => $avgF1,
            ],
            'predictions' => $predictions,
        ];
    }

    /**
     * Generate confusion matrix
     *
     * @param array $testData Test data points
     * @param array $testLabels True labels
     * @return array Confusion matrix and class labels
     */
    public function confusionMatrix(array $testData, array $testLabels): array
    {
        $predictions = [];
        foreach ($testData as $point) {
            $predictions[] = $this->predict($point);
        }

        $classes = array_unique(array_merge($testLabels, $predictions));
        sort($classes);

        $matrix = [];
        foreach ($classes as $actual) {
            $matrix[$actual] = [];
            foreach ($classes as $predicted) {
                $matrix[$actual][$predicted] = 0;
            }
        }

        for ($i = 0; $i < count($testLabels); $i++) {
            $actual = $testLabels[$i];
            $predicted = $predictions[$i];
            $matrix[$actual][$predicted]++;
        }

        return [
            'matrix' => $matrix,
            'classes' => $classes,
        ];
    }

    /**
     * Save model to file
     *
     * @param string $filepath Path to save model
     * @throws \RuntimeException
     */
    public function save(string $filepath): void
    {
        if (empty($this->trainingData)) {
            throw new \RuntimeException('Cannot save untrained model');
        }

        $data = [
            'class' => self::class,
            'trainingData' => $this->trainingData,
            'trainingLabels' => $this->trainingLabels,
        ];

        $json = json_encode($data, JSON_PRETTY_PRINT);
        if ($json === false) {
            throw new \RuntimeException('Failed to serialize model');
        }

        if (file_put_contents($filepath, $json) === false) {
            throw new \RuntimeException("Failed to write model to {$filepath}");
        }
    }

    /**
     * Load model from file
     *
     * @param string $filepath Path to model file
     * @return self Loaded model instance
     * @throws \RuntimeException
     */
    public static function load(string $filepath): self
    {
        if (!file_exists($filepath)) {
            throw new \RuntimeException("Model file not found: {$filepath}");
        }

        $json = file_get_contents($filepath);
        if ($json === false) {
            throw new \RuntimeException("Failed to read model file: {$filepath}");
        }

        $data = json_decode($json, true);
        if ($data === null) {
            throw new \RuntimeException("Failed to parse model file: {$filepath}");
        }

        if (!isset($data['class']) || $data['class'] !== self::class) {
            throw new \RuntimeException("Invalid model file format");
        }

        $model = new self();
        $model->trainingData = $data['trainingData'];
        $model->trainingLabels = $data['trainingLabels'];

        return $model;
    }
}
