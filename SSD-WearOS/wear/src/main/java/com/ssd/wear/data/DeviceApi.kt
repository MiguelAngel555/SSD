package com.ssd.wear.data

import retrofit2.Response
import retrofit2.http.Body
import retrofit2.http.Header
import retrofit2.http.POST

data class DeviceTokenRequest(
    val fcm_token: String,
    val plataforma: String = "wearos",
)

interface DeviceApi {
    @POST("dispositivo/fcm-token")
    suspend fun registrarToken(
        @Header("Authorization") bearer: String,
        @Body body: DeviceTokenRequest,
    ): Response<Unit>
}
