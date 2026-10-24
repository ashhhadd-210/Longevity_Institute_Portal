# 08 — Security Model

**Status:** Draft for internal review · **Milestone:** M1 (hardening pass completes in M4)

## 1. Threat model — what actually matters for an invitation-only institutional platform

| # | Threat | Why it matters here | Controls |
|---|--------|--------------------|----------|
| 1 | Invitation token theft/guessing | The token *is* admission | 256-bit random token; only SHA-256 hash stored; single-use; expiring; revocable; constant-time comparison; HTTPS-only links |
| 2 | Account enumeration | The membership list is itself confidential | Identical responses/timing on login, reset, and (M2) application flows regardless of address existence |
| 3 | Credential stuffing / brute force | Curated professionals = high-value identities | Per-account + per-IP throttling, lockout with audit, breached-password screening, admin MFA |
| 4 | Privilege escalation | One admin account governs admission | Permission checks at policy layer on every mutation; role assignment Founder-reserved and audited; MFA required before any admin route |
| 5 | Leak of admin-only data (city/state, pipeline records, declined applications) | Institutional trust + GDPR | Query-layer scoping (not template-layer), separate admin resources, authorization matrix tests |
| 6 | Email compromise (spoofing the Institute) | Invitation is the first impression | SPF + DKIM + DMARC; transactional/broadcast stream separation; delivery logging |
| 7 | Dependency vulnerabilities | Ten-year platform outlives package fashions | `composer audit` in CI, Dependabot, minimal dependency policy (first-party Laravel wherever possible) |
| 8 | Data loss | Institutional history is the product | Managed backups + PITR + independent nightly dumps; **rehearsed** restore (M1 demo) |
| 9 | Session hijacking | Long-lived professional accounts | Secure/HttpOnly/SameSite cookies, regeneration on privilege change, idle+absolute timeouts, per-request account-state checks |

## 2. Application controls (M1)

- **Input:** Form Request validation everywhere; Unicode-aware rules (never ASCII-only on names);
  file uploads (photos) validated by content type + size, re-encoded on processing, stored
  privately, served via signed URLs.
- **Output:** Blade auto-escaping; no `{!! !!}` without a documented sanitizer; CSP headers
  (self-hosted assets only), `X-Frame-Options: DENY`, `Referrer-Policy`, HSTS.
- **CSRF** on all state-changing routes (Laravel default, verified in tests).
- **Rate limiting:** named limiters — auth flows (tight), invitation acceptance (tight),
  future public application form (moderate + honeypot), admin endpoints (generous but present).
- **Audit:** all security-relevant events append-only with actor, IP, UA (doc 03 §2 —
  DB role cannot UPDATE/DELETE audit rows).

## 3. Data protection (GDPR — technical implementation; legal wording is counsel's)

- Consent captured at registration against a versioned privacy notice (re-consent on change).
- Data minimisation defaults: collect what the profile standard needs; admin-only fields
  clearly marked at collection time (client standard for city/state).
- Export: per-user machine-readable export (JSON) assembled by `DataRequestService`.
- Erasure: anonymize-in-place strategy — personal fields overwritten, institutional record
  of *decisions* preserved in lawful, de-identified form (rationale documented; final legal
  posture confirmed with counsel via Linas).
- Hosting region decision recorded in ADR-007.

## 4. Operational security

- SSH keys only; least-privilege DB users (app user ≠ migration user ≠ readonly);
  private networking between droplets and database; Cloudflare proxy in front of origins.
- Secret rotation procedure in runbook; leaked-secret response: rotate → redeploy → audit review.
- Sentry scrubs PII from error payloads (default deny-list + explicit `before_send` filter).

## 5. Verification cadence

- CI security gates on every PR (audit, static analysis, authorization matrix tests).
- M4: full hardening pass — dependency review, header verification, rate-limit tuning,
  manual auth-flow abuse testing — before the first real invitation is sent.
