# M1 Walkthrough Video — Recording Script

**Target:** 9–11 minutes · plain English · calm pace · her four questions in order.

## Before you record (15 min prep)

1. **Enroll MFA on the staging founder account first** (waga@prontodigital.dev).
   Sign in → you'll be sent to the enrollment page → Begin enrollment → confirm
   password → scan the QR with any authenticator app (Google Authenticator etc.)
   → enter the code. Do this OFF camera — then the video can show the full
   polished flow: password → phone code → dashboard.
2. **Clean browser profile**: new Chrome profile or guest window. No personal
   tabs, no bookmarks bar (Ctrl+Shift+B), no extensions visible.
3. **Tabs pre-opened, in this order** (you'll just move left to right):
   - Tab 1: http://167.71.98.53/login (staging)
   - Tab 2: http://167.99.63.85/login (production)
   - Tab 3: github.com/ActiveLongevityInstitute/active-longevity-portal
   - Tab 4: the repo's Actions page
   - Tab 5: the repo's docs/ folder
   - Tab 6: DO project resources (cloud.digitalocean.com → Active Longevity Portal project)
   - Tab 7: DO → Databases → portal-postgres → Settings (Trusted Sources visible)
4. **Recorder**: OBS Studio (free) or Loom. 1080p, system + mic audio. Record
   the browser window only — not the full desktop.
5. Have your phone (authenticator app) next to you for the sign-in scene.
6. **Never show**: DigitalOcean API pages, Postmark, any .env, terminal windows.

---

## Scene 1 — Opening (30s, on Tab 1, not signed in)

> "Hello Lina. This is a walkthrough of Milestone 1 in plain language. One thing
> to say up front: this milestone was about building the foundation — the
> security, the ownership, and the machinery underneath the Portal. Most of it
> is deliberately invisible, so in this video I'll make it visible: what exists,
> what the Institute owns, how administrative access is protected, and why this
> sets up Milestone 2 to move quickly."

## Scene 2 — What has been built: the live Portal (2 min, Tabs 1–2)

Show the staging sign-in page. Point at the Institute name, the calm design.

> "This is the Portal, live on the internet, running on the Institute's own
> servers. There are two identical systems: a staging system — a private
> rehearsal space where you and I review new work first — and the production
> system, which will be the real Portal. Nothing reaches production until it
> has been proven on staging first."

Flip briefly to Tab 2 (production login):

> "Same Portal, on the production server. Right now they answer on plain
> numeric addresses; they'll take the portal.activelongevityinstitute.org name
> when we do the domain step together — the systems behind the name are
> finished."

> "You'll notice there's no 'create account' button anywhere. That's by design —
> this Portal is invitation-only. The only way in is an invitation issued by the
> Institute. That rule is enforced in the foundation itself, not just hidden
> from the page."

## Scene 3 — How administrative access works (2.5 min, Tab 1)

Sign in with email + password. When the code prompt appears, hold up cadence:

> "Signing in as an administrator takes two things: the password, and a
> six-digit code from a phone. Even if a password were ever stolen, the
> administrative area stays closed without the phone."

Enter the code, land on /home, then go to /admin — the dashboard appears:

> "This is the administrative view. It already tracks the numbers that will
> matter in Milestone 2: collaborators, open applications, referrals, and
> outstanding invitations — all zero today, because admission opens next
> milestone."

Then the killer line:

> "Two more protections you can't see: administrators who haven't set up their
> phone code are stopped and sent to enrollment before they can touch any
> administrative page. And every significant action — every sign-in, every
> future admission decision — is written into a permanent record that the
> system itself refuses to let anyone alter. Even me. Institutional memory,
> built in."

Sign out.

## Scene 4 — What the Institute owns (2.5 min, Tabs 3, 6, 7)

Tab 3 (GitHub repo):

> "Everything I write lives here — and look at the name at the top:
> ActiveLongevityInstitute. This is the Institute's own account. I work inside
> it with delegated access. If our engagement ever ended, you change nothing —
> it's already yours, with its full history."

Tab 6 (DO project resources):

> "The servers and the database, in the Institute's own hosting account: the
> production server, the staging server, and the database. Roughly thirty-three
> dollars a month, billed directly to the Institute — inside the estimate from
> the proposal."

Tab 7 (Trusted Sources):

> "One detail I want to show because it matters: the database — where
> collaborator information will live — accepts connections from exactly two
> machines: the two Portal servers. Not from the wider internet. Not even from
> my own computer."

## Scene 5 — The quality machinery (1.5 min, Tabs 4, then 3)

Tab 4 (Actions, green runs). Open the latest CI run to show the steps:

> "Every single change goes through this gate before it can ship: code style,
> a deep automated inspection, a security check of every third-party component,
> and forty-three automated tests. If any step fails, the change cannot be
> deployed. The green marks are those gates passing — you'll be able to see
> this page at any time."

> "Among those tests are ones that encode your standards directly: names with
> international characters are preserved exactly; a collaborator being admitted
> is separate from being visible in the directory; and the permanent record
> refuses to be rewritten."

## Scene 6 — The safety net (1 min, can stay on any tab)

> "One more thing that was rehearsed, not just promised. The database is backed
> up automatically every day. We took a real backup, restored it onto a clean
> database, and checked every table — a perfect match. Then we deliberately
> tried to tamper with the history in the restored copy, and the database
> refused. So even in a disaster-recovery situation, the Institute's records
> come back complete and still tamper-proof."

## Scene 7 — Docs + the road to Milestone 2 (1.5 min, Tab 5)

Tab 5 (docs folder, scroll slowly):

> "And this is the documentation — the architecture, every significant decision
> with its reasoning, and step-by-step operational guides: how to deploy, how
> to recover, how to respond to an incident. Written so that any competent
> developer could take over tomorrow. That's your portability guarantee taking
> shape — it gets formally proven in Milestone 4's cold-start test."

Close:

> "So why does this make Milestone 2 fast? Because the admission system's
> machinery — invitations with secure one-time links, the referral records,
> applications, decisions, the permanent history — was all built and tested as
> part of this foundation. Milestone 2 is about giving that machinery its
> screens: your review queue, the referral form, the application form, and the
> registration journey. Then we'll walk a complete admission through, end to
> end, together."

> "Take your time with all of this, and send any questions my way — I'm happy
> to go deeper on anything. Thank you, Lina."

---

## After recording

- Watch it once fully before sending (check no personal info flashed by).
- Export at 1080p; if the file exceeds Fiverr's attachment limit, upload
  unlisted to Loom/Vimeo and send the link in the order chat instead.
- Deliver in the order chat (not a new delivery) with one line:
  "As requested — a plain-English walkthrough of Milestone 1. Questions very
  welcome."
