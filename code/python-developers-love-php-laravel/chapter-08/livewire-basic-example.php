<?php

declare(strict_types=1);

/**
 * filename: app/Livewire/Counter.php
 * Example: Basic Livewire component
 * 
 * Installation: composer require livewire/livewire
 * Publish config: php artisan livewire:publish --config
 * 
 * Usage in Blade template:
 * <livewire:counter />
 */

namespace App\Livewire;

use Livewire\Component;

class Counter extends Component
{
    public int $count = 0;

    public function increment(): void
    {
        $this->count++;
    }

    public function decrement(): void
    {
        $this->count--;
    }

    public function reset(): void
    {
        $this->count = 0;
    }

    public function render(): string
    {
        return view('livewire.counter');
    }
}

/**
 * filename: resources/views/livewire/counter.blade.php
 * Blade template for the Counter component
 */
/*
<div>
    <h1>Count: {{ $count }}</h1>
    
    <button wire:click="increment" class="btn btn-primary">+</button>
    <button wire:click="decrement" class="btn btn-secondary">-</button>
    <button wire:click="reset" class="btn btn-danger">Reset</button>
    
    <p>This counter updates without page refresh!</p>
</div>
*/

/**
 * filename: resources/views/layouts/app.blade.php
 * Include Livewire styles and scripts in your layout
 */
/*
<!DOCTYPE html>
<html>
<head>
    @livewireStyles
</head>
<body>
    @livewire('counter')
    
    @livewireScripts
</body>
</html>
*/

