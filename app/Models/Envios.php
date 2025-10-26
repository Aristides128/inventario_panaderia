<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Envios extends Model
{
    use SoftDeletes;
    protected $table = 'envios';
    protected $primaryKey = 'id_envio';
    protected $fillable = [
        'id_producto',
        'id_sucursal',
        'sucursal_destino_id',
        'cantidad',
        'observaciones',
    ];

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'id_producto');
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursales::class, 'id_sucursal');
    }

    public function sucursal_destino()
    {
        return $this->belongsTo(Sucursales::class, 'sucursal_destino_id');
    }
}