<?php

declare(strict_types=1);

namespace App;

class ModelRouter
{
    private array $models = [
        'haiku' => ['cost' => 0.25, 'capability' => 1],
        'sonnet' => ['cost' => 3.0, 'capability' => 5],
        'opus' => ['cost' => 15.0, 'capability' => 10]
    ];
    
    public function selectModel(string $task, int $complexity): string
    {
        if ($complexity <= 3) {
            return 'haiku';
        } elseif ($complexity <= 7) {
            return 'sonnet';
        } else {
            return 'opus';
        }
    }
    
    public function estimateCost(string $model, int $tokens): float
    {
        $costPerMillion = $this->models[$model]['cost'];
        return ($tokens / 1_000_000) * $costPerMillion;
    }
    
    public function optimize(array $tasks): array
    {
        return array_map(function($task) {
            $complexity = $task['complexity'] ?? 5;
            $model = $this->selectModel($task['description'], $complexity);
            
            return array_merge($task, [
                'selected_model' => $model,
                'estimated_cost' => $this->estimateCost($model, $task['tokens'] ?? 1000)
            ]);
        }, $tasks);
    }
}
