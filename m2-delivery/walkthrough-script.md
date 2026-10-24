# M2 Walkthrough Video — Recording Script

**Target:** 10–12 minutes · plain English · calm pace · one continuous admission,
told as a story rather than a feature tour.

**The spine of the video:** a real person arrives, is questioned, is approved, is
invited, and joins. Everything else hangs off that. Resist the urge to show
features in isolation; she understands the Portal best when she is watching
somebody travel through it.

---

## Before you record (20 min prep)

1. **Sign in to staging as the founder first, off camera**, so the video never
   shows the two-factor code being typed. Sign in at
   `https://staging.portal.activelongevityinstitute.org/login`, enter the code
   from the authenticator app, and stay signed in.
2. **Clear the decks in the queue.** If staging has leftover test cases from the
   screenshot run, either leave them (they make the queue look realistic) or
   decline them off camera. A queue with three or four entries reads better than
   an empty one.
3. **Clean browser profile.** New Chrome profile or guest window. No bookmarks
   bar (Ctrl+Shift+B), no extensions, no personal tabs.
4. **Two browser windows, side by side or on separate desktops:**
   - Window A: signed in as the founder (the Institute's side)
   - Window B: a private window (the candidate's side)
   You will move between them. Label them in your head as "you" and "them".
5. **Tabs pre-opened in Window A:**
   - Tab 1: `/apply` (signed out is fine, it is public)
   - Tab 2: `/admin/queue`
6. **Recorder:** OBS Studio or Loom. 1080p, system and microphone audio. Record
   the browser window only, never the full desktop.
7. **Never show:** the terminal, DigitalOcean, Postmark, any `.env`, the
   repository, or the credentials file.

---

## Scene 1 — Opening (40s, Window A, Tab 1)

> "Hello Linas. This is Milestone 2, the admission system, and I want to show it
> to you the way you will actually use it. Rather than walk through screens one
> at a time, we are going to follow one person all the way in: she applies, you
> ask her a question, you approve her, you invite her, and she joins. Everything
> we agreed over the last few weeks appears somewhere along that path, and I will
> point each one out as we pass it."

---

## Scene 2 — Somebody applies (1.5 min, Window A, Tab 1)

Show the application form. Scroll slowly.

> "This is the form on the Institute's website. Anybody can reach it. No account,
> no password, nothing to set up."

Point at the note under the professional title field.

> "This line matters more than it looks. People shorten their own titles when they
> suspect a form will not take the whole thing, so we tell them plainly that there
> is room. There are three hundred characters here, which comfortably holds a named
> chair with a donor, a department and a second appointment."

Fill it in as a real person would. Use a long title deliberately. Submit.

> "And that is the confirmation. Notice what it does not say. It does not promise a
> date, because the Institute has not promised one. It says a person will read it,
> which is true."

---

## Scene 3 — It arrives in your queue (1 min, Window A, Tab 2)

Refresh the queue.

> "Here is your side. Applications and referrals arrive in one list, oldest first,
> so nobody waits longest just because they came in by a different door."

Open the case.

> "Everything she wrote, in full. Look at the title: it wraps onto a second line
> rather than being cut off with three dots. Whatever the Portal accepted, the
> Portal shows."

---

## Scene 4 — The four actions (2.5 min, on the case)

Scroll to the actions. Go slowly here; this is the heart of it.

> "Four things you can do, and this is the part we spent the most time getting
> right. Two of them are judgements and two of them are not."

Point at each in turn.

> "Approve is final and goes into the permanent record. Decline is also final, and
> asks you to write down why, because a judgement without a reason is not much use
> to anyone reading it in three years."

> "Need more information is not a decision at all. It is a question. The case stays
> open, and you can ask again as many times as you need. That was the distinction
> you drew, and it matters: if questions were recorded as decisions, somebody you
> asked twice and then approved would look in the record as though you had decided
> about them three times."

> "Set aside is a pause, for you. Nothing is sent to anyone. It stays right here in
> the queue where you will see it again, because a case that vanishes when you pause
> it is a case that gets forgotten."

Ask a question. Submit. Show the status change.

> "It now says awaiting information, and it is still open. Nothing has been decided."

Record a reply.

> "When she writes back, you record what she said, and the case comes back to you.
> The question and the answer both stay in her history."

---

## Scene 5 — Approving, and what approving does not do (1 min)

Approve her.

> "Now watch what did not happen. She has not been invited. Approving records your
> judgement and stops there."

Point at the invitation panel that has now appeared.

> "Inviting is a separate act, and it only becomes available after approval. That was
> deliberate. Inviting somebody is a consequence of deciding about them, never
> another way of deciding about them."

---

## Scene 6 — The invitation, and the LinkedIn route (2 min)

Open the invitation form.

> "This is the small form you asked about. Her name is the only thing required. If
> you found somebody on LinkedIn and have no email address for them, that is fine,
> because most of the time you will not have one, and I did not want you inventing
> an address to satisfy a form."

Choose the copy-link option. Add a personal sentence. Generate.

> "And here is the link, once. Only a fingerprint of it is stored, so there is no
> page anywhere that can show it to you again. Not this page on a refresh, not me,
> not anybody."

Let that sit for a beat.

> "If you lose it, this button issues a fresh one and the old link stops working at
> that same moment. So losing it costs you a click, not a support request."

Copy the link.

---

## Scene 7 — The scanner test (1.5 min, Window B)

Paste the link in the private window. Open it. Go back. Open it again. And again.

> "This is the thing you were right to worry about. LinkedIn and Outlook open links
> to inspect them before a human ever sees them. I have just opened this one three
> times, exactly as a scanner would."

Show the page still working.

> "Still perfectly good. Opening it changes nothing. It is only used up when somebody
> actually finishes registering, which means a scanner can never burn an invitation
> before it reaches the person it was meant for."

Point at the personal sentence.

> "And there is your sentence, in your words, exactly as you wrote it."

---

## Scene 8 — She joins (1 min, Window B)

Fill in the registration. Pause on the preferred name field.

> "She confirms her own details rather than being stuck with whatever you typed. You
> are the authority on who is admitted; she is the authority on the facts of her own
> life."

> "And this field is how she tells the Portal what to call her. If she writes Nadia
> here, every message from the Institute begins Hello Nadia. If she leaves it blank
> we use her first name. If we do not confidently know a name at all, we greet her
> without one rather than send Hello comma."

Complete registration.

> "She is now an admitted collaborator."

---

## Scene 9 — Try the link again (20s)

Reload the same link.

> "And the link is spent. Single use, exactly as agreed."

Then visit any made-up invitation address.

> "A link that has expired, or been withdrawn, or simply never existed, gets this
> page. Never an error message. Arriving at a lapsed link is not a mistake the
> visitor made, so we do not treat it like one. And a wrong guess sees exactly the
> same words as a real expired invitation, so nothing here tells anybody whether
> they have stumbled onto something real."

---

## Scene 10 — The history (1.5 min, Window A, back to the case)

Open her case again and scroll to the history.

> "And here is the whole story in one place. She applied. You asked her something.
> She answered. You approved her. You invited her. She joined."

> "This is not a separate log that we write alongside everything else. It is built
> from the records themselves, which means it cannot quietly disagree with what
> actually happened, and no future change can forget to write to it."

---

## Scene 11 — Referrals and the relationship question (1.5 min)

Switch to the collaborator's window, go to Recommend.

> "Collaborators can recommend people too. And this is the question you added on the
> twenty-seventh."

Point at the dropdown, and open it.

> "Notice these two. Not Mentor and Mentee, which cannot tell you who mentored whom,
> but I mentored them and They mentored me. Same question, no ambiguity, and it
> changes how you would word an introduction."

Point at the note under the panel.

> "And the referrer is told, right here, that these answers are seen by the Institute
> alone. They never reach the person being recommended and never appear on a profile."

Submit it, then show it in the founder's queue with the private panel visible.

> "There it is on your side, marked seen by the Institute only. One more thing worth
> saying: nothing has been sent to Tomas. He may not even know he has been
> recommended. If you need to understand something before you decide, the Portal asks
> the referrer, never him. The Institute does not contact a referred professional
> until you have approved them."

---

## Scene 12 — Closing (40s)

> "That is Milestone 2. One person, from a form on your website to an admitted
> collaborator, with every step recorded and nobody contacted before you decided
> they should be."

> "Two things I have left for you. The courteous reply when you decline an
> application is not built, because you had not finally confirmed it and I was not
> going to have the Portal write to somebody you had chosen not to contact. Say the
> word and it is a small addition."

> "And when you are ready, I would like to do this with you rather than for you: you
> at the controls, running a real admission start to finish. That is how this
> milestone is meant to close."

> "Milestone 3 is profiles, the directory and search, and it opens with the
> visibility table we agreed, for you to read before I write any code. Thank you,
> Linas."

---

## After recording

- Watch it once at 1.5x looking only for anything that should not be on screen:
  terminals, credentials, other tabs, notifications.
- Turn off system notifications before recording, not after.
- Export 1080p. Keep it under about 500 MB so it uploads to Fiverr cleanly.
- Save as `video/M2-Walkthrough.mp4` in this folder.
