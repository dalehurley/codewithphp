<?php

// Interface: A contract
interface Playable
{
    public function play(): string;
}

// Abstract class: A template
abstract class Instrument
{
    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    abstract public function makeSound(): string;
}

// Inheritance + Interface implementation
class Guitar extends Instrument implements Playable
{
    public function makeSound(): string
    {
        return "Strum strum";
    }

    public function play(): string
    {
        return "Playing {$this->name}: {$this->makeSound()}";
    }
}

$guitar = new Guitar("Fender Stratocaster");
echo $guitar->play() . PHP_EOL;
