<?php

declare(strict_types=1);

namespace ComputerScience\Chapter02\Tests;

use ComputerScience\Chapter02\DynamicArray;
use PHPUnit\Framework\TestCase;
use OutOfBoundsException;

class DynamicArrayTest extends TestCase
{
    private DynamicArray $array;

    protected function setUp(): void
    {
        $this->array = new DynamicArray(4);
    }

    public function testNewArrayIsEmpty(): void
    {
        $this->assertTrue($this->array->isEmpty());
        $this->assertEquals(0, $this->array->size());
        $this->assertEquals(4, $this->array->capacity());
    }

    public function testAddElements(): void
    {
        $this->array->add(10);
        $this->array->add(20);
        $this->array->add(30);

        $this->assertEquals(3, $this->array->size());
        $this->assertEquals(10, $this->array->get(0));
        $this->assertEquals(20, $this->array->get(1));
        $this->assertEquals(30, $this->array->get(2));
    }

    public function testResizeWhenFull(): void
    {
        $this->array->add(1);
        $this->array->add(2);
        $this->array->add(3);
        $this->array->add(4);

        $this->assertEquals(4, $this->array->capacity());
        $this->assertEquals(0, $this->array->getResizeCount());

        $this->array->add(5); // Triggers resize

        $this->assertEquals(8, $this->array->capacity());
        $this->assertEquals(1, $this->array->getResizeCount());
    }

    public function testGetOutOfBoundsThrowsException(): void
    {
        $this->array->add(10);

        $this->expectException(OutOfBoundsException::class);
        $this->array->get(1);
    }

    public function testSetElement(): void
    {
        $this->array->add(10);
        $this->array->add(20);

        $this->array->set(0, 100);
        $this->assertEquals(100, $this->array->get(0));
    }

    public function testRemoveElement(): void
    {
        $this->array->add(10);
        $this->array->add(20);
        $this->array->add(30);

        $removed = $this->array->remove(1);

        $this->assertEquals(20, $removed);
        $this->assertEquals(2, $this->array->size());
        $this->assertEquals(10, $this->array->get(0));
        $this->assertEquals(30, $this->array->get(1));
    }

    public function testInsertElement(): void
    {
        $this->array->add(10);
        $this->array->add(30);

        $this->array->insert(1, 20);

        $this->assertEquals(3, $this->array->size());
        $this->assertEquals(10, $this->array->get(0));
        $this->assertEquals(20, $this->array->get(1));
        $this->assertEquals(30, $this->array->get(2));
    }

    public function testIndexOf(): void
    {
        $this->array->add(10);
        $this->array->add(20);
        $this->array->add(30);

        $this->assertEquals(1, $this->array->indexOf(20));
        $this->assertNull($this->array->indexOf(999));
    }

    public function testContains(): void
    {
        $this->array->add(10);
        $this->array->add(20);

        $this->assertTrue($this->array->contains(10));
        $this->assertFalse($this->array->contains(999));
    }

    public function testClear(): void
    {
        $this->array->add(10);
        $this->array->add(20);
        $this->array->add(30);

        $this->array->clear();

        $this->assertTrue($this->array->isEmpty());
        $this->assertEquals(0, $this->array->size());
    }

    public function testToArray(): void
    {
        $this->array->add(10);
        $this->array->add(20);
        $this->array->add(30);

        $result = $this->array->toArray();

        $this->assertEquals([10, 20, 30], $result);
    }

    public function testMultipleResizes(): void
    {
        // Add 17 elements to trigger multiple resizes
        // Capacity: 4 → 8 → 16 → 32
        for ($i = 1; $i <= 17; $i++) {
            $this->array->add($i);
        }

        $this->assertEquals(17, $this->array->size());
        $this->assertEquals(32, $this->array->capacity());
        $this->assertEquals(3, $this->array->getResizeCount());
    }

    public function testToString(): void
    {
        $this->array->add(1);
        $this->array->add(2);
        $this->array->add(3);

        $string = (string)$this->array;

        $this->assertStringContainsString('1, 2, 3', $string);
        $this->assertStringContainsString('size: 3', $string);
        $this->assertStringContainsString('capacity: 4', $string);
    }
}
