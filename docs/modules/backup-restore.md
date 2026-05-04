# Backup & Restore

The single feature most likely to save you. A one-click export of every SocietyPress table — members, events, donations, library, store, ballots, committees, photos, documents, settings — bundled into a ZIP you can keep offsite. Pair with a copy of `wp-content/uploads/` and you have a complete portable snapshot of your society's data.

This is your insurance against accidental deletion, a forgotten admin password, a host going out of business, or a failed migration. Run it monthly. Keep the last three months somewhere outside your hosting account.

## What you can do

- One-click full-site export from **SocietyPress → Settings → Export & Backup**.
- Restore by importing the SQL dump on a fresh install.
- Carry the ZIP between hosts when you change providers.
- Hand the ZIP to a successor when you step down as webmaster.

## How to run a backup

**SocietyPress → Settings → Export & Backup.** Click **Download Site Export**.

Behind the scenes, the plugin streams a SQL dump of every `sp_*` table into a single file, decrypts any encrypted member fields to plaintext (so the export is portable), generates a README, and zips everything up. The download starts in your browser as a `.zip` named with your site and the date.

For a 200-member society, the export takes 5–15 seconds. For 2,000 members with thousands of records, plan on a minute or two. If your browser shows "Failed - Network error" partway through, your host's PHP `max_execution_time` is too short — ask their support to bump it to 120 seconds or more, then try again.

## What's in the ZIP

| File | What it is |
|---|---|
| `societypress.sql` | Full SQL dump of every `sp_*` table on your site. Drop-in replacement on restore. |
| `README.txt` | Plain-English restore instructions plus the manifest of what's in the dump. |

**Decrypted on export.** Sensitive member fields (phone numbers, street addresses, dates of birth) are stored encrypted at rest using XChaCha20-Poly1305. The export decrypts them to plaintext so the backup is portable to a new server with a different encryption key. The encryption is a runtime safety net, not a vault — you have always owned this data.

## What's NOT in the ZIP

The export covers SocietyPress data. Two things you also need to preserve on your own:

- **WordPress core tables** (`wp_posts`, `wp_options`, `wp_users`, etc.). Use **Tools → Export** in the WordPress admin for an XML dump of pages, posts, and the page-builder content. Or rely on your host's nightly backup. SocietyPress members ARE WordPress users — the user accounts live in `wp_users`, not in `sp_members`.
- **Uploaded files** in `wp-content/uploads/`. This is where your newsletter PDFs, member photos, gallery images, and document uploads physically live. Streaming them through PHP would crash on most shared hosting, so copy them separately via cPanel File Manager, FTP, or your host's backup tool.

A complete backup = SocietyPress export ZIP + WordPress XML export + `wp-content/uploads/` folder.

## How to restore

**Restoring is rare.** You usually use the export to migrate to a new host or to roll back a specific catastrophe, not as a routine action. The flow:

1. **Install SocietyPress on the destination site** as if it were a new install. Run the setup wizard the minimum amount needed to land in the admin.
2. **Open phpMyAdmin** (in cPanel) on the destination, select the WordPress database, click **Import**, and upload `societypress.sql` from your ZIP. Run.
3. **Check the result.** Visit **SocietyPress → Members → All Members**. Your roster should be there, count matching the original site.
4. **Re-encrypt sensitive fields** if the destination uses a different encryption key. Visit **SocietyPress → Settings → Privacy → Re-encrypt sensitive data** (only present if a key mismatch is detected). One-click; runs in the background.
5. **Copy `wp-content/uploads/` over** via FTP or cPanel File Manager so newsletters, photos, and documents resolve.

If your host doesn't expose phpMyAdmin (some do not), upload `societypress.sql` to your home directory via cPanel File Manager and run it via WP-CLI: `wp db import societypress.sql`. Your host's support can do this for you in under five minutes.

## Recommended cadence

- **Monthly** is the right rhythm for most societies. Pick a date — first of the month, after the monthly meeting, the day dues are pulled, whatever sticks — and make it routine.
- **Keep three months offsite.** Cloud folder, USB drive, email-to-self with the ZIP attached. If your hosting account is the only place a backup lives, it isn't a backup.
- **Take an extra one before any big change.** Major upgrade, theme switch, big import, member purge, fiscal-year close.
- **Take one when handing off the website** to a successor. Last task you do.

## If something looks wrong

**The download starts but the file is 0 bytes.** Almost always a PHP timeout. Ask your host to raise `max_execution_time` to at least 120 seconds, then try again. Some hosts also have a separate `max_input_time` and a server-level cap (LiteSpeed, mod_security) — your host's support handles these.

**The ZIP downloads but won't open.** It's probably HTML in disguise — your host returned an error page. Open the file in a text editor: if you see `<html>` at the top, that's a server error you're saving as a ZIP. Same fix as above (timeouts) plus check `wp-content/debug.log` for the underlying PHP error.

**Restore fails with "Table already exists."** Drop the existing `sp_*` tables first, then re-import. In phpMyAdmin: select all `sp_*` tables in the left sidebar, click **Drop**, confirm. Then re-run the SQL import. The dump includes `DROP TABLE IF EXISTS` for each table but some MySQL configurations ignore those — manual drop is the reliable path.

**Member phone numbers and addresses look like gibberish on the destination site.** The encryption key didn't move with the data. Two paths: re-import the SQL dump on the destination AFTER the encryption key is identical to the source (copy `SP_LIBSODIUM_KEY` from the source's `wp-config.php`), or accept the loss and re-collect from members. Memory-key storage is intentional: if the key were in the database, the encryption wouldn't add real protection.

**The export grew much larger between months.** Probably the URL access log filling up. **SocietyPress → Settings → Privacy** has a retention setting (default 90 days). Lower it if you don't need a full year of access history; the export is bounded by whatever the retention setting allows.

## Related guides

- [Settings → Privacy](members.md) — encryption and retention details
- [ENS Migration](../ENS-MIGRATION-GUIDE.md) — special case: import a complete ENS dataset into a fresh SocietyPress site
- [Setup Wizard](setup-wizard.md) — what you'll need to do on the destination before a restore
