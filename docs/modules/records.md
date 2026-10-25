# Genealogical Records

Searchable databases for transcribed records — cemetery rosters, census extracts, marriage indexes, naturalization papers, court minutes, anything where you have rows of structured data members can search.

This is different from the [Library](library.md), which is a catalog of physical items. Records are the *contents* of items — names, dates, places — that members search across.

## What you can do

- Build any number of "collections" (one collection per record type — Sample County Marriages 1837-1900, Saint Mary's Cemetery, etc.).
- Define the columns each collection has (different record types need different fields).
- Import the data from CSV.
- Let members search across one collection or all collections at once.
- Make a collection members-only (a researcher's perk).
- Keep individual fields off the public site (a donor's name, say) while still storing them.
- Export a collection back to CSV any time — your data stays portable.
- Import the same file again safely — rows already there aren't added twice.

## How to build your first collection

Pick a record type. For this example, let's say cemetery transcriptions for St. Mary's Cemetery.

**SocietyPress → Records → Record Collections → Add New Collection.** Name it "St. Mary's Cemetery." Set the record type to Cemetery / Burial Index, and the access to Public or Members only.

Then define the fields. For a cemetery, you'd want:

- Surname (Text)
- Given Name (Text)
- Birth Date (Text — genealogy dates are messy; see below)
- Death Date (Text)
- Section / Plot (Text)
- Inscription (Long Text)
- Notes (Long Text)

Field types are Text, Long Text, Number and Date. Each field has two tick boxes: **Searchable** (the search looks in it) and **Public** (it shows on the public site; untick it to keep a field to the admin).

Save. The collection now exists with no records. Time to load data.

## How to import records

**SocietyPress → Records → Import Records.** Upload your CSV (or a `.genrecord` file). SocietyPress reads the headers and asks you to match each one to a collection field; skip columns you don't want.

Preview shows the first rows matched up. If anything looks wrong, fix the CSV in your spreadsheet and upload it again. The importer handles tens of thousands of rows, working through them in batches with a progress bar.

Importing the same file twice adds nothing: a row is skipped when the collection already holds an identical one. A source that genuinely lists the same line more than once — three infants buried the same day — keeps every copy.

## How to put records on the public site

SocietyPress makes a **Records** page when it's first switched on. Or, with the page builder, drop the **Genealogical Records Search** widget on any page and set:

- A collection, to search only that one — or leave it blank to let visitors choose.
- Require login, or not.

**Shortcode:** `[societypress_records]` for all collections. `[societypress_records collection="cemetery-index"]` to lock it to one — the collection's slug or its ID both work.

Members type a search term, optionally filter by collection, and see results in a table. Each row opens to show the full record, without the fields that aren't marked Public.

### Sorting the results

Click any column heading to sort by it; click it again to reverse. An arrow marks the column in use. Results arrive sorted A–Z by the first column — for most collections that's the surname — so a visitor who has not searched for anything still lands on an index in the order they expect.

Sorting happens across the whole collection, not just the twenty-five rows on screen, so page 1 of a sort by Death Date really is the earliest date in the collection. Records with that column empty go last in both directions, and a column of numbers sorts as numbers: Lot 9 before Lot 10, not after Lot 1.

On a phone the table drops its heading row, so the sort appears as two dropdowns beside the search box instead.

Only columns that hold one value can be sorted. When results span several collections there is nothing but Collection to sort by — the table falls back to a summary column, because no set of field names is true of every row in a mixed result. Filter to one collection to get its own columns back.

## How to handle messy dates

Genealogy dates are *always* messy: "abt 1820," "before 1850," "circa 1830s." Use a **Text** field for them — it takes anything and searches as written. The **Date** type is for dates you know exactly; it won't accept "abt 1820."

## How to export a collection

**SocietyPress → Records → Browse Records**, choose the collection, and click **Export CSV** at the top. The export includes every field, public or not.

This is the canonical "your data, your spreadsheet" export. Run it periodically as a backup. If you ever leave SocietyPress, every collection comes with you.

## If something looks wrong

**Search returns nothing for a record I know is there.** Three things to check: (1) is the field "Searchable"? (2) is the term spelled the same way? Record search matches the letters you type, so try other spellings — "Smith" and "Smyth" are different searches. (3) is the visitor signed in, if the collection is members-only?

**Import says column count doesn't match.** Some rows in your CSV have extra commas or quotes. Open the file in a spreadsheet, fix the row, save as CSV, re-upload. The importer is strict about matching column counts.

**A field I added isn't showing on the public site.** Tick its **Public** box on the collection's edit screen.

**A field is showing that should be private.** Untick its **Public** box on the collection's edit screen. It disappears from the public site straight away, for every record. To keep a whole collection to members, set the collection's access to Members only.

**A corrected file added new rows instead of fixing old ones.** A row counts as already there only when every field matches. Change a spelling in the file and it's a different row. To correct records, edit them in **Browse Records** — or, for many changes, delete the collection (which removes its records too), set it up again, and import the corrected file.

## Related guides

- [Library](library.md) — for cataloging the *physical books* that contain these records
- [Help Requests](help-requests.md) — where members ask "is this person in your records?"
