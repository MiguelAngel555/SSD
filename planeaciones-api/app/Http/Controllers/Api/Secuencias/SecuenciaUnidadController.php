<?php

namespace App\Http\Controllers\Api\Secuencias;

use App\Http\Controllers\Api\Concerns\VerificaEdicionSecuencia;
use App\Http\Controllers\Controller;
use App\Models\Secuencia;
use App\Models\SecuenciaUnidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class SecuenciaUnidadController extends Controller
{
    use VerificaEdicionSecuencia;

    /**
     * POST /api/docente/secuencias/{secuencia}/unidades
     * Agregar una unidad manualmente (caso sin PDF, o para ampliar la secuencia).
     */
    public function store(Request $request, Secuencia $secuencia)
    {
        try {
            $this->autorizarEdicion($secuencia, $request->user());

            $siguienteNumero = $secuencia->unidades()->max('numero') + 1;

            $unidad = $secuencia->unidades()->create([
                'numero' => $siguienteNumero,
                'nombre' => "Unidad {$siguienteNumero}",
                'proposito_esperado' => '',
                'horas_saber' => 0,
                'horas_saber_hacer' => 0,
                'horas_totales' => 0,
                'porcentaje_unidad' => 0,
            ]);

            $unidad->evaluacion()->create(['periodo_semanas' => 1, 'resultado_aprendizaje' => '']);
            foreach (['apertura', 'desarrollo', 'cierre'] as $fase) {
                $unidad->fases()->create(['fase' => $fase]);
            }

            return response()->json($unidad->fresh(['temas', 'evaluacion', 'evidencias', 'fases.actividades']), 201);
        } catch (Throwable $e) {
            Log::error('SecuenciaUnidadController@store: error al crear la unidad', [
                'secuencia_id' => $secuencia->id,
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo crear la unidad.'], 500);
        }
    }

    /**
     * PATCH /api/docente/unidades/{unidad}
     */
    public function update(Request $request, SecuenciaUnidad $unidad)
    {
        try {
            $this->autorizarEdicion($unidad->secuencia, $request->user());

            $data = $request->validate([
                'nombre' => ['sometimes', 'string', 'max:150'],
                'proposito_esperado' => ['sometimes', 'string'],
                'horas_saber' => ['sometimes', 'integer'],
                'horas_saber_hacer' => ['sometimes', 'integer'],
                'horas_totales' => ['sometimes', 'integer'],
                'porcentaje_unidad' => ['sometimes', 'numeric'],
            ]);

            $unidad->update($data);

            return response()->json($unidad->fresh());
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('SecuenciaUnidadController@update: error al actualizar la unidad', [
                'unidad_id' => $unidad->id,
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo actualizar la unidad.'], 500);
        }
    }
}
