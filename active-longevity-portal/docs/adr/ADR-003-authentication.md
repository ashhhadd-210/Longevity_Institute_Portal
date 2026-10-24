# ADR-003 — Authentication: Session-based, Fortify backend, TOTP MFA

**Status:** Proposed (internal review) · **Date:** 2026-08

## Decision
First-party session authentication (Laravel Fortify headless backend + custom Livewire
views). Mandatory TOTP MFA with hashed recovery codes for all admin-tier roles, enforced by
middleware before any admin route. Argon2id password hashing. Invitation-only: no public
registration route exists; accounts are created via invitation acceptance (M2) or
administratively.

## Alternatives considered
- **OAuth/social login (Google, LinkedIn):** rejected for Foundation — ties institutional
  identity to third-party accounts, complicates the curated-admission story, and adds a
  dependency; can be added later as a *secondary* login method if ever wanted.
- **SMS/email-code MFA:** rejected — SIM-swap risk and deliverability coupling; TOTP is
  self-contained and free.
- **WebAuthn/passkeys:** attractive, deferred — smaller recovery/support surface for a
  founder-operated platform matters more right now; additive later.
- **JWT/stateless tokens:** rejected — no API consumer exists; server sessions are simpler
  to secure and invalidate.

## Consequences
Password reset and login flows must be enumeration-safe (membership list is confidential).
The invitation itself serves as email verification at registration. MFA enrollment is a
hard wall for admins, not a suggestion.
