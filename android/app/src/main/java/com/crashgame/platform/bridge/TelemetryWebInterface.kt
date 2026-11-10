package com.crashgame.platform.bridge

import android.webkit.JavascriptInterface
import com.crashgame.platform.data.local.dao.ObservationDao
import com.crashgame.platform.data.local.entity.GameRoundEntity
import com.crashgame.platform.data.local.entity.RawObservationEntity
import com.crashgame.platform.domain.model.RoundState
import kotlinx.coroutines.CoroutineScope
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.flow.MutableStateFlow
import kotlinx.coroutines.flow.asStateFlow
import kotlinx.coroutines.launch
import java.util.UUID

class TelemetryWebInterface(
    private val observationDao: ObservationDao,
    private val scope: CoroutineScope
) {
    private val _liveRoundState = MutableStateFlow(RoundState())
    val liveRoundState = _liveRoundState.asStateFlow()

    @JavascriptInterface
    fun onInspectPacket(packetJson: String) {
        scope.launch(Dispatchers.IO) {
            observationDao.insertObservation(
                RawObservationEntity(
                    observationUuid = UUID.randomUUID().toString(),
                    sourceType = "WEB_INSPECT",
                    rawPayload = packetJson,
                    capturedAtMs = System.currentTimeMillis()
                )
            )
        }
    }

    @JavascriptInterface
    fun onConsoleLog(level: String, message: String) {
        // Telemetry internal log receiver
    }

    @JavascriptInterface
    fun onAnimationTick(dt: Float, jerk: Float, x: Float, y: Float) {
        _liveRoundState.value = _liveRoundState.value.copy(
            frameDeltaMs = dt,
            animationJerk = jerk,
            planeX = x,
            planeY = y
        )
    }

    @JavascriptInterface
    fun onTrailCurvature(curvatureK: Float) {
        _liveRoundState.value = _liveRoundState.value.copy(
            trailCurvatureK = curvatureK
        )
    }

    @JavascriptInterface
    fun onRoundStart(roundId: String) {
        _liveRoundState.value = RoundState(
            roundId = roundId,
            isActive = true,
            startedAtMs = System.currentTimeMillis()
        )
    }

    @JavascriptInterface
    fun onRoundCrash(roundId: String, finalMultiplier: Float, frameHistoryJson: String) {
        val endedAt = System.currentTimeMillis()
        _liveRoundState.value = _liveRoundState.value.copy(
            isActive = false,
            crashMultiplier = finalMultiplier
        )

        scope.launch(Dispatchers.IO) {
            observationDao.insertRound(
                GameRoundEntity(
                    roundUuid = UUID.randomUUID().toString(),
                    serverRoundId = roundId,
                    crashMultiplier = finalMultiplier,
                    startedAtMs = _liveRoundState.value.startedAtMs,
                    crashedAtMs = endedAt,
                    durationMs = (endedAt - _liveRoundState.value.startedAtMs).toInt(),
                    frameHistoryJson = frameHistoryJson,
                    isSynced = false
                )
            )
        }
    }
}

