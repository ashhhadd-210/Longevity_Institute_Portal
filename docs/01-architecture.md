# 01 — System Architecture

**Status:** Draft for internal review · **Milestone:** M1 · **Owner:** Waga C / Pronto Digital

## 1. Shape of the system

One server-rendered Laravel monolith, one PostgreSQL database, one queue, one mail
provider. No microservices, no SPA, no proprietary runtime dependencies.

```
                        ┌─────────────────────────────────────────┐
                        │        activelongevityinstitute.org      │
                        │   (existing site: Collaborator Login +   │
                        │        Become a Collaborator form)       │
                        └───────────────┬─────────────────────────┘
                                        │ HTTPS (Cloudflare DNS/proxy)
                        ┌───────────────▼─────────────────────────┐
                        │        portal.activelongevity…org        │
                        │      Laravel 12 (PHP 8.3) monolith       │
                        │  ┌────────────────────────────────────┐  │
   Blade + Livewire ────┼─▶│ HTTP layer (routes, Livewire,      │  │
   Tailwind CSS         │  │ controllers, form requests,        │  │
                        │  │ policies)                          │  │
                        │  └───────────────┬────────────────────┘  │
                        │  ┌───────────────▼────────────────────┐  │
                        │  │ Service layer (Admission, Identity,│  │
                        │  │ Profile, Directory, Governance)    │  │
                        │  │  — the only place business rules   │  │
                        │  │    live; emits domain events       │  │
                        │  └───────┬───────────────┬────────────┘  │
                        │  ┌───────▼──────┐  ┌─────▼────────────┐  │
                        │  │ Eloquent     │  │ Domain events →  │  │
                        │  │ models       │  │ queued listeners │  │
                        │  └───────┬──────┘  └─────┬────────────┘  │
                        └──────────┼───────────────┼───────────────┘
                                   │               │
                    ┌──────────────▼──┐   ┌────────▼─────────────────┐
                    │ DO Managed      │   │ Queue worker (database    │
                    │ PostgreSQL 16   │   │ driver) → Postmark mail,  │
                    │                 │   │ audit writes, notifications│
                    └─────────────────┘   └──────────────────────────┘

        Supporting: DO Spaces (photographs) · Sentry (errors) · Postmark (email)
                    GitHub + Actions (CI/CD) · Cloudflare (DNS, TLS, WAF)
```

## 2. Layering rules

| Layer | Responsibility | May call | Must never |
|-------|---------------|----------|------------|
| HTTP (routes, Livewire components, controllers, form requests) | Translate requests to service calls; render views | Services, policies | Contain business rules or touch mail/queue directly |
| **Services** (`app/Services`) | All business rules: admission, invitation issue, decision recording, profile completion, visibility | Models, events | Render views, know about HTTP |
| Models (Eloquent) | Persistence, relationships, casts, scopes | — | Contain workflow logic |
| Events + queued listeners | Side effects: send email, write audit events, notify | Services, mail | Be invoked inline for slow work |

**One service layer, two consumers** (proposal commitment): the web UI calls services today;
a future HTTP API calls the same classes tomorrow. Rules are never reimplemented per
consumer, so they cannot drift.

**Events and queued jobs from day one:** significant actions (`InvitationIssued`,
`ApplicationDecided`, `CollaboratorAdmitted`, `ProfileCompleted` …) emit domain events.
Notifications and audit writes subscribe. A future digest, webhook, or integration is a new
subscriber — an additive change, never surgery on working code. Queued delivery also means
a slow mail provider never delays a person's registration.

## 3. Module boundaries (within one conventional codebase)

Deliberately **not** a DDD module tree — conventional Laravel layout, with boundaries
expressed through service class grouping and test structure (see doc 04 and ADR-008).
The conceptual modules:

- **Identity** — users, authentication, MFA, sessions, account states
- **Governance** — roles, permissions, role assignments, audit events
- **Admission** — applications, referrals, invitations, decisions, registration
- **Community** — collaborators, profiles, expertise, organisations, directory visibility
- **Privacy** — consents, data requests (export/erasure)
- **Messaging (transactional)** — outbound email, delivery logging, bounce handling

A rule of thumb enforced in review: code in one module reaches another module **through its
service**, not through its models.

## 4. Environments

| Environment | Purpose | Data | URL (proposed) |
|-------------|---------|------|----------------|
| Local | Development | Seeded fixtures | `localhost` |
| Staging | Institute review, milestone demos | Seeded/synthetic only — never real collaborator data | `staging.portal.activelongevityinstitute.org` |
| Production | Live Portal | Real | `portal.activelongevityinstitute.org` |

Staging is available to the Institute continuously from M1 (proposal commitment). Exact
hostnames to be confirmed with Linas alongside DNS work; the application makes no
assumption about its hostname.

## 5. Explicitly out of architectural scope for the Foundation Release

Not designed around, only *not precluded*: separate frontend/SPA, mobile app, public API,
microservices, search service (PostgreSQL full-text/trigram covers realistic scale),
messaging/groups/feeds, analytics warehouse. Each future roadmap item must be reachable by
**adding** code (a new service, subscriber, or surface), not by editing the core — the
standing test from the proposal.
