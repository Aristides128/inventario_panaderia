<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class detalle_lotes extends Model
{
    //
    use SoftDeletes;
    protected $table = 'detalle_lotes';
    protected $primaryKey = 'id_detalle_lote';
    protected $fillable = [
        'id_lote',
        'id_producto',
        'cantidad',
        'fecha_vencimiento',
    ];
    public function lote()
    {
        return $this->belongsTo(lotes::class, 'id_lote');
    }
    public function producto()
    {
        return $this->belongsTo(Productos::class, 'id_producto');
    }
}
