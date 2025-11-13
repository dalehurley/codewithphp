<?php

declare(strict_types=1);

namespace ComputerScience\Chapter04\Tests;

use ComputerScience\Chapter04\DoublyLinkedList;
use PHPUnit\Framework\TestCase;
use UnderflowException;
use OutOfBoundsException;

class DoublyLinkedListTest extends TestCase
{
    private DoublyLinkedList $list;

    protected function setUp(): void
    {
        $this->list = new DoublyLinkedList();
    }

    public function testNewListIsEmpty(): void
    {
        $this->assertTrue($this->list->isEmpty());
        $this->assertEquals(0, $this->list->size());
    }

    public function testPrepend(): void
    {
        $this->list->prepend(10);
        $this->list->prepend(20);
        $this->list->prepend(30);

        $this->assertEquals([30, 20, 10], $this->list->toArray());
        $this->assertEquals(3, $this->list->size());
    }

    public function testAppend(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $this->assertEquals([10, 20, 30], $this->list->toArray());
        $this->assertEquals(3, $this->list->size());
    }

    public function testInsertAt(): void
    {
        $this->list->append(10);
        $this->list->append(30);
        $this->list->insertAt(1, 20);

        $this->assertEquals([10, 20, 30], $this->list->toArray());
    }

    public function testDeleteFirst(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $deleted = $this->list->deleteFirst();

        $this->assertEquals(10, $deleted);
        $this->assertEquals([20, 30], $this->list->toArray());
    }

    public function testDeleteLast(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $deleted = $this->list->deleteLast();

        $this->assertEquals(30, $deleted);
        $this->assertEquals([10, 20], $this->list->toArray());
    }

    public function testDeleteLastFromEmptyList(): void
    {
        $this->expectException(UnderflowException::class);
        $this->list->deleteLast();
    }

    public function testDeleteSingleElement(): void
    {
        $this->list->append(10);

        $this->list->deleteFirst();

        $this->assertTrue($this->list->isEmpty());
        $this->assertNull($this->list->getHead());
        $this->assertNull($this->list->getTail());
    }

    public function testDeleteByValue(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $result = $this->list->delete(20);

        $this->assertTrue($result);
        $this->assertEquals([10, 30], $this->list->toArray());
    }

    public function testSearch(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $this->assertTrue($this->list->search(20));
        $this->assertFalse($this->list->search(999));
    }

    public function testGet(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $this->assertEquals(10, $this->list->get(0));
        $this->assertEquals(20, $this->list->get(1));
        $this->assertEquals(30, $this->list->get(2));
    }

    public function testGetOptimization(): void
    {
        // Test that get() optimizes by traversing from closest end
        for ($i = 0; $i < 10; $i++) {
            $this->list->append($i);
        }

        // Access near end should traverse from tail
        $this->assertEquals(9, $this->list->get(9));
        $this->assertEquals(8, $this->list->get(8));

        // Access near beginning should traverse from head
        $this->assertEquals(0, $this->list->get(0));
        $this->assertEquals(1, $this->list->get(1));
    }

    public function testToArrayReverse(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $reversed = $this->list->toArrayReverse();

        $this->assertEquals([30, 20, 10], $reversed);
    }

    public function testBidirectionalTraversal(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $forward = $this->list->toArray();
        $backward = $this->list->toArrayReverse();

        $this->assertEquals([10, 20, 30], $forward);
        $this->assertEquals([30, 20, 10], $backward);
    }

    public function testHeadAndTailPointers(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $head = $this->list->getHead();
        $tail = $this->list->getTail();

        $this->assertEquals(10, $head->data);
        $this->assertEquals(30, $tail->data);
    }

    public function testClear(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $this->list->clear();

        $this->assertTrue($this->list->isEmpty());
        $this->assertEquals(0, $this->list->size());
        $this->assertNull($this->list->getHead());
        $this->assertNull($this->list->getTail());
    }

    public function testToString(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $string = (string)$this->list;

        $this->assertStringContainsString('10', $string);
        $this->assertStringContainsString('20', $string);
        $this->assertStringContainsString('30', $string);
        $this->assertStringContainsString('⇄', $string);
    }

    public function testMixedOperations(): void
    {
        $this->list->append(10);
        $this->list->prepend(5);
        $this->list->append(20);
        $this->list->insertAt(2, 15);

        $this->assertEquals([5, 10, 15, 20], $this->list->toArray());

        $this->list->deleteLast();
        $this->assertEquals([5, 10, 15], $this->list->toArray());

        $this->list->deleteFirst();
        $this->assertEquals([10, 15], $this->list->toArray());

        $this->list->delete(10);
        $this->assertEquals([15], $this->list->toArray());
    }
}
