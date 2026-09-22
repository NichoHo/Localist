<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Indonesia spans three time zones (WIB/WITA/WIT); business hours need
        // the business's own city clock, not one hardcoded zone. Default WIB
        // (the most populous zone) so a city imported without one still works.
        Schema::table('cities', function (Blueprint $table) {
            $table->string('timezone')->default('Asia/Jakarta')->after('region');
        });
    }

    public function down(): void
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
