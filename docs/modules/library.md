# Library

Catalog of every book, periodical, microfilm, and CD in your physical library. Members search it from the website; volunteers maintain it from the admin. OPAC-style — open-access, no checkouts (most society libraries are reference-only and don't lend).

## What you can do

- Catalog books, serials, microfilm, manuscripts, CDs, DVDs, ephemera, and society publications.
- Track call number, shelf location, condition, donor, acquisition date, value.
- Search by title, author, surname (huge for genealogy libraries), county, state, publisher, year.
- Show the catalog on your public site with members-only access optional per-collection.
- Sell society-published books from the same catalog — flip a switch and a catalog item appears in the store.
- Import an existing catalog from CSV (we'll map the columns).
- Auto-fetch cover thumbnails from Open Library by ISBN.

## How to add your first item

**SocietyPress → Library → Add New.** Title, author, publisher, year. Pick a category. Add the call number and shelf location. Save.

If the item has an ISBN, paste it in. SocietyPress queries Open Library and fills in publisher, year, and a cover thumbnail automatically. You can override anything it pulls.

## How to import a whole catalog

Most societies have their catalog in a spreadsheet, an old Access database, or printed cards. Get it into a CSV:

- One row per item.
- Columns we recognize: title, author, publisher, pub_year, isbn, call_number, shelf_location, geographic_location, surname, county, state, category, condition, donor, acquisition_year, item_value.
- Don't worry about column names — the importer lets you map them on upload.

**SocietyPress → Library → Import Library.** Upload, map columns, preview. The import handles dozens to thousands of rows. For a library with 17,000 items, expect about 10 minutes.

After import, run **SocietyPress → Library → Catalog Enrichment** if you want to auto-pull missing covers from Open Library. It batches through anything with an ISBN but no cover URL.

## How to organize by category

**SocietyPress → Library → Categories.** Add categories that match how your library is organized — "County Histories," "Family Histories," "Vital Records," "Microfilm," "Newsletters," "Reference."

On the front-end catalog, members can filter by category. Same category names show up in the admin list view as a filter dropdown.

## How to put the catalog on the public site

**Page builder:** drop the "Library Catalog" widget. Configure:

- Login required (members-only) or public.
- Items per page (default 25).
- Whether to show in-stock badges.
- Whether the search box appears.

**Shortcode:** `[sp_library_catalog]`. Same options as widget settings.

Members search by title, author, or use the advanced search to narrow by category, geographic location (county/state), surname, or year range. Results expand inline to show the full record (call number, shelf location, donor, etc.).

## How to sell a society publication from the catalog

For books your society has published — county histories, family genealogies, transcribed records:

1. Open the library item edit page.
2. Find the **Store Listing** section.
3. Set "Item Value" above 0 (this is the sale price).
4. Optionally pick a Store Category (e.g., "County Histories").
5. Optionally write a Store Description (marketing copy, separate from the catalog description).
6. Set per-unit Shipping fee.
7. Save.

That item now appears in the public store automatically (assuming the [Store](store.md) module is enabled). The library catalog still shows it; only the storefront pulls in items with a value > 0.

## If something looks wrong

**Cover thumbnails aren't loading.** Open Library's API is rate-limited and occasionally times out. Run **SocietyPress → Library → Catalog Enrichment** again — it skips items that already have a cover and retries the failed ones.

**An item shows up in the storefront but shouldn't.** Open the item, set Item Value to 0 (or leave blank). The storefront only shows items with a value > 0.

**Search isn't finding a known item.** SocietyPress searches title, author, surname, county, and state. If the search term is in the description (free text), the search might miss it — the search is keyword-indexed, not full-text. Try a different word from the title or author.

**Import says columns aren't recognized.** During the upload step, you map every CSV column to a SocietyPress field. Set unrecognized columns to "Skip" — they'll be ignored. The required column is title; everything else is optional.

**Donor name is wrong on a batch of items.** Bulk edit: select the items in the catalog list, choose "Edit Donor" from the bulk actions dropdown, set the new name, apply.

## Related guides

- [Store](store.md) — how the catalog → storefront link works
- [Records](records.md) — for searchable record databases (cemetery transcriptions, etc.) — different module
- [Help Requests](help-requests.md) — when a member can't find something, this is where they ask
