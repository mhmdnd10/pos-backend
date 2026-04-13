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
        Schema::table('products', function (Blueprint $table) {
            $table->string('model')->nullable()->after('brand');
            $table->string('search_text',500)->nullable()->after('description')->index();
            $table->string('shelf_location')->nullable()->after('minimum_stock_alert');
            
        });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('model');
            $table->dropColumn('search_text');
            $table->dropColumn('shelf_location');
        });
    }
};
