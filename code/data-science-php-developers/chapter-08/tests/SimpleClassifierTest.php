<?php

declare(strict_types=1);

namespace Tests\Chapter08;

use PHPUnit\Framework\TestCase;
use DataScience\ML\SimpleClassifier;

class SimpleClassifierTest extends TestCase
{
    private SimpleClassifier $classifier;

    protected function setUp(): void
    {
        $this->classifier = new SimpleClassifier();
    }

    public function test_trains_and_predicts_correctly(): void
    {
        $trainingData = [
            [1.0, 1.0],
            [2.0, 2.0],
            [9.0, 9.0],
            [10.0, 10.0],
        ];

        $trainingLabels = ['A', 'A', 'B', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        // Test point close to class A
        $prediction = $this->classifier->predict([1.5, 1.5], k: 3);
        $this->assertEquals('A', $prediction);

        // Test point close to class B
        $prediction = $this->classifier->predict([9.5, 9.5], k: 3);
        $this->assertEquals('B', $prediction);
    }

    public function test_throws_exception_when_not_trained(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Model not trained');

        $this->classifier->predict([1.0, 2.0]);
    }

    public function test_throws_exception_on_dimension_mismatch(): void
    {
        $trainingData = [[1.0, 2.0], [3.0, 4.0]];
        $trainingLabels = ['A', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $this->expectException(\InvalidArgumentException::class);

        // Try to predict with wrong dimensions
        $this->classifier->predict([1.0, 2.0, 3.0]);
    }

    public function test_throws_exception_on_invalid_k(): void
    {
        $trainingData = [[1.0, 2.0], [3.0, 4.0]];
        $trainingLabels = ['A', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $this->expectException(\InvalidArgumentException::class);

        // K too large
        $this->classifier->predict([1.0, 2.0], k: 10);
    }

    public function test_evaluate_returns_accurate_metrics(): void
    {
        $trainingData = [
            [1.0, 1.0], [1.1, 1.1], [1.2, 1.2],
            [5.0, 5.0], [5.1, 5.1], [5.2, 5.2],
        ];
        $trainingLabels = ['A', 'A', 'A', 'B', 'B', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $testData = [[1.05, 1.05], [5.05, 5.05]];
        $testLabels = ['A', 'B'];

        $evaluation = $this->classifier->evaluate($testData, $testLabels);

        $this->assertEquals(1.0, $evaluation['accuracy']);
        $this->assertEquals(2, $evaluation['correct']);
        $this->assertEquals(2, $evaluation['total']);
    }

    public function test_evaluate_detailed_returns_precision_recall_f1(): void
    {
        $trainingData = [
            [1.0, 1.0], [1.1, 1.1], [1.2, 1.2],
            [5.0, 5.0], [5.1, 5.1], [5.2, 5.2],
        ];
        $trainingLabels = ['A', 'A', 'A', 'B', 'B', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $testData = [[1.05, 1.05], [5.05, 5.05], [1.08, 1.08]];
        $testLabels = ['A', 'B', 'A'];

        $evaluation = $this->classifier->evaluateDetailed($testData, $testLabels);

        $this->assertArrayHasKey('accuracy', $evaluation);
        $this->assertArrayHasKey('per_class', $evaluation);
        $this->assertArrayHasKey('macro_avg', $evaluation);

        $this->assertEquals(1.0, $evaluation['accuracy']);
        $this->assertArrayHasKey('A', $evaluation['per_class']);
        $this->assertArrayHasKey('B', $evaluation['per_class']);

        $this->assertEquals(1.0, $evaluation['per_class']['A']['precision']);
        $this->assertEquals(1.0, $evaluation['per_class']['A']['recall']);
        $this->assertEquals(1.0, $evaluation['per_class']['A']['f1_score']);
    }

    public function test_confusion_matrix_generation(): void
    {
        $trainingData = [
            [1.0, 1.0], [1.1, 1.1],
            [5.0, 5.0], [5.1, 5.1],
        ];
        $trainingLabels = ['A', 'A', 'B', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $testData = [[1.05, 1.05], [5.05, 5.05]];
        $testLabels = ['A', 'B'];

        $result = $this->classifier->confusionMatrix($testData, $testLabels);

        $this->assertArrayHasKey('matrix', $result);
        $this->assertArrayHasKey('classes', $result);
        $this->assertContains('A', $result['classes']);
        $this->assertContains('B', $result['classes']);
    }

    public function test_predict_with_probability(): void
    {
        $trainingData = [
            [1.0, 1.0], [1.1, 1.1], [1.2, 1.2],
            [5.0, 5.0], [5.1, 5.1], [5.2, 5.2],
        ];
        $trainingLabels = ['A', 'A', 'A', 'B', 'B', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $result = $this->classifier->predictWithProbability([1.05, 1.05], k: 3);

        $this->assertArrayHasKey('prediction', $result);
        $this->assertArrayHasKey('probabilities', $result);
        $this->assertEquals('A', $result['prediction']);
        $this->assertIsFloat($result['probabilities']['A']);
        $this->assertGreaterThan(0.5, $result['probabilities']['A']);
    }

    public function test_save_and_load_model(): void
    {
        $trainingData = [[1.0, 2.0], [3.0, 4.0]];
        $trainingLabels = ['A', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $filepath = sys_get_temp_dir() . '/test_classifier.json';

        // Save model
        $this->classifier->save($filepath);
        $this->assertFileExists($filepath);

        // Load model
        $loadedClassifier = SimpleClassifier::load($filepath);
        $this->assertInstanceOf(SimpleClassifier::class, $loadedClassifier);

        // Test prediction works on loaded model
        $prediction = $loadedClassifier->predict([1.5, 2.5], k: 1);
        $this->assertEquals('A', $prediction);

        // Clean up
        unlink($filepath);
    }

    public function test_throws_exception_on_non_numeric_features(): void
    {
        $trainingData = [[1.0, 2.0], [3.0, 4.0]];
        $trainingLabels = ['A', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All point values must be numeric');

        $this->classifier->predict([1.0, 'invalid']);
    }

    public function test_k_equals_one(): void
    {
        $trainingData = [
            [1.0, 1.0],
            [9.0, 9.0],
        ];
        $trainingLabels = ['A', 'B'];

        $this->classifier->train($trainingData, $trainingLabels);

        $prediction = $this->classifier->predict([1.5, 1.5], k: 1);
        $this->assertEquals('A', $prediction);
    }
}
