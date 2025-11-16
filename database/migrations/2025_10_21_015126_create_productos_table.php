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
         Schema::create('productos', function (Blueprint $table) {
            $table->id('id_producto');
            $table->string('nombre', 100);
            $table->string('descripcion', 255);
            $table->foreignId('id_categoria')->constrained('categorias', 'id_categoria')->onDelete('cascade');
            $table->integer('stock_actual')->default(0);
            $table->enum('unidad_medida', ['kilogramo', 'unidad', 'libras' ,'onzas', 'gramos', 'litros'])->default('unidad');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
