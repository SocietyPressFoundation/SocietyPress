# SocietyPress — Softaculous Package

This directory is the submission package for listing SocietyPress in the
Softaculous script library, so a society can install the whole product from
their host's control panel in one click.

`societypress.zip` is a build artifact and is gitignored. Everything else here
is source.

## Building

```
./scripts/build-softaculous.sh
```

The script downloads a fresh WordPress, strips the default themes and plugins,
layers in the SocietyPress plugin (including `assets/`), the parent theme and
all five child themes, strips the marketing theme that lives nested inside
`Code/theme/`, and writes `societypress.zip`.

It also does two things that are easy to miss:

- Rewrites `<version>` in `info.xml` from the plugin's `Version:` header, so the
  advertised version can never drift from what is in the bundle. It fails the
  build rather than shipping a mismatch.
- Measures the extracted footprint and warns if `<space>` in `info.xml` has
  fallen below it.

## Where the schema came from

The tag names in `info.xml`, `install.xml` and `upgrade.xml` are **not
documented completely or accurately** on softaculous.com. The authoritative
reference is the shipped WordPress package on any cPanel server that runs
Softaculous:

```
/var/softaculous/wp/info.xml
/var/softaculous/wp/install.xml
/var/softaculous/wp/upgrade.xml
/var/softaculous/wp/fileindex.php
```

Skystra has these. `install.php` and `upgrade.php` there are ionCube-encoded and
cannot be read, so the `$__settings` keys we rely on are the documented ones
plus `dbprefix`, which is confirmed by its presence as an input in the reference
`install.xml`.

Things worth knowing, all of which were originally got wrong here:

- The root element of `install.xml` is `<softinstall>`. There is **no** outer
  `<install>` wrapper. Same for `<softupgrade>` in `upgrade.xml`.
- Groups live inside a `<settings>` wrapper, and a group's heading is a **child
  element**, not an attribute.
- An input's `name` and default `value` are **attributes** on `<input>`, not
  child elements.
- Language strings interpolate as `{{key}}`, not `[[key]]`, and are declared in
  `info.xml` under `<languages>` as `<english-key>`, not as a `<lang><en>` tree.
- `fileindex.php` is a **plain text file**, one path per line, despite the
  `.php` extension. It is not PHP and must not contain an array or any tags.
- Softaculous silently ignores tags it does not recognise. A wrong tag name
  produces no error — just a catalog entry with a blank field.

## Images

`images/` holds the logos, at the exact sizes Softaculous expects:

| File | Size | Purpose |
|---|---|---|
| `societypress.png` | 160×160 | Main logo |
| `societypress_100.gif` | 100×100 | Secondary logo |
| `societypress_48.png` | 48×48 | Medium icon |
| `societypress_32.gif` | 32×32 | Small icon |

**Still missing: screenshots.** Softaculous expects 600×400 GIFs named
`SID_screenshot1.gif` and upward, where `SID` is the numeric script ID they
assign on acceptance. These have to be captured from a real site — the demo is
the obvious source — and cannot be generated from the repo.

## Submitting

1. Build the package.
2. Add screenshots to `images/` (see above).
3. Email the contents of this directory to `sales@softaculous.com`.

To test before submitting, copy this directory to `/var/softaculous/societypress/`
on a server running Softaculous and register it as a custom script through WHM.
Note that the name, category and script ID are set during that registration, not
in `info.xml` — which is why none of them appear in this file.
