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

