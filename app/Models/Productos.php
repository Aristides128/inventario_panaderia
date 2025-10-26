<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Productos extends Model
{
    use SoftDeletes;
    protected $table = 'productos';
    protected $primaryKey = 'id_producto';
    protected $fillable = [
        'nombre',
        'descripcion',
        'id_categoria',
        'stock',
    ];
    public function categoria()
    {
        return $this->belongsTo(Categorias::class, 'id_categoria');
    }
}