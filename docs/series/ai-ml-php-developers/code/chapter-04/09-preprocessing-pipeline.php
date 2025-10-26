<?php

declare(strict_types=1);

/**
 * Chapter 04: Data Collection and Preprocessing
 * Example 9: Complete Preprocessing Pipeline
 * 
 * Demonstrates: OOP pipeline, method chaining, reusable preprocessing workflow
 */

/**
 * Data Preprocessing Pipeline
 * 
 * Combines loading, cleaning, normalization, and encoding
 * into a reusable workflow.
 */
class PreprocessingPipeline
{
    private array $data = [];
    private array $transformations = [];
    private array $parameters = []; // Store preprocessing parameters for reuse

    public function load(string $source, string $type = 'csv'): self
    {
        $this->data = match ($type) {
            'csv' => $this->loadCsv($source),
            'json' => $this->loadJson($source),
            'database' => $this->loadDatabase($source),
            default => throw new InvalidArgumentException("Unsupported type: $type")
        };

        return $this;
    }

    public function handleMissing(string $column, string $strategy = 'drop'): self
    {
        $this->data = match ($strategy) {
            'drop' => array_filter($this->data, fn($row) => !empty($row[$column])),
            'mean' => $this->imputeMean($column),
            'mode' => $this->imputeMode($column),
            'zero' => array_map(fn($row) => [
                ...$row,
                $column => $row[$column] ?? 0
            ], $this->data),
            default => $this->data
        };

        $this->transformations[] = "HandleMissing($column, $strategy)";
        return $this;
    }

    public function normalize(string $column, string $method = 'minmax'): self
    {
        $this->data = match ($method) {
            'minmax' => $this->minMaxNormalize($column),
            'zscore' => $this->zScoreNormalize($column),
            default => $this->data
        };

        $this->transformations[] = "Normalize($column, $method)";
        return $this;
    }

    public function encode(string $column, string $method = 'label'): self
    {
        $this->data = match ($method) {
            'label' => $this->labelEncode($column),
            'onehot' => $this->oneHotEncode($column),
            'frequency' => $this->frequencyEncode($column),
            default => $this->data
        };

        $this->transformations[] = "Encode($column, $method)";
        return $this;
    }

    public function get(): array
    {
        return $this->data;
    }

    public function save(string $path): void
    {
        // Ensure directory exists
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new RuntimeException("Could not create directory: $dir");
            }
        }

        file_put_contents($path, json_encode($this->data, JSON_PRETTY_PRINT));
    }

    /**
     * Save preprocessing parameters for later reuse
     * Critical for applying same transformations to new data in production
     */
    public function saveParameters(string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new RuntimeException("Could not create directory: $dir");
            }
        }

        $params = [
            'transformations' => $this->transformations,
            'parameters' => $this->parameters,
            'created_at' => date('Y-m-d H:i:s')
        ];

        file_put_contents($path, json_encode($params, JSON_PRETTY_PRINT));
    }

    /**
     * Load preprocessing parameters to apply to new data
     */
    public function loadParameters(string $path): self
    {
        if (!file_exists($path)) {
            throw new RuntimeException("Parameters file not found: $path");
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException("Could not read parameters file");
        }

        $params = json_decode($content, true);
        if ($params === null) {
            throw new RuntimeException("Invalid JSON in parameters file");
        }

        $this->parameters = $params['parameters'] ?? [];
        $this->transformations = $params['transformations'] ?? [];

        return $this;
    }

    /**
     * Get saved parameters
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function summary(): string
    {
        $output = "Pipeline Summary:\n";
        $output .= "- Records: " . count($this->data) . "\n";
        $output .= "- Transformations applied:\n";
        foreach ($this->transformations as $t) {
            $output .= "  • $t\n";
        }
        return $output;
    }

    // Private helper methods
    private function loadCsv(string $path): array
    {
        $file = fopen($path, 'r');
        if ($file === false) {
            throw new RuntimeException("Could not open file: $path");
        }

        $headers = fgetcsv($file, 0, ',', '"', '\\');
        if ($headers === false) {
            throw new RuntimeException("Invalid CSV format");
        }

        // Define numeric fields for type coercion
        $numericFields = ['age', 'total_orders', 'avg_order_value', 'has_subscription', 'is_active'];

        $data = [];
        while (($row = fgetcsv($file, 0, ',', '"', '\\')) !== false) {
            $combined = array_combine($headers, $row);
            // Coerce numeric string values to actual numbers
            foreach ($numericFields as $field) {
                if (isset($combined[$field]) && $combined[$field] !== '') {
                    $combined[$field] = is_numeric($combined[$field]) ? (float)$combined[$field] : $combined[$field];
                }
            }
            $data[] = $combined;
        }
        fclose($file);

        if (empty($data)) {
            throw new RuntimeException("No data loaded from CSV");
        }

        return $data;
    }

    private function loadJson(string $path): array
    {
        return json_decode(file_get_contents($path), true);
    }

    private function loadDatabase(string $query): array
    {
        // Simplified - would need connection details in real implementation
        return [];
    }

    private function imputeMean(string $column): array
    {
        $values = array_filter(
            array_column($this->data, $column),
            fn($v) => $v !== null && $v !== ''
        );

        if (empty($values)) {
            return $this->data;
        }

        $mean = array_sum($values) / count($values);

        return array_map(fn($row) => [
            ...$row,
            $column => $row[$column] ?? $mean
        ], $this->data);
    }

    private function imputeMode(string $column): array
    {
        $values = array_filter(
            array_column($this->data, $column),
            fn($v) => $v !== null && $v !== ''
        );

        if (empty($values)) {
            return $this->data;
        }

        $frequency = array_count_values($values);
        arsort($frequency);
        $mode = array_key_first($frequency);

        return array_map(fn($row) => [
            ...$row,
            $column => $row[$column] ?? $mode
        ], $this->data);
    }

    private function minMaxNormalize(string $column): array
    {
        $values = array_column($this->data, $column);
        $min = min($values);
        $max = max($values);

        // Save parameters for later reuse
        $this->parameters["minmax_$column"] = [
            'min' => $min,
            'max' => $max,
            'column' => $column
        ];

        if ($max === $min) {
            return $this->data;
        }

        return array_map(fn($row) => [
            ...$row,
            $column . '_normalized' => ($row[$column] - $min) / ($max - $min)
        ], $this->data);
    }

    private function zScoreNormalize(string $column): array
    {
        $values = array_column($this->data, $column);
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($v) => ($v - $mean) ** 2, $values)) / count($values);
        $stdDev = sqrt($variance);

        // Save parameters for later reuse
        $this->parameters["zscore_$column"] = [
            'mean' => $mean,
            'std' => $stdDev,
            'column' => $column
        ];

        if ($stdDev === 0) {
            return $this->data;
        }

        return array_map(fn($row) => [
            ...$row,
            $column . '_standardized' => ($row[$column] - $mean) / $stdDev
        ], $this->data);
    }

    private function labelEncode(string $column): array
    {
        $unique = array_unique(array_column($this->data, $column));
        $mapping = array_flip(array_values($unique));

        // Save mapping for later reuse
        $this->parameters["label_$column"] = [
            'mapping' => $mapping,
            'column' => $column
        ];

        return array_map(fn($row) => [
            ...$row,
            $column . '_encoded' => $mapping[$row[$column]]
        ], $this->data);
    }

    private function oneHotEncode(string $column): array
    {
        $unique = array_unique(array_column($this->data, $column));

        return array_map(function ($row) use ($column, $unique) {
            $encoded = $row;
            foreach ($unique as $value) {
                $encoded[$column . '_' . $value] = ($row[$column] === $value) ? 1 : 0;
            }
            return $encoded;
        }, $this->data);
    }

    private function frequencyEncode(string $column): array
    {
        $frequency = array_count_values(array_column($this->data, $column));

        return array_map(fn($row) => [
            ...$row,
            $column . '_frequency' => $frequency[$row[$column]]
        ], $this->data);
    }
}

// Example: Complete preprocessing workflow
$pipeline = new PreprocessingPipeline();

$processed = $pipeline
    ->load(__DIR__ . '/data/customers.csv', 'csv')
    ->handleMissing('age', 'mean')
    ->normalize('age', 'minmax')
    ->normalize('total_orders', 'minmax')
    ->encode('gender', 'label')
    ->encode('country', 'frequency')
    ->get();

echo $pipeline->summary();
echo "\nFirst 2 processed records:\n";
print_r(array_slice($processed, 0, 2));

// Save for use in future ML chapters
$pipeline->save(__DIR__ . '/processed/final_preprocessed.json');
echo "\n✓ Final preprocessed data saved\n";
