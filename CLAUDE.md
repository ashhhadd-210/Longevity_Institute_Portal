# Active Longevity Institute — Collaborator Portal

Canonical project context. Loaded automatically each session. Keep this updated
whenever anything below changes.

---

## 1. Identity and communication rules (non-negotiable)

- All client-facing work is by **Waga C of Pronto Digital**. Sign messages "Waga".
- **Nothing** client-visible may reference AI, Claude, code generation, Ashhad's
  real name, Bayut, personal gmail, Fiverr, or internal working files.
- Repo git identity: `Waga C <275084704+ashhhadd-210@users.noreply.github.com>`
  (author AND committer). Never the Bayut address.
- Before every push: scan commits and tracked files for the terms above; confirm
  no `.env`, tokens, keys or delivery folders are tracked.
- **Client message style:** plain text (Fiverr renders no markdown). No em-dashes.
  Simple, human, warm, precise. Short paragraphs. No jargon. Explain reasoning,
  never lecture. He is non-technical but extremely careful and reads everything.

## 2. Client and contract

- **Client:** Linas Liubinskas (he/him), founder, Active Longevity Institute, Chicago.
  Fiverr handle `linasliubin`. Email `linas@activelongevityinstitute.org`.
- Contract via Fiverr. Total **USD $2,500**, four milestones, ~14 weeks.
  Prices are the developer price, before Fiverr buyer fees.

| Milestone | Scope | Duration | Price | Status |
|---|---|---|---|---|
| M1 Foundation & Identity | repo, CI, envs, domain model, auth, MFA, RBAC, email infra, backups, ADRs | 3.5 wk | $600 | **APPROVED & PAID OUT 2026-08-17** |
| M2 Admission System | invitations, referral form, application form, review queue, admission record, consent | 4 wk | $600 | **APPROVED & PAID OUT 2026-09-01**, plus a $50 tip and feedback left |
| M3 Profiles, Directory & Identity | profiles, photos, directory, search (name+expertise), visibility, visual identity | 3.5 wk | $600 | **Scope + quote sent 2026-09-02** (`m3-proposal/`). Awaiting his go-ahead; he is budget-timing constrained |
| M4 Production Readiness & Handover | hardening, monitoring, docs, training, cold-start test, launch, 30-day defects | 2 wk | $700 | not started |

**Approved addition:** Item 1 (up to three position/organisation combinations per
profile) at **$350**. Structural work now; entry screens in M3.

**Deferred additions — REPRICED INDIVIDUALLY AND SENT 2026-09-02:**
2. Controlled credential library with submit-for-approval — **SENT AND AGREED AT
   $150**, moved into M3 on 2026-09-02. (I later drafted a revision holding it at
   $220; that revision was NOT sent. **$150 is the number of record.** Never
   quote $220.) He argued (correctly, better than my
   original advice) that credentials sit right after the name on every profile,
   so they must be controlled from the start or retrofitting means a data
   cleanup across ~240 people. I openly reversed my "defer it" recommendation.
   Price fell because the vocabulary engine is shared, not standalone.
3. Two-step administrative profile review and publication — **$180**
4. Exact profile preview before publication — **WITHDRAWN, given free.** Building
   the profile page means building the page that shows a profile, so showing it
   to its owner costs almost nothing. Told his to remove it from his list. This
   concession is what makes the "buy none of these yet" advice credible.

**Also quoted 2026-09-02:** Announcements **$300**, prospective-collaborator
preview **$250**. **Recommended he defer all four**, with reasons.

**Money position (verified arithmetic):** paid $1,200 + $50 tip. Remaining
commitment **$1,800** (M3 $600 + Item 1 $350 + credential library $150 + M4 $700).
M3 alone is **$1,100** ($600 + $350 + $150). Contract base = $2,500.
**Staging:** $600 starts the work; the other $570 covers things built late in the
milestone and is invoiced then.
**Do NOT push announcements ($300) or the preview ($250) while he is
cash-constrained and mid-decision.** That $550, plus M4's $700, is worth far more
than a short-term bump, and the credibility of "buy none of these yet" is what
makes every later recommendation land.

**His constraint is TIMING, not price** ("I may need a little time before I have
sufficient funds"). Hence M3 is structured to start at **$600 alone**, with the
$350 Item 1 screens invoiced weeks later since they are built late in the
milestone. Lead with the small number.

**New scope acknowledged, not yet priced** (he accepted both as separate,
priced-later work; quote each when its moment arrives):
- **Announcements / community communications.** Raised 2026-08-25, approved in
  principle same day. Natural moment: immediately after M3, so it inherits the
  directory's filtering. Quote before building.
- **Prospective-collaborator preview.** Raised 2026-08-28. Recommended shape is
  the safe one (see section 6). Natural moment: end of M3, reusing the real
  profile and directory templates. Modest.

**Absorbed free:** one-time load of his ~240 founding-collaborator spreadsheet
(NOT a self-service import feature, which stays unbuilt until proven necessary).
Also: Portal naming, generational suffixes, hidden empty fields, photo guidance
text, name display standard, analytics event recording.

## 3. Working method

- **Architect first.** Each milestone begins with a design package, agreed
  internally, then with the client, before code is written.
- Challenge his ideas with reasoning when there is a better way. He asked for
  this explicitly: "I want your thinking, not simply implementation of mine."
- Price additions openly; never absorb them by cutting testing, docs or security.
  Proposal commitment: "I would rather raise a cost at the time than deliver
  something weaker and say nothing."
- Model the domain, not the release. Additive migrations only after deployment.

## 4. Live infrastructure (all Institute-owned)

- **Repo:** `ActiveLongevityInstitute/active-longevity-portal` (private).
  Ashhad has push, not admin. GitHub Environments unavailable (paid feature on
  private repos), so deploys use **repository** secrets `STAGING_*` /
  `PRODUCTION_*`, gated by vars `DEPLOY_STAGING_ENABLED` /
  `DEPLOY_PRODUCTION_ENABLED`.
- **DigitalOcean** project `6c29d1ff-3166-4f6e-b4f0-05d6d500e8cd` (NYC3), ~$33/mo:
  - `portal-production` id 592758824, 167.99.63.85, private 10.108.0.3
  - `portal-staging` id 592758827, 167.71.98.53, private 10.108.0.4
  - `portal-postgres` id 877e4cac-2f75-4820-b4c3-eecd9c54e691, PG16, dbs
    `portal_production` + `portal_staging`; trusted sources = the two droplets only
  - Firewall `portal-web` (22/80/443). SSH key `portal-deploy` id 58522792.
- **Addresses (live, TLS, auto-renewing):**
  - https://portal.activelongevityinstitute.org
  - https://staging.portal.activelongevityinstitute.org
- App path `/var/www/portal`, deploy user `deploy`, queue unit `portal-queue`.
- **DNS at GoDaddy**, not Cloudflare (ADR-009). MX = Google Workspace. DMARC
  already `p=quarantine`. Postmark DKIM + Return-Path verified. Never modify
  MX/SPF/DMARC; Portal DNS records are additive only.
- **Postmark: APPROVED** for sending to any address (2026-08-18). Servers
  `portal-staging` 20344253, `portal-production` 20344254. The Portal uses its own
  credentials, separate from the account's exposed default-server token.
- Sentry SDK installed; DSN not yet supplied.
- Credentials live in `project longevity/PRIVATE-do-not-send/`. Never upload.

## 5. Stack

Laravel 12 / PHP 8.3, PostgreSQL 16, Blade + Livewire + Tailwind (server-rendered),
Fortify auth, GitHub Actions CI (Pint, Larastan level 6, composer audit,
migrations + tests on PG16). Ubuntu 24.04 ships PHP 8.3 natively, no PPA needed.

Local dev: portable PostgreSQL at `~/tools/pgsql`, data dir `~/tools/pgdata16`,
user `postgres` / `portalDevLocal2026`, dbs `portal_dev` + `portal_test`.
Start with `~/tools/pgsql/bin/pg_ctl -D ~/tools/pgdata16 start`.

Gotchas: `artisan key:generate --show` emits ANSI codes, always add `--no-ansi`.
Long SSH provisioning must be `setsid nohup` or it dies when sshd restarts.
Faker is dev-only and unavailable on servers, so seeders must not call `fake()`.

## 6. Agreed product decisions (all confirmed with Linas)

### Review of applications and referrals: FOUR actions
1. **Approve** — final judgement, recorded permanently, enables an invitation.
2. **Need More Information** — a question, NOT a decision. Keeps the case open,
   may repeat. Recorded as its own event type, never as a decision.
3. **Set Aside** — silent internal pause, stays visible in the queue.
4. **Decline / No Action** — final judgement, recorded with reasoning.

Issuing an invitation is a **consequence of approval**, never a review action.

### Who gets contacted
- **Referral:** Need More Information goes to the **referrer**, not the candidate.
  Set Aside and Decline are silent. The referrer is told only once an invitation
  has been sent, and is never told about a decline.
- **Application:** Need More Information goes to the applicant. Set Aside silent.
  Decline records the decision and offers Linas an *optional* courteous reply
  (not automatic — awaiting his final confirmation).
- **Hard rule:** the Portal never contacts a referred professional before Linas
  approves. Until approval they are being discussed, not dealt with.

### Invitations
- Unique per person, single-use, expiring, revocable, tied to the admission
  record. No shared or common link, ever.
- **The token must NOT be consumed on GET.** Only on completed registration,
  otherwise LinkedIn and Outlook link scanners burn it before the recipient clicks.
- **Copy-link route:** Linas can copy the link and send it herself via LinkedIn.
  All protections hold regardless of delivery channel.
- The link is shown **once** at generation (only its hash is stored). Reissue is
  one click and invalidates the old link. Revisit after Alpha if impractical.
- Delivery tracking (sent/delivered/bounced) applies to email only. Copy-link
  shows issued and accepted. The Portal must never imply delivery it cannot observe.
- The invitation names the referrer where one exists, plus an optional personal
  sentence from Linas.
- Workflow: select or create candidate, Generate Invitation, copy link. No
  developer involvement ever.

### Creating a candidate from LinkedIn (confirmed 2026-08-25)
- **Name is the only required field.** Email is optional, because most LinkedIn
  relationships have no email. Generate Invitation, link shown once, copy, paste
  into his own LinkedIn message. Two or three fields, two clicks.
- Founding-group people already exist from the spreadsheet load, so the common
  path is find, click, copy. Typing a name is only for someone new.
- **Provisional-data principle:** anything Linas types about a person is
  provisional until that person confirms it at registration. He is the authority
  on admission; they are the authority on the facts of their own life.
- Every record permanently remembers its origin: direct invitation, referral, or
  application.
- A lapsed, revoked or already-used link shows a courteous page, never an error.

### Referral relationship (confirmed 2026-08-27)
- Referral form asks **"What is your relationship to this person?"** from an
  Institute-controlled list (a **seeded lookup table, not a hardcoded enum** —
  he will grow it): Colleague, Former Colleague, Research Collaborator,
  Professional Acquaintance, Friend, Family Member, **"I mentored them"**,
  **"They mentored me"**, Other, Prefer Not to Specify. Other opens short free text.
- Mentor/Mentee were reworded directionally because the original pair was
  ambiguous about who mentored whom.
- **Required**, precisely so Prefer Not to Specify carries meaning. The optional
  free-text **"How do you know this person?"** note stays optional.
- **Admin-only forever.** Never shown to the candidate, never auto-inserted into
  the invitation, never on any profile even after admission. It informs his
  wording; it never writes it (the stated relationship is only one side's view).
- Surfaced in the review queue and again beside the personal-sentence box on the
  invitation-generation screen. Part of the referral record and its provenance.

### Character limits (committed to him 2026-08-25)
Counted in characters, not bytes. First / middle / family / preferred name **100**
each; organisation and institution **200**; department **200**; professional title
**300** (named chairs with donor, department and joint appointment are real).
Each of the three positions carries its own title/organisation/department at these
limits. **Display rule: whatever was accepted is shown in full.** Long values wrap,
never truncate with an ellipsis. Test fixtures must include worst-case real names
and titles so layouts are proven before he sees them.

### Announcements (approved in principle 2026-08-25, priced later)
- Stored **Portal content**; email is only the messenger. Announcements remain
  readable in the Portal permanently, so a collaborator admitted later can still
  read them.
- Audience selector reuses the M3 directory search (discipline, specialty,
  country). Hence: build after M3.
- **Separate Postmark stream from transactional mail.** Broadcast mail must never
  endanger invitation deliverability.
- Opt-out applies to announcements only, never to admission or account email.
- Every send recorded against each collaborator's record from the first one.
- Before any broadcast he sees it rendered as a real recipient will receive it,
  greeting included.

### Salutation rule (CONFIRMED 2026-08-28)
- One rule, one place in the code, used by **everything** that addresses a person:
  invitations, admission mail, announcements, and the Portal's own greeting.
- Chain: **preferred name → first name → courteous greeting with no name.**
  The third step matters: pre-registration we may hold only what he typed, and
  possibly a single name. The Portal must never send "Hello ,".
- The preferred-name field absorbs the prefix question we declined to model: a
  person who wants "Doctor Smith" simply writes that.
- Preferred name governs **address**, never **identity**. The formal name remains
  the name of record.

### Prospective-collaborator preview (APPROVED 2026-08-28)
- He dropped the anonymised real-directory idea herself: "please consider the
  anonymized real-directory idea dropped." Blurred identities are off the table.
- **Blurring is rejected outright.** CSS blur ships the real data to the browser;
  it is the appearance of protection, not protection. Rule: data a visitor may not
  have never leaves the server.
- **Anonymised real profiles are also rejected**, for two reasons: (a)
  re-identification — "pension policy, Lithuania, government" identifies exactly
  one person in a community of a few hundred, and fails worst for the rarest and
  most valuable collaborators; (b) **consent** — collaborators agree to be visible
  to *admitted collaborators*; showing them to visitors changes that bargain.
- **Recommended instead, all zero personal data:** (1) the full discipline and
  specialty taxonomy with the "why longevity raises questions here" lines — the
  most persuasive asset the Institute owns; (2) live aggregate figures with a
  no-fine-breakdown rule and a switch he flips when the numbers flatter; (3) a
  complete example profile, plainly labelled as an example, plus a plain statement
  of what is public / collaborator-visible / Institute-only.
- Never invent members or figures. Anything illustrative says so.
- Security: the recommended page is identical for every visitor and holds nothing
  to scrape, so no rate limiting or bot defence burden. The anonymised-directory
  version would carry that burden forever.
- **The genuinely architectural piece, to settle in M3:** every profile field
  carries a **visibility class**, enforced centrally, not template by template.

### Visibility architecture (CONFIRMED 2026-08-29)
He challenged the word "public" and was right: it was doing two jobs. **Retire
the word entirely.** Name the audience literally. Two independent axes.

His approval, verbatim: agreed the four-audience architecture "including leaving
'Anyone on the web' empty for now", the narrow-never-widen rule, and "strongly"
that the promise made at collection determines the ceiling. He also accepted
deferring the field-by-field table to the M3 package and asked that attention now
return to M2.

**Axis 1, audience — a four-rung ladder, most restrictive first:**
1. The Institute only
2. The collaborator and the Institute
3. Admitted collaborators
4. Anyone on the web

**Today not one field sits on rung 4, and the preview page needs none there.**
The rung exists so that a future need (for example, letting a collaborator's
membership be verifiable by an employer) is a setting rather than a rebuild.
Rebuilds of visibility logic are where privacy accidents happen.

**Axis 2, authority — who sets it.** Two locks: the Institute sets a **ceiling**
per field; the collaborator chooses **within** it. The effective visibility is
always the more restrictive. **A collaborator can narrow, never widen.**

**The rule that decides where a ceiling sits: the promise made at collection.**
A field cannot later be shown to a wider audience than the person was told when
they gave it. That is why city and state stay Institute-only and are not the
collaborator's to widen. It also means ceilings are not arbitrary preference.

**Institute-fixed:** directory presence (already: gated by profile completion with
audited override); city and state at rung 1; **email address never above rung 2,
ever, because the Portal carries messages instead of displaying addresses. An
address that is never displayed cannot be harvested.**

**Collaborator-chosen within the ceiling:** whether another collaborator may
register an interest in working with them; which optional fields they fill at all.
Note that **leaving a field blank is already a visibility control** and the Portal
already honours it (empty fields vanish), so collaborator authority exists in
embryo already.

**Fails safe:** a field with no declared visibility is Institute-only, and CI
**fails the build** if a new field is added without an explicit declaration. The
system's mistakes must always err toward privacy.

**Deliverable, inside M3, no additional cost:** a field-by-field visibility table
in plain English for his approval before any code, **generated from the same
source the code enforces**, so policy and behaviour can never drift apart, plus an
automated test walking every field against every kind of viewer.

**Timing, told to him 2026-08-29.** Settled in two parts. The *principle* is
settled now (it shapes the architecture, and he was right to raise it early).
The *field-by-field table* belongs to the M3 design package, which opens once M2
is delivered, because the table lists every profile field and the final field list
is itself M3 work. Stated reason given to him, and it is the true one: written
today it would be a draft we must rewrite properly later.

**Batch the money conversation.** The preview quote and the announcements quote
are both delivered at that same M3 opening, so he gets one clear commercial
discussion rather than a trickle. Until then, attention stays on M2.

### Duplicate candidates
Both submissions are kept (each carries information the other lacks) and converge
on **one case, one decision, one invitation, one admission history**. Email match
is a confident match, flagged for his confirmation. Name match is only a
possibility. **Never auto-merge.** A referral for someone already invited or
admitted shows the existing case and blocks a second invitation.

### Controlled vocabularies: ONE mechanism, three vocabularies (agreed 2026-09-02)
He specified credentials + disciplines + specialties as three evolving
Institute-controlled libraries with an identical lifecycle: **search -> select ->
request if missing -> Institute review -> approve/standardise -> permanent.**
**Build the engine ONCE**, with one proposal review queue and one set of admin
screens, and run all three vocabularies on it. A fourth vocabulary is then nearly
free. Engine + disciplines/specialties request flow = **M3, absorbed**
(propose-for-approval was already agreed in principle). Credential library = M3,
**$150**.
- **A specialty links to MANY disciplines, not one.** Health economics genuinely
  sits under both economics and public health; searching either finds the person.
- **Tension surfaced and resolved:** his new message says pick discipline first,
  the older agreement says specialty only with discipline derived. Resolution:
  discipline-first is a **search narrowing aid**, never a stored second answer.
  Stored truth stays "specialty, disciplines derived", so they cannot disagree.
  **A collaborator MUST also be able to search specialties directly** without
  choosing a discipline, or we make the front door hardest for exactly the
  interdisciplinary people the Institute exists for.
- **Never block the collaborator:** a requested entry saves against their profile
  immediately, marked awaiting approval, visible to them and the Institute only,
  and appears publicly the moment it is approved.
- **Standardising an entry propagates** to every profile using it, because people
  select entries rather than typing text. This dividend only exists if built
  BEFORE the data does.
- **Retiring an entry keeps it** for people already holding it; it just stops
  being offered. History is not rewritten by a policy change.
- Search, never scroll, everywhere. Admin must stay usable at ~1000 entries.
- **Credential picker specifics (confirmed to him 2026-09-02):**
  - Type-ahead over the approved library; the collaborator SELECTS an entry, never
    types free text. Up to three shown after the name.
  - **Search matches the full meaning as well as the acronym.** Someone who holds a
    Master of Public Health but does not think "MPH" must still find it, or they
    request something we already have.
  - **One acronym can have several legitimate entries.** MD = Doctor of Medicine
    or Managing Director; FRCP differs by London/Edinburgh/Glasgow/Canada. The
    meaning shown beside each entry is what disambiguates at selection time. This
    is the real reason the meaning field earns its keep.
  - **The Request form shows near matches first**, so people do not submit
    "Masters in Public Health" when "Master of Public Health" is already approved.
    Cuts his duplicate-approval load.
  - **Seed list required before launch** (widely used international credentials),
    sent to him for review alongside the discipline structure. Without a decent
    seed everyone meets an empty box and floods his with requests.
  - Expect the FIRST wave to generate the most requests; it tails off fast. Tell
    his this in advance so it does not read as a fault.
  - Payoff to state plainly: because the Portal knows what each acronym means, an
    unfamiliar reader can see it. This directly answers his worry that credentials
    are meaningless outside their profession or country.
- **Deferred honestly:** merge/deduplicate tooling for when two approved entries
  turn out to mean the same. Useful at scale, unnecessary at launch.

### Expertise taxonomy: two levels
- **Discipline → Specialty.** The collaborator selects specialties only; the
  discipline follows automatically, so the two levels cannot contradict.
- Searching a discipline returns everyone in its specialties. Collaborators may
  hold specialties across several disciplines (the interdisciplinary point).
- Built **from the questions longer lives raise, not from academic departments**.
  Insurance, pensions, housing, urban design, employment, government and
  technology belong in the first paragraph, not as an appendix.
- Each discipline carries a short line on why longevity raises questions there.
- Institute-controlled; collaborators may propose additions for approval.
- The **shape** is fixed before M3; the **contents** remain his to grow forever.

### Names
- Separate first / optional middle / family name. No prefixes (Dr., Prof.).
  Up to three credentials after the name. Full Unicode. Generational suffixes
  (Jr., Sr., II, III) as a separate field so sorting stays correct.
- **Preferred Name** field. Prompt is "How would you like the Portal to address
  you?", never "if different from your first name". Shown as secondary
  information near the formal name, labelled e.g. "Addressed as Khim Tong".
  Falls back to the first name; hidden entirely when blank.

### Profile links (up to five)
- Two fields: **label** (about 50 characters, collaborator-entered) and **URL**.
- The public profile shows the label only, clickable. The URL is not printed,
  though the browser always reveals it on hover, which is why this is safe.
- The label **replaces** the earlier one-sentence explanation. Not both.
- Only http/https accepted. Links open in a new tab, isolated (`noopener`).
- The collaborator sets the order (Link 1 first). No Institute-wide standard order.
- Unused slots vanish from the published profile.

### Other confirmed standards
- Calm, professional design. Normal capitalisation. **Rectangular** photos.
  No social-media badges or stars.
- Admitted is not the same as visible. Directory visibility is gated by profile
  completion with an audited admin override. His two-step review then publish is
  deferred item 3.
- Country public; city and state admin-only, explained at collection.
- Unused optional fields disappear from published profiles.
- Photo standards: software enforces format, size, not-animated and crop; a human
  enforces head-and-shoulders, no hats and recency at his review step.
- Initial directory: browse plus search by name, expertise and country. Richer
  filtering waits for evidence (roughly 150+ collaborators).
- Organisations are structured records (built). **No public organisation
  directory** yet.
- **No messaging for the first Beta.** Use collaboration-interest records instead.
  **ASSIGNED 2026-09-02 to M3, absorbed free.** It was agreed in principle but
  never written into any milestone, and the visibility architecture he approved
  on 29 August already refers to it ("whether another collaborator may register
  an interest in working with them"). Bounded deliberately: a record and a note
  he can see, NOT threads, replies or notification preferences.
- Record analytics **events** from now, since they cannot be backfilled. The
  dashboard itself is deferred.

### Owner analytics dashboard (asked 2026-09-12, QUOTED $600, told him to WAIT)
The August "record events from now" decision paid off: **22 event types already
recorded** since M1, indexed on `occurred_at`, append-only. Already answerable
from data held today: registrations/admissions, unique actives per period, login
frequency, returning vs inactive, and trends.
- **Separate the record from the dashboard.** Recording must happen early and is
  absorbed; the dashboard is only a reading of it, can be built any time, and
  covers the whole period before it existed. This separation is the core idea
  explained to him and is what keeps it cheap.
- **Not yet capturable** (depends on M3 / announcements): profile completion,
  breakdown by discipline, directory searches and profile views, announcement
  readership. **M3 must add event recording for each new surface as it is built**,
  absorbed, on the same August principle.
- **Privacy as architecture, not policy:** aggregates only, NO per-named-person
  activity screen ever, and a **minimum group size** below which a breakdown is
  not offered. Same rule already agreed for the prospective-collaborator preview.
- **Pushed back on email open-tracking** for announcement readership: it needs a
  tracking pixel (intrusive, and a different thing from counting logins), and
  Apple Mail Privacy Protection and others pre-load images, so most "opens" would
  be machines. **Measure clicks back to the Portal instead.**
- **Small-numbers warning given:** at Alpha/Beta size two people on holiday looks
  like a 40% collapse. Show counts and plain trends, never dramatic percentages,
  and always state the base size.
- **Recommended he defer it:** nothing worth reading at 10 collaborators. Right
  moment is once the founding group is actually using it, around or after M4.
- **PRICE IS $600, NOT $400.** My draft said $400; Ashhad raised it to $600 before
  sending, and he has confirmed $600 back in writing. **$600 is the number of
  record.** Never quote $400 to him. A drafted "correction" to $400 was written
  and then pulled before sending.
- **LESSON, now proven TWICE:** Ashhad edits drafts before sending, so prices in
  my drafts are PROPOSALS, NOT FACTS.
  1. Analytics dashboard: I drafted $400, he sent **$600**.
  2. Credential library: he sent **$150**; my later $220 revision was never sent,
     yet I recorded $220 and carried a wrong M3 total of $1,170 into a client
     message and a PDF before he caught it.
  **Before writing any figure into a client message or document, ASK what was
  actually sent.** Never "correct" the client from my own draft, and never treat
  an unsent revision as the number of record.
  **NUMBERS OF RECORD: M3 = $1,100 ($600 + $350 + $150). M4 = $700.
  Remaining = $1,800. Optional total = $1,330. Dashboard = $600.**
- Alpha (5-10 people) maps to Wave 0. Beta (founding group) maps to Wave 1. A
  feedback mechanism is needed before Alpha. **ASSIGNED 2026-09-02 to M4,
  absorbed free.** Also agreed without ever being written into a milestone.
- English throughout. Acronyms defined on first use.
- "Curated Collaboration Portal" is his preferred term for the Portal.

## 6b. Alpha/Beta access route (answered 2026-09-14)

Permanent front door: **https://portal.activelongevityinstitute.org** (verified
live, HTTP 200). Invitation links are for FIRST REGISTRATION ONLY and are spent
on completion; everything after is the login page.
- **The real risk is not a lost bookmark.** It is somebody re-clicking the
  invitation email months later. Already handled: the `already_registered` branch
  of `invitation-unavailable` tells them so and links to sign in.
- **The Portal address must appear in every message the Portal sends**, so a
  collaborator's inbox is the index rather than their memory. Keep this true for
  every future mailable.
- Password reset works, but M1's anti-enumeration means a mistyped address gets
  the same calm message. **First thing to check if an Alpha participant says a
  reset never arrived.**
- Last gap closes when the Institute website carries a "Collaborator sign in" link.

## 6c. Taxonomy questions SENT 2026-09-14

`m3-proposal/Discipline-and-Specialty-Questions.html` + `.pdf` (8 questions plus a
22-row draft discipline table, supplied to be argued with, each row carrying a
draft "why longer lives raise questions here" line).
- **Headline instruction to him: do not try to be complete.** The request-new flow
  means the list only has to cover the founding group. Fifteen to twenty beats
  sixty. This is the single most useful thing in the document.
- Primary exercise: read down the founding spreadsheet and record what those
  people actually call themselves.
- Awaiting his answers. Nothing is blocked on it today, but it is on the M3
  critical path once M3 starts.

## 6d. Alpha readiness answered 2026-09-15

**PRODUCTION STILL RUNS M1.** `portal.../login` = 200 but `/apply` = 404. M2 has
never been released to production, only staging. **A production release is a
required, easily-forgotten step before any real person is invited.**

**Alpha readiness = M3 complete.** Today a registered collaborator signs in and
reaches a placeholder ("profiles and the directory open in a forthcoming
release") plus the Recommend button. Admission half done, use-it half not built.
Told his this plainly; it is not scope creep, profiles always lived in M3.

**Answer to "is anything outside scope": NO.** Everything needed for Alpha is
inside the four agreed milestones. Remaining $1,800 (M3 $1,100 + M4 $700).

**RESEQUENCED, absorbed:** the feedback mechanism moves **M4 -> M3**, because it
was agreed as "needed before Alpha" but sat in a milestone that comes after
Alpha. Owned as my error in the client message.

**Agreed route:** taxonomy answers (free, now) -> M3 -> visibility table approval
-> release M2+M3 to production together -> deliverability tests -> one live
admission with his -> Alpha -> M4 runs while Alpha group is using it.

## 6e. Mobile and free-text search answered 2026-09-15

Answered as ONE reply because both are the same worry: the taxonomy becoming
large and unwieldy. Shared answer: **keep the structured taxonomy small, let
search carry specificity.**

**Free-text search: the foundation was already specified in M1.** `docs/03-erd.md`
line 157 and `docs/01-architecture.md` line 97: PostgreSQL native, `tsvector` on
profile text, `pg_trgm` on names, explicitly no external search service. **But the
FEATURE is outside the M3 contract line** (name + expertise + country), so it is a
modest paid addition. **Deliberately not priced in the message; to be quoted with
the M3 design package.**
- Caveat given to him: free text finds words, not meaning. "Muscle loss" will not
  match "sarcopenia". That is why both structure and text must exist.

**Mobile commitments made (M3 design must honour these):**
- Long Field Family names **wrap, never truncate** (same rule as professional titles).
- **Search-first selection, never scroll-a-list** - the same design that makes a
  large vocabulary work on desktop, so mobile is not a degraded variant.
- Profile completion **sectioned**, resumable across sittings and devices.
- Photo upload/crop must work from a phone camera roll.
- Directory renders as a **list, not a wide table**.
- **Test on real devices**, not just a narrowed browser window. Stated as a
  delivery condition.
- Generous text sizes and plain layouts: senior readers, imperfect light.

## 6f. Field/Specialty architecture refinements (agreed 2026-09-16)

He tested the model against real professional identities and **reversed an earlier
decision**. This supersedes "collaborator selects specialties only":
- **Fields are directly selectable. Specialty is genuinely OPTIONAL.** Actuarial
  Science, Human Resources, Venture Capital are complete identities on their own;
  inventing specialties beneath them to keep the tree symmetrical would be
  manufacturing vocabulary nobody uses.
- **SUPERSEDED 2026-09-17. The union model is DEAD.** He reversed it and he was
  right: selecting a Specialty must NOT silently assign a Field. A journalist who
  writes about health policy is a journalist; filing them under Public Health
  misrepresents them, which is the exact failure the taxonomy exists to prevent.
  **Fields and Specialties are two INDEPENDENT selections. The Specialty->Field
  links exist ONLY for search, never for assignment or validation.** Someone
  searching Epidemiology still finds a person who chose Nutritional Epidemiology
  as a Specialty, without that person ever being labelled an epidemiologist.
  A search relationship, not an identity claim. Simpler than the union model.
- **No Primary Field, no hard maximum.** Told him gently that 3 Fields says more
  than 15, and to put that in the orientation rather than enforce it.

**Specialty -> Field is currently a SINGLE FK** (`expertise_areas.discipline_id`,
from M2 groundwork). **M3 must change it to a linking table (many-to-many).**
Already promised 27 Aug; small, planned, not a rewrite.

**Aliases: one small table**, term -> Field or Specialty. Two properties:
search covers names AND aliases together; **aliases are NEVER displayed** (profile
shows "Actuarial Science", not "Actuary"). An alias may point at MORE THAN ONE
entry.

**Longevity: deliberately left undecided, and that is safe.** Because an alias can
point at several entries, Longevity can surface geroscience/demography/actuarial/
architecture without being forced into the professional taxonomy. If he later
makes it a Field, or a cross-cutting theme, both remain open. Told him nothing
waits on this.

**No dead end:** if no term fits, he/she enters their own wording, profile saves,
they continue. Same pattern as the credential request. No moderation machinery for
Alpha's 8 people.

**Orientation screen** on first classification, dismissible, always reachable.
Key line: they are NOT being asked to describe everything they know.

## 6g. The "mini-app" is already built (answered 2026-09-16)

**The public application already lives in the Portal**, served at the Portal's own
address via `routes/web.php` `/apply`. What he calls the mini-app IS what M2
delivered. The website's role is a hyperlink. **No technical issue in "moving" it
because it never lived on the website.**

**It already shares the vocabulary:** `StoreApplicationRequest` validates
`expertise_claimed` against `expertise_areas`. There was never a risk of two
taxonomies.

**The ONE genuine gap: no carry-forward.** `RegistrationService` copies nothing
from the application, so an approved applicant would retype data. **Quoted $150,
after M3** (needs profiles to exist). Must carry public-appropriate fields
forward, keep `motivation` (admission-decision-only) permanently private, and
present as confirm-not-retype.

## 6h. Master Field Register v0.2 received 2026-09-17

**64 Fields**, far above the 15-20 I advised. **He is right and I conceded.** His
test ("would an accomplished professional recognise this as a legitimate
professional home?") beats mine. A long list is an inconvenience; a CHRO finding
nothing that describes them is a lost collaborator. Search-first selection means
64 costs nothing at the interface.
- His principle: **"Classify lightly. Describe richly."** Use it in the
  orientation screen copy.
- **Sparse-directory problem I raised unprompted:** 8 Alpha participants across 64
  Fields leaves ~58 empty. **The directory must show only Fields that contain
  collaborators, with counts.** Five-minute design decision now, awkward
  conversation after Alpha otherwise.
- **No dead end at BOTH levels** (Field and Specialty): enter own wording, profile
  saves, continue, never blocked pending review. Told him plainly this is
  designed but not built, since M3 has not started.
- **Specialty vocabulary deliberately not exhaustive before Alpha.** He now agrees
  with the original advice.
- **"Longevity" = Institute-wide Discovery Term.** Works because an alias may
  point at SEVERAL entries. Not a Field, not forced under one. Search gathers
  people across geroscience/demography/actuarial/architecture regardless of how
  they classified themselves.
- He asked to be told when something conflicts rather than have it absorbed
  silently. **Honour that.**
- **Reply SENT 2026-09-17** (`m3-proposal/fiverr-reply-field-register.txt`).
- **Register REVIEWED and reply written 2026-09-18**
  (`m3-proposal/fiverr-reply-register-review.txt`). Source doc:
  `MASTER FIELD REGISTER v0.2.docx`; plain-text extract at
  `project longevity/register_extract.txt`.
- **HEADLINE FINDING: 3 of his OWN August priorities are missing from the 64.**
  He said insurance, pensions, housing, urban design, employment, government and
  technology must be "in the first paragraph, not as an appendix". Present: urban
  design, employment, government (Public Administration), technology. **ABSENT:
  Insurance, Pensions, Housing.** Nearest neighbours are NOT substitutes -
  Actuarial Science prices risk but does not run an insurer; Financial Planning is
  not pensions; Architecture describes designers, not housing provision.
- **Near-neighbour problem is the real implementation risk, not the number 64.**
  Social Care/Social Work; Public Health/Health Policy/Public Policy/Public
  Administration; Finance/Financial Planning/Investment Management/Venture
  Capital; Statistics/Biostatistics/Bioinformatics; Technology/Computer
  Science/AI; Nutrition Science/Dietetics. All defensible, but bare names give a
  collaborator nothing to choose with. **Asked him for ~20 one-line descriptors
  (disambiguation, NOT definitions or longevity rationale) only where a Field has
  a close neighbour.**
- **Search must normalise `&` / "and" / hyphens** so "Genetics and Genomics" and
  "Speech Language Pathology" both resolve. Longest Field = 39 chars, fits.
- **Counts during Alpha: recommended OFF.** "Medicine 1" reads as deflating.
  Same switch logic as the preview aggregates; turn on when numbers flatter.
- Alias set is mostly job titles pointing at Fields; some point at several (data
  scientist -> Statistics AND Computer Science).
- **Commercial: nothing in this note adds scope.** Independent selection is
  SIMPLER than the union model. The alias feature (which "Longevity" now depends
  on) remains unpriced and absorbed; the $180 option was raised with Ashhad and
  not taken up.

## 6i. MILESTONE 3 BUILT (2026-09-19). Contracted scope complete; delivery in progress.

**Repo was broken and is fixed.** The folder reorganisation had split it: app,
config, database in `active-longevity-portal/` but resources, routes, public,
tests one level up. All restored; clean against remote.

**Built, 191 tests / 589 assertions, Pint + Larastan L6 + audit clean:**
- Vocabulary schema: Specialty->Field **many-to-many, search only, never
  assignment**; `profile_fields` + `profile_specialties` INDEPENDENT; aliases;
  credentials as RECORDS (3/profile, DB trigger); `vocabulary_proposals` (one
  engine, three vocabularies).
- His Registers loaded from `database/data/*.json`: **67 Fields, 410
  Specialties, 411 links, 133 aliases, 114 credentials.** 6 aliases unresolved
  until he sends Pensions/Insurance/Psychometrics specialties.
- `VocabularyNormaliser` (&/and, hyphens, US/UK, safe singular; both sides).
- `ExpertiseSearch` ranked exact > prefix > alias > contains > ambiguous.
- **Visibility**: `Audience` (4 rungs, 4th empty), `VisibilityRegister` (34
  fields, fails safe, CI-enforced), `portal:visibility-table` generates
  `docs/11-visibility-table.md` **from the enforced rules**.
- Classification screen (search-first, labelled, orientation once, propose-a-term
  never blocks). Profile edit (sectioned, saves per part). **Positions x3 (Item
  1, paid).** Photos (GD, 3:4 portrait 600x800, GIF refused, animated WebP
  refused, EXIF rotation, disk = `PORTAL_PHOTO_DISK`). Links (label 50, http/s
  only). Credential picker (meaning searchable, near-matches before request).
- Directory (browse, search name/expertise/country; **only occupied Fields
  shown; counts OFF at Alpha**; non-listable = 404 not 403). Profile page (city
  never rendered; links label-only).
- **Collaboration interest** (once per pair, only if target accepts, never self;
  Institute sees at /admin/interests). **Feedback** (footer link every page;
  /admin/feedback). Both were the "no milestone" items, absorbed into M3.
- Admin: **Proposals queue** (/admin/vocabulary: approve w/ standardised wording,
  decline, map-to-existing with near-match suggestions; proposer emailed on
  approve/map, NOT on decline). **Registers screen** (/admin/taxonomy: add,
  rename, reorder, retire Fields/Specialties/aliases + descriptor lines).
- **Seeders are CREATE-ONLY now.** They were updateOrCreate-by-slug and would
  have silently undone any Institute rename on the next deploy. Test proves a
  rename survives a re-seed.
- Reference data installs on every release via `portal:sync-reference-data`.

**Bugs the tests caught before they shipped:** `sortBy` array misread (exact
matches not first); `Audience::canSee` inverted (community could see
Institute-only data); morph map omissions x2 (audit trail refuses unknown
models - the guard working); user factory random middle names making
directory assertions flaky (pin names in tests); deferred constraint trigger
needs `SET CONSTRAINTS ALL IMMEDIATE` inside RefreshDatabase.

**Livewire is NOT installed** despite the stack note. M1-M3 are plain Blade +
progressive-enhancement vanilla JS. Do not add it.

**Not done, blocked on him:** the ~240 founding-collaborator spreadsheet load
(he has not sent the spreadsheet). Descriptor lines for ~29 Fields (he is
writing them; enter via /admin/taxonomy or `expertise-vocabulary.json`).

**Production:** m2.1 deployed 2026-09-19 (M2 + M3 foundation). Production has
NO collaborator accounts, so nothing is exposed. m3.0 to follow after staging
verification. The visibility table is in the delivery package for his approval
before any participant is invited.

**Commit discipline lesson (bit me twice):** `php artisan test | grep` swallows
the exit code. Always `test > file; RC=$?` and commit only if RC=0.

## 7. Current state and next actions

**2026-10-05: LINAS'S SCREENSHOT REVIEW APPLIED, tag `m3.4` live (fee9026).**
His PDF "Response to M3 Update.pdf" (Downloads) + 3 emails. Done free in M3:
logo (compass mark from activelongevityinstitute.org/favicon.svg ->
public/images/ali-logo.svg, x-portal-mark) + "Collaborator Portal" in every
header, Institute name in footer, "Curated" removed; profile "Your name"
section incl. "Name I go by" (preferred_name; registration label renamed);
readiness checklist (ProfileEditor::missingForReadiness); About ->
"Additional Information"; App\Support\Countries full names + selects; 3
numbered credential lines; decline emails collaborator (VocabularyDeclinedMail,
confirm dialog) - reverses the earlier "decline sends nothing" rule at his
request; interests show emails + mailto introduction; Admin "Collaborators"
page (invited / in progress / ready) + CollaboratorRegisteredMail to founders;
Registers column labels, sort_order from 1; invitation expiry 90 days. 212
tests. Revoked a stray Aug test invitation in Ashhad's real name on staging.
PENDING: user must create Linas's staging founder account in DO console
(`portal:create-admin linas@activelongevityinstitute.org Linas Liubinskas
--founder`) and WhatsApp him the password; check staging MAIL_MAILER (may be
`log`). Reply text: m3-reconciled-delivery/fiverr-reply-review-answers.txt.
Open question to him: should a Specialty alone satisfy readiness?

**2026-10-03: AMERICAN ENGLISH THROUGHOUT, tag `m3.3` live.** Linas: all
Institute materials use American English. Every visible British spelling fixed
(views, mail, validation names, permission descriptions, credentials.json,
demo seeder); identifiers/columns (organisation_*, Organisation model) and the
VocabularyNormaliser's UK->US mapping deliberately unchanged.
tests/Unit/AmericanEnglishTest fails on British spelling in any Blade file.
207 tests. Walkthrough was sent as video + 28 screenshots (he prefers the
screenshots; video hard to follow without sound). He is still reviewing before
confirming M3 complete. **Write every client-visible string in American
English.**

**2026-10-02: EIGHT COLUMN-B ADJUSTMENTS DONE, tag `m3.2` live on staging +
production** (fb81dc9 + wording fix). Linas chased on 2 Oct; they had slipped
(approved 27 Sep, not started). 206 tests / 659 assertions. Readiness now =
name, identity (professional_title), About Your Work (one_line_summary, 500),
Field or pending Field proposal, country, photo; migration re-judges every
profile, so staging demo profiles without photos left the directory (only
Helena Marsh listed). Standards notice: users.profile_standards_shown_at /
_version, config portal.profile_standards_version = 1.0, page /profile/standards.
Next: walkthrough with Linas (WhatsApp voice, early morning Chicago), then
close M3; M4 ($700 + $150 M3 balance) and column C ($1,460) only on his word.

**MONEY OF RECORD (corrected 2026-09-27):** Linas paid **$950** against M3's
$1,100 on 2026-09-19 and said the remaining **$150 will be paid with M4**. So paid
to date = $600 + $600 + $950 = **$2,150** (+ $50 tip). Still owed on contract =
M4 $700 + M3 balance $150 = **$850**. Column C quotes ($1,460 all, or $1,190
sections 9-19 as a group + $180 review step + $90 credential panel) and the older
optionals (dashboard $600, announcements $300, preview $250, carry-forward $150)
are on top of that and unauthorised. When M4 is invoiced, include the $150.

**2026-09-27: M3 RECONCILIATION SENT; production tagged `m3.1`; M4 ON HOLD.**
Linas paid M3 (19 Sep), then sent a 33-point profile/directory architecture
(21 Sep), the v1.0 Registers (23 Sep), a credential-panel note and detailed
specs for sections 9-19. Agreed method: three columns (A covered / B covered
differently, adjusted free within M3 / C new, costed, NOT started). He is
self-funded and rebuilding funds; Alpha waits until the public website has
been live 30 days; nothing new starts without written authorisation.
Delivered: `m3-delivery/M3-Reconciliation.html` + `.pdf` (14 pages) and
`m3-proposal/fiverr-message-reconciliation.txt`. Column C prices: sections
9-19 $1,460 singly / $1,190 as a group; Directory Review step $180;
credential panel $90; everything $1,460. Column B adjustments (free, on his
word): orientation wording + Profile Standards notice with recorded
version/time; Professional Identity rename/prompt; About Your Work 500 chars
required (was one_line_summary 160); About 2,000 (was about 5,000); positions
+ country + start year; photo required for readiness + reworded guidance;
readiness rule = name, identity, About Your Work, Field or pending proposal,
country, photo, wording "ready for Directory Review" + Preview; directory card
= photo, name+credentials, identity, country (no badges/summary).
`m3.1` (991b7c3): v1.0 Registers loaded (67 Fields, 29 descriptors filled only
where null, 419 Specialties / 434 links, 144 aliases, "do not force" list
excluded, "Pensions & Retirement Benefits" Specialty retired), Longevity
search notice, credentials.issuing_body/description columns (approval fills
blanks). 200 tests / 628 assertions. Earlier replies in `m3-proposal/`.

**2026-09-19: M3 DELIVERY PACKAGE BUILT; production tagged `m3.0`.**
193 tests / 606 assertions green. `m3-delivery/` holds `M3-Delivery-Report.html`
+ `.pdf` (49 pages, 19 embedded screenshots, Appendix A = visibility table), `fiverr-delivery-message.txt`,
`walkthrough-script.md`, `Who-Can-See-What.pdf` (visibility table, also Appendix A of the report), `screenshots/` (24 PNGs) +
`screenshots.zip`, `video/M3-Walkthrough.mp4` (6m44s, subtitles burned in,
`.srt`/`.vtt`). Recorded on staging by `demo-m3.mjs` (session scratchpad) after
`portal:remove-example-accounts --confirm` cleaned earlier runs; demo people
Helena Marsh (actuary) and Rafael Duarte (geriatrician) remain on staging with
a Field "Longevity Economics" approved from Helena's proposal.

**Live-server faults found and fixed during delivery (all in the report):**
release `rsync --delete` would have wiped `storage/app` uploads and the
`public/storage` link (now excluded; release runs `storage:link --force` and
`chmod -R g+rwX storage/app`); `directory_visible` defaulted false so complete
profiles 404'd on their own page (now true by default, migration 000005,
`Profile::isListable()` is the one rule); the GD preflight must ask PHP
directly (`grep -q` + pipefail false-alarms). The droplets DO have php8.3-gd.
SSH to droplets is blocked in auto mode; the user runs commands in the DO web
console as root (`sudo -u deploy php artisan …`). His `~/.ssh/id_ed25519` is a
work key: never suggest it for this client.

**Still pending at M3 sign-off:** spreadsheet load (he has not sent it); ~29
Field descriptor lines (he is writing them); he should test staging himself.
Next: M4 (hardening, monitoring, docs, training, cold-start test, launch).

### Earlier state (M2)


M1 is complete. **M2 build is complete, pushed and LIVE ON STAGING as of
2026-08-30** (5 commits, `97d6f6d`). CI green, staging deploy green, pages
verified live. Design package: `docs/10-m2-design-package.md`.

**Audited 2026-09-01 against every M2 promise; three gaps found and closed.**
Only one item is deliberately unbuilt: the **optional courteous reply on
declining an application** — he never gave final confirmation, so it waits on
his, not on us. Ask his before M2 sign-off.

**M2 DELIVERY PACKAGE BUILT 2026-09-01** in `m2-delivery/`:
`M2-Delivery-Report.html` + `.pdf` (19 pages, 15 embedded screenshots),
`fiverr-delivery-message.txt`, `walkthrough-script.md`, `screenshots/` (16 PNGs)
and `screenshots.zip`. **Every screenshot was captured from live staging** by
driving the real UI end to end through the real MFA wall (Playwright script kept
in the session scratchpad; it signs in with TOTP, applies, questions, approves,
invites, registers, refers, and captures the courteous pages).

**Walkthrough video DONE** (2026-09-01): `video/M2-Walkthrough.mp4`, 7m16s,
1280x720 H.264, 8.7 MB, **subtitles burned in** plus `.srt` and `.vtt` supplied.
Recorded by driving live staging with Playwright; captions are a DOM overlay
injected via `addInitScript`, so no subtitle-capable encoder is needed. Full
ffmpeg installed via `winget install Gyan.FFmpeg` (Playwright's bundled ffmpeg
is webm-only and cannot make MP4).

**Still outstanding before sign-off:** send the three email delivery tests, and
sit with him while he runs a complete admission herself.

**Trap that produced a wrong first cut of the video:** the queue's second column
reads "Referred by <name>", so `locator('tr', {hasText: 'Nadia Okonkwo'})`
matched a *referral* row when looking for the applicant, and the demo invited
the wrong person while narrating about another. Match the name column exactly
(`td:first-child:text-is(...)`) and assert the `<h1>` shows the expected person
before narrating about them.

**Gotcha for any future UI automation:** the signed-in layout puts a "Sign out"
submit button in the header, so `click('button[type=submit]')` signs you out.
Target buttons by their text.

**Built and proven (95 tests, 285 assertions; Pint clean; Larastan L6 clean;
composer audit clean; migrate:fresh --seed clean; rollbacks verified):**
- Four review actions with Set Aside; only Approve/Decline write a decision.
- Contact rules enforced in the domain: referral questions go to the *referrer*,
  returned as a `ContactPoint` value so it is testable, never template logic.
- Copy-link invitations with **no email address** (`invitations.email` is now
  nullable; DB check forces an address only for email delivery).
- **GET never consumes a token**; only completed registration does.
- Courteous landing page for lapsed/withdrawn/used links; unknown token shows
  the identical page.
- Link shown once (`IssuedInvitation` returns it; only the hash is stored).
- Partial unique index makes a second live invitation per case impossible.
- Candidate case convergence with confident/possible matching, never auto-merge.
- History assembled from real records (no parallel log table).
- Salutation resolver in `App\Support\Salutation`, used by every message.
- **Referrer told once an invitation is issued** (and at no other moment; never
  about a decline or a pause). Guarded against reissue sending it twice.
- Generational name suffix, held apart from the family name so sorting is right.
- Daily `portal:expire-invitations` sweep so lapsed invitations stop counting as
  outstanding and the history can say one went unused.
- Duplicate suggestions explain themselves when the match is already
  admitted/invited/decided instead of offering a join that would be refused.

**Deployment facts learned the hard way (2026-08-31):**
- Deploys run `migrate --force` only. Reference data now installs via
  `portal:sync-reference-data` on every release — **without it the referral form
  has an empty relationship list and is unusable.**
- **No scheduler cron existed.** Deploys now install the `schedule:run` crontab
  line idempotently and verify it. Without this any scheduled task silently
  never fires.
- The release script runs under `set -euo pipefail`: `crontab -l` with no
  crontab, and `grep -v` filtering every line, both exit non-zero and will abort
  a release that has already succeeded. Neutralise with `|| true`.
- No SSH access to the droplets from the dev machine; the deploy key lives only
  in GitHub secrets. Server-side changes must go through the deploy workflow.
- Character limits 100/200/200/300 in `App\Support\FieldLimits`, matching the DB.
- Item 1: `current_position` profile-entry section, capped at three by the DB.

**Key architectural notes for future sessions:**
- **Livewire is NOT installed** despite earlier notes. M2 is server-rendered
  Blade + controllers + FormRequests. Do not assume Livewire.
- Larastan needs `--memory-limit=1G` (CI already passes it).
- Blade does not compile a directive preceded by a word character
  (`Institute@if(...)` silently breaks). Use an inline ternary instead.
- `UserFactory` sets `middle_name` at random 20% of the time; pin it in tests
  that assert on `fullName()`.
- Copy-link registrations produce an **unverified** email address (the person
  typed it; the Institute never posted to it). Visible in the audit context.
  A verification flow is a small open item for M4.

**Immediate next:**
1. Send the three delivery tests: Gmail, Outlook, and **Albert Tuskenis,
   ATuskenis@govst.edu** (the university test, which matters most). Report which
   folder each landed in.
2. Prepare the discipline and specialty taxonomy for his review, questions-first.
   This is the one item where *he* is the bottleneck, so send it early.
3. Push, deploy to staging, and record the M2 walkthrough for delivery.
4. Wire the Sentry DSN when he supplies it.

**M2 closes with** Linas running a complete admission live: website application,
review, decision, invitation, registration, admitted collaborator. This journey
is already covered end to end by
`tests/Feature/Admission/CompleteAdmissionJourneyTest.php`.
