# ADR-007 — Data Residency & Hosting Region: DigitalOcean NYC3 (US)

**Status:** Proposed (confirm with Institute) · **Date:** 2026-08

## Decision
Host in DigitalOcean's NYC3 region — the Institute is in Chicago; its legal home and
administration are US-based. GDPR compliance is achieved through the Portal's design
(lawful basis + consent at registration, minimisation, export, erasure — doc 08 §3) and
appropriate safeguards for EU/UK collaborators' data, not through EU hosting.

## Context
The Institute expects collaborators from the EU/UK, placing the Portal within GDPR scope
from its first European registration. GDPR does not mandate EU storage; it mandates lawful
processing and adequate protections for transfers.

## Alternatives considered
- **EU region (AMS/FRA):** stronger optics for EU collaborators, rejected as the default —
  it does not remove GDPR obligations (the US institute still processes the data), adds
  latency for the primarily-US administration, and can be revisited with a documented
  migration path (managed DB restore into another region) if counsel advises.
- **Split residency:** rejected — complexity far beyond the Foundation's needs.

## Consequences
Privacy notice must disclose US processing; standard contractual/consent language is
counsel's call (legal review recommended in the proposal and flagged to Linas). Regional
migration remains possible: provider-independent dumps exist and nothing binds the app to
a region.
