<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        if (! Schema::hasColumn('licenses', 'user_id')) {
            Schema::table('licenses', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('customer_id')->index();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('licenses', 'user_id')) {
            Schema::table('licenses', function (Blueprint $table) {
                // dropIndex requires the index name on some DBs; use dropColumn which will drop index too
                $table->dropColumn('user_id');
            });
        }
    }
};
