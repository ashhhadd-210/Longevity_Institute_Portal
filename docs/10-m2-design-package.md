# Milestone 2 — Admission System: design package

Status: internal design, agreed before code.
Written 29 August 2026. Supersedes nothing; extends docs 02, 05 and 08.

---

## 1. What Milestone 2 delivers

A person can reach the Institute by three routes, be judged once, and become an
admitted collaborator, with every step recorded and nothing happening to them
before the Institute decides it should.

The milestone closes when the Founder runs a complete admission herself, end to
end, without a developer present: a website application arrives, she reviews it,
she decides, she issues an invitation, the person registers, and an admitted
collaborator exists.

**The three routes in:**

| Route | Who starts it | Who may be contacted, and when |
|---|---|---|
| Application | The candidate, from the public website | The applicant, once she asks or decides |
| Referral | An admitted collaborator | The **referrer** only, until approval |
| Direct invitation | The Founder, from LinkedIn or anywhere | The candidate, only after she invites |

---

## 2. What Milestone 1 already provides

M2 is mostly **surface over an existing foundation**. This is deliberate and it
is why the milestone is achievable in its window. Already built and tested:

- `Application`, `Referral`, `AdmissionDecision`, `Invitation`, `Collaborator`,
  `Profile` and the reference tables, with provenance modelled (ADR-008).
- `InvitationService` — issue, resend with fresh token, revoke, and hashed
  token lookup. Only the SHA-256 hash is stored.
- `RegistrationService` — atomically creates User, Collaborator, empty Profile,
  consent record and role, and marks the invitation accepted.
- `AuditRecorder` — the single append-only write path for the institutional
  record.
- Postmark transport, delivery webhook, `EmailMessage` tracking, MFA wall,
  role and permission gates.

**Consequence:** M2 writes the screens, the four-action review, the case
convergence and the schema deltas listed below. It does not rebuild admission.

---

## 3. Gaps found between the M1 schema and what was later agreed

These are the real findings of the survey. Each blocks a decision she has
already approved, and each is additive to a deployed database.

| # | Finding | Blocks | Fix |
|---|---|---|---|
| 1 | `invitations.email` is `NOT NULL` | The copy-link route. Most LinkedIn relationships have no email address at all | Make nullable, add `delivery_channel`, with a database check that email-delivery still requires an address |
| 2 | No `preferred_name` anywhere | The salutation rule confirmed 28 August | Add to `users` and `invitations` |
| 3 | `referrals.relationship_to_referrer` is a free-text string | The Institute-controlled relationship list agreed 27 August | Lookup table, foreign key, free text only for "Other" |
| 4 | No character limits applied | The 100/200/200/300 limits promised 25 August | Apply as column types, in characters not bytes |
| 5 | `ReviewAction` had three cases | Set Aside, agreed 25 August | Fourth case added, plus `set_aside` status and reason |
| 6 | No "current position" section | Approved addition Item 1, three positions at $350 | `position` section on `profile_entries`, capped at three |
| 7 | Livewire is named in the stack notes but is **not installed** | — | Build server-rendered Blade. No new dependency, no build step, and admission workflows are better as plain auditable form posts |

---

## 4. Decisions this milestone implements

Every item traces to a specific agreement. This table is the acceptance
checklist for the milestone.

**Review — four actions (18 and 25 August)**

- Approve and Decline are judgements and write an `AdmissionDecision`.
- Need More Information is a **question**, recorded as its own event type, may
  repeat, keeps the case open.
- Set Aside is a **silent pause**, stays visible in the queue, writes no
  decision.
- Issuing an invitation is a **consequence of approval**, never a review action,
  and therefore never appears among the four buttons.

**Who is contacted (18 August, hard rule)**

- Referral: Need More Information goes to the **referrer**. Set Aside and
  Decline are silent. The referrer learns only that an invitation was sent, and
  is never told about a decline.
- Application: Need More Information goes to the applicant. Set Aside silent.
  Decline offers an *optional* courteous reply, never automatic.
- **The Portal never contacts a referred professional before approval.**

**Invitations (18 and 25 August)**

- Unique, single-use, expiring, revocable, tied to the admission record.
- **The token is not consumed on GET.** Link scanners must not burn it.
- Shown once at generation; only the hash is stored. Reissue invalidates the old.
- Copy-link and email are equal citizens; every protection holds either way.
- Delivery tracking only where it can be observed. Copy-link shows issued and
  accepted, and never implies delivery.
- Names the referrer where one exists, plus an optional sentence from the Founder.
- A lapsed, revoked or spent link shows a **courteous page**, never an error.

**Duplicate candidates (18 August)**

- Both submissions kept; they converge on one case, one decision, one
  invitation, one history.
- Email match is confident but still **flagged for her confirmation**. Name match
  is only a possibility. **Never auto-merge.**
- A referral for someone already invited or admitted shows the existing case and
  blocks a second invitation.

**Referral relationship (27 August)**

- Required, from the Institute-controlled list, so that Prefer Not to Specify
  carries meaning. Mentor entries are directional.
- Optional free-text "How do you know this person?".
- **Admin-only forever.** Never shown to the candidate, never auto-inserted into
  the invitation, never on a profile.
- Surfaced in the queue and beside the personal-sentence box at invitation.

**Salutation (28 August)**

- One resolver used by every message: preferred name, then first name, then a
  courteous greeting with no name. The Portal never sends "Hello ,".

**Character limits (25 August)** — 100 / 200 / 200 / 300, and whatever was
accepted is displayed in full, wrapped, never truncated.

---

## 5. Build order

Each step is a working vertical slice. Nothing later depends on anything
unbuilt.

**Step 1 — Vocabulary and schema.**
Four review actions; `set_aside` status with reason and actor; relationship
lookup table seeded with her list; invitation delivery channel with email made
optional; preferred name; character limits; the three-position section; the
uncommitted `information_requests`, `candidate_cases` and `disciplines`
migrations brought in and made current.

**Step 2 — Domain services.**
`ReviewService` enforcing that only two of four actions write a decision.
`CandidateCaseService` for convergence and duplicate suggestion, never
auto-merging. `CaseHistoryService` assembling one timeline from the real
records rather than a second parallel log. `InvitationService` extended for
copy-link, optional email, referrer naming and the personal sentence.

**Step 3 — Intake.**
Public application form and its thank-you page. Collaborator referral form with
the relationship question. Both write a candidate case and run duplicate
detection at submission.

**Step 4 — Review queue.**
The list, filtered by open state. The case view showing every submission that
converged, the referrer's private relationship note, and the assembled history.
The four actions, each with its own consequence and its own contact rule.

**Step 5 — Invitation lifecycle.**
Generate from an approved case or from a name typed fresh. Link shown once.
Copy-link and email. Reissue and revoke. The courteous landing page for links
that no longer work.

**Step 6 — Registration.**
The invited person confirms their own details. GET never consumes. Consent
recorded. Admission completes through the existing `RegistrationService`.

**Step 7 — Messages.**
Invitation, information request, and the optional courteous decline, all through
the salutation resolver, on the transactional Postmark stream.

**Step 8 — Proof and delivery.**
Tests for each agreed rule above, Pint, Larastan level 6, CI green, walkthrough
and delivery package.

---

## 6. Explicitly not in Milestone 2

Announcements and the prospective-collaborator preview, both new scope and both
quoted at the Milestone 3 opening. The directory, profile screens and search.
The discipline contents, which are hers to write; M2 fixes only the shape.
Deferred additions 2 to 4. Self-service import.

---

## 7. How the milestone closes

She runs it herself: application in, review, decision, invitation, registration,
admitted collaborator, with the history readable afterwards and no developer in
the loop at any point.
