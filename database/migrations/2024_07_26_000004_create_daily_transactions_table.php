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
        Schema::create('daily_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('agent_id', 16);
            $table->char('product_id', 64);
            $table->foreign('agent_id')->references('id')->on('agents')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict')->onUpdate('cascade');
            $table->char('source_account', 64);
            $table->double('nominal');
            $table->double('admin_fee');
            $table->double('total');
            $table->enum('status', ['FAILED', 'SUCCESS', 'SUSPECT']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_transactions');
    }
};
