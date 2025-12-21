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
        Schema::table('detalle_producciones', function (Blueprint $table) {
            $table->foreignId('id_empleado')->nullable()->after('id_producto')->constrained('empleados', 'id_empleado')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalle_producciones', function (Blueprint $table) {
            $table->dropForeign(['id_empleado']);
            $table->dropColumn('id_empleado');
        });
    }
};
