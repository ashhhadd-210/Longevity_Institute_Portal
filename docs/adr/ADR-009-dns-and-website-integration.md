# ADR-009 — DNS Management and Institute Website Integration

**Status:** Proposed (needs an Institute decision) · **Date:** 2026-08

## Context — the domain as it actually is

Verified against public DNS on 15 August 2026:

| Record | Value | Meaning |
|--------|-------|---------|
| NS | `ns67/ns68.domaincontrol.com` | DNS is hosted at **GoDaddy**, not Cloudflare |
| MX | `aspmx.l.google.com` and alternates | The Institute's mail runs on **Google Workspace** — this is the Founder's live address |
| SPF | `v=spf1 include:dc-aa8e722993._spfm.activelongevityinstitute.org ~all` | GoDaddy's SPF-merge indirection; the include target is a record inside this same zone |
| DMARC | `v=DMARC1; p=quarantine; adkim=r; aspf=r; rua=…@onsecureserver.net` | **Already enforcing at quarantine**, with relaxed alignment |
| A (root) | `185.230.63.107`; `www` → `pointing.wixdns.net` | The Institute website is hosted on **Wix** |
| `portal.*`, `staging.portal.*` | absent | The Portal subdomains are unclaimed |

Three consequences follow, none of which were visible when the architecture
package was written.

## Decision 1 — Portal email is configured additively; existing records are not edited

DMARC is already at `p=quarantine`, so the assumption in ADR-006 that we would
begin at `p=none` and tighten is wrong: mail that fails alignment is quarantined
**today**. The Portal must therefore be aligned before its first send, not after.

It can be, without touching a single existing record:

- **DKIM** — a new `TXT` record on a Postmark-specific selector.
- **Return-Path** — a new `CNAME` on a subdomain (e.g. `pm-bounces.portal`),
  which gives SPF alignment from Postmark's own envelope domain.

Because DMARC specifies relaxed alignment (`aspf=r`, `adkim=r`), a subdomain
Return-Path aligns with the organisational domain. **The root SPF record, the
MX records and the DMARC policy are left exactly as they are**, so there is no
mechanism by which Portal setup can disturb the Founder's live mail. No DMARC
change is required at any point.

## Decision 2 — DNS stays at GoDaddy for the Foundation Release

The Institute created a Cloudflare account, but the domain has never been
delegated to it, so Cloudflare is currently outside the DNS path entirely.
Moving to it means changing nameservers at GoDaddy — a change that carries the
Institute's live website and the Founder's email with it.

The specific hazard: the SPF record does not name Google directly. It defers to
`dc-aa8e722993._spfm.activelongevityinstitute.org`, a GoDaddy-managed record
*inside the zone*. Cloudflare's onboarding scan discovers records by guessing
common names; it cannot enumerate a zone. A vendor-specific name like this can
easily be missed, and if it is, SPF silently breaks for the Founder's mail —
which, under a `p=quarantine` policy, means her mail starts landing in spam.
Cloudflare's free plan also offers no partial (subdomain-only) mode, so there is
no smaller version of this change available.

**Recommendation:** keep DNS at GoDaddy for now and add only the records the
Portal needs. Revisit the Cloudflare migration later as a deliberate, scheduled
task, beginning with a full zone export from GoDaddy so every record is
replicated rather than rediscovered. TLS is then issued on the droplet itself
(Let's Encrypt) rather than through a Cloudflare origin certificate.

Rejected alternative — migrating now: the benefits Cloudflare brings here
(proxying, WAF, faster DNS) are modest for an invitation-only Portal with a
small, curated membership, and they do not justify putting a live institutional
mail flow at risk during foundation work.

## Decision 3 — The Portal is a subdomain; its forms are hosted, not rebuilt in Wix

The Institute website is a Wix site, so ADR-001's note about co-hosting with a
PHP website does not apply and no shared environment is possible.

- The Portal lives at `portal.activelongevityinstitute.org` (staging at
  `staging.portal.…`).
- The **Collaborator Login** entry point is a styled link from the Wix site to
  the Portal — no integration work inside Wix.
- The **Become a Collaborator** application form (Milestone 2) is built and
  hosted by the Portal, styled to the Institute's visual language, and linked
  (or iframe-embedded) from a Wix page. Rebuilding it as a Wix form would put
  the application record outside the Portal's domain model and outside the
  admission pipeline, which is precisely what the permanent admission record
  exists to prevent.

## Consequences

- Portal email setup is purely additive and reversible; the Founder's mail is
  never in the path of a Portal change.
- The Cloudflare account remains unused until a migration is scheduled; nothing
  in the architecture depends on it (ADR-005 lists it for DNS/TLS only).
- Whoever holds GoDaddy access must add three records, or delegate that access —
  this is the one infrastructure dependency the Institute cannot delegate to the
  developer as things stand.
