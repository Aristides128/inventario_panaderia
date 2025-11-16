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
        'estado_compra',
        'observaciones',
    ];

    public function producto()
    {
        return $this->belongsTo(Productos::class, 'id_producto');
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