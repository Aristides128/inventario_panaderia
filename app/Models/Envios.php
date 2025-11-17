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
        'id_sucursal',
        'sucursal_destino_id',
        'observaciones',
        'fecha_envio',
    ];

    public function sucursal_origen()
    {
        return $this->belongsTo(Sucursales::class, 'id_sucursal');
    }

    public function sucursal_destino()
    {
        return $this->belongsTo(Sucursales::class, 'sucursal_destino_id');
    }

    public function detalle_envios()
    {
        return $this->hasMany(detalle_envios::class, 'id_envio');
    }
}