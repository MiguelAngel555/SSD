<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Carrera extends Model
{
    protected $table = 'carreras';
    
    use SoftDeletes;

    protected $fillable = ['nombre', 'clave', 'director_id', 'secretario_id', 'activo'];

    public function director()
    {
        return $this->belongsTo(User::class, 'director_id');
    }

    public function secretario()
    {
        return $this->belongsTo(User::class, 'secretario_id');
    }

    public function especialidades()
    {
        return $this->hasMany(Especialidad::class);
    }

    public function secuencias()
    {
        return $this->hasMany(Secuencia::class);
    }
}
