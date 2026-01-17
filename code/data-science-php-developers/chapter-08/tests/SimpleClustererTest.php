<?php

declare(strict_types=1);

namespace Tests\Chapter08;

use PHPUnit\Framework\TestCase;
use DataScience\ML\SimpleClusterer;

class SimpleClustererTest extends TestCase
{
    private SimpleClusterer $clusterer;

    protected function setUp(): void
    {
        $this->clusterer = new SimpleClusterer();
    }

    public function test_clusters_well_separated_data(): void
    {
        // Three well-separated clusters
        $data = [
            [0, 0], [1, 1], [0, 1],        // Cluster 1
            [10, 10], [11, 11], [10, 11],  // Cluster 2
            [20, 20], [21, 21], [20, 21],  // Cluster 3
        ];

        $this->clusterer->fit($data, k: 3);

        $labels = $this->clusterer->getLabels();

        // Points 0-2 should be in same cluster
        $this->assertEquals($labels[0], $labels[1]);
        $this->assertEquals($labels[0], $labels[2]);

        // Points 3-5 should be in same cluster (different from 0-2)
        $this->assertEquals($labels[3], $labels[4]);
        $this->assertEquals($labels[3], $labels[5]);
        $this->assertNotEquals($labels[0], $labels[3]);

        // Points 6-8 should be in same cluster (different from others)
        $this->assertEquals($labels[6], $labels[7]);
        $this->assertEquals($labels[6], $labels[8]);
        $this->assertNotEquals($labels[0], $labels[6]);
        $this->assertNotEquals($labels[3], $labels[6]);
    }

    public function test_predict_assigns_to_nearest_cluster(): void
    {
        $data = [
            [0, 0], [1, 1],
            [10, 10], [11, 11],
        ];

        $this->clusterer->fit($data, k: 2);

        // New point close to first cluster
        $predictions = $this->clusterer->predict([[0.5, 0.5]]);

        // Should be assigned to cluster 0 or 1 (whichever contains [0,0])
        $this->assertContains($predictions[0], [0, 1]);
    }

    public function test_inertia_decreases_with_more_clusters(): void
    {
        $data = array_map(fn($i) => [mt_rand(0, 100), mt_rand(0, 100)], range(0, 49));

        $inertias = [];
        for ($k = 2; $k <= 5; $k++) {
            $clusterer = new SimpleClusterer();
            $clusterer->fit($data, k: $k);
            $inertias[$k] = $clusterer->inertia($data);
        }

        // Inertia should decrease as K increases
        $this->assertGreaterThan($inertias[5], $inertias[2]);
    }

    public function test_get_centroids(): void
    {
        $data = [
            [0, 0], [1, 1],
            [10, 10], [11, 11],
        ];

        $this->clusterer->fit($data, k: 2);

        $centroids = $this->clusterer->getCentroids();

        $this->assertCount(2, $centroids);
        $this->assertCount(2, $centroids[0]);
    }

    public function test_throws_exception_on_invalid_k(): void
    {
        $data = [[1, 2], [3, 4]];

        $this->expectException(\InvalidArgumentException::class);

        $this->clusterer->fit($data, k: 10);
    }

    public function test_throws_exception_on_empty_data(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Data cannot be empty');

        $this->clusterer->fit([], k: 3);
    }

    public function test_throws_exception_when_not_fitted(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Model not fitted');

        $this->clusterer->predict([[1, 2]]);
    }

    public function test_silhouette_score(): void
    {
        // Well-separated clusters should have high silhouette score
        $data = [
            [0, 0], [1, 1], [0, 1],
            [10, 10], [11, 11], [10, 11],
        ];

        $this->clusterer->fit($data, k: 2);

        $score = $this->clusterer->silhouetteScore($data);

        // Should be positive for well-separated clusters
        $this->assertGreaterThan(0, $score);
    }

    public function test_save_and_load_model(): void
    {
        $data = [[1, 2], [3, 4], [10, 11], [12, 13]];

        $this->clusterer->fit($data, k: 2);

        $filepath = sys_get_temp_dir() . '/test_clusterer.json';

        // Save model
        $this->clusterer->save($filepath);
        $this->assertFileExists($filepath);

        // Load model
        $loadedClusterer = SimpleClusterer::load($filepath);
        $this->assertInstanceOf(SimpleClusterer::class, $loadedClusterer);

        // Test prediction works on loaded model
        $predictions = $loadedClusterer->predict([[2, 3]]);
        $this->assertCount(1, $predictions);

        // Clean up
        unlink($filepath);
    }

    public function test_handles_k_equals_one(): void
    {
        $data = [[1, 2], [3, 4], [5, 6]];

        $this->clusterer->fit($data, k: 1);

        $labels = $this->clusterer->getLabels();

        // All points should be in cluster 0
        $this->assertEquals([0, 0, 0], $labels);
    }

    public function test_handles_mismatched_dimensions(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All data points must have same dimensions');

        $data = [[1, 2], [3, 4, 5]]; // Different dimensions

        $this->clusterer->fit($data, k: 2);
    }
}
