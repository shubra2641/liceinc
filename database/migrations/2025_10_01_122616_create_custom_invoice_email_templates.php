<?php

use App\Models\EmailTemplate;
use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Custom invoice payment confirmation for user
        EmailTemplate::create([
            'name' => 'custom_invoice_payment_confirmation',
            'subject' => 'Payment Confirmation - Custom Service',
            'body' => '<h2>Payment Confirmation</h2>
            <p>Dear {{customer_name}},</p>
            <p>Thank you for your payment! Your custom service payment has been successfully processed.</p>
            
            <h3>Payment Details:</h3>
            <ul>
                <li><strong>Invoice Number:</strong> {{invoice_number}}</li>
                <li><strong>Service Description:</strong> {{service_description}}</li>
                <li><strong>Amount:</strong> {{currency}} {{amount}}</li>
                <li><strong>Payment Method:</strong> {{payment_method}}</li>
                <li><strong>Payment Date:</strong> {{payment_date}}</li>
                <li><strong>Transaction ID:</strong> {{transaction_id}}</li>
            </ul>
            
            <p>Your payment has been received and the service will be processed accordingly.</p>
            <p>If you have any questions or need support, please don\'t hesitate to contact us.</p>
            
            <p>Best regards,<br>
            {{site_name}} Team</p>',
            'type' => 'user',
            'category' => 'payment',
            'variables' => [
                'customer_name', 'customer_email', 'invoice_number', 'service_description',
                'amount', 'currency', 'payment_method', 'payment_date', 'transaction_id', 'site_name',
            ],
            'is_active' => true,
            'description' => 'Email template sent to user when custom invoice payment is successful',
        ]);

        // Admin notification for custom invoice payment
        EmailTemplate::create([
            'name' => 'admin_custom_invoice_payment',
            'subject' => 'New Custom Invoice Payment - {{customer_name}}',
            'body' => '<h2>New Custom Invoice Payment Received</h2>
            <p>Dear Administrator,</p>
            <p>A new custom invoice payment has been successfully processed.</p>
            
            <h3>Customer Information:</h3>
            <ul>
                <li><strong>Name:</strong> {{customer_name}}</li>
                <li><strong>Email:</strong> {{customer_email}}</li>
            </ul>
            
            <h3>Payment Details:</h3>
            <ul>
                <li><strong>Invoice Number:</strong> {{invoice_number}}</li>
                <li><strong>Service Description:</strong> {{service_description}}</li>
                <li><strong>Amount:</strong> {{currency}} {{amount}}</li>
                <li><strong>Payment Method:</strong> {{payment_method}}</li>
                <li><strong>Transaction ID:</strong> {{transaction_id}}</li>
                <li><strong>Payment Date:</strong> {{payment_date}}</li>
            </ul>
            
            <p>You can view the full details in your admin panel.</p>
            
            <p>Best regards,<br>
            {{site_name}} System</p>',
            'type' => 'admin',
            'category' => 'payment',
            'variables' => [
                'customer_name', 'customer_email', 'invoice_number', 'service_description',
                'amount', 'currency', 'payment_method', 'transaction_id', 'payment_date', 'site_name',
            ],
            'is_active' => true,
            'description' => 'Email template sent to admin when custom invoice payment is successful',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        EmailTemplate::whereIn('name', [
            'custom_invoice_payment_confirmation',
            'admin_custom_invoice_payment',
        ])->delete();
    }
};
