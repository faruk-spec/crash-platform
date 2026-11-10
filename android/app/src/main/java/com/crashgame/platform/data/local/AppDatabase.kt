package com.crashgame.platform.data.local

import android.content.Context
import androidx.room.Database
import androidx.room.Room
import androidx.room.RoomDatabase
import com.crashgame.platform.data.local.dao.ObservationDao
import com.crashgame.platform.data.local.entity.GameRoundEntity
import com.crashgame.platform.data.local.entity.RawObservationEntity

@Database(
    entities = [GameRoundEntity::class, RawObservationEntity::class],
    version = 1,
    exportSchema = false
)
abstract class AppDatabase : RoomDatabase() {
    abstract fun observationDao(): ObservationDao

    companion object {
        @Volatile
        private var INSTANCE: AppDatabase? = null

        fun getInstance(context: Context): AppDatabase {
            return INSTANCE ?: synchronized(this) {
                val instance = Room.databaseBuilder(
                    context.applicationContext,
                    AppDatabase::class.java,
                    "crash_platform_offline.db"
                )
                    .setJournalMode(JournalMode.WRITE_AHEAD_LOGGING) // High-performance WAL mode
                    .fallbackToDestructiveMigration()
                    .build()
                INSTANCE = instance
                instance
            }
        }
    }
}

