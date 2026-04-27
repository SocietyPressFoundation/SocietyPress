# Newsletters

A PDF archive. Upload your back issues; SocietyPress generates cover thumbnails and shows them in a grid. Members browse, click, read.

## What you can do

- Upload past newsletters as PDFs.
- Auto-generate cover thumbnails from the first page of each PDF.
- Organize by year, by series ("Quarterly Journal," "Monthly Newsletter," "Annual Report").
- Restrict to members-only or make public.
- Bulk upload — drag a folder of PDFs once instead of one at a time.

## How to add your first newsletter

**SocietyPress → Newsletters → Add New.** Title (e.g., "March 2026 Quarterly"), date, optional description, upload the PDF. Save.

The cover thumbnail generates automatically — the first page of the PDF, rendered as a small image. If you want to override it (e.g., the first page is a plain table-of-contents and you'd prefer to use the second page), upload a custom cover image on the same edit page.

## How to bulk upload back issues

Got fifty back issues sitting in a folder? **SocietyPress → Newsletters → Bulk Upload.** Drag the folder into the dropzone. SocietyPress queues each file, generates titles from the filenames (e.g., `2025-Q1-Newsletter.pdf` becomes "2025 Q1 Newsletter"), and processes them.

Editable batch — review the auto-generated titles before final import. Set the publication date series-wide if it's not in the filenames.

## How to put the archive on a page

**Page builder:** drop the "Newsletter Archive" widget. Configure: per-page count, login-required toggle.

**Shortcode:** `[sp_newsletter_archive]`.

The archive shows the cover thumbnails in a grid, sorted newest-first, with the title below each. Click a thumbnail to open the PDF.

## How to control access

**SocietyPress → Settings → Newsletters → Members-only download** (toggle).

When on, non-members can see the grid and the cover thumbnails (so they know what they'd get if they joined) but clicking a thumbnail prompts a login. Members get the PDF directly.

Per-newsletter override is available — open any newsletter, set "Visibility" to public to make that single issue available to everyone (useful for sample issues used in marketing).

## If something looks wrong

**Cover thumbnail didn't generate.** PDF parsing depends on the host's available libraries. **SocietyPress → Newsletters** lists each newsletter; click "Regenerate cover" on the row. If it fails repeatedly, your host may not have the required PDF library — upload a custom cover image as a fallback.

**Bulk upload stalled.** Refresh and try again. Bulk upload processes files one at a time; if your PHP timeout is short (under 30 seconds), large batches can hit it. Either upload in smaller batches or ask your host to bump `max_execution_time`.

**Members can't download.** Verify they're logged in. The "Members-only download" setting requires a logged-in session. If a logged-in member still can't download, check their member status — lapsed members fail the access check.

## Related guides

- [Documents](documents.md) — for non-newsletter PDFs (bylaws, policies)
- [Page Builder](page-builder.md) — placement on pages
