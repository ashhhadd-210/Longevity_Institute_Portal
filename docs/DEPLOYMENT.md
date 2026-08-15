# Deployment

## Environments

| Environment | Host | Deploys | Data |
|-------------|------|---------|------|
| Staging | DigitalOcean droplet (`staging.portal.…`) | Automatically on every push to `main` | Synthetic only |
| Production | DigitalOcean droplet (`portal.…`) | Manually, from a tag, after staging verification and the Institute's GO | Real |

Both workflows are gated by repository variables (`DEPLOY_STAGING_ENABLED`,
`DEPLOY_PRODUCTION_ENABLED`) so they stay inert until infrastructure exists.
Set them to `true` in the repository settings to activate.

## First-time server setup

1. Create an Ubuntu LTS droplet in the Institute's DigitalOcean team (NYC3),
   attached to the VPC of the managed PostgreSQL cluster.
2. As root: `bash scripts/provision-server.sh` (installs nginx, PHP-FPM 8.3,
   the queue worker service, firewall, fail2ban, SSH hardening).
3. Complete the manual steps the script prints: deploy key, `.env`, TLS
   origin certificate, DNS, first deploy, queue start, admin accounts.
4. Create a `staging`/`production` environment in GitHub with secrets:
   `*_SSH_KEY` (private key of the deploy user), `*_HOST`, `*_USER`.

## Release flow

```
merge to main ──▶ CI (style, static analysis, audit, migrations, tests)
                        │ green
                        ▼
                deploy-staging (rsync + migrate + cache + queue:restart)
                        │ Institute review on staging
                        ▼
                tag (e.g. m1.0) ──▶ deploy-production (manual, maintenance window)
```

Migrations are additive (doc 03), so a standard release needs no downtime;
the production workflow still wraps the release in `artisan down`/`up` for a
conservative window measured in seconds.

## Rollback

1. Re-run **Deploy production** with the previous tag.
2. Because migrations are additive, the previous release runs against the
   newer schema. If a migration itself must be reverted, follow the restore
   procedure in [RUNBOOK.md](RUNBOOK.md) instead — never `migrate:rollback`
   in production against institutional data.

## Cold-start test (Milestone 4 acceptance)

A clean droplet + this repository + these documents must yield a working
Portal with no other knowledge. The recorded session is a formal acceptance
condition of Milestone 4.
