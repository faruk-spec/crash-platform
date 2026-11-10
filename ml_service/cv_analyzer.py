"""
Autonomous Flight-Trail Computer Vision Analysis Module
Extracts spline curvature, frame delta jitter, and Farneback optical flow.
"""
import numpy as np

def calculate_spline_curvature(points: list) -> float:
    """
    Computes curvature κ of flight-trail contour points:
    κ = |x' y'' - y' x''| / (x'^2 + y'^2)^(3/2)
    """
    if len(points) < 3:
        return 0.0

    pts = np.array(points, dtype=np.float64)
    # 1st derivatives
    dx = np.gradient(pts[:, 0])
    dy = np.gradient(pts[:, 1])

    # 2nd derivatives
    d2x = np.gradient(dx)
    d2y = np.gradient(dy)

    curvature = np.abs(dx * d2y - dy * d2x) / np.power(dx**2 + dy**2 + 1e-6, 1.5)
    return float(np.mean(curvature))

def analyze_frame_jitter(frame_history: list) -> dict:
    """
    Analyzes micro-stutters and velocity variance from recorded frames
    """
    if not frame_history or len(frame_history) < 5:
        return {"avg_jerk": 0.0, "max_jerk": 0.0, "stutter_count": 0}

    jerks = [f.get('jerk', 0.0) for f in frame_history]
    dts = [f.get('dt', 16.6) for f in frame_history]

    # Stutter defined as frame duration > 33.3ms (dropped frame at 60fps)
    stutters = sum(1 for dt in dts if dt > 33.3)

    return {
        "avg_jerk": float(np.mean(jerks)),
        "max_jerk": float(np.max(jerks)),
        "stutter_count": stutters,
        "sample_frames": len(frame_history)
    }
