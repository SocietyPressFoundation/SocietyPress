# Moving from ENS to SocietyPress

A step-by-step guide for genealogical and historical society webmasters who are leaving Easynet Solutions (ENS, sometimes branded EasyNetSites) and bringing their site to SocietyPress.

This guide assumes you are not a developer. You don't need to know PHP, MySQL, or anything about WordPress to follow it. You **do** need access to your current ENS site as an administrator, and access to a web hosting account where SocietyPress will live.

---

## Before you start

You'll want about **two to four hours** of uninterrupted time, plus a calm follow-up week to verify that nothing got lost.

Have these ready:

1. **Administrator access to your current ENS site** — the login that lets you export member data.
2. **A web hosting account** — almost any cPanel host works (HostGator, Bluehost, Skystra, SiteGround, A2, GreenGeeks, etc.). You'll need:
   - A domain name (your existing one or a new one)
   - PHP 8.0 or newer enabled (cPanel's "MultiPHP Manager" or "PHP Selector")
   - The ability to create a MySQL database and database user
   - The ability to upload a PHP file via cPanel File Manager or FTP
3. **A new email address for site notifications** — something like `webmaster@yoursociety.org` works well. Doesn't have to be new, but it should be one you actually check.
4. **A coffee, a notebook, and a willingness to take it one step at a time.**

If any of the above doesn't apply or you're unsure, stop and email us before starting. It's much easier to answer a question now than to undo a half-finished migration.

---

## What gets migrated

SocietyPress can import the standard ENS member export. Here is what comes across cleanly, what comes across with some translation, and what doesn't come across at all.

### Migrates cleanly

These fields map one-to-one and arrive in SocietyPress exactly as they were in ENS:

- Name (prefix, first, preferred, middle, maiden, last, suffix)
- Membership status (active / inactive / lapsed)
- Join date
- Birth date (we combine ENS's separate year/month/day fields into one date)
- Primary address (street, city, state, postal code, country)
- Phone (primary and cell)
- Email
- Website
- Seasonal/alternate address (and the months you use it)
- Gender
- Email preferences (general / events / newsletter)
- Member directory visibility (whether your name, address, phone, email, photo show in the public directory)
- Administrative notes (imported as the first note on the member record)
- ENS Member Record ID (kept as your member number so cross-reference still works)

### Migrates with some translation

- **Country** — ENS stores free-form country names ("United States", "USA", "U.S.A."); SocietyPress normalizes these to standard two-letter codes ("US"). The importer handles the common variants.
- **Member Active** — ENS uses Yes/No; SocietyPress uses active/inactive/lapsed. The import maps Yes → active, No → inactive. If you want some "No" rows treated as "lapsed" (for renewal nudging), you can update those after import.
- **Joint members** — ENS treats spouses as a single record with "Joint" fields. SocietyPress can either keep them combined or split them into two linked household members. You choose during the import.

### Doesn't migrate (and why)

These fields exist in ENS but have no equivalent in SocietyPress, so they'll be skipped or stored as a note:

- **File Name, Contact, Login, Login Count, Last Login Date** — ENS's internal bookkeeping fields. SocietyPress generates its own equivalents.
- **Image Filename** — ENS stores member photos by filename only; the actual files are in a separate folder. We can pull these across as a separate step if you have the photo files on hand. (Skip for now if you don't.)
- **Toll Free Phone, International Phone, Preferred Phone, Fax, Business Phone, Alt. International Phone, Alt. Preferred Phone, Alt. Email** — extra phone/email slots. Most societies have used only one or two of these, so the importer keeps the primary phone/email and writes the rest into the member's notes.
- **Quarterly, Volunteering?, Joint Member** — Yes/No flags. Volunteering preference can be re-collected via the Volunteers module after migration.
- **Your Skills, Your Interests, Your Education** — free-form text fields. The importer combines these into a single "About this member" note.
- **Surname Inquiry** — ENS's surname-research feature. SocietyPress tracks surnames inside the Records module instead.
- **Last Updated By, Last Updated Date** — audit metadata. SocietyPress starts a fresh audit history at import time.
- **Deceased** — if a record is marked deceased in ENS, the importer marks the SocietyPress member as inactive and adds a "Deceased per ENS export" note.

If you have data in fields the importer would skip and you want it preserved, **let us know before you import** — we can usually add a one-time mapping for your society's specific situation.

---

## Step 1 — Export your data from ENS

1. Log in to your ENS administrator panel.
2. Find the **Membership** or **Members** section. The exact menu name varies by ENS version.
3. Look for an **Export** or **Download CSV** button. (If your ENS panel doesn't show one, contact ENS support and ask for a "full member export." It's part of your account.)
4. Save the CSV file somewhere you'll find it again. A folder on your Desktop named something like `Society Migration` is fine.
5. While you're in ENS, also export anything else you can:
   - Events / calendar
   - Newsletter archive (PDFs)
   - Library/holdings catalog (CSV if available)
   - Photo galleries (download the images)
   - Records (cemeteries, obituaries, etc., if you have them)

Open the member CSV in a spreadsheet program (Excel, Numbers, LibreOffice) and **sanity-check it**: do you see your member rows, with names and emails and addresses? If yes, you're good. If the file is empty or looks scrambled, ask ENS to re-export it before continuing.

---

## Step 2 — Set up SocietyPress

You have two paths. Pick the easier one.

### Path A — One-click installer (recommended)

This is the fastest path for most societies.

1. In your web browser, go to: `https://getsocietypress.org/sp-installer.php`
2. Right-click the page (or `File → Save Page As`) and save it as `sp-installer.php`. **Don't rename it.**
3. Log in to your hosting account's cPanel.
4. Open **File Manager** and navigate to your domain's `public_html` folder.
5. Upload `sp-installer.php` into `public_html`.
6. Open your web browser and visit `https://yourdomain.org/sp-installer.php` (replace `yourdomain.org` with your actual domain).
7. The installer will check your server, then ask you a few questions:
   - **Database** name, username, password, host (you create these in cPanel's "MySQL Databases" tool first)
   - **Site name** (your society name)
   - **Administrator** username, email, and password (you'll use these to log in)
   - **Membership period** (annual / rolling / lifetime)
8. Click **Install**. The installer downloads WordPress, downloads SocietyPress, configures the database, creates your admin account, and deletes itself when done.
9. After about two minutes you'll be logged into your new SocietyPress site.

### Path B — Manual install

If your host doesn't allow the one-click installer (rare), follow the standard WordPress install instructions, then download the SocietyPress bundle from `https://getsocietypress.org/downloads/societypress-latest.zip` and unzip it into your `wp-content/` folder so that `societypress/` lands in `wp-content/plugins/` and the themes land in `wp-content/themes/`. Activate the plugin and the SocietyPress theme from the WordPress admin.

---

## Step 3 — Configure SocietyPress

After install, you'll see the **Setup Wizard**. It walks you through three short pages:

1. **Organization information** — name, address, phone, email, website
2. **Membership configuration** — tier names, dues amounts, expiration policy
3. **Appearance** — pick a theme (Heritage, Coastline, Prairie, Ledger, or Parlor — see the theme picker for previews) and your colors

Take five minutes to do this even if you're eager to import members. The setup wizard puts the right defaults in place so the import has somewhere to land.

---

## Step 4 — Import your members

1. In the SocietyPress admin sidebar, go to **Members → Import**.
2. Click **Choose file** and select the ENS CSV you exported in Step 1.
3. SocietyPress will read the column headers, recognize the ENS format, and show you a preview:
   - How many rows it found
   - The first three rows mapped to SocietyPress fields
   - Any warnings (missing required fields, duplicate emails, etc.)
4. Review the preview carefully. If something looks wrong (a name in the wrong column, addresses cut off), fix the CSV in your spreadsheet, save it, and re-upload.
5. Choose your **joint member behavior**:
   - **Combine** — keep ENS's joint-member rows as a single SocietyPress member with both names. Simpler.
   - **Split** — create two linked household members. More accurate but creates more records to manage.
6. Click **Import**.
7. Watch the progress bar. For a society with 200 members, the import takes about 30 seconds. For 2,000 members, about five minutes.
8. When it finishes, you'll see a summary:
   - How many members were imported
   - How many were skipped (and why — usually duplicate emails)
   - A link to download an "import log" CSV listing every action

---

## Step 5 — Verify the import

This is the most important step. Don't skip it.

1. Go to **Members → All Members**. Confirm the total count matches what you exported.
2. Open three or four random members and check that their data is there:
   - Name, address, phone, email — correct?
   - Membership status — correct?
   - Join date — correct?
   - Notes — anything from ENS that the importer noted as "no direct field"?
3. Open your **own** member record and check it carefully. You're more likely to notice errors on data you know intimately.
4. Search for a member you remember by name. Does the search find them?
5. Visit the public-facing **Member Directory** page. Are the right members showing? Are privacy preferences honored?

If anything is wrong, you have three options:
- **Fix the CSV** and re-import (the importer can either skip duplicates or update existing members — you choose)
- **Fix individual records** by hand in the SocietyPress admin
- **Roll back** by going to **Members → Import** → **Recent Imports** → **Undo this import**

---

## Step 6 — Import your other data

Same general process for the rest:

- **Events** — go to **Events → Import** if you have an ENS calendar export, or just create your upcoming events fresh
- **Library catalog** — go to **Library → Import**. SocietyPress accepts a generic CSV; if your ENS export has odd column names, the importer lets you map them
- **Newsletters** — go to **Newsletters → Add New** and upload your back issues as PDFs, one at a time (or use bulk upload if you have many)
- **Photos** — go to **Photos & Videos → Folders** to recreate your gallery structure, then upload images via the WordPress media library
- **Records** — go to **Records → Import**. Records are usually the messiest data; expect to spend some time on these

You don't have to do all of this on the same day. Members and the front page are the priorities — everything else can roll in over the following weeks.

---

## Step 7 — Cut over

When you're ready to make SocietyPress your live site:

1. **Decide on your domain.** If you're keeping the same domain that ENS hosts, you'll need to point your DNS at your new host. Your hosting provider has instructions specific to where your domain is registered. This change can take up to 48 hours to propagate.
2. **Set up email forwarding** if your domain's email goes through ENS. cPanel's **Email Accounts** tool replaces ENS's email handling.
3. **Notify your members** before the cutover. A short email a week ahead of time, then again the day of, prevents confusion. Sample text:

   > *We're moving our website to a new platform. On [date], the site will briefly look different and you may need to log in again. Your membership and account history are coming with us — nothing is being lost. If you can't log in after the move, click "Forgot password" on the login page and a reset link will be emailed to you.*

4. **Announce after** the cutover too. The first email to land at members from the new site is also a chance to welcome them to the new look.
5. **Don't cancel ENS for at least 30 days.** Keep it running in case you need to re-export anything you missed. Check the new site daily for the first week and weekly for the rest of the month.

---

## What to do if something goes wrong

- **Import looks wrong** — undo it, fix the CSV, re-import. The undo always works for the most recent import.
- **A member can't log in** — they need to use "Forgot password." ENS passwords don't transfer (we never see them — they're hashed in ENS's database). Every member sets a fresh password the first time they sign in to SocietyPress. This is a feature, not a bug.
- **The site looks broken** — go to **Appearance → Themes** and confirm SocietyPress (or one of the child themes) is the active theme. The default WordPress themes (Twenty Twenty-Three, etc.) won't work properly for a society site.
- **An email isn't going out** — go to **Settings → Email** and click "Send test email." If the test fails, your host probably blocks WordPress's default mail. Most hosts let you switch to SMTP via a setting; cPanel's mail tools also work.
- **A page is missing or 404s** — go to **Settings → Permalinks** and click "Save Changes" without changing anything. This rebuilds WordPress's URL routing.

---

## Getting help

- **Documentation:** `https://getsocietypress.org/docs`
- **GitHub issues** (for technical questions and bug reports): `https://github.com/charles-stricklin/SocietyPress/issues`
- **Email:** the contact form on getsocietypress.org

When you ask for help, tell us:
- What step you were on
- What you expected to happen
- What actually happened
- Any error messages, exactly as they appeared (a screenshot is great)

---

## A note on your old data

The ENS CSV you exported is the only complete snapshot of your member history that exists outside of ENS. **Make a backup of that CSV file and keep it somewhere safe** — a cloud folder, a thumb drive, an email attachment to yourself. After your subscription with ENS ends, you can't get it back.

The same goes for the SocietyPress site you're about to build. SocietyPress includes a one-click full-site export (Settings → Export) that produces a single ZIP containing everything: database, files, photos, and configuration. **Run it monthly.** Keep the last three months of backups. This is your insurance against everything from a forgotten password to a host going out of business.
