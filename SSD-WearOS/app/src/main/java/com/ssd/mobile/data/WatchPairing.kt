package com.ssd.mobile.data

import android.content.Context
import com.google.android.gms.wearable.DataMap
import com.google.android.gms.wearable.PutDataMapRequest
import com.google.android.gms.wearable.Wearable
import kotlinx.coroutines.tasks.await

/**
 * Resultado de intentar avisarle al reloj. Si no hay ningún nodo conectado
 * (reloj apagado, no emparejado, o app aún no instalada ahí), lo reportamos
 * para que la UI lo muestre en vez de fallar en silencio.
 */
sealed class ResultadoEnvioReloj {
    data object Enviado : ResultadoEnvioReloj()
    data object SinRelojConectado : ResultadoEnvioReloj()
    data class Error(val mensaje: String) : ResultadoEnvioReloj()
}

object WatchPairing {

    private const val RUTA_TOKEN = "/auth-token"
    private const val RUTA_LOGOUT = "/logout"

    suspend fun enviarTokenAlReloj(context: Context, token: String): ResultadoEnvioReloj {
        return enviarDataItem(context, RUTA_TOKEN) { dataMap ->
            dataMap.putString("token_key", token)
        }
    }

    /** Avisa al reloj que debe borrar su sesión y dejar de estar registrado para push. */
    suspend fun avisarLogoutAlReloj(context: Context): ResultadoEnvioReloj {
        return enviarDataItem(context, RUTA_LOGOUT) { /* sin payload extra */ }
    }

    private suspend fun enviarDataItem(
        context: Context,
        ruta: String,
        armarPayload: (DataMap) -> Unit,
    ): ResultadoEnvioReloj {
        return try {
            val nodeClient = Wearable.getNodeClient(context)
            val nodos = nodeClient.connectedNodes.await()

            if (nodos.isEmpty()) {
                return ResultadoEnvioReloj.SinRelojConectado
            }

            val request = PutDataMapRequest.create(ruta).apply {
                armarPayload(dataMap)
                // Forzamos que el DataItem cuente como "cambiado" aunque el
                // contenido (p. ej. el mismo token tras un logout/login) sea
                // idéntico al anterior. Sin esto, Wear OS deduplica por
                // contenido y onDataChanged nunca se vuelve a disparar.
                dataMap.putLong("timestamp", System.currentTimeMillis())
            }.asPutDataRequest().setUrgent()

            // DataClient encola el DataItem de forma persistente: si el reloj
            // no está disponible en este instante, se sincroniza en cuanto
            // vuelva a conectarse. No depende de que ambos procesos estén
            // vivos exactamente al mismo tiempo, a diferencia de sendMessage.
            Wearable.getDataClient(context).putDataItem(request).await()

            ResultadoEnvioReloj.Enviado
        } catch (e: Exception) {
            ResultadoEnvioReloj.Error(e.message ?: "No se pudo comunicar con el reloj.")
        }
    }
}
