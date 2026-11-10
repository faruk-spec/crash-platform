"""
Autonomous Flight-Trail Computer Vision Analysis Module
Extracts spline curvature, frame delta jitter, and velocity fluctuations.
Works both with NumPy and with pure-Python fallbacks.
"""
import math

def calculate_spline_curvature(points: list) -> float:
    """
    Computes curvature κ of flight-trail contour points:
    κ = |x' y'' - y' x''| / (x'^2 + y'^2)^(3/2)
    """
    if len(points) < 3:
        return 0.0

    try:
        import numpy as np
        pts = np.array(points, dtype=np.float64)
        dx = np.gradient(pts[:, 0])
        dy = np.gradient(pts[:, 1])
        d2x = np.gradient(dx)
        d2y = np.gradient(dy)
        curvature = np.abs(dx * d2y - dy * d2x) / np.power(dx**2 + dy**2 + 1e-6, 1.5)
        return float(np.mean(curvature))
    except ImportError:
        # High-precision pure-Python fallback
        curvatures = []
        for i in range(1, len(points) - 1):
            p0 = points[i - 1]
            p1 = points[i]
            p2 = points[i + 1]

            dx1, dy1 = p1[0] - p0[0], p1[1] - p0[1]
            dx2, dy2 = p2[0] - p1[0], p2[1] - p1[1]

            dx = (dx1 + dx2) / 2.0
            dy = (dy1 + dy2) / 2.0

            d2x = dx2 - dx1
            d2y = dy2 - dy1

            denom = math.pow(dx * dx + dy * dy + 1e-6, 1.5)
            k = abs(dx * d2y - dy * d2x) / denom
            curvatures.append(k)

        return float(sum(curvatures) / len(curvatures)) if curvatures else 0.0

def analyze_frame_jitter(frame_history: list) -> dict:
    """
    Analyzes micro-stutters and velocity variance from recorded frames
    """
    if not frame_history or len(frame_history) < 2:
        return {"avg_jerk": 0.0, "max_jerk": 0.0, "stutter_count": 0, "sample_frames": len(frame_history)}

    jerks = [f.get('jerk', 0.0) for f in frame_history]
    dts = [f.get('dt', 16.6) for f in frame_history]

    stutters = sum(1 for dt in dts if dt > 33.3)
    avg_jerk = sum(jerks) / len(jerks)
    max_jerk = max(jerks)

    return {
        "avg_jerk": round(float(avg_jerk), 6),
        "max_jerk": round(float(max_jerk), 6),
        "stutter_count": stutters,
        "sample_frames": len(frame_history)
    }
