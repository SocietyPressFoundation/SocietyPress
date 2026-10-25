# Lineage Programs

Recognition programs for members who can document descent from historically significant ancestors. "First Families of [your county]," "Pioneer Settlers," "Civil War Veterans Descendants," "Mayflower Descendants" — anything that takes proof of descent and grants a certificate.

## What you can do

- Offer any number of programs.
- Take applications online — the applicant signs in, describes the ancestor and the line of descent, and uploads proof documents.
- Charge an optional application fee through Stripe.
- Review applications in an admin queue: Submitted → In Review → Info Requested → Approved or Rejected.
- Give each approved application a certificate number automatically.
- Show approved members on a public roster, if they agreed to be listed.
- Email applicants when their application moves forward.
- Let members print their own certificate from a print-ready page.

## How to set up your first program

**SocietyPress → Lineage Programs**, then fill in the form on the left:

- **Name** — "First Families of Sample County" or whatever you call it.
- **URL Name** — filled in from the name if left blank. It also starts every certificate number, so don't change it once you've approved anyone.
- **Description** — a short summary, shown to applicants on the application form.
- **Requirements** — the documentation you need from applicants, also shown on the form.
- **Cutoff Year** — e.g. 1850; the ancestor must have been in the area by this year. Reviewers see whether each application meets it.
- **Geographic Scope** — e.g. "Sample County, Texas". Leave blank if not geographic.
- **Application Fee** — 0 for no fee.
- **Badge Label** — a short label shown with members' approved applications (e.g. "First Families").
- **Badge Color** — use a dark color; it carries white text. It's also the certificate's border and the program's heading on the roster.
- **Options** — **Active**, **Accepting new applications** (pause applications without hiding the program), and **Show approved members on a public roster**.
- **Sort Order** — lower numbers list first.

Click **Add Program**.

## How members apply

Make an Apply page with the **Lineage Application Form** widget, or `[sp_lineage_apply]`. Applicants must sign in.

The form asks for:

1. The program. Under the list, **About [program]** opens its description, requirements, cutoff year and area.
2. Their relationship to the ancestor ("great-great-grandfather").
3. The ancestor's name — first, middle, last, maiden.
4. Birth and death dates and places. Any format is fine ("abt 1820").
5. The year the ancestor arrived in or settled in the area, and the evidence for it.
6. The lineage narrative — the chain of descent from the ancestor to them.
7. Sources cited.
8. A proof document with a label ("1850 census page"). One document per save; they add more by editing the application.
9. Whether to list their approved ancestor publicly on the program roster.

**Save Draft** keeps it to finish later. **Submit for Review** sends it in.

If the program has a fee, **Submit for Review** goes on to Stripe Checkout, and the application is marked paid when Stripe confirms. If Stripe isn't set up, the application is still submitted and marked as payment due, so you can collect the fee another way.

## How to review an application

**SocietyPress → Lineage Applications.** The list shows all applications across all programs with filters (status, program, search by ancestor or applicant name). Click "Review" on a row.

The review page shows applicant info, ancestor detail (with a green checkmark if the arrival year meets the cutoff, red X otherwise), narrative, sources, attached proofs (clickable to open in a new tab).

Under **Review Decision**:

- **Status** — drop down through the workflow.
- **Notes for applicant** — visible to them, sent in the status-change email.
- **Internal notes (staff only)** — never shown to the applicant.
- **List on the public roster once approved** — starts as the applicant chose; change it if they ask.

Click **Save Review**.

When you set the status to **Approved** and save, SocietyPress gives the application a certificate number — the first eight letters of the program's URL Name, the year, and a running number, such as `FIRST-FA-2026-0042` — and the approval email goes out with the number in it.

When you flip to **Rejected** or **Info Requested**, the email goes out with your notes for the applicant.

## How members see their applications

Members see their own applications on a page with the **My Lineage Applications** widget, or `[sp_lineage_my_applications apply_url="/apply/"]`. The list shows each application's status, ancestor, certificate number once approved, and your notes when you've asked for more information or not approved it. Members can withdraw an application they no longer want reviewed.

The `apply_url` setting points at your Apply page, so members can click **Edit** on a draft, or on an application you've asked for more information about.

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

**Applicant says they can't submit.** First check that the program is active and accepting applications. If they're paying a fee and Stripe is misconfigured, they'd see an error after clicking Submit — verify your Stripe keys under **SocietyPress → Settings → Events → Online Payments**.

**Stripe redirect lands on a "payment_error" page.** Open Stripe's dashboard → Developers → Logs and look at the most recent failed Checkout Session. The error message tells you what's wrong (most common: an old test-mode key that expired).

**The roster shows the wrong people.** The roster lists approved applications whose applicant ticked "List my approved ancestor publicly", for programs with **Show approved members on a public roster** on. The member makes that choice on the application. If they ask to be added or removed later, open the application and change **List on the public roster once approved**, then **Save Review**.

**Certificate number is missing.** Open the application and click **Save Review** with the status still Approved; a number is given to any approved application that lacks one.

**A program needs to be deleted.** Programs with applications can't be deleted (they'd orphan the applications). Mark inactive instead. A program with no applications can be deleted from the Programs list.

## Related guides

- [Members](members.md) — applicants link to member records when emails match
- [Donations](donations.md) — application fees use the same Stripe path
- [Documents](documents.md) — for storing the proof attachments long-term
