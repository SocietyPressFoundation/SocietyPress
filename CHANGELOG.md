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
Sixteen modules, all but Members switchable off, so a society runs the parts it
actually uses.

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

