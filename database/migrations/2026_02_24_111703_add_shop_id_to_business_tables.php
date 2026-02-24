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
        $tables=[
            'users',
            'products',
            'categories',
            'sales',
            'sale_items',
            'purchases',
            'purchase_items',
            'customers',
            'suppliers',
            'customer_ledgers',
            'supplier_ledgers',
            'customer_payments',
            'supplier_payments',
        ];
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->foreignId('shop_id')->after('id')->constrained('shops')->onDelete('cascade');
                $table->index('shop_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_tables', function (Blueprint $table) {
            //
        });
    }
};
