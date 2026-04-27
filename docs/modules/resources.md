# Resources

A categorized directory of useful external links — partner societies, archives, online databases, vital-records sites. The "links" page every society has, made navigable.

## What you can do

- Add external links with title, URL, description, category.
- Group by category (Sample County / Texas / National / DNA / Cemeteries / etc.).
- Sort within category.
- Track when each link was last verified (encourages periodic cleanup).
- Public or members-only access (most are public).
- Bulk import from CSV.

## How to add your first resource

**SocietyPress → Resource Links → Add New.** Title (e.g., "Texas Vital Records"), URL, optional description, category. Save.

Categories help visitors navigate — a 100-link list is daunting; a 100-link list grouped into 10 categories is browsable.

## How to import a list

**SocietyPress → Resource Links → Import Links.** CSV with columns: title, url, description, category, sort_order. Upload, map, preview, import.

If you're moving from EasyNetSites, the ENS Links export format is recognized — drop in the CSV and the importer maps the columns automatically.

## How to set up categories

**SocietyPress → Resource Links → Categories.** Add categories in the order you want them displayed. Common starter set:

- General Genealogy Resources
- [Your State] Resources
- [Your County] Resources
- National Archives & Databases
- DNA Testing & Analysis
- Cemeteries
- Newspapers & Obituaries
- Migration & Immigration
- Military Records

You can have as many or as few as makes sense. Empty categories are hidden from the public display.

## How to put the directory on a page

**Page builder:** drop the "Resource Links" widget. **Shortcode:** `[sp_resources]`.

The display groups by category, with each link's title (clickable) + description. Sort by category, then by sort_order within category.

If you want a single category on a single page (e.g., "DNA Resources" page), the shortcode accepts `category="dna-resources"` (using the category slug).

## How to handle dead links

**SocietyPress → Resource Links → Verify Links.** Runs a batch check — visits each URL, marks broken links red. Doesn't auto-delete; you decide what to fix vs remove.

The "last verified" timestamp updates per check. Links not verified in 6+ months get a yellow warning badge in the admin.

## If something looks wrong

**Links not appearing in the public directory.** Three reasons: (1) the link's status is `inactive`, (2) the link's category is empty / inactive, (3) members-only access on a page viewed by a non-member.

**Categories displaying in wrong order.** Edit the categories, set sort_order. Lower numbers appear first. Items with the same sort_order are alphabetized by name.

**Imported CSV has extra/missing columns.** During upload, the importer asks you to map each CSV column to a SocietyPress field. Skip columns you don't want.

## Related guides

- [Library](library.md) — for cataloging physical books in your library
- [Records](records.md) — for searchable transcribed records (different from links)
