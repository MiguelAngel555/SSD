package com.ssd.wear

import android.Manifest
import android.os.Build
import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.wear.compose.material.MaterialTheme
import com.google.android.gms.wearable.MessageClient
import com.google.android.gms.wearable.MessageEvent
import com.google.android.gms.wearable.Wearable
import com.ssd.wear.data.DeviceRegistrationRepository
import com.ssd.wear.services.MensajeHandler
import com.ssd.wear.ui.NotificacionesScreen
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers

/**
 * Además del WearableListenerService del manifest (respaldo), esta Activity
 * registra su propio listener de mensajes mientras está en pantalla. Es la
 * vía principal y confiable: en emuladores, el listener declarado en el
 * manifest a veces no se enlaza a tiempo y el mensaje del celular se pierde
 * aunque la app esté abierta.
 */
class MainActivity : ComponentActivity(), MessageClient.OnMessageReceivedListener {

    private val scope = CoroutineScope(Dispatchers.IO)

    private val solicitarPermiso =
        registerForActivityResult(ActivityResultContracts.RequestPermission()) { /* no-op */ }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            solicitarPermiso.launch(Manifest.permission.POST_NOTIFICATIONS)
        }

        DeviceRegistrationRepository.cargarEstadoInicial(applicationContext)

        setContent {
            MaterialTheme {
                val estado by DeviceRegistrationRepository.estado.collectAsState()
                NotificacionesScreen(estado = estado)
            }
        }
    }

    override fun onResume() {
        super.onResume()
        Wearable.getMessageClient(this).addListener(this)
    }

    override fun onPause() {
        Wearable.getMessageClient(this).removeListener(this)
        super.onPause()
    }

    override fun onMessageReceived(event: MessageEvent) {
        MensajeHandler.procesar(applicationContext, scope, event)
    }
}
