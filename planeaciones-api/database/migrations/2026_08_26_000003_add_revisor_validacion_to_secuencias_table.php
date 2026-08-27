<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * El "PTC que valida" del formato UTH-ACA-DC-F-PVSD/14 es el Revisor,
     * no el Director. El revisor puede firmar digitalmente en el momento en
     * que envía la secuencia a validación (enviar-validacion); si lo hace,
     * esa firma se reutiliza automáticamente en el documento final y el
     * Director ya no puede "firmar digitalmente" en su lugar.
     */
    public function up(): void
    {
        Schema::table('secuencias', function (Blueprint $table) {
            $table->foreignId('revisor_validacion_id')->nullable()->after('documento_validacion_origen')
                ->constrained('users')->nullOnDelete();
            $table->longText('revisor_firma_digital')->nullable()->after('revisor_validacion_id');
        });

        // Nuevo origen posible: el documento quedó firmado con la firma
        // digital que el REVISOR puso al enviar a validación (distinto de
        // 'firma_digital', que antes representaba la firma del Director).
        DB::statement("ALTER TABLE secuencias MODIFY documento_validacion_origen ENUM('archivo_subido', 'firma_digital', 'firma_digital_revisor') NULL");
    }

    public function down(): void
    {
        Schema::table('secuencias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('revisor_validacion_id');
            $table->dropColumn('revisor_firma_digital');
        });

        DB::statement("ALTER TABLE secuencias MODIFY documento_validacion_origen ENUM('archivo_subido', 'firma_digital') NULL");
    }
};
