<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compras extends Model
{
    use SoftDeletes;
    protected $table = 'compras';
    protected $primaryKey = 'id_compra';
    protected $fillable = [
        'id_producto',
        'id_proveedor',
        'id_sucursal',
        'cantidad_paquetes',
        'cantidad_productos',
        'precio_total',
        'precio_unitario',
        'estado_compra',
        'observaciones',
        'fecha_vencimiento',
    ];
}