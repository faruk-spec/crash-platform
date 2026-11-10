/**
 * Autonomous Crash Game Telemetry & Canvas Fluctuation Harvester
 * Intercepts WebSockets, Network, Console, and 60FPS Canvas Animation
 */
(function () {
    if (window.__CRASH_HARVESTER_INITIALIZED__) return;
    window.__CRASH_HARVESTER_INITIALIZED__ = true;

    console.log("[HARVESTER] Initializing In-App Browser Inspector...");

    let currentRoundId = null;
    let roundActive = false;
    let lastFrameTime = performance.now();
    let prevX = 0, prevY = 0;
    let prevVx = 0, prevVy = 0;
    let frameHistory = [];

    // ==========================================
    // 1. WEBSOCKET & NETWORK PACKET INTERCEPTOR
    // ==========================================
    const OriginalWebSocket = window.WebSocket;
    window.WebSocket = function (...args) {
        const ws = new OriginalWebSocket(...args);

        ws.addEventListener('message', function (event) {
            const now = performance.now();
            try {
                const data = JSON.parse(event.data);
                
                // Transmit raw inspect packet to Android Kotlin Native Interface
                if (window.AndroidBridge && window.AndroidBridge.onInspectPacket) {
                    window.AndroidBridge.onInspectPacket(JSON.stringify({
                        type: 'WS_INBOUND',
                        timestamp_ms: now,
                        data: data
                    }));
                }

                // Detect round lifecycle events
                if (data.type === 'round_start' || (data.id && data.status === 'started')) {
                    currentRoundId = data.round_id || data.id || ('R-' + Date.now());
                    roundActive = true;
                    frameHistory = [];
                    if (window.AndroidBridge) window.AndroidBridge.onRoundStart(currentRoundId);
                } else if (data.type === 'crash' || data.type === 'ended' || data.final_multiplier) {
                    roundActive = false;
                    const finalMultiplier = parseFloat(data.final_multiplier || data.multiplier || 1.0);
                    if (window.AndroidBridge) {
                        window.AndroidBridge.onRoundCrash(currentRoundId, finalMultiplier, JSON.stringify(frameHistory));
                    }
                }
            } catch (e) {
                // Non-JSON or binary payload
            }
        });

        const originalSend = ws.send;
        ws.send = function (data) {
            if (window.AndroidBridge && window.AndroidBridge.onInspectPacket) {
                window.AndroidBridge.onInspectPacket(JSON.stringify({
                    type: 'WS_OUTBOUND',
                    timestamp_ms: performance.now(),
                    payload: data.toString()
                }));
            }
            return originalSend.apply(this, arguments);
        };

        return ws;
    };

    // ==========================================
    // 2. CONSOLE & ERROR LOGGER HOOK
    // ==========================================
    ['log', 'warn', 'error'].forEach(level => {
        const originalConsole = console[level];
        console[level] = function (...args) {
            if (window.AndroidBridge && window.AndroidBridge.onConsoleLog) {
                window.AndroidBridge.onConsoleLog(level, args.map(a => typeof a === 'object' ? JSON.stringify(a) : a).join(' '));
            }
            originalConsole.apply(console, args);
        };
    });

    // ==========================================
    // 3. CANVAS ANIMATION & FLIGHT-TRAIL TRACKER
    // ==========================================
    const originalGetContext = HTMLCanvasElement.prototype.getContext;
    HTMLCanvasElement.prototype.getContext = function (type, attributes) {
        const ctx = originalGetContext.call(this, type, attributes);

        if (type === '2d' && !this.__hooked__) {
            this.__hooked__ = true;
            const originalDrawImage = ctx.drawImage;
            const originalBezierCurveTo = ctx.bezierCurveTo;

            // Track Plane Coordinates and Jitter
            ctx.drawImage = function (...args) {
                if (roundActive && args.length >= 3) {
                    const now = performance.now();
                    const dt = now - lastFrameTime;
                    const x = args[1];
                    const y = args[2];

                    if (dt > 0 && prevX !== 0) {
                        const vx = (x - prevX) / dt;
                        const vy = (y - prevY) / dt;
                        const ax = (vx - prevVx) / dt;
                        const ay = (vy - prevVy) / dt;
                        const jerk = Math.sqrt(ax * ax + ay * ay); // Acceleration spike (jerk)

                        const frameData = {
                            t: Math.round(now),
                            dt: parseFloat(dt.toFixed(2)),
                            x: parseFloat(x.toFixed(2)),
                            y: parseFloat(y.toFixed(2)),
                            jerk: parseFloat(jerk.toFixed(4))
                        };

                        frameHistory.push(frameData);
                        if (frameHistory.length > 300) frameHistory.shift();

                        // Notify Android Kotlin HUD
                        if (window.AndroidBridge && window.AndroidBridge.onAnimationTick) {
                            window.AndroidBridge.onAnimationTick(dt, jerk, x, y);
                        }

                        prevVx = vx;
                        prevVy = vy;
                    }

                    prevX = x;
                    prevY = y;
                    lastFrameTime = now;
                }
                return originalDrawImage.apply(this, arguments);
            };

            // Track Flight Trail Bezier Spline Curvature
            ctx.bezierCurveTo = function (cp1x, cp1y, cp2x, cp2y, x, y) {
                if (roundActive) {
                    const dx = x - cp1x;
                    const dy = y - cp1y;
                    const curvatureK = Math.abs(dx * cp2y - dy * cp2x) / Math.pow(dx * dx + dy * dy + 0.001, 1.5);

                    if (window.AndroidBridge && window.AndroidBridge.onTrailCurvature) {
                        window.AndroidBridge.onTrailCurvature(parseFloat(curvatureK.toFixed(6)));
                    }
                }
                return originalBezierCurveTo.apply(this, arguments);
            };
        }

        return ctx;
    };

    console.log("[HARVESTER] Injected Harvester active on target page.");
})();
