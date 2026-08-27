<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Asigna a un Revisor la responsabilidad de revisar todas las secuencias
 * de un cuatrimestre dentro de una carrera específica.
 */
class RevisorAsignacion extends Model
{
    protected $table = 'revisor_asignaciones';

    protected $fillable = ['revisor_id', 'carrera_id', 'cuatrimestre_id'];

    public function revisor()
    {
        return $this->belongsTo(User::class, 'revisor_id');
    }

    public function carrera()
    {
        return $this->belongsTo(Carrera::class);
    }

    public function cuatrimestre()
    {
        return $this->belongsTo(Cuatrimestre::class);
    }
}
