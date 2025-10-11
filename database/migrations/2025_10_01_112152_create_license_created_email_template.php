<?php

use Illuminate\Database\Migrations\Migration;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        App\Models\EmailTemplate::create([
            'name' => 'license_created',
            'subject' => 'Your License Has Been Created - {{product_name}}',
            'body' => '<h2>License Created Successfully!</h2>
            <p>Dear {{customer_name}},</p>
            <p>Your license has been successfully created for the following product:</p>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3>License Details:</h3>
                <ul>
                    <li><strong>Product:</strong> {{product_name}}</li>
                    <li><strong>License Key:</strong> <code style="background: #e9ecef; padding: 4px 8px; border-radius: 4px;">{{license_key}}</code></li>
                    <li><strong>License Type:</strong> {{license_type}}</li>
                    <li><strong>Max Domains:</strong> {{max_domains}}</li>
                    <li><strong>License Expires:</strong> {{license_expires_at}}</li>
                    <li><strong>Support Expires:</strong> {{support_expires_at}}</li>
                    <li><strong>Created Date:</strong> {{created_date}}</li>
                </ul>
            </div>
            
            <p>You can now use this license key to activate your product. Please keep this email safe as it contains your license information.</p>
            
            <p>If you have any questions or need support, please don\'t hesitate to contact us.</p>
            
            <p>Best regards,<br>
            {{site_name}} Team</p>',
            'type' => 'user',
            'category' => 'license',
            'variables' => [
                'customer_name', 'customer_email', 'product_name', 'license_key',
                'license_type', 'max_domains', 'license_expires_at', 'support_expires_at',
                'created_date', 'site_name',
            ],
            'is_active' => true,
            'description' => 'Email template sent to user when a new license is created',
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        App\Models\EmailTemplate::where('name', 'license_created')->delete();
    }
};
