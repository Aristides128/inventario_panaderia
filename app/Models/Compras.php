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
        'fecha_compra',
        'id_sucursal',
        'total',
        'estado_compra',
        'observaciones',
    ];

    public function detalle_compra()
    {
        return $this->belongsTo(DetalleCompras::class, 'id_detalle_compra');
    }
    public function proveedor()
    {
        return $this->belongsTo(Proveedores::class, 'id_proveedor');
    }
    public function sucursal()
    {
        return $this->belongsTo(Sucursales::class, 'id_sucursal');
    }
}