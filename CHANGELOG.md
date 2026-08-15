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
