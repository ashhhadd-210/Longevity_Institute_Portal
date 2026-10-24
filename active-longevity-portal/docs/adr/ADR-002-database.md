# ADR-002 — Database: PostgreSQL 16 (DigitalOcean Managed)

**Status:** Accepted · **Date:** 2026-08

## Decision
PostgreSQL 16 on DigitalOcean Managed Databases for all environments' server-side data;
no SQLite even in development/CI.

## Context
The admission record demands relational integrity (institutional history must not cascade
away); profiles need evolving attributes (JSONB); directory search must work without a
separate service at any realistic scale (native full-text + `pg_trgm`). Names must survive
full Unicode extended Latin (UTF-8 + ICU collation, `citext` for emails).

## Alternatives considered
- **MySQL/MariaDB:** viable, but weaker JSONB ergonomics, no `citext`, weaker trigram search.
- **SQLite for dev/CI:** rejected — behavioral differences (constraints, citext, collations)
  make tests lie; CI runs the same engine as production.
- **External search (Meilisearch/Elastic):** rejected as premature — below ~200 collaborators,
  Postgres native search covers real behavior; an engine can be added later additively.

## Consequences
Managed backups + PITR from day one, plus independent nightly logical dumps for
provider-portability. All developers run Postgres locally (documented in README).
