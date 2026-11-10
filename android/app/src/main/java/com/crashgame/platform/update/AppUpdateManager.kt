package com.crashgame.platform.update

import android.app.DownloadManager
import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.net.Uri
import android.os.Build
import android.os.Environment
import androidx.core.content.FileProvider
import com.crashgame.platform.data.remote.ReleaseApiService
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.withContext
import java.io.File
import java.io.FileInputStream
import java.security.MessageDigest

data class UpdateInfo(
    val hasUpdate: Boolean,
    val versionName: String,
    val versionCode: Int,
    val downloadUrl: String,
    val sha256Checksum: String,
    val releaseNotes: String,
    val isMandatory: Boolean
)

class AppUpdateManager(
    private val context: Context,
    private val releaseApi: ReleaseApiService
) {
    suspend fun checkForUpdates(currentVersionCode: Int): UpdateInfo = withContext(Dispatchers.IO) {
        try {
            val response = releaseApi.getLatestRelease(currentVersionCode)
            if (response.hasUpdate) {
                UpdateInfo(
                    hasUpdate = true,
                    versionName = response.versionName,
                    versionCode = response.versionCode,
                    downloadUrl = response.downloadUrl,
                    sha256Checksum = response.sha256Checksum,
                    releaseNotes = response.releaseNotes ?: "Automated build release update",
                    isMandatory = response.isMandatory
                )
            } else {
                UpdateInfo(false, "", 0, "", "", "", false)
            }
        } catch (e: Exception) {
            UpdateInfo(false, "", 0, "", "", "", false)
        }
    }

    fun startDownloadAndInstall(updateInfo: UpdateInfo) {
        val fileName = "crash_platform_update_${updateInfo.versionCode}.apk"
        val destination = File(context.getExternalFilesDir(Environment.DIRECTORY_DOWNLOADS), fileName)
        if (destination.exists()) destination.delete()

        val request = DownloadManager.Request(Uri.parse(updateInfo.downloadUrl))
            .setTitle("Downloading App Update ${updateInfo.versionName}")
            .setDescription("Verifying cryptographic signature...")
            .setNotificationVisibility(DownloadManager.Request.VISIBILITY_VISIBLE_NOTIFY_COMPLETED)
            .setDestinationUri(Uri.fromFile(destination))

        val manager = context.getSystemService(Context.DOWNLOAD_SERVICE) as DownloadManager
        val downloadId = manager.enqueue(request)

        val onComplete = object : BroadcastReceiver() {
            override fun onReceive(ctxt: Context, intent: Intent) {
                val id = intent.getLongExtra(DownloadManager.EXTRA_DOWNLOAD_ID, -1)
                if (id == downloadId) {
                    context.unregisterReceiver(this)
                    if (verifyChecksum(destination, updateInfo.sha256Checksum)) {
                        installApk(destination)
                    }
                }
            }
        }

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
            context.registerReceiver(
                onComplete,
                IntentFilter(DownloadManager.ACTION_DOWNLOAD_COMPLETE),
                Context.RECEIVER_EXPORTED
            )
        } else {
            @Suppress("UnspecifiedRegisterReceiverFlag")
            context.registerReceiver(
                onComplete,
                IntentFilter(DownloadManager.ACTION_DOWNLOAD_COMPLETE)
            )
        }
    }

    private fun verifyChecksum(file: File, expectedSha256: String): Boolean {
        val digest = MessageDigest.getInstance("SHA-256")
        FileInputStream(file).use { fis ->
            val buffer = ByteArray(8192)
            var bytesRead: Int
            while (fis.read(buffer).also { bytesRead = it } != -1) {
                digest.update(buffer, 0, bytesRead)
            }
        }
        val calculated = digest.digest().joinToString("") { "%02x".format(it) }
        return calculated.equals(expectedSha256, ignoreCase = true)
    }

    private fun installApk(file: File) {
        val uri = FileProvider.getUriForFile(context, "${context.packageName}.provider", file)
        val installIntent = Intent(Intent.ACTION_VIEW).apply {
            setDataAndType(uri, "application/vnd.android.package-archive")
            addFlags(Intent.FLAG_GRANT_READ_URI_PERMISSION)
            addFlags(Intent.FLAG_ACTIVITY_NEW_TASK)
        }
        context.startActivity(installIntent)
    }
}
