# Library

Catalog of every book, periodical, microfilm and CD in your physical library. Members search it from the website; volunteers maintain it from the admin. Most society libraries are reference-only, so SocietyPress assumes yours is — switch on lending if yours lends.

## What you can do

- Catalog books, serials, microfilm, manuscripts, CDs, DVDs, ephemera and society publications.
- Record call number, shelf location, condition, donor, acquisition date and value.
- Tag each item with one or more Media Types, Subjects and Locations from word lists you control.
- Let visitors search by title, author, call number, description, county, state or surname — or all at once — and narrow by year.
- Show the catalog on your public site, to everyone or members only.
- Sell society-published books from the same catalog — tick a box and the item appears in the Store.
- Import an existing catalog from a spreadsheet (CSV), mapping its columns as you go.
- Fill in missing publishers, years and covers from Open Library by ISBN.

## How to add your first item

**Library → Library Catalog → Add New Item.** Title, author, publisher, year, call number and shelf location; pick its Media Type, Subjects and Location. Save.

If the item has an ISBN, add it, and **Fill In Book Details** (below) can look up the rest.

## How to import a whole catalog

Most societies have their catalog in a spreadsheet, an old Access database, or printed cards. Get it into a CSV:

- One row per item.
- Whatever columns you have — title, author, publisher, year, ISBN, call number, shelf location, county, state, surname, condition, donor, acquisition date, value.
- Don't worry about column names — the importer lets you match them on upload.

**Library → Import Library.** Upload, match the columns, preview, import. It handles dozens to thousands of rows; a library of 17,000 items takes about ten minutes.

Afterwards, **Library → Fill In Book Details** works through every item with an ISBN, filling in missing details and covers from Open Library.

## How to set up your word lists and search columns

**Library → Catalog Options.**

- **The lists catalogers pick from** — Media Type, Subject and Location, one entry per line. Delete a line to take it out of the dropdowns; items already using it keep it until someone edits them. **Put back the standard list** restores the starting set.
- **What a visitor sees in search results** — untick columns your catalog doesn't fill in, and use the arrows to put them in order.
- **Lending** — tick **This library lends items out** only if material leaves the building. Otherwise the catalog says nothing about borrowing.

## How to put the catalog on the public site

SocietyPress makes a **Library** page when it's first switched on. You can also drop the **Library Catalog** widget on any page with the page builder. Its settings:

- Items per page.
- Show the landing page — collection stats, browse by type, popular subjects.
- Show the search box.
- Show available items only.
- Require login to view.

Visitors search by title, author, call number, description, county, state, surname, or all of them, and open a row to see the full record.

## How to sell a society publication from the catalog

For books your society has published — county histories, family genealogies, transcribed records:

1. Open the item's edit page and find the **Store Listing** section.
2. Tick **List this item for sale in the Store**.
3. Make sure **Value** is above 0 — it's the sale price.
4. Optionally pick a Store Category and write a Store Description (separate from the catalog description).
5. Set the shipping fee per copy, and save.

The item now appears in the Store (if the [Store](store.md) feature is on) and stays in the catalog. A value on its own never puts an item in the Store, so things held in the collection stay out of it.

## If something looks wrong

**Covers aren't loading.** Open Library limits how fast it can be asked and sometimes times out. Run **Fill In Book Details** again — it skips items that already have a cover.

**An item shows up in the Store but shouldn't.** Open it and untick **List this item for sale in the Store**.

**Search isn't finding a known item.** Check which field you're searching. Choose **All Listed Fields** to look in all of them at once.

**Import says columns aren't recognized.** During the upload step, match each column to a SocietyPress field, or skip it. Only the title is required.

**Donor name is wrong on a batch of items.** There's no bulk edit for donors yet; open each item and correct it.

## Related guides

- [Store](store.md) — how the catalog → storefront link works
- [Records](records.md) — for searchable record databases (cemetery transcriptions, etc.) — different module
- [Help Requests](help-requests.md) — when a member can't find something, this is where they ask
