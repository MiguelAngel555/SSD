package com.ssd.wear.services

import android.util.Log
import com.google.android.gms.wearable.MessageEvent
import com.google.android.gms.wearable.WearableListenerService
import com.ssd.wear.data.DeviceRegistrationRepository
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch

class TokenReceiverService : WearableListenerService() {

    private val scope = CoroutineScope(Dispatchers.IO)

    override fun onMessageReceived(event: MessageEvent) {
        if (event.path != RUTA_TOKEN) return

        val token = String(event.data)
        Log.d("TokenReceiverService", "Token de sesión recibido del celular")

        scope.launch {
            DeviceRegistrationRepository.registrarConToken(applicationContext, token)
        }
    }

    companion object {
        private const val RUTA_TOKEN = "/auth-token"
    }
}
