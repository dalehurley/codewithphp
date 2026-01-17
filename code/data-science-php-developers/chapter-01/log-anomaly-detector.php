<?php

declare(strict_types=1);

/**
 * Use Case 4: Log Anomaly Detector
 * 
 * This class detects unusual patterns in application logs using
 * statistical analysis (mean and standard deviation).
 * 
 * Requirements:
 * - PHP 8.4+
 * - Database with 'logs' table
 * - Laravel/Eloquent or PDO
 */

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

final class LogAnomalyDetector
{
    /**
     * Detect spikes in log entries using statistical analysis.
     * 
     * Uses the "3-sigma rule" - data points more than 3 standard deviations
     * from the mean are considered anomalies.
     * 
     * @param string $logType Type of log to analyze (e.g., 'error', 'warning')
     * @param int $thresholdMultiplier How many std deviations constitute an anomaly
     * @param int $daysBack How many days of history to analyze
     * @return array Array of anomalous time periods
     */
    public function detectSpikes(
        string $logType,
        int $thresholdMultiplier = 3,
        int $daysBack = 7
    ): array {
        // Get error counts per hour for the specified time period
        $counts = DB::table('logs')
            ->select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour'),
                DB::raw('COUNT(*) as error_count')
            )
            ->where('log_type', $logType)
            ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
            ->groupBy('hour')
            ->get();

        if ($counts->isEmpty()) {
            return []; // No data to analyze
        }

        // Calculate mean and standard deviation
        $values = $counts->pluck('error_count')->toArray();
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / count($values);
        $stdDev = sqrt($variance);

        // Detect anomalies
        $threshold = $mean + ($thresholdMultiplier * $stdDev);

        $anomalies = $counts->filter(function ($count) use ($threshold) {
            return $count->error_count > $threshold;
        })->map(function ($anomaly) use ($mean, $stdDev) {
            return [
                'hour' => $anomaly->hour,
                'count' => $anomaly->error_count,
                'deviation' => round(($anomaly->error_count - $mean) / $stdDev, 2),
                'severity' => $this->calculateSeverity($anomaly->error_count, $mean, $stdDev),
            ];
        });

        return $anomalies->values()->toArray();
    }

    /**
     * Calculate severity level based on deviation from mean.
     * 
     * @param float $value The actual value
     * @param float $mean The mean value
     * @param float $stdDev The standard deviation
     * @return string Severity level: low, medium, high, critical
     */
    private function calculateSeverity(float $value, float $mean, float $stdDev): string
    {
        $deviation = ($value - $mean) / $stdDev;

        return match (true) {
            $deviation >= 5 => 'critical',
            $deviation >= 4 => 'high',
            $deviation >= 3 => 'medium',
            default => 'low',
        };
    }

    /**
     * Get summary statistics for log analysis.
     * 
     * @param string $logType Log type to analyze
     * @param int $daysBack Days of history
     * @return array Statistical summary
     */
    public function getStatistics(string $logType, int $daysBack = 7): array
    {
        $counts = DB::table('logs')
            ->where('log_type', $logType)
            ->where('created_at', '>=', Carbon::now()->subDays($daysBack))
            ->get();

        $hourlyGroups = $counts->groupBy(function ($log) {
            return Carbon::parse($log->created_at)->format('Y-m-d H:00:00');
        })->map->count();

        $values = $hourlyGroups->values()->toArray();

        if (empty($values)) {
            return ['error' => 'No data available'];
        }

        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / count($values);

        return [
            'mean' => round($mean, 2),
            'std_dev' => round(sqrt($variance), 2),
            'min' => min($values),
            'max' => max($values),
            'total_hours' => count($values),
            'total_logs' => array_sum($values),
        ];
    }
}

// Example usage:
// $detector = new LogAnomalyDetector();
// $anomalies = $detector->detectSpikes('error', thresholdMultiplier: 3, daysBack: 7);
// foreach ($anomalies as $anomaly) {
//     echo "Spike detected at {$anomaly['hour']}: ";
//     echo "{$anomaly['count']} errors ({$anomaly['severity']} severity)\n";
// }
