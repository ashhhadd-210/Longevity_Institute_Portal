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
