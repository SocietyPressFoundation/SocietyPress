# Help Requests

Public Q&A forum. The "duty librarian" model — anyone (member or visitor) posts a research question; volunteers answer when they can, out of comradery, not for a fee. The questions and answers stay searchable on your site, growing into a knowledge base over time.

This is the *free* path. The [Research Services](research-services.md) module is the optional paid escalation for cases that genuinely need many hours of focused work.

## What you can do

- Public submission — visitors fill out a form; a simple sum and an email check keep spam out.
- Members and staff answer on the question's page.
- Mark helpful answers with a star, so the best ones stand out.
- The asker (or staff) accepts one answer, which closes the thread, or marks it resolved.
- A public archive of open and resolved questions, with a search box that looks in titles, descriptions and tags.
- Spam controls — the sum, email verification, and a limit of three questions per email address a day.
- The asker is emailed when someone answers.

## How to put a Q&A page on your site

You'll typically want two pages — one to *ask* a question, one to *browse* existing questions.

**Ask page** (e.g., `/ask/`): drop the **Help Request Submission Form** widget, or put `[sp_help_request_submit]` on it. The form takes name, email, title, description and optional tags, and asks the sum.

**Archive page** (e.g., `/research-help/`): drop the **Help Requests Archive** widget, or put `[sp_help_requests_archive]` on it. It lists open questions first, then resolved ones, each with its tags, and has a search box.

A homepage button reading "Got a research question? Ask the community" that links to the ask page works well; the archive can sit off the main menu.

## How submissions actually flow

1. A visitor fills in the form, answers the sum ("what is 5 + 7?"), and submits.
2. SocietyPress emails them a link to confirm their address.
3. They click it, and the question goes live in the archive.
4. Members answer it on the question's page. The asker gets an email each time someone answers.
5. The asker (or staff) accepts the answer that solved it, which closes the thread, or marks it resolved.

## How to moderate

**SocietyPress → Research Help Requests** is the admin list. Filter it by status, or search it.

Bulk actions: tick rows and choose **Mark Resolved**, **Hide** or **Delete**. A notice at the top shows how many questions are still waiting for the asker to confirm their email.

**Help Tags**, in the same area, lets you tidy the tags people have used.

## How to control abuse

Each email address can submit three questions a day, and each internet address nine (three times as many, since a library or a household can share one). Neither number can be changed from the admin yet.

Email verification means a made-up or mistyped address can't post — the question never goes live until the link is clicked.

There's no setting yet to hold every question for a moderator before it goes live. To deal with something already posted, hide or delete it from the admin list.

## How to use tags

Tags are free text on the form: someone types "sample county, marriage records, 1850s" and SocietyPress keeps them. They show on each question in the archive, and the archive's search box looks in them, so searching "marriage records" finds questions tagged that way.

## If something looks wrong

**The form says "Bad request — please refresh."** The sum or the form's security token expired (each is good for 30 minutes). Refresh the page and try again.

**The verification email doesn't arrive.** Check your site can send email at all — "Lost your password?" on the sign-in page with your own address is the quickest test. If that never arrives, see [Email Setup](email-setup.md). Ask the visitor to look in their junk folder too.

**Spam is getting through.** Hide or delete the posts from the admin list. If it's a sustained attack from one place, block the source at your host's firewall.

## Related guides

- [Research Services](research-services.md) — paid escalation when free help isn't enough
- [Members](members.md) — non-members can ask, but members can endorse + accept answers
