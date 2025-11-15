<?php

declare(strict_types=1);

namespace App;

class SequentialSteps
{
    private array $pipeline = [];
    
    public function add(callable $step): self
    {
        $this->pipeline[] = $step;
        return $this;
    }
    
    public function run($input)
    {
        $output = $input;
        foreach ($this->pipeline as $step) {
            $output = $step($output);
        }
        return $output;
    }
}
