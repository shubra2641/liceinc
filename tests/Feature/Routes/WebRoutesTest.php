<?php

namespace Tests\Feature\Routes;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * Web Routes Feature Test.
 */
class WebRoutesTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $adminUser;

    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->regularUser = User::factory()->create([
            'role' => 'user',
        ]);
    }

    /**
     * Test public routes accessibility.
     */
    public function test_public_routes_are_accessible(): void
    {
        // Home page
        $response = $this->get(route('home'));
        $response->assertStatus(200);

        // License status page
        $response = $this->get(route('license.status'));
        $response->assertStatus(200);

        // Knowledge base
        $response = $this->get(route('kb.index'));
        $response->assertStatus(200);

        // Public products
        $response = $this->get(route('public.products.index'));
        $response->assertStatus(200);

        // Support ticket creation
        $response = $this->get(route('support.tickets.create'));
        $response->assertStatus(200);
    }

    /**
     * Test language switcher with valid locale.
     */
    public function test_language_switcher_with_valid_locale(): void
    {
        $response = $this->get(route('lang.switch', ['locale' => 'en']));
        $response->assertRedirect();
    }

    /**
     * Test language switcher with invalid locale.
     */
    public function test_language_switcher_with_invalid_locale(): void
    {
        $response = $this->get(route('lang.switch', ['locale' => 'invalid']));
        $response->assertRedirect();
    }

    /**
     * Test license status check with rate limiting.
     */
    public function test_license_status_check_with_rate_limiting(): void
    {
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 12; $i++) {
            $response = $this->post(route('license.status.check'), [
                'license_key' => 'test-license-key',
                'domain' => 'example.com',
            ]);

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test KB search with rate limiting.
     */
    public function test_kb_search_with_rate_limiting(): void
    {
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 22; $i++) {
            $response = $this->get(route('kb.search', ['q' => 'test']));

            if ($i < 20) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test support ticket creation with rate limiting.
     */
    public function test_support_ticket_creation_with_rate_limiting(): void
    {
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 7; $i++) {
            $response = $this->post(route('support.tickets.store'), [
                'subject' => 'Test Subject',
                'message' => 'Test Message',
                'email' => 'test@example.com',
            ]);

            if ($i < 5) {
                $response->assertStatus(302); // Redirect after creation
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test purchase code verification with rate limiting.
     */
    public function test_purchase_code_verification_with_rate_limiting(): void
    {
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 12; $i++) {
            $response = $this->get(route('verify-purchase-code', ['purchaseCode' => 'test-code']));

            if ($i < 10) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test authenticated user routes.
     */
    public function test_authenticated_user_routes(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->regularUser)
            ->get(route('user.tickets.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->regularUser)
            ->get(route('user.licenses.index'));
        $response->assertStatus(200);
    }

    /**
     * Test admin routes accessibility.
     */
    public function test_admin_routes_accessibility(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.products.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    /**
     * Test admin routes with rate limiting.
     */
    public function test_admin_routes_with_rate_limiting(): void
    {
        // Test auto-update check rate limiting
        for ($i = 0; $i < 7; $i++) {
            $response = $this->actingAs($this->adminUser)
                ->post(route('admin.auto-update.check'), [
                    'license_key' => 'test-license',
                    'product_slug' => 'test-product',
                    'domain' => 'example.com',
                    'current_version' => '1.0.0',
                ]);

            if ($i < 5) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test payment routes with validation.
     */
    public function test_payment_routes_with_validation(): void
    {
        // Test with valid product ID
        $response = $this->get(route('payment.gateways', ['product' => '1']));
        $response->assertStatus(200);

        // Test with invalid product ID
        $response = $this->get(route('payment.gateways', ['product' => 'invalid']));
        $response->assertStatus(404);
    }

    /**
     * Test route parameter validation.
     */
    public function test_route_parameter_validation(): void
    {
        // Test KB category with valid slug
        $response = $this->get(route('kb.category', ['slug' => 'valid-slug']));
        $response->assertStatus(200);

        // Test KB category with invalid slug
        $response = $this->get(route('kb.category', ['slug' => 'Invalid_Slug!']));
        $response->assertStatus(404);

        // Test product with valid slug
        $response = $this->get(route('public.products.show', ['slug' => 'valid-product']));
        $response->assertStatus(200);

        // Test product with invalid slug
        $response = $this->get(route('public.products.show', ['slug' => 'Invalid_Product!']));
        $response->assertStatus(404);
    }

    /**
     * Test unauthorized access to admin routes.
     */
    public function test_unauthorized_access_to_admin_routes(): void
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.dashboard'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.products.index'));
        $response->assertStatus(403);
    }

    /**
     * Test guest access to protected routes.
     */
    public function test_guest_access_to_protected_routes(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * Test dashboard AJAX endpoints.
     */
    public function test_dashboard_ajax_endpoints(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.stats'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.system-overview'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.license-distribution'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.revenue'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.activity-timeline'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.api-requests'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.api-performance'));
        $response->assertStatus(200);
    }

    /**
     * Test cache clearing functionality.
     */
    public function test_cache_clearing_functionality(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.clear-cache'));
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /**
     * Test product management routes with validation.
     */
    public function test_product_management_routes_with_validation(): void
    {
        // Test with valid product ID
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.products.data', ['product' => '1']));
        $response->assertStatus(200);

        // Test with invalid product ID
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.products.data', ['product' => 'invalid']));
        $response->assertStatus(404);
    }

    /**
     * Test product update routes with validation.
     */
    public function test_product_update_routes_with_validation(): void
    {
        // Test with valid product update ID
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.product-updates.toggle-status', ['productUpdate' => '1']), [
                'is_active' => true,
            ]);
        $response->assertStatus(200);

        // Test with invalid product update ID
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.product-updates.toggle-status', ['productUpdate' => 'invalid']), [
                'is_active' => true,
            ]);
        $response->assertStatus(404);
    }

    /**
     * Test payment settings routes with rate limiting.
     */
    public function test_payment_settings_routes_with_rate_limiting(): void
    {
        // Test payment connection with rate limiting
        for ($i = 0; $i < 7; $i++) {
            $response = $this->actingAs($this->adminUser)
                ->post(route('admin.payment-settings.test'), [
                    'gateway' => 'paypal',
                    'credentials' => [
                        'client_id' => 'test',
                        'client_secret' => 'test',
                    ],
                ]);

            if ($i < 5) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }

    /**
     * Test file download routes with security middleware.
     */
    public function test_file_download_routes_with_security_middleware(): void
    {
        // Test product file download with valid ID
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.product-files.download', ['file' => '1']));
        $response->assertStatus(200);

        // Test product file download with invalid ID
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.product-files.download', ['file' => 'invalid']));
        $response->assertStatus(404);
    }

    /**
     * Test programming language routes with validation.
     */
    public function test_programming_language_routes_with_validation(): void
    {
        // Test with valid language slug
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.programming-languages.license-file', ['language' => 'php']));
        $response->assertStatus(200);

        // Test with invalid language slug
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.programming-languages.license-file', ['language' => 'Invalid_Language!']));
        $response->assertStatus(404);
    }

    /**
     * Test license verification guide routes.
     */
    public function test_license_verification_guide_routes(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-guide.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-guide.api-documentation'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-guide.integration-examples'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-guide.troubleshooting'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-guide.security-best-practices'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-guide.faq'));
        $response->assertStatus(200);
    }

    /**
     * Test profile management routes.
     */
    public function test_profile_management_routes(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.profile.edit'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->patch(route('admin.profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);
        $response->assertRedirect();

        $response = $this->actingAs($this->adminUser)
            ->patch(route('admin.profile.update-password'), [
                'current_password' => 'password',
                'password' => 'newpassword',
                'password_confirmation' => 'newpassword',
            ]);
        $response->assertRedirect();
    }

    /**
     * Test email template routes.
     */
    public function test_email_template_routes(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.email-templates.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.email-templates.create'));
        $response->assertStatus(200);
    }

    /**
     * Test reports and analytics routes.
     */
    public function test_reports_and_analytics_routes(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.license-data'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.reports.api-status-data'));
        $response->assertStatus(200);
    }

    /**
     * Test license verification logs routes.
     */
    public function test_license_verification_logs_routes(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-logs.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-logs.stats'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.license-verification-logs.suspicious-activity'));
        $response->assertStatus(200);
    }

    /**
     * Test OAuth routes.
     */
    public function test_oauth_routes(): void
    {
        $response = $this->get(route('auth.envato'));
        $response->assertRedirect();

        $response = $this->get(route('auth.envato.callback'));
        $response->assertStatus(200);
    }

    /**
     * Test installation routes.
     */
    public function test_installation_routes(): void
    {
        $response = $this->get(route('install.welcome'));
        $response->assertStatus(200);

        $response = $this->get(route('install.license'));
        $response->assertStatus(200);

        $response = $this->get(route('install.requirements'));
        $response->assertStatus(200);
    }
}
