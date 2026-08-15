# ADR-004 — Authorization: RBAC as data, enforced through policies

**Status:** Proposed (internal review) · **Date:** 2026-08

## Decision
Roles, permissions and role-assignments are database rows (`roles`, `permissions`,
`permission_role`, `role_assignments` with assigned_by/assigned_at). Application code checks
**permissions, never role names**, through Laravel Policies as the single choke point.
Roles flag `requires_mfa`. No `is_admin` boolean exists anywhere.

## Alternatives considered
- **`is_admin` flag / enum column:** rejected explicitly in the proposal — future Institute
  officers, regional reviewers and read-only staff must be configuration, not schema migration.
- **spatie/laravel-permission package:** solid, but the model is small and central enough to
  own outright — fewer dependencies on a ten-year platform, and role assignment provenance
  (who granted, when) is first-class in our schema rather than bolted on.
- **Full ABAC/policy-engine:** over-engineering for the Foundation; policies already give us
  contextual checks ("under which circumstances") where needed.

## Consequences
An authorization matrix test (role × endpoint) ships in M1 and becomes the standing
regression net. Role assignment is initially Founder-reserved. Adding a reviewer role for
M2's admission queue is a seeder change.
