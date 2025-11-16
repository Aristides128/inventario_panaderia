<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id('id_compra');
            $table->date('fecha_compra');
            $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal')->onDelete('cascade');
            $table->enum('estado_compra', ['Pendiente', 'Recibido', 'Cancelado'])->default('Pendiente');
            $table->decimal('total', 10, 2)->default(0);
            $table->string('observaciones', 255)->nullable();
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
