<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Throwable;

class ProfileController extends Controller
{
    /**
     * GET /api/perfil
     * Datos propios del usuario autenticado, para pintar/editar en la vista de perfil.
     */
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'nombre' => $user->nombre,
            'apellido_paterno' => $user->apellido_paterno,
            'apellido_materno' => $user->apellido_materno,
            'nombre_completo' => $user->nombre_completo,
            'email' => $user->email,
            'roles' => $user->roles()->pluck('nombre'),
            'carrera_dirigida' => $user->carreraDirigida?->nombre,
            'asignaturas' => $user->asignaturas()->pluck('nombre'),
            'two_factor_enabled' => (bool) $user->two_factor_confirmed_at,
            'two_factor_method' => $user->two_factor_method,
        ]);
    }

    /**
     * PUT /api/perfil
     * El usuario solo puede editar su nombre y apellidos. El correo institucional
     * no se toca aquí a propósito: está ligado al login, al 2FA y a las notificaciones.
     */
    public function update(Request $request)
    {
        try {
            $data = $request->validate([
                'nombre' => ['required', 'string', 'max:100'],
                'apellido_paterno' => ['required', 'string', 'max:100'],
                'apellido_materno' => ['nullable', 'string', 'max:100'],
            ]);

            $user = $request->user();
            $user->update($data);

            return response()->json([
                'message' => 'Tus datos se actualizaron correctamente.',
                'nombre_completo' => $user->fresh()->nombre_completo,
            ]);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('ProfileController@update: error al actualizar el perfil', [
                'user_id' => $request->user()?->id,
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo actualizar tu perfil.'], 500);
        }
    }

    /**
     * PUT /api/perfil/password
     * Requiere la contraseña actual. Al cambiarla cerramos sesión en los demás
     * dispositivos/pestañas por seguridad, pero dejamos viva la sesión actual
     * desde la que se hizo el cambio (para no repetir el bug de "me vota").
     */
    public function updatePassword(Request $request)
    {
        try {
            $data = $request->validate([
                'password_actual' => ['required', 'string'],
                'password' => ['required', 'string', 'min:8', 'confirmed'],
            ]);

            $user = $request->user();

            if (! Hash::check($data['password_actual'], $user->password)) {
                throw ValidationException::withMessages([
                    'password_actual' => ['La contraseña actual no es correcta.'],
                ]);
            }

            $user->update(['password' => Hash::make($data['password'])]);

            $tokenActual = $request->user()->currentAccessToken();
            $user->tokens()
                ->when($tokenActual, fn($q) => $q->where('id', '!=', $tokenActual->id))
                ->delete();

            return response()->json(['message' => 'Tu contraseña se actualizó correctamente.']);
        } catch (ValidationException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('ProfileController@updatePassword: error al cambiar la contraseña', [
                'user_id' => $request->user()?->id,
                'mensaje' => $e->getMessage(),
                'linea' => $e->getLine(),
                'archivo' => $e->getFile(),
            ]);

            return response()->json(['message' => 'No se pudo actualizar tu contraseña.'], 500);
        }
    }
}
