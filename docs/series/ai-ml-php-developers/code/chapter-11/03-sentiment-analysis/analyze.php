<?php

declare(strict_types=1);

/**
 * Sentiment Analysis System
 * 
 * This demonstrates a complete ML integration:
 * 1. Training a model from data
 * 2. Using the trained model for predictions
 * 3. Proper error handling and validation
 * 4. Performance monitoring
 */

class SentimentAnalyzer
{
    public function __construct(
        private string $pythonPath = 'python3',
        private string $scriptDir = __DIR__,
        private string $modelDir = 'models'
    ) {}

    /**
     * Train a new sentiment model from CSV data.
     */
    public function train(string $dataPath): array
    {
        $start = microtime(true);

        echo "Training sentiment model...\n";
        echo "Data: {$dataPath}\n\n";

        $escapedPath = escapeshellarg($dataPath);
        $command = "{$this->pythonPath} {$this->scriptDir}/train_model.py {$escapedPath}";

        // Capture both stdout and stderr
        $descriptors = [
            0 => ['pipe', 'r'],  // stdin
            1 => ['pipe', 'w'],  // stdout
            2 => ['pipe', 'w'],  // stderr
        ];

        $process = proc_open($command, $descriptors, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException('Failed to start Python training process');
        }

        fclose($pipes[0]);  // Close stdin

        $stdout = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);

        echo "--- Training Output ---\n";
        echo $stderr;  // Training prints to stderr
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
     * Predict sentiment for given text.
     */
    public function predict(string $text): array
    {
        if (empty(trim($text))) {
            throw new InvalidArgumentException('Text cannot be empty');
        }

        $start = microtime(true);

        $data = json_encode(['text' => $text]);
        $escaped = escapeshellarg($data);

        $command = "{$this->pythonPath} {$this->scriptDir}/predict.py {$escaped}";
        $output = shell_exec($command);

        if ($output === null) {
            throw new RuntimeException('Failed to execute prediction script');
        }

        $result = json_decode($output, true);

        if (isset($result['error'])) {
            throw new RuntimeException("Prediction error: {$result['error']}");
        }

        $duration = microtime(true) - $start;
        $result['prediction_time'] = round($duration * 1000, 2);  // milliseconds

        return $result;
    }

    /**
     * Batch predict sentiments for multiple texts.
     */
    public function predictBatch(array $texts): array
    {
        return array_map(
            fn(string $text) => $this->predict($text),
            $texts
        );
    }
}

// Example usage
try {
    $analyzer = new SentimentAnalyzer();

    // Step 1: Train the model
    echo "=== Step 1: Train Model ===\n";
    $trainingResult = $analyzer->train('data/reviews.csv');
    echo "✅ Training completed in {$trainingResult['training_time']}s\n";
    echo "   Test Accuracy: " . round($trainingResult['accuracy'] * 100, 1) . "%\n";
    echo "   CV Accuracy: " . round($trainingResult['cv_mean'] * 100, 1) . "% ";
    echo "(±" . round($trainingResult['cv_std'] * 100, 1) . "%)\n\n";

    // Step 2: Make predictions
    echo "=== Step 2: Predict Sentiments ===\n\n";

    $testReviews = [
        "This is absolutely wonderful! I love it so much!",
        "Terrible product. Complete waste of money.",
        "It's okay. Nothing special but it works.",
        "Best purchase I've made this year! Highly recommended!",
        "Disappointed with the quality. Not worth the price."
    ];

    foreach ($testReviews as $review) {
        $result = $analyzer->predict($review);

        $emoji = match ($result['sentiment']) {
            'positive' => '😊',
            'negative' => '😞',
            'neutral' => '😐',
            default => '❓'
        };

        echo "{$emoji} {$result['sentiment']} ";
        echo "(" . round($result['confidence'] * 100, 1) . "% confident, ";
        echo "{$result['prediction_time']}ms)\n";
        echo "   \"{$review}\"\n\n";
    }

    echo "✅ Sentiment analysis complete!\n";
} catch (Exception $e) {
    echo "❌ Error: {$e->getMessage()}\n";
    exit(1);
}


