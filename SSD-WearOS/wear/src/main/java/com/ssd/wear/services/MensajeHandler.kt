package com.ssd.wear.services

import android.content.Context
import android.util.Log
import com.ssd.wear.data.DeviceRegistrationRepository
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.launch

object MensajeHandler {

    const val RUTA_TOKEN = "/auth-token"
    const val RUTA_LOGOUT = "/logout"

    fun procesarToken(context: Context, scope: CoroutineScope, token: String) {
        Log.d("MensajeHandler", "Token de sesión recibido")
        scope.launch {
            try {
                DeviceRegistrationRepository.registrarConToken(context, token)
            } catch (e: Exception) {
                Log.e("MensajeHandler", "Error al registrar el token en el repositorio", e)
            }
        }
    }

    fun procesarLogout(context: Context, scope: CoroutineScope) {
        Log.d("MensajeHandler", "Logout recibido")
        scope.launch {
            try {
                DeviceRegistrationRepository.cerrarSesion(context)
            } catch (e: Exception) {
                Log.e("MensajeHandler", "Error al procesar el cierre de sesión en el repositorio", e)
            }
        }
    }
}
