<?php

declare(strict_types=1);

namespace ComputerScience\Chapter03\Tests;

use ComputerScience\Chapter03\CircularQueue;
use PHPUnit\Framework\TestCase;
use UnderflowException;
use OverflowException;

/**
 * Test cases for CircularQueue implementation
 */
class CircularQueueTest extends TestCase
{
    private CircularQueue $queue;

    protected function setUp(): void
    {
        $this->queue = new CircularQueue(5);
    }

    public function testNewQueueIsEmpty(): void
    {
        $this->assertTrue($this->queue->isEmpty());
        $this->assertFalse($this->queue->isFull());
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

    public function testQueueBecomesFullAtCapacity(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->queue->enqueue($i * 10);
        }

        $this->assertTrue($this->queue->isFull());
        $this->assertEquals(5, $this->queue->size());
    }

    public function testEnqueueToFullQueueThrowsException(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->queue->enqueue($i);
        }

        $this->expectException(OverflowException::class);
        $this->queue->enqueue(6);
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

    public function testCircularBehavior(): void
    {
        // Fill the queue
        for ($i = 1; $i <= 5; $i++) {
            $this->queue->enqueue($i);
        }

        // Remove first 3 items
        $this->assertEquals(1, $this->queue->dequeue());
        $this->assertEquals(2, $this->queue->dequeue());
        $this->assertEquals(3, $this->queue->dequeue());

        // Now we can add 3 more (circular wraparound)
        $this->queue->enqueue(6);
        $this->queue->enqueue(7);
        $this->queue->enqueue(8);

        // Verify correct order
        $this->assertEquals(4, $this->queue->dequeue());
        $this->assertEquals(5, $this->queue->dequeue());
        $this->assertEquals(6, $this->queue->dequeue());
        $this->assertEquals(7, $this->queue->dequeue());
        $this->assertEquals(8, $this->queue->dequeue());

        $this->assertTrue($this->queue->isEmpty());
    }

    public function testFIFOOrder(): void
    {
        $items = [10, 20, 30, 40, 50];

        foreach ($items as $item) {
            $this->queue->enqueue($item);
        }

        // Should come out in same order (FIFO)
        foreach ($items as $item) {
            $this->assertEquals($item, $this->queue->dequeue());
        }

        $this->assertTrue($this->queue->isEmpty());
    }

    public function testToArray(): void
    {
        $this->queue->enqueue(10);
        $this->queue->enqueue(20);
        $this->queue->enqueue(30);

        $array = $this->queue->toArray();

        $this->assertEquals([10, 20, 30], $array);
    }

    public function testToArrayWithWraparound(): void
    {
        // Fill queue
        for ($i = 1; $i <= 5; $i++) {
            $this->queue->enqueue($i);
        }

        // Remove 2 items and add 2 more (causing wraparound)
        $this->queue->dequeue(); // Remove 1
        $this->queue->dequeue(); // Remove 2
        $this->queue->enqueue(6);
        $this->queue->enqueue(7);

        $array = $this->queue->toArray();

        $this->assertEquals([3, 4, 5, 6, 7], $array);
    }

    public function testMultipleEnqueuesAndDequeuesWithCapacity(): void
    {
        $this->queue->enqueue(1);
        $this->queue->enqueue(2);
        $this->assertEquals(1, $this->queue->dequeue());

        $this->queue->enqueue(3);
        $this->queue->enqueue(4);
        $this->queue->enqueue(5);
        $this->queue->enqueue(6);

        // Queue now has [2, 3, 4, 5, 6] but is full
        $this->assertTrue($this->queue->isFull());

        $this->assertEquals(2, $this->queue->dequeue());
        $this->assertFalse($this->queue->isFull());
    }
}
