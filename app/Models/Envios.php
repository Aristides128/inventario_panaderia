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
}