<?php

namespace App\Http\Controllers\Api\Secuencias;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Cuatrimestre;
use App\Models\RevisorAsignacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Administra a qué cuatrimestre(s) de qué carrera(s) queda asignado cada
 * Revisor. Administrador puede operar sobre cualquier carrera; Director y
 * Secretario solo sobre la carrera que dirigen/secretarían.
 */
class RevisorAsignacionController extends Controller
{
    /**
     * GET /api/revisor-asignaciones
     */
    public function index(Request $request)
    {
        try {
            $usuario = $request->user();
            $query = RevisorAsignacion::with([
                'revisor:id,nombre,apellido_paterno,apellido_materno',
                'carrera:id,nombre',
                'cuatrimestre:id,numero,nombre',
            ]);

            if (! $usuario->tieneRol('Administrador')) {
                $query->where('carrera_id', $this->carreraDelUsuario($usuario));
            }

            return response()->json($query->orderBy('carrera_id')->orderBy('cuatrimestre_id')->get());
        } catch (Throwable $e) {
            Log::error('RevisorAsignacionController@index: error al listar asignaciones', [
                'mensaje' => $e->getMessage(), 'linea' => $e->getLine(), 'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudieron cargar las asignaciones.'], 500);
        }
    }

    /**
     * GET /api/revisor-asignaciones/catalogos
     */
    public function catalogos(Request $request)
    {
        try {
            $usuario = $request->user();

            return response()->json([
                'revisores' => User::whereHas('roles', fn ($q) => $q->where('nombre', 'Revisor'))
                    ->orderBy('nombre')->get(['id', 'nombre', 'apellido_paterno', 'apellido_materno']),
                'cuatrimestres' => Cuatrimestre::orderBy('numero')->get(['id', 'numero', 'nombre']),
                'carreras' => $usuario->tieneRol('Administrador')
                    ? Carrera::where('activo', true)->orderBy('nombre')->get(['id', 'nombre'])
                    : [],
            ]);
        } catch (Throwable $e) {
            Log::error('RevisorAsignacionController@catalogos: error al cargar catálogos', [
                'mensaje' => $e->getMessage(), 'linea' => $e->getLine(), 'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudieron cargar los catálogos.'], 500);
        }
    }

    /**
     * POST /api/revisor-asignaciones
     * body: { revisor_id, cuatrimestre_id, carrera_id (solo Admin) }
     */
    public function store(Request $request)
    {
        try {
            $usuario = $request->user();
            $esAdmin = $usuario->tieneRol('Administrador');

            $data = $request->validate([
                'revisor_id' => ['required', 'exists:users,id'],
                'cuatrimestre_id' => ['required', 'exists:cuatrimestres,id'],
                'carrera_id' => [$esAdmin ? 'required' : 'nullable', 'exists:carreras,id'],
            ]);

            $carreraId = $esAdmin ? $data['carrera_id'] : $this->carreraDelUsuario($usuario);

            $esRevisor = User::whereKey($data['revisor_id'])
                ->whereHas('roles', fn ($q) => $q->where('nombre', 'Revisor'))
                ->exists();

            if (! $esRevisor) {
                throw ValidationException::withMessages([
                    'revisor_id' => ['El usuario seleccionado no tiene el rol de Revisor.'],
                ]);
            }

            $asignacion = RevisorAsignacion::firstOrCreate([
                'revisor_id' => $data['revisor_id'],
                'carrera_id' => $carreraId,
                'cuatrimestre_id' => $data['cuatrimestre_id'],
            ]);

            return response()->json($asignacion->load(['revisor', 'carrera', 'cuatrimestre']), 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('RevisorAsignacionController@store: error al crear la asignación', [
                'mensaje' => $e->getMessage(), 'linea' => $e->getLine(), 'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo crear la asignación.'], 500);
        }
    }

    /**
     * DELETE /api/revisor-asignaciones/{asignacion}
     */
    public function destroy(Request $request, RevisorAsignacion $asignacion)
    {
        try {
            $usuario = $request->user();

            if (! $usuario->tieneRol('Administrador') && $asignacion->carrera_id !== $this->carreraDelUsuario($usuario)) {
                abort(403, 'No puedes modificar asignaciones de otra carrera.');
            }

            $asignacion->delete();

            return response()->json(['message' => 'Asignación eliminada.']);
        } catch (Throwable $e) {
            Log::error('RevisorAsignacionController@destroy: error al eliminar la asignación', [
                'asignacion_id' => $asignacion->id,
                'mensaje' => $e->getMessage(), 'linea' => $e->getLine(), 'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo eliminar la asignación.'], 500);
        }
    }

    private function carreraDelUsuario(User $usuario): int
    {
        $carreraId = $usuario->carreraDirigida()->value('id') ?? $usuario->carreraSecretariada()->value('id');
        abort_unless($carreraId, 403, 'No tienes una carrera asignada.');

        return $carreraId;
    }
}
