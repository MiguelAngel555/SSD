package com.ssd.wear.services

import com.google.android.gms.wearable.DataEvent
import com.google.android.gms.wearable.DataEventBuffer
import com.google.android.gms.wearable.DataMapItem
import com.google.android.gms.wearable.WearableListenerService
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers

/**
 * Escucha los DataItem que manda la app del celular vía DataClient.
 * A diferencia de MessageClient, estos se sincronizan de forma persistente
 * y no requieren que ambos procesos estén vivos en el mismo instante.
 */
class TokenReceiverService : WearableListenerService() {

    private val scope = CoroutineScope(Dispatchers.IO)

    override fun onDataChanged(dataEvents: DataEventBuffer) {
        try {
            for (event in dataEvents) {
                if (event.type != DataEvent.TYPE_CHANGED) continue

                when (event.dataItem.uri.path) {
                    MensajeHandler.RUTA_TOKEN -> {
                        val dataMap = DataMapItem.fromDataItem(event.dataItem).dataMap
                        val token = dataMap.getString("token_key")
                        if (!token.isNullOrEmpty()) {
                            MensajeHandler.procesarToken(applicationContext, scope, token)
                        }
                    }
                    MensajeHandler.RUTA_LOGOUT -> {
                        MensajeHandler.procesarLogout(applicationContext, scope)
                    }
                }
            }
        } finally {
            // Buena práctica: DataEventBuffer mantiene un cursor nativo que
            // hay que liberar explícitamente para evitar fugas de memoria.
            dataEvents.release()
        }
    }
}
