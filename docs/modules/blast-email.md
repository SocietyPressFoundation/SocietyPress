# Blast Email

Send one email to all your members, or to a group of them. SocietyPress sends it in small batches in the background, leaves out members who've opted out, and keeps a count of what was sent and what failed.

## What you can do

- Write an email with formatting, pictures and attachments — a flyer, the newsletter PDF.
- Send it to all active members, a group (a committee, say), your mailing-list subscribers, members whose renewal is due, recent joiners, or members with a birthday this month — and narrow any of those to particular membership plans.
- Personalize it with each member's name.
- Send under a committee's name or address instead of the society's.
- Keep a draft and come back to it.

## How to send your first email

**Newsletters & Email → Send Email → Compose New.**

- **Subject** — required.
- **Recipients** — All Active Members, By Group, Mailing list subscribers, Renewal due soon, Recently joined, or Birthdays this month. Each shows how many people it reaches. Underneath, tick one or more membership plans to send only to members on those plans.
- **Email Body** — the editor, with merge tags (below) and the Add Media button for pictures.
- **Attachments** — optional files sent with every copy. Keep them small; large attachments slow delivery and some hosts refuse them.
- **Send as** — optional sender name and From address, such as "Membership Committee". Leave blank to send under your society's name. Use an address at your own website's domain; a Gmail or Yahoo address as the From often lands in junk.

Click **Send Now**, or **Save Draft** to finish later. There's no scheduling — an email goes when you click Send Now. It's sent in batches of 50 a few seconds apart, so a large list takes a few minutes.

## How merge tags work

Type these into the body and each recipient sees their own:

- `{{first_name}}` — first name
- `{{last_name}}` — last name
- `{{display_name}}` — full name as shown on the site
- `{{organization_name}}` — your society's name

## How opt-outs work

A member who ticks "Do not send me any email" in My Account (or whom you mark that way on their record, under Preferences) is left out of every email automatically. Mailing-list subscribers get a one-click unsubscribe link at the bottom of each email.

For a notice everyone must get — an emergency, a quorum call — tick **Send even to members who opted out**. Use it sparingly.

## How to see what happened

**Newsletters & Email → Send Email** lists every email with its status and how many were sent and how many failed. A failure means WordPress couldn't hand that copy to your mail server; it doesn't tell you about mail that was accepted and later bounced or sorted into junk.

**Email Templates**, in the same menu, is something else: the wording of the emails SocietyPress sends on its own — welcome, renewal reminders, event confirmations.

## If something looks wrong

**Fewer people received it than expected.** Opted-out members are left out, and "All Active Members" leaves out lapsed and inactive ones. A member with no email address on file can't receive anything. The count beside each Recipients choice shows the real number before you send.

**Emails not arriving at all.** Check your site can send email — "Lost your password?" on the sign-in page with your own address is the quickest test. If that never arrives, see [Email Setup](email-setup.md). Some hosts also limit how much mail a site can send in an hour.

**Someone got an email they shouldn't have.** Check their record: their status, plan and groups decide which audiences they're in. To reach a precise set of people, put them in a group and send **By Group**.

**Members complaining about too many emails.** Send less, and use the narrower audiences — a group, or particular plans — rather than All Active Members for everything.

## Related guides

- [Members](members.md) — audiences are defined by member attributes
- [Newsletters](newsletters.md) — new-issue announcement is a common blast use case
- [Events](events.md) — event reminders are another common use case
