package com.crashgame.platform.data.remote

import android.content.Context
import androidx.work.CoroutineWorker
import androidx.work.WorkerParameters
import com.crashgame.platform.data.local.AppDatabase
import com.google.gson.Gson
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory
import java.security.MessageDigest
import java.util.UUID

class BatchSyncWorker(
    appContext: Context,
    workerParams: WorkerParameters
) : CoroutineWorker(appContext, workerParams) {

    override suspend fun doWork(): Result = withContext(Dispatchers.IO) {
        val db = AppDatabase.getInstance(applicationContext)
        val dao = db.observationDao()

        val pendingRounds = dao.getPendingRounds()
        if (pendingRounds.isEmpty()) {
            return@withContext Result.success()
        }

        val records = pendingRounds.map { round ->
            mapOf(
                "round_uuid" to round.roundUuid,
                "server_round_id" to round.serverRoundId,
                "crash_multiplier" to round.crashMultiplier,
                "started_at" to java.text.SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSS'Z'", java.util.Locale.US).format(java.util.Date(round.startedAtMs)),
                "crashed_at" to java.text.SimpleDateFormat("yyyy-MM-dd'T'HH:mm:ss.SSS'Z'", java.util.Locale.US).format(java.util.Date(round.crashedAtMs)),
                "duration_ms" to round.durationMs,
                "raw_inspect" to round.frameHistoryJson
            )
        }

        val batchUuid = UUID.randomUUID().toString()
        val payloadString = Gson().toJson(records)
        val checksum = sha256(payloadString)

        val retrofit = Retrofit.Builder()
            .baseUrl("https://your-domain.com/") // Configurable via SharedPreferences
            .addConverterFactory(GsonConverterFactory.create())
            .build()
        val api = retrofit.create(SyncApiService::class.java)

        try {
            val response = api.uploadBatch(
                deviceUuid = "default-collector-01",
                request = BatchSyncRequest(
                    batchUuid = batchUuid,
                    deviceUuid = "default-collector-01",
                    records = records,
                    checksum = checksum
                )
            )

            if (response.status == "SUCCESS") {
                dao.markRoundsSynced(pendingRounds.map { it.roundUuid })
                Result.success()
            } else {
                Result.retry()
            }
        } catch (e: Exception) {
            Result.retry()
        }
    }

    private fun sha256(input: String): String {
        val bytes = MessageDigest.getInstance("SHA-256").digest(input.toByteArray())
        return bytes.joinToString("") { "%02x".format(it) }
    }
}

