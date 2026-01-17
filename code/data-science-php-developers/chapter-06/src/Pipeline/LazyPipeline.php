<?php

declare(strict_types=1);

namespace DataScience\Pipeline;

use Generator;

class LazyPipeline
{
    private Generator $source;
    private array $operations = [];
    
    /**
     * Create pipeline from generator source
     */
    public static function from(Generator $source): self
    {
        $pipeline = new self();
        $pipeline->source = $source;
        return $pipeline;
    }
    
    /**
     * Filter items
     */
    public function filter(callable $predicate): self
    {
        $this->operations[] = function(Generator $source) use ($predicate): Generator {
            foreach ($source as $key => $item) {
                if ($predicate($item)) {
                    yield $key => $item;
                }
            }
        };
        
        return $this;
    }
    
    /**
     * Transform items
     */
    public function map(callable $transformer): self
    {
        $this->operations[] = function(Generator $source) use ($transformer): Generator {
            foreach ($source as $key => $item) {
                yield $key => $transformer($item);
            }
        };
        
        return $this;
    }
    
    /**
     * Take first N items
     */
    public function take(int $limit): self
    {
        $this->operations[] = function(Generator $source) use ($limit): Generator {
            $count = 0;
            
            foreach ($source as $key => $item) {
                if ($count >= $limit) {
                    break;
                }
                
                yield $key => $item;
                $count++;
            }
        };
        
        return $this;
    }
    
    /**
     * Skip first N items
     */
    public function skip(int $offset): self
    {
        $this->operations[] = function(Generator $source) use ($offset): Generator {
            $count = 0;
            
            foreach ($source as $key => $item) {
                $count++;
                
                if ($count <= $offset) {
                    continue;
                }
                
                yield $key => $item;
            }
        };
        
        return $this;
    }
    
    /**
     * Group items by key
     */
    public function groupBy(callable $keySelector): self
    {
        $this->operations[] = function(Generator $source) use ($keySelector): Generator {
            $groups = [];
            
            foreach ($source as $item) {
                $key = $keySelector($item);
                
                if (!isset($groups[$key])) {
                    $groups[$key] = [];
                }
                
                $groups[$key][] = $item;
            }
            
            foreach ($groups as $key => $items) {
                yield $key => $items;
            }
        };
        
        return $this;
    }
    
    /**
     * Reduce to single value
     */
    public function reduce(callable $reducer, mixed $initial = null): mixed
    {
        $result = $initial;
        
        foreach ($this->execute() as $item) {
            $result = $reducer($result, $item);
        }
        
        return $result;
    }
    
    /**
     * Collect to array
     */
    public function toArray(): array
    {
        return iterator_to_array($this->execute());
    }
    
    /**
     * Count items
     */
    public function count(): int
    {
        $count = 0;
        
        foreach ($this->execute() as $item) {
            $count++;
        }
        
        return $count;
    }
    
    /**
     * Execute pipeline and return generator
     */
    public function execute(): Generator
    {
        $current = $this->source;
        
        foreach ($this->operations as $operation) {
            $current = $operation($current);
        }
        
        return $current;
    }
    
    /**
     * Iterate over results
     */
    public function each(callable $callback): void
    {
        foreach ($this->execute() as $key => $item) {
            $callback($item, $key);
        }
    }
}
