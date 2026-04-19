<div align="center">

# SocietyPress

**A complete WordPress platform for genealogical and historical societies.**

Free and open source under the GPL. No pricing tiers, no upgrades, no lock-in —
just the whole thing, given away.

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](LICENSE)
[![WordPress: 6.0+](https://img.shields.io/badge/WordPress-6.0%2B-21759b.svg)](https://wordpress.org/)
[![PHP: 8.0+](https://img.shields.io/badge/PHP-8.0%2B-777bb4.svg)](https://www.php.net/)
[![Version](https://img.shields.io/badge/Plugin-v1.0.19-brightgreen.svg)](CHANGELOG.md)

[**getsocietypress.org**](https://getsocietypress.org) &nbsp;·&nbsp;
[**Live demo**](https://demo.getsocietypress.org) &nbsp;·&nbsp;
[**Documentation**](docs/) &nbsp;·&nbsp;
[**Report a bug**](https://github.com/SocietyPressFoundation/SocietyPress/issues)

</div>

---

## What it is

SocietyPress is a single-file WordPress plugin paired with a purpose-built
parent theme and five ready-to-use child themes. Together they give a
genealogical or historical society everything it needs to run itself online:
members, dues, events, a library catalog, newsletters, a records repository,
volunteer coordination, donations, a blast-email system, committees, voting,
a page builder, and a frontend that's legible for non-technical volunteers
by default.

It was written for the people who actually keep these organizations
running — committee chairs, long-serving volunteers, librarians — not
for agencies or developers. Everything is biased toward "a senior volunteer
can do this" over "a developer will figure it out."

---

## What's in the bundle

| Module | Status | Purpose |
|---|---|---|
| **Members** | Always on | Profiles, tiers, dues, groups, surname research, member portal |
| **Events** | Toggleable | Scheduling, registration, waitlists, recurring, calendars, iCal |
| **Library** | Toggleable | Full OPAC-style catalog (tested at 19,000+ items) |
| **Newsletters** | Toggleable | PDF archive with auto-generated cover thumbnails |
| **Resources** | Toggleable | Curated external links, searchable |
| **Committees** | Toggleable | Committees, chairs, delegated permissions |
| **Volunteers** | Toggleable | Opportunities, signups, waitlists, hours tracking |
| **Donations** | Toggleable | Campaign-based tracking, progress bars, acknowledgments |
| **Blast Email** | Toggleable | Mass email with merge tags, batching, delivery tracking |
| **Records** | Toggleable | Genealogical records repository (cemetery, census, marriage, etc.) |
| **Store** | Toggleable | Public storefront for products and library items |
| **Documents** | Toggleable | Board documents, policies, forms with per-doc access |
| **Photos & Videos** | Toggleable | Nested galleries plus YouTube embeds, frontend lightbox |
| **Voting** | Toggleable | Elections, ballots, results |

Plus: page builder (21 widgets), design system (live-preview color and
typography controls), email system (logged, merge-tagged, dev-mode), unified
admin sidebar, a setup wizard, a custom login page, full site lockdown,
field-level encryption for sensitive data (XChaCha20-Poly1305), GDPR
exporters and erasers, and search across every module.

A complete breakdown lives in [`docs/FEATURES.md`](docs/FEATURES.md).

---

## Requirements

- WordPress **6.0** or later
- PHP **8.0** or later
- MySQL **5.7+** or MariaDB **10.3+**
- PHP `libsodium` extension (present in PHP 7.2+) — for field encryption
- PHP `imagick` extension — for newsletter cover thumbnails (optional)

---

## Installing

### One-click (recommended)

On any cPanel host with Softaculous, pick **SocietyPress** from the catalog
and install. This provisions a fresh WordPress, installs the plugin, activates
the theme, and runs the setup wizard automatically.

*Submission to Softaculous is in progress — see the
[roadmap](ROADMAP.md).*

### Manual install

Download the latest `societypress.zip` from the
[Releases](https://github.com/SocietyPressFoundation/SocietyPress/releases)
page, extract it, and upload the contents to your existing WordPress site.

### From source

```
git clone https://github.com/SocietyPressFoundation/SocietyPress.git
# Plugin:          Code/plugin/societypress.php  →  wp-content/plugins/societypress/
# Parent theme:    Code/theme/                    →  wp-content/themes/societypress/
# Child themes:    Code/theme-<name>/             →  wp-content/themes/<name>/
```

Activate the plugin, then activate either the parent theme or a child theme.
The setup wizard will open on first activation.

---

## Documentation

| Document | What's in it |
|---|---|
| [`docs/FEATURES.md`](docs/FEATURES.md) | Complete module-by-module feature reference |
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Technical reference: tables, hooks, AJAX, crons, templates |
| [`docs/ENS-MIGRATION-GUIDE.md`](docs/ENS-MIGRATION-GUIDE.md) | Migrating from EasyNetSites, step by step |
| [`ROADMAP.md`](ROADMAP.md) | What's next, organized by theme |
| [`CHANGELOG.md`](CHANGELOG.md) | Release history |

End-user guides (setup walkthroughs, module-by-module how-tos) live at
[getsocietypress.org/docs](https://getsocietypress.org/docs).

---

## Getting help

- **Support forums** — [getsocietypress.org/community](https://getsocietypress.org/community)
- **Documentation** — [getsocietypress.org/docs](https://getsocietypress.org/docs)
- **Bug reports** — [GitHub Issues](https://github.com/SocietyPressFoundation/SocietyPress/issues)
- **Security issues** — see [SECURITY.md](SECURITY.md) (please do **not** file these publicly)
- **Everything else** — the [feedback form](https://getsocietypress.org/feedback)

See also [`SUPPORT.md`](SUPPORT.md).

---

## Contributing

SocietyPress is open source but **not open to external contributions** at this
time — it's designed, written, and maintained by one person, and the
pace/direction is set accordingly. You are welcome to fork, modify, and
deploy it under the terms of the GPL. Bug reports and well-described feature
requests via [GitHub Issues](https://github.com/SocietyPressFoundation/SocietyPress/issues)
are very welcome.

Full policy in [`CONTRIBUTING.md`](CONTRIBUTING.md).

---

## License

Released under the [GNU General Public License v2.0 or later](LICENSE).

Free as in freedom. Free as in beer.
