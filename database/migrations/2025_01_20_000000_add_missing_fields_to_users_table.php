<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            // Personal information fields
            if (!Schema::hasColumn('users', 'firstname')) {
                $table->string('firstname')->nullable()->after('name');
            }
            if (!Schema::hasColumn('users', 'lastname')) {
                $table->string('lastname')->nullable()->after('firstname');
            }
            if (!Schema::hasColumn('users', 'companyname')) {
                $table->string('companyname')->nullable()->after('lastname');
            }

            // Address fields
            if (!Schema::hasColumn('users', 'address1')) {
                $table->text('address1')->nullable()->after('companyname');
            }
            if (!Schema::hasColumn('users', 'address2')) {
                $table->text('address2')->nullable()->after('address1');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address2');
            }
            if (!Schema::hasColumn('users', 'state')) {
                $table->string('state')->nullable()->after('city');
            }
            if (!Schema::hasColumn('users', 'postcode')) {
                $table->string('postcode')->nullable()->after('state');
            }
            if (!Schema::hasColumn('users', 'country')) {
                $table->string('country')->nullable()->after('postcode');
            }

            // Contact fields
            if (!Schema::hasColumn('users', 'phonenumber')) {
                $table->string('phonenumber')->nullable()->after('country');
            }
            if (!Schema::hasColumn('users', 'currency')) {
                $table->string('currency', 3)->default('USD')->after('phonenumber');
            }

            // Additional fields
            if (!Schema::hasColumn('users', 'notes')) {
                $table->text('notes')->nullable()->after('currency');
            }
            if (!Schema::hasColumn('users', 'cardnum')) {
                $table->string('cardnum')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('users', 'startdate')) {
                $table->date('startdate')->nullable()->after('cardnum');
            }
            if (!Schema::hasColumn('users', 'expdate')) {
                $table->date('expdate')->nullable()->after('startdate');
            }
            if (!Schema::hasColumn('users', 'lastlogin')) {
                $table->timestamp('lastlogin')->nullable()->after('expdate');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended'])->default('active')->after('lastlogin');
            }
            if (!Schema::hasColumn('users', 'language')) {
                $table->string('language', 5)->default('en')->after('status');
            }

            // Email and SSO fields
            if (!Schema::hasColumn('users', 'allow_sso')) {
                $table->boolean('allow_sso')->default(false)->after('language');
            }
            if (!Schema::hasColumn('users', 'email_verified')) {
                $table->boolean('email_verified')->default(false)->after('allow_sso');
            }
            if (!Schema::hasColumn('users', 'email_preferences')) {
                $table->json('email_preferences')->nullable()->after('email_verified');
            }

            // Password reset fields
            if (!Schema::hasColumn('users', 'password_reset_token')) {
                $table->string('password_reset_token')->nullable()->after('email_preferences');
            }
            if (!Schema::hasColumn('users', 'password_reset_expires')) {
                $table->timestamp('password_reset_expires')->nullable()->after('password_reset_token');
            }

            // Financial fields
            if (!Schema::hasColumn('users', 'balance')) {
                $table->decimal('balance', 10, 2)->default(0)->after('password_reset_expires');
            }
            if (!Schema::hasColumn('users', 'credit_limit')) {
                $table->decimal('credit_limit', 10, 2)->nullable()->after('balance');
            }

            // Timestamp fields
            if (!Schema::hasColumn('users', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('credit_limit');
            }
            if (!Schema::hasColumn('users', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'firstname', 'lastname', 'companyname',
                'address1', 'address2', 'city', 'state', 'postcode', 'country',
                'phonenumber', 'currency',
                'notes', 'cardnum', 'startdate', 'expdate', 'lastlogin', 'status', 'language',
                'allow_sso', 'email_verified', 'email_preferences',
                'password_reset_token', 'password_reset_expires',
                'balance', 'credit_limit',
                'created_by', 'updated_by'
            ]);
        });
    }
};