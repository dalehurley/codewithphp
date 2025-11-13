<?php

declare(strict_types=1);

namespace ComputerScience\Chapter03\Tests;

use ComputerScience\Chapter03\Queue;
use PHPUnit\Framework\TestCase;
use UnderflowException;

/**
 * Test cases for Queue implementation
 */
class QueueTest extends TestCase
{
    private Queue $queue;

    protected function setUp(): void
    {
        $this->queue = new Queue();
    }

    public function testNewQueueIsEmpty(): void
    {
        $this->assertTrue($this->queue->isEmpty());
        $this->assertEquals(0, $this->queue->size());
    }

    public function testEnqueueAddsItems(): void
    {
        $this->queue->enqueue(10);
        $this->assertFalse($this->queue->isEmpty());
        $this->assertEquals(1, $this->queue->size());

        $this->queue->enqueue(20);
        $this->assertEquals(2, $this->queue->size());
    }

    public function testDequeueRemovesFirstItem(): void
    {
        $this->queue->enqueue(10);
        $this->queue->enqueue(20);
        $this->queue->enqueue(30);

        $this->assertEquals(10, $this->queue->dequeue());
        $this->assertEquals(2, $this->queue->size());

        $this->assertEquals(20, $this->queue->dequeue());
        $this->assertEquals(1, $this->queue->size());
    }

    public function testFrontReturnsFirstWithoutRemoving(): void
    {
        $this->queue->enqueue(10);
        $this->queue->enqueue(20);

        $this->assertEquals(10, $this->queue->front());
        $this->assertEquals(2, $this->queue->size()); // Size unchanged
        $this->assertEquals(10, $this->queue->front()); // Still same item
    }

    public function testDequeueFromEmptyQueueThrowsException(): void
    {
        $this->expectException(UnderflowException::class);
        $this->queue->dequeue();
    }

    public function testFrontEmptyQueueThrowsException(): void
    {
        $this->expectException(UnderflowException::class);
        $this->queue->front();
    }

    public function testFIFOOrder(): void
    {
        $items = [1, 2, 3, 4, 5];

        foreach ($items as $item) {
            $this->queue->enqueue($item);
        }

        // Should come out in same order (FIFO)
        foreach ($items as $item) {
            $this->assertEquals($item, $this->queue->dequeue());
        }

        $this->assertTrue($this->queue->isEmpty());
    }

    public function testMixedTypes(): void
    {
        $this->queue->enqueue(42);
        $this->queue->enqueue("hello");
        $this->queue->enqueue([1, 2, 3]);
        $this->queue->enqueue(true);

        $this->assertEquals(42, $this->queue->dequeue());
        $this->assertEquals("hello", $this->queue->dequeue());
        $this->assertEquals([1, 2, 3], $this->queue->dequeue());
        $this->assertEquals(true, $this->queue->dequeue());
    }

    public function testToArray(): void
    {
        $this->queue->enqueue(10);
        $this->queue->enqueue(20);
        $this->queue->enqueue(30);

        $array = $this->queue->toArray();

        $this->assertEquals([10, 20, 30], $array);
    }

    public function testMultipleEnqueuesAndDequeues(): void
    {
        $this->queue->enqueue(1);
        $this->queue->enqueue(2);
        $this->assertEquals(1, $this->queue->dequeue());

        $this->queue->enqueue(3);
        $this->queue->enqueue(4);
        $this->assertEquals(2, $this->queue->dequeue());
        $this->assertEquals(3, $this->queue->dequeue());
        $this->assertEquals(4, $this->queue->dequeue());

        $this->assertTrue($this->queue->isEmpty());
    }
}
