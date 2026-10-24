# ADR-005 — Hosting: DigitalOcean droplets + Managed PostgreSQL, Cloudflare in front

**Status:** Accepted (accounts created by the Institute, Aug 2026) · **Date:** 2026-08

## Decision
Two conventionally-provisioned Ubuntu droplets (staging, production) under the Institute's
DigitalOcean team, DO Managed PostgreSQL, DO Spaces for photographs, Cloudflare for
DNS/TLS/proxy. Provisioning captured as a documented, repeatable script in the repository.

## Alternatives considered
- **PaaS (Heroku/Vercel/DO App Platform):** simpler deploys, rejected for proprietary
  platform lock-in and weaker portability story; the brief requires the Portal be fully
  portable and provider-independent.
- **Kubernetes/containers-in-production:** rejected — operational complexity wildly
  disproportionate to a two-droplet workload the Founder's organisation must be able to
  inherit.
- **Shared PHP hosting:** rejected — no queue workers, no infrastructure-as-record, weak
  security posture.

## Consequences
Predictable ~$40–60/month billed directly to the Institute. The provisioning script is half
of the M4 cold-start test. Vertical scaling headroom is ample for any realistic community
size; nothing precludes moving providers, since backups include provider-independent dumps.
