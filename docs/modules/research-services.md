# Research Services

Optional paid research-case workflow. Comradery via [Help Requests](help-requests.md) is the default — most quick questions get free volunteer help. Research Services is the opt-in escalation for cases that genuinely need many hours of focused work: deep ancestor traces, full record-set transcriptions, professional-grade reports.

This module is **off by default**. Enable it under **SocietyPress → Settings → Modules** when you want it.

## What you can do

- Take paid research requests via a public intake form with up-front Stripe billing.
- Set your default rate and minimum hours (default $30/hour, 1 hour authorized).
- Researchers (members you flag as such) claim cases from a queue, log hours as they work.
- Bill additional hours mid-case — researcher requests, requester authorizes via Stripe, case continues.
- In-system messaging between requester and researcher with file attachments.
- Status-change emails fire on every transition (claimed, in_progress, needs_more_hours, completed).
- Convert a free Help Request thread into a paid case when it turns out the work is too big.
- All hours flow into the unified volunteer-hours ledger — paid hours are still logged hours.

## How a case flows

1. Visitor fills the intake form on a page hosting `[sp_research_request]`.
2. Submission creates the case in `pending_payment` status. Visitor is redirected to Stripe Checkout for the up-front fee (rate × max_hours_authorized).
3. On payment success, case flips to `open` and lands in the admin queue. Configured admin email is notified.
4. A researcher claims the case (either an admin assigns from the case-edit page, or a researcher self-claims from the dashboard). Status flips to `claimed`. Requester is emailed.
5. Researcher logs the first hour of work. Status auto-flips to `in_progress`. Requester is emailed.
6. Researcher works the case, communicates via in-system messages, logs hours as they go.
7. **If more time is needed:** researcher uses the "Request Additional Hours" form on the admin case page. SocietyPress creates a pending invoice, emails the requester a Stripe payment link, sets status to `needs_more_hours`. When the requester pays, the invoice marks paid, the case's `max_hours_authorized` increases, status returns to `in_progress`.
8. Researcher delivers results (typically as a message with a PDF report attached). Marks status `completed`. Requester is emailed.

## How to enable the module

**SocietyPress → Settings → Modules → Paid Research Services → On.** The new admin menu item (SocietyPress → Research Cases) appears.

Configure defaults under **SocietyPress → Settings**:

- **Default hourly rate** — what you charge per hour. Default 30.00.
- **Default hours authorized** — how many hours the up-front fee covers. Default 1.0 (1 hour).
- **SLA days** — initial response time to set on every new case. Default 14.

These are starting points. Individual cases can override the rate and hours from the admin case-edit page.

## How to put the intake form on a page

**Page builder:** drop the "Research Case Request" widget. **Shortcode:** `[sp_research_request]`.

The form has fields for case title, description, surname, ancestor name, time period, location, and "what have you already tried?" (optional). At the bottom is a quote box that shows the up-front cost calculated from your defaults.

When the visitor submits, they're sent to Stripe Checkout. On success, they're returned to the form page with a "Payment received — your case is in the queue" message.

## How to set up researchers

Researchers are members you've flagged as such. There's no separate "researcher role" — any member can be flagged.

(Today the flag is configured manually by an admin in the member edit page. A self-service researcher signup flow with skill profiles is on the future roadmap.)

Flagged researchers see two things:

1. The "My Research Assignments" dashboard (via `[sp_my_research_assignments]` shortcode) showing their active cases plus open cases available to claim.
2. Inline log-hours forms on each active case so they can record time as they work without leaving the page.

## How to manage cases

**SocietyPress → Research Cases.** List filters by status (Pending Payment / Open / Claimed / In Progress / Needs More Hours / Completed / Cancelled / Refunded) plus search by title, requester, or surname.

Click "Review" on a row to open the single-case view. The page shows requester info, the full case description, hours logged so far (with totals), invoices issued, and a sidebar with status workflow + researcher assignment + rate override + SLA override + admin notes.

Two postboxes auto-inject below the main case detail:

- **Hours Logged** — a table of every hour entry plus an inline form to log more.
- **Request Additional Hours** — when the case needs more time, fill this out (hours + reason). It creates a pending invoice, emails the requester a payment link, sets status to `needs_more_hours`.

## How members track their cases

Members see their cases via `[sp_my_research_cases]` (matching page-builder widget). Each card shows status, researcher (if assigned), paid amount, hours authorized, and SLA.

If there's a pending invoice for additional hours, the card shows it inline as a yellow callout with an "Authorize & Pay" button. Clicking the button creates a Stripe Checkout for that invoice; on payment success, hours bump and the case continues.

## How conversion-from-Help-Request works

A Help Request thread sometimes reveals "this is too much for free help." On the admin Help Request view page, when the Research Services module is enabled, a button appears: **↑ Convert to paid Research Case**.

Clicking it:

1. Creates a new Research Case pre-filled with the Help Request's title, description, and requester contact info.
2. Status starts at `pending_payment` so the requester can authorize the fee.
3. Drops a system response into the original Help Request thread noting the escalation.
4. Emails the requester explaining their question has been escalated, with a link to authorize payment.

The original Help Request stays — only the conversation is preserved. If the requester pays, the new case enters the queue. If they don't, nothing happens to either record.

## How messaging works

Both admin and member case views render a chat-style thread of messages. Reply form below accepts text + an optional file attachment (image / PDF / DOC / DOCX / TXT).

Sending a message emails the *other* party — researcher when the requester wrote, requester when staff/researcher wrote. Falls back to your configured admin email when no researcher has claimed yet.

The thread is private to the requester, the assigned researcher, and staff. Other members can't see it.

## If something looks wrong

**Intake form says "stripe_unconfigured."** Drop your Stripe credentials in **SocietyPress → Settings → Payments**.

**Case is stuck in `pending_payment` after the requester says they paid.** Stripe success URL didn't get hit (closed tab, browser interrupted). Look at the Stripe dashboard → Payments to verify the charge succeeded; if it did, manually flip the case status to `open` and update `paid_amount` in the admin.

**Requester didn't get the additional-hours email.** **SocietyPress → Settings → Email → Send test email** to confirm outbound mail. If mail works, look at the case's invoice list — the invoice should have been created. The email goes to the requester's email; verify it's correct on the case.

**Researcher can't see open cases.** Check that they're logged in and on a page hosting `[sp_my_research_assignments]`. The dashboard uses the logged-in user's ID to find claimed cases; if the page is cached, it might show stale data.

**A researcher claimed a case but isn't doing the work.** Open the case admin, change `claimed_by_user_id` (Researcher dropdown in the sidebar) to assign someone else. The original researcher loses access; the new one is notified.

## Related guides

- [Help Requests](help-requests.md) — the free comradery counterpart
- [Donations](donations.md) — same Stripe Checkout path is reused
- [Members](members.md) — researchers are members; volunteer hours are member-attributed
- [Governance](governance.md) — research hours feed the same volunteer-hours ledger
