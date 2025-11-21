<?php

declare(strict_types=1);

namespace App\Billing;

class CostAttribution
{
    public function __construct(
        private readonly object $db
    ) {
        $this->initializeSchema();
    }

    /**
     * Record cost attribution
     */
    public function recordCost(array $attribution): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO claude_costs (
                user_id, feature, model, input_tokens, output_tokens,
                cost, request_id, created_at
            ) VALUES (
                :user_id, :feature, :model, :input_tokens, :output_tokens,
                :cost, :request_id, :created_at
            )
        ");

        $stmt->execute([
            'user_id' => $attribution['user_id'],
            'feature' => $attribution['feature'],
            'model' => $attribution['model'],
            'input_tokens' => $attribution['input_tokens'],
            'output_tokens' => $attribution['output_tokens'],
            'cost' => $attribution['cost'],
            'request_id' => $attribution['request_id'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Get cost breakdown by feature
     */
    public function getCostByFeature(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare("
            SELECT
                feature,
                COUNT(*) as request_count,
                SUM(input_tokens) as total_input_tokens,
                SUM(output_tokens) as total_output_tokens,
                SUM(cost) as total_cost
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY feature
            ORDER BY total_cost DESC
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get cost breakdown by user
     */
    public function getCostByUser(string $startDate, string $endDate, int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT
                user_id,
                COUNT(*) as request_count,
                SUM(cost) as total_cost,
                AVG(cost) as avg_cost_per_request
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY user_id
            ORDER BY total_cost DESC
            LIMIT :limit
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
            'limit' => $limit,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Get model usage statistics
     */
    public function getModelStats(string $startDate, string $endDate): array
    {
        $stmt = $this->db->prepare("
            SELECT
                model,
                COUNT(*) as request_count,
                SUM(cost) as total_cost,
                AVG(output_tokens) as avg_output_tokens
            FROM claude_costs
            WHERE created_at BETWEEN :start AND :end
            GROUP BY model
            ORDER BY total_cost DESC
        ");

        $stmt->execute([
            'start' => $startDate,
            'end' => $endDate,
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    private function initializeSchema(): void
    {
        $this->db->exec("
            CREATE TABLE IF NOT EXISTS claude_costs (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id TEXT NOT NULL,
                feature TEXT NOT NULL,
                model TEXT NOT NULL,
                input_tokens INTEGER NOT NULL,
                output_tokens INTEGER NOT NULL,
                cost REAL NOT NULL,
                request_id TEXT,
                created_at DATETIME NOT NULL
            )
        ");

        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_user_date ON claude_costs(user_id, created_at)");
        $this->db->exec("CREATE INDEX IF NOT EXISTS idx_feature_date ON claude_costs(feature, created_at)");
    }
}
