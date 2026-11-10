# AI/ML Model Evaluation & Scientific Standards

## Zero Paid AI Architecture
The entire analytical pipeline is self-contained and operates without third-party paid APIs (e.g. OpenAI, Anthropic, Gemini):
* Survival Modeling: `lifelines` (Kaplan-Meier Non-Parametric Estimator)
* Gradient Boosted Regressors: `lightgbm`
* Numerical Statistics: `numpy`, `scipy.stats`
* Computer Vision: `opencv-python-headless`

## Evaluation Metrics
1. **Brier Score Calibration**:
   $$BS = \frac{1}{N} \sum_{t=1}^N (f_t - o_t)^2$$
   Where $f_t$ is the forecast probability of reaching the target threshold (e.g. 2.0x), and $o_t \in \{0, 1\}$ is the actual outcome.
2. **Out-of-Sample Holdout Testing**:
   Models are evaluated strictly chronologically (training on rounds $1..T$, testing on $T+1..T+K$) to prevent lookahead bias.
3. **Strict "NO SIGNAL" Policy**:
   If the conditional survival probability is below the administrator-configured threshold (e.g. 75%), or if sample size is insufficient, the engine emits `NO_SIGNAL`. Predictions are never fabricated.
