# 05 — Authentication Model

**Status:** Draft for internal review · **Milestone:** M1

## 1. Positioning

Session-based authentication on Laravel's first-party primitives (Fortify headless backend
+ custom Livewire views) — no OAuth provider dependency, no JWT complexity. This Portal is
**invitation-only**: there is no self-service sign-up route; account creation happens only
through invitation acceptance (M2) and administrative creation (M1, for admin accounts).
Details in ADR-003.

## 2. Flows

| Flow | Behavior |
|------|----------|
| Login | Email + password → (if MFA enrolled) TOTP challenge → session. Throttled per account **and** per IP. |
| Logout | Session invalidated server-side; other-device logout available. |
| Password reset | Standard signed, expiring, single-use token. **Response is identical whether or not the email exists** (enumeration protection — the membership list is confidential). |
| Email verification | On registration (M2) the invitation itself proves email ownership — the accepted invitation marks the address verified without a second round-trip. Admin-created accounts verify by first-login reset link. |
| Registration | M2 surface, M1 foundation: `RegistrationService` exists and is exercised in tests; it consumes an accepted invitation, creates User + Collaborator + empty Profile atomically, records consent, emits `CollaboratorAdmitted`. |
| Session security | HTTP-only, `Secure`, `SameSite=Lax` cookies; session ID regenerated at login and privilege change; absolute + idle timeouts (admin sessions shorter). |

## 3. MFA (administrators — mandatory, never deferred)

- TOTP (authenticator app), enforced at the **role tier**: any user holding a role flagged
  `requires_mfa` (founder, administrator, and future reviewer/staff roles) cannot reach any
  admin route until enrolled — middleware redirects to enrollment, not merely "encouraged."
- Hashed one-time recovery codes issued at enrollment; regenerable; use is audited.
- Secret stored encrypted (Laravel encrypted cast). Enrollment, challenge success/failure,
  and recovery-code use all emit audit events.
- Collaborators (non-admin) can enroll voluntarily; it is required only for admin tiers.

## 4. Account states

```
active ⇄ suspended        (admin action, audited, reversible — blocks login immediately)
active → deactivated      (departure/erasure path; login blocked; history preserved)
```

State checks run at login **and** per-request middleware, so suspension takes effect on live
sessions, not at next login.

## 5. Password & credential policy

- Argon2id hashing (Laravel default bcrypt acceptable; Argon2id preferred — final call in ADR-003).
- Length-based strength (12+ chars) over composition rules; checked against known-breached
  passwords (offline k-anonymity check) — no forced periodic rotation (NIST-aligned).
- All authentication events (login success/failure, lockout, reset request/use, MFA events)
  → `audit_events` with IP + user agent in context.

## 6. What M1 demonstrates

Admin signs in on staging → MFA challenge → authenticated admin → role/permission check
visibly allows admin surface and denies a non-admin account. (Demo 2 of the M1 acceptance.)
