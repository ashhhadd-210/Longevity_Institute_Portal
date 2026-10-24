# Security

The threat model and full control matrix live in
[08-security-model.md](08-security-model.md). This page records what is
implemented and the operational rules.

## Implemented in Milestone 1

- **Authentication:** session-based (Fortify); passwords hashed (bcrypt via
  Laravel's `hashed` cast); minimum 12 characters checked against known
  breaches (k-anonymity) on reset; rate limiting per account + IP on login
  and per session on the two-factor challenge.
- **MFA:** TOTP with hashed one-time recovery codes. Mandatory for every
  role flagged `requires_mfa` (founder, administrator) — a middleware wall
  redirects un-enrolled admins to enrollment before any admin route.
- **Enumeration protection:** login failures, suspended-account failures and
  password-reset requests are indistinguishable from the outside; reset
  requests return the same message whether or not the address exists. The
  membership list is confidential.
- **Account states:** `active / suspended / deactivated`, enforced on every
  request, so suspension ejects live sessions immediately.
- **Authorization:** permissions as data (ADR-004), checked through a single
  gate bridge; the authorization matrix test in
  `tests/Feature/Identity/AuthorizationMatrixTest.php` is the regression net.
- **Invitations:** 256-bit single-use tokens, stored only as SHA-256 hashes,
  expiring, revocable; re-send rotates the token and invalidates the old one.
- **Audit:** authentication and admission events recorded append-only with
  IP and user agent; the database trigger rejects any rewrite of history.
- **Dependencies:** `composer audit` gates CI; Dependabot and secret
  scanning active on the repository.
- **Transport/headers:** TLS via Cloudflare; `X-Frame-Options: DENY`,
  `nosniff`, referrer policy set at nginx (provision script).

## Operational rules

- No secret in the repository, ever. Vault → GitHub secrets / server `.env`.
- Admin accounts are created only via `php artisan portal:create-admin`,
  which prints a one-time password and records the creation in the audit
  stream.
- Suspected credential leak: rotate first (RUNBOOK.md), investigate second.
- The Milestone 4 hardening pass (headers review, rate-limit tuning, manual
  abuse testing of auth flows) happens before the first real invitation.
