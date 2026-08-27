<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El Director también puede firmar digitalmente (dibujando su firma en
     * el navegador al validar), igual que ya podía hacerlo el Revisor.
     * Esa firma se estampa en la sección "Firma del director de carrera"
     * del PDF, de forma independiente a la firma del PTC/revisor.
     *
     * Simplificamos "documento_validacion_origen" a solo dos valores:
     * - 'archivo_subido': el documento final es un archivo que subieron
     *   (firmado a mano/escaneado).
     * - 'firma_digital': el documento final lo generó el sistema, con la(s)
     *   firma(s) digital(es) disponibles (del revisor, del director, o
     *   ambas). El PDF ya coloca cada firma en su celda correspondiente
     *   según qué campos tengan valor.
     */
    public function up(): void
    {
        Schema::table('secuencias', function (Blueprint $table) {
            $table->longText('director_firma_digital')->nullable()->after('revisor_firma_digital');
        });

        DB::statement("UPDATE secuencias SET documento_validacion_origen = 'firma_digital' WHERE documento_validacion_origen = 'firma_digital_revisor'");
        DB::statement("ALTER TABLE secuencias MODIFY documento_validacion_origen ENUM('archivo_subido', 'firma_digital') NULL");
    }

    public function down(): void
    {
        Schema::table('secuencias', function (Blueprint $table) {
            $table->dropColumn('director_firma_digital');
        });

        DB::statement("ALTER TABLE secuencias MODIFY documento_validacion_origen ENUM('archivo_subido', 'firma_digital', 'firma_digital_revisor') NULL");
    }
};
