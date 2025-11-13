<?php

declare(strict_types=1);

namespace ComputerScience\Chapter04\Tests;

use ComputerScience\Chapter04\SinglyLinkedList;
use PHPUnit\Framework\TestCase;
use UnderflowException;
use OutOfBoundsException;

class SinglyLinkedListTest extends TestCase
{
    private SinglyLinkedList $list;

    protected function setUp(): void
    {
        $this->list = new SinglyLinkedList();
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

    public function testInsertAtBeginning(): void
    {
        $this->list->append(20);
        $this->list->insertAt(0, 10);

        $this->assertEquals([10, 20], $this->list->toArray());
    }

    public function testInsertAtEnd(): void
    {
        $this->list->append(10);
        $this->list->insertAt(1, 20);

        $this->assertEquals([10, 20], $this->list->toArray());
    }

    public function testInsertAtInvalidIndex(): void
    {
        $this->list->append(10);

        $this->expectException(OutOfBoundsException::class);
        $this->list->insertAt(5, 20);
    }

    public function testDeleteFirst(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $deleted = $this->list->deleteFirst();

        $this->assertEquals(10, $deleted);
        $this->assertEquals([20, 30], $this->list->toArray());
        $this->assertEquals(2, $this->list->size());
    }

    public function testDeleteFirstFromEmptyList(): void
    {
        $this->expectException(UnderflowException::class);
        $this->list->deleteFirst();
    }

    public function testDeleteByValue(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $result = $this->list->delete(20);

        $this->assertTrue($result);
        $this->assertEquals([10, 30], $this->list->toArray());
        $this->assertEquals(2, $this->list->size());
    }

    public function testDeleteNonExistentValue(): void
    {
        $this->list->append(10);
        $this->list->append(20);

        $result = $this->list->delete(999);

        $this->assertFalse($result);
        $this->assertEquals(2, $this->list->size());
    }

    public function testDeleteAt(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $deleted = $this->list->deleteAt(1);

        $this->assertEquals(20, $deleted);
        $this->assertEquals([10, 30], $this->list->toArray());
    }

    public function testDeleteAtInvalidIndex(): void
    {
        $this->list->append(10);

        $this->expectException(OutOfBoundsException::class);
        $this->list->deleteAt(5);
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

    public function testGetInvalidIndex(): void
    {
        $this->list->append(10);

        $this->expectException(OutOfBoundsException::class);
        $this->list->get(5);
    }

    public function testReverse(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);
        $this->list->append(40);

        $this->list->reverse();

        $this->assertEquals([40, 30, 20, 10], $this->list->toArray());
    }

    public function testFindMiddle(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);
        $this->list->append(40);
        $this->list->append(50);

        $middle = $this->list->findMiddle();

        $this->assertEquals(30, $middle);
    }

    public function testFindMiddleEvenLength(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);
        $this->list->append(40);

        $middle = $this->list->findMiddle();

        $this->assertEquals(30, $middle); // Returns second middle
    }

    public function testFindMiddleEmptyList(): void
    {
        $middle = $this->list->findMiddle();

        $this->assertNull($middle);
    }

    public function testHasCycleNoCycle(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $this->assertFalse($this->list->hasCycle());
    }

    public function testClear(): void
    {
        $this->list->append(10);
        $this->list->append(20);
        $this->list->append(30);

        $this->list->clear();

        $this->assertTrue($this->list->isEmpty());
        $this->assertEquals(0, $this->list->size());
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
    }

    public function testToStringEmptyList(): void
    {
        $string = (string)$this->list;

        $this->assertEquals('[]', $string);
    }

    public function testMixedOperations(): void
    {
        $this->list->append(10);
        $this->list->prepend(5);
        $this->list->append(20);
        $this->list->insertAt(2, 15);

        $this->assertEquals([5, 10, 15, 20], $this->list->toArray());

        $this->list->delete(15);
        $this->assertEquals([5, 10, 20], $this->list->toArray());

        $this->list->reverse();
        $this->assertEquals([20, 10, 5], $this->list->toArray());
    }
}
