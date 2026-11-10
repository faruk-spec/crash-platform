package com.crashgame.platform.data.local.entity

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "game_rounds",
    indices = [Index(value = ["roundUuid"], unique = true)]
)
data class GameRoundEntity(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val roundUuid: String,
    val serverRoundId: String?,
    val crashMultiplier: Float,
    val startedAtMs: Long,
    val crashedAtMs: Long,
    val durationMs: Int,
    val frameHistoryJson: String?,
    val isSynced: Boolean = false,
    val createdAtMs: Long = System.currentTimeMillis()
)
