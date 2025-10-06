<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Dynamic Email Mailable with enhanced security.
 *
 * A flexible email class that renders content from database templates
 * with comprehensive security measures and proper error handling.
 *
 * Features:
 * - Dynamic email template rendering from database
 * - Variable substitution with security validation
 * - HTML and text content support
 * - Enhanced security measures (XSS protection, input validation)
 * - Comprehensive error handling for template rendering
 * - Proper logging for errors and warnings only
 * - Template validation and sanitization
 * - Queue support for background processing
 */
class DynamicEmail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * The email template instance.
     */
    public EmailTemplate $template;
    /**
     * The data for variable substitution.
     *
     * @var array<string, mixed>
     */
    public array $data;
    /**
     * The rendered email content.
     *
     * @var array<string, string>
     */
    public array $rendered;
    /**
     * Create a new message instance with enhanced security.
     *
     * Initializes the dynamic email with template and data, performing
     * validation and sanitization for security.
     *
     * @param  EmailTemplate  $template  The email template to use
     * @param  array<string, mixed>  $data  The data for variable substitution
     *
     * @throws \InvalidArgumentException When template is invalid
     * @throws \Exception When template rendering fails
     */
    public function __construct(EmailTemplate $template, array $data = [])
    {
        try {
            // Validate template
            if (! $template || ! $template->is_active) {
                throw new \InvalidArgumentException('Invalid or inactive email template provided');
            }
            // Sanitize input data
            $sanitizedData = $this->sanitizeData($data);
            $this->template = $template;
            $this->data = $sanitizedData;
            $this->rendered = $template->render($sanitizedData);
            // Validate rendered content
            if (empty($this->rendered['subject']) || empty($this->rendered['body'])) {
                throw new \Exception('Template rendering resulted in empty subject or body');
            }
        } catch (\Exception $e) {
            Log::error('DynamicEmail construction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $template->id ?? 'unknown',
                'template_name' => $template->name ?? 'unknown',
            ]);
            throw $e;
        }
    }
    /**
     * Get the message envelope with enhanced security.
     *
     * Returns the email envelope with sanitized subject line
     * and proper security measures.
     *
     * @return Envelope The email envelope
     *
     * @throws \Exception When envelope creation fails
     */
    public function envelope(): Envelope
    {
        try {
            $subject = $this->sanitizeOutput($this->rendered['subject']);
            return new Envelope(
                subject: $subject,
            );
        } catch (\Exception $e) {
            Log::error('DynamicEmail envelope creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $this->template->id ?? 'unknown',
            ]);
            throw $e;
        }
    }
    /**
     * Get the message content definition with enhanced security.
     *
     * Returns the email content with sanitized data and proper
     * security measures for template rendering.
     *
     * @return Content The email content definition
     *
     * @throws \Exception When content creation fails
     */
    public function content(): Content
    {
        try {
            // Sanitize rendered content
            $sanitizedRendered = [
                'subject' => $this->sanitizeOutput($this->rendered['subject']),
                'body' => $this->sanitizeOutput($this->rendered['body']),
            ];
            return new Content(
                view: 'emails.dynamic',
                with: [
                    'template' => $this->template,
                    'data' => $this->data,
                    'rendered' => $sanitizedRendered,
                ],
            );
        } catch (\Exception $e) {
            Log::error('DynamicEmail content creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'template_id' => $this->template->id ?? 'unknown',
            ]);
            throw $e;
        }
    }
    /**
     * Get the attachments for the message.
     *
     * Returns an empty array as this email class does not support
     * attachments. Override this method in subclasses if needed.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment> Empty array
     */
    public function attachments(): array
    {
        return [];
    }
    /**
     * Sanitize input data to prevent XSS attacks.
     *
     * Recursively sanitizes array data to prevent XSS attacks
     * and ensure data integrity.
     *
     * @param  array<string, mixed>  $data  The data to sanitize
     *
     * @return array<string, mixed> The sanitized data
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            $sanitizedKey = htmlspecialchars(trim($key), ENT_QUOTES, 'UTF-8');
            if (is_array($value)) {
                $sanitized[$sanitizedKey] = $this->sanitizeData($value);
            } elseif (is_string($value)) {
                $sanitized[$sanitizedKey] = htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
            } else {
                $sanitized[$sanitizedKey] = $value;
            }
        }
        return $sanitized;
    }
    /**
     * Sanitize output content to prevent XSS attacks.
     *
     * Sanitizes string content to prevent XSS attacks while
     * preserving HTML formatting for email templates.
     *
     * @param  string  $content  The content to sanitize
     *
     * @return string The sanitized content
     */
    private function sanitizeOutput(string $content): string
    {
        // Allow basic HTML tags for email formatting but sanitize dangerous content
        $allowedTags = '<p><br><strong><em><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><div><span>';
        // Strip dangerous tags and attributes
        $content = strip_tags($content, $allowedTags);
        // Remove dangerous attributes
        $content = preg_replace('/(<[^>]+)\s+(on\w+|javascript:|data:|vbscript:)[^>]*>/i', '$1>', $content);
        return trim($content);
    }
}
