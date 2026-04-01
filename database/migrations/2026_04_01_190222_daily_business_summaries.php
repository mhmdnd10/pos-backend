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
        Schema::create('daily_business_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained()->onDelete('cascade');
            $table->date('business_date');
            $table->decimal('total_sales',14,2)->default(0);
            $table->decimal('total_purchases', 14, 2)->default(0);
            $table->decimal('customer_payments', 14, 2)->default(0);
            $table->decimal('supplier_payments', 14, 2)->default(0);
            $table->unsignedInteger('sales_count')->default(0);
            $table->unsignedInteger('purchases_count')->default(0);
            $table->unique(['shop_id', 'business_date']);
            $table->index('shop_id','idx_shop_id');
            $table->index('business_date','idx_business_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_business_summaries');
    }
};
