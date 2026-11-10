"""
Autonomous Crash Game Statistical Survival & Regressor Engine
Trained purely on empirical rounds collected from the game. Zero paid APIs.
"""
import numpy as np
import pandas as pd
from lifelines import KaplanMeierFitter
import lightgbm as lgb
from typing import Dict, Any, List

class CrashPredictorEngine:
    def __init__(self):
        self.kmf = KaplanMeierFitter()
        self.lgb_model = None
        self.is_trained = False
        self.empirical_sample_size = 0

    def train_on_empirical_data(self, rounds_data: List[Dict[str, Any]]) -> Dict[str, Any]:
        """
        Trains model directly on actual recorded historical rounds.
        Features include:
        - Recent crash streaks (last 3, 5, 10 rounds below 1.5x)
        - Frame animation jitter (jerk index from Canvas)
        - Network packet arrival delta variance
        """
        if len(rounds_data) < 20:
            return {"status": "INSUFFICIENT_DATA", "record_count": len(rounds_data)}

        df = pd.DataFrame(rounds_data)
        durations = df['crash_multiplier'].values
        events = np.ones(len(durations))

        # 1. Fit Non-Parametric Survival Distribution on Real Multipliers
        self.kmf.fit(durations, event_observed=events)
        self.empirical_sample_size = len(df)

        # 2. Extract Features for LightGBM
        df['recent_low_streak'] = df['recent_low_streak'].fillna(0)
        df['avg_frame_jerk'] = df['avg_frame_jerk'].fillna(0.0)
        df['trail_curvature_var'] = df['trail_curvature_var'].fillna(0.0)
        df['packet_jitter_ms'] = df['packet_jitter_ms'].fillna(0.0)

        X = df[['recent_low_streak', 'avg_frame_jerk', 'trail_curvature_var', 'packet_jitter_ms']]
        y = (df['crash_multiplier'] >= 2.0).astype(int)

        if len(np.unique(y)) > 1:
            train_data = lgb.Dataset(X, label=y)
            params = {
                'objective': 'binary',
                'metric': 'binary_logloss',
                'learning_rate': 0.05,
                'num_leaves': 15,
                'verbose': -1
            }
            self.lgb_model = lgb.train(params, train_data, num_boost_round=100)
            self.is_trained = True

        return {
            "status": "TRAINED",
            "sample_size": self.empirical_sample_size,
            "median_multiplier": float(np.median(durations)),
            "prob_survival_2x": float(self.kmf.predict(2.0)),
            "prob_survival_5x": float(self.kmf.predict(5.0)),
            "prob_survival_8x": float(self.kmf.predict(8.0))
        }

    def predict_live(self, current_mult: float, live_features: Dict[str, float], min_confidence: float) -> Dict[str, Any]:
        """
        Evaluates real-time conditional survival probability: P(M > Target | M > Current)
        """
        if not self.is_trained or current_mult <= 0:
            return {"signal": "NO_SIGNAL", "reason": "ENGINE_NOT_TRAINED"}

        # Conditional probability from real empirical survival curve
        s_current = float(self.kmf.predict(current_mult))
        s_target_2x = float(self.kmf.predict(2.0))

        cond_prob_2x = (s_target_2x / s_current) if s_current > 0 else 0.0

        if cond_prob_2x >= min_confidence:
            return {
                "signal": "ACTIVE_SIGNAL",
                "target_multiplier": 2.0,
                "confidence": round(cond_prob_2x, 4),
                "data_source": "EMPIRICAL_SURVIVAL_MODEL"
            }
        else:
            return {
                "signal": "NO_SIGNAL",
                "confidence": round(cond_prob_2x, 4),
                "reason": "BELOW_CONFIDENCE_THRESHOLD"
            }

