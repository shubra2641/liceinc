<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;

class EmailTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $templates = [
            // USER TEMPLATES
            [
                'name' => 'user_email_verification',
                'subject' => 'Verify Your Email Address - {{site_name}}',
                'body' => '<h2>Verify Your Email Address</h2>' .
                    '<p>Hello {{user_name}},</p>' .
                    '<p>Thank you for registering with {{site_name}}! To complete your registration ' .
                    'and activate your account, please verify your email address by clicking the button below:</p>' .
                    '<div style="text-align: center; margin: 30px 0;">' .
                    '<a href="{{verification_url}}" class="email-button">Verify Email Address</a></div>' .
                    '<div class="highlight-box"><h4>Important Information</h4>' .
                    '<p>This verification link will expire on <strong>{{verification_expires}}</strong>.</p></div>',
                'type' => 'user',
                'category' => 'registration',
                'variables' => ['verification_url', 'verification_expires', 'user_name'],
                'is_active' => true,
                'description' => 'Email verification sent to new users after registration',
            ],
            [
                'name' => 'user_password_reset',
                'subject' => 'Reset Your Password - {{site_name}}',
                'body' => '<h2>Reset Your Password</h2><p>Hello {{user_name}},</p>' .
                    '<p>We received a request to reset your password for your {{site_name}} account. ' .
                    'Click the button below to reset your password:</p>' .
                    '<div style="text-align: center; margin: 30px 0;">' .
                    '<a href="{{reset_url}}" class="email-button">Reset Password</a></div>',
                'type' => 'user',
                'category' => 'registration',
                'variables' => ['reset_url', 'reset_expires', 'user_name'],
                'is_active' => true,
                'description' => 'Password reset email sent to users',
            ],
            [
                'name' => 'user_welcome',
                'subject' => 'Welcome to {{site_name}}!',
                'body' => '<h2>Welcome to {{site_name}}!</h2><p>Hello {{user_name}},</p>' .
                    '<p>Welcome to {{site_name}}! We\'re excited to have you as part of our community. ' .
                    'Your account was successfully created on {{registration_date}}.</p>' .
                    '<div style="text-align: center; margin: 30px 0;">' .
                    '<a href="{{site_url}}/dashboard" class="email-button">Go to Dashboard</a></div>',
                'type' => 'user',
                'category' => 'registration',
                'variables' => ['user_name', 'registration_date', 'site_name'],
                'is_active' => true,
                'description' => 'Welcome email sent to new users after successful registration',
            ],
            [
                'name' => 'user_license_created',
                'subject' => 'New License Created - {{product_name}}',
                'body' => '<h2>New License Created</h2><p>Hello {{user_name}},</p>' .
                    '<p>Great news! A new license has been created for you. Here are the details:</p>' .
                    '<table class="email-table"><tr><th>Product</th><td>{{product_name}}</td></tr>' .
                    '<tr><th>License Key</th><td style="font-family: monospace;">{{license_key}}</td></tr>' .
                    '<tr><th>Expires</th><td>{{expires_at}}</td></tr></table>',
                'type' => 'user',
                'category' => 'license',
                'variables' => ['license_key', 'product_name', 'expires_at', 'user_name'],
                'is_active' => true,
                'description' => 'Notification sent when a new license is created for user',
            ],
            [
                'name' => 'user_license_expiring',
                'subject' => 'License Expiring Soon - {{product_name}}',
                'body' => '<h2>License Expiring Soon</h2><p>Hello {{user_name}},</p>' .
                    '<p>Your license for <strong>{{product_name}}</strong> will expire in ' .
                    '{{days_remaining}} days on {{expires_at}}.</p>' .
                    '<div class="info-box warning"><h4>Action Required</h4>' .
                    '<p>To continue using this product without interruption, ' .
                    'please renew your license before it expires.</p></div>',
                'type' => 'user',
                'category' => 'license',
                'variables' => ['license_key', 'product_name', 'expires_at', 'days_remaining', 'user_name'],
                'is_active' => true,
                'description' => 'Warning sent when license is about to expire',
            ],
            [
                'name' => 'user_license_updated',
                'subject' => 'License Updated - {{product_name}}',
                'body' => '<h2>License Updated</h2><p>Hello {{user_name}},</p>' .
                    '<p>Your license for <strong>{{product_name}}</strong> has been {{update_type}}.</p>',
                'type' => 'user',
                'category' => 'license',
                'variables' => ['license_key', 'product_name', 'update_type', 'user_name'],
                'is_active' => true,
                'description' => 'Notification sent when license is updated',
            ],
            [
                'name' => 'user_product_version_update',
                'subject' => 'Product Update Available - {{product_name}}',
                'body' => '<h2>Product Update Available</h2><p>Hello {{user_name}},</p>' .
                    '<p>A new version of <strong>{{product_name}}</strong> is now available! ' .
                    'We\'ve released version {{new_version}} with new features and improvements.</p>',
                'type' => 'user',
                'category' => 'product',
                'variables' => ['product_name', 'old_version', 'new_version', 'download_url', 'user_name'],
                'is_active' => true,
                'description' => 'Notification sent when product version is updated',
            ],
            [
                'name' => 'user_ticket_created',
                'subject' => 'Support Ticket Created - #{{ticket_id}}',
                'body' => '<h2>Support Ticket Created</h2><p>Hello {{user_name}},</p>' .
                    '<p>Thank you for contacting our support team. We\'ve received your support ticket ' .
                    'and will get back to you as soon as possible.</p>',
                'type' => 'user',
                'category' => 'ticket',
                'variables' => ['ticket_id', 'ticket_subject', 'ticket_status', 'user_name'],
                'is_active' => true,
                'description' => 'Confirmation sent when user creates a support ticket',
            ],
            [
                'name' => 'user_ticket_status_update',
                'subject' => 'Ticket Status Updated - #{{ticket_id}}',
                'body' => '<h2>Ticket Status Updated</h2><p>Hello {{user_name}},</p>' .
                    '<p>The status of your support ticket has been updated.</p>',
                'type' => 'user',
                'category' => 'ticket',
                'variables' => ['ticket_id', 'ticket_subject', 'old_status', 'new_status', 'user_name'],
                'is_active' => true,
                'description' => 'Notification sent when ticket status is updated',
            ],
            [
                'name' => 'user_ticket_reply',
                'subject' => 'New Reply to Your Ticket - #{{ticket_id}}',
                'body' => '<h2>New Reply to Your Ticket</h2><p>Hello {{user_name}},</p>' .
                    '<p>You have received a new reply to your support ticket from {{replied_by}}.</p>',
                'type' => 'user',
                'category' => 'ticket',
                'variables' => ['ticket_id', 'ticket_subject', 'reply_message', 'replied_by', 'user_name'],
                'is_active' => true,
                'description' => 'Notification sent when there is a reply to user ticket',
            ],
            [
                'name' => 'user_invoice_approaching_due',
                'subject' => 'Invoice Due Soon - #{{invoice_number}}',
                'body' => '<h2>Invoice Due Soon</h2><p>Hello {{user_name}},</p>' .
                    '<p>This is a friendly reminder that your invoice #{{invoice_number}} ' .
                    'is due in {{days_remaining}} days on {{due_date}}.</p>',
                'type' => 'user',
                'category' => 'invoice',
                'variables' => ['invoice_number', 'invoice_amount', 'due_date', 'days_remaining', 'user_name'],
                'is_active' => true,
                'description' => 'Reminder sent when invoice is approaching due date',
            ],
            [
                'name' => 'user_invoice_paid',
                'subject' => 'Invoice Paid - #{{invoice_number}}',
                'body' => '<h2>Invoice Paid - Thank You!</h2><p>Hello {{user_name}},</p>' .
                    '<p>Thank you for your payment! We\'ve successfully processed your payment ' .
                    'for invoice #{{invoice_number}}.</p>',
                'type' => 'user',
                'category' => 'invoice',
                'variables' => ['invoice_number', 'invoice_amount', 'payment_date', 'payment_method', 'user_name'],
                'is_active' => true,
                'description' => 'Confirmation sent when invoice is paid',
            ],
            [
                'name' => 'user_invoice_cancelled',
                'subject' => 'Invoice Cancelled - #{{invoice_number}}',
                'body' => '<h2>Invoice Cancelled</h2><p>Hello {{user_name}},</p>' .
                    '<p>Your invoice #{{invoice_number}} has been cancelled.</p>',
                'type' => 'user',
                'category' => 'invoice',
                'variables' => ['invoice_number', 'invoice_amount', 'cancellation_reason', 'user_name'],
                'is_active' => true,
                'description' => 'Notification sent when invoice is cancelled',
            ],

            // ADMIN TEMPLATES
            [
                'name' => 'admin_license_created',
                'subject' => 'New License Created - {{product_name}}',
                'body' => '<h2>New License Created</h2><p>A new license has been created in the system.</p>' .
                    '<table class="email-table"><tr><th>Product</th><td>{{product_name}}</td></tr>' .
                    '<tr><th>License Key</th><td style="font-family: monospace;">{{license_key}}</td></tr>' .
                    '<tr><th>Customer</th><td>{{customer_name}} ({{customer_email}})</td></tr></table>',
                'type' => 'admin',
                'category' => 'license',
                'variables' => ['license_key', 'product_name', 'customer_name', 'customer_email'],
                'is_active' => true,
                'description' => 'Notification sent to admin when new license is created',
            ],
            [
                'name' => 'admin_license_expiring',
                'subject' => 'License Expiring Soon - {{product_name}}',
                'body' => '<h2>License Expiring Soon</h2>' .
                    '<p>A customer license is about to expire and may need attention.</p>',
                'type' => 'admin',
                'category' => 'license',
                'variables' => [
                    'license_key', 'product_name', 'customer_name',
                    'customer_email', 'expires_at', 'days_remaining'
                ],
                'is_active' => true,
                'description' => 'Warning sent to admin when license is about to expire',
            ],
            [
                'name' => 'admin_license_renewed',
                'subject' => 'License Renewed - {{product_name}}',
                'body' => '<h2>License Renewed</h2><p>A customer has renewed their license.</p>',
                'type' => 'admin',
                'category' => 'license',
                'variables' => ['license_key', 'product_name', 'customer_name', 'customer_email', 'new_expires_at'],
                'is_active' => true,
                'description' => 'Notification sent to admin when license is renewed',
            ],
            [
                'name' => 'admin_ticket_created',
                'subject' => 'New Support Ticket - #{{ticket_id}}',
                'body' => '<h2>New Support Ticket</h2><p>A new support ticket has been created by a customer.</p>',
                'type' => 'admin',
                'category' => 'ticket',
                'variables' => ['ticket_id', 'ticket_subject', 'customer_name', 'customer_email', 'ticket_priority'],
                'is_active' => true,
                'description' => 'Notification sent to admin when new ticket is created',
            ],
            [
                'name' => 'admin_ticket_reply',
                'subject' => 'Ticket Reply from Customer - #{{ticket_id}}',
                'body' => '<h2>Customer Reply to Ticket</h2><p>A customer has replied to their support ticket.</p>',
                'type' => 'admin',
                'category' => 'ticket',
                'variables' => ['ticket_id', 'ticket_subject', 'customer_name', 'customer_email', 'reply_message'],
                'is_active' => true,
                'description' => 'Notification sent to admin when customer replies to ticket',
            ],
            [
                'name' => 'admin_ticket_closed',
                'subject' => 'Ticket Closed by Customer - #{{ticket_id}}',
                'body' => '<h2>Ticket Closed by Customer</h2><p>A customer has closed their support ticket.</p>',
                'type' => 'admin',
                'category' => 'ticket',
                'variables' => ['ticket_id', 'ticket_subject', 'customer_name', 'customer_email', 'closure_reason'],
                'is_active' => true,
                'description' => 'Notification sent to admin when customer closes ticket',
            ],
            [
                'name' => 'admin_invoice_approaching_due',
                'subject' => 'Invoice Due Soon - #{{invoice_number}}',
                'body' => '<h2>Invoice Due Soon</h2>' .
                    '<p>A customer invoice is approaching its due date.</p>',
                'type' => 'admin',
                'category' => 'invoice',
                'variables' => [
                    'invoice_number', 'invoice_amount', 'customer_name',
                    'customer_email', 'due_date', 'days_remaining'
                ],
                'is_active' => true,
                'description' => 'Reminder sent to admin when invoice is approaching due date',
            ],
            [
                'name' => 'admin_invoice_cancelled',
                'subject' => 'Invoice Cancelled - #{{invoice_number}}',
                'body' => '<h2>Invoice Cancelled</h2>' .
                    '<p>A customer invoice has been cancelled.</p>',
                'type' => 'admin',
                'category' => 'invoice',
                'variables' => [
                    'invoice_number', 'invoice_amount', 'customer_name',
                    'customer_email', 'cancellation_reason'
                ],
                'is_active' => true,
                'description' => 'Notification sent to admin when invoice is cancelled',
            ],
        ];

        foreach ($templates as $template) {
            EmailTemplate::updateOrCreate(
                ['name' => $template['name']],
                $template,
            );
        }
    }
}
