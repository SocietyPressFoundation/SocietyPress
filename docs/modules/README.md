# SocietyPress Module Guides

A guide per module, written for the volunteer who runs the website. No code, no jargon, no preamble. Pick the module you're trying to use and the guide tells you what it does, how to set it up, and what to do when something looks wrong.

## Where to start

If you just installed SocietyPress and the site is empty:

1. **[Setup Wizard](setup-wizard.md)** — the first ten minutes: your society's details, your colors, and your membership year.
2. **[Members](members.md)** — get your roster in. CSV import or one-by-one. This is the foundation; most other modules attach to members.
3. **[Child Themes](child-themes.md)** — pick a look. Fastest path to a site that doesn't feel default.
4. **[Page Builder](page-builder.md)** — build pages from ready-made widgets: events, the directory, a donate form and more.

## All modules

### Always on

- **[Members](members.md)** — roster, dues, renewals, member directory, household relationships.

### Toggleable (Settings → Features)

- **[Events](events.md)** — calendar, registration, speakers, recurring events.
- **[Library](library.md)** — catalog of books, periodicals, microfilm. OPAC-style search.
- **[Newsletters](newsletters.md)** — PDF archive with auto-generated cover thumbnails.
- **[Resources](resources.md)** — categorized directory of useful external links.
- **[Governance](governance.md)** — committees, officers, meeting minutes, volunteer hours.
- **[Voting](voting.md)** — ballots for board elections and bylaw amendments.
- **[Surveys](surveys.md)** — ask members questions, one answer each, anonymous if you want. Answers added up for you.
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
- **[Backup & Restore](backup-restore.md)** — the one-click export, scheduled backups, and moving to a new host.
- **[User Access & Roles](user-access.md)** — give volunteers different levels of access without giving everyone admin rights.
- **[Email Setup](email-setup.md)** — SMTP, transactional providers, SPF/DKIM, real cron — what to do once so renewal reminders and blast emails actually land.
- **[Privacy & GDPR](privacy-gdpr.md)** — encryption at rest, the export/erasure tools, processing requests, what the law actually requires.
- **[Glossary](glossary.md)** — words SocietyPress uses, defined in plain English. Look here when a term in another guide isn't obvious.
- **[Theme Presets](theme-presets.md)** — save your colors and fonts as a look, and share it.
- **[Child Themes](child-themes.md)** — Heritage, Coastline, Prairie, Ledger, Parlor — when to pick which.
- **[Page Builder](page-builder.md)** — stack ready-made widgets on a page, no code.

### For developers

- **[Developer Reference](developer-reference.md)** — shortcodes, filters, custom tables, capabilities and the encryption API, for webmasters extending SocietyPress.

## Style

These guides assume you're not technical. They assume you're busy. They assume the only reason you're reading is because something needs doing.

Each guide opens with one paragraph: what the module is, who it's for. Then 2-4 named recipes ("How to add your first event"). Then a short "If something looks wrong" section.

## Conventions

- "Member" = anyone in `sp_members`. They have a WordPress user account behind the scenes.
- "Visitor" = someone reading your site without logging in.
- "Admin" = the volunteer who manages the site (you, probably).
- "Society" = your organization.
- Paths start from the drop-down groups in the admin sidebar — **Members**, **Money**, **Events**, **Meetings & Board**, **Volunteers**, **Library**, **Research**, **Newsletters & Email**, **Surveys**, **Website**, **Reports** and **Settings**. An administrator can rearrange them under **Settings → Admin Sidebar**, so on your site a screen may live somewhere else.
