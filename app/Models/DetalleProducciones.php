<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Producciones;
use App\Models\Productos;

class DetalleProducciones extends Model
{
    use SoftDeletes;

    protected $table = 'detalle_producciones';
    protected $primaryKey = 'id_detalle';

    protected $fillable = [
        'id_produccion',
        'id_producto',
        'id_empleado',
        'cantidad_utilizada',
    ];

    public function Produccion()    
    {
        return $this->belongsTo(Producciones::class, 'id_produccion');
    }
    
    public function Producto()    
    {
        return $this->belongsTo(Productos::class, 'id_producto');
    }

    public function Empleado()
    {
        return $this->belongsTo(Empleado::class, 'id_empleado');
    }
}