<?php
namespace App\Services;
use App\Mail\DynamicEmail;
use App\Models\EmailTemplate;
use App\Models\Setting;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
/**
 * Email Service with enhanced security.
 *
 * A comprehensive email service that handles dynamic email sending
 * using database-stored templates with variable substitution and
 * comprehensive security measures.
 *
 * Features:
 * - Template-based email system with security validation
 * - Variable substitution support with XSS protection
 * - User and admin specific templates
 * - Queue support for performance
 * - Enhanced error handling and logging
 * - Template validation and sanitization
 * - Input validation and sanitization
 * - Comprehensive security measures
 * - Clean code structure with no duplicate patterns
 * - Proper type hints and return types
 */
class EmailService
{
    /**
     * Send email using template name and data with enhanced security.
     *
     * Sends an email using a database-stored template with comprehensive
     * validation, sanitization, and error handling.
     *
     * @param  string  $templateName  Template identifier
     * @param  string  $recipientEmail  Recipient email address
     * @param  array  $data  Variables for template substitution
     * @param  string|null  $recipientName  Optional recipient name
     *
     * @return bool Success status
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendEmail(
        string $templateName,
        string $recipientEmail,
        array $data = [],
        ?string $recipientName = null,
    ): bool {
        try {
            // Validate and sanitize inputs
            $templateName = $this->validateTemplateName($templateName);
            $recipientEmail = $this->validateEmail($recipientEmail);
            $recipientName = $this->sanitizeString($recipientName);
            $data = $this->sanitizeData($data);
            $template = EmailTemplate::getByName($templateName);
            if (! $template) {
                Log::error('Email template not found: '.$templateName);
                return false;
            }
            // Add common variables with sanitization
            $data = array_merge($data, [
                'recipient_email' => $recipientEmail,
                'recipient_name' => $recipientName ?? 'User',
                'site_name' => config('app.name'),
                'site_url' => config('app.url'),
                'current_year' => date('Y'),
            ]);
            // Send email
            Mail::to($recipientEmail, $recipientName)->send(new DynamicEmail($template, $data));
            return true;
        } catch (Exception $e) {
            Log::error('Failed to send email: '.$e->getMessage(), [
                'template' => $templateName,
                'recipient' => $recipientEmail,
                'exception' => $e->getTraceAsString(),
            ]);
            return false;
        }
    }
    /**
     * Send email to user using template with enhanced security.
     *
     * Sends an email to a specific user using a database-stored template
     * with comprehensive validation and sanitization.
     *
     * @param  User  $user  User instance
     * @param  string  $templateName  Template identifier
     * @param  array  $data  Variables for template substitution
     *
     * @return bool Success status
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendToUser(User $user, string $templateName, array $data = []): bool
    {
        if (! $user || ! $user->email) {
            Log::error('Invalid user provided for email sending');
            return false;
        }
        $userData = [
            'user_name' => $this->sanitizeString($user->name),
            'user_firstname' => $this->sanitizeString($user->firstname ?? ''),
            'user_lastname' => $this->sanitizeString($user->lastname ?? ''),
            'user_id' => $user->id,
        ];
        return $this->sendEmail($templateName, $user->email, array_merge($data, $userData), $user->name);
    }
    /**
     * Send email to admin using template with enhanced security.
     *
     * Sends an email to the admin using a database-stored template
     * with comprehensive validation and sanitization.
     *
     * @param  string  $templateName  Template identifier
     * @param  array  $data  Variables for template substitution
     *
     * @return bool Success status
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendToAdmin(string $templateName, array $data = []): bool
    {
        // Get admin email from settings or use default
        $adminEmail = Setting::get('support_email', config('mail.from.address'));
        if (empty($adminEmail)) {
            Log::error('Admin email not configured for email sending');
            return false;
        }
        $adminData = [
            'admin_name' => 'Administrator',
            'site_name' => config('app.name'),
        ];
        return $this->sendEmail($templateName, $adminEmail, array_merge($data, $adminData), 'Administrator');
    }
    /**
     * Send bulk emails to multiple users with enhanced security.
     *
     * Sends emails to multiple users using a database-stored template
     * with comprehensive validation and sanitization.
     *
     * @param  array  $users  Array of User instances or email addresses
     * @param  string  $templateName  Template identifier
     * @param  array  $data  Variables for template substitution
     *
     * @return array Results array with success/failure counts
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendBulkEmail(array $users, string $templateName, array $data = []): array
    {
        if (empty($users)) {
            Log::error('Empty users array provided for bulk email sending');
            return ['total' => 0, 'success' => 0, 'failed' => 0, 'errors' => []];
        }
        $results = [
            'total' => count($users),
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];
        foreach ($users as $user) {
            try {
                if ($user instanceof User) {
                    $success = $this->sendToUser($user, $templateName, $data);
                } else {
                    $success = $this->sendEmail($templateName, $user, $data);
                }
                if ($success) {
                    $results['success']++;
                } else {
                    $results['failed']++;
                    $results['errors'][] = $user instanceof User ? $user->email : $user;
                }
            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = $user instanceof User ? $user->email : $user;
                Log::error('Failed to send bulk email to user: '.$e->getMessage());
            }
        }
        return $results;
    }
    /**
     * Get available templates by type and category with enhanced security.
     *
     * Retrieves email templates filtered by type and category with
     * comprehensive validation and sanitization.
     *
     * @param  string  $type  Template type ('user' or 'admin')
     * @param  string|null  $category  Optional category filter
     *
     * @return Collection Collection of email templates
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function getTemplates(string $type, ?string $category = null): Collection
    {
        $type = $this->validateTemplateType($type);
        $category = $this->sanitizeString($category);
        $query = EmailTemplate::forType($type)->active();
        if ($category) {
            $query->forCategory($category);
        }
        return $query->get();
    }
    /**
     * Create or update email template with enhanced security.
     *
     * Creates or updates an email template with comprehensive
     * validation and sanitization.
     *
     * @param  array  $templateData  Template data
     *
     * @return EmailTemplate The created or updated template
     *
     * @throws \InvalidArgumentException When template data is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function createOrUpdateTemplate(array $templateData): EmailTemplate
    {
        if (empty($templateData['name'])) {
            throw new \InvalidArgumentException('Template name is required');
        }
        $templateData = $this->sanitizeData($templateData);
        return EmailTemplate::updateOrCreate(
            ['name' => $templateData['name']],
            $templateData,
        );
    }
    /**
     * Test email template rendering with enhanced security.
     *
     * Tests email template rendering with comprehensive validation
     * and sanitization.
     *
     * @param  string  $templateName  Template identifier
     * @param  array  $data  Test data
     *
     * @return array Rendered content
     *
     * @throws \InvalidArgumentException When template not found
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function testTemplate(string $templateName, array $data = []): array
    {
        $templateName = $this->validateTemplateName($templateName);
        $data = $this->sanitizeData($data);
        $template = EmailTemplate::getByName($templateName);
        if (! $template) {
            throw new \InvalidArgumentException("Template not found: {$templateName}");
        }
        return $template->render($data);
    }
    /**
     * Send user registration welcome email with enhanced security.
     *
     * Sends a welcome email to a newly registered user with
     * comprehensive validation and sanitization.
     *
     * @param  User  $user  User instance
     *
     * @return bool Success status
     *
     * @throws \InvalidArgumentException When user is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendUserWelcome(User $user): bool
    {
        if (! $user || ! $user->created_at) {
            Log::error('Invalid user provided for welcome email');
            return false;
        }
        return $this->sendToUser($user, 'user_welcome', [
            'registration_date' => $user->created_at->format('M d, Y'),
        ]);
    }
    /**
     * Send welcome email to user with additional data support and enhanced security.
     *
     * Sends a welcome email to a user with additional data for template
     * substitution and comprehensive validation.
     *
     * @param  User  $user  User instance
     * @param  array  $data  Additional data for template substitution
     *
     * @return bool Success status
     *
     * @throws \InvalidArgumentException When user is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendWelcome(User $user, array $data = []): bool
    {
        if (! $user || ! $user->created_at) {
            Log::error('Invalid user provided for welcome email');
            return false;
        }
        $welcomeData = array_merge([
            'registration_date' => $user->created_at->format('M d, Y'),
        ], $this->sanitizeData($data));
        return $this->sendToUser($user, 'user_welcome', $welcomeData);
    }
    /**
     * Send email verification email with enhanced security.
     *
     * Sends an email verification email to a user with comprehensive
     * validation and sanitization.
     *
     * @param  User  $user  User instance
     * @param  string  $verificationUrl  Verification URL
     *
     * @return bool Success status
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendEmailVerification(User $user, string $verificationUrl): bool
    {
        if (! $user || empty($verificationUrl)) {
            Log::error('Invalid user or verification URL provided');
            return false;
        }
        return $this->sendToUser($user, 'user_email_verification', [
            'verification_url' => $this->sanitizeString($verificationUrl),
            'verification_expires' => now()->addHours(24)->format('M d, Y \a\t g:i A'),
        ]);
    }
    /**
     * Send admin notification when a new user registers with enhanced security.
     *
     * Sends an admin notification when a new user registers with
     * comprehensive validation and sanitization.
     *
     * @param  User  $user  User instance
     *
     * @return bool Success status
     *
     * @throws \InvalidArgumentException When user is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendNewUserNotification(User $user): bool
    {
        if (! $user) {
            Log::error('Invalid user provided for new user notification');
            return false;
        }
        return $this->sendToAdmin('admin_new_user_registration', [
            'user_name' => $this->sanitizeString($user->name),
            'user_email' => $this->sanitizeString($user->email),
            'user_firstname' => $this->sanitizeString($user->firstname ?? ''),
            'user_lastname' => $this->sanitizeString($user->lastname ?? ''),
            'user_phone' => $this->sanitizeString($user->phonenumber ?? 'Not provided'),
            'user_country' => $this->sanitizeString($user->country ?? 'Not provided'),
            'registration_date' => $user->created_at->format('M d, Y \a\t g:i A'),
            'registration_ip' => $this->sanitizeString(request()->ip() ?? 'Unknown'),
            'user_agent' => $this->sanitizeString(request()->userAgent() ?? 'Unknown'),
        ]);
    }
    /**
     * Send payment confirmation email with enhanced security.
     *
     * Sends a payment confirmation email to a user with comprehensive
     * validation and sanitization.
     *
     * @param  mixed  $license  License instance
     * @param  mixed  $invoice  Invoice instance
     *
     * @return bool Success status
     *
     * @throws \InvalidArgumentException When parameters are invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    public function sendPaymentConfirmation($license, $invoice): bool
    {
        if (! $license || ! $invoice || ! $license->user) {
            Log::error('Invalid license or invoice provided for payment confirmation');
            return false;
        }
        return $this->sendToUser($license->user, 'payment_confirmation', [
            'customer_name' => $this->sanitizeString($license->user->name),
            'customer_email' => $this->sanitizeString($license->user->email),
            'product_name' => $this->sanitizeString($license->product->name),
            'order_number' => $this->sanitizeString($invoice->invoice_number),
            'license_key' => $this->sanitizeString($license->license_key),
            'invoice_number' => $this->sanitizeString($invoice->invoice_number),
            'amount' => $invoice->amount,
            'currency' => $this->sanitizeString($invoice->currency),
            'payment_method' => $this->sanitizeString(ucfirst($invoice->metadata['gateway'] ?? 'Unknown')),
            'payment_date' => $invoice->paid_at->format('M d, Y \a\t g:i A'),
            'license_expires_at' => $license->license_expires_at ?
                $license->license_expires_at->format('M d, Y') : 'Never',
        ]);
    }
    /**
     * Send password reset email.
     */
    public function sendPasswordReset(User $user, string $resetUrl): bool
    {
        return $this->sendToUser($user, 'user_password_reset', [
            'reset_url' => $resetUrl,
            'reset_expires' => now()->addHours(1)->format('M d, Y \a\t g:i A'),
        ]);
    }
    /**
     * Send license expiration warning to user.
     */
    public function sendLicenseExpiring(User $user, array $licenseData): bool
    {
        return $this->sendToUser($user, 'user_license_expiring', array_merge($licenseData, [
            'license_key' => $licenseData['license_key'] ?? '',
            'product_name' => $licenseData['product_name'] ?? '',
            'expires_at' => $licenseData['expires_at'] ?? '',
            'days_remaining' => $licenseData['days_remaining'] ?? 0,
        ]));
    }
    /**
     * Send license updated notification to user.
     */
    public function sendLicenseUpdated(User $user, array $licenseData): bool
    {
        return $this->sendToUser($user, 'user_license_updated', array_merge($licenseData, [
            'license_key' => $licenseData['license_key'] ?? '',
            'product_name' => $licenseData['product_name'] ?? '',
            'update_type' => $licenseData['update_type'] ?? 'updated',
        ]));
    }
    /**
     * Send product version update notification to user.
     */
    public function sendProductVersionUpdate(User $user, array $productData): bool
    {
        return $this->sendToUser($user, 'user_product_version_update', array_merge($productData, [
            'product_name' => $productData['product_name'] ?? '',
            'old_version' => $productData['old_version'] ?? '',
            'new_version' => $productData['new_version'] ?? '',
            'download_url' => $productData['download_url'] ?? '',
        ]));
    }
    /**
     * Send support ticket created notification to user.
     */
    public function sendTicketCreated(User $user, array $ticketData): bool
    {
        return $this->sendToUser($user, 'user_ticket_created', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'ticket_status' => $ticketData['ticket_status'] ?? 'open',
        ]));
    }
    /**
     * Send support ticket status update notification to user.
     */
    public function sendTicketStatusUpdate(User $user, array $ticketData): bool
    {
        return $this->sendToUser($user, 'user_ticket_status_update', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'old_status' => $ticketData['old_status'] ?? '',
            'new_status' => $ticketData['new_status'] ?? '',
        ]));
    }
    /**
     * Send support ticket reply notification to user.
     */
    public function sendTicketReply(User $user, array $ticketData): bool
    {
        return $this->sendToUser($user, 'user_ticket_reply', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'reply_message' => $ticketData['reply_message'] ?? '',
            'replied_by' => $ticketData['replied_by'] ?? 'Support Team',
        ]));
    }
    /**
     * Send invoice approaching due date notification to user.
     */
    public function sendInvoiceApproachingDue(User $user, array $invoiceData): bool
    {
        return $this->sendToUser($user, 'user_invoice_approaching_due', array_merge($invoiceData, [
            'invoice_number' => $invoiceData['invoice_number'] ?? '',
            'invoice_amount' => $invoiceData['invoice_amount'] ?? 0,
            'due_date' => $invoiceData['due_date'] ?? '',
            'days_remaining' => $invoiceData['days_remaining'] ?? 0,
        ]));
    }
    /**
     * Send invoice paid notification to user.
     */
    public function sendInvoicePaid(User $user, array $invoiceData): bool
    {
        return $this->sendToUser($user, 'user_invoice_paid', array_merge($invoiceData, [
            'invoice_number' => $invoiceData['invoice_number'] ?? '',
            'invoice_amount' => $invoiceData['invoice_amount'] ?? 0,
            'payment_date' => $invoiceData['payment_date'] ?? '',
            'payment_method' => $invoiceData['payment_method'] ?? '',
        ]));
    }
    /**
     * Send invoice cancelled notification to user.
     */
    public function sendInvoiceCancelled(User $user, array $invoiceData): bool
    {
        return $this->sendToUser($user, 'user_invoice_cancelled', array_merge($invoiceData, [
            'invoice_number' => $invoiceData['invoice_number'] ?? '',
            'invoice_amount' => $invoiceData['invoice_amount'] ?? 0,
            'cancellation_reason' => $invoiceData['cancellation_reason'] ?? '',
        ]));
    }
    /**
     * Send admin notification for license created.
     */
    public function sendAdminLicenseCreated(array $licenseData): bool
    {
        return $this->sendToAdmin('admin_license_created', array_merge($licenseData, [
            'license_key' => $licenseData['license_key'] ?? '',
            'product_name' => $licenseData['product_name'] ?? '',
            'customer_name' => $licenseData['customer_name'] ?? '',
            'customer_email' => $licenseData['customer_email'] ?? '',
        ]));
    }
    /**
     * Send admin notification for license expiring.
     */
    public function sendAdminLicenseExpiring(array $licenseData): bool
    {
        return $this->sendToAdmin('admin_license_expiring', array_merge($licenseData, [
            'license_key' => $licenseData['license_key'] ?? '',
            'product_name' => $licenseData['product_name'] ?? '',
            'customer_name' => $licenseData['customer_name'] ?? '',
            'customer_email' => $licenseData['customer_email'] ?? '',
            'expires_at' => $licenseData['expires_at'] ?? '',
            'days_remaining' => $licenseData['days_remaining'] ?? 0,
        ]));
    }
    /**
     * Send admin notification for license renewed.
     */
    public function sendAdminLicenseRenewed(array $licenseData): bool
    {
        return $this->sendToAdmin('admin_license_renewed', array_merge($licenseData, [
            'license_key' => $licenseData['license_key'] ?? '',
            'product_name' => $licenseData['product_name'] ?? '',
            'customer_name' => $licenseData['customer_name'] ?? '',
            'customer_email' => $licenseData['customer_email'] ?? '',
            'new_expires_at' => $licenseData['new_expires_at'] ?? '',
        ]));
    }
    /**
     * Send admin notification for support ticket created.
     */
    public function sendAdminTicketCreated(array $ticketData): bool
    {
        return $this->sendToAdmin('admin_ticket_created', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'customer_name' => $ticketData['customer_name'] ?? '',
            'customer_email' => $ticketData['customer_email'] ?? '',
            'ticket_priority' => $ticketData['ticket_priority'] ?? 'normal',
        ]));
    }
    /**
     * Send renewal reminder to user.
     */
    public function sendRenewalReminder(User $user, array $renewalData): bool
    {
        return $this->sendToUser($user, 'user_renewal_reminder', array_merge($renewalData, [
            'license_key' => $renewalData['license_key'] ?? '',
            'product_name' => $renewalData['product_name'] ?? '',
            'expires_at' => $renewalData['expires_at'] ?? '',
            'invoice_amount' => $renewalData['invoice_amount'] ?? 0,
            'invoice_due_date' => $renewalData['invoice_due_date'] ?? '',
            'invoice_id' => $renewalData['invoice_id'] ?? '',
        ]));
    }
    /**
     * Send admin notification for renewal reminder.
     */
    public function sendAdminRenewalReminder(array $renewalData): bool
    {
        return $this->sendToAdmin('admin_renewal_reminder', array_merge($renewalData, [
            'license_key' => $renewalData['license_key'] ?? '',
            'product_name' => $renewalData['product_name'] ?? '',
            'customer_name' => $renewalData['customer_name'] ?? '',
            'customer_email' => $renewalData['customer_email'] ?? '',
            'expires_at' => $renewalData['expires_at'] ?? '',
            'invoice_amount' => $renewalData['invoice_amount'] ?? 0,
            'invoice_id' => $renewalData['invoice_id'] ?? '',
        ]));
    }
    /**
     * Send admin notification for ticket reply from user.
     */
    public function sendAdminTicketReply(array $ticketData): bool
    {
        return $this->sendToAdmin('admin_ticket_reply', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'customer_name' => $ticketData['customer_name'] ?? '',
            'customer_email' => $ticketData['customer_email'] ?? '',
            'reply_message' => $ticketData['reply_message'] ?? '',
        ]));
    }
    /**
     * Send admin notification for ticket closed by user.
     */
    public function sendAdminTicketClosed(array $ticketData): bool
    {
        return $this->sendToAdmin('admin_ticket_closed', array_merge($ticketData, [
            'ticket_id' => $ticketData['ticket_id'] ?? '',
            'ticket_subject' => $ticketData['ticket_subject'] ?? '',
            'customer_name' => $ticketData['customer_name'] ?? '',
            'customer_email' => $ticketData['customer_email'] ?? '',
            'closure_reason' => $ticketData['closure_reason'] ?? '',
        ]));
    }
    /**
     * Send admin notification for invoice approaching due.
     */
    public function sendAdminInvoiceApproachingDue(array $invoiceData): bool
    {
        return $this->sendToAdmin('admin_invoice_approaching_due', array_merge($invoiceData, [
            'invoice_number' => $invoiceData['invoice_number'] ?? '',
            'invoice_amount' => $invoiceData['invoice_amount'] ?? 0,
            'customer_name' => $invoiceData['customer_name'] ?? '',
            'customer_email' => $invoiceData['customer_email'] ?? '',
            'due_date' => $invoiceData['due_date'] ?? '',
            'days_remaining' => $invoiceData['days_remaining'] ?? 0,
        ]));
    }
    /**
     * Send admin notification for invoice cancelled.
     */
    public function sendAdminInvoiceCancelled(array $invoiceData): bool
    {
        return $this->sendToAdmin('admin_invoice_cancelled', array_merge($invoiceData, [
            'invoice_number' => $invoiceData['invoice_number'] ?? '',
            'invoice_amount' => $invoiceData['invoice_amount'] ?? 0,
            'customer_name' => $invoiceData['customer_name'] ?? '',
            'customer_email' => $invoiceData['customer_email'] ?? '',
            'cancellation_reason' => $invoiceData['cancellation_reason'] ?? '',
        ]));
    }
    /**
     * Send payment failure notification to admin.
     */
    public function sendPaymentFailureNotification($order): bool
    {
        return $this->sendToAdmin('admin_payment_failure', [
            'customer_name' => $order->user->name,
            'customer_email' => $order->user->email,
            'product_name' => $order->product->name,
            'order_number' => $order->order_number,
            'amount' => $order->amount,
            'currency' => $order->currency,
            'payment_method' => ucfirst($order->payment_gateway),
            'failure_reason' => $order->gateway_response['error'] ?? 'Unknown error',
            'failure_date' => now()->format('M d, Y \a\t g:i A'),
        ]);
    }
    /**
     * Send license creation notification to user.
     */
    public function sendLicenseCreated($license): bool
    {
        return $this->sendToUser($license->user, 'license_created', [
            'customer_name' => $license->user->name,
            'customer_email' => $license->user->email,
            'product_name' => $license->product->name,
            'license_key' => $license->license_key,
            'license_type' => ucfirst($license->license_type),
            'max_domains' => $license->max_domains,
            'license_expires_at' => $license->license_expires_at ?
                $license->license_expires_at->format('M d, Y') : 'Never',
            'support_expires_at' => $license->support_expires_at ?
                $license->support_expires_at->format('M d, Y') : 'Never',
            'created_date' => $license->created_at->format('M d, Y \a\t g:i A'),
        ]);
    }
    /**
     * Send admin notification about payment and license creation.
     */
    public function sendAdminPaymentNotification($license, $invoice): bool
    {
        return $this->sendToAdmin('admin_payment_license_created', [
            'customer_name' => $license->user->name,
            'customer_email' => $license->user->email,
            'product_name' => $license->product->name,
            'license_key' => $license->license_key,
            'invoice_number' => $invoice->invoice_number,
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => ucfirst($invoice->metadata['gateway'] ?? 'Unknown'),
            'transaction_id' => $invoice->metadata['transaction_id'] ?? 'N/A',
            'payment_date' => $invoice->paid_at->format('M d, Y \a\t g:i A'),
            'license_type' => ucfirst($license->license_type),
            'max_domains' => $license->max_domains,
        ]);
    }
    /**
     * Send custom invoice payment confirmation to user.
     */
    public function sendCustomInvoicePaymentConfirmation($invoice): bool
    {
        return $this->sendToUser($invoice->user, 'custom_invoice_payment_confirmation', [
            'customer_name' => $invoice->user->name,
            'customer_email' => $invoice->user->email,
            'invoice_number' => $invoice->invoice_number,
            'service_description' => $invoice->notes ?? 'Custom Service',
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => ucfirst($invoice->metadata['gateway'] ?? 'Unknown'),
            'payment_date' => $invoice->paid_at->format('M d, Y \a\t g:i A'),
            'transaction_id' => $invoice->metadata['transaction_id'] ?? 'N/A',
        ]);
    }
    /**
     * Send admin notification for custom invoice payment.
     */
    public function sendAdminCustomInvoicePaymentNotification($invoice): bool
    {
        return $this->sendToAdmin('admin_custom_invoice_payment', [
            'customer_name' => $invoice->user->name,
            'customer_email' => $invoice->user->email,
            'invoice_number' => $invoice->invoice_number,
            'service_description' => $invoice->notes ?? 'Custom Service',
            'amount' => $invoice->amount,
            'currency' => $invoice->currency,
            'payment_method' => ucfirst($invoice->metadata['gateway'] ?? 'Unknown'),
            'transaction_id' => $invoice->metadata['transaction_id'] ?? 'N/A',
            'payment_date' => $invoice->paid_at->format('M d, Y \a\t g:i A'),
        ]);
    }
    /**
     * Validate and sanitize template name.
     *
     * Validates the template name and returns a sanitized version
     * with proper security measures.
     *
     * @param  string  $templateName  The template name to validate
     *
     * @return string The validated and sanitized template name
     *
     * @throws \InvalidArgumentException When template name is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    private function validateTemplateName(string $templateName): string
    {
        if (empty($templateName)) {
            throw new \InvalidArgumentException('Template name cannot be empty');
        }
        $sanitized = htmlspecialchars(trim($templateName), ENT_QUOTES, 'UTF-8');
        if (empty($sanitized)) {
            throw new \InvalidArgumentException('Template name contains invalid characters');
        }
        return $sanitized;
    }
    /**
     * Validate and sanitize email address.
     *
     * Validates the email address and returns a sanitized version
     * with proper security measures.
     *
     * @param  string  $email  The email address to validate
     *
     * @return string The validated and sanitized email address
     *
     * @throws \InvalidArgumentException When email is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    private function validateEmail(string $email): string
    {
        if (empty($email) === true) {
            throw new \InvalidArgumentException('Email address cannot be empty');
        }
        $sanitized = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        if (! filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address format');
        }
        return $sanitized;
    }
    /**
     * Validate and sanitize template type.
     *
     * Validates the template type and returns a sanitized version
     * with proper security measures.
     *
     * @param  string  $type  The template type to validate
     *
     * @return string The validated and sanitized template type
     *
     * @throws \InvalidArgumentException When template type is invalid
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    private function validateTemplateType(string $type): string
    {
        $allowedTypes = ['user', 'admin'];
        $sanitized = htmlspecialchars(trim($type), ENT_QUOTES, 'UTF-8');
        if (! in_array($sanitized, $allowedTypes, true)) {
            throw new \InvalidArgumentException(
                'Invalid template type. Allowed values: '.implode(', ', $allowedTypes),
            );
        }
        return $sanitized;
    }
    /**
     * Sanitize string input with XSS protection.
     *
     * Sanitizes string input to prevent XSS attacks and other
     * security vulnerabilities.
     *
     * @param  string|null  $input  The input string to sanitize
     *
     * @return string|null The sanitized string or null
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    private function sanitizeString(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    /**
     * Sanitize array data with XSS protection.
     *
     * Recursively sanitizes array data to prevent XSS attacks
     * and other security vulnerabilities.
     *
     * @param  array  $data  The data array to sanitize
     *
     * @return array The sanitized data array
     *
     * @version 1.0.6
     *
     *
     *
     *
     */
    private function sanitizeData(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeData($value);
            } elseif (is_string($value)) {
                $sanitized[$key] = $this->sanitizeString($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
}
