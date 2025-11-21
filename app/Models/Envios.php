<?php

namespace App\Models;

use App\Models\DetalleEnvio;
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

    public function Sucursal()
    {
        return $this->belongsTo(Sucursales::class, 'id_sucursal');
    }
    public function Productos()
    {
        return $this->belongsTo(Productos::class, 'id_producto');
    }

    public function Sucursal_destino()
    {
        return $this->belongsTo(Sucursales::class, 'sucursal_destino_id');
    }

    public function Detalle_envios()
    {
        return $this->hasMany(DetalleEnvio::class, 'id_envio');
    }
}