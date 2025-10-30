<?php

declare(strict_types=1);

namespace App\Services\ML;

use Phpml\Classification\NaiveBayes;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WordTokenizer;

class SentimentAnalysisService extends ModelService
{
    private TokenCountVectorizer $vectorizer;
    private array $vocabulary = [];

    public function __construct()
    {
        parent::__construct('sentiment');
        $this->vectorizer = new TokenCountVectorizer(new WordTokenizer());
    }

    protected function loadModel(): mixed
    {
        $modelPath = config('ml.models.sentiment.path');

        // For this tutorial, we'll use a simple pre-configured model
        // In production, load a trained model from disk using:
        // $model = unserialize(file_get_contents($modelPath));

        $model = new NaiveBayes();

        // Training data: simple positive/negative examples
        $samples = [
            'excellent product highly recommend',
            'amazing quality very satisfied',
            'great value love it',
            'perfect exactly what needed',
            'fantastic service quick delivery',
            'wonderful experience best purchase',
            'outstanding quality exceeded expectations',
            'brilliant design works perfectly',
            'terrible waste money',
            'poor quality very disappointed',
            'awful product hate it',
            'worst purchase regret buying',
            'horrible service never again',
            'disappointing terrible experience',
            'useless garbage complete waste',
            'pathetic quality avoid this',
        ];

        $labels = [
            'positive',
            'positive',
            'positive',
            'positive',
            'positive',
            'positive',
            'positive',
            'positive',
            'negative',
            'negative',
            'negative',
            'negative',
            'negative',
            'negative',
            'negative',
            'negative',
        ];

        // Transform text to features
        $this->vectorizer->fit($samples);
        $this->vectorizer->transform($samples);

        // Train the model
        $model->train($samples, $labels);

        return $model;
    }

    public function predict(mixed $input): mixed
    {
        $this->ensureModelLoaded();

        // Use caching to avoid recomputing same inputs
        return $this->cachedPredict($input, function () use ($input) {
            return $this->executePrediction(function () use ($input) {
                $text = is_array($input) ? $input['text'] : $input;

                // Preprocess text
                $processed = $this->preprocessText($text);

                // Transform to features
                $testSamples = [$processed];
                $this->vectorizer->transform($testSamples);

                // Predict
                $prediction = $this->model->predict($testSamples);
                $sentiment = $prediction[0];

                // Calculate confidence score (simplified)
                $score = $this->calculateConfidence($processed, $sentiment);

                return [
                    'text' => $text,
                    'sentiment' => $sentiment,
                    'confidence' => $score,
                    'emoji' => $this->getEmoji($sentiment),
                    'timestamp' => now()->toIso8601String(),
                ];
            });
        });
    }

    private function preprocessText(string $text): string
    {
        // Convert to lowercase
        $text = strtolower($text);

        // Remove special characters but keep spaces
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);

        // Remove extra whitespace
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function calculateConfidence(string $text, string $sentiment): float
    {
        // Simplified confidence calculation based on keyword presence
        $positiveWords = [
            'excellent',
            'amazing',
            'great',
            'perfect',
            'fantastic',
            'wonderful',
            'outstanding',
            'brilliant',
            'love',
            'recommend',
            'satisfied',
            'quality',
            'best',
            'exceeded',
            'works',
        ];

        $negativeWords = [
            'terrible',
            'poor',
            'awful',
            'worst',
            'horrible',
            'disappointing',
            'useless',
            'pathetic',
            'hate',
            'regret',
            'disappointed',
            'waste',
            'never',
            'avoid',
            'garbage',
        ];

        $words = explode(' ', $text);
        $posCount = 0;
        $negCount = 0;

        foreach ($words as $word) {
            if (in_array($word, $positiveWords)) {
                $posCount++;
            }
            if (in_array($word, $negativeWords)) {
                $negCount++;
            }
        }

        $total = $posCount + $negCount;
        if ($total === 0) {
            return 0.5;
        }

        $confidence = $sentiment === 'positive'
            ? $posCount / $total
            : $negCount / $total;

        return round($confidence, 2);
    }

    private function getEmoji(string $sentiment): string
    {
        return match ($sentiment) {
            'positive' => '😊',
            'negative' => '😞',
            default => '😐',
        };
    }
}

