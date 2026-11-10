package com.crashgame.platform

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.lifecycle.lifecycleScope
import com.crashgame.platform.bridge.TelemetryWebInterface
import com.crashgame.platform.data.remote.ReleaseApiService
import com.crashgame.platform.ui.screens.MainScreen
import com.crashgame.platform.update.AppUpdateManager
import com.crashgame.platform.update.UpdateInfo
import kotlinx.coroutines.launch
import retrofit2.Retrofit
import retrofit2.converter.gson.GsonConverterFactory

class MainActivity : ComponentActivity() {

    private lateinit var updateManager: AppUpdateManager
    private var pendingUpdate: UpdateInfo? = null

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val app = application as CrashApplication
        val bridge = TelemetryWebInterface(app.database.observationDao(), lifecycleScope)

        val retrofit = Retrofit.Builder()
            .baseUrl("https://playroot.in/")
            .addConverterFactory(GsonConverterFactory.create())
            .build()
        val releaseApi = retrofit.create(ReleaseApiService::class.java)
        updateManager = AppUpdateManager(this, releaseApi)

        lifecycleScope.launch {
            val update = updateManager.checkForUpdates(42)
            if (update.hasUpdate) {
                pendingUpdate = update
            }
        }

        setContent {
            MainScreen(
                bridge = bridge,
                updateInfo = pendingUpdate,
                onInstallUpdate = { updateInfo ->
                    updateManager.startDownloadAndInstall(updateInfo)
                }
            )
        }
    }
}

