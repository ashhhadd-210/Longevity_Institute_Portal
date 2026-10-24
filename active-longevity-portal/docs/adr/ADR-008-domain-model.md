# ADR-008 — Domain Model Scope & Shape

**Status:** Proposed (internal review) · **Date:** 2026-08

## Decision
1. **Model the domain, not the release:** M1 migrates the full Foundation domain —
   identity, governance, the complete admission pipeline (applications, referrals,
   decisions, invitations), community (collaborators, profiles, CV entries, links,
   expertise, organisations), privacy, and email logging — even though most surfaces arrive
   in M2/M3.
2. **User ≠ Collaborator ≠ Profile** — three entities with distinct lifecycles
   (authentication identity / institutional membership / professional presentation).
3. **No `people` table in the Foundation Release:** pre-registration candidates live in the
   pipeline records (application/referral/invitation) that carry their details; provenance
   is preserved by foreign keys (collaborator → invitation → decision → application/referral).
   Cross-record deduplication is by email, surfaced to the reviewer.
4. **Wishlist-signal entities are designed but not migrated in M1:**
   `collaboration_interests`, `acronyms`/`acronym_meanings`, `conduct_reports`,
   `admin_broadcasts` are shaped in docs 02/03 and added by additive migration when their
   surface is scheduled.
5. **Conventional Laravel layout, not a DDD module tree;** boundaries live in service-class
   grouping (`app/Services/Admission`, `Community`, …) reached only through services.

## Alternatives considered
- **Person supertype entity now:** cleaner in theory for "same human across records";
  rejected for the Foundation — it adds joins to every pipeline query for a benefit that
  matters only at volumes the Institute may never reach, and it can be introduced later
  additively (backfilled from emails) without breaking provenance.
- **Migrating wishlist tables in M1:** rejected — the client explicitly marked these as
  evolving ideas; unused tables invite churn and rework, while additive migrations cost
  nothing later. The commitment made to Linas was to *account for them in the domain model*,
  which the documented designs do.
- **Single `admission_events` table separate from audit:** rejected — one append-only
  `audit_events` stream with a typed taxonomy serves both governance and the permanent
  admission record; the admission history of a person is a filtered query, not a second
  bookkeeping system.

## Consequences
M2 builds UI over schema that already exists and is already tested (RegistrationService is
exercised in M1's suite). Declined applications, expired invitations and departures are
representable states, not data loss. Every roadmap feature named to date lands as new rows,
new services, or new surfaces — not core edits.
