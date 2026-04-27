# Documents

Upload and organize your society's documents — bylaws, policies, board minutes, financial reports, anything that isn't a newsletter or library item. Per-document access control (public / members-only / staff-only).

## What you can do

- Upload PDFs, Word docs, Excel files, anything WordPress media library accepts.
- Organize by category (Bylaws / Financial / Meeting Minutes / Policies / Member Resources).
- Per-document access — public, members-only, or staff-only.
- Version tracking — replace a document with a newer version; the old one is archived.
- Bulk upload — drag a whole folder.
- Search across documents (full-text title + description).
- Display on a public page with category filtering.

## How to add your first document

**SocietyPress → Documents → Add New.** Title, description, file upload. Pick a category. Set visibility (Public / Members / Staff). Save.

For sensitive documents (board financials, executive session minutes), set visibility to Members or Staff. The document is filed in the WordPress media library, but the access check happens before the file URL is served.

## How to organize categories

**SocietyPress → Documents → Categories.** Add categories in the order you want them to appear. Suggested starter set:

- Bylaws
- Articles of Incorporation
- Financial Reports
- Board Meeting Minutes
- Membership Meeting Minutes
- Committee Documents
- Policies
- Member Resources

Each category can have its own default visibility (so all "Board Meeting Minutes" default to Members-only, all "Member Resources" default to public). You override per-document on the edit page.

## How to put a document library on a page

**Page builder:** drop the "Documents" widget. **Shortcode:** `[sp_documents]`. The display shows a list grouped by category, with title, description, file type icon (PDF, DOC, XLS, etc.), and a Download button.

Members see only documents they have access to. Non-members see only public documents. Staff see everything.

Filter the widget to a specific category if you want a "Bylaws" page that only shows the bylaws.

## How to bulk upload

**SocietyPress → Documents → Bulk Upload.** Drag a folder; SocietyPress queues each file, generates a title from the filename, and sets the default category for the batch. Edit titles + descriptions individually after upload.

For large batches (50+), upload in groups to avoid hitting your host's PHP timeout.

## How to version a document

When you have a new version of a document already on file:

**SocietyPress → Documents → [doc] → Edit.** Click "Replace File." Upload the new version. The old file is archived (not deleted) and the document's URL now serves the new file.

Members downloading the document always get the current version. Old versions are accessible to staff via the document's revision history.

## If something looks wrong

**A member can't download a document they should be able to.** Check their member status. The members-only access check requires `status = active`. Lapsed or expired members fail it.

**A public document is being asked for login.** Two things to check: (1) the document's visibility is set to Public (not Members), (2) the download URL doesn't have a stale cookie value. Try in an incognito window.

**Bulk upload stalled.** Refresh and try again with a smaller batch. PHP timeouts kill long-running uploads.

**Document download serves the wrong file.** If you replaced a file with a new version, the WP cache or browser cache may be serving the old one. Cmd+Shift+R / Ctrl+Shift+R to force-refresh; clear the Yoast / W3 / your-caching-plugin cache if you have one.

## Related guides

- [Newsletters](newsletters.md) — for newsletter PDFs specifically
- [Library](library.md) — for cataloged book holdings
- [Lineage Programs](lineage-programs.md) — proof attachments live in the media library too
