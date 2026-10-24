# Database

PostgreSQL 16 (ADR-002). The schema is defined entirely by the migrations in
[database/migrations](../database/migrations); the entity-relationship
diagram and table annotations live in [03-erd.md](03-erd.md), and the domain
reasoning in [02-domain-model.md](02-domain-model.md).

## Conventions in force

- UTF-8 with ICU collation; `citext` for all email columns; `timestamptz`
  everywhere (UTC in the database, localized at display).
- Status columns are `text` with `CHECK` constraints — readable in psql,
  enforced by the database, extendable by additive migration.
- Foreign keys default to `RESTRICT`: institutional history must not cascade
  away. Soft deletion only where the domain justifies it (profiles).
- `audit_events` is append-only, enforced by a trigger that rejects UPDATE
  and DELETE regardless of connection role.
- Migrations are **additive after first deployment**. Schema changes add;
  they do not rewrite (doc 01 §5).

## Local setup

```
createdb portal_dev && createdb portal_test
php artisan migrate:fresh --seed        # seeds roles, expertise, demo fixtures
php artisan test                        # runs against portal_test
```

Tests never use SQLite; CI runs the same PostgreSQL 16 as production.

## Backups

Two independent mechanisms, both rehearsed (doc 07 §5, RUNBOOK.md):
managed daily backups + point-in-time recovery at DigitalOcean, and nightly
`pg_dump` archives shipped to the Institute's object storage by
[scripts/backup-database.sh](../scripts/backup-database.sh).
