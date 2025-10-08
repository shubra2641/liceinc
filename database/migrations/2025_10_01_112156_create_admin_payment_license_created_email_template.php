<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        App\Models\EmailTemplate::create([
            'name' => 'admin_payment_license_created',
            'subject' => 'New Payment & License Created - {{customer_name}}',
            'body' => '<h2>New Payment & License Created</h2>
            <p>A new payment has been processed and a license has been created.</p>
            
            <h3>Customer Information:</h3>
            <ul>
                <li><strong>Name:</strong> {{customer_name}}</li>
                <li><strong>Email:</strong> {{customer_email}}</li>
            </ul>
            
            <h3>Product Information:</h3>
            <ul>
                <li><strong>Product:</strong> {{product_name}}</li>
                <li><strong>License Key:</strong> <code style="background: #e9ecef; padding: 4px 8px; border-radius: 4px;">{{license_key}}</code></li>
                <li><strong>License Type:</strong> {{license_type}}</li>
                <li><strong>Max Domains:</strong> {{max_domains}}</li>
            </ul>
            
            <h3>Payment Information:</h3>
            <ul>
                <li><strong>Invoice Number:</strong> {{invoice_number}}</li>
                <li><strong>Amount:</strong> ${{amount}} {{currency}}</li>
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
                'customer_name', 'customer_email', 'product_name', 'license_key',
                'license_type', 'max_domains', 'invoice_number', 'amount', 'currency',
                'payment_method', 'transaction_id', 'payment_date', 'site_name',
            ],
            'is_active' => true,
            'description' => 'Email template sent to admin when a payment is processed and license is created',
        ]);
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        App\Models\EmailTemplate::where('name', 'admin_payment_license_created')->delete();
    }
};
