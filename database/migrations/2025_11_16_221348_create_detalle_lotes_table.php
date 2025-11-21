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
        Schema::create('detalle_lotes', function (Blueprint $table) {
            $table->id('id_detalle_lote');
            $table->foreignId('id_lote')->constrained('lotes', 'id_lote')->onDelete('cascade');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->onDelete('cascade');
            $table->integer('cantidad')->default(0);
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
        Schema::dropIfExists('detalle_lotes');
    }
};
