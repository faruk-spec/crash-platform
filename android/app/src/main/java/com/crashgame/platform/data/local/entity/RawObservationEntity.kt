package com.crashgame.platform.data.local.entity

import androidx.room.Entity
import androidx.room.Index
import androidx.room.PrimaryKey

@Entity(
    tableName = "raw_observations",
    indices = [Index(value = ["observationUuid"], unique = true)]
)
data class RawObservationEntity(
    @PrimaryKey(autoGenerate = true) val id: Long = 0,
    val observationUuid: String,
    val sourceType: String,
    val rawPayload: String,
    val capturedAtMs: Long,
    val isSynced: Boolean = false
)
