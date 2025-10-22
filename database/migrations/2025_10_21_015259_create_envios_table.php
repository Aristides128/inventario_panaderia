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
        Schema::create('envios', function (Blueprint $table) {
            $table->id('id_envio');
            $table->foreignId('id_producto')->constrained('productos', 'id_producto')->onDelete('cascade');
            $table->foreignId('id_sucursal')->constrained('sucursales', 'id_sucursal')->onDelete('cascade');
            $table->foreignId('sucursal_destino_id')->constrained('sucursales', 'id_sucursal')->onDelete('cascade');
            $table->integer('cantidad')->default(0);
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
        Schema::dropIfExists('envios');
    }
};
