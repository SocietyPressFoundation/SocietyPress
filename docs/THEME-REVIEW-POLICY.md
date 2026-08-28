# Theme Exchange — review policy

## What the Exchange is

Societies make their sites look like their own choosing, and then have no way to lend that work to the society three counties over that admires it. The Theme Exchange is that lending shelf.

It has three tiers, and they are three different levels of trust.

**Tier 1 — a saved look.**
Colors, fonts, spacing, sizes. A file of settings and nothing else. The worst a bad one does is make a site ugly, and one click puts it back. No review.

**Tier 2 — a bundle.**
A saved look plus a stylesheet and image files, in a `.sp childtheme` archive. SocietyPress strips anything from the stylesheet that is not styling and accepts only image files alongside it. A bundle cannot *do* anything; it can only look like something. No review.

**Tier 3 — a child theme.** A full WordPress theme. This document is about Tier 3 and only Tier 3.

All three tiers are open from the day the Exchange is. Tier 3's queue may be slow, and how slow is visible to everybody — but it is not closed, and was not held back until the rest had proved itself.

---

## Why Tier 3 is different

A WordPress theme contains executable code. It runs on the society's own server, with the site's own permissions, every time a page loads. A good one gives a society something genuinely its own. A bad one can read the member list.

That is the whole reason for a review. Nothing about a theme's appearance can be dangerous. Everything about its code can be.

---

## What the badge means, and what it does not

A theme that passes review carries **Reviewed by SocietyPress** where a society can see it before installing.

It means: a person read the theme's code, start to finish, at the version being offered, and found nothing that reaches outside making the site look a certain way.

It does not mean the theme is well built, that it will suit your society, that it will keep working after the next WordPress release, or that anybody is obliged to maintain it. It is a statement about safety and nothing else.

**Who reviews.** Today, we do, personally. There is no review board, and the policy will say so plainly rather than implying a committee that does not exist. If that changes, this section changes with it.

---

## What is accepted

A theme is accepted when ALL of the following are true.

1. **It is a child theme of the SocietyPress parent theme.**
Not a standalone theme, not a child of something else.

2. **It is licensed GPL-2.0-or-later**, like everything else here, and SAYS so.

3. **Its code does presentation and nothing else.**
Templates, styling, and the small amount of PHP it takes to arrange them.

4. **Every asset it ships is in the archive.**
No fonts, scripts, styles, or images fetched from another site at page load.

5. **It names a person or a society as its author**, with a working contact address.

6. **It installs on a clean site and works** without instructions beyond "activate it."

## What is denied

Any one of these ends the review. There is no partial pass.

**Anything that sends data anywhere.** No analytics, no phoning home, no telemetry, however anonymous, however well meant. 

**Anything that fetches code at runtime** — remote scripts, remote stylesheets, fonts loaded from a third party, an updater of its own. 

**Anything that reads what it has no business reading.** Member records, payment records, user accounts, settings unrelated to appearance.

**Anything that writes outside its own settings.** Creating pages, changing options, adding users, touching another plugin's data. 

**Obscured code.** Minified PHP, encoded strings, anything a reviewer cannot simply read. Not because it is necessarily malicious, but because it cannot be reviewed, and an unreviewable theme cannot carry a badge that says it was.

**Advertising.** No links, credits, or branding that a society cannot remove.

**Anything that alters SocietyPress's own behavior** rather than its appearance.

## What SocietyPress itself sends

A fair question, given the line above about themes: SocietyPress does phone
home, and a theme may not.

Once a week an install tells us three things — the society's name, its website
address, and which version it is running. Nothing else, and nothing about any
person. All three are already on the society's own public homepage. There is no
setting to turn it off, and the Privacy screen says so in those words.

We keep tabs on who is using our software. It is how we know which versions are
still out there and how far an announcement reaches.

A theme is held to a stricter rule than the plugin because the two are not in
the same position. A society chooses SocietyPress, is told plainly what it
sends, and can decline by not installing it. A theme is a passenger inside a
site that has already made that decision, and it arrives from somebody the
society has never heard of.

## What is asked for but not required

These do not decide acceptance. They are said out loud because they are what separates a theme somebody uses from a theme somebody tries once.

- Works on a phone.
- Readable contrast, and text that survives being enlarged.
- Sensible behavior when a society has no logo, no hero image, or no events.
- A screenshot that shows what it actually looks like.

## When a reviewed theme fails approval

1. **The badge is withdrawn immediately**, before anybody is contacted and before anything is explained. A badge in doubt is a badge removed.

2. **The theme is delisted** from the Exchange the same day.

3. **Societies running it are told**, by name, what was found and what to do. Not a changelog line — a message that reaches the person who installed it.

4. **What was found is published**, including the fact that it passed review. A review process that hides its misses is worth less than no review at all.

5. **The author may submit again**, unless what was found looks deliberate.

A society is never left with a theme it cannot get out of: switching back to a SocietyPress theme is one click, and no theme in the Exchange is permitted to own anything a society would lose by leaving it.

## Who may submit

Anybody.

Not only societies, not only members, not only people we know. SocietyPress is GPL, which means anyone may already write a child theme and hand it straight to a society without asking us. The Exchange has no authority over that and never will. What it governs is only which themes carry the badge — so restricting who may submit would not keep one bad theme away from one society. It would only mean fewer good ones were ever read.

So the question is not who is permitted to write themes. The license settled that. The question is whose work SocietyPress will put its name on, and the answer is: anyone willing to be named alongside it.

**A submission names a person.** A working contact address, a real name or a real society, published with the theme. Anonymous submissions are refused without review. This is the whole gate, and it is enough of one: a theme asks a society to run its code on the server that holds the members' names, addresses, payment history, and family records about living people. Somebody has to be answerable for that, and it cannot be us.

**One open submission per author at a time.** A second is accepted when the first is decided. This is not a judgment about anyone's work — it is the only way one reviewer and an open door can coexist.

**A review may be declined without a reason.** This is not a rejection and is not recorded as one. A rejection means a theme failed a line in this document, and we say which line. A declined review means only that we are not able to take it on. The distinction matters because the alternative is promising to read anything anyone ever sends, forever, and that is a promise that would be broken quietly rather than kept.

---

## Submitting

A society submits a `.zip` file and the contact details of a person. It joins a queue that is public — everybody can see what is waiting, how long it has waited, and what was decided.

The review is a checklist drawn from this document, and the completed checklist is published with the decision.

A rejection says which line it failed and what would fix it.

**How long does a review take?**
There is no promise. A theme is reviewed when someone is able to review it. The queue shows how long everything in it has been waiting, so nobody has to ask, and repeated requests about timing will be ignored.

**What happens to a theme whose author disappears?**
It is unlisted at the next WordPress release that breaks it, once SocietyPress is notified.

**Is a re-review required on every update?**
Only when the code changes.
