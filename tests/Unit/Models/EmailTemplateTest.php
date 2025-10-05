<?php

namespace Tests\Unit\Models;

use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for EmailTemplate model.
 */
class EmailTemplateTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test email template creation.
     */
    public function test_can_create_email_template(): void
    {
        $template = EmailTemplate::create([
            'name' => 'test_template',
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'type' => 'user',
            'category' => 'registration',
            'variables' => ['name', 'email'],
            'is_active' => true,
            'description' => 'Test template',
        ]);

        $this->assertInstanceOf(EmailTemplate::class, $template);
        $this->assertEquals('test_template', $template->name);
        $this->assertEquals('Test Subject', $template->subject);
        $this->assertEquals('Test Body', $template->body);
        $this->assertEquals('user', $template->type);
        $this->assertEquals('registration', $template->category);
        $this->assertTrue($template->is_active);
    }

    /**
     * Test email template rendering.
     */
    public function test_can_render_email_template(): void
    {
        $template = EmailTemplate::create([
            'name' => 'test_template',
            'subject' => 'Hello {{name}}',
            'body' => 'Welcome {{name}}, your email is {{email}}',
            'type' => 'user',
            'category' => 'registration',
            'is_active' => true,
        ]);

        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];

        $rendered = $template->render($data);

        $this->assertIsArray($rendered);
        $this->assertArrayHasKey('subject', $rendered);
        $this->assertArrayHasKey('body', $rendered);
        $this->assertEquals('Hello John Doe', $rendered['subject']);
        $this->assertEquals('Welcome John Doe, your email is john@example.com', $rendered['body']);
    }

    /**
     * Test getByName method.
     */
    public function test_get_by_name(): void
    {
        EmailTemplate::create([
            'name' => 'test_template',
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'type' => 'user',
            'category' => 'registration',
            'is_active' => true,
        ]);

        $template = EmailTemplate::getByName('test_template');

        $this->assertInstanceOf(EmailTemplate::class, $template);
        $this->assertEquals('test_template', $template->name);
    }

    /**
     * Test getByTypeAndCategory method.
     */
    public function test_get_by_type_and_category(): void
    {
        EmailTemplate::create([
            'name' => 'user_registration',
            'subject' => 'Welcome',
            'body' => 'Welcome to our service',
            'type' => 'user',
            'category' => 'registration',
            'is_active' => true,
        ]);

        EmailTemplate::create([
            'name' => 'admin_registration',
            'subject' => 'New User',
            'body' => 'A new user has registered',
            'type' => 'admin',
            'category' => 'registration',
            'is_active' => true,
        ]);

        $templates = EmailTemplate::getByTypeAndCategory('user', 'registration');

        $this->assertCount(1, $templates);
        $this->assertEquals('user_registration', $templates->first()->name);
    }

    /**
     * Test active scope.
     */
    public function test_active_scope(): void
    {
        EmailTemplate::create([
            'name' => 'active_template',
            'subject' => 'Active Subject',
            'body' => 'Active Body',
            'type' => 'user',
            'category' => 'test',
            'is_active' => true,
        ]);

        EmailTemplate::create([
            'name' => 'inactive_template',
            'subject' => 'Inactive Subject',
            'body' => 'Inactive Body',
            'type' => 'user',
            'category' => 'test',
            'is_active' => false,
        ]);

        $activeTemplates = EmailTemplate::active()->get();

        $this->assertCount(1, $activeTemplates);
        $this->assertEquals('active_template', $activeTemplates->first()->name);
    }

    /**
     * Test forType scope.
     */
    public function test_for_type_scope(): void
    {
        EmailTemplate::create([
            'name' => 'user_template',
            'subject' => 'User Subject',
            'body' => 'User Body',
            'type' => 'user',
            'category' => 'test',
            'is_active' => true,
        ]);

        EmailTemplate::create([
            'name' => 'admin_template',
            'subject' => 'Admin Subject',
            'body' => 'Admin Body',
            'type' => 'admin',
            'category' => 'test',
            'is_active' => true,
        ]);

        $userTemplates = EmailTemplate::forType('user')->get();

        $this->assertCount(1, $userTemplates);
        $this->assertEquals('user_template', $userTemplates->first()->name);
    }

    /**
     * Test forCategory scope.
     */
    public function test_for_category_scope(): void
    {
        EmailTemplate::create([
            'name' => 'registration_template',
            'subject' => 'Registration Subject',
            'body' => 'Registration Body',
            'type' => 'user',
            'category' => 'registration',
            'is_active' => true,
        ]);

        EmailTemplate::create([
            'name' => 'license_template',
            'subject' => 'License Subject',
            'body' => 'License Body',
            'type' => 'user',
            'category' => 'license',
            'is_active' => true,
        ]);

        $registrationTemplates = EmailTemplate::forCategory('registration')->get();

        $this->assertCount(1, $registrationTemplates);
        $this->assertEquals('registration_template', $registrationTemplates->first()->name);
    }

    /**
     * Test variables casting.
     */
    public function test_variables_casting(): void
    {
        $template = EmailTemplate::create([
            'name' => 'test_template',
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'type' => 'user',
            'category' => 'test',
            'variables' => ['name', 'email', 'company'],
            'is_active' => true,
        ]);

        $this->assertIsArray($template->variables);
        $this->assertContains('name', $template->variables);
        $this->assertContains('email', $template->variables);
        $this->assertContains('company', $template->variables);
    }

    /**
     * Test is_active casting.
     */
    public function test_is_active_casting(): void
    {
        $template = EmailTemplate::create([
            'name' => 'test_template',
            'subject' => 'Test Subject',
            'body' => 'Test Body',
            'type' => 'user',
            'category' => 'test',
            'is_active' => 1,
        ]);

        $this->assertIsBool($template->is_active);
        $this->assertTrue($template->is_active);
    }
}
