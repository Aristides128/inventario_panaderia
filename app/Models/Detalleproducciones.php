<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
    
   
}