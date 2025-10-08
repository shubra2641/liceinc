<?php

use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**   * Run the migrations. */
    public function up(): void
    {
        // Create admin new user registration email template
        App\Models\EmailTemplate::create([
            'name' => 'admin_new_user_registration',
            'subject' => 'New User Registration - {{user_name}}',
            'body' => '<h2>New User Registration</h2>
<p>A new user has registered on your website.</p>

<h3>User Information:</h3>
<ul>
    <li><strong>Name:</strong> {{user_name}}</li>
    <li><strong>Email:</strong> {{user_email}}</li>
    <li><strong>First Name:</strong> {{user_firstname}}</li>
    <li><strong>Last Name:</strong> {{user_lastname}}</li>
    <li><strong>Phone:</strong> {{user_phone}}</li>
    <li><strong>Country:</strong> {{user_country}}</li>
    <li><strong>Registration Date:</strong> {{registration_date}}</li>
    <li><strong>IP Address:</strong> {{registration_ip}}</li>
    <li><strong>User Agent:</strong> {{user_agent}}</li>
</ul>

<p>You can view and manage users in your admin panel.</p>

<p>Best regards,<br>
{{site_name}} System</p>',
            'type' => 'admin',
            'category' => 'registration',
            'variables' => [
                'user_name',
                'user_email',
                'user_firstname',
                'user_lastname',
                'user_phone',
                'user_country',
                'registration_date',
                'registration_ip',
                'user_agent',
                'site_name',
            ],
            'is_active' => true,
            'description' => 'Email template sent to admin when a new user registers',
        ]);
    }

    /**   * Reverse the migrations. */
    public function down(): void
    {
        App\Models\EmailTemplate::where('name', 'admin_new_user_registration')->delete();
    }
};
