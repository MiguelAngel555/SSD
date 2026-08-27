<?php

namespace App\Http\Controllers\Api\Usuario;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\ConfirmacionCuenta;
use App\Models\Role;
use App\Models\User;
use App\Notifications\NuevaCuentaNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Versión acotada de UserController para Director y Secretario: solo
 * pueden ver/crear/editar usuarios con rol Docente, y solo si sus materias
 * pertenecen a la carrera que dirigen/secretarían. Nunca pueden tocar
 * otros roles ni docentes de otra carrera.
 */
class DocenteCarreraController extends Controller
{
    /**
     * GET /api/carrera/docentes?q=&page=
     */
    public function index(Request $request)
    {
        try {
            $carreraId = $this->carreraDelUsuario($request->user());

            $docentes = User::query()
                ->with(['roles', 'asignaturas'])
                ->whereHas('roles', fn ($q) => $q->where('nombre', 'Docente'))
                ->whereHas('asignaturas.especialidades', fn ($q) => $q->where('carrera_id', $carreraId))
                ->when($request->filled('q'), function ($query) use ($request) {
                    $q = $request->q;
                    $query->where(fn ($w) => $w->where('nombre', 'like', "%{$q}%")
                        ->orWhere('apellido_paterno', 'like', "%{$q}%")
                        ->orWhere('apellido_materno', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%"));
                })
                ->orderBy('nombre')
                ->paginate(10);

            return response()->json($docentes);
        } catch (Throwable $e) {
            Log::error('DocenteCarreraController@index: error al listar docentes', [
                'mensaje' => $e->getMessage(), 'linea' => $e->getLine(), 'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudieron cargar los docentes.'], 500);
        }
    }

    /**
     * GET /api/carrera/docentes/catalogos
     * Solo las asignaturas de la carrera propia (a diferencia del catálogo global de Admin).
     */
    public function catalogos(Request $request)
    {
        try {
            $carreraId = $this->carreraDelUsuario($request->user());

            return response()->json([
                'asignaturas' => Asignatura::with('cuatrimestre')
                    ->where('activo', true)
                    ->whereHas('especialidades', fn ($q) => $q->where('carrera_id', $carreraId))
                    ->orderBy('nombre')
                    ->get(['id', 'nombre', 'clave', 'cuatrimestre_id']),
            ]);
        } catch (Throwable $e) {
            Log::error('DocenteCarreraController@catalogos: error al cargar catálogos', [
                'mensaje' => $e->getMessage(), 'linea' => $e->getLine(), 'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudieron cargar los catálogos.'], 500);
        }
    }

    /**
     * POST /api/carrera/docentes
     */
    public function store(Request $request)
    {
        try {
            $carreraId = $this->carreraDelUsuario($request->user());
            $data = $this->validarDatos($request, $carreraId);
            $rolDocenteId = Role::where('nombre', 'Docente')->value('id');

            $passwordTemporal = Str::password(12);

            $docente = DB::transaction(function () use ($data, $rolDocenteId, $passwordTemporal) {
                $nuevo = User::create([
                    'nombre' => $data['nombre'],
                    'apellido_paterno' => $data['apellido_paterno'],
                    'apellido_materno' => $data['apellido_materno'] ?? null,
                    'email' => $data['email'],
                    'password' => Hash::make($passwordTemporal),
                ]);

                $nuevo->roles()->sync([$rolDocenteId]);
                $nuevo->asignaturas()->sync($data['asignatura_ids'] ?? []);

                return $nuevo;
            });

            $this->enviarCredenciales($docente, $passwordTemporal);

            return response()->json($docente->fresh(['roles', 'asignaturas']), 201);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('DocenteCarreraController@store: error al crear el docente', [
                'datos' => $request->except('password'),
                'mensaje' => $e->getMessage(), 'linea' => $e->getLine(), 'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo crear el docente.'], 500);
        }
    }

    /**
     * PUT /api/carrera/docentes/{usuario}
     */
    public function update(Request $request, User $usuario)
    {
        try {
            $carreraId = $this->carreraDelUsuario($request->user());
            $this->verificarPerteneceACarrera($usuario, $carreraId);

            $data = $this->validarDatos($request, $carreraId, $usuario->id);

            $usuario->update([
                'nombre' => $data['nombre'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'] ?? null,
                'email' => $data['email'],
            ]);

            $usuario->asignaturas()->sync($data['asignatura_ids'] ?? []);

            return response()->json($usuario->fresh(['roles', 'asignaturas']));
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('DocenteCarreraController@update: error al actualizar el docente', [
                'usuario_id' => $usuario->id,
                'mensaje' => $e->getMessage(), 'linea' => $e->getLine(), 'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo actualizar el docente.'], 500);
        }
    }

    // ── helpers ──────────────────────────────────────────────

    private function validarDatos(Request $request, int $carreraId, ?int $ignorarId = null): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($ignorarId)],
            'asignatura_ids' => ['nullable', 'array'],
            'asignatura_ids.*' => ['exists:asignaturas,id'],
        ]);

        if (! empty($data['asignatura_ids'])) {
            $asignaturasDeLaCarrera = Asignatura::whereIn('id', $data['asignatura_ids'])
                ->whereHas('especialidades', fn ($q) => $q->where('carrera_id', $carreraId))
                ->pluck('id')
                ->all();

            if (count($asignaturasDeLaCarrera) !== count($data['asignatura_ids'])) {
                throw ValidationException::withMessages([
                    'asignatura_ids' => ['Solo puedes asignar materias que pertenezcan a tu carrera.'],
                ]);
            }
        }

        return $data;
    }

    private function verificarPerteneceACarrera(User $usuario, int $carreraId): void
    {
        $perteneceHoy = $usuario->asignaturas()
            ->whereHas('especialidades', fn ($q) => $q->where('carrera_id', $carreraId))
            ->exists();

        // Un docente sin materias asignadas aún (recién creado) también se
        // permite editar si además tiene el rol Docente y ninguna materia
        // de otra carrera, para no bloquear el primer alta de materias.
        $tieneMateriaDeOtraCarrera = $usuario->asignaturas()
            ->whereDoesntHave('especialidades', fn ($q) => $q->where('carrera_id', $carreraId))
            ->exists();

        abort_unless(
            $usuario->tieneRol('Docente') && ($perteneceHoy || (! $tieneMateriaDeOtraCarrera)),
            403,
            'Ese docente no pertenece a tu carrera.'
        );
    }

    private function carreraDelUsuario(User $usuario): int
    {
        $carreraId = $usuario->carreraDirigida()->value('id') ?? $usuario->carreraSecretariada()->value('id');
        abort_unless($carreraId, 403, 'No tienes una carrera asignada.');

        return $carreraId;
    }

    private function enviarCredenciales(User $usuario, string $passwordTemporal): void
    {
        $confirmacion = ConfirmacionCuenta::updateOrCreate(
            ['user_id' => $usuario->id],
            ['token' => Str::random(64), 'expires_at' => now()->addDays(7)]
        );

        $link = $usuario->email_verified_at
            ? null
            : rtrim(config('app.frontend_url'), '/') . '/confirmar-cuenta?token=' . $confirmacion->token;

        $usuario->notify(new NuevaCuentaNotification($passwordTemporal, $link));
    }
}
