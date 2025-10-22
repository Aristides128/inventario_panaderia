<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sucursales extends Model
{
    use SoftDeletes;
    protected $table = 'sucursales';
    protected $primaryKey = 'id_sucursal';
    protected $fillable = [
        'nombre',
        'direccion',
    ];
}