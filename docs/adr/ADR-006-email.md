# ADR-006 — Email: Postmark, transactional stream, full authentication, per-message logging

**Status:** Accepted (account created by the Institute, Aug 2026) · **Date:** 2026-08

## Decision
Postmark on the Institute's domain for all Portal email. SPF + DKIM + DMARC configured in
M1 (DMARC begins `p=none` with monitoring, tightened once existing mail flows are confirmed
clean). All sends queued, logged to `email_messages`, and status-updated via Postmark
webhooks. Transactional and (future) broadcast traffic use separate message streams.
Templates live in version control; no admin template editor in the Foundation Release.

## Context
Email deliverability is the project's single largest operational risk: the invitation is the
Institute's first contact with a curated professional, and an invitation in spam is an
invitation that was never sent — invisibly. Acceptance requires verified inbox placement on
Gmail, Outlook, and at least one university/institutional address (client requirement).

## Alternatives considered
- **Amazon SES:** cheaper at volume, rejected for now — Postmark's deliverability focus,
  simpler setup and superior message-level visibility fit a low-volume/high-stakes profile;
  the Institute already created the account.
- **SMTP via hosting/Google Workspace:** rejected — no per-message API status, reputation
  risk, no stream separation.
- **Admin-editable templates (deferred, per proposal):** a mis-edited template reaching an
  invited professional costs more than the feature returns; version-controlled templates
  with change-by-request for the Foundation Release.

## Consequences
`EmailMessage` + webhooks give the M2 admin queue per-invitation delivery status and
one-click reissue. DMARC tightening is a deliberate two-step to avoid breaking Linas's
existing founder email.
