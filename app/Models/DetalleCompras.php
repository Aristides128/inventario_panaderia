<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetalleCompras extends Model
{
    //
    use SoftDeletes;
    protected $table = 'detalle_compras';
    protected $primaryKey = 'id_detalle';
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

    public function Proveedores()
     {
        return $this->belongsTo(Proveedores::class, 'id_proveedor')->withDefault();
    }
     public function Productos()
     {
        return $this->belongsTo(Productos::class, 'id_producto')->withDefault();
    }
   
   
}
