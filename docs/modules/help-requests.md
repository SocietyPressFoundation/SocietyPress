# Help Requests

Public Q&A forum. The "duty librarian" model — anyone (member or visitor) posts a research question; volunteers answer when they can, out of comradery, not for a fee. The questions and answers stay searchable on your site, growing into a knowledge base over time.

This is the *free* path. The [Research Services](research-services.md) module is the optional paid escalation for cases that genuinely need many hours of focused work.

## What you can do

- Public submission — visitors fill out a form, math captcha + email verification keep spam out.
- Members and staff respond. Each response logs the time it took to write — those minutes flow into your unified volunteer-hours ledger automatically.
- Endorse helpful answers ("★ Helpful") so the best responses surface.
- Asker (or staff) marks a thread Resolved or accepts a specific response as the answer.
- Public archive of every resolved thread, searchable, filterable by tag.
- Spam controls — captcha, email verification, per-email rate limit, optional moderation queue.
- Email lifecycle — askers get notified when someone responds.

## How to put a Q&A page on your site

You'll typically want two pages — one to *ask* a question, one to *browse* existing questions.

**Ask page** (e.g., `/ask/`): drop the "Help Request Submission Form" widget OR `[sp_help_request_submit]` shortcode. The form captures name, email, title, description, optional tags, and includes the captcha.

**Archive page** (e.g., `/research-help/`): drop the "Help Requests Archive" widget OR `[sp_help_requests_archive]`. The archive shows open + resolved threads with their tags, filters by tag pills, paginates.

A good homepage CTA pattern: "Got a research question? **Ask the community →**" linking to the ask page. The archive lives off the main nav.

## How submissions actually flow

1. Visitor fills the form, answers the captcha (a simple "what is 5 + 7?"), submits.
2. SocietyPress creates the request in `pending_verification` status and emails the visitor a verification link.
3. Visitor clicks the link. If you've left the moderator-approval setting off (default), the request transitions to `open` and goes live on the archive immediately. If approval is on, it transitions to `pending_review` and a staff member publishes it.
4. Members see the open request, respond, log the time it took. The asker gets an email each time someone responds.
5. The asker (or staff) marks the question resolved. Optionally the asker accepts a specific response as the answer.

## How responses log volunteer hours

Every response asks "How long did this take?" with quick options (5 min / 15 min / 30 min / 1 hour / custom). The default is 15 minutes; one click submits the response with the time. The hours flow into `sp_volunteer_hours` automatically — same ledger as committee work, library duty, event volunteering.

This is deliberate: every helping action produces a logged hour. At the end of the year, your society can report "our members donated 2,400 volunteer hours" and the data is real, not estimated.

For aspiring Certified Genealogist (CG) candidates, this builds a portable track record — "47 questions answered, 41 marked helpful, 32 hours logged" appears on their member profile.

## How to moderate

**SocietyPress → Research Help Requests** is the admin queue. The list filters by status (All / Open / Pending Review / Resolved / Closed / Hidden) and includes a search box.

Bulk actions: select rows, pick **Approve** (publishes pending_review), **Mark Resolved**, **Hide**, or **Delete** from the dropdown.

The page also shows a notice at the top whenever there are pending submissions waiting for verification or approval, so you don't have to remember to check.

## How to control abuse

Per-email rate limit (default 3 submissions per email per 24h, configurable in Settings). Per-IP rate limit (default 9 per IP per 24h — three times the email cap, since multiple legitimate users can share an IP).

Email verification means you can't spam from a typoed/throwaway email — the request never goes live until the verification link is clicked.

If you want all public submissions reviewed by a moderator before going live, turn on **SocietyPress → Settings → Help Requests → Require Approval**. Public submissions land in `pending_review`; moderator clicks Approve to publish.

## How to use tags

Tags are free-text on the submission form. Visitors enter "sample county, marriage records, 1850s" and SocietyPress stores them.

The archive shows the top-12 most-used tags as filter pills above the thread list. Clicking a pill filters the archive to threads with that tag. Combined with the search box, this turns the archive into a navigable knowledge base instead of a chronological dump.

## If something looks wrong

**The form says "Bad request — please refresh."** The captcha or nonce expired (each captcha is good for 30 minutes). Just refresh the page and try again.

**Verification email doesn't arrive.** **SocietyPress → Settings → Email → Send test email** to confirm outbound mail works. If it doesn't, switch to SMTP under the same Settings page.

**Resolved threads don't show up.** Check the archive's status filter. The shortcode shows open + resolved by default — if you've narrowed it via URL params, broaden again.

**Spam is getting through.** First, lower the per-email rate limit to 1/day. Second, turn on the moderator-approval setting so every request gets a human gate. Third, consider blocking the source IP at your host's firewall if it's a sustained attack.

**Volunteer hours aren't logging.** Each response form has a required time-entry picker. If members are using the admin-side reply form (not the front-end member-facing one), the time-entry isn't there — they should be replying from the public thread page, not the WP admin.

## Related guides

- [Research Services](research-services.md) — paid escalation when free help isn't enough
- [Members](members.md) — non-members can ask, but members can endorse + accept answers
- [Governance](governance.md) — volunteer hours feed into the same ledger
