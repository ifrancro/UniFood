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
        Schema::table('pedidos', function (Blueprint $table) {
            $table->uuid('order_token')->nullable()->unique();
        });

        Schema::create('pedido_contadores', function (Blueprint $table) {
            $table->date('fecha')->primary();
            $table->unsignedBigInteger('ultimo_numero')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_contadores');

        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropUnique(['order_token']);
            $table->dropColumn('order_token');
        });
    }
};
