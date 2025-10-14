<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        
        // Add any global test setup here
    }

    /**
     * Clean up after tests.
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Add any global test cleanup here
    }

    /**
     * Assert that a value is not null.
     */
    protected function assertNotNull($actual, string $message = ''): void
    {
        $this->assertNotSame(null, $actual, $message);
    }

    /**
     * Assert that a value is null.
     */
    protected function assertNull($actual, string $message = ''): void
    {
        $this->assertSame(null, $actual, $message);
    }
}
