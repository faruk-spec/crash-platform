package com.crashgame.platform.data.remote

import com.google.gson.annotations.SerializedName
import retrofit2.http.Body
import retrofit2.http.GET
import retrofit2.http.Header
import retrofit2.http.POST
import retrofit2.http.Query

data class ReleaseResponse(
    @SerializedName("has_update") val hasUpdate: Boolean,
    @SerializedName("version_name") val versionName: String,
    @SerializedName("version_code") val versionCode: Int,
    @SerializedName("download_url") val downloadUrl: String,
    @SerializedName("sha256_checksum") val sha256Checksum: String,
    @SerializedName("release_notes") val releaseNotes: String?,
    @SerializedName("is_mandatory") val isMandatory: Boolean
)

data class BatchSyncRequest(
    @SerializedName("batch_uuid") val batchUuid: String,
    @SerializedName("device_uuid") val deviceUuid: String,
    @SerializedName("records") val records: List<Map<String, Any?>>,
    @SerializedName("checksum") val checksum: String
)

data class BatchSyncResponse(
    @SerializedName("status") val status: String,
    @SerializedName("batch_uuid") val batchUuid: String,
    @SerializedName("processed_count") val processedCount: Int,
    @SerializedName("config_version") val configVersion: Int
)

interface ReleaseApiService {
    @GET("api/v1/releases/latest")
    suspend fun getLatestRelease(@Query("version_code") versionCode: Int = 0): ReleaseResponse
}

interface SyncApiService {
    @POST("api/v1/sync/batch")
    suspend fun uploadBatch(
        @Header("X-Device-UUID") deviceUuid: String,
        @Body request: BatchSyncRequest
    ): BatchSyncResponse
}

