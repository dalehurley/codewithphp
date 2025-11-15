<?php

declare(strict_types=1);

namespace App;

class ModelEvaluation
{
    public function evaluate(array $predictions, array $groundTruth): array
    {
        $correct = 0;
        $total = count($predictions);
        
        foreach ($predictions as $i => $pred) {
            if (isset($groundTruth[$i]) && $pred === $groundTruth[$i]) {
                $correct++;
            }
        }
        
        return [
            'accuracy' => $correct / max($total, 1),
            'total' => $total,
            'correct' => $correct
        ];
    }
}
