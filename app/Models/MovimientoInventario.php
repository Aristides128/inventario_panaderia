<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MovimientoInventario extends Model
{
    use SoftDeletes;
    
    protected $table = 'movimientos_inventario';
    protected $primaryKey = 'id_movimiento';
    
    protected $fillable = [
        'id_producto',
        'id_lote',
        'tipo_movimiento',
        'cantidad',
        'cantidad_anterior',
        'cantidad_nueva',
        'referencia_tipo',
        'referencia_id',
        'observaciones',
        'usuario_id',
    ];

    // Relaciones
    public function producto()
    {
        return $this->belongsTo(Productos::class, 'id_producto', 'id_producto');
    }

    public function lote()
    {
        return $this->belongsTo(lotes::class, 'id_lote', 'id_lote');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id', 'id');
    }
}
