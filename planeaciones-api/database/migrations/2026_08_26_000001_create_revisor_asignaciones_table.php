<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A un Revisor se le asigna revisar un cuatrimestre completo dentro de
     * una carrera (todas las asignaturas de ese cuatrimestre en esa carrera
     * caen bajo su cola de revisión). Un mismo revisor puede tener varias
     * asignaciones (distintos cuatrimestres y/o carreras).
     */
    public function up(): void
    {
        Schema::create('revisor_asignaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('revisor_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete();
            $table->foreignId('cuatrimestre_id')->constrained('cuatrimestres')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['revisor_id', 'carrera_id', 'cuatrimestre_id'], 'revisor_asig_unica');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('revisor_asignaciones');
    }
};
