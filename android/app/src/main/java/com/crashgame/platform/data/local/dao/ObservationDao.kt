package com.crashgame.platform.data.local.dao

import androidx.room.Dao
import androidx.room.Insert
import androidx.room.OnConflictStrategy
import androidx.room.Query
import com.crashgame.platform.data.local.entity.GameRoundEntity
import com.crashgame.platform.data.local.entity.RawObservationEntity
import kotlinx.coroutines.flow.Flow

@Dao
interface ObservationDao {
    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertObservation(observation: RawObservationEntity): Long

    @Insert(onConflict = OnConflictStrategy.REPLACE)
    suspend fun insertRound(round: GameRoundEntity): Long

    @Query("SELECT * FROM game_rounds WHERE isSynced = 0 LIMIT 50")
    suspend fun getPendingRounds(): List<GameRoundEntity>

    @Query("SELECT * FROM raw_observations WHERE isSynced = 0 LIMIT 100")
    suspend fun getPendingObservations(): List<RawObservationEntity>

    @Query("UPDATE game_rounds SET isSynced = 1 WHERE roundUuid IN (:roundUuids)")
    suspend fun markRoundsSynced(roundUuids: List<String>)

    @Query("UPDATE raw_observations SET isSynced = 1 WHERE observationUuid IN (:observationUuids)")
    suspend fun markObservationsSynced(observationUuids: List<String>)

    @Query("SELECT COUNT(*) FROM game_rounds WHERE isSynced = 0")
    fun getPendingCountFlow(): Flow<Int>

    @Query("SELECT * FROM game_rounds ORDER BY id DESC LIMIT 20")
    fun getRecentRoundsFlow(): Flow<List<GameRoundEntity>>

    suspend fun saveCompletedRound(
        roundUuid: String,
        serverRoundId: String?,
        multiplier: Float,
        startedAt: Long,
        crashedAt: Long,
        durationMs: Int,
        frameHistoryJson: String?
    ) {
        insertRound(
            GameRoundEntity(
                roundUuid = roundUuid,
                serverRoundId = serverRoundId,
                crashMultiplier = multiplier,
                startedAtMs = startedAt,
                crashedAtMs = crashedAt,
                durationMs = durationMs,
                frameHistoryJson = frameHistoryJson,
                isSynced = false
            )
        )
    }
}
