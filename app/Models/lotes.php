<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class lotes extends Model
{
    //
    use SoftDeletes;
    protected $table = 'lotes';
    protected $primaryKey = 'id_lote';
    protected $fillable = [
        'semana',
        'mes',
        'anio',
    ];
    public function producto()
    {
        return $this->belongsTo(Productos::class, 'id_producto');
    }
}
