<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('purchases_archive', 'payment_status')) {
            Schema::table('purchases_archive', function (Blueprint $table) {
                $table->enum('payment_status', ['paid', 'partial', 'unpaid'])
                    ->default('unpaid')
                    ->after('remaining_amount');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('purchases_archive', 'payment_status')) {
            Schema::table('purchases_archive', function (Blueprint $table) {
                $table->dropColumn('payment_status');
            });
        }
    }
};
