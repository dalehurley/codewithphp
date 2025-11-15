<?php

declare(strict_types=1);

namespace App;

class ModelComparison
{
    public function compare(array $baseMetrics, array $fineTunedMetrics): array
    {
        return [
            'accuracy_improvement' => $fineTunedMetrics['accuracy'] - $baseMetrics['accuracy'],
            'base_model' => $baseMetrics,
            'fine_tuned_model' => $fineTunedMetrics
        ];
    }
}
