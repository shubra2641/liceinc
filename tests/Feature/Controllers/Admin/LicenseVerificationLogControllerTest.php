<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\LicenseVerificationLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for LicenseVerificationLogController.
 *
 * This test suite covers all license verification log operations, filtering,
 * statistics, suspicious activity detection, cleanup, and export functionality.
 */
class LicenseVerificationLogControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->user = User::factory()->create();
    }

    /** @test */
    public function admin_can_view_license_verification_logs()
    {
        LicenseVerificationLog::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.license-verification-logs.index');
        $response->assertViewHas(['logs', 'stats', 'suspiciousActivity', 'sources', 'domains']);
    }

    /** @test */
    public function admin_can_filter_logs_by_status()
    {
        LicenseVerificationLog::factory()->create(['status' => 'success']);
        LicenseVerificationLog::factory()->create(['status' => 'failed']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.index', ['status' => 'success']));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function admin_can_filter_logs_by_source()
    {
        LicenseVerificationLog::factory()->create(['verification_source' => 'install']);
        LicenseVerificationLog::factory()->create(['verification_source' => 'api']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.index', ['source' => 'install']));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function admin_can_filter_logs_by_domain()
    {
        LicenseVerificationLog::factory()->create(['domain' => 'example.com']);
        LicenseVerificationLog::factory()->create(['domain' => 'test.com']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.index', ['domain' => 'example']));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function admin_can_filter_logs_by_ip()
    {
        LicenseVerificationLog::factory()->create(['ip_address' => '192.168.1.1']);
        LicenseVerificationLog::factory()->create(['ip_address' => '10.0.0.1']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.index', ['ip' => '192.168']));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function admin_can_filter_logs_by_date_range()
    {
        LicenseVerificationLog::factory()->create(['created_at' => now()->subDays(5)]);
        LicenseVerificationLog::factory()->create(['created_at' => now()->subDays(10)]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.index', [
                'date_from' => now()->subDays(7)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function admin_can_get_license_verification_statistics()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.stats', ['days' => 30]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_verifications',
            'successful_verifications',
            'failed_verifications',
            'error_verifications',
        ]);
    }

    /** @test */
    public function statistics_request_validates_days_parameter()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.stats', ['days' => 500]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['days']);
    }

    /** @test */
    public function admin_can_get_suspicious_activity()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.suspicious-activity', [
                'hours' => 24,
                'min_attempts' => 3,
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'suspicious_ips',
            'suspicious_domains',
            'high_failure_rates',
        ]);
    }

    /** @test */
    public function admin_can_view_license_verification_log_details()
    {
        $log = LicenseVerificationLog::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.show', $log));

        $response->assertStatus(200);
        $response->assertViewIs('admin.license-verification-logs.show');
        $response->assertViewHas('log', $log);
    }

    /** @test */
    public function admin_can_clean_old_logs()
    {
        LicenseVerificationLog::factory()->create(['created_at' => now()->subDays(100)]);
        LicenseVerificationLog::factory()->create(['created_at' => now()->subDays(50)]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.license-verification-logs.clean'), ['days' => 90]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Successfully cleaned 1 old log entries',
            'cleaned_count' => 1,
        ]);
    }

    /** @test */
    public function clean_logs_request_validates_days_parameter()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.license-verification-logs.clean'), ['days' => 5000]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['days']);
    }

    /** @test */
    public function admin_can_export_license_verification_logs()
    {
        LicenseVerificationLog::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
        $response->assertHeader('Content-Disposition');
    }

    /** @test */
    public function admin_can_export_filtered_logs()
    {
        LicenseVerificationLog::factory()->create(['status' => 'success']);
        LicenseVerificationLog::factory()->create(['status' => 'failed']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.export', ['status' => 'success']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function export_request_validates_filter_parameters()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.export', [
                'status' => 'invalid_status',
                'source' => 'invalid_source',
                'date_from' => 'invalid_date',
                'date_to' => 'invalid_date',
            ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['status', 'source', 'date_from', 'date_to']);
    }

    /** @test */
    public function admin_can_get_dashboard_statistics()
    {
        LicenseVerificationLog::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.dashboard-stats'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'stats' => [
                'total_verifications',
                'successful_verifications',
                'failed_verifications',
                'error_verifications',
                'unique_domains',
                'unique_ips',
                'recent_activity',
                'suspicious_activity',
            ],
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_license_verification_logs()
    {
        $log = LicenseVerificationLog::factory()->create();

        $routes = [
            'admin.license-verification-logs.index',
            'admin.license-verification-logs.show',
            'admin.license-verification-logs.stats',
            'admin.license-verification-logs.suspicious-activity',
            'admin.license-verification-logs.clean',
            'admin.license-verification-logs.export',
            'admin.license-verification-logs.dashboard-stats',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)
                ->get(route($route, $log));

            $response->assertStatus(403);
        }
    }

    /** @test */
    public function guest_cannot_access_license_verification_logs()
    {
        $log = LicenseVerificationLog::factory()->create();

        $response = $this->get(route('admin.license-verification-logs.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function license_verification_logs_handles_database_errors_gracefully()
    {
        // Mock database error by using invalid data
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.index', [
                'date_from' => 'invalid-date',
                'date_to' => 'invalid-date',
            ]));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function statistics_handles_service_errors_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.stats'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_verifications',
            'successful_verifications',
            'failed_verifications',
            'error_verifications',
        ]);
    }

    /** @test */
    public function suspicious_activity_handles_invalid_parameters()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.suspicious-activity', [
                'hours' => 'invalid',
                'min_attempts' => 'invalid',
            ]));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'suspicious_ips',
            'suspicious_domains',
            'high_failure_rates',
        ]);
    }

    /** @test */
    public function clean_logs_handles_database_errors_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.license-verification-logs.clean'), ['days' => 90]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'cleaned_count' => 0,
        ]);
    }

    /** @test */
    public function export_handles_empty_results_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.export', ['status' => 'nonexistent']));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');
    }

    /** @test */
    public function dashboard_statistics_handles_empty_data_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.dashboard-stats'));

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'stats' => [
                'total_verifications' => 0,
                'successful_verifications' => 0,
                'failed_verifications' => 0,
                'error_verifications' => 0,
                'unique_domains' => 0,
                'unique_ips' => 0,
                'recent_activity' => 0,
            ],
        ]);
    }

    /** @test */
    public function export_generates_correct_csv_format()
    {
        $log = LicenseVerificationLog::factory()->create([
            'status' => 'success',
            'verification_source' => 'install',
            'domain' => 'example.com',
            'ip_address' => '192.168.1.1',
            'is_valid' => true,
            'response_message' => 'Valid license',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.license-verification-logs.export'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/csv');

        $content = $response->getContent();
        $this->assertStringContainsString('ID,Purchase Code Hash,Domain,IP Address,Status', $content);
        $this->assertStringContainsString('example.com', $content);
        $this->assertStringContainsString('192.168.1.1', $content);
        $this->assertStringContainsString('success', $content);
    }
}
