package com.ssd.mobile

import android.app.Application
import androidx.lifecycle.AndroidViewModel
import androidx.lifecycle.viewModelScope
import com.ssd.mobile.data.ApiClient
import com.ssd.mobile.data.LoginRequest
import com.ssd.mobile.data.ResultadoEnvioReloj
import com.ssd.mobile.data.SecureTokenStore
import com.ssd.mobile.data.TwoFactorVerifyRequest
import com.ssd.mobile.data.WatchPairing
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.StateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch

enum class Pantalla { LOGIN, DOS_FACTORES, CONFIRMACION }

data class AuthUiState(
    val pantalla: Pantalla = Pantalla.LOGIN,
    val cargando: Boolean = false,
    val error: String? = null,
    val challengeToken: String? = null,
    val relojConectado: Boolean? = null, // null = aún no se sabe / no se ha intentado
)

class AuthViewModel(application: Application) : AndroidViewModel(application) {

    private val tokenStore = SecureTokenStore(application)

    private val _uiState = MutableStateFlow(AuthUiState())
    val uiState: StateFlow<AuthUiState> = _uiState.asStateFlow()

    fun login(email: String, password: String) {
        _uiState.value = _uiState.value.copy(cargando = true, error = null)

        viewModelScope.launch {
            try {
                val respuesta = ApiClient.authApi.login(LoginRequest(email, password))
                val body = respuesta.body()

                if (!respuesta.isSuccessful || body == null) {
                    _uiState.value = _uiState.value.copy(
                        cargando = false,
                        error = "Correo o contraseña incorrectos.",
                    )
                    return@launch
                }

                if (body.requires_2fa) {
                    _uiState.value = _uiState.value.copy(
                        cargando = false,
                        pantalla = Pantalla.DOS_FACTORES,
                        challengeToken = body.challenge_token,
                    )
                } else {
                    onSesionIniciada(body.token)
                }
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    cargando = false,
                    error = "No se pudo conectar con el servidor. Verifica tu conexión.",
                )
            }
        }
    }

    fun verificarCodigo(codigo: String) {
        val challengeToken = _uiState.value.challengeToken ?: return
        _uiState.value = _uiState.value.copy(cargando = true, error = null)

        viewModelScope.launch {
            try {
                val respuesta = ApiClient.authApi.verificarCodigo(
                    TwoFactorVerifyRequest(challengeToken, codigo)
                )
                val body = respuesta.body()

                if (!respuesta.isSuccessful || body == null) {
                    _uiState.value = _uiState.value.copy(cargando = false, error = "Código incorrecto.")
                    return@launch
                }

                onSesionIniciada(body.token)
            } catch (e: Exception) {
                _uiState.value = _uiState.value.copy(
                    cargando = false,
                    error = "No se pudo verificar el código. Intenta de nuevo.",
                )
            }
        }
    }

    private fun onSesionIniciada(token: String?) {
        if (token == null) {
            _uiState.value = _uiState.value.copy(cargando = false, error = "Respuesta inválida del servidor.")
            return
        }

        tokenStore.guardarToken(token)
        _uiState.value = _uiState.value.copy(cargando = false, pantalla = Pantalla.CONFIRMACION)
        enviarTokenAlReloj(token)
    }

    fun enviarTokenAlReloj(token: String = tokenStore.obtenerToken().orEmpty()) {
        if (token.isEmpty()) return

        viewModelScope.launch {
            when (WatchPairing.enviarTokenAlReloj(getApplication(), token)) {
                is ResultadoEnvioReloj.Enviado ->
                    _uiState.value = _uiState.value.copy(relojConectado = true)
                is ResultadoEnvioReloj.SinRelojConectado, is ResultadoEnvioReloj.Error ->
                    _uiState.value = _uiState.value.copy(relojConectado = false)
            }
        }
    }

    fun logout() {
        val token = tokenStore.obtenerToken()
        _uiState.value = _uiState.value.copy(cargando = true)

        viewModelScope.launch {
            if (token != null) {
                try {
                    ApiClient.authApi.logout("Bearer $token")
                } catch (e: Exception) {
                    // Si no hay conexión, igual cerramos sesión localmente;
                    // el token en el servidor quedará huérfano pero inutilizable
                    // en la práctica ya que el usuario cerrará también en web.
                }
            }

            // Avisa al reloj para que borre su sesión y deje de estar registrado.
            WatchPairing.avisarLogoutAlReloj(getApplication())

            tokenStore.limpiar()
            _uiState.value = AuthUiState() // vuelve todo a su estado inicial (pantalla LOGIN)
        }
    }
}
