<?php

namespace Tests\Unit\Models;

use App\Models\Invoice;
use App\Models\License;
use App\Models\LicenseDomain;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for License model.
 */
class LicenseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test license creation.
     */
    public function test_can_create_license(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'license_type' => 'premium',
            'status' => 'active',
            'max_domains' => 5,
            'notes' => 'Test license',
        ]);

        $this->assertInstanceOf(License::class, $license);
        $this->assertEquals($product->id, $license->product_id);
        $this->assertEquals($user->id, $license->user_id);
        $this->assertEquals('premium', $license->license_type);
        $this->assertEquals('active', $license->status);
        $this->assertEquals(5, $license->max_domains);
        $this->assertEquals('Test license', $license->notes);
        $this->assertNotNull($license->purchase_code);
        $this->assertNotNull($license->license_key);
    }

    /**
     * Test automatic purchase code generation.
     */
    public function test_automatic_purchase_code_generation(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $this->assertNotNull($license->purchase_code);
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', $license->purchase_code);
    }

    /**
     * Test license key equals purchase code.
     */
    public function test_license_key_equals_purchase_code(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals($license->purchase_code, $license->license_key);
    }

    /**
     * Test default values.
     */
    public function test_default_values(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $this->assertEquals('active', $license->status);
        $this->assertEquals(1, $license->max_domains);
    }

    /**
     * Test support active attribute.
     */
    public function test_support_active_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $activeLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'support_expires_at' => now()->addDays(30),
        ]);

        $expiredLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'support_expires_at' => now()->subDays(30),
        ]);

        $noSupportLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $this->assertTrue($activeLicense->support_active);
        $this->assertFalse($expiredLicense->support_active);
        $this->assertFalse($noSupportLicense->support_active);
    }

    /**
     * Test expires at attribute.
     */
    public function test_expires_at_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'license_expires_at' => now()->addDays(30),
        ]);

        $this->assertEquals($license->license_expires_at, $license->expires_at);

        $license->expires_at = now()->addDays(60);
        $this->assertEquals(now()->addDays(60)->format('Y-m-d H:i:s'), $license->license_expires_at->format('Y-m-d H:i:s'));
    }

    /**
     * Test active domains count attribute.
     */
    public function test_active_domains_count_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'test.com',
            'status' => 'inactive',
        ]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'active.com',
            'status' => 'active',
        ]);

        $this->assertEquals(2, $license->active_domains_count);
    }

    /**
     * Test has reached domain limit method.
     */
    public function test_has_reached_domain_limit(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'max_domains' => 2,
        ]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);

        $this->assertFalse($license->hasReachedDomainLimit());

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'test.com',
            'status' => 'active',
        ]);

        $this->assertTrue($license->fresh()->hasReachedDomainLimit());
    }

    /**
     * Test remaining domains attribute.
     */
    public function test_remaining_domains_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'max_domains' => 3,
        ]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);

        $this->assertEquals(2, $license->fresh()->remaining_domains);
    }

    /**
     * Test status check methods.
     */
    public function test_status_check_methods(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $activeLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'active',
            'license_expires_at' => now()->addDays(30),
        ]);

        $expiredLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'active',
            'license_expires_at' => now()->subDays(30),
        ]);

        $suspendedLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'suspended',
        ]);

        $this->assertTrue($activeLicense->isActive());
        $this->assertFalse($activeLicense->isExpired());
        $this->assertFalse($activeLicense->isSuspended());

        $this->assertFalse($expiredLicense->isActive());
        $this->assertTrue($expiredLicense->isExpired());
        $this->assertFalse($expiredLicense->isSuspended());

        $this->assertFalse($suspendedLicense->isActive());
        $this->assertFalse($suspendedLicense->isExpired());
        $this->assertTrue($suspendedLicense->isSuspended());
    }

    /**
     * Test status badge class attribute.
     */
    public function test_status_badge_class_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $activeLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $suspendedLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'suspended',
        ]);

        $expiredLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'expired',
        ]);

        $this->assertEquals('badge-success', $activeLicense->status_badge_class);
        $this->assertEquals('badge-warning', $suspendedLicense->status_badge_class);
        $this->assertEquals('badge-danger', $expiredLicense->status_badge_class);
    }

    /**
     * Test status label attribute.
     */
    public function test_status_label_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $this->assertEquals('Active', $license->status_label);
    }

    /**
     * Test days until expiry attribute.
     */
    public function test_days_until_expiry_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'license_expires_at' => now()->addDays(30),
        ]);

        $this->assertEquals(30, $license->days_until_expiry);

        $noExpiryLicense = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);

        $this->assertNull($noExpiryLicense->days_until_expiry);
    }

    /**
     * Test days until support expiry attribute.
     */
    public function test_days_until_support_expiry_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'support_expires_at' => now()->addDays(15),
        ]);

        $this->assertEquals(15, $license->days_until_support_expiry);
    }

    /**
     * Test mark as active method.
     */
    public function test_mark_as_active(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'suspended',
        ]);

        $result = $license->markAsActive();

        $this->assertTrue($result);
        $this->assertEquals('active', $license->fresh()->status);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'License marked as active');
        });
    }

    /**
     * Test mark as suspended method.
     */
    public function test_mark_as_suspended(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);

        $result = $license->markAsSuspended();

        $this->assertTrue($result);
        $this->assertEquals('suspended', $license->fresh()->status);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'License marked as suspended');
        });
    }

    /**
     * Test mark as expired method.
     */
    public function test_mark_as_expired(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create([
            'user_id' => $user->id,
            'product_id' => $product->id,
            'status' => 'active',
        ]);

        $result = $license->markAsExpired();

        $this->assertTrue($result);
        $this->assertEquals('expired', $license->fresh()->status);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'License marked as expired');
        });
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'active',
            'license_expires_at' => now()->addDays(30),
        ]);

        License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'suspended',
        ]);

        License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'active',
            'license_expires_at' => now()->subDays(30),
        ]);

        $this->assertCount(1, License::active()->get());
        $this->assertCount(1, License::suspended()->get());
        $this->assertCount(1, License::expired()->get());
        $this->assertCount(3, License::forUser($user)->get());
        $this->assertCount(3, License::forProduct($product->id)->get());
        $this->assertCount(2, License::byStatus('active')->get());
    }

    /**
     * Test relationships.
     */
    public function test_relationships(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);

        $log = LicenseLog::create([
            'license_id' => $license->id,
            'status' => 'success',
        ]);

        $invoice = Invoice::create([
            'license_id' => $license->id,
            'user_id' => $user->id,
            'product_id' => $product->id,
            'amount' => 100.00,
            'status' => 'pending',
        ]);

        $this->assertInstanceOf(User::class, $license->user);
        $this->assertInstanceOf(Product::class, $license->product);
        $this->assertTrue($license->domains->contains($domain));
        $this->assertTrue($license->logs->contains($log));
        $this->assertTrue($license->invoices->contains($invoice));
    }

    /**
     * Test static methods.
     */
    public function test_static_methods(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'active',
            'license_expires_at' => now()->addDays(30),
        ]);

        License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'status' => 'suspended',
        ]);

        $statistics = License::getStatistics();
        $forUser = License::getForUser($user);
        $forProduct = License::getForProduct($product->id);
        $expiringLicenses = License::getExpiringLicenses(30);

        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('active', $statistics);
        $this->assertArrayHasKey('suspended', $statistics);
        $this->assertArrayHasKey('expired', $statistics);
        $this->assertEquals(2, $statistics['total']);
        $this->assertEquals(1, $statistics['active']);
        $this->assertEquals(1, $statistics['suspended']);

        $this->assertCount(2, $forUser);
        $this->assertCount(2, $forProduct);
        $this->assertCount(1, $expiringLicenses);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $license = License::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'max_domains' => '5',
        ]);

        $this->assertIsInt($license->product_id);
        $this->assertIsInt($license->user_id);
        $this->assertIsInt($license->max_domains);
    }
}
