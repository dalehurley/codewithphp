<?php

declare(strict_types=1);

namespace App;

class AlertingSystem
{
    private array $rules = [];
    
    public function addRule(string $name, callable $condition, callable $action): void
    {
        $this->rules[$name] = ['condition' => $condition, 'action' => $action];
    }
    
    public function check(array $metrics): array
    {
        $triggered = [];
        
        foreach ($this->rules as $name => $rule) {
            if (call_user_func($rule['condition'], $metrics)) {
                call_user_func($rule['action'], $metrics);
                $triggered[] = $name;
            }
        }
        
        return $triggered;
    }
}
