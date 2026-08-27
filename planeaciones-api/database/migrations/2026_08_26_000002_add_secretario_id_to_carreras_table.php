<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Igual que director_id: el Secretario asignado a una carrera puede
     * gestionar (dar de alta/editar) a los docentes de esa carrera, igual
     * que el Director.
     */
    public function up(): void
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->foreignId('secretario_id')->nullable()->after('director_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('carreras', function (Blueprint $table) {
            $table->dropConstrainedForeignId('secretario_id');
        });
    }
};
