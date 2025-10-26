<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Producciones;
use App\Models\Productos;

class Detalleproducciones extends Model
{
    use SoftDeletes;

    protected $table = 'detalleproducciones';
    protected $primaryKey = 'id_detalle';

    
    protected $fillable = [
        'id_produccion',
        'id_producto',
        'cantidad_utilizada',
    ];

    public function produccion()    
    {
        return $this->hasMany(Producciones::class, 'id_produccion');
    }
    
    public function producto()    
    {
        return $this->belongsTo(Productos::class, 'id_producto');
    }
    
   
}