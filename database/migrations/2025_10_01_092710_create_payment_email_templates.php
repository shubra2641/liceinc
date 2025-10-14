<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Payment Confirmation Email Template
        App\Models\EmailTemplate::create([
            'name' => 'payment_confirmation',
            'subject' => 'Payment Confirmation - {{product_name}}',
            'body' => '<h2>Payment Confirmation</h2>
            <p>Dear {{customer_name}},</p>
            <p>Thank you for your purchase! Your payment has been successfully processed.</p>
            
            <h3>Order Details:</h3>
            <ul>
                <li><strong>Order Number:</strong> {{order_number}}</li>
                <li><strong>Product:</strong> {{product_name}}</li>
                <li><strong>Amount:</strong> {{currency}} {{amount}}</li>
                <li><strong>Payment Method:</strong> {{payment_method}}</li>
                <li><strong>Payment Date:</strong> {{payment_date}}</li>
            </ul>
            
            <h3>License Information:</h3>
            <ul>
                <li><strong>License Key:</strong> {{license_key}}</li>
                <li><strong>Expires:</strong> {{license_expires_at}}</li>
            </ul>
            
            <h3>Invoice:</h3>
            <p>Invoice Number: {{invoice_number}}</p>
            
            <p>You can now download your product and start using it with the provided license key.</p>
            <p>If you have any questions, please don\'t hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            {{site_name}} Team</p>',
            'type' => 'user',
            'category' => 'payment',
            'variables' => [
                'customer_name', 'customer_email', 'product_name', 'order_number',
                'license_key', 'invoice_number', 'amount', 'currency', 'payment_method',
                'payment_date', 'license_expires_at', 'site_name',
            ],
            'is_active' => true,
            'description' => 'Email template sent to customer after successful payment',
        ]);

        // Admin Payment Failure Notification Template
        App\Models\EmailTemplate::create([
            'name' => 'admin_payment_failure',
            'subject' => 'Payment Failure Alert - Order {{order_number}}',
            'body' => '<h2>Payment Failure Alert</h2>
            <p>A payment has failed and requires your attention.</p>
            
            <h3>Order Details:</h3>
            <ul>
                <li><strong>Order Number:</strong> {{order_number}}</li>
                <li><strong>Customer:</strong> {{customer_name}} ({{customer_email}})</li>
                <li><strong>Product:</strong> {{product_name}}</li>
                <li><strong>Amount:</strong> {{currency}} {{amount}}</li>
                <li><strong>Payment Method:</strong> {{payment_method}}</li>
                <li><strong>Failure Date:</strong> {{failure_date}}</li>
                <li><strong>Failure Reason:</strong> {{failure_reason}}</li>
            </ul>
            
            <p>Please review this order and contact the customer if necessary.</p>
            
            <p>Best regards,<br>
            {{site_name}} System</p>',
            'type' => 'admin',
            'category' => 'payment',
            'variables' => [
                'customer_name', 'customer_email', 'product_name', 'order_number',
                'amount', 'currency', 'payment_method', 'failure_reason', 'failure_date', 'site_name',
            ],
            'is_active' => true,
            'description' => 'Email template sent to admin when payment fails',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        App\Models\EmailTemplate::whereIn('name', ['payment_confirmation', 'admin_payment_failure'])->delete();
    }
};
