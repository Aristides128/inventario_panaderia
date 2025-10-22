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
      Schema::create('compras', function (Blueprint $table) {
            $table->id('id_compra');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->onDelete('cascade');
            $table->foreignId('id_proveedor')->constrained('proveedores', 'id_proveedor')->onDelete('cascade');
            $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal')->onDelete('cascade');
            $table->integer('cantidad_paquetes')->default(0)->nullable();
            $table->integer('cantidad_producto')->default(0);
            $table->decimal('precio_total', 10, 2)->default(0);
            $table->decimal('precio_unitario', 10, 2)->default(0)->nullable();
            $table->enum('estado_compra', ['Pendiente', 'Recibido', 'Cancelado'])->default('Pendiente');
            $table->string('observaciones', 255)->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};
