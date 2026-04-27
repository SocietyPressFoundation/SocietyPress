# Gallery

Photo albums for your society's events, plus member-submitted "Picture Wall" galleries (ancestor portraits, member-submitted heritage photos).

## What you can do

- Create photo albums with titles, descriptions, captions per photo.
- Two album types: **Curated** (admin uploads) and **Submissions** (members submit, admin approves).
- Cover photo per album, sortable order, public / members-only visibility.
- Drag-to-reorder photos within an album.
- Bulk upload — drop multiple files at once.
- Picture Wall — special submission-album type for ancestor portraits with name, relationship, and submitter credit.

## How to make a curated album

**SocietyPress → Photo Gallery → Add Album.** Title, description, optional event association, visibility. Save.

Then upload photos. Drag files into the album's edit page; SocietyPress processes them via WP Media Library and adds them to the album. Set captions per photo. Drag to reorder.

Cover photo: pick from the uploaded photos, or upload separately. The album list view uses the cover photo as the album's thumbnail.

## How to make a Picture Wall

A Picture Wall is a member-submission album. Members upload an ancestor portrait + name + relationship + brief note. Staff approves before it appears publicly.

**SocietyPress → Photo Gallery → Add Album.** Title (e.g., "Ancestor Portraits"), description. **Submission Type** = Submissions. **Accepts Submissions** = on. **Submission Instructions** = the guidance shown to members on the upload form.

To put the submission form on a page: drop the "Picture Wall — Submission Form" widget OR `[sp_picture_wall_submit album="ancestor-portraits"]`. Members fill in the form, upload, submit. Status starts at `pending`.

To display the approved photos: drop the "Picture Wall" widget OR `[sp_picture_wall album="ancestor-portraits"]`. Configure columns (default 4) and whether to show submitter credits.

## How to moderate Picture Wall submissions

**SocietyPress → Picture Wall Pending.** Each pending submission shows the photo thumbnail, ancestor name, relationship, caption, submitter, date.

Two buttons per submission:

- **Approve** — submission goes live in the public Picture Wall. Submitter is emailed.
- **Reject** — submission is hidden. Submitter is emailed (with a polite "thank you for the submission, this one wasn't approved" note).

A pending-submissions banner appears on every SocietyPress admin page when items are awaiting review, so you don't have to remember to check.

## How to display albums

**Page builder:** drop the "Photo Gallery" widget. Pick which album(s). **Shortcode:** `[sp_album id="..."]` or `[sp_album slug="annual-banquet-2025"]`.

The display is a responsive grid (4-col → 2-col → 1-col on mobile). Click any photo to open a lightbox with caption.

For a master "all albums" page, the gallery widget without an album ID shows the album list (each album as a thumbnail card). Click a card to open the album.

## If something looks wrong

**Photos didn't upload.** WordPress's max upload size (set in php.ini) might be smaller than the photo file. Check **SocietyPress → Settings → System → Max upload size**. If a photo is bigger, resize it (most genealogy photos can be ≤ 2MB without losing detail) or ask your host to bump the upload limit.

**A member's submission isn't appearing in Pending.** Check that the submission album has `submission_type = 'submissions'` AND `accepts_submissions = 1`. Both flags must be on for submissions to land. Toggle them on the album edit page.

**Submission form rejects images.** The form accepts JPG and PNG by default (image/* mime type). HEIC files from iPhones get rejected — submitter should convert to JPG first (their iPhone's Photos app can do this; share → "Save as JPG" or via Files).

**Lightbox isn't opening.** Browser cache. Hard-refresh. If it still doesn't work, check the browser console for a JS error and report it.

## Related guides

- [Events](events.md) — albums can be associated with events
- [Members](members.md) — submitter credits link to member records when emails match
- [Page Builder](page-builder.md) — placement on pages
