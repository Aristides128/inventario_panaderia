<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleEnvio extends Model
{
    //

    use SoftDeletes;
    protected $table = 'detalle_envios';
    protected $primaryKey = 'id_detalle_envio';
    protected $fillable = [
        'id_envio',
        'id_producto',
        'cantidad',
    ];

    public function envio()
    {
        return $this->belongsTo(envios::class, 'id_envio');
    }
    public function producto()
    {
        return $this->belongsTo(Productos::class, 'id_producto');
    }
}
