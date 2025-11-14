<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categorias extends Model
{
    use SoftDeletes;
    
    protected $table = 'categorias';
    protected $primaryKey = 'id_categoria';
    protected $fillable = ['nombre', 'descripcion', 'deleted_at'];
    protected $dates = ['deleted_at'];

    public function productos()
    {
        return $this->hasMany(Productos::class, 'id_categoria');
    }
}