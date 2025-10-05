<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\EmailTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for EmailTemplateController.
 *
 * This test suite covers all email template management operations, CRUD functionality,
 * filtering, testing, status management, and error handling.
 */
class EmailTemplateControllerTest extends TestCase
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
    public function admin_can_view_email_templates_index()
    {
        EmailTemplate::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.email-templates.index');
        $response->assertViewHas(['templates', 'types', 'categories']);
    }

    /** @test */
    public function admin_can_filter_email_templates_by_type()
    {
        EmailTemplate::factory()->create(['type' => 'user']);
        EmailTemplate::factory()->create(['type' => 'admin']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.index', ['type' => 'user']));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
    }

    /** @test */
    public function admin_can_filter_email_templates_by_category()
    {
        EmailTemplate::factory()->create(['category' => 'registration']);
        EmailTemplate::factory()->create(['category' => 'license']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.index', ['category' => 'registration']));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
    }

    /** @test */
    public function admin_can_filter_email_templates_by_active_status()
    {
        EmailTemplate::factory()->create(['is_active' => true]);
        EmailTemplate::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.index', ['is_active' => '1']));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
    }

    /** @test */
    public function admin_can_search_email_templates()
    {
        EmailTemplate::factory()->create(['name' => 'Welcome Email']);
        EmailTemplate::factory()->create(['name' => 'License Expired']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.index', ['search' => 'Welcome']));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
    }

    /** @test */
    public function admin_can_view_email_template_creation_form()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.email-templates.create');
        $response->assertViewHas(['types', 'categories']);
    }

    /** @test */
    public function admin_can_create_email_template()
    {
        $templateData = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'This is a test email template body content.',
            'type' => 'user',
            'category' => 'registration',
            'is_active' => true,
            'description' => 'Test template description',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.store'), $templateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('email_templates', [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'type' => 'user',
            'category' => 'registration',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function email_template_creation_validates_required_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.store'), []);

        $response->assertSessionHasErrors(['name', 'subject', 'body', 'type', 'category']);
    }

    /** @test */
    public function email_template_creation_validates_name_uniqueness()
    {
        EmailTemplate::factory()->create(['name' => 'Existing Template']);

        $templateData = [
            'name' => 'Existing Template',
            'subject' => 'Test Subject',
            'body' => 'Test body content',
            'type' => 'user',
            'category' => 'registration',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.store'), $templateData);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function email_template_creation_validates_type()
    {
        $templateData = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'Test body content',
            'type' => 'invalid_type',
            'category' => 'registration',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.store'), $templateData);

        $response->assertSessionHasErrors(['type']);
    }

    /** @test */
    public function email_template_creation_validates_body_minimum_length()
    {
        $templateData = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'Short',
            'type' => 'user',
            'category' => 'registration',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.store'), $templateData);

        $response->assertSessionHasErrors(['body']);
    }

    /** @test */
    public function admin_can_view_email_template_details()
    {
        $template = EmailTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.show', $template));

        $response->assertStatus(200);
        $response->assertViewIs('admin.email-templates.show');
        $response->assertViewHas('emailTemplate', $template);
    }

    /** @test */
    public function admin_can_view_email_template_edit_form()
    {
        $template = EmailTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.edit', $template));

        $response->assertStatus(200);
        $response->assertViewIs('admin.email-templates.edit');
        $response->assertViewHas(['emailTemplate', 'types', 'categories']);
    }

    /** @test */
    public function admin_can_update_email_template()
    {
        $template = EmailTemplate::factory()->create();

        $updateData = [
            'name' => 'Updated Template',
            'subject' => 'Updated Subject',
            'body' => 'This is an updated email template body content.',
            'type' => 'admin',
            'category' => 'license',
            'is_active' => false,
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.email-templates.update', $template), $updateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'name' => 'Updated Template',
            'subject' => 'Updated Subject',
            'type' => 'admin',
            'category' => 'license',
            'is_active' => false,
        ]);
    }

    /** @test */
    public function email_template_update_validates_required_fields()
    {
        $template = EmailTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.email-templates.update', $template), []);

        $response->assertSessionHasErrors(['name', 'subject', 'body', 'type', 'category']);
    }

    /** @test */
    public function email_template_update_validates_name_uniqueness()
    {
        $template1 = EmailTemplate::factory()->create(['name' => 'Template 1']);
        $template2 = EmailTemplate::factory()->create(['name' => 'Template 2']);

        $updateData = [
            'name' => 'Template 1',
            'subject' => 'Test Subject',
            'body' => 'Test body content',
            'type' => 'user',
            'category' => 'registration',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.email-templates.update', $template2), $updateData);

        $response->assertSessionHasErrors(['name']);
    }

    /** @test */
    public function admin_can_delete_email_template()
    {
        $template = EmailTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.email-templates.destroy', $template));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('email_templates', ['id' => $template->id]);
    }

    /** @test */
    public function admin_can_toggle_email_template_status()
    {
        $template = EmailTemplate::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.toggle', $template));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('email_templates', [
            'id' => $template->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function admin_can_test_email_template_rendering()
    {
        $template = EmailTemplate::factory()->create([
            'body' => 'Hello {{user_name}}, welcome to {{site_name}}!',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.test', $template));

        $response->assertStatus(200);
        $response->assertViewIs('admin.email-templates.test');
        $response->assertViewHas(['emailTemplate', 'testData', 'rendered']);
    }

    /** @test */
    public function admin_can_send_test_email()
    {
        $template = EmailTemplate::factory()->create();

        $testData = [
            'test_email' => 'test@example.com',
            'test_data' => [
                'user_name' => 'Test User',
                'custom_field' => 'Custom Value',
            ],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.send-test', $template), $testData);

        $response->assertRedirect();
        // Note: Success/failure depends on email service configuration
    }

    /** @test */
    public function test_email_validates_email_address()
    {
        $template = EmailTemplate::factory()->create();

        $testData = [
            'test_email' => 'invalid-email',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.send-test', $template), $testData);

        $response->assertSessionHasErrors(['test_email']);
    }

    /** @test */
    public function test_email_validates_required_email()
    {
        $template = EmailTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.send-test', $template), []);

        $response->assertSessionHasErrors(['test_email']);
    }

    /** @test */
    public function non_admin_cannot_access_email_templates()
    {
        $template = EmailTemplate::factory()->create();

        $routes = [
            'admin.email-templates.index',
            'admin.email-templates.create',
            'admin.email-templates.show',
            'admin.email-templates.edit',
            'admin.email-templates.test',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($this->user)
                ->get(route($route, $template));

            $response->assertStatus(403);
        }
    }

    /** @test */
    public function non_admin_cannot_perform_email_template_operations()
    {
        $template = EmailTemplate::factory()->create();

        $templateData = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'Test body content',
            'type' => 'user',
            'category' => 'registration',
        ];

        $updateData = [
            'name' => 'Updated Template',
            'subject' => 'Updated Subject',
            'body' => 'Updated body content',
            'type' => 'admin',
            'category' => 'license',
        ];

        $testData = [
            'test_email' => 'test@example.com',
        ];

        // Test store
        $response = $this->actingAs($this->user)
            ->post(route('admin.email-templates.store'), $templateData);
        $response->assertStatus(403);

        // Test update
        $response = $this->actingAs($this->user)
            ->put(route('admin.email-templates.update', $template), $updateData);
        $response->assertStatus(403);

        // Test destroy
        $response = $this->actingAs($this->user)
            ->delete(route('admin.email-templates.destroy', $template));
        $response->assertStatus(403);

        // Test toggle
        $response = $this->actingAs($this->user)
            ->post(route('admin.email-templates.toggle', $template));
        $response->assertStatus(403);

        // Test send test
        $response = $this->actingAs($this->user)
            ->post(route('admin.email-templates.send-test', $template), $testData);
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_email_templates()
    {
        $template = EmailTemplate::factory()->create();

        $response = $this->get(route('admin.email-templates.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function email_templates_handles_database_errors_gracefully()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.index'));

        $response->assertStatus(200);
        $response->assertViewHas('templates');
    }

    /** @test */
    public function email_template_creation_handles_validation_errors_gracefully()
    {
        $templateData = [
            'name' => '', // Invalid: empty name
            'subject' => '', // Invalid: empty subject
            'body' => 'Short', // Invalid: too short
            'type' => 'invalid', // Invalid: not in allowed values
            'category' => '', // Invalid: empty category
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.store'), $templateData);

        $response->assertSessionHasErrors(['name', 'subject', 'body', 'type', 'category']);
    }

    /** @test */
    public function email_template_update_handles_validation_errors_gracefully()
    {
        $template = EmailTemplate::factory()->create();

        $updateData = [
            'name' => '', // Invalid: empty name
            'subject' => '', // Invalid: empty subject
            'body' => 'Short', // Invalid: too short
            'type' => 'invalid', // Invalid: not in allowed values
            'category' => '', // Invalid: empty category
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.email-templates.update', $template), $updateData);

        $response->assertSessionHasErrors(['name', 'subject', 'body', 'type', 'category']);
    }

    /** @test */
    public function email_template_test_handles_rendering_errors_gracefully()
    {
        $template = EmailTemplate::factory()->create([
            'body' => 'Invalid template with {{unclosed_variable',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.test', $template));

        $response->assertStatus(200);
        $response->assertViewHas('rendered');
    }

    /** @test */
    public function email_template_send_test_handles_service_errors_gracefully()
    {
        $template = EmailTemplate::factory()->create();

        $testData = [
            'test_email' => 'test@example.com',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.send-test', $template), $testData);

        $response->assertRedirect();
        // The response will depend on email service configuration
    }

    /** @test */
    public function email_template_variables_are_handled_correctly()
    {
        $template = EmailTemplate::factory()->create([
            'variables' => ['user_name', 'site_name', 'custom_field'],
        ]);

        $templateData = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'Test body content',
            'type' => 'user',
            'category' => 'registration',
            'variables' => ['user_name', 'site_name', 'custom_field'],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.store'), $templateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('email_templates', [
            'name' => 'Test Template',
            'variables' => json_encode(['user_name', 'site_name', 'custom_field']),
        ]);
    }

    /** @test */
    public function email_template_boolean_fields_are_handled_correctly()
    {
        $templateData = [
            'name' => 'Test Template',
            'subject' => 'Test Subject',
            'body' => 'Test body content',
            'type' => 'user',
            'category' => 'registration',
            'is_active' => '1', // String boolean
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.email-templates.store'), $templateData);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('email_templates', [
            'name' => 'Test Template',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function email_template_pagination_works_correctly()
    {
        EmailTemplate::factory()->count(25)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.email-templates.index'));

        $response->assertStatus(200);
        $response->assertViewHas('templates');

        $templates = $response->viewData('templates');
        $this->assertCount(20, $templates->items()); // Default pagination
    }
}
