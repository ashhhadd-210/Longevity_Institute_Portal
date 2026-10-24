# ADR-001 — Application Framework: Laravel 12 (PHP 8.3)

**Status:** Accepted (agreed with the Institute, Aug 2026) · **Date:** 2026-08

## Decision
Build the Portal as a Laravel 12 / PHP 8.3 monolith with server-rendered UI
(Blade + Livewire + Tailwind CSS).

## Context
The Institute requires a ten-year institutional asset that must never depend on a single
developer, with a deep maintenance hiring pool and conventional structure. The proposal
presented Laravel and Node.js/Next.js as equally capable and deliberately let the Institute
choose consciously.

## Alternatives considered
- **Node.js 22 + Next.js 15 (TypeScript):** largest talent pool, better if a mobile app or
  rich interactivity were near-term. Rejected because conventions vary more between teams —
  continuity depends on the discipline of the original build, whereas Laravel's
  convention-driven layout means a new developer finds things where the framework says they
  will be.
- **Separate SPA frontend + API:** rejected for the Foundation Release — more to maintain,
  deploy and secure, with no requirement that demands it. A separate frontend can still be
  added later against the same service layer without disturbing the core.

## Consequences
First-party coverage for auth, queues, mail, policies means less bespoke code exists to
decay. If the Institute later wants a mobile app, an API layer is added over the existing
services (additive). The framework choice is the one decision that is genuinely difficult to
revisit; it is therefore recorded first.
