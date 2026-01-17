<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Cross-Validation for Model Evaluation
 *
 * Provides methods for cross-validation to assess model performance
 * and generalization ability.
 */
class CrossValidator
{
    /**
     * Perform K-fold cross-validation
     *
     * @param object $model Model instance with train() and evaluate() methods
     * @param array $data Training data
     * @param array $labels Training labels
     * @param int $k Number of folds
     * @return array Cross-validation results
     * @throws \InvalidArgumentException
     */
    public function kFold(object $model, array $data, array $labels, int $k = 5): array
    {
        if ($k < 2 || $k > count($data)) {
            throw new \InvalidArgumentException('K must be between 2 and number of samples');
        }

        if (count($data) !== count($labels)) {
            throw new \InvalidArgumentException('Data and labels must have same length');
        }

        $n = count($data);
        $foldSize = (int)floor($n / $k);

        $scores = [];

        for ($fold = 0; $fold < $k; $fold++) {
            // Split data into train and validation
            $validationStart = $fold * $foldSize;
            $validationEnd = min($validationStart + $foldSize, $n);

            $trainData = [];
            $trainLabels = [];
            $validationData = [];
            $validationLabels = [];

            for ($i = 0; $i < $n; $i++) {
                if ($i >= $validationStart && $i < $validationEnd) {
                    $validationData[] = $data[$i];
                    $validationLabels[] = $labels[$i];
                } else {
                    $trainData[] = $data[$i];
                    $trainLabels[] = $labels[$i];
                }
            }

            // Train and evaluate
            $modelClone = clone $model;
            $modelClone->train($trainData, $trainLabels);
            $evaluation = $modelClone->evaluate($validationData, $validationLabels);

            $scores[] = $evaluation['accuracy'];
        }

        return [
            'scores' => $scores,
            'mean_score' => array_sum($scores) / count($scores),
            'std_dev' => $this->calculateStdDev($scores),
            'min_score' => min($scores),
            'max_score' => max($scores),
        ];
    }

    /**
     * Perform stratified K-fold cross-validation
     * Maintains class distribution in each fold
     *
     * @param object $model Model instance
     * @param array $data Training data
     * @param array $labels Training labels
     * @param int $k Number of folds
     * @return array Cross-validation results
     * @throws \InvalidArgumentException
     */
    public function stratifiedKFold(object $model, array $data, array $labels, int $k = 5): array
    {
        if ($k < 2 || $k > count($data)) {
            throw new \InvalidArgumentException('K must be between 2 and number of samples');
        }

        if (count($data) !== count($labels)) {
            throw new \InvalidArgumentException('Data and labels must have same length');
        }

        // Group indices by class
        $classSamples = [];
        foreach ($labels as $i => $label) {
            $classSamples[$label][] = $i;
        }

        // Create stratified folds
        $folds = array_fill(0, $k, ['data' => [], 'labels' => []]);

        foreach ($classSamples as $label => $indices) {
            shuffle($indices);
            $foldSize = (int)ceil(count($indices) / $k);

            for ($fold = 0; $fold < $k; $fold++) {
                $start = $fold * $foldSize;
                $end = min($start + $foldSize, count($indices));

                for ($i = $start; $i < $end; $i++) {
                    $idx = $indices[$i];
                    $folds[$fold]['data'][] = $data[$idx];
                    $folds[$fold]['labels'][] = $labels[$idx];
                }
            }
        }

        // Perform cross-validation
        $scores = [];

        for ($fold = 0; $fold < $k; $fold++) {
            // Use current fold as validation, others as training
            $trainData = [];
            $trainLabels = [];
            $validationData = $folds[$fold]['data'];
            $validationLabels = $folds[$fold]['labels'];

            for ($i = 0; $i < $k; $i++) {
                if ($i !== $fold) {
                    $trainData = array_merge($trainData, $folds[$i]['data']);
                    $trainLabels = array_merge($trainLabels, $folds[$i]['labels']);
                }
            }

            // Train and evaluate
            $modelClone = clone $model;
            $modelClone->train($trainData, $trainLabels);
            $evaluation = $modelClone->evaluate($validationData, $validationLabels);

            $scores[] = $evaluation['accuracy'];
        }

        return [
            'scores' => $scores,
            'mean_score' => array_sum($scores) / count($scores),
            'std_dev' => $this->calculateStdDev($scores),
            'min_score' => min($scores),
            'max_score' => max($scores),
        ];
    }

    /**
     * Perform Leave-One-Out Cross-Validation (LOOCV)
     *
     * @param object $model Model instance
     * @param array $data Training data
     * @param array $labels Training labels
     * @return array Cross-validation results
     * @throws \InvalidArgumentException
     */
    public function leaveOneOut(object $model, array $data, array $labels): array
    {
        if (count($data) !== count($labels)) {
            throw new \InvalidArgumentException('Data and labels must have same length');
        }

        $n = count($data);
        $scores = [];

        for ($i = 0; $i < $n; $i++) {
            // Leave one out for validation
            $trainData = [];
            $trainLabels = [];

            for ($j = 0; $j < $n; $j++) {
                if ($j !== $i) {
                    $trainData[] = $data[$j];
                    $trainLabels[] = $labels[$j];
                }
            }

            // Train and evaluate on single sample
            $modelClone = clone $model;
            $modelClone->train($trainData, $trainLabels);
            $evaluation = $modelClone->evaluate([$data[$i]], [$labels[$i]]);

            $scores[] = $evaluation['accuracy'];
        }

        return [
            'scores' => $scores,
            'mean_score' => array_sum($scores) / count($scores),
            'std_dev' => $this->calculateStdDev($scores),
            'min_score' => min($scores),
            'max_score' => max($scores),
        ];
    }

    /**
     * Calculate standard deviation
     *
     * @param array $values Array of numeric values
     * @return float Standard deviation
     */
    private function calculateStdDev(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }

        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($x) => ($x - $mean) ** 2, $values)) / count($values);
        return sqrt($variance);
    }

    /**
     * Perform time series cross-validation (walk-forward)
     *
     * @param object $model Model instance
     * @param array $data Time series data
     * @param array $labels Time series labels
     * @param int $minTrainSize Minimum training size
     * @param int $testSize Test set size
     * @return array Cross-validation results
     * @throws \InvalidArgumentException
     */
    public function timeSeriesSplit(
        object $model,
        array $data,
        array $labels,
        int $minTrainSize = 10,
        int $testSize = 5
    ): array {
        if (count($data) !== count($labels)) {
            throw new \InvalidArgumentException('Data and labels must have same length');
        }

        $n = count($data);

        if ($minTrainSize + $testSize > $n) {
            throw new \InvalidArgumentException('Not enough data for time series split');
        }

        $scores = [];

        // Walk forward through time
        for ($trainEnd = $minTrainSize; $trainEnd + $testSize <= $n; $trainEnd += $testSize) {
            $trainData = array_slice($data, 0, $trainEnd);
            $trainLabels = array_slice($labels, 0, $trainEnd);
            $testData = array_slice($data, $trainEnd, $testSize);
            $testLabels = array_slice($labels, $trainEnd, $testSize);

            // Train and evaluate
            $modelClone = clone $model;
            $modelClone->train($trainData, $trainLabels);
            $evaluation = $modelClone->evaluate($testData, $testLabels);

            $scores[] = $evaluation['accuracy'];
        }

        return [
            'scores' => $scores,
            'mean_score' => array_sum($scores) / count($scores),
            'std_dev' => $this->calculateStdDev($scores),
            'min_score' => min($scores),
            'max_score' => max($scores),
            'num_splits' => count($scores),
        ];
    }
}
