<?php

declare(strict_types=1);

/**
 * Exercise 1 Solution: Multi-Model Sentiment Analyzer
 * 
 * This solution demonstrates:
 * - Training multiple classifiers (Naive Bayes, Logistic Regression, Linear SVM)
 * - Comparing performance metrics
 * - Automatically selecting the best model
 * - Saving the best model for production use
 */

class MultiModelSentimentAnalyzer
{
    public function __construct(
        private string $pythonPath = 'python3',
        private string $scriptDir = __DIR__
    ) {}

    /**
     * Train multiple models and select the best one.
     */
    public function trainAndCompare(string $dataPath): array
    {
        $start = microtime(true);

        echo "=== Multi-Model Sentiment Analyzer ===\n\n";
        echo "Training three classifiers:\n";
        echo "  1. Naive Bayes (baseline)\n";
        echo "  2. Logistic Regression\n";
        echo "  3. Linear SVM\n\n";
        echo "Data: {$dataPath}\n";
        echo "This may take 30-60 seconds...\n\n";

        $escapedPath = escapeshellarg($dataPath);
        $command = "{$this->pythonPath} {$this->scriptDir}/exercise1-train.py {$escapedPath}";

        // Use proc_open to capture both stdout and stderr
        $descriptors = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start training process');
        }

        fclose($pipes[0]);

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        // Display training output
        echo "--- Training Output ---\n";
        echo $stderr;
        echo "----------------------\n\n";

        if ($exitCode !== 0) {
            throw new RuntimeException("Training failed with exit code {$exitCode}");
        }

        // Parse result from last line of stdout
        $lines = array_filter(explode("\n", trim($stdout)));
        $lastLine = end($lines);
        $result = json_decode($lastLine, true);

        if (isset($result['error'])) {
            throw new RuntimeException("Training error: {$result['error']}");
        }

        $duration = microtime(true) - $start;
        $result['training_time'] = round($duration, 2);

        return $result;
    }

    /**
     * Display comparison results in a nice format.
     */
    public function displayResults(array $results): void
    {
        echo "=== Model Comparison Summary ===\n\n";

        echo "🏆 Best Model: {$results['best_model']}\n";
        echo "   Accuracy: " . round($results['accuracy'] * 100, 2) . "%\n";
        echo "   F1-Score: " . round($results['f1_score'] * 100, 2) . "%\n";
        echo "   CV Accuracy: " . round($results['cv_mean'] * 100, 2) . "%\n\n";

        echo "All Models:\n";
        echo str_repeat('-', 60) . "\n";
        printf("%-25s %-15s %-15s\n", "Model", "Accuracy", "F1-Score");
        echo str_repeat('-', 60) . "\n";

        foreach ($results['all_results'] as $model) {
            $marker = ($model['name'] === $results['best_model']) ? '🏆 ' : '   ';
            printf(
                "%s%-22s %-15s %-15s\n",
                $marker,
                $model['name'],
                round($model['accuracy'] * 100, 2) . '%',
                round($model['f1'] * 100, 2) . '%'
            );
        }

        echo str_repeat('-', 60) . "\n\n";
        echo "Training completed in {$results['training_time']}s\n";
        echo "Best model saved to: {$results['model_path']}\n\n";

        echo "✅ Exercise 1 Complete!\n\n";

        echo "What we learned:\n";
        echo "  ✓ How to compare multiple ML algorithms\n";
        echo "  ✓ Different classifiers have different strengths\n";
        echo "  ✓ F1-score balances precision and recall\n";
        echo "  ✓ Cross-validation provides robust accuracy estimates\n";
        echo "  ✓ Automated model selection saves time\n";
    }
}

// Run the solution
try {
    $analyzer = new MultiModelSentimentAnalyzer();

    // Train and compare models
    $results = $analyzer->trainAndCompare('../03-sentiment-analysis/data/reviews.csv');

    // Display results
    $analyzer->displayResults($results);
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}


