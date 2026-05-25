<?php

use App\Models\Admin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('admins', 'role')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->string('role')->default(Admin::ROLE_ADMIN)->after('password');
            });
        }

        DB::table('admins')
            ->whereNull('role')
            ->update(['role' => Admin::ROLE_ADMIN]);
    }

    public function down(): void
    {
        if (Schema::hasColumn('admins', 'role')) {
            Schema::table('admins', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
