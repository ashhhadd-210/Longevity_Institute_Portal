# Active Longevity Institute — Collaborator Portal

**Milestone 1 Architecture Package — pre-implementation review draft**

This repository will contain the Institute Collaborator Portal, a secure, invitation-based
professional community platform owned by the Active Longevity Institute, Chicago.

The Portal is not a networking product. It is the operational instrument of the Institute's
curation process: it exists so the Institute can exercise judgement about who belongs,
preserve that judgement institutionally, and enable trusted interdisciplinary relationships.

## Status

No application code has been written yet — deliberately. The documents in `docs/` are the
Milestone 1 architecture package, produced for internal review **before** implementation
begins, per the project's working method: *architect first, then build against acceptance
criteria*.

## Reading order

| Doc | Contents |
|-----|----------|
| [docs/01-architecture.md](docs/01-architecture.md) | System architecture, layering, module boundaries |
| [docs/02-domain-model.md](docs/02-domain-model.md) | Entity catalog, lifecycles, provenance chain |
| [docs/03-erd.md](docs/03-erd.md) | Entity-relationship diagram and annotated schema |
| [docs/04-repository-structure.md](docs/04-repository-structure.md) | Code layout, branching, CI pipeline |
| [docs/05-authentication-model.md](docs/05-authentication-model.md) | Auth flows, MFA, sessions, account states |
| [docs/06-authorization-model.md](docs/06-authorization-model.md) | Roles, permissions, enforcement points |
| [docs/07-infrastructure.md](docs/07-infrastructure.md) | Environments, DNS, email, secrets, deployment |
| [docs/08-security-model.md](docs/08-security-model.md) | Threat model, controls, token design, backup/restore |
| [docs/09-m1-task-breakdown.md](docs/09-m1-task-breakdown.md) | Sequenced M1 tasks mapped to the five acceptance demos |
| [docs/adr/](docs/adr/) | Architecture Decision Records (ADR-001 …) |

## Governing principles (from the accepted proposal)

1. **Phase the surface area, not the foundation.** M1 models the full domain, even where
   the interface arrives in M2/M3. Future features add code; they do not edit the core.
2. **Roles and permissions are data, never booleans.** There is no `is_admin` flag anywhere
   in this system.
3. **Institute ownership from commit one.** Every account, credential and repository is the
   Institute's; the developer holds delegated access only.
4. **Documentation is a milestone acceptance condition**, verified at the end by a cold-start
   deployment test.
5. **Conventional technology, used conventionally.** Novelty is a liability in a platform
   intended to outlive its first developer.
