# Installer

The fastest path from "I heard about SocietyPress" to "my society's site is running on it." A single PHP file (`sp-installer.php`) you upload to your web host's `public_html` directory, then visit in a browser.

For a long-form walk-through with screenshots, see the [Installation page on getsocietypress.org](https://getsocietypress.org/docs/installation/). This guide is the "I just want to know what it does" version.

## What it does

In about three minutes, on most cPanel hosts, the installer:

1. Downloads the latest WordPress from wordpress.org.
2. Downloads the latest SocietyPress bundle from getsocietypress.org/downloads/societypress-latest.zip.
3. Extracts both into the directory you ran it from.
4. Configures `wp-config.php` with the database credentials you provide.
5. Creates the WordPress admin account.
6. Activates the SocietyPress plugin and the SocietyPress parent theme.
7. Pre-fills your society name and email.
8. Deletes itself when done.

You end up with a clean, ready-to-use SocietyPress site. The Setup Wizard runs automatically the first time you log in.

## What you need before starting

- **A web hosting account.** Any cPanel host works — HostGator, Bluehost, Skystra, SiteGround, A2, GreenGeeks, namecheap, etc. Shared hosting is fine; you don't need a VPS.
- **A domain name.** Existing or new. The installer doesn't care — it runs at whatever URL you point it at.
- **PHP 8.0 or newer.** Most hosts let you toggle PHP version via cPanel's "MultiPHP Manager" or "PHP Selector." Set it to 8.0+ before running the installer.
- **A MySQL database.** Create one in cPanel's "MySQL Databases" tool. Note the database name, username, password, and host (usually `localhost`).
- **About 15 minutes.** Three minutes for the installer, the rest for cPanel setup if it's your first time.

## How to run it

1. **Download the installer.** Right-click [https://getsocietypress.org/sp-installer.php](https://getsocietypress.org/sp-installer.php) and save the file as `sp-installer.php`. Don't rename it.

2. **Upload it to your host.** In cPanel → File Manager → public_html, click Upload and pick the file. Or use FTP — SFTP, FileZilla, whichever you're comfortable with.

3. **Visit the installer in a browser.** Go to `https://yourdomain.org/sp-installer.php`. (If the page shows raw PHP code, your host's PHP isn't enabled or is below 8.0.)

4. **Fill in the form:**
   - Database name, username, password, host.
   - Site name (your society's name).
   - Administrator username, email, password (you'll use these to log in).
   - Optional: society address, phone.
   - Optional: pre-create membership tiers.

5. **Click Install.** Watch the progress log. About 90 seconds for the WordPress download, another 30 for the SocietyPress bundle, then a flurry of "configuring database / creating admin / activating theme / activating plugin" messages.

6. **Done.** The installer redirects you to the WP admin login. Use the username and password you set. The Setup Wizard takes you the rest of the way.

## How it's safer than it looks

A "give it your database password and let it download stuff and execute it" workflow sounds risky. Three things keep it bounded:

- **Single-file, self-deleting.** `sp-installer.php` deletes itself once installation completes. There's no leftover code attacking the server later.
- **Verified downloads.** Both WordPress and the SocietyPress bundle are downloaded over HTTPS from canonical sources. The installer checks file integrity before extracting.
- **Locked-down install.** Database credentials go straight into `wp-config.php` (where WordPress needs them anyway) and never touch the session, never get logged, never get transmitted to a third party.

If you want the audit trail: the installer source is in the SocietyPress repo at `Code/installer/sp-installer.php`. ~1,200 lines of well-commented PHP. Read it before running it if you'd rather.

## How to use it without the one-click flow

If your host doesn't allow PHP scripts to be uploaded (rare), or you'd just rather do it manually:

1. Install WordPress the standard way (most hosts have a 1-click WordPress installer in cPanel).
2. Download the SocietyPress bundle from `https://getsocietypress.org/downloads/societypress-latest.zip`.
3. Extract it. You'll get a `societypress/` plugin folder and `themes/` (with `societypress/`, `heritage/`, `coastline/`, etc.).
4. Upload `societypress/` to `wp-content/plugins/` and the contents of `themes/` to `wp-content/themes/`.
5. Activate the SocietyPress plugin (Plugins → SocietyPress → Activate).
6. Activate a theme — either the parent SocietyPress theme or one of the five child themes (Heritage, Coastline, Prairie, Ledger, Parlor).
7. The Setup Wizard runs on your first admin page load.

## How to upgrade an existing install

When a new SocietyPress release is tagged on GitHub, your site sees an "Update available" notice in the WordPress admin (just like core WP updates). Click it, click Update, done.

If your host doesn't allow auto-updates, manual upgrade:

1. Download the latest bundle.
2. Extract.
3. Upload the new `societypress/` folder to `wp-content/plugins/`, replacing the old.
4. Visit any admin page — SocietyPress notices the version mismatch and runs the database migration automatically. No data is touched.

The plugin and parent theme version are kept in lockstep. If you've activated a child theme, the child theme has its own version on its own cycle and doesn't need to update with each plugin release.

## How to install on Softaculous-enabled hosts

Once SocietyPress is approved by Softaculous Limited (in progress as of late April 2026), you'll be able to install via your host's Softaculous interface in literally one click — no installer file to download, no database setup, no PHP version toggling. cPanel → Softaculous → Search "SocietyPress" → Install.

This is the recommended path for societies that just want a working site as fast as possible, but it depends on your host having Softaculous (most cPanel hosts do; some don't).

## If something looks wrong

**The installer page is blank.** PHP isn't enabled or is below 8.0. cPanel → MultiPHP Manager → set the domain to PHP 8.0+. Refresh.

**"Cannot connect to database" error.** Double-check the database name, username, password, and host. Common gotchas: most hosts prefix database names with your account name (e.g., your "society" database is actually "youraccount_society"); host is usually `localhost`, not your domain.

**"Cannot write to public_html" error.** File permissions. cPanel → File Manager → public_html → Permissions → 755. The installer needs to write `wp-config.php`, the WordPress files, and the SocietyPress bundle.

**Installation completed but I get a 404 on the home page.** WordPress permalinks haven't been written yet. Log in to the WP admin → Settings → Permalinks → Save Changes (without changing anything). This rebuilds the URL routing.

**A specific feature module isn't appearing in the admin sidebar.** Settings → Modules — toggle the modules you want enabled. The installer leaves all modules off by default to keep the admin sidebar clean; you turn on what you need.

**The Setup Wizard doesn't run.** It only runs once. To re-run it: SocietyPress → Settings → Reset Setup Wizard.

## Related guides

- [Setup Wizard](setup-wizard.md) — what the wizard does after the installer hands off
- [Members](members.md) — usually the first thing you do after install
- [Theme Presets](theme-presets.md) — pick a look right after install
