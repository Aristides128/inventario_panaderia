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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id('id_movimiento');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->onDelete('cascade');
            $table->foreignId('id_lote')->nullable()->constrained('lotes', 'id_lote')->onDelete('set null');
            $table->enum('tipo_movimiento', ['ENTRADA', 'SALIDA',]);
            $table->integer('cantidad');
            $table->integer('cantidad_anterior')->comment('Stock antes del movimiento');
            $table->integer('cantidad_nueva')->comment('Stock después del movimiento');
            $table->enum('referencia_tipo', ['COMPRA', 'PRODUCCION', 'ENVIO', 'AJUSTE'])->nullable();
            $table->unsignedBigInteger('referencia_id')->nullable()->comment('ID de la compra, producción, envío, etc.');
            $table->text('observaciones')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users', 'id')->onDelete('set null');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para mejorar rendimiento de consultas
            $table->index(['id_producto', 'created_at']);
            $table->index(['tipo_movimiento', 'created_at']);
            $table->index(['referencia_tipo', 'referencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
