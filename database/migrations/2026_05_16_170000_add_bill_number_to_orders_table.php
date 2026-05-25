<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'bill_number')) {
                $table->string('bill_number')->nullable()->unique()->after('md5');
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'bill_number')) {
                $table->dropUnique('orders_bill_number_unique');
                $table->dropColumn('bill_number');
            }
        });
    }
};
