<?php

declare(strict_types=1);

namespace DataScience\ML;

/**
 * Simple K-Means Clustering
 *
 * Groups data points into K clusters using iterative centroid refinement.
 * This is an educational implementation for learning ML concepts.
 */
class SimpleClusterer
{
    private array $centroids = [];
    private array $labels = [];

    /**
     * Fit K-Means clustering
     *
     * @param array $data Training data (array of feature arrays)
     * @param int $k Number of clusters
     * @param int $maxIterations Maximum iterations before stopping
     * @throws \InvalidArgumentException
     */
    public function fit(array $data, int $k, int $maxIterations = 100): void
    {
        if (empty($data)) {
            throw new \InvalidArgumentException('Data cannot be empty');
        }

        if ($k < 1 || $k > count($data)) {
            throw new \InvalidArgumentException('K must be between 1 and number of data points');
        }

        // Validate all data points have same dimensions
        $dimensions = count($data[0]);
        foreach ($data as $point) {
            if (count($point) !== $dimensions) {
                throw new \InvalidArgumentException('All data points must have same dimensions');
            }
        }

        // Initialize centroids randomly
        $this->centroids = $this->initializeCentroids($data, $k);

        for ($iteration = 0; $iteration < $maxIterations; $iteration++) {
            // Assign points to nearest centroid
            $clusters = $this->assignClusters($data);

            // Calculate new centroids
            $newCentroids = $this->calculateCentroids($data, $clusters, $k);

            // Check for convergence
            if ($this->hasConverged($this->centroids, $newCentroids)) {
                break;
            }

            $this->centroids = $newCentroids;
        }

        // Store final labels
        $this->labels = $this->assignClusters($data);
    }

    /**
     * Predict cluster for new points
     *
     * @param array $points Array of data points
     * @return array Array of cluster assignments
     * @throws \RuntimeException
     */
    public function predict(array $points): array
    {
        if (empty($this->centroids)) {
            throw new \RuntimeException('Model not fitted');
        }

        $predictions = [];

        foreach ($points as $point) {
            $minDistance = PHP_FLOAT_MAX;
            $cluster = 0;

            foreach ($this->centroids as $i => $centroid) {
                $distance = $this->euclideanDistance($point, $centroid);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $cluster = $i;
                }
            }

            $predictions[] = $cluster;
        }

        return $predictions;
    }

    /**
     * Get cluster labels for training data
     *
     * @return array Cluster assignments
     */
    public function getLabels(): array
    {
        return $this->labels;
    }

    /**
     * Get cluster centroids
     *
     * @return array Centroid coordinates
     */
    public function getCentroids(): array
    {
        return $this->centroids;
    }

    /**
     * Calculate inertia (within-cluster sum of squares)
     * Lower inertia indicates tighter clusters
     *
     * @param array $data Training data
     * @return float Inertia value
     * @throws \RuntimeException
     */
    public function inertia(array $data): float
    {
        if (empty($this->centroids)) {
            throw new \RuntimeException('Model not fitted');
        }

        $inertia = 0;

        foreach ($data as $i => $point) {
            $cluster = $this->labels[$i];
            $centroid = $this->centroids[$cluster];
            $distance = $this->euclideanDistance($point, $centroid);
            $inertia += $distance ** 2;
        }

        return $inertia;
    }

    /**
     * Calculate silhouette score for clustering quality
     * Range: [-1, 1], higher is better
     *
     * @param array $data Training data
     * @return float Average silhouette score
     * @throws \RuntimeException
     */
    public function silhouetteScore(array $data): float
    {
        if (empty($this->centroids)) {
            throw new \RuntimeException('Model not fitted');
        }

        $n = count($data);
        $scores = [];

        for ($i = 0; $i < $n; $i++) {
            $point = $data[$i];
            $cluster = $this->labels[$i];

            // Calculate a(i): mean distance to points in same cluster
            $sameClusterDistances = [];
            for ($j = 0; $j < $n; $j++) {
                if ($i !== $j && $this->labels[$j] === $cluster) {
                    $sameClusterDistances[] = $this->euclideanDistance($point, $data[$j]);
                }
            }
            $a = !empty($sameClusterDistances) ? array_sum($sameClusterDistances) / count($sameClusterDistances) : 0;

            // Calculate b(i): min mean distance to points in other clusters
            $b = PHP_FLOAT_MAX;
            foreach (array_unique($this->labels) as $otherCluster) {
                if ($otherCluster === $cluster) {
                    continue;
                }

                $otherClusterDistances = [];
                for ($j = 0; $j < $n; $j++) {
                    if ($this->labels[$j] === $otherCluster) {
                        $otherClusterDistances[] = $this->euclideanDistance($point, $data[$j]);
                    }
                }

                if (!empty($otherClusterDistances)) {
                    $meanDist = array_sum($otherClusterDistances) / count($otherClusterDistances);
                    $b = min($b, $meanDist);
                }
            }

            // Calculate silhouette score for this point
            $s = ($b - $a) / max($a, $b);
            $scores[] = $s;
        }

        return array_sum($scores) / count($scores);
    }

    /**
     * Initialize centroids randomly
     *
     * @param array $data Training data
     * @param int $k Number of clusters
     * @return array Initial centroids
     */
    private function initializeCentroids(array $data, int $k): array
    {
        if ($k < 1 || $k > count($data)) {
            throw new \InvalidArgumentException('K must be between 1 and number of data points');
        }

        $indices = array_rand($data, $k);

        // array_rand returns int for k=1, array for k>1
        if (!is_array($indices)) {
            $indices = [$indices];
        }

        $centroids = [];
        foreach ($indices as $index) {
            $centroids[] = $data[$index];
        }

        return $centroids;
    }

    /**
     * Assign each point to nearest centroid
     *
     * @param array $data Data points
     * @return array Cluster assignments
     */
    private function assignClusters(array $data): array
    {
        $clusters = [];

        foreach ($data as $point) {
            $minDistance = PHP_FLOAT_MAX;
            $cluster = 0;

            foreach ($this->centroids as $i => $centroid) {
                $distance = $this->euclideanDistance($point, $centroid);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $cluster = $i;
                }
            }

            $clusters[] = $cluster;
        }

        return $clusters;
    }

    /**
     * Calculate new centroids as mean of assigned points
     *
     * @param array $data Training data
     * @param array $clusters Cluster assignments
     * @param int $k Number of clusters
     * @return array New centroids
     */
    private function calculateCentroids(array $data, array $clusters, int $k): array
    {
        $dimensions = count($data[0]);
        $centroids = array_fill(0, $k, array_fill(0, $dimensions, 0));
        $counts = array_fill(0, $k, 0);

        // Sum points in each cluster
        foreach ($data as $i => $point) {
            $cluster = $clusters[$i];
            $counts[$cluster]++;

            for ($d = 0; $d < $dimensions; $d++) {
                $centroids[$cluster][$d] += $point[$d];
            }
        }

        // Calculate means, handle empty clusters
        for ($c = 0; $c < $k; $c++) {
            if ($counts[$c] === 0) {
                // Reinitialize empty cluster to random data point
                $centroids[$c] = $data[array_rand($data)];
            } else {
                for ($d = 0; $d < $dimensions; $d++) {
                    $centroids[$c][$d] /= $counts[$c];
                }
            }
        }

        return $centroids;
    }

    /**
     * Check if centroids have converged
     *
     * @param array $old Old centroids
     * @param array $new New centroids
     * @param float $tolerance Convergence threshold
     * @return bool True if converged
     */
    private function hasConverged(array $old, array $new, float $tolerance = 0.0001): bool
    {
        foreach ($old as $i => $oldCentroid) {
            $distance = $this->euclideanDistance($oldCentroid, $new[$i]);

            if ($distance > $tolerance) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate Euclidean distance
     *
     * @param array $point1 First point
     * @param array $point2 Second point
     * @return float Distance
     */
    private function euclideanDistance(array $point1, array $point2): float
    {
        $sum = 0;

        for ($i = 0; $i < count($point1); $i++) {
            $sum += ($point1[$i] - $point2[$i]) ** 2;
        }

        return sqrt($sum);
    }

    /**
     * Save model to file
     *
     * @param string $filepath Path to save model
     * @throws \RuntimeException
     */
    public function save(string $filepath): void
    {
        if (empty($this->centroids)) {
            throw new \RuntimeException('Cannot save unfitted model');
        }

        $data = [
            'class' => self::class,
            'centroids' => $this->centroids,
            'labels' => $this->labels,
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
        $model->centroids = $data['centroids'];
        $model->labels = $data['labels'];

        return $model;
    }
}
