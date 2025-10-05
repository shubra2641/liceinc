<?php

namespace Tests\Unit\Requests\Admin;

use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

/**
 * Test suite for UpdateSettingsRequest.
 *
 * Tests validation rules, authorization, and data preparation
 * for settings update requests.
 */
class UpdateSettingsRequestTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->customer = User::factory()->create();
        $this->customer->assignRole('customer');
    }

    /**
     * Test admin can authorize request.
     */
    public function test_admin_can_authorize_request(): void
    {
        $request = new UpdateSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $this->assertTrue($request->authorize());
    }

    /**
     * Test customer cannot authorize request.
     */
    public function test_customer_cannot_authorize_request(): void
    {
        $request = new UpdateSettingsRequest();
        $request->setUserResolver(fn () => $this->customer);

        $this->assertFalse($request->authorize());
    }

    /**
     * Test guest cannot authorize request.
     */
    public function test_guest_cannot_authorize_request(): void
    {
        $request = new UpdateSettingsRequest();
        $request->setUserResolver(fn () => null);

        $this->assertFalse($request->authorize());
    }

    /**
     * Test validation rules for required fields.
     */
    public function test_validation_rules_for_required_fields(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('site_name', $rules);
        $this->assertContains('required', $rules['site_name']);
        $this->assertContains('string', $rules['site_name']);
        $this->assertContains('max:255', $rules['site_name']);
    }

    /**
     * Test validation rules for optional fields.
     */
    public function test_validation_rules_for_optional_fields(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('site_description', $rules);
        $this->assertContains('nullable', $rules['site_description']);
        $this->assertContains('string', $rules['site_description']);
        $this->assertContains('max:500', $rules['site_description']);

        $this->assertArrayHasKey('support_email', $rules);
        $this->assertContains('nullable', $rules['support_email']);
        $this->assertContains('email', $rules['support_email']);
    }

    /**
     * Test validation rules for file uploads.
     */
    public function test_validation_rules_for_file_uploads(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('site_logo', $rules);
        $this->assertContains('nullable', $rules['site_logo']);
        $this->assertContains('mimes:jpeg,png,jpg,gif,svg,webp', $rules['site_logo']);
        $this->assertContains('max:5120', $rules['site_logo']);

        $this->assertArrayHasKey('seo_og_image', $rules);
        $this->assertContains('nullable', $rules['seo_og_image']);
        $this->assertContains('mimes:jpeg,png,jpg,gif,svg,webp', $rules['seo_og_image']);
        $this->assertContains('max:5120', $rules['seo_og_image']);
    }

    /**
     * Test validation rules for boolean fields.
     */
    public function test_validation_rules_for_boolean_fields(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $booleanFields = [
            'maintenance_mode',
            'envato_auth_enabled',
            'envato_oauth_enabled',
            'license_verify_envato',
            'license_fallback_internal',
            'license_cache_verification',
            'license_allow_offline',
            'license_allow_localhost',
            'license_allow_ip_addresses',
            'license_allow_wildcards',
            'license_validate_ssl',
            'license_auto_approve_subdomains',
            'license_auto_register_domains',
            'license_auto_suspend',
            'license_allow_expired_verification',
            'license_encrypt_data',
            'license_secure_tokens',
            'license_validate_signatures',
            'license_prevent_sharing',
            'license_detect_suspicious',
            'license_block_vpn',
            'license_require_https',
            'license_notify_verification',
            'license_notify_expiration',
            'license_notify_domain_change',
            'license_notify_suspicious',
            'license_use_slack',
            'license_enable_caching',
            'license_optimize_queries',
            'license_use_indexes',
            'license_compress_responses',
            'license_allow_test',
            'license_bypass_testing',
            'license_mock_envato',
            'license_generate_fake_data',
            'preloader_enabled',
            'logo_show_text',
            'enable_captcha',
            'enable_human_question',
        ];

        foreach ($booleanFields as $field) {
            $this->assertArrayHasKey($field, $rules);
            $this->assertContains('boolean', $rules[$field]);
        }
    }

    /**
     * Test validation rules for integer fields.
     */
    public function test_validation_rules_for_integer_fields(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $integerFields = [
            'avg_response_time' => ['min:1', 'max:168'],
            'license_max_attempts' => ['min:1'],
            'license_lockout_minutes' => ['min:1'],
            'license_cache_duration' => ['min:1', 'max:1440'],
            'license_grace_period' => ['min:0', 'max:30'],
            'license_max_domains' => ['min:1', 'max:100'],
            'license_domain_cooldown' => ['min:1', 'max:168'],
            'license_default_duration' => ['min:1', 'max:3650'],
            'license_support_duration' => ['min:1', 'max:3650'],
            'license_renewal_reminder' => ['min:1', 'max:365'],
            'license_expiration_grace' => ['min:0', 'max:30'],
            'preloader_duration' => ['min:500', 'max:10000'],
            'logo_width' => ['min:50', 'max:500'],
            'logo_height' => ['min:20', 'max:200'],
            'license_batch_size' => ['min:1', 'max:1000'],
        ];

        foreach ($integerFields as $field => $expectedRules) {
            $this->assertArrayHasKey($field, $rules);
            $this->assertContains('integer', $rules[$field]);

            foreach ($expectedRules as $expectedRule) {
                $this->assertContains($expectedRule, $rules[$field]);
            }
        }
    }

    /**
     * Test validation rules for string fields with max length.
     */
    public function test_validation_rules_for_string_fields_with_max_length(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $stringFields = [
            'support_phone' => 'max:20',
            'seo_site_title' => 'max:255',
            'seo_site_description' => 'max:500',
            'seo_kb_title' => 'max:255',
            'seo_kb_description' => 'max:500',
            'seo_tickets_title' => 'max:255',
            'seo_tickets_description' => 'max:500',
            'preloader_color' => 'max:7',
            'preloader_background_color' => 'max:7',
            'logo_text' => 'max:255',
            'logo_text_color' => 'max:7',
            'logo_text_font_size' => 'max:10',
            'captcha_site_key' => 'max:255',
            'captcha_secret_key' => 'max:255',
            'license_test_prefix' => 'max:10',
        ];

        foreach ($stringFields as $field => $expectedRule) {
            $this->assertArrayHasKey($field, $rules);
            $this->assertContains($expectedRule, $rules[$field]);
        }
    }

    /**
     * Test validation rules for license API token.
     */
    public function test_validation_rules_for_license_api_token(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('license_api_token', $rules);
        $this->assertContains('nullable', $rules['license_api_token']);
        $this->assertContains('string', $rules['license_api_token']);
        $this->assertContains('min:32', $rules['license_api_token']);
        $this->assertContains('max:128', $rules['license_api_token']);
    }

    /**
     * Test validation rules for cache driver.
     */
    public function test_validation_rules_for_cache_driver(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('license_cache_driver', $rules);
        $this->assertContains('nullable', $rules['license_cache_driver']);
        $this->assertContains('string', $rules['license_cache_driver']);
        $this->assertContains('in:file,database,redis,memcached', $rules['license_cache_driver']);
    }

    /**
     * Test validation rules for preloader type.
     */
    public function test_validation_rules_for_preloader_type(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('preloader_type', $rules);
        $this->assertContains('nullable', $rules['preloader_type']);
        $this->assertContains('string', $rules['preloader_type']);
        $this->assertContains('in:spinner,dots,bars,pulse,progress,custom', $rules['preloader_type']);
    }

    /**
     * Test validation rules for human questions.
     */
    public function test_validation_rules_for_human_questions(): void
    {
        $request = new UpdateSettingsRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('human_questions', $rules);
        $this->assertContains('nullable', $rules['human_questions']);

        $this->assertArrayHasKey('human_questions.*.question', $rules);
        $this->assertContains('required_with:human_questions', $rules['human_questions.*.question']);
        $this->assertContains('string', $rules['human_questions.*.question']);
        $this->assertContains('max:255', $rules['human_questions.*.question']);

        $this->assertArrayHasKey('human_questions.*.answer', $rules);
        $this->assertContains('required_with:human_questions', $rules['human_questions.*.answer']);
        $this->assertContains('string', $rules['human_questions.*.answer']);
        $this->assertContains('max:255', $rules['human_questions.*.answer']);
    }

    /**
     * Test custom error messages.
     */
    public function test_custom_error_messages(): void
    {
        $request = new UpdateSettingsRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('site_name.required', $messages);
        $this->assertEquals('Site name is required', $messages['site_name.required']);

        $this->assertArrayHasKey('site_name.max', $messages);
        $this->assertEquals('Site name must be less than 255 characters', $messages['site_name.max']);

        $this->assertArrayHasKey('support_email.email', $messages);
        $this->assertEquals('Support email must be a valid email address', $messages['support_email.email']);

        $this->assertArrayHasKey('license_api_token.min', $messages);
        $this->assertEquals('License API token must be at least 32 characters', $messages['license_api_token.min']);
    }

    /**
     * Test custom attributes.
     */
    public function test_custom_attributes(): void
    {
        $request = new UpdateSettingsRequest();
        $attributes = $request->attributes();

        $this->assertArrayHasKey('site_name', $attributes);
        $this->assertEquals('Site Name', $attributes['site_name']);

        $this->assertArrayHasKey('support_email', $attributes);
        $this->assertEquals('Support Email', $attributes['support_email']);

        $this->assertArrayHasKey('license_api_token', $attributes);
        $this->assertEquals('License API Token', $attributes['license_api_token']);
    }

    /**
     * Test data preparation trims strings.
     */
    public function test_data_preparation_trims_strings(): void
    {
        $request = new UpdateSettingsRequest();
        $request->merge([
            'site_name' => '  Test Site  ',
            'site_description' => '  Test Description  ',
            'support_email' => '  test@example.com  ',
        ]);

        $request->prepareForValidation();

        $this->assertEquals('Test Site', $request->input('site_name'));
        $this->assertEquals('Test Description', $request->input('site_description'));
        $this->assertEquals('test@example.com', $request->input('support_email'));
    }

    /**
     * Test data preparation converts boolean fields.
     */
    public function test_data_preparation_converts_boolean_fields(): void
    {
        $request = new UpdateSettingsRequest();
        $request->merge([
            'maintenance_mode' => '1',
            'envato_auth_enabled' => '0',
            'preloader_enabled' => 'true',
            'logo_show_text' => 'false',
        ]);

        $request->prepareForValidation();

        $this->assertTrue($request->input('maintenance_mode'));
        $this->assertFalse($request->input('envato_auth_enabled'));
        $this->assertTrue($request->input('preloader_enabled'));
        $this->assertFalse($request->input('logo_show_text'));
    }

    /**
     * Test validation with valid data.
     */
    public function test_validation_with_valid_data(): void
    {
        $request = new UpdateSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $validData = [
            'site_name' => 'Test Site',
            'site_description' => 'Test Description',
            'support_email' => 'test@example.com',
            'avg_response_time' => 24,
            'support_phone' => '+1234567890',
            'timezone' => 'UTC',
            'maintenance_mode' => true,
            'envato_auth_enabled' => false,
            'license_max_attempts' => 5,
            'license_lockout_minutes' => 15,
            'preloader_enabled' => true,
            'preloader_type' => 'spinner',
            'preloader_color' => '#ff0000',
            'preloader_duration' => 1000,
            'logo_width' => 200,
            'logo_height' => 80,
            'logo_show_text' => true,
            'logo_text' => 'Test Logo',
            'enable_captcha' => false,
            'enable_human_question' => true,
            'human_questions' => [
                [
                    'question' => 'What is 2 + 2?',
                    'answer' => '4',
                ],
            ],
        ];

        $validator = Validator::make($validData, $request->rules(), $request->messages());
        $this->assertTrue($validator->passes());
    }

    /**
     * Test validation with invalid data.
     */
    public function test_validation_with_invalid_data(): void
    {
        $request = new UpdateSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $invalidData = [
            'site_name' => '', // Required field missing
            'support_email' => 'invalid-email', // Invalid email
            'avg_response_time' => 'not-a-number', // Invalid number
            'preloader_duration' => 100, // Too small
            'logo_width' => 10, // Too small
            'license_api_token' => 'short', // Too short
            'license_cache_driver' => 'invalid-driver', // Invalid option
            'preloader_type' => 'invalid-type', // Invalid option
        ];

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertFalse($validator->passes());

        $errors = $validator->errors();
        $this->assertTrue($errors->has('site_name'));
        $this->assertTrue($errors->has('support_email'));
        $this->assertTrue($errors->has('avg_response_time'));
        $this->assertTrue($errors->has('preloader_duration'));
        $this->assertTrue($errors->has('logo_width'));
        $this->assertTrue($errors->has('license_api_token'));
        $this->assertTrue($errors->has('license_cache_driver'));
        $this->assertTrue($errors->has('preloader_type'));
    }

    /**
     * Test validation with invalid human questions.
     */
    public function test_validation_with_invalid_human_questions(): void
    {
        $request = new UpdateSettingsRequest();
        $request->setUserResolver(fn () => $this->admin);

        $invalidData = [
            'site_name' => 'Test Site',
            'enable_human_question' => true,
            'human_questions' => [
                [
                    'question' => '', // Empty question
                    'answer' => '4',
                ],
                [
                    'question' => 'What color is the sky?',
                    'answer' => '', // Empty answer
                ],
            ],
        ];

        $validator = Validator::make($invalidData, $request->rules(), $request->messages());
        $this->assertFalse($validator->passes());

        $errors = $validator->errors();
        $this->assertTrue($errors->has('human_questions.*.question'));
        $this->assertTrue($errors->has('human_questions.*.answer'));
    }
}
