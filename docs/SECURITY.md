# Security & Access Control Policy

## Security Invariants

1. **Credential Secrecy**:
   * No API keys, GitHub tokens, or database passwords are hardcoded in the frontend or Android APK.
   * Android release signing keys reside exclusively in GitHub Encrypted Secrets.
2. **Device Authentication**:
   * Devices generate unique hardware UUIDs and exchange cryptographically random bearer tokens during registration.
3. **Webhook Verification**:
   * Inbound GitHub Actions webhooks require an HMAC-SHA256 signature in the `X-Webhook-Secret` header.
4. **Target Website Interaction Boundaries**:
   * Collection is strictly read-only observation of public client-side telemetry (Canvas, WebSockets).
   * Never stores or attempts to collect user passwords, banking info, or session tokens.
5. **In-App Package Integrity**:
   * Every APK downloaded for auto-updating is verified against a SHA-256 hash before `PackageInstaller` launch.
