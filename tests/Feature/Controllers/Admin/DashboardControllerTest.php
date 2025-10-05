<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\Invoice;
use App\Models\KbArticle;
use App\Models\License;
use App\Models\LicenseLog;
use App\Models\Product;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/**
 * DashboardController Feature Test.
 */
class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'role' => 'admin',
        ]);
    }

    /**
     * Test dashboard index page loads successfully.
     */
    public function test_dashboard_index_loads_successfully(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas(['stats', 'latestTickets', 'latestLicenses']);
    }

    /**
     * Test dashboard index with fallback data on error.
     */
    public function test_dashboard_index_with_fallback_data(): void
    {
        // Mock database error
        $this->mock(Product::class, function ($mock) {
            $mock->shouldReceive('count')->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertViewHas('error', 'Failed to load dashboard data');
    }

    /**
     * Test get system overview data.
     */
    public function test_get_system_overview_data(): void
    {
        // Create test data
        Product::factory()->count(3)->create();
        License::factory()->count(5)->create(['status' => 'active']);
        License::factory()->count(2)->create(['status' => 'expired']);
        Ticket::factory()->count(3)->create(['status' => 'open']);
        Ticket::factory()->count(2)->create(['status' => 'pending']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.system-overview'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels' => ['Active Licenses', 'Expired Licenses', 'Pending Requests', 'Total Products'],
            'data' => [5, 2, 5, 3],
        ]);
    }

    /**
     * Test get system overview data with fallback on error.
     */
    public function test_get_system_overview_data_with_fallback(): void
    {
        // Mock database error
        $this->mock(License::class, function ($mock) {
            $mock->shouldReceive('where->count')->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.system-overview'));

        $response->assertStatus(200);
        $response->assertJson([
            'labels' => ['Active Licenses', 'Expired Licenses', 'Pending Requests', 'Total Products'],
            'data' => [0, 0, 0, 0],
        ]);
    }

    /**
     * Test get license distribution data.
     */
    public function test_get_license_distribution_data(): void
    {
        // Create test data
        License::factory()->count(4)->create(['license_type' => 'regular']);
        License::factory()->count(3)->create(['license_type' => 'extended']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.license-distribution'));

        $response->assertStatus(200);
        $response->assertJson([
            'labels' => ['Regular', 'Extended'],
            'data' => [4, 3],
        ]);
    }

    /**
     * Test get license distribution data with fallback on error.
     */
    public function test_get_license_distribution_data_with_fallback(): void
    {
        // Mock database error
        $this->mock(License::class, function ($mock) {
            $mock->shouldReceive('where->count')->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.license-distribution'));

        $response->assertStatus(200);
        $response->assertJson([
            'labels' => ['Regular', 'Extended'],
            'data' => [0, 0],
        ]);
    }

    /**
     * Test get revenue data with monthly period.
     */
    public function test_get_revenue_data_monthly(): void
    {
        $year = date('Y');

        // Create test data for current year
        $product = Product::factory()->create(['price' => 100]);
        License::factory()->create([
            'product_id' => $product->id,
            'created_at' => Carbon::create($year, 6, 15), // June
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.revenue', ['period' => 'monthly', 'year' => $year]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'data',
        ]);

        $data = $response->json();
        $this->assertCount(12, $data['labels']);
        $this->assertCount(12, $data['data']);
    }

    /**
     * Test get revenue data with quarterly period.
     */
    public function test_get_revenue_data_quarterly(): void
    {
        $year = date('Y');

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.revenue', ['period' => 'quarterly', 'year' => $year]));

        $response->assertStatus(200);
        $response->assertJson([
            'labels' => ['Q1', 'Q2', 'Q3', 'Q4'],
        ]);

        $data = $response->json();
        $this->assertCount(4, $data['data']);
    }

    /**
     * Test get revenue data with yearly period.
     */
    public function test_get_revenue_data_yearly(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.revenue', ['period' => 'yearly']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'data',
        ]);

        $data = $response->json();
        $this->assertCount(5, $data['labels']); // Last 5 years
        $this->assertCount(5, $data['data']);
    }

    /**
     * Test get revenue data with fallback on error.
     */
    public function test_get_revenue_data_with_fallback(): void
    {
        // Mock database error
        $this->mock(License::class, function ($mock) {
            $mock->shouldReceive('join->whereBetween->sum')->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.revenue', ['period' => 'monthly']));

        $response->assertStatus(200);
        $response->assertJson([
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [0, 0, 0, 0, 0, 0],
        ]);
    }

    /**
     * Test get activity timeline data.
     */
    public function test_get_activity_timeline_data(): void
    {
        $today = Carbon::today();

        // Create test data for today
        Ticket::factory()->create(['created_at' => $today]);
        License::factory()->create(['created_at' => $today]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.activity-timeline'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'data',
        ]);

        $data = $response->json();
        $this->assertCount(7, $data['labels']); // Last 7 days
        $this->assertCount(7, $data['data']);
    }

    /**
     * Test get activity timeline data with fallback on error.
     */
    public function test_get_activity_timeline_data_with_fallback(): void
    {
        // Mock database error
        $this->mock(Ticket::class, function ($mock) {
            $mock->shouldReceive('whereBetween->count')->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.activity-timeline'));

        $response->assertStatus(200);
        $response->assertJson([
            'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            'data' => [0, 0, 0, 0, 0, 0, 0],
        ]);
    }

    /**
     * Test get dashboard stats.
     */
    public function test_get_dashboard_stats(): void
    {
        // Create test data
        Product::factory()->count(3)->create();
        User::factory()->count(5)->create();
        License::factory()->count(4)->create(['status' => 'active']);
        Ticket::factory()->count(2)->create(['status' => 'open']);
        KbArticle::factory()->count(6)->create();
        Invoice::factory()->count(3)->create(['status' => 'paid', 'amount' => 100]);
        Invoice::factory()->count(2)->create(['status' => 'pending', 'amount' => 50]);
        LicenseLog::factory()->count(10)->create(['status' => 'success']);
        LicenseLog::factory()->count(2)->create(['status' => 'failed']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.stats'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'products',
            'customers',
            'licenses_active',
            'tickets_open',
            'kb_articles',
            'invoices_count',
            'invoices_total_amount',
            'invoices_paid_amount',
            'invoices_paid_count',
            'invoices_due_soon_amount',
            'invoices_unpaid_amount',
            'invoices_cancelled_count',
            'invoices_cancelled_amount',
            'api_requests_today',
            'api_requests_this_month',
            'api_success_rate',
            'api_errors_today',
            'api_errors_this_month',
        ]);

        $data = $response->json();
        $this->assertEquals(3, $data['products']);
        $this->assertEquals(5, $data['customers']);
        $this->assertEquals(4, $data['licenses_active']);
        $this->assertEquals(2, $data['tickets_open']);
        $this->assertEquals(6, $data['kb_articles']);
        $this->assertEquals(5, $data['invoices_count']);
        $this->assertEquals(400, $data['invoices_total_amount']);
        $this->assertEquals(300, $data['invoices_paid_amount']);
        $this->assertEquals(3, $data['invoices_paid_count']);
        $this->assertEquals(100, $data['invoices_unpaid_amount']);
    }

    /**
     * Test get dashboard stats with fallback on error.
     */
    public function test_get_dashboard_stats_with_fallback(): void
    {
        // Mock database error
        $this->mock(Product::class, function ($mock) {
            $mock->shouldReceive('count')->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.stats'));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(0, $data['products']);
        $this->assertEquals(0, $data['customers']);
        $this->assertEquals(0, $data['licenses_active']);
    }

    /**
     * Test get API requests data with daily period.
     */
    public function test_get_api_requests_data_daily(): void
    {
        $today = Carbon::today();

        // Create test data
        LicenseLog::factory()->create([
            'created_at' => $today,
            'status' => 'success',
        ]);
        LicenseLog::factory()->create([
            'created_at' => $today,
            'status' => 'failed',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.api-requests', ['period' => 'daily', 'days' => 7]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'datasets' => [
                [
                    'label' => 'Total Requests',
                    'data',
                    'borderColor',
                    'backgroundColor',
                    'fill',
                ],
                [
                    'label' => 'Successful',
                    'data',
                    'borderColor',
                    'backgroundColor',
                    'fill',
                ],
                [
                    'label' => 'Failed',
                    'data',
                    'borderColor',
                    'backgroundColor',
                    'fill',
                ],
            ],
        ]);

        $data = $response->json();
        $this->assertCount(7, $data['labels']);
        $this->assertCount(3, $data['datasets']);
    }

    /**
     * Test get API requests data with hourly period.
     */
    public function test_get_api_requests_data_hourly(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.api-requests', ['period' => 'hourly']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'datasets',
        ]);

        $data = $response->json();
        $this->assertCount(24, $data['labels']); // Last 24 hours
    }

    /**
     * Test get API requests data with fallback on error.
     */
    public function test_get_api_requests_data_with_fallback(): void
    {
        // Mock database error
        $this->mock(LicenseLog::class, function ($mock) {
            $mock->shouldReceive('whereBetween->count')->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.api-requests'));

        $response->assertStatus(200);
        $response->assertJson([
            'labels' => [],
            'datasets' => [],
        ]);
    }

    /**
     * Test get API performance data.
     */
    public function test_get_api_performance_data(): void
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        // Create test data
        LicenseLog::factory()->create([
            'created_at' => $today,
            'status' => 'success',
            'domain' => 'example.com',
        ]);
        LicenseLog::factory()->create([
            'created_at' => $today,
            'status' => 'failed',
            'domain' => 'test.com',
        ]);
        LicenseLog::factory()->create([
            'created_at' => $yesterday,
            'status' => 'success',
            'domain' => 'example.com',
        ]);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.api-performance'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'today' => [
                'total',
                'success',
                'failed',
                'success_rate',
            ],
            'yesterday' => [
                'total',
                'success',
                'failed',
                'success_rate',
            ],
            'top_domains',
        ]);

        $data = $response->json();
        $this->assertEquals(2, $data['today']['total']);
        $this->assertEquals(1, $data['today']['success']);
        $this->assertEquals(1, $data['today']['failed']);
        $this->assertEquals(50.0, $data['today']['success_rate']);
    }

    /**
     * Test get API performance data with fallback on error.
     */
    public function test_get_api_performance_data_with_fallback(): void
    {
        // Mock database error
        $this->mock(LicenseLog::class, function ($mock) {
            $mock->shouldReceive('whereDate->count')->andThrow(new \Exception('Database error'));
        });

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.api-performance'));

        $response->assertStatus(200);
        $response->assertJson([
            'today' => ['total' => 0, 'success' => 0, 'failed' => 0, 'success_rate' => 0],
            'yesterday' => ['total' => 0, 'success' => 0, 'failed' => 0, 'success_rate' => 0],
            'top_domains' => [],
        ]);
    }

    /**
     * Test clear cache functionality.
     */
    public function test_clear_cache(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.dashboard.clear-cache'));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'All caches cleared successfully!');
    }

    /**
     * Test clear cache with error handling.
     */
    public function test_clear_cache_with_error(): void
    {
        // Mock Artisan call to throw exception
        $this->mock(\Illuminate\Support\Facades\Artisan::class, function ($mock) {
            $mock->shouldReceive('call')->andThrow(new \Exception('Cache clear failed'));
        });

        $response = $this->actingAs($this->adminUser)
            ->post(route('admin.dashboard.clear-cache'));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Failed to clear caches. Please try again.');
    }

    /**
     * Test unauthorized access to dashboard.
     */
    public function test_unauthorized_access_to_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    /**
     * Test guest access to dashboard.
     */
    public function test_guest_access_to_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /**
     * Test dashboard with large dataset.
     */
    public function test_dashboard_with_large_dataset(): void
    {
        // Create large dataset
        Product::factory()->count(100)->create();
        User::factory()->count(500)->create();
        License::factory()->count(200)->create(['status' => 'active']);
        Ticket::factory()->count(50)->create(['status' => 'open']);
        KbArticle::factory()->count(75)->create();
        Invoice::factory()->count(300)->create(['status' => 'paid', 'amount' => 100]);
        LicenseLog::factory()->count(1000)->create(['status' => 'success']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /**
     * Test API success rate calculation.
     */
    public function test_api_success_rate_calculation(): void
    {
        // Create test data
        LicenseLog::factory()->count(8)->create(['status' => 'success']);
        LicenseLog::factory()->count(2)->create(['status' => 'failed']);

        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.stats'));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(80.0, $data['api_success_rate']); // 8/10 * 100
    }

    /**
     * Test API success rate with zero requests.
     */
    public function test_api_success_rate_with_zero_requests(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('admin.dashboard.stats'));

        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(0, $data['api_success_rate']);
    }
}
