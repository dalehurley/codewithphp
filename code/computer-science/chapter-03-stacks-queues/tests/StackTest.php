<?php

declare(strict_types=1);

namespace ComputerScience\Chapter03\Tests;

use ComputerScience\Chapter03\Stack;
use PHPUnit\Framework\TestCase;
use UnderflowException;

/**
 * Test cases for Stack implementation
 */
class StackTest extends TestCase
{
    private Stack $stack;

    protected function setUp(): void
    {
        $this->stack = new Stack();
    }

    public function testNewStackIsEmpty(): void
    {
        $this->assertTrue($this->stack->isEmpty());
        $this->assertEquals(0, $this->stack->size());
    }

    public function testPushAddsItems(): void
    {
        $this->stack->push(10);
        $this->assertFalse($this->stack->isEmpty());
        $this->assertEquals(1, $this->stack->size());

        $this->stack->push(20);
        $this->assertEquals(2, $this->stack->size());
    }

    public function testPopRemovesLastItem(): void
    {
        $this->stack->push(10);
        $this->stack->push(20);
        $this->stack->push(30);

        $this->assertEquals(30, $this->stack->pop());
        $this->assertEquals(2, $this->stack->size());

        $this->assertEquals(20, $this->stack->pop());
        $this->assertEquals(1, $this->stack->size());
    }

    public function testPeekReturnsTopWithoutRemoving(): void
    {
        $this->stack->push(10);
        $this->stack->push(20);

        $this->assertEquals(20, $this->stack->peek());
        $this->assertEquals(2, $this->stack->size()); // Size unchanged
        $this->assertEquals(20, $this->stack->peek()); // Still same item
    }

    public function testPopFromEmptyStackThrowsException(): void
    {
        $this->expectException(UnderflowException::class);
        $this->stack->pop();
    }

    public function testPeekEmptyStackThrowsException(): void
    {
        $this->expectException(UnderflowException::class);
        $this->stack->peek();
    }

    public function testLIFOOrder(): void
    {
        $items = [1, 2, 3, 4, 5];

        foreach ($items as $item) {
            $this->stack->push($item);
        }

        // Should come out in reverse order (LIFO)
        for ($i = count($items) - 1; $i >= 0; $i--) {
            $this->assertEquals($items[$i], $this->stack->pop());
        }

        $this->assertTrue($this->stack->isEmpty());
    }

    public function testMixedTypes(): void
    {
        $this->stack->push(42);
        $this->stack->push("hello");
        $this->stack->push([1, 2, 3]);
        $this->stack->push(true);

        $this->assertEquals(true, $this->stack->pop());
        $this->assertEquals([1, 2, 3], $this->stack->pop());
        $this->assertEquals("hello", $this->stack->pop());
        $this->assertEquals(42, $this->stack->pop());
    }

    public function testToArray(): void
    {
        $this->stack->push(10);
        $this->stack->push(20);
        $this->stack->push(30);

        $array = $this->stack->toArray();

        $this->assertEquals([10, 20, 30], $array);
    }

    public function testMultiplePushesAndPops(): void
    {
        $this->stack->push(1);
        $this->stack->push(2);
        $this->assertEquals(2, $this->stack->pop());

        $this->stack->push(3);
        $this->stack->push(4);
        $this->assertEquals(4, $this->stack->pop());
        $this->assertEquals(3, $this->stack->pop());
        $this->assertEquals(1, $this->stack->pop());

        $this->assertTrue($this->stack->isEmpty());
    }
}
