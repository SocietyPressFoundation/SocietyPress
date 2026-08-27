# Theme Exchange — review policy

**Status: draft.** Nothing here is in force yet. It is written to be argued
with, and the submission queue will be built to match whatever it says once it
has been settled.

---

## What the Exchange is

Societies make their sites look like themselves, and then have no way to lend
that work to the society three counties over that admires it. The Theme Exchange
is that lending shelf.

It has three tiers, and they are three different levels of trust.

**Tier 1 — a saved look.** Colors, fonts, spacing, sizes. A file of settings and
nothing else. The worst a bad one does is make a site ugly, and one click puts it
back. No review.

**Tier 2 — a bundle.** A saved look plus a stylesheet and image files, in a
`.spchildtheme` archive. SocietyPress strips anything from the stylesheet that
is not styling and accepts only image files alongside it. A bundle cannot *do*
anything; it can only look like something. No review.

**Tier 3 — a child theme.** A real WordPress theme. This document is about
Tier 3 and only Tier 3.

---

## Why Tier 3 is different

A WordPress theme contains program code. It runs on the society's own server,
with the site's own permissions, every time a page loads. A good one gives a
society something genuinely its own. A bad one can read the member list.

That is the whole reason for a review. Nothing about a theme's appearance can be
dangerous. Everything about its code can be.

---

## What the badge means, and what it does not

A theme that passes review carries **Reviewed by SocietyPress** where a society
can see it before installing.

It means: a person read the theme's code, start to finish, at the version being
offered, and found nothing that reaches outside making the site look a certain
way.

It does not mean the theme is well built, that it will suit your society, that it
will keep working after the next WordPress release, or that anybody is obliged to
maintain it. It is a statement about safety and nothing else.

**Who reviews.** Today, the SocietyPress maintainer, personally. There is no
review board, and the policy will say so plainly rather than implying a committee
that does not exist. If that changes, this section changes with it.

---

## What is accepted

A theme is accepted when all of the following are true.

1. **It is a child theme of the SocietyPress parent theme.** Not a standalone
   theme, not a child of something else.
2. **It is licensed GPL-2.0-or-later**, like everything else here, and says so.
3. **Its code does presentation and nothing else.** Templates, styling, and the
   small amount of PHP it takes to arrange them.
4. **Every asset it ships is in the archive.** No fonts, scripts, styles, or
   images fetched from somewhere else at page load.
5. **It names a person or a society as its author**, with a working contact
   address.
6. **It installs on a clean site and works** without instructions beyond
   "activate it."

## What is turned down

Any one of these ends the review. There is no partial pass.

- **Anything that sends data anywhere.** No analytics, no phoning home, no
  telemetry, however anonymous, however well meant.
- **Anything that fetches code at runtime** — remote scripts, remote stylesheets,
  fonts loaded from a third party, an updater of its own.
- **Anything that reads what it has no business reading.** Member records,
  payment records, user accounts, settings unrelated to appearance.
- **Anything that writes outside its own settings.** Creating pages, changing
  options, adding users, touching another plugin's data.
- **Obscured code.** Minified PHP, encoded strings, anything a reviewer cannot
  simply read. Not because it is necessarily malicious, but because it cannot be
  reviewed, and an unreviewable theme cannot carry a badge that says it was.
- **Advertising.** No links, credits, or branding that a society cannot remove.
- **Anything that alters SocietyPress's own behavior** rather than its
  appearance.

## What is asked for but not required

These do not decide acceptance. They are said out loud because they are what
separates a theme somebody uses from a theme somebody tries once.

- Works on a phone.
- Readable contrast, and text that survives being enlarged.
- Sensible behavior when a society has no logo, no hero image, or no events.
- A screenshot that shows what it actually looks like.

---

## When a reviewed theme turns out to be bad

It will happen eventually. The policy that matters is the one written before it
does.

1. **The badge is withdrawn immediately**, before anybody is contacted and before
   anything is explained. A badge in doubt is a badge removed.
2. **The theme is delisted** from the Exchange the same day.
3. **Societies running it are told**, by name, what was found and what to do. Not
   a changelog line — a message that reaches the person who installed it.
4. **What was found is published**, including the fact that it passed review.
   A review process that hides its misses is worth less than no review at all.
5. **The author may submit again**, unless what was found looks deliberate.

A society is never left with a theme it cannot get out of: switching back to a
SocietyPress theme is one click, and no theme in the Exchange is permitted to own
anything a society would lose by leaving it.

---

## Submitting

To be built once this policy is settled. The shape it will take:

- A society submits a `.zip` file and the contact details of a person.
- It joins a queue that is public — everybody can see what is waiting, how long
  it has waited, and what was decided.
- The review is a checklist drawn from this document, and the completed checklist
  is published with the decision.
- A rejection says which line it failed and what would fix it.

---

## Open questions

Things this draft does not settle, and should before it stops being a draft.

- **How long a review is promised to take**, if anything is promised at all. One
  maintainer cannot promise a week.
- **What happens to a theme whose author disappears.** Delisted at the next
  WordPress release that breaks it, or adopted?
- **Whether a re-review is required on every update**, or only when the code
  changes.
- **Whether the Exchange accepts themes from anybody**, or only from societies
  running SocietyPress.
