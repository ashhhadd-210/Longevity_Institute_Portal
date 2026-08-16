# 07 — Infrastructure & Environments

**Status:** Draft for internal review · **Milestone:** M1

## 1. Ownership map (all Institute-owned; developer has delegated access — confirmed done 12–13 Aug 2026)

| Service | Account | Role |
|---------|---------|------|
| GitHub | `@ActiveLongevityInstitute` org, `active-longevity-portal` private repo | Source of truth, CI/CD |
| DigitalOcean | Institute team | Droplets (staging + production), Managed PostgreSQL, Spaces |
| Cloudflare | Institute account (`linas@activelongevityinstitute.org`) | DNS, TLS, proxy/WAF |
| Postmark | Institute account | Transactional email + delivery webhooks |
| Sentry | Institute account | Error monitoring, both environments |
| Password manager | Institute vault | Every credential; developer never holds sole access |

## 2. Target topology

> **Amended August 2026 (ADR-009).** DNS is hosted at GoDaddy, not Cloudflare; the
> Institute website is on Wix; the domain already publishes `DMARC p=quarantine` over
> Google Workspace mail. DNS stays at GoDaddy for the Foundation Release and the Portal
> records are added additively. TLS is issued on the droplet (Let's Encrypt).

```
DNS at GoDaddy (activelongevityinstitute.org)  — unchanged records: MX, SPF, DMARC, Wix
 ├── portal.…            → production droplet   (new A record)
 ├── staging.portal.…    → staging droplet      (new A record, access-restricted)
 └── Postmark: DKIM (TXT) + Return-Path (CNAME) on a Portal subdomain — additive only

DigitalOcean (region: NYC3 — near Chicago; ADR-007)
 ├── droplet: portal-production   (Ubuntu 24.04 LTS, nginx, PHP-FPM 8.3, queue worker via systemd)
 ├── droplet: portal-staging      (same shape, smaller)
 ├── Managed PostgreSQL 16        (private networking only; separate staging + production DBs;
 │                                 automated daily backups + PITR; app connects via connection pool)
 └── Spaces bucket: photographs   (private; served via short-lived signed URLs — added with M3)
```

### Provisioned (16 August 2026)

All resources live in the Institute's **Active Longevity Portal** project, NYC3.

| Resource | Detail | Monthly |
|----------|--------|---------|
| `portal-production` droplet | Ubuntu 24.04 LTS, 1 vCPU / 2 GB / 50 GB | $12 |
| `portal-staging` droplet | Ubuntu 24.04 LTS, 1 vCPU / 1 GB / 25 GB | $6 |
| `portal-postgres` cluster | PostgreSQL 16, 1 vCPU / 1 GB, databases `portal_production` + `portal_staging` | $15 |
| | **Total** | **$33** |

Access control in force:

- **Cloud firewall `portal-web`** on both droplets: inbound 22/80/443 only.
- **Database trusted sources**: the two droplets *only* — the cluster is not
  reachable from the public internet, including from the developer's machine.
- Applications connect over the **private** hostname
  (`private-portal-postgres-…`), so database traffic never leaves the VPC.
- SSH is key-only (`portal-deploy` ed25519); password authentication disabled
  by the provisioning script.

Both environments are deployed and serving from GitHub Actions: staging
automatically on merge to `main`, production manually from tag `m1.0`.

### Outstanding dependencies

These require credentials or access the developer does not hold:

| Item | Blocked on | Effect until resolved |
|------|-----------|----------------------|
| Postmark server, sender domain, DKIM values | Postmark **account** API token (account-owner level) | No Portal email; `MAIL_MAILER=log` |
| Three DNS records (`portal`, `staging.portal`, DKIM/Return-Path) | GoDaddy access, which the Institute has not delegated (ADR-009) | Environments reachable by IP only |
| TLS certificates | The DNS records above (Let's Encrypt validates by hostname) | HTTP only; `SESSION_SECURE_COOKIE=false` |
| Sentry error monitoring | Sentry DSN from the Institute's account | SDK installed, inactive |

Staging mirrors production's shape (same PHP/Postgres versions, same deploy pipeline) so a
staging pass is meaningful. Estimated running cost stays within the proposal's ~$40–60/month
envelope, billed directly to the Institute.

## 3. Email infrastructure (M1's most consequential deliverable)

1. Postmark server per environment (staging sends only to team addresses via allowlist).
2. Sending domain `activelongevityinstitute.org`: DKIM `TXT` and Return-Path `CNAME` added
   at GoDaddy. The existing SPF, MX and DMARC records are **not** modified — DMARC is
   already `p=quarantine` with relaxed alignment, so a subdomain Return-Path plus DKIM
   aligns the Portal without any policy change (ADR-009). Alignment must be verified
   before the first send, not after.
3. **Message streams separated:** transactional only for the Portal. Future admin broadcasts
   use a broadcast stream so reputation of invitations is never coupled to bulk sends.
4. Every send goes through the queue → `EmailMessage` row → Postmark; webhooks update
   delivery/bounce/spam status. Admin can later see per-invitation status and reissue in one
   click (M2 surface, M1 rails).
5. **Acceptance test:** delivery to inbox (not spam) verified on Gmail, Outlook, and at least
   one university/institutional address (client requirement). Candidates: any .edu contact of
   Linas's founding cohort — to be arranged with him.

## 4. Configuration & secrets

- Runtime config via `.env` only; `.env.example` documents every variable.
- Secrets flow: Institute password manager (source of truth) → GitHub environment secrets
  (deploy-time) → server `.env` (runtime). Rotation procedure documented in the runbook.
- Sentry DSN per environment; release tagging on deploy so errors map to git tags.

## 5. Backups & recovery (M1 must *prove* this, not claim it)

- DO Managed PostgreSQL: automated daily backups + point-in-time recovery.
- Additionally, a nightly logical dump (`pg_dump`) shipped to a separate Spaces bucket —
  provider-independent copies, because portability applies to data too.
- **M1 demo includes a live restore:** restore the staging database from backup, verify
  seeded records, in front of Linas. Runbook documents both restore paths with exact commands.

## 6. Server baseline

Ubuntu LTS, unattended security upgrades, UFW (22 restricted, 80/443 via Cloudflare),
SSH keys only (no password auth), fail2ban, nginx with TLS (Cloudflare origin certs),
PHP-FPM tuned to droplet size, systemd unit for the queue worker, logrotate. Provisioning
recorded as a documented, repeatable script in the repo — this is half the cold-start test.
