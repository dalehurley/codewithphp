<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Simple Linear Regression
 *
 * Finds best-fit line: y = mx + b using least squares method.
 * This is an educational implementation for learning ML concepts.
 */
class SimpleRegressor
{
    private ?float $slope = null;
    private ?float $intercept = null;

    /**
     * Train the model using least squares method
     *
     * @param array $x Training X values
     * @param array $y Training Y values
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     */
    public function train(array $x, array $y): void
    {
        if (count($x) !== count($y)) {
            throw new \InvalidArgumentException('X and Y must have same length');
        }

        $n = count($x);

        if ($n < 2) {
            throw new \InvalidArgumentException('Need at least 2 data points');
        }

        // Validate all values are numeric
        foreach ($x as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All X values must be numeric');
            }
        }
        foreach ($y as $value) {
            if (!is_numeric($value)) {
                throw new \InvalidArgumentException('All Y values must be numeric');
            }
        }

        // Calculate means
        $meanX = array_sum($x) / $n;
        $meanY = array_sum($y) / $n;

        // Calculate slope (m)
        $numerator = 0;
        $denominator = 0;

        for ($i = 0; $i < $n; $i++) {
            $numerator += ($x[$i] - $meanX) * ($y[$i] - $meanY);
            $denominator += ($x[$i] - $meanX) ** 2;
        }

        if ($denominator == 0) {
            throw new \RuntimeException('Cannot fit line (all X values are identical)');
        }

        $this->slope = $numerator / $denominator;

        // Calculate intercept (b)
        $this->intercept = $meanY - ($this->slope * $meanX);
    }

    /**
     * Predict Y for given X
     *
     * @param float $x Input value
     * @return float Predicted value
     * @throws \RuntimeException
     */
    public function predict(float $x): float
    {
        if ($this->slope === null || $this->intercept === null) {
            throw new \RuntimeException('Model not trained');
        }

        return ($this->slope * $x) + $this->intercept;
    }

    /**
     * Predict multiple values
     *
     * @param array $x Array of input values
     * @return array Array of predicted values
     */
    public function predictBatch(array $x): array
    {
        return array_map(fn($val) => $this->predict($val), $x);
    }

    /**
     * Calculate R² (coefficient of determination)
     *
     * @param array $x Test X values
     * @param array $y Test Y values
     * @return float R² score (1.0 = perfect fit, 0 = baseline, negative = worse than baseline)
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function rSquared(array $x, array $y): float
    {
        if ($this->slope === null || $this->intercept === null) {
            throw new \RuntimeException('Model not trained');
        }

        if (count($x) !== count($y)) {
            throw new \InvalidArgumentException('X and Y must have same length');
        }

        if (count($x) < 2) {
            throw new \InvalidArgumentException('Need at least 2 data points');
        }

        $n = count($x);
        $meanY = array_sum($y) / $n;

        // Total sum of squares
        $sst = 0;
        for ($i = 0; $i < $n; $i++) {
            $sst += ($y[$i] - $meanY) ** 2;
        }

        // Residual sum of squares
        $ssr = 0;
        for ($i = 0; $i < $n; $i++) {
            $predicted = $this->predict($x[$i]);
            $ssr += ($y[$i] - $predicted) ** 2;
        }

        $rSquared = 1 - ($ssr / $sst);

        // R² can be negative for very poor fits
        // Warn if negative (model fits worse than horizontal line)
        if ($rSquared < 0) {
            trigger_error('R² is negative - model fits worse than horizontal line', E_USER_WARNING);
        }

        return $rSquared;
    }

    /**
     * Calculate Mean Absolute Error
     *
     * @param array $x Test X values
     * @param array $y Test Y values
     * @return float MAE
     * @throws \InvalidArgumentException
     */
    public function meanAbsoluteError(array $x, array $y): float
    {
        if (count($x) !== count($y)) {
            throw new \InvalidArgumentException('X and Y must have same length');
        }

        if (empty($x)) {
            throw new \InvalidArgumentException('Arrays cannot be empty');
        }

        $n = count($x);
        $sum = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = $this->predict($x[$i]);
            $sum += abs($y[$i] - $predicted);
        }

        return $sum / $n;
    }

    /**
     * Calculate Root Mean Squared Error
     *
     * @param array $x Test X values
     * @param array $y Test Y values
     * @return float RMSE
     * @throws \InvalidArgumentException
     */
    public function rootMeanSquaredError(array $x, array $y): float
    {
        if (count($x) !== count($y)) {
            throw new \InvalidArgumentException('X and Y must have same length');
        }

        if (empty($x)) {
            throw new \InvalidArgumentException('Arrays cannot be empty');
        }

        $n = count($x);
        $sum = 0;

        for ($i = 0; $i < $n; $i++) {
            $predicted = $this->predict($x[$i]);
            $sum += ($y[$i] - $predicted) ** 2;
        }

        return sqrt($sum / $n);
    }

    /**
     * Calculate Mean Absolute Percentage Error
     *
     * @param array $x Test X values
     * @param array $y Test Y values
     * @return float MAPE (as percentage)
     * @throws \InvalidArgumentException
     */
    public function meanAbsolutePercentageError(array $x, array $y): float
    {
        if (count($x) !== count($y)) {
            throw new \InvalidArgumentException('X and Y must have same length');
        }

        if (empty($x)) {
            throw new \InvalidArgumentException('Arrays cannot be empty');
        }

        $n = count($x);
        $sum = 0;

        for ($i = 0; $i < $n; $i++) {
            if ($y[$i] == 0) {
                throw new \InvalidArgumentException('Cannot calculate MAPE with zero actual values');
            }
            $predicted = $this->predict($x[$i]);
            $sum += abs(($y[$i] - $predicted) / $y[$i]);
        }

        return ($sum / $n) * 100;
    }

    /**
     * Get model parameters
     *
     * @return array Model parameters
     */
    public function getParameters(): array
    {
        return [
            'slope' => $this->slope,
            'intercept' => $this->intercept,
            'equation' => $this->slope !== null && $this->intercept !== null
                ? sprintf('y = %.4fx + %.4f', $this->slope, $this->intercept)
                : null,
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
        if ($this->slope === null || $this->intercept === null) {
            throw new \RuntimeException('Cannot save untrained model');
        }

        $data = [
            'class' => self::class,
            'slope' => $this->slope,
            'intercept' => $this->intercept,
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
        $model->slope = $data['slope'];
        $model->intercept = $data['intercept'];

        return $model;
    }
}
