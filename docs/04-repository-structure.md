# 04 — Repository Structure, Branching & CI

**Status:** Draft for internal review · **Milestone:** M1

## 1. Principle

Conventional Laravel, used conventionally. A new developer must find things where the
framework says they will be — that is the Institute's continuity requirement, and it is why
we deliberately rejected a bespoke DDD module tree (ADR-008). Domain boundaries are
expressed through **service class grouping**, not directory invention.

## 2. Layout

```
active-longevity-portal/
├── app/
│   ├── Enums/                    # AccountState, InvitationStatus, DecisionType, ...
│   ├── Events/                   # InvitationIssued, ApplicationDecided, CollaboratorAdmitted, ...
│   ├── Listeners/                # queued: SendInvitationEmail, RecordAuditEvent, ...
│   ├── Livewire/                 # UI components, grouped by surface (Admin/, Auth/, Profile/)
│   ├── Mail/                     # Mailables — one class per transactional message type
│   ├── Models/                   # Eloquent models (User, Collaborator, Invitation, ...)
│   ├── Policies/                 # authorization enforcement (backed by permission checks)
│   ├── Services/
│   │   ├── Admission/            # InvitationService, ApplicationService, ReferralService,
│   │   │                         #   DecisionService, RegistrationService
│   │   ├── Identity/             # AuthService hooks, MfaService, AccountStateService
│   │   ├── Governance/           # RoleService, AuditRecorder
│   │   ├── Community/            # ProfileService, DirectoryService, ExpertiseService
│   │   └── Privacy/              # ConsentService, DataRequestService
│   └── Support/                  # small shared helpers only — resist a "utils" dump
├── database/
│   ├── migrations/               # additive; never rewritten after merge to main
│   └── seeders/                  # RolePermissionSeeder, ExpertiseSeeder, DemoDataSeeder
├── docs/                         # this package + DATABASE.md, DEPLOYMENT.md, ...
│   └── adr/
├── resources/views/              # Blade; theme tokens in one place (design handover risk)
├── routes/  (web.php, admin.php) # admin routes separately grouped + middleware-gated
├── tests/
│   ├── Feature/                  # per-workflow: Admission/, Identity/, Governance/
│   └── Unit/                     # services in isolation
├── .env.example                  # every variable, documented, no real values
├── .github/workflows/ci.yml      # + deploy-staging.yml, deploy-production.yml
├── CHANGELOG.md                  # plain-language, per-milestone
└── README.md                     # clean-machine setup in under 30 minutes
```

## 3. Branching & review discipline

- `main` — protected; deployable; merge only via PR with green CI. Repository lives in the
  **Institute's GitHub organization from commit one** — never transferred later.
- `feature/<short-name>` branches; small PRs, one concern each.
- Even as a solo developer: PRs are written for the *next* developer — description says what
  and why. Milestones tagged (`m1.0`, `m2.0`, …) so contractual deliverables map to git history.
- Conventional commit style (`feat:`, `fix:`, `docs:`, `chore:`) for a readable changelog.

## 4. CI pipeline (GitHub Actions — every PR and push to main)

| Stage | Tool | Gate |
|-------|------|------|
| Static analysis | Larastan (PHPStan) | Must pass |
| Code style | Laravel Pint | Must pass |
| Tests | Pest/PHPUnit against a **PostgreSQL 16 service container** (same engine as prod — no SQLite-in-CI shortcut) | Must pass |
| Migrations | `migrate:fresh --seed` runs clean on empty Postgres | Must pass |
| Dependency security | `composer audit` + Dependabot + GitHub secret scanning | Must pass / alert |

## 5. Deployment

- **Staging:** auto-deploy on merge to `main`.
- **Production:** manual trigger against a tag, after staging verification. Deploy = SSH
  script (maintenance window acceptable at this scale): pull → composer install → migrate →
  cache config/routes/views → restart queue worker. Rollback = redeploy previous tag +
  documented DB restore path (doc 08).
- Secrets live in GitHub Actions environments (deploy) and server `.env` (runtime), sourced
  from the Institute's password manager vault. **No secret ever enters the repository**;
  `.env.example` documents every variable with placeholder values.
