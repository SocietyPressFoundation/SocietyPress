# Genealogical Records

Searchable databases for transcribed records — cemetery rosters, census extracts, marriage indexes, naturalization papers, court minutes, anything where you have rows of structured data members can search.

This is different from the [Library](library.md), which is a catalog of physical items. Records are the *contents* of items — names, dates, places — that members search across.

## What you can do

- Build any number of "collections" (one collection per record type — Sample County Marriages 1837-1900, Saint Mary's Cemetery, etc.).
- Define the columns each collection has (different record types need different fields).
- Import the data from CSV.
- Let members search across one collection or all collections at once.
- Hide certain collections behind the members-only login (a researcher's perk).
- Make individual fields private (donor name visible to staff, hidden from search results).
- Export a collection back to CSV anytime — your data stays portable.

## How to build your first collection

Pick a record type. For this example, let's say cemetery transcriptions for St. Mary's Cemetery.

**SocietyPress → Records → Collections → Add Collection.** Name it "St. Mary's Cemetery." Set Record Type to "Cemetery." Pick the access level — public, members-only, or staff-only.

Then define the fields. For a cemetery, you'd want:

- Surname (text, indexed for search)
- Given Name (text)
- Birth Date (date or text — genealogy dates are messy)
- Death Date (date or text)
- Section / Plot (text)
- Inscription (text)
- Notes (text, optional)

Each field has flags: "Searchable" (appears in the search box and indexes), "Public" (visible to non-members or marked private), "Required" (can't import a row without it).

Save. The collection now exists with no records. Time to load data.

## How to import records

**SocietyPress → Records → Import Records.** Upload your CSV. SocietyPress reads the headers and asks you to map each one to a collection field. Skip columns you don't want. Save the mapping (you can save it as a "preset" so subsequent imports use the same mapping without re-doing the work).

Preview shows the first 5 rows mapped. If anything looks wrong, fix the CSV in your spreadsheet and re-upload. The importer handles 10,000+ rows with no trouble.

## How to put records on the public site

**Page builder:** drop the "Records Search" widget. Configure:

- Specific collection (drops the picker — search only this collection) OR
- All collections (member picks from a dropdown).
- Login required or not.

**Shortcode:** `[sp_records_search]` for all collections. `[sp_records_search collection="123"]` to lock it to one (use the collection's ID, visible in the admin).

Members type a search term, optionally filter by collection, and see results in a table. Each row expands inline to show the full record. Public/members-only/staff-only field flags determine what each viewer sees.

## How to handle messy dates

Genealogy dates are *always* messy: "abt 1820," "before 1850," "circa 1830s." We accommodate. When you define a date field, pick "Free-text date" (the default) instead of "Strict date." Free-text accepts anything; the search treats it as text. Strict dates parse and validate.

If you mix the two — strict for known dates, free-text for fuzzy ones — define two fields ("Death Date" as strict, "Death Date Approximate" as free-text). Most projects don't bother and just use free-text everywhere.

## How to export a collection

**SocietyPress → Records → Collections.** Click the collection. There's an "Export to CSV" button at the top right. The export honors your field-level public/private flags from a staff perspective — i.e., it includes everything, including private fields, since you're the staff.

This is the canonical "your data, your spreadsheet" export. Run it periodically as a backup. If you ever leave SocietyPress, every collection comes with you.

## If something looks wrong

**Search returns nothing for a record I know is there.** Three things to check: (1) is the field "Searchable"? (2) is the term spelled the same way (try Soundex variants — "Smith" vs "Smyth")? (3) is the visitor logged in if the collection is members-only?

**Import says column count doesn't match.** Some rows in your CSV have extra commas or quotes. Open the file in a spreadsheet, fix the row, save as CSV, re-upload. The importer is strict about matching column counts.

**A field I added isn't showing in search results.** Each field has a "Show in results" flag separate from "Searchable." A field can be searched but hidden from the result table — useful for searching by donor without listing the donor publicly. Edit the field, tick "Show in results."

**Records are showing private fields to non-members.** Each field has its own "Public / Members / Staff" access level. Open the field, set the access level to Members or Staff. The change applies to existing rows immediately.

**Importing duplicates.** The importer doesn't dedupe by default — it inserts every row in the CSV. To deduplicate, define a "key" field (usually a record ID from the source) and set "Update existing rows by [key]" on the import options.

## Related guides

- [Library](library.md) — for cataloging the *physical books* that contain these records
- [Help Requests](help-requests.md) — where members ask "is this person in your records?"
