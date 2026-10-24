# 02 — Domain Model

**Status:** Draft for internal review · **Milestone:** M1 (modeled in full; surfaced progressively)

> Proposal commitment: **model the domain, not the release.** M1 builds the schema for the
> whole Foundation Release — referrals, applications, decisions, profiles — even though
> their interfaces arrive in M2/M3. "M1 doesn't have the referral UI" ≠ "don't design referrals."

## 1. The three identities — the model's central distinction

The cheap design collapses everything into `users`. This system separates three concepts
that have different lifecycles:

| Concept | What it is | Exists when |
|---------|-----------|-------------|
| **User** | An authentication identity: email, password, MFA, sessions, account state | Someone can sign in (admins and admitted collaborators) |
| **Collaborator** | Institutional membership: admitted status, provenance, cohort/wave, directory visibility | Registration completes after an accepted invitation |
| **Profile** | Professional presentation: names as displayed, title, photograph, About, CV entries, links | Created with the collaborator; completed by them |

Consequences the model must represent correctly (from the Foundation Plan):

- A person can **apply and be declined** — an Application exists; no User, no Collaborator.
- A person can **be invited and never register** — an Invitation expires; nothing else exists.
- An **administrator is a User but not a Collaborator** (Linas is a Founder-role User; whether
  he also appears as a collaborator in the directory is his choice, and the model permits both).
- A collaborator can **leave** — Collaborator status changes; the admission history remains.

**Person-before-registration:** candidate identity prior to registration lives *in the pipeline
records themselves* (application / referral / invitation each carry name + email). We do not
introduce a separate `people` table in the Foundation Release; deduplication across pipeline
records is by email match, surfaced to the reviewer. A consolidating Person entity can be
added later **additively** if pipeline volume ever justifies it. Reasoning in ADR-008.

## 2. Entity catalog

### Identity & governance

- **User** — email (verified), password hash, MFA secret + recovery codes, account state
  (`active`, `suspended`, `deactivated`), canonical name fields.
- **Role** — named set of permissions (`founder`, `administrator`, `collaborator` seeded;
  `admission_reviewer`, `regional_reviewer`, `readonly_staff` addable as **configuration, not migration**).
- **Permission** — atomic capability (e.g. `admission.review`, `collaborator.visibility.override`).
- **RoleAssignment** — user ↔ role with `assigned_by` and `assigned_at`: *who granted what, when* is itself institutional history.
- **AuditEvent** — append-only record: actor, event type, subject, context JSONB, occurred-at.
  The **permanent admission record** is the admission-related slice of this stream. Never updated, never deleted.

### Admission pipeline (all M1 schema; M2 surfaces)

- **Application** — website "Become a Collaborator" submission. Candidate details,
  professional information, status (`submitted → under_review → approved | declined | withdrawn`).
- **Referral** — structured recommendation by an approved collaborator: candidate details,
  reason, relationship to referrer, `referrer_collaborator_id`. Same status flow as Application.
  **No limit** on referrals per collaborator (client standard).
- **AdmissionDecision** — the judgement: polymorphic subject (application | referral | direct),
  decision (`approved`/`declined`), `decided_by`, rationale, decided-at. A referral/application
  *receives* a decision; it does not *become* one.
- **Invitation** — secure admission instrument: recipient email + name, **hashed** single-use
  token, expiry, status (`issued → sent → accepted | expired | revoked`), `issued_by`,
  source linkage (decision → application/referral, or direct), send count + resend capability.
- **Provenance chain** (client standard — how did this person enter the pipeline?):
  `Collaborator → Invitation → AdmissionDecision → (Application | Referral | direct-by-Founder)`.
  Answerable years later entirely from foreign keys, without archaeology.

### Community (M1 schema; M3 surfaces)

- **Collaborator** — user_id, invitation_id (provenance), status (`active`, `inactive`, `departed`),
  cohort/wave label (`wave_0`, `wave_1`, …), admitted-at, directory visibility (see §4).
- **Profile** — professional title, one-line summary (character-limited), About (1–2 paragraphs),
  photograph (object-storage path; **rectangular** display), country (public),
  city + state/region (**admin-only** — distinction explained at completion time),
  languages, collaboration availability; up to **three** displayed credentials (no Dr./Prof. prefixes).
- **ProfileEntry** — structured CV rows in Institute-defined sections
  (`experience`, `education`, `appointment`, `publication`, …): section + ordered position +
  typed JSONB payload. Standardized structure, **unrestricted quantity** (client standard).
- **ProfileLink** — up to **five** external links, each with a one-sentence description (client standard).
- **ExpertiseArea** — Institute-controlled vocabulary (never free text — the single decision
  that makes search/grouping/matching/reporting possible later), with active flag and ordering.
- **CollaboratorExpertise** — collaborator ↔ expertise area.
- **Organisation** — institution/company as first-class entity; official name may appear in
  original language alongside English (client standard). Profiles reference organisations
  rather than storing free-text employer strings.

### Privacy (M1 schema; M2 surfaces)

- **Consent** — user, document (`privacy_notice`), document version, granted-at, IP. Re-consent on version change.
- **DataRequest** — user, type (`export` | `erasure`), status, requested/completed timestamps. GDPR applies from the first EU/UK registration.

### Transactional email (M1 — this is core M1 scope)

- **EmailMessage** — every outbound message: type, recipient, subject, Postmark message id,
  status (`queued → sent → delivered | bounced | spam_complaint | failed`), timestamps.
  Updated by Postmark webhooks. This is what makes per-invitation delivery status and
  one-click reissue possible — the answer to the project's #1 operational risk.

### Modeled-but-deferred (ERD-documented; migrations when their surface arrives)

Wishlist signals Waga committed to account for in the domain, shaped now so they arrive
additively — but **not** migrated in M1, because additive migrations are cheap and unused
tables invite churn (reasoning in ADR-008):

- **CollaborationInterest** — "I would like to collaborate": from-collaborator, about-collaborator,
  recorded as an institutional record (deliberately **not** messaging).
- **Acronym / AcronymMeaning** — Institute-owned Acronym Library; one acronym, many meanings;
  seeded by the Founder, later expanded by collaborators; selectable in profile content.
- **ConductReport** — discreet report-to-admin mechanism (community standards).
- **AdminBroadcast** — one personalized message to all collaborators (uses EmailMessage rails).

## 3. Lifecycle state machines

```
Application:  submitted ──▶ under_review ──▶ approved ──▶ (AdmissionDecision + Invitation)
                                   │                └──▶ declined (recorded, retained)
                                   └──────────────────▶ withdrawn

Invitation:   issued ──▶ sent ──▶ accepted ──▶ (User + Collaborator created)
                 │          ├──▶ expired  (re-issuable: new token, provenance kept)
                 │          └──▶ revoked  (admin action, audited)
                 └── resend: same invitation, send_count++, delivery re-logged

Collaborator: admitted(active) ──▶ inactive ──▶ active   (reversible, audited)
                        └──────────▶ departed            (history preserved)
```

## 4. Admission ≠ visibility (confirmed with Linas)

```
Application → Review → Approved → Invited → Registered/Admitted
   → Profile Completion → Directory Eligible → Visible
```

- **Directory-eligible** is *computed* from the required-completion checklist (name,
  professional title, photograph, About, country — final criteria confirmed with Linas
  before M3).
- **Visible** = eligible **AND** visibility enabled — with an audited administrative override
  in both directions (hide always possible; show-despite-incomplete possible where Linas
  judges appropriate).
- The per-collaborator wave/cohort label plus this visibility control is the entire technical
  cost of the **Wave 0 / Wave 1 / Wave 2+** launch strategy.

## 5. Name & character standards (foundational, from Linas)

- Separate `first_name`, `middle_name` (optional), `last_name` on User; displayed credentials
  (≤3) on Profile. No professional prefixes anywhere.
- Full Unicode extended-Latin + diacritics end-to-end: UTF-8 database encoding, ICU collation,
  no ASCII-only validation, tested with names like *Søren Kierkegård-Ñíguez*. A collaborator's
  name is never simplified because of a technical limitation.
- English is the Portal's working language; organisation names may also carry an
  original-language form (`name_original` column from day one).
