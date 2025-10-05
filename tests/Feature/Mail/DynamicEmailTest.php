<?php

namespace Tests\Feature\Mail;

use App\Mail\DynamicEmail;
use App\Models\EmailTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Test suite for DynamicEmail mailable.
 */
class DynamicEmailTest extends TestCase
{
    use RefreshDatabase;

    protected $template;

    protected $data;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test email template
        $this->template = EmailTemplate::factory()->create([
            'name' => 'test_template',
            'subject' => 'Test Subject {{name}}',
            'body' => 'Hello {{name}}, this is a test email.',
            'type' => 'html',
        ]);

        $this->data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ];
    }

    /**
     * Test email can be created successfully.
     */
    public function test_email_can_be_created(): void
    {
        $email = new DynamicEmail($this->template, $this->data);

        $this->assertInstanceOf(DynamicEmail::class, $email);
        $this->assertEquals($this->template, $email->template);
        $this->assertEquals($this->data, $email->data);
    }

    /**
     * Test email envelope generation.
     */
    public function test_email_envelope_generation(): void
    {
        $email = new DynamicEmail($this->template, $this->data);
        $envelope = $email->envelope();

        $this->assertStringContainsString('John Doe', $envelope->subject);
    }

    /**
     * Test email content generation.
     */
    public function test_email_content_generation(): void
    {
        $email = new DynamicEmail($this->template, $this->data);
        $content = $email->content();

        $this->assertEquals('emails.dynamic', $content->view);
        $this->assertArrayHasKey('template', $content->with);
        $this->assertArrayHasKey('data', $content->with);
        $this->assertArrayHasKey('rendered', $content->with);
    }

    /**
     * Test email can be sent.
     */
    public function test_email_can_be_sent(): void
    {
        Mail::fake();

        $email = new DynamicEmail($this->template, $this->data);

        Mail::to('test@example.com')->send($email);

        Mail::assertSent(DynamicEmail::class, function ($mail) {
            return $mail->template->id === $this->template->id &&
                   $mail->data === $this->data;
        });
    }

    /**
     * Test email with empty data.
     */
    public function test_email_with_empty_data(): void
    {
        $email = new DynamicEmail($this->template, []);

        $this->assertInstanceOf(DynamicEmail::class, $email);
        $this->assertEquals([], $email->data);
    }

    /**
     * Test email with complex data.
     */
    public function test_email_with_complex_data(): void
    {
        $complexData = [
            'user' => [
                'name' => 'Jane Doe',
                'email' => 'jane@example.com',
            ],
            'order' => [
                'id' => 12345,
                'total' => 99.99,
            ],
            'items' => [
                ['name' => 'Product 1', 'price' => 49.99],
                ['name' => 'Product 2', 'price' => 50.00],
            ],
        ];

        $email = new DynamicEmail($this->template, $complexData);

        $this->assertEquals($complexData, $email->data);
    }

    /**
     * Test email attachments.
     */
    public function test_email_attachments(): void
    {
        $email = new DynamicEmail($this->template, $this->data);
        $attachments = $email->attachments();

        $this->assertIsArray($attachments);
        $this->assertEmpty($attachments);
    }

    /**
     * Test email with different template types.
     */
    public function test_email_with_different_template_types(): void
    {
        $textTemplate = EmailTemplate::factory()->create([
            'name' => 'text_template',
            'subject' => 'Text Subject',
            'body' => 'Plain text email body',
            'type' => 'text',
        ]);

        $email = new DynamicEmail($textTemplate, $this->data);

        $this->assertEquals($textTemplate, $email->template);
        $this->assertEquals('text', $email->template->type);
    }

    /**
     * Test email template rendering.
     */
    public function test_email_template_rendering(): void
    {
        $email = new DynamicEmail($this->template, $this->data);

        // The rendered content should be available
        $this->assertArrayHasKey('subject', $email->rendered);
        $this->assertArrayHasKey('body', $email->rendered);
    }
}
