# Lineage Programs

Recognition programs for members who can document descent from historically significant ancestors. "First Families of [your county]," "Pioneer Settlers," "Civil War Veterans Descendants," "Mayflower Descendants" — anything that takes proof of descent and grants a certificate.

## What you can do

- Define any number of programs (most societies offer one or two — this module supports as many as you want).
- Take applications online — applicant fills the form, uploads proofs, narrates the lineage.
- Charge an optional application fee via Stripe checkout.
- Review applications in an admin queue with status workflow (draft → submitted → in_review → info_requested → approved/rejected).
- Auto-generate certificate numbers (FORMAT: PROGRAM-YEAR-NNNN) on approval.
- Show approved members on a public roster (members opt in/out of public listing).
- Send status-change emails — applicants get notified when work moves forward.
- Generate printable certificates from a template (members print to PDF themselves — no PDF library required).

## How to set up your first program

**SocietyPress → Lineage Programs → Add Program.**

- **Name** — "First Families of Sample County County" or whatever you call it.
- **Slug** — URL-friendly identifier, auto-generated. Don't change it once you have applications.
- **Description** — public-facing summary shown on the program page.
- **Requirements** — what documentation must the applicant provide? Markdown supported.
- **Cutoff Year** — e.g., 1850. Applicant's ancestor must have been in the area by this year.
- **Geographic Scope** — e.g., "Sample County, Texas." Leave blank if not geographic.
- **Application Fee** — set to 0 for no fee. If > 0, applicant pays via Stripe before the application enters review.
- **Badge Label** — short label shown on approved members' profiles (e.g., "First Families").
- **Badge Color** — pick a hex color (the certificate uses it as the border).
- **Active** — controls visibility.
- **Accepting Applications** — separate toggle so you can pause new applications without deactivating the program.
- **Public Roster** — show approved members on a public roster page.

Save. The program is live.

## How members apply

You'll need an Apply page on your public site. Drop the "Lineage Application Form" widget (or `[sp_lineage_apply]` shortcode) on it.

The form walks the applicant through:

1. Pick a program (if you have multiple).
2. Their relationship to the ancestor ("great-great-grandfather").
3. Ancestor's name (first, middle, last, maiden).
4. Birth/death dates and places. Free-text dates accepted ("abt 1820" is fine).
5. Year the ancestor arrived in / settled in the area.
6. Evidence of arrival/residence (free text).
7. Lineage narrative — chain of descent from ancestor to applicant.
8. Sources cited.
9. File uploads — birth/marriage/death certificates, census pages, will excerpts. Multiple files per application.
10. Public-listing opt-in.

Two buttons at the bottom: **Save Draft** (comes back later to finish) and **Submit for Review** (starts the formal review).

If the program has an application fee, "Submit for Review" routes through Stripe Checkout. Once paid, the application moves to `submitted` status and staff get a notification.

## How to review an application

**SocietyPress → Lineage Applications.** The list shows all applications across all programs with filters (status, program, search by ancestor or applicant name). Click "Review" on a row.

The review page shows applicant info, ancestor detail (with a green checkmark if the arrival year meets the cutoff, red X otherwise), narrative, sources, attached proofs (clickable to open in a new tab).

The right sidebar is the decision form:

- **Status** — drop down through the workflow.
- **Notes for applicant** — visible to them, sent in the status-change email.
- **Internal notes** — staff-only, never visible to the applicant.

When you flip status to **Approved**, SocietyPress auto-generates a certificate number (e.g., `FIRST-FA-2026-0042`) and the approval-email goes out automatically with the number included.

When you flip to **Rejected** or **Info Requested**, the email goes out with your notes for the applicant.

## How members see their applications

Members see their own applications via the `[sp_lineage_my_applications apply_url="/apply/"]` shortcode (matching widget exists). The list shows status, ancestor name, certificate number when approved, and reviewer notes for `info_requested` / `rejected` cases.

The `apply_url` attribute points at your public Apply page so members can edit drafts and `info_requested` applications by clicking "Edit."

For each approved application, the member sees a **View / Print Certificate** link that opens the certificate page (`/?sp_certificate=NNNN`). The page is print-styled — landscape letter, generous borders, the program's badge color, applicant name in italic, ancestor in bold. Members hit File → Print → Save as PDF (or print on heavy paper for a real certificate).

## How to put the public roster on a page

Drop the "Lineage Roster" widget (or `[sp_lineage_roster]`). Configure:

- Specific program (filter to one program's roster) or blank (show all programs grouped).
- Show ancestor dates (default on).
- Show certificate numbers (default off — most societies don't display them publicly).

The roster shows ancestor name, lifespan, member name, sorted by surname. Each program section uses its badge color for the heading.

Members who opted out of public listing don't appear here, even if approved.

## How the certificate page works

Public URL: `/?sp_certificate=NNNN` (using the certificate number as the key). No login required — anyone with the number can view and print.

We chose this over a PDF library because (a) modern browsers all do "Save as PDF" via File → Print, (b) bundled PDF libraries are heavy and add a dependency we don't want, (c) the printable HTML page is more flexible — your CSS can be tweaked for fancier designs.

If you want to ship physical printed certificates instead of letting members print themselves, you can — open the certificate page on a staff machine and print to your nice printer with heavy paper.

## If something looks wrong

**Applicant says they can't submit.** First check that the program is active and accepting applications. If they're paying a fee and Stripe is misconfigured, they'd see an error after clicking Submit — verify your Stripe keys under **SocietyPress → Settings → Payments**.

**Stripe redirect lands on a "payment_error" page.** Open Stripe's dashboard → Developers → Logs and look at the most recent failed Checkout Session. The error message tells you what's wrong (most common: an old test-mode key that expired).

**The roster shows the wrong people.** The roster shows applications where status = approved AND public_listing = 1 AND the program's "Public Roster" toggle is on. If a member opted out, they don't appear. To force them on, edit their application and tick "List my approved ancestor publicly."

**Certificate number is missing.** Certificate numbers generate on the *first* approval. If you approved an application before the auto-generation logic was in place, edit the application and re-save with status=approved — the auto-gen runs again.

**A program needs to be deleted.** Programs with applications can't be deleted (they'd orphan the applications). Mark inactive instead. If you really need to remove a program with no applications, the admin Programs list has a Delete row action.

## Related guides

- [Members](members.md) — applicants link to member records when emails match
- [Donations](donations.md) — application fees use the same Stripe path
- [Documents](documents.md) — for storing the proof attachments long-term
