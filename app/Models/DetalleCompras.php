<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleCompras extends Model
{
    //
    use SoftDeletes;
    protected $table = 'detalle_compras';
    protected $primaryKey = 'id_detalle_compra';
    protected $fillable = [
        'id_compra',
        'id_proveedor',
        'id_producto',
        'cantidad_paquetes',
        'cantidad_producto',
        'precio_unitario',
        'subtotal',
        'fecha_vencimiento',
    ];
}
