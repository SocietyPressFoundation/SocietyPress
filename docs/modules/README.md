# SocietyPress — Harold's Module Guides

A guide per module, written for the volunteer who runs the website. No code, no jargon, no preamble. Pick the module you're trying to use and the guide tells you what it does, how to set it up, and what to do when something looks wrong.

## Where to start

If you just installed SocietyPress and the site is empty:

1. **[Setup Wizard](setup-wizard.md)** — first 10 minutes. Tells SocietyPress your society's name, picks default fonts and colors, sets up your first membership tier.
2. **[Members](members.md)** — get your roster in. CSV import or one-by-one. This is the foundation; most other modules attach to members.
3. **[Theme Presets](theme-presets.md)** — pick a look. Fastest path to a site that doesn't feel default.
4. **[Page Builder](page-builder.md)** — drop modules into pages. Every shortcode listed in these guides has a matching widget you can drag.

## All modules

### Always on

- **[Members](members.md)** — roster, dues, renewals, member directory, household relationships.

### Toggleable (Settings → Modules)

- **[Events](events.md)** — calendar, registration, speakers, recurring events.
- **[Library](library.md)** — catalog of books, periodicals, microfilm. OPAC-style search.
- **[Newsletters](newsletters.md)** — PDF archive with auto-generated cover thumbnails.
- **[Resources](resources.md)** — categorized directory of useful external links.
- **[Governance](governance.md)** — committees, officers, meeting minutes, volunteer hours.
- **[Voting](voting.md)** — ballots for board elections and bylaw amendments.
- **[Store](store.md)** — sell publications and merchandise. Stripe + PayPal checkout.
- **[Donations](donations.md)** — campaigns, online giving, recurring donations, receipts.
- **[Records](records.md)** — searchable databases for cemetery transcriptions, census data, etc.
- **[Help Requests](help-requests.md)** — public Q&A forum. The "duty librarian" model.
- **[Research Services](research-services.md)** — paid research-case workflow, opt-in.
- **[Lineage Programs](lineage-programs.md)** — First Families, Pioneer Settlers, etc. Application + roster + certificate.
- **[Gallery](gallery.md)** — photo albums for events. Member-submitted Picture Walls.
- **[Documents](documents.md)** — upload bylaws, policies, minutes. Per-document access control.
- **[Blast Email](blast-email.md)** — send to all members or specific groups. Delivery tracking.

### Cross-cutting

- **[Installer](installer.md)** — the one-click `sp-installer.php` flow.
- **[Setup Wizard](setup-wizard.md)** — the first-run configuration flow.
- **[Insights](insights.md)** — engagement and use numbers across every module, on one admin/board-only page.
- **[Backup & Restore](backup-restore.md)** — one-click full-site export and the recipe for restoring it.
- **[User Access & Roles](user-access.md)** — give volunteers different levels of access without giving everyone admin rights.
- **[Email Setup](email-setup.md)** — SMTP, transactional providers, SPF/DKIM, real cron — what to do once so renewal reminders and blast emails actually land.
- **[Privacy & GDPR](privacy-gdpr.md)** — encryption at rest, the export/erasure tools, processing requests, what the law actually requires.
- **[Glossary](glossary.md)** — words SocietyPress uses, defined in plain English. Look here when a term in another guide isn't obvious.
- **[Theme Presets](theme-presets.md)** — export your look as JSON, import others'.
- **[Child Themes](child-themes.md)** — Heritage, Coastline, Prairie, Ledger, Parlor — when to pick which.
- **[Page Builder](page-builder.md)** — drag widgets into columns, no code.

### For developers

- **[Developer Reference](developer-reference.md)** — shortcodes, filters, AJAX endpoints, REST routes, custom tables, capabilities, encryption API. For technically-inclined webmasters extending the platform.

## Style

These guides assume you're not technical. They assume you're busy. They assume the only reason you're reading is because something needs doing.

Each guide opens with one paragraph: what the module is, who it's for. Then 2-4 named recipes ("How to add your first event"). Then a short "If something looks wrong" section.

Every screenshot is a placeholder until we have a clean demo to capture from. If a guide says `[screenshot:...]`, that's a note to add the picture later.

## Conventions

- "Member" = anyone in `sp_members`. They have a WordPress user account behind the scenes.
- "Visitor" = someone reading your site without logging in.
- "Admin" = the volunteer who manages the site (you, probably).
- "Society" = your organization.
- All paths in the SocietyPress admin start at **SocietyPress → ...** in the left sidebar.
