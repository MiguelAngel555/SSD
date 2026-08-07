package com.ssd.wear.services

import android.content.Context
import android.util.Log
import com.google.android.gms.wearable.MessageEvent
import com.ssd.wear.data.DeviceRegistrationRepository
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.launch

/**
 * Un solo lugar para decidir qué hacer con cada mensaje que manda el celular,
 * sin importar si llegó vía el WearableListenerService del manifest o vía un
 * listener registrado en vivo desde una Activity (MessageClient.addListener).
 *
 * Se necesitan las dos vías porque en emuladores el listener del manifest a
 * veces no se enlaza a tiempo (bug conocido de Wear OS emulator), así que
 * la Activity registra su propio listener mientras está en pantalla como
 * respaldo confiable.
 */
object MensajeHandler {

    const val RUTA_TOKEN = "/auth-token"
    const val RUTA_LOGOUT = "/logout"

    fun procesar(context: Context, scope: CoroutineScope, event: MessageEvent) {
        when (event.path) {
            RUTA_TOKEN -> {
                val token = String(event.data)
                Log.d("MensajeHandler", "Token de sesión recibido del celular")
                scope.launch {
                    DeviceRegistrationRepository.registrarConToken(context, token)
                }
            }
            RUTA_LOGOUT -> {
                Log.d("MensajeHandler", "Logout recibido del celular")
                scope.launch {
                    DeviceRegistrationRepository.cerrarSesion(context)
                }
            }
        }
    }
}
