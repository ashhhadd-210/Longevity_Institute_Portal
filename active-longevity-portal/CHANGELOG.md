# Changelog

A plain-language record of what each milestone delivered, written for the Institute.

## Milestone 1 — Foundation & Identity (in progress)

**Goal:** the technical foundation the Portal will be built on — repository, environments,
database, authentication with administrator MFA, roles and permissions, email
infrastructure, backups, and the architecture documentation set.

Delivered so far:

- Institute-owned repository initialised with the full architecture package
  (`docs/01`–`09` and the first eight Architecture Decision Records)
- Laravel 12 application scaffold on PHP 8.3
- Continuous integration pipeline: code style, static analysis, dependency security
  audit, and the full test suite running against PostgreSQL 16 on every change
- The complete Foundation domain model in PostgreSQL: identity (users with separate
  name fields and full Unicode support), roles and permissions as data, the full
  admission pipeline (applications, referrals, decisions, invitations with hashed
  single-use tokens), collaborators with directory visibility controls, professional
  profiles with structured CV sections and capped external links, the
  Institute-controlled expertise vocabulary, organisations, consent and data-request
  records, the append-only institutional audit stream (enforced by the database
  itself), and the outbound email delivery log
- Seeded role/permission catalog and a placeholder expertise vocabulary
- A domain test suite proving the foundation's guarantees: Unicode names survive
  intact, email uniqueness is case-insensitive, audit history cannot be rewritten,
  the five-link profile cap holds in the schema, directory visibility rules behave
  exactly as specified, and admission provenance is traversable years later from
  collaborator back to the original application
- The identity layer: sign-in, sign-out and password reset with rate limiting and
  account-enumeration protection; TOTP two-factor authentication with recovery
  codes, mandatory for administrative roles and enforced by a hard enrollment
  wall before any administrative page; account suspension that takes effect on
  live sessions immediately
- Authorization enforced end to end through data-driven permissions, with an
  authorization matrix test as the standing regression net
- The admission services (foundation for Milestone 2): invitation issue, re-send
  with token rotation, revocation, and registration that atomically creates the
  admitted collaborator with consent capture — all covered by tests
- Email infrastructure rails: every outbound message logged with per-message
  delivery status updated by Postmark webhooks; invitation email template;
  transactional message stream configuration
- A minimal administrative dashboard, the authentication interface, and the
  `portal:create-admin` command for bootstrapping administrator accounts
- Deployment machinery: staging auto-deploy and manual tag-based production
  deploy workflows (inert until servers exist), the server provisioning script,
  and the independent nightly database backup script
- Operations documentation: deployment, runbook (restore, rotation, incident
  response), configuration, database and security references
- Sentry SDK installed for error monitoring (activates when the DSN is set)
- The Portal now answers on its own addresses over an encrypted connection:
  `portal.activelongevityinstitute.org` and
  `staging.portal.activelongevityinstitute.org`. Certificates renew
  automatically, plain connections are redirected to the secure address, and
  session cookies are marked so they can never travel unencrypted
