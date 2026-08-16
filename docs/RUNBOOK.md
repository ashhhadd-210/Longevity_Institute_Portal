# Operations Runbook

Written for whoever operates the Portal — including a future developer who
has never seen it. Every procedure assumes only repository access and the
Institute's password manager vault.

## Deploy

Staging deploys itself on merge to `main`. Production: GitHub → Actions →
**Deploy production** → run with the release tag. Details in
[DEPLOYMENT.md](DEPLOYMENT.md).

## Roll back

Re-run **Deploy production** with the previous tag (schema is additive; old
code runs safely against newer schema).

## Restore the database

Two independent paths (doc 07 §5):

**A. DigitalOcean managed backup / point-in-time recovery**
1. DigitalOcean → Databases → portal cluster → Backups & restores.
2. Restore creates a NEW cluster; it never overwrites the live one.
3. Verify the restored data (`psql`, spot-check `collaborators`,
   `audit_events` row counts), then repoint `DB_HOST` in `/var/www/portal/.env`
   and `php artisan config:cache && php artisan queue:restart`.

**B. Nightly logical dump (provider-independent)**
1. Fetch the dump: `s3cmd get s3://<bucket>/database/portal-<stamp>.dump`
2. Restore into an empty database:
   `pg_restore --no-owner --dbname="$DATABASE_URL" portal-<stamp>.dump`
3. Verify, then repoint the application as above.

### Rehearsal record

Performed on staging, 16 August 2026, using path B exactly as written above:

| Check | Source (`portal_staging`) | Restored copy |
|-------|---------------------------|---------------|
| roles | 3 | 3 |
| permissions | 20 | 20 |
| users | 1 | 1 |
| audit events | 2 | 2 |

The restored copy also **retained the append-only protection**: an attempted
`UPDATE` on `audit_events` in the restored database was refused by the trigger
(`audit_events is append-only: UPDATE is not permitted`). Institutional history
survives disaster recovery still un-rewritable, which is the property that
actually matters.

Re-rehearse after any major schema change.

## Rotate a credential

1. Generate the new value at the provider (Postmark token, DB password,
   Spaces key, Sentry DSN).
2. Store it in the Institute's vault (the vault is the source of truth).
3. Update `/var/www/portal/.env` on the affected server(s) and any GitHub
   environment secret that carries it.
4. `php artisan config:cache && php artisan queue:restart`
5. Revoke the old value at the provider. Confirm the app still functions.

If a secret may have leaked: rotate first, investigate second, and review
`audit_events` for the exposure window.

## Suspend a user immediately

```
php artisan tinker
>>> App\Models\User::where('email', '...')->first()
      ->forceFill(['account_state' => 'suspended'])->save();
```

Suspension is enforced per request — live sessions are ejected on their next
request. Record the reason with the governance audit trail (an admin surface
for this arrives in M2).

## Incident response

1. Confirm impact: Sentry (errors), uptime monitor, `journalctl -u portal-queue`.
2. Stabilise: roll back to the previous tag, or `php artisan down` if data is
   at risk.
3. Preserve evidence: do not truncate logs; `audit_events` is append-only by
   design.
4. Communicate to the Institute: what is affected, since when, next update.
5. Post-incident: write the timeline into the changelog and, where the cause
   was structural, an ADR.

## Queue

The worker runs as systemd unit `portal-queue`. Status: `systemctl status
portal-queue`. After every deploy the workflow runs `queue:restart`. Failed
jobs: `php artisan queue:failed` / `queue:retry all`.
