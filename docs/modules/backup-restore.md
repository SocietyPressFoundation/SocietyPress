# Backup & Restore

Your insurance against an accidental deletion, a host going out of business, or a move gone wrong. SocietyPress has two kinds of backup, both on **Settings → Export & Backup**:

- **Download Site Export** — a one-click copy of all your SocietyPress data, readable anywhere. Good for keeping your data, or handing it to a successor.
- **Scheduled Backups** — fuller backups made daily, weekly or on demand, kept on your server: SocietyPress data plus members' sign-in accounts and your settings, and optionally your uploaded files. This is the one to move a whole site with.

Whichever you use, keep copies somewhere other than your hosting account. A backup that lives only on the server it protects isn't a backup.

## Download Site Export

**Settings → Export & Backup → Download Site Export.** The ZIP holds:

| File | What it is |
|---|---|
| `societypress.sql` | Every SocietyPress table — members, events, donations, library, store, committees, ballots, photo and document records. |
| `README.txt` | What's in it, and how to load it. |

Encrypted member fields (cell and other phone numbers, street addresses) are written out readable, so the copy works on any host. Keep the file somewhere private.

**Not included:** members' sign-in accounts, SocietyPress settings, pages, and uploaded files (newsletter PDFs, photos, documents).

## Scheduled Backups

On the same screen:

- **Frequency** — Off, Daily or Weekly, and the hour to run (server time; it runs on the first visit after that hour).
- **Keep this many** — older backups are deleted automatically.
- **Include uploaded files** — newsletter PDFs, photos and documents. Makes backups much larger.
- **Email when ready** — sends an admin a download link each time one finishes.

**Create a backup now** makes one on the spot. **Available Backups** lists them, with what each includes; download them from there.

Each backup holds every SocietyPress table, the WordPress user accounts, your SocietyPress settings and, if you ticked it, your uploaded files. Encrypted fields stay encrypted, so the backup can only be read on a site with the same secret keys in `wp-config.php` — keep a copy of that file with your backups.

## How to restore

There's no restore button yet; it's on the [roadmap](https://github.com/SocietyPressFoundation/SocietyPress/blob/main/ROADMAP.md). Restoring is rare, and your host's support can do the database step for you in a few minutes.

To move to a new host with a scheduled backup:

1. Install WordPress and SocietyPress on the new host.
2. Copy your old site's `wp-config.php` secret keys (`AUTH_KEY` and `SECURE_AUTH_KEY` and the rest of that block) into the new site's `wp-config.php`. Without them the encrypted fields can't be read.
3. In phpMyAdmin (cPanel), import the `.sql` files from the backup into the new database. The table names include your old site's table prefix (usually `wp_`), so the new site should use the same prefix.
4. Copy the uploaded files back into `wp-content/uploads/`.
5. Visit the admin once. SocietyPress brings the database up to date.

Your SocietyPress settings are in the backup as a file, but there's no way to load it yet; re-enter them under **Settings**.

With a **Download Site Export** instead, import `societypress.sql` the same way. Members' records come back, but not their sign-in accounts, so they can't sign in until those are recreated. The readable phone numbers and addresses in it are encrypted again automatically once an admin opens the Members screen.

## Recommended rhythm

- **Monthly at least.** Weekly scheduled backups cost nothing once they're set.
- **Keep three months somewhere else** — a cloud folder, a USB drive, your email.
- **Take one before any big change** — a big import, a theme switch, a member clean-out, the year-end close.
- **Take one when you hand the website on.** It's the last thing you do.

## If something looks wrong

**The export download stops partway, or the file is empty.** Your host's time limit cut it off. Ask them to raise PHP's `max_execution_time` to at least 120 seconds.

**The ZIP won't open.** Open it in a text editor. If it starts with `<html>`, your host sent an error page instead; it's the same time-limit problem.

**Phone numbers and addresses look like gibberish after a move.** The new site has different secret keys in `wp-config.php`. Copy the old site's keys over and the fields read again — SocietyPress leaves them untouched for exactly that reason.

**Scheduled backups never appear.** They run on the first site visit after the chosen hour, so a site nobody visits runs late. Use **Create a backup now** to check they work at all.

## Related guides

- [Privacy & GDPR](privacy-gdpr.md) — what's encrypted, and where the key comes from
- [Installer](installer.md) — setting up the new site before a restore
- [ENS Migration](../ENS-MIGRATION-GUIDE.md) — bringing an EasyNetSites society across
