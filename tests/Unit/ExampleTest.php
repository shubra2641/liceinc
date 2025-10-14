<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_basic_assertion(): void
    {
        $this->assertTrue(true);
    }

    /**
     * Test string operations.
     */
    public function test_string_operations(): void
    {
        $string = 'Hello World';
        $this->assertEquals('Hello World', $string);
        $this->assertStringContainsString('World', $string);
    }

    /**
     * Test array operations.
     */
    public function test_array_operations(): void
    {
        $array = [1, 2, 3, 4, 5];
        $this->assertCount(5, $array);
        $this->assertContains(3, $array);
        $this->assertNotContains(6, $array);
    }

    /**
     * Test mathematical operations.
     */
    public function test_mathematical_operations(): void
    {
        $result = 2 + 2;
        $this->assertEquals(4, $result);
        $this->assertGreaterThan(3, $result);
        $this->assertLessThan(5, $result);
    }
}
