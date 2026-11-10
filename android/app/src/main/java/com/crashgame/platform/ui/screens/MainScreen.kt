package com.crashgame.platform.ui.screens

import android.annotation.SuppressLint
import android.view.ViewGroup
import android.webkit.WebChromeClient
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.compose.foundation.background
import androidx.compose.foundation.layout.*
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.*
import androidx.compose.runtime.*
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontFamily
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.compose.ui.unit.sp
import androidx.compose.ui.viewinterop.AndroidView
import com.crashgame.platform.bridge.TelemetryWebInterface
import com.crashgame.platform.update.UpdateInfo

@SuppressLint("SetJavaScriptEnabled")
@Composable
fun MainScreen(
    bridge: TelemetryWebInterface,
    updateInfo: UpdateInfo?,
    onInstallUpdate: (UpdateInfo) -> Unit
) {
    val roundState by bridge.liveRoundState.collectAsState()
    var showUpdateDialog by remember { mutableStateOf(updateInfo?.hasUpdate == true) }

    Scaffold(
        topBar = {
            Row(
                modifier = Modifier
                    .fillMaxWidth()
                    .background(Color(0xFF0F172A))
                    .padding(horizontal = 16.dp, vertical = 10.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically
            ) {
                Column {
                    Text(
                        text = "AUTONOMOUS PREDICTOR",
                        color = Color(0xFF38BDF8),
                        fontSize = 13.sp,
                        fontWeight = FontWeight.Bold,
                        letterSpacing = 1.sp
                    )
                    Text(
                        text = "Target: damansuperstar1.com",
                        color = Color(0xFF94A3B8),
                        fontSize = 11.sp
                    )
                }
                Row(verticalAlignment = Alignment.CenterVertically) {
                    Box(
                        modifier = Modifier
                            .size(8.dp)
                            .background(Color(0xFF10B981), shape = RoundedCornerShape(4.dp))
                    )
                    Spacer(modifier = Modifier.width(6.dp))
                    Text(
                        text = "SYNC ACTIVE",
                        color = Color(0xFF10B981),
                        fontSize = 11.sp,
                        fontWeight = FontWeight.Bold
                    )
                }
            }
        }
    ) { paddingValues ->
        Box(
            modifier = Modifier
                .fillMaxSize()
                .padding(paddingValues)
        ) {
            // 1. Hardened In-App Game Browser
            AndroidView(
                factory = { context ->
                    WebView(context).apply {
                        layoutParams = ViewGroup.LayoutParams(
                            ViewGroup.LayoutParams.MATCH_PARENT,
                            ViewGroup.LayoutParams.MATCH_PARENT
                        )
                        settings.javaScriptEnabled = true
                        settings.domStorageEnabled = true
                        settings.databaseEnabled = true
                        settings.mediaPlaybackRequiresUserGesture = false

                        addJavascriptInterface(bridge, "AndroidBridge")

                        webViewClient = object : WebViewClient() {
                            override fun onPageFinished(view: WebView?, url: String?) {
                                super.onPageFinished(view, url)
                                // Inject Automated Telemetry & Canvas Fluctuation Harvester
                                val js = context.assets.open("injected_harvester.js")
                                    .bufferedReader().use { it.readText() }
                                view?.evaluateJavascript(js, null)
                            }
                        }
                        webChromeClient = WebChromeClient()

                        loadUrl("https://damansuperstar1.com/")
                    }
                },
                modifier = Modifier.fillMaxSize()
            )

            // 2. Floating Predictor & Telemetry HUD Overlay
            Card(
                modifier = Modifier
                    .align(Alignment.BottomCenter)
                    .fillMaxWidth()
                    .padding(12.dp),
                shape = RoundedCornerShape(16.dp),
                colors = CardDefaults.cardColors(containerColor = Color(0xEE1E293B)),
                elevation = CardDefaults.cardElevation(defaultElevation = 8.dp)
            ) {
                Column(modifier = Modifier.padding(16.dp)) {
                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween,
                        verticalAlignment = Alignment.CenterVertically
                    ) {
                        Text(
                            text = if (roundState.isActive) "ROUND IN PROGRESS" else "WAITING FOR TAKEOFF",
                            color = if (roundState.isActive) Color(0xFF10B981) else Color(0xFF94A3B8),
                            fontSize = 12.sp,
                            fontWeight = FontWeight.Bold
                        )
                        Text(
                            text = if (roundState.crashMultiplier > 0) "Last: ${String.format("%.2f", roundState.crashMultiplier)}x" else "--",
                            color = Color(0xFFEF4444),
                            fontSize = 13.sp,
                            fontWeight = FontWeight.Bold,
                            fontFamily = FontFamily.Monospace
                        )
                    }

                    Spacer(modifier = Modifier.height(10.dp))

                    Row(
                        modifier = Modifier.fillMaxWidth(),
                        horizontalArrangement = Arrangement.SpaceBetween
                    ) {
                        Column {
                            Text(text = "ANIMATION JERK", color = Color(0xFF64748B), fontSize = 10.sp)
                            Text(
                                text = String.format("%.4f", roundState.animationJerk),
                                color = Color(0xFFF59E0B),
                                fontSize = 14.sp,
                                fontWeight = FontWeight.SemiBold,
                                fontFamily = FontFamily.Monospace
                            )
                        }
                        Column {
                            Text(text = "TRAIL CURVATURE (k)", color = Color(0xFF64748B), fontSize = 10.sp)
                            Text(
                                text = String.format("%.4f", roundState.trailCurvatureK),
                                color = Color(0xFF38BDF8),
                                fontSize = 14.sp,
                                fontWeight = FontWeight.SemiBold,
                                fontFamily = FontFamily.Monospace
                            )
                        }
                        Column {
                            Text(text = "PREDICTIVE SIGNAL", color = Color(0xFF64748B), fontSize = 10.sp)
                            Text(
                                text = roundState.predictionSignal,
                                color = if (roundState.predictionSignal == "NO_SIGNAL") Color(0xFF94A3B8) else Color(0xFF10B981),
                                fontSize = 14.sp,
                                fontWeight = FontWeight.Bold
                            )
                        }
                    }
                }
            }

            // 3. In-App Auto-Update Modal Dialog
            if (showUpdateDialog && updateInfo != null) {
                AlertDialog(
                    onDismissRequest = { if (!updateInfo.isMandatory) showUpdateDialog = false },
                    title = { Text(text = "App Update Available", fontWeight = FontWeight.Bold) },
                    text = {
                        Column {
                            Text("A new signed release (${updateInfo.versionName}) is available.")
                            Spacer(modifier = Modifier.height(6.dp))
                            Text(
                                text = updateInfo.releaseNotes,
                                fontSize = 12.sp,
                                color = Color.Gray
                            )
                        }
                    },
                    confirmButton = {
                        Button(onClick = {
                            showUpdateDialog = false
                            onInstallUpdate(updateInfo)
                        }) {
                            Text("Download & Install")
                        }
                    },
                    dismissButton = {
                        if (!updateInfo.isMandatory) {
                            TextButton(onClick = { showUpdateDialog = false }) {
                                Text("Later")
                            }
                        }
                    }
                )
            }
        }
    }
}

