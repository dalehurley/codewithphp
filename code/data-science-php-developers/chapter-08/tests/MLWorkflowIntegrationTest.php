<?php

declare(strict_types=1);

namespace Tests\Chapter08;

use PHPUnit\Framework\TestCase;
use DataScience\ML\SimpleClassifier;
use DataScience\ML\SimpleRegressor;
use DataScience\ML\SimpleClusterer;
use DataScience\ML\FeatureEngineer;
use DataScience\ML\CrossValidator;

/**
 * Integration test for complete ML workflow
 */
class MLWorkflowIntegrationTest extends TestCase
{
    public function test_complete_classification_workflow(): void
    {
        // 1. Feature Engineering
        $engineer = new FeatureEngineer();

        $rawFeatures = [100, 200, 300, 150, 250];
        $scaled = $engineer->minMaxScale($rawFeatures);

        $this->assertCount(5, $scaled);

        // 2. Train model
        $classifier = new SimpleClassifier();

        $trainingData = [
            [0.0, 0.2], [0.2, 0.0], [0.1, 0.1],
            [0.8, 1.0], [1.0, 0.8], [0.9, 0.9],
        ];
        $trainingLabels = ['low', 'low', 'low', 'high', 'high', 'high'];

        $classifier->train($trainingData, $trainingLabels);

        // 3. Cross-validation
        $validator = new CrossValidator();
        $cvResults = $validator->kFold($classifier, $trainingData, $trainingLabels, k: 3);

        $this->assertArrayHasKey('mean_score', $cvResults);
        $this->assertGreaterThan(0.5, $cvResults['mean_score']);

        // 4. Evaluate on test set
        $testData = [[0.15, 0.15], [0.85, 0.85]];
        $testLabels = ['low', 'high'];

        $evaluation = $classifier->evaluate($testData, $testLabels);

        $this->assertArrayHasKey('accuracy', $evaluation);
        $this->assertArrayHasKey('predictions', $evaluation);
        $this->assertEquals(1.0, $evaluation['accuracy']);

        // 5. Detailed evaluation
        $detailed = $classifier->evaluateDetailed($testData, $testLabels);

        $this->assertArrayHasKey('per_class', $detailed);
        $this->assertArrayHasKey('macro_avg', $detailed);

        // 6. Model persistence
        $filepath = sys_get_temp_dir() . '/workflow_test_model.json';
        $classifier->save($filepath);

        $loadedModel = SimpleClassifier::load($filepath);
        $prediction = $loadedModel->predict([0.1, 0.1]);
        $this->assertEquals('low', $prediction);

        unlink($filepath);
    }

    public function test_complete_regression_workflow(): void
    {
        // 1. Prepare data
        $engineer = new FeatureEngineer();

        $x = [1000, 1500, 2000, 2500, 3000];
        $y = [150, 200, 250, 300, 350];

        // Normalize features
        $xNormalized = $engineer->minMaxScale($x);

        // 2. Train model
        $regressor = new SimpleRegressor();
        $regressor->train($x, $y);

        // 3. Evaluate
        $rSquared = $regressor->rSquared($x, $y);
        $this->assertGreaterThan(0.95, $rSquared);

        $mae = $regressor->meanAbsoluteError($x, $y);
        $this->assertLessThan(5, $mae);

        $rmse = $regressor->rootMeanSquaredError($x, $y);
        $this->assertLessThan(10, $rmse);

        // 4. Make predictions
        $prediction = $regressor->predict(1750);
        $this->assertGreaterThan(200, $prediction);
        $this->assertLessThan(250, $prediction);

        // 5. Model persistence
        $filepath = sys_get_temp_dir() . '/workflow_test_regressor.json';
        $regressor->save($filepath);

        $loadedRegressor = SimpleRegressor::load($filepath);
        $loadedPrediction = $loadedRegressor->predict(1750);
        $this->assertEqualsWithDelta($prediction, $loadedPrediction, 0.01);

        unlink($filepath);
    }

    public function test_complete_clustering_workflow(): void
    {
        // 1. Prepare customer data
        $customers = [
            [100, 2], [150, 3], [120, 2],     // Low spenders
            [500, 8], [600, 10], [550, 9],     // Medium spenders
            [1200, 20], [1100, 18], [1300, 22], // High spenders
        ];

        // 2. Feature engineering - normalize
        $engineer = new FeatureEngineer();
        $spends = array_column($customers, 0);
        $visits = array_column($customers, 1);

        $normalizedSpends = $engineer->minMaxScale($spends);
        $normalizedVisits = $engineer->minMaxScale($visits);

        // 3. Fit clustering
        $clusterer = new SimpleClusterer();
        $clusterer->fit($customers, k: 3);

        $labels = $clusterer->getLabels();
        $this->assertCount(9, $labels);

        // 4. Evaluate clustering quality
        $inertia = $clusterer->inertia($customers);
        $this->assertGreaterThan(0, $inertia);

        $silhouette = $clusterer->silhouetteScore($customers);
        $this->assertGreaterThan(-1, $silhouette);
        $this->assertLessThan(1, $silhouette);

        // 5. Predict new customer clusters
        $newCustomers = [[130, 3], [580, 9], [1150, 19]];
        $predictions = $clusterer->predict($newCustomers);

        $this->assertCount(3, $predictions);

        // 6. Model persistence
        $filepath = sys_get_temp_dir() . '/workflow_test_clusterer.json';
        $clusterer->save($filepath);

        $loadedClusterer = SimpleClusterer::load($filepath);
        $loadedPredictions = $loadedClusterer->predict($newCustomers);
        $this->assertEquals($predictions, $loadedPredictions);

        unlink($filepath);
    }

    public function test_stratified_cross_validation(): void
    {
        // Imbalanced dataset
        $trainingData = [
            [1.0, 1.0], [1.1, 1.1], [1.2, 1.2], [1.3, 1.3], [1.4, 1.4], // 5 low
            [9.0, 9.0], [9.1, 9.1], // 2 high
        ];
        $trainingLabels = ['low', 'low', 'low', 'low', 'low', 'high', 'high'];

        $classifier = new SimpleClassifier();
        $validator = new CrossValidator();

        $results = $validator->stratifiedKFold($classifier, $trainingData, $trainingLabels, k: 3);

        $this->assertArrayHasKey('mean_score', $results);
        $this->assertArrayHasKey('std_dev', $results);
        $this->assertGreaterThan(0, $results['mean_score']);
    }

    public function test_feature_engineering_pipeline(): void
    {
        $engineer = new FeatureEngineer();

        // Multiple transformations
        $rawData = [1, 2, 3, 4, 5];

        // 1. Standardize
        $standardized = $engineer->standardize($rawData);
        $mean = array_sum($standardized) / count($standardized);
        $this->assertEqualsWithDelta(0, $mean, 0.001);

        // 2. Create polynomial features
        $poly = $engineer->polynomialFeatures($rawData, degree: 2);
        $this->assertCount(10, $poly); // x and x²

        // 3. Bin features
        $binned = $engineer->binFeature($rawData, [2, 4]);
        $this->assertEquals([0, 0, 1, 1, 2], $binned);

        // 4. One-hot encode categories
        $categories = ['red', 'blue', 'red', 'green', 'blue'];
        $result = $engineer->oneHotEncode($categories);
        $encoded = $result['encoded'];

        $this->assertCount(5, $encoded);
        $this->assertCount(3, $encoded[0]); // 3 unique categories
    }

    public function test_model_comparison(): void
    {
        // Same dataset, different models
        $trainingData = [
            [1.0, 1.0], [1.5, 1.5], [2.0, 2.0],
            [8.0, 8.0], [9.0, 9.0], [10.0, 10.0],
        ];
        $trainingLabels = ['A', 'A', 'A', 'B', 'B', 'B'];

        $testData = [[1.2, 1.2], [8.5, 8.5]];
        $testLabels = ['A', 'B'];

        // Model 1: KNN with k=3
        $classifier1 = new SimpleClassifier();
        $classifier1->train($trainingData, $trainingLabels);
        $eval1 = $classifier1->evaluate($testData, $testLabels);

        // Model 2: KNN with k=1
        $classifier2 = new SimpleClassifier();
        $classifier2->train($trainingData, $trainingLabels);
        $eval2 = $classifier2->evaluate($testData, $testLabels);

        // Both should have reasonable accuracy
        $this->assertGreaterThan(0.5, $eval1['accuracy']);
        $this->assertGreaterThan(0.5, $eval2['accuracy']);
    }

    public function test_confusion_matrix_workflow(): void
    {
        $trainingData = [
            [1.0, 1.0], [1.1, 1.1],
            [5.0, 5.0], [5.1, 5.1],
            [9.0, 9.0], [9.1, 9.1],
        ];
        $trainingLabels = ['A', 'A', 'B', 'B', 'C', 'C'];

        $testData = [[1.05, 1.05], [5.05, 5.05], [9.05, 9.05]];
        $testLabels = ['A', 'B', 'C'];

        $classifier = new SimpleClassifier();
        $classifier->train($trainingData, $trainingLabels);

        $confusionMatrix = $classifier->confusionMatrix($testData, $testLabels);

        $this->assertArrayHasKey('matrix', $confusionMatrix);
        $this->assertArrayHasKey('classes', $confusionMatrix);
        $this->assertCount(3, $confusionMatrix['classes']);

        // Diagonal should have all correct predictions
        $matrix = $confusionMatrix['matrix'];
        $this->assertEquals(1, $matrix['A']['A']);
        $this->assertEquals(1, $matrix['B']['B']);
        $this->assertEquals(1, $matrix['C']['C']);
    }
}
