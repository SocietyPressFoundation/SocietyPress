# Changelog

All notable changes to SocietyPress are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

SocietyPress uses a single running version shared by the plugin, the parent theme
and every child theme — they are built, released and installed together, so they
carry one number rather than drifting apart. The patch digit increments each
release, without rolling over: 1.1.9, 1.1.10, 1.1.11, and so on. A change in the
first or second digit marks a milestone release, not a technical distinction about
the kind of change involved.

Entries describe user-visible changes only. For the underlying commits, see
[the Git log](https://github.com/SocietyPressFoundation/SocietyPress/commits/main).

The 1.0 and 1.1 development line is archived in
[CHANGELOG-1.x.md](CHANGELOG-1.x.md); the pre-1.0 work in
[CHANGELOG-pre1.md](CHANGELOG-pre1.md).

---

## [1.5.4] — 2026-08-29

**Imported institutions keep their names.** A library or museum in an ENS
export usually appears as a contact person's name with the institution in the
File Name column, and SocietyPress would only file it as an organization when
the Membership Type column said the exact word "Organization." Societies write
"Institutional," or "Corporate," or "Non-Profit" — and when they did, the
institution's name was thrown away during the import with no error and nothing
in the log to notice. The import now recognises the ordinary ways of saying
the same thing.

**And when it still doesn't recognise the word, it says so.** Any row that
carried an organization name the import decided not to use is now counted on
the results screen, along with the Membership Type values it saw, so a society
using its own vocabulary is told which rows to look at rather than finding out
months later that the library is filed under its librarian. Correcting those
rows and importing the file again is safe — a second import updates the same
members instead of duplicating them.

---

## [1.5.3] — 2026-08-29

**The store's page-size control is three buttons instead of a box to type in.**
Choosing how many items to see at once no longer means typing a number and
pressing Go, and no longer means knowing that zero is the way to ask for
everything. The store now offers 20, 50 and All as buttons, with the current
one highlighted. The buttons appear only when the catalog runs past two pages
at the default size — a store with forty items or fewer shows just the page
links, since a page-length choice on a list that short is one more thing to
read for no gain.

---

## [1.5.2] — 2026-08-29

Removes a private domain reference from the shipped plugin and repairs the
build-time scanner that was supposed to have caught it.

**A code comment named a private development site.** A comment explaining the
affiliation-logo safety net cited the maintainer's own test site by name to
illustrate the case it guards against. The illustration was never necessary —
the sentence makes its point better in general terms, since any society that
has customized its footer is in exactly that position — and the name had
shipped inside every release since 1.1.11. The comment now describes the case
without naming anyone.

**The leak scanner had never run.** `build-softaculous.sh` announced
"Scanning for data leaks..." on every build and checked nothing, in three
independent ways: it read its pattern list from a local configuration file that
had never been created, so the list was empty and a guard skipped the scan
entirely; it looked only at the plugin file, never at the parent theme or the
five child themes that ship in the same bundle; and it recorded a hit in a
variable that nothing ever read, so even a detected leak printed a warning and
let the build succeed. The scanner now refuses to build when it has no patterns
configured, scans everything that ships, and exits non-zero on a hit. A scanner
that cannot fail the build is decoration.

**The Softaculous package was missing its wordmark.** `info.xml` opens its
catalog description with an inline `logo.png` that did not exist, so the listing
would have rendered a broken image against its own text. It is a separate file
from the square catalog icon and keeps its literal name rather than taking the
assigned script ID, which is why it went unnoticed.

---

## [1.5.1] — 2026-08-28

Repairs the Softaculous package, which had drifted far enough from both the
product and Softaculous's actual file format that a one-click install would
have produced a half-working site.

**The install bundle was missing the plugin's assets.** The builder copied
`societypress.php` and the translations but not `assets/`, so every Softaculous
install would have shipped without the PWA icons and favicons and without the
CSS and JS behind the gallery viewer, the events pages, the editor table, the
searchable select and the leadership search. The builder now copies them, and
refuses to build at all if they are absent rather than quietly producing a
broken bundle.

**The package files were written against a schema that does not exist.**
Softaculous ignores tags it does not recognise without reporting anything, so
the catalog entry would have appeared with no description, no version and no
PHP requirement, and the installation form would have been empty. The metadata,
install form and upgrade form have been rewritten against the shipped
WordPress package, and the file index rewritten in the plain-text format
Softaculous actually reads.

**Installs no longer hide themselves from search engines.** The installer
forced the "discourage search engines" setting on for every new site, and the
switch that undoes it is buried three menus deep where a volunteer will never
find it. A society exists to be found, so the install form now asks, and
defaults to visible.

**The database table prefix is honoured.** It was hardcoded to `wp_`, which
meant two SocietyPress sites could never share one database. The install form
now offers the prefix and the installer uses it.

**Smaller things.** The install form gained a site tagline field, so a new site
no longer launches carrying WordPress's "Just another WordPress site"
placeholder. Child themes are no longer excluded from upgrades — they ship as
part of one versioned set, and holding them back was what made the Theme
Gallery offer an update that installed and then reappeared forever. The
declared install footprint is now measured from a real build rather than
guessed, and the builder warns when it falls behind.

---

## [1.5.0] — 2026-08-28

The first release SocietyPress considers finished.

Everything a genealogical or historical society needs to run online, in one
free, GPL-licensed WordPress product: members and dues, events and
registrations, a library catalog, record collections, newsletters, blast email,
donations, a store, committees and meetings, ballots, volunteers, documents,
photo galleries, lineage programs, research help, and a help desk of its own.
Eighteen optional modules on top of Members and Finances, which are always
available, so a society runs the parts it actually uses.

**For the volunteer who has to operate it.** A page builder rather than a
theme editor. A setup wizard on first run. Every module screen tells you when
the public cannot see it yet and offers to make the page — and then to put it
in your menu. Nothing that renders is hardcoded: every visible string is
editable from the admin by somebody with no technical background.

**For the society that is moving.** A migration path from EasyNetSites that
reads their export column by column, a handbook that names every field and what
it becomes, and three ways to back out if it goes wrong.

**For the society that outgrows the look.** Five child themes ship with the
product. The Theme Exchange adds saved looks and bundles from other societies,
and full child themes that have been read line by line before they carry a
badge.

**Under it.** 77 custom tables, ~107 AJAX endpoints, XChaCha20-Poly1305
encryption for sensitive member data, GDPR exporters and erasers, a PWA, and
~7,900 translatable strings with templates for the plugin and parent theme.

**What it tells us.** Once a week an install sends its society name, website
address and version, and nothing else. Settings → Privacy shows exactly what
went and says plainly that there is no switch.

The development history that led here is archived in
[CHANGELOG-1.x.md](CHANGELOG-1.x.md).

