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

```
Cloudflare DNS (activelongevityinstitute.org)
 ├── portal.…            → production droplet
 ├── staging.portal.…    → staging droplet          (access-restricted)
 └── Postmark DNS: DKIM (TXT), Return-Path (CNAME), SPF include, DMARC policy

DigitalOcean (region: NYC3 — near Chicago; ADR-007)
 ├── droplet: portal-production   (Ubuntu LTS, nginx, PHP-FPM 8.3, queue worker via systemd)
 ├── droplet: portal-staging      (same shape, smaller)
 ├── Managed PostgreSQL 16        (private networking only; separate staging + production DBs;
 │                                 automated daily backups + PITR; app connects via connection pool)
 └── Spaces bucket: photographs   (private; served via short-lived signed URLs)
```

Staging mirrors production's shape (same PHP/Postgres versions, same deploy pipeline) so a
staging pass is meaningful. Estimated running cost stays within the proposal's ~$40–60/month
envelope, billed directly to the Institute.

## 3. Email infrastructure (M1's most consequential deliverable)

1. Postmark server per environment (staging sends only to team addresses via allowlist).
2. Sending domain `activelongevityinstitute.org`: DKIM + Return-Path verified in Cloudflare;
   SPF include; DMARC starting `p=none` with reports monitored, tightening to `quarantine`
   once clean (do not jump straight to `reject` on a domain that already sends founder email —
   verify existing mail flows first).
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
