<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producciones extends Model
{
    use SoftDeletes;
    protected $table = 'producciones';
    protected $primaryKey = 'id_produccion';
    protected $fillable = [
        'fecha_produccion',
        'observaciones',
    ];

    public function detalles()
    {
        return $this->hasMany(DetalleProducciones::class, 'id_produccion', 'id_produccion');
    }
}