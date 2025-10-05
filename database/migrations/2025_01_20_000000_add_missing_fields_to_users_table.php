<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if table exists before modifying it
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                // Personal Information Fields
                if (! Schema::hasColumn('users', 'firstname')) {
                    $table->string('firstname')->nullable()->after('name');
                }
                if (! Schema::hasColumn('users', 'lastname')) {
                    $table->string('lastname')->nullable()->after('firstname');
                }
                if (! Schema::hasColumn('users', 'companyname')) {
                    $table->string('companyname')->nullable()->after('lastname');
                }

                // Address Fields
                if (! Schema::hasColumn('users', 'address1')) {
                    $table->text('address1')->nullable()->after('companyname');
                }
                if (! Schema::hasColumn('users', 'address2')) {
                    $table->text('address2')->nullable()->after('address1');
                }
                if (! Schema::hasColumn('users', 'city')) {
                    $table->string('city')->nullable()->after('address2');
                }
                if (! Schema::hasColumn('users', 'state')) {
                    $table->string('state')->nullable()->after('city');
                }
                if (! Schema::hasColumn('users', 'postcode')) {
                    $table->string('postcode')->nullable()->after('state');
                }
                if (! Schema::hasColumn('users', 'country')) {
                    $table->string('country')->nullable()->after('postcode');
                }

                // Contact Information
                if (! Schema::hasColumn('users', 'phonenumber')) {
                    $table->string('phonenumber')->nullable()->after('country');
                }
                if (! Schema::hasColumn('users', 'currency')) {
                    $table->string('currency', 3)->default('USD')->after('phonenumber');
                }

                // Additional Fields
                if (! Schema::hasColumn('users', 'notes')) {
                    $table->text('notes')->nullable()->after('currency');
                }
                if (! Schema::hasColumn('users', 'cardnum')) {
                    $table->string('cardnum')->nullable()->after('notes');
                }
                if (! Schema::hasColumn('users', 'startdate')) {
                    $table->date('startdate')->nullable()->after('cardnum');
                }
                if (! Schema::hasColumn('users', 'expdate')) {
                    $table->date('expdate')->nullable()->after('startdate');
                }
                if (! Schema::hasColumn('users', 'lastlogin')) {
                    $table->timestamp('lastlogin')->nullable()->after('expdate');
                }
                if (! Schema::hasColumn('users', 'status')) {
                    $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('lastlogin');
                }
                if (! Schema::hasColumn('users', 'language')) {
                    $table->string('language', 5)->default('en')->after('status');
                }

                // SSO and Email Preferences
                if (! Schema::hasColumn('users', 'allow_sso')) {
                    $table->boolean('allow_sso')->default(false)->after('language');
                }
                if (! Schema::hasColumn('users', 'email_verified')) {
                    $table->boolean('email_verified')->default(false)->after('allow_sso');
                }
                if (! Schema::hasColumn('users', 'email_preferences')) {
                    $table->json('email_preferences')->nullable()->after('email_verified');
                }

                // Password Reset Fields
                if (! Schema::hasColumn('users', 'pwresetkey')) {
                    $table->string('pwresetkey')->nullable()->after('email_preferences');
                }
                if (! Schema::hasColumn('users', 'pwresetexpiry')) {
                    $table->timestamp('pwresetexpiry')->nullable()->after('pwresetkey');
                }

                // Financial Fields
                if (! Schema::hasColumn('users', 'credit')) {
                    $table->decimal('credit', 10, 2)->default(0.00)->after('pwresetexpiry');
                }
                if (! Schema::hasColumn('users', 'taxexempt')) {
                    $table->boolean('taxexempt')->default(false)->after('credit');
                }
                if (! Schema::hasColumn('users', 'latefeeoveride')) {
                    $table->boolean('latefeeoveride')->default(false)->after('taxexempt');
                }
                if (! Schema::hasColumn('users', 'overideduenotices')) {
                    $table->boolean('overideduenotices')->default(false)->after('latefeeoveride');
                }
                if (! Schema::hasColumn('users', 'separateinvoices')) {
                    $table->boolean('separateinvoices')->default(false)->after('overideduenotices');
                }
                if (! Schema::hasColumn('users', 'disableautocc')) {
                    $table->boolean('disableautocc')->default(false)->after('separateinvoices');
                }
                if (! Schema::hasColumn('users', 'emailoptout')) {
                    $table->boolean('emailoptout')->default(false)->after('disableautocc');
                }
                if (! Schema::hasColumn('users', 'marketing_emails_opt_in')) {
                    $table->boolean('marketing_emails_opt_in')->default(false)->after('emailoptout');
                }
                if (! Schema::hasColumn('users', 'overrideautoclose')) {
                    $table->boolean('overrideautoclose')->default(false)->after('marketing_emails_opt_in');
                }

                // Timestamps
                if (! Schema::hasColumn('users', 'datecreated')) {
                    $table->timestamp('datecreated')->nullable()->after('overrideautoclose');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'firstname',
                    'lastname',
                    'companyname',
                    'address1',
                    'address2',
                    'city',
                    'state',
                    'postcode',
                    'country',
                    'phonenumber',
                    'currency',
                    'notes',
                    'cardnum',
                    'startdate',
                    'expdate',
                    'lastlogin',
                    'status',
                    'language',
                    'allow_sso',
                    'email_verified',
                    'email_preferences',
                    'pwresetkey',
                    'pwresetexpiry',
                    'credit',
                    'taxexempt',
                    'latefeeoveride',
                    'overideduenotices',
                    'separateinvoices',
                    'disableautocc',
                    'emailoptout',
                    'marketing_emails_opt_in',
                    'overrideautoclose',
                    'datecreated',
                ]);
            });
        }
    }
};
