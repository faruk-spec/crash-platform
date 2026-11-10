package com.crashgame.platform.domain.model

data class RoundState(
    val roundId: String = "",
    val isActive: Boolean = false,
    val startedAtMs: Long = 0L,
    val currentMultiplier: Float = 1.0f,
    val crashMultiplier: Float = 0.0f,
    val frameDeltaMs: Float = 16.6f,
    val animationJerk: Float = 0.0f,
    val trailCurvatureK: Float = 0.0f,
    val planeX: Float = 0.0f,
    val planeY: Float = 0.0f,
    val predictionSignal: String = "NO_SIGNAL",
    val predictionConfidence: Float = 0.0f
)
