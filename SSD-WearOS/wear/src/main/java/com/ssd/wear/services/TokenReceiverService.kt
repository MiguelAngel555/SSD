package com.ssd.wear.services

import com.google.android.gms.wearable.MessageEvent
import com.google.android.gms.wearable.WearableListenerService
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers

/**
 * Vía de respaldo declarada en el manifest: la usa el sistema cuando la app
 * no está en primer plano. En emuladores puede fallar en enlazarse a tiempo;
 * la vía principal en la práctica es el listener en vivo de MainActivity.
 */
class TokenReceiverService : WearableListenerService() {

    private val scope = CoroutineScope(Dispatchers.IO)

    override fun onMessageReceived(event: MessageEvent) {
        MensajeHandler.procesar(applicationContext, scope, event)
    }
}
