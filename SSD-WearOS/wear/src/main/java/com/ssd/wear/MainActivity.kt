package com.ssd.wear

import android.Manifest
import android.os.Build
import android.os.Bundle
import android.util.Log // <-- 1. Importa la clase Log
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.activity.result.contract.ActivityResultContracts
import androidx.compose.runtime.collectAsState
import androidx.compose.runtime.getValue
import androidx.wear.compose.material.MaterialTheme
import com.ssd.wear.data.DeviceRegistrationRepository
import com.ssd.wear.ui.NotificacionesScreen

class MainActivity : ComponentActivity() {

    private val solicitarPermiso =
        registerForActivityResult(ActivityResultContracts.RequestPermission()) { /* no-op */ }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        // 2. Agrega este Log.i para confirmar el inicio de la app
        Log.i("MainActivityWear", "=== APLICACIÓN WEAR OS INICIADA CORRECTAMENTE ===")

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
}