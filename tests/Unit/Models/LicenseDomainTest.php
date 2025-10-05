<?php

namespace Tests\Unit\Models;

use App\Models\License;
use App\Models\LicenseDomain;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Test suite for LicenseDomain model.
 */
class LicenseDomainTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Log is already configured for testing
    }

    /**
     * Test domain creation.
     */
    public function test_can_create_domain(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
            'is_verified' => true,
            'verified_at' => now(),
            'added_at' => now(),
        ]);

        $this->assertInstanceOf(LicenseDomain::class, $domain);
        $this->assertEquals($license->id, $domain->license_id);
        $this->assertEquals('example.com', $domain->domain);
        $this->assertEquals('active', $domain->status);
        $this->assertTrue($domain->is_verified);
    }

    /**
     * Test automatic added_at timestamp.
     */
    public function test_automatic_added_at_timestamp(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);

        $this->assertNotNull($domain->added_at);
    }

    /**
     * Test markAsVerified method.
     */
    public function test_mark_as_verified(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'pending',
            'is_verified' => false,
        ]);

        $result = $domain->markAsVerified();

        $this->assertTrue($result);
        $this->assertTrue($domain->fresh()->is_verified);
        $this->assertEquals('active', $domain->fresh()->status);
        $this->assertNotNull($domain->fresh()->verified_at);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Domain marked as verified') &&
                   $context['domain'] === 'example.com';
        });
    }

    /**
     * Test markAsActive method.
     */
    public function test_mark_as_active(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'inactive',
        ]);

        $result = $domain->markAsActive();

        $this->assertTrue($result);
        $this->assertEquals('active', $domain->fresh()->status);

        Log::assertLogged('info', function ($message, $context) {
            return str_contains($message, 'Domain marked as active');
        });
    }

    /**
     * Test markAsInactive method.
     */
    public function test_mark_as_inactive(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);

        $result = $domain->markAsInactive();

        $this->assertTrue($result);
        $this->assertEquals('inactive', $domain->fresh()->status);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Domain marked as inactive');
        });
    }

    /**
     * Test markAsSuspended method.
     */
    public function test_mark_as_suspended(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);

        $result = $domain->markAsSuspended();

        $this->assertTrue($result);
        $this->assertEquals('suspended', $domain->fresh()->status);

        Log::assertLogged('warning', function ($message, $context) {
            return str_contains($message, 'Domain marked as suspended');
        });
    }

    /**
     * Test updateLastUsed method.
     */
    public function test_update_last_used(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
        ]);

        $result = $domain->updateLastUsed();

        $this->assertTrue($result);
        $this->assertNotNull($domain->fresh()->last_used_at);
    }

    /**
     * Test isActive method.
     */
    public function test_is_active(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $activeDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'active.com',
            'status' => 'active',
        ]);

        $inactiveDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'inactive.com',
            'status' => 'inactive',
        ]);

        $this->assertTrue($activeDomain->isActive());
        $this->assertFalse($inactiveDomain->isActive());
    }

    /**
     * Test isVerified method.
     */
    public function test_is_verified(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $verifiedDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'verified.com',
            'status' => 'active',
            'is_verified' => true,
        ]);

        $unverifiedDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'unverified.com',
            'status' => 'active',
            'is_verified' => false,
        ]);

        $this->assertTrue($verifiedDomain->isVerified());
        $this->assertFalse($unverifiedDomain->isVerified());
    }

    /**
     * Test isRecentlyUsed method.
     */
    public function test_is_recently_used(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $recentDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'recent.com',
            'status' => 'active',
            'last_used_at' => now()->subDays(5),
        ]);

        $oldDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'old.com',
            'status' => 'active',
            'last_used_at' => now()->subDays(50),
        ]);

        $neverUsedDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'never.com',
            'status' => 'active',
        ]);

        $this->assertTrue($recentDomain->isRecentlyUsed(30));
        $this->assertFalse($oldDomain->isRecentlyUsed(30));
        $this->assertFalse($neverUsedDomain->isRecentlyUsed(30));
    }

    /**
     * Test status badge class attribute.
     */
    public function test_status_badge_class_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $activeDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'active.com',
            'status' => 'active',
        ]);

        $inactiveDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'inactive.com',
            'status' => 'inactive',
        ]);

        $suspendedDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'suspended.com',
            'status' => 'suspended',
        ]);

        $pendingDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'pending.com',
            'status' => 'pending',
        ]);

        $this->assertEquals('badge-success', $activeDomain->status_badge_class);
        $this->assertEquals('badge-secondary', $inactiveDomain->status_badge_class);
        $this->assertEquals('badge-danger', $suspendedDomain->status_badge_class);
        $this->assertEquals('badge-warning', $pendingDomain->status_badge_class);
    }

    /**
     * Test status label attribute.
     */
    public function test_status_label_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $activeDomain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'active.com',
            'status' => 'active',
        ]);

        $this->assertEquals('Active', $activeDomain->status_label);
    }

    /**
     * Test days since last used attribute.
     */
    public function test_days_since_last_used_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
            'last_used_at' => now()->subDays(5),
        ]);

        $this->assertEquals(5, $domain->days_since_last_used);
    }

    /**
     * Test days since added attribute.
     */
    public function test_days_since_added_attribute(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
            'added_at' => now()->subDays(10),
        ]);

        $this->assertEquals(10, $domain->days_since_added);
    }

    /**
     * Test scopes.
     */
    public function test_scopes(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'active.com',
            'status' => 'active',
            'is_verified' => true,
            'last_used_at' => now()->subDays(5),
        ]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'inactive.com',
            'status' => 'inactive',
            'is_verified' => false,
        ]);

        $this->assertCount(1, LicenseDomain::active()->get());
        $this->assertCount(1, LicenseDomain::verified()->get());
        $this->assertCount(2, LicenseDomain::forLicense($license->id)->get());
        $this->assertCount(1, LicenseDomain::recentlyUsed(30)->get());
        $this->assertCount(1, LicenseDomain::byStatus('active')->get());
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

        $this->assertInstanceOf(License::class, $domain->license);
        $this->assertEquals($license->id, $domain->license->id);
    }

    /**
     * Test static methods.
     */
    public function test_static_methods(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'active.com',
            'status' => 'active',
            'is_verified' => true,
            'last_used_at' => now()->subDays(5),
        ]);

        LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'inactive.com',
            'status' => 'inactive',
            'is_verified' => false,
        ]);

        $forLicense = LicenseDomain::getForLicense($license->id);
        $activeForLicense = LicenseDomain::getActiveForLicense($license->id);
        $recentlyUsed = LicenseDomain::getRecentlyUsed(30, 10);
        $statistics = LicenseDomain::getStatistics();

        $this->assertCount(2, $forLicense);
        $this->assertCount(1, $activeForLicense);
        $this->assertCount(1, $recentlyUsed);
        $this->assertArrayHasKey('total', $statistics);
        $this->assertArrayHasKey('active', $statistics);
        $this->assertArrayHasKey('verified', $statistics);
        $this->assertEquals(2, $statistics['total']);
        $this->assertEquals(1, $statistics['active']);
        $this->assertEquals(1, $statistics['verified']);
    }

    /**
     * Test casts.
     */
    public function test_casts(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();
        $license = License::factory()->create(['user_id' => $user->id, 'product_id' => $product->id]);

        $domain = LicenseDomain::create([
            'license_id' => $license->id,
            'domain' => 'example.com',
            'status' => 'active',
            'is_verified' => '1',
        ]);

        $this->assertIsInt($domain->license_id);
        $this->assertIsBool($domain->is_verified);
    }
}
