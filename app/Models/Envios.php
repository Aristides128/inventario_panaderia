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

    public function Sucursales()
    {
        return $this->belongsTo(Sucursales::class, 'id_sucursal');
    }
    public function Productos()
    {
        return $this->belongsTo(Productos::class, 'id_producto');
    }

  
}