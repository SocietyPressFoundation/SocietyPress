# Backup & Restore

Your insurance against an accidental deletion, a host going out of business, or a move gone wrong. SocietyPress has two kinds of backup, both on **Settings → Export & Backup**:

- **Download Site Export** — a one-click copy of all your SocietyPress data, readable anywhere. Good for keeping your data, or handing it to a successor.
- **Scheduled Backups** — whole-site backups made daily, weekly or on demand, kept on your server: every page, member and record, sign-in accounts, settings, plugins and themes, and optionally your uploaded files. These are the ones **Restore** loads back, on this site or on a new host.

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

Each backup holds the whole site: every database table (members, pages, news posts, menus, settings, sign-in accounts, and other plugins' data), your installed plugins and themes, and, if you ticked it, your uploaded files. It also carries the key that unlocks members' phone numbers and addresses, so it can be restored on any host.

**Guard backup files the way you would a printed membership roster.** Anyone who has one can read every member's name, email, phone number and address. Don't email them or leave them in a shared folder.

## How to restore

**Settings → Export & Backup → Restore a Backup.** Only a site administrator sees it.

- To restore a backup from the **Available Backups** list, use its **Restore** button.
- To restore a backup file saved on your computer — for example after moving to a new host — choose it under **Restore a Backup** and click **Upload Backup File**. Large files are fine; they're sent in small pieces. The file then appears in the list with its own **Restore** button.

Restoring **replaces** the whole site with what was in the backup: members, pages, settings, sign-in accounts, plugins and themes. Uploaded files from the backup are put back alongside any newer ones. Before it starts, SocietyPress makes a backup of the site as it is, so a restore can be undone by restoring that one. Keep the page open until it says it's finished; it usually takes a minute or two.

A few things are looked after for you:

- **You stay signed in.** If the account you're using isn't in the backup (say you made a new one when setting up a new host), it's kept as an administrator. Everyone else's account comes back as it was.
- **The site keeps its own address.** Links in pages and menus that pointed at the old address are changed to the new one.
- **Phone numbers and addresses stay readable** on a new host, and payment settings carry over.
- **SocietyPress itself stays at the version you have installed**, and brings the restored data up to date.

To move to a new host: install WordPress and SocietyPress there, sign in, upload your latest backup and restore it.

A **Download Site Export** can't be restored with the button; it's for keeping your data or handing it on. Its `societypress.sql` loads with phpMyAdmin if you ever need it.

## Recommended rhythm

- **Monthly at least.** Weekly scheduled backups cost nothing once they're set.
- **Keep three months somewhere else** — a cloud folder, a USB drive, your email.
- **Take one before any big change** — a big import, a theme switch, a member clean-out, the year-end close.
- **Take one when you hand the website on.** It's the last thing you do.

## If something looks wrong

**The export download stops partway, or the file is empty.** Your host's time limit cut it off. Ask them to raise PHP's `max_execution_time` to at least 120 seconds.

**The ZIP won't open.** Open it in a text editor. If it starts with `<html>`, your host sent an error page instead; it's the same time-limit problem.

**Phone numbers and addresses look like gibberish after a move.** The data was copied by hand rather than with **Restore**, and the new site has different secret keys in `wp-config.php`. Copy the old site's keys over and the fields read again — SocietyPress leaves them untouched for exactly that reason. Restoring a backup made with SocietyPress 1.5.145 or later avoids this.

**A restore stops partway.** It tells you why. If it stopped before "Switching your site over," nothing on the site changed. If it stopped after, restore the backup SocietyPress made just before you started — it's at the top of the list.

**Scheduled backups never appear.** They run on the first site visit after the chosen hour, so a site nobody visits runs late. Use **Create a backup now** to check they work at all.

## Related guides

- [Privacy & GDPR](privacy-gdpr.md) — what's encrypted, and where the key comes from
- [Installer](installer.md) — setting up the new site before a restore
- [ENS Migration](../ENS-MIGRATION-GUIDE.md) — bringing an EasyNetSites society across
