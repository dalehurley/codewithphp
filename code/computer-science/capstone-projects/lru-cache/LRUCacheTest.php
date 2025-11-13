<?php

declare(strict_types=1);

namespace ComputerScience\Capstone\Tests;

use ComputerScience\Capstone\LRUCache;
use PHPUnit\Framework\TestCase;

class LRUCacheTest extends TestCase
{
    public function testBasicPutAndGet(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->put(2, 'two');

        $this->assertEquals('one', $cache->get(1));
        $this->assertEquals('two', $cache->get(2));
    }

    public function testEvictionWhenFull(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->put(2, 'two');
        $cache->put(3, 'three');  // Should evict key 1

        $this->assertNull($cache->get(1));  // Evicted
        $this->assertEquals('two', $cache->get(2));
        $this->assertEquals('three', $cache->get(3));
    }

    public function testGetUpdatesAccessOrder(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->put(2, 'two');
        $cache->get(1);            // Access 1, making it most recent
        $cache->put(3, 'three');   // Should evict 2, not 1

        $this->assertEquals('one', $cache->get(1));    // Still exists
        $this->assertNull($cache->get(2));             // Evicted
        $this->assertEquals('three', $cache->get(3));
    }

    public function testUpdateExistingKey(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->put(2, 'two');
        $cache->put(1, 'ONE');  // Update value for key 1

        $this->assertEquals('ONE', $cache->get(1));
        $this->assertEquals(2, $cache->size());  // Size unchanged
    }

    public function testUpdateMovesToFront(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->put(2, 'two');
        $cache->put(1, 'ONE');     // Update 1, moves to front
        $cache->put(3, 'three');   // Should evict 2, not 1

        $this->assertEquals('ONE', $cache->get(1));
        $this->assertNull($cache->get(2));  // Evicted
        $this->assertEquals('three', $cache->get(3));
    }

    public function testGetNonExistentKey(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');

        $this->assertNull($cache->get(999));
    }

    public function testHas(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');

        $this->assertTrue($cache->has(1));
        $this->assertFalse($cache->has(999));
    }

    public function testDelete(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->put(2, 'two');

        $this->assertTrue($cache->delete(1));
        $this->assertFalse($cache->has(1));
        $this->assertEquals(1, $cache->size());
    }

    public function testDeleteNonExistent(): void
    {
        $cache = new LRUCache(2);

        $this->assertFalse($cache->delete(999));
    }

    public function testClear(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->put(2, 'two');

        $cache->clear();

        $this->assertTrue($cache->isEmpty());
        $this->assertEquals(0, $cache->size());
        $this->assertNull($cache->get(1));
        $this->assertNull($cache->get(2));
    }

    public function testCapacityOne(): void
    {
        $cache = new LRUCache(1);

        $cache->put(1, 'one');
        $cache->put(2, 'two');  // Should evict 1

        $this->assertNull($cache->get(1));
        $this->assertEquals('two', $cache->get(2));
    }

    public function testKeys(): void
    {
        $cache = new LRUCache(3);

        $cache->put(1, 'one');
        $cache->put(2, 'two');
        $cache->put(3, 'three');

        $keys = $cache->keys();

        $this->assertEquals([3, 2, 1], $keys);  // MRU to LRU order
    }

    public function testKeysAfterAccess(): void
    {
        $cache = new LRUCache(3);

        $cache->put(1, 'one');
        $cache->put(2, 'two');
        $cache->put(3, 'three');
        $cache->get(1);  // Access 1, moves to front

        $keys = $cache->keys();

        $this->assertEquals([1, 3, 2], $keys);  // 1 is now MRU
    }

    public function testValues(): void
    {
        $cache = new LRUCache(3);

        $cache->put(1, 'a');
        $cache->put(2, 'b');
        $cache->put(3, 'c');

        $values = $cache->values();

        $this->assertEquals(['c', 'b', 'a'], $values);  // MRU to LRU
    }

    public function testIsEmpty(): void
    {
        $cache = new LRUCache(2);

        $this->assertTrue($cache->isEmpty());

        $cache->put(1, 'one');
        $this->assertFalse($cache->isEmpty());

        $cache->clear();
        $this->assertTrue($cache->isEmpty());
    }

    public function testIsFull(): void
    {
        $cache = new LRUCache(2);

        $this->assertFalse($cache->isFull());

        $cache->put(1, 'one');
        $this->assertFalse($cache->isFull());

        $cache->put(2, 'two');
        $this->assertTrue($cache->isFull());

        $cache->put(3, 'three');
        $this->assertTrue($cache->isFull());  // Still full after eviction
    }

    public function testStats(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->put(2, 'two');

        $cache->get(1);    // Hit
        $cache->get(2);    // Hit
        $cache->get(999);  // Miss

        $stats = $cache->getStats();

        $this->assertEquals(2, $stats['hits']);
        $this->assertEquals(1, $stats['misses']);
        $this->assertEquals(2/3, $stats['hitRate'], '', 0.01);
    }

    public function testResetStats(): void
    {
        $cache = new LRUCache(2);

        $cache->put(1, 'one');
        $cache->get(1);
        $cache->get(999);

        $cache->resetStats();

        $stats = $cache->getStats();

        $this->assertEquals(0, $stats['hits']);
        $this->assertEquals(0, $stats['misses']);
    }

    public function testToString(): void
    {
        $cache = new LRUCache(3);

        $cache->put(1, 'a');
        $cache->put(2, 'b');

        $string = (string)$cache;

        $this->assertStringContainsString('2=b', $string);
        $this->assertStringContainsString('1=a', $string);
        $this->assertStringContainsString('2/3', $string);
    }

    public function testComplexScenario(): void
    {
        $cache = new LRUCache(3);

        $cache->put(1, 'one');
        $cache->put(2, 'two');
        $cache->put(3, 'three');

        $this->assertEquals(['three', 'two', 'one'], $cache->values());

        $cache->get(1);  // 1 becomes MRU
        $this->assertEquals(['one', 'three', 'two'], $cache->values());

        $cache->put(4, 'four');  // Evicts 2 (LRU)
        $this->assertNull($cache->get(2));
        $this->assertEquals(['four', 'one', 'three'], $cache->values());

        $cache->put(3, 'THREE');  // Update 3, moves to front
        $this->assertEquals(['THREE', 'four', 'one'], $cache->values());
    }

    public function testInvalidCapacity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new LRUCache(0);
    }

    public function testMixedDataTypes(): void
    {
        $cache = new LRUCache(3);

        $cache->put(1, 42);
        $cache->put(2, 'string');
        $cache->put(3, [1, 2, 3]);

        $this->assertEquals(42, $cache->get(1));
        $this->assertEquals('string', $cache->get(2));
        $this->assertEquals([1, 2, 3], $cache->get(3));
    }

    public function testLargeCapacity(): void
    {
        $cache = new LRUCache(1000);

        for ($i = 0; $i < 1000; $i++) {
            $cache->put($i, "value_$i");
        }

        $this->assertEquals(1000, $cache->size());
        $this->assertTrue($cache->isFull());

        $cache->put(1000, 'overflow');  // Should evict 0
        $this->assertNull($cache->get(0));
        $this->assertEquals('overflow', $cache->get(1000));
    }
}
