# Problem Reports

A "Report a Problem" button that files a fault from the page it happened on, with the page address, browser, screen size, theme and version numbers already attached. For the officer who notices something looks wrong and has no idea what a developer would need to know.

## Why it exists

What normally reaches a developer is an email that says *the events page looks funny on my iPad*. No address, no browser, no versions, no idea which of the four events pages — and by the time anyone asks, the officer has moved on and cannot remember.

Every one of those missing facts is known to the browser and the server at the moment the fault is noticed. So the report is filed from the page, and the context is collected rather than asked for.

This is for faults in the site and the software. It is not a moderation queue — member conduct is a different problem and does not belong here.

## How to file one

Look for **Report a Problem** in the toolbar. If your site has the toolbar switched off — SocietyPress hides it by default — it is a small flag button in the bottom-left corner of the page instead.

Click it from the page where the problem is. That matters: the form records the page you were on when you opened it.

Five questions, none of them technical:

- **What kind of problem is it?** It looks wrong / it does the wrong thing / something is out of date / I cannot get to something / something else.
- **In one line, what is wrong?**
- **What did you see?** Describe it the way you would to a colleague. If it only happens sometimes, say when.
- **What did you expect instead?** Optional, but it is often the most useful box on the form.
- **How much is it hurting?** Blocking / serious / normal / minor.

Send. That is it. You never have to describe your browser, your screen, or which version of anything you are running.

## What gets collected automatically

- The page address and title.
- Your browser, its language, your screen and window size.
- SocietyPress, WordPress and PHP versions.
- The active theme and its parent.
- Which modules are switched on.
- Server software, memory limit, whether debug mode is on, the site's timezone.
- Which admin screen you were on, if it was an admin screen.

No IP address. Your name and email come from your account, so we can come back to you.

## How to work through the reports

**SocietyPress → Settings → Problem Reports.** Opens on the open ones; blocking problems sit at the top regardless of age.

Click a report to see everything: what they wrote, where, the full diagnostics, and a panel down the right for status and notes.

Statuses are New → Triaged → Being worked on → Resolved, plus "Not a fault / won't fix" for the ones that turn out to be working as designed.

**Export CSV** takes the lot into a spreadsheet — useful when you are writing up a year of faults for a committee, or handing the site to a new webmaster.

## How to get email when one arrives

**Settings → AI Assistant → Problem Reports → Email me new reports.** Leave the address blank to use the site administrator's.

Worth switching on. The person who fixes things is rarely the person who reported, and nobody watches an admin screen they have no reason to open.

## How to let members report too

By default only officers and volunteers with admin access see the button. **Who can report** can widen that to any logged-in member.

Worth considering: members see the site the way members do, so they find faults officers never will. Reports always carry the account that filed them, so it is not anonymous.

## How to make a rambling report actionable

If you have the AI Assistant configured, each report has a **Summarise with AI** button. It turns the description into a one-line title, what seems to be happening, steps to reproduce, and where to look first — using the diagnostics, so it can spot that a report is really about a narrow screen when the reporter never mentioned their phone.

It never overwrites what the officer wrote. The summary appears alongside their words, so if it has misread the report, the evidence is right there.

It is a button rather than automatic, because there is no sense paying to summarise a report nobody has read yet.

## How to send a report upstream to GitHub

If the fault is in SocietyPress itself rather than your own content, you can push it to a repository as a real issue.

**Settings → AI Assistant → Send reports to GitHub.** Fill in `owner/repository` and a personal access token that can create issues. Then each report gets a **Send to GitHub** button.

Nothing goes automatically — most reports on a society site are about that society's own content, and somebody has to decide which ones are software faults. The issue carries the description, the triage notes if you generated them, and the diagnostics in a collapsible block. **The reporter's name and email are never included**, because that is a public tracker and they did not agree to be published. The report number lets you join it back up on your own site.

## If something looks wrong

**No button anywhere.** Check Settings → AI Assistant → Problem Reports → Reporting is ticked, and that your account has SocietyPress admin access (or set "Who can report" to any logged-in member). Reporting works whether or not the AI Assistant module is on.

**The button is there but the form will not send.** The summary and "what did you see" boxes are both required. Beyond that, a report filed from a page on a different site is rejected — the form only accepts pages on this site.

**"Send to GitHub" isn't offered.** It only appears when both a repository and a token are saved, and it disappears once a report has been pushed, so the same fault cannot be filed twice.

**GitHub refused the request.** Almost always the token: it needs permission to create issues in that repository, and fine-grained tokens expire. The exact message from GitHub is shown on the screen.

**A member asked for their data to be erased and they had filed reports.** The reports are kept, with their name and email removed. The fault they described is an organisational record — deleting it would destroy the history of a bug rather than the identity of a person.
