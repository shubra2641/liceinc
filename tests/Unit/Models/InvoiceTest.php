<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\License;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for Invoice model.
 */
class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test invoice creation.
     */
    public function test_can_create_invoice(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
            'notes' => 'Test invoice',
        ]);

        $this->assertInstanceOf(Invoice::class, $invoice);
        $this->assertEquals($user->id, $invoice->user_id);
        $this->assertEquals($license->id, $invoice->license_id);
        $this->assertEquals($product->id, $invoice->product_id);
        $this->assertEquals('renewal', $invoice->type);
        $this->assertEquals(99.99, $invoice->amount);
        $this->assertEquals('USD', $invoice->currency);
        $this->assertEquals('pending', $invoice->status);
    }

    /**
     * Test automatic invoice number generation.
     */
    public function test_automatic_invoice_number_generation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->assertNotNull($invoice->invoice_number);
        $this->assertStringStartsWith('INV-', $invoice->invoice_number);
        $this->assertStringContainsString(date('Y'), $invoice->invoice_number);
    }

    /**
     * Test generateInvoiceNumber method.
     */
    public function test_generate_invoice_number(): void
    {
        $invoiceNumber = Invoice::generateInvoiceNumber();

        $this->assertIsString($invoiceNumber);
        $this->assertStringStartsWith('INV-', $invoiceNumber);
        $this->assertStringContainsString(date('Y'), $invoiceNumber);
        $this->assertEquals(16, strlen($invoiceNumber)); // INV-YYYY-XXXXXXXX
    }

    /**
     * Test isOverdue method.
     */
    public function test_is_overdue(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $overdueInvoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->subDays(1),
        ]);

        $pendingInvoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(1),
        ]);

        $this->assertTrue($overdueInvoice->isOverdue());
        $this->assertFalse($pendingInvoice->isOverdue());
    }

    /**
     * Test markAsPaid method.
     */
    public function test_mark_as_paid(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $result = $invoice->markAsPaid();

        $this->assertTrue($result);
        $this->assertEquals('paid', $invoice->fresh()->status);
        $this->assertNotNull($invoice->fresh()->paid_at);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Invoice marked as paid') &&
                   $context['amount'] === 99.99;
        });
    }

    /**
     * Test markAsOverdue method.
     */
    public function test_mark_as_overdue(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $result = $invoice->markAsOverdue();

        $this->assertTrue($result);
        $this->assertEquals('overdue', $invoice->fresh()->status);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Invoice marked as overdue');
        });
    }

    /**
     * Test cancel method.
     */
    public function test_cancel(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $result = $invoice->cancel();

        $this->assertTrue($result);
        $this->assertEquals('cancelled', $invoice->fresh()->status);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Invoice cancelled');
        });
    }

    /**
     * Test remaining amount attribute.
     */
    public function test_remaining_amount_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $pendingInvoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $paidInvoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'paid',
            'due_date' => now()->addDays(30),
            'paid_at' => now(),
        ]);

        $this->assertEquals(99.99, $pendingInvoice->remaining_amount);
        $this->assertEquals(0, $paidInvoice->remaining_amount);
    }

    /**
     * Test days until due attribute.
     */
    public function test_days_until_due_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
        ]);

        $this->assertEquals(7, $invoice->days_until_due);
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'paid',
            'due_date' => now()->addDays(30),
            'paid_at' => now(),
        ]);

        Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'overdue',
            'due_date' => now()->subDays(1),
        ]);

        $this->assertCount(1, Invoice::pending()->get());
        $this->assertCount(1, Invoice::paid()->get());
        $this->assertCount(1, Invoice::overdue()->get());
    }

    /**
     * Test dueSoon scope.
     */
    public function test_due_soon_scope(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(3),
        ]);

        Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(10),
        ]);

        $dueSoon = Invoice::dueSoon(7)->get();

        $this->assertCount(1, $dueSoon);
        $this->assertEquals(3, $dueSoon->first()->days_until_due);
    }

    /**
     * Test relationships.
     */
    public function test_relationships(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $invoice = Invoice::create([
            'user_id' => $user->id,
            'license_id' => $license->id,
            'product_id' => $product->id,
            'type' => 'renewal',
            'amount' => 99.99,
            'currency' => 'USD',
            'status' => 'pending',
            'due_date' => now()->addDays(30),
        ]);

        $this->assertInstanceOf(User::class, $invoice->user);
        $this->assertInstanceOf(License::class, $invoice->license);
        $this->assertInstanceOf(Product::class, $invoice->product);
        $this->assertEquals($user->id, $invoice->user->id);
        $this->assertEquals($license->id, $invoice->license->id);
        $this->assertEquals($product->id, $invoice->product->id);
    }
}
