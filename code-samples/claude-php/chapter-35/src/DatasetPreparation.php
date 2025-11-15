<?php

declare(strict_types=1);

namespace App;

class DatasetPreparation
{
    public function prepare(array $examples): array
    {
        return array_map(fn($ex) => [
            'prompt' => $ex['input'],
            'completion' => $ex['output']
        ], $examples);
    }
    
    public function validate(array $dataset): array
    {
        $errors = [];
        foreach ($dataset as $i => $example) {
            if (empty($example['prompt']) || empty($example['completion'])) {
                $errors[] = "Example $i missing prompt or completion";
            }
        }
        return $errors;
    }
}
