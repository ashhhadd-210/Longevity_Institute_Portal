# 09 — Milestone 1 Task Breakdown

**Status:** Draft for internal review · **Budget:** $600 · **Duration:** 3.5 weeks
**Objective:** the architecture the next ten years will be built on, and the identity/security
layer everything depends on. *Not* screens.

## Sequencing

### Week 0.5 — Access, DNS reality-check, repo bootstrap
- [ ] Accept all invitations (GitHub org, DigitalOcean, Cloudflare, Postmark); verify access tiers; credentials into Institute vault
- [ ] **Audit current Cloudflare DNS zone** — record existing mail/site records *before* touching anything (SPF changes affect Linas's live email)
- [ ] Create `active-longevity-portal` private repo in the org (if not already); protect `main`
- [ ] Laravel 12 scaffold, Pint + Larastan, `.env.example`, this docs package committed — **commit one is Institute-owned**
- [ ] CI skeleton: style + static analysis + tests green on empty app, Postgres 16 service container wired

### Week 1 — Database foundation & domain model
- [ ] DO Managed PostgreSQL provisioned (staging + production databases, private networking)
- [ ] Extensions: `citext`, `pg_trgm`; UTF-8/ICU collation verified with diacritic fixtures
- [ ] Full M1 migration set per docs 02/03: identity, governance, admission pipeline, community, privacy, email log
- [ ] `CHECK` constraints, FKs with explicit `ON DELETE`, indexes per doc 03 §3; audit table append-only enforcement
- [ ] Seeders: roles/permissions, placeholder expertise vocabulary, demo fixtures
- [ ] `DATABASE.md` + generated ERD committed
- **Gate:** `migrate:fresh --seed` clean in CI; model relationship tests pass

### Week 2 — Identity, MFA, authorization
- [ ] Fortify-backed auth: login, logout, password reset (enumeration-safe), sessions per doc 05
- [ ] TOTP MFA + recovery codes; `requires_mfa` middleware wall on admin route group
- [ ] RBAC: models, `hasPermission`, policies, seeded catalog per doc 06
- [ ] Account states with per-request enforcement
- [ ] `RegistrationService` (M2's consumer) built and covered by tests now — invitation → user+collaborator+profile, atomically
- [ ] **Authorization matrix test** (role × endpoint allow/deny)
- [ ] Audit recorder listening to identity/governance events
- **Gate:** Demo-2 storyline works locally end-to-end

### Week 3 — Email, environments, deployment, backups
- [ ] Staging + production droplets provisioned via documented script (doc 07 §6)
- [ ] Deploy pipelines: auto-staging on `main`, manual production on tag
- [ ] Postmark: servers, DKIM/Return-Path/SPF/DMARC(`p=none`) in Cloudflare; staging allowlist
- [ ] Queued mail → `EmailMessage` logging → Postmark webhooks (delivery/bounce/spam)
- [ ] **Inbox placement verified: Gmail, Outlook, one university address** — screenshots archived for the demo
- [ ] Nightly `pg_dump` to independent Spaces bucket; **restore rehearsed on staging and timed**
- [ ] Sentry wired (both envs, release tagging); uptime check on staging
- **Gate:** merge-to-main reaches staging untouched by hand; restore procedure reproducible from runbook alone

### Week 3.5 — Documentation, ADRs, demo prep
- [ ] ADR-001…008 finalized (accepted status where agreed with Linas)
- [ ] `README` (clean-machine < 30 min), `ARCHITECTURE.md`, `DEPLOYMENT.md`, `CONFIGURATION.md`, `SECURITY.md`, runbook skeleton, `CHANGELOG.md` (M1 entry)
- [ ] Rehearse all five demos; Monday progress note + demo scheduling with Linas (Chicago time)

## The five acceptance demos

| # | Demo | Proof shown to Linas |
|---|------|---------------------|
| 1 | Infrastructure | Commit → CI → staging deploy, live; Sentry capturing a forced test error; everything in Institute accounts |
| 2 | Identity | Admin login → MFA challenge → admin surface; non-admin denied by role/permission check |
| 3 | Email | Portal-triggered message via Postmark on the Institute domain → arrives in Gmail + Outlook + university inbox; delivery status visible in the log |
| 4 | Recovery | Staging DB restored from backup live; seeded data verified intact |
| 5 | Architecture | ER diagram, architecture doc, ADRs, README, repository tour, staging URL |

## Explicitly NOT in M1 (protecting the $600)

Dashboards beyond a minimal authenticated admin shell · directory UI · search · profile
editing UI · application/referral/invitation UIs (M2) · visual identity (M3, tokens ready) ·
messaging/groups/analytics/matching (roadmap) · notification management UI (deferred by
proposal) · migrations for wishlist-signal entities (designed in doc 02, added additively
when surfaced).

## M1 definition of done

All five demos pass in front of Linas from **staging** (not localhost); CI green on `main`;
docs current; no credential outside the Institute's vault; GO/NO-GO recorded → M2.
