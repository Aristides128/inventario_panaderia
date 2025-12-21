<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Empleado extends Model
{
    use SoftDeletes;

    protected $table = 'empleados';
    protected $primaryKey = 'id_empleado';

    protected $fillable = [
        'nombre',
        'puesto',
        'telefono',
        'estado',
    ];

    /**
     * Get the production details associated with the employee.
     */
    public function detalleProducciones(): HasMany
    {
        return $this->hasMany(DetalleProducciones::class, 'id_empleado', 'id_empleado');
    }
}
