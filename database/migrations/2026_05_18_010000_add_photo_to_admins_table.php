<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('admins', 'photo')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('photo')->nullable()->after('role');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('admins', 'photo')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('photo');
            });
        }
    }
};
