# Moving from ENS to SocietyPress

A step-by-step guide for genealogical and historical society webmasters who are leaving Easynet Solutions (ENS, sometimes branded EasyNetSites) and bringing their site to SocietyPress.

This guide assumes you are not a developer. You don't need to know PHP, MySQL, or anything about WordPress to follow it. You **do** need access to your current ENS site as an administrator, and access to a web hosting account where SocietyPress will live.

**Coming from ENS-Classic or the new ENS-Responsive?** Both paths are supported. ENS-Classic is being sunset (Blue Crab's official end date is 5/31/2027 with no guarantee of extension), and ENS-R societies who'd prefer to own their stack are also welcome — the export format is similar enough that the same CSV importer reads both. If you're still deciding which way to go, see the [side-by-side comparison](https://getsocietypress.org/comparison/) first.

**Want to see one done first?** [A migration, start to finish](ENS-MIGRATION-WALKTHROUGH.md) walks through a complete import on a real file — the numbers it produced, the two places it needed a hand, and why running the import twice is safe.

---

## Before you start

You'll want about **two to four hours** of uninterrupted time, plus a calm follow-up week to verify that nothing got lost.

Have these ready:

1. **Administrator access to your current ENS site** — the login that lets you export member data.
2. **A web hosting account** — almost any cPanel host works (Bluehost, Skystra, SiteGround, A2, GreenGeeks, etc.). You'll need:
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
- Birth date (ENS's separate year, month and day become one date; a birthday given without a year is kept as just the month and day)
- Primary address (street, city, state, postal code, country)
- Phone (primary and cell)
- Email
- Website
- Seasonal/alternate address (and the months you use it)
- Gender
- Email preferences (general / events / newsletter)
- Member directory visibility (whether your name, address, phone, email, photo show in the public directory)
- Administrative notes (imported as the first note on the member record)
- Membership Number (kept as the member's number, so "Member #284" in old newsletters still means the same person)
- ENS Member Record ID (kept behind the scenes so a second import recognizes everyone instead of duplicating them)

### Migrates with some translation

- **Country** — ENS stores free-form country names ("United States", "USA", "U.S.A."); SocietyPress normalizes these to standard two-letter codes ("US"). The importer handles the common variants.
- **Member Active** — ENS uses Yes/No; SocietyPress uses active, lapsed and inactive. The import maps Yes → active and No → lapsed. A member marked Deceased comes in as inactive, with Deceased set.
- **Couples** — ENS exports each spouse as their own row, and the two rows share a **Membership Tie ID**. SocietyPress keeps each person as their own member and links the pair into one household by that ID. Where a couple shares one email address, the second person's sign-in becomes a variant of it (`name+sp2@…`) that most mail services deliver to the same inbox.
- **Institutions** — a library or museum member arrives as an organization, named from the File Name column, when its Membership Type says so ("Organization", "Institutional", "Corporate" and similar). The import results tell you about any it couldn't tell apart.

### Needs a step of its own

Nearly every column in an ENS member export has a home in SocietyPress — the
[full column reference](#every-ens-column-and-where-it-lands) below says where
each one goes. Two things need more than the import:

- **Member photos.** ENS exports only each photo's file name (the Image
  Filename column); the pictures themselves are separate files. Upload them to
  `wp-content/uploads/sp-profile-photos/` on your new site — cPanel's File
  Manager or FTP — keeping their names exactly as ENS had them, odd punctuation
  and all. Then import your member file again. Re-importing updates the same
  members rather than adding them twice, and attaches every photo it can now
  find. The results screen tells you how many photos named in the file weren't
  found, with examples. You can do this the same day or weeks later.
- **Passwords.** They never transfer — ENS keeps them scrambled, as it should.
  The first time a member signs in to the new site, SocietyPress explains the
  change and emails them a link to choose a new password.

---

## Step 1 — Export your data from ENS

1. Log in to your ENS administrator panel.
2. Find the **Membership** or **Members** section. The exact menu name varies by ENS version.
3. Look for an **Export** or **Download CSV** button. (If your ENS panel doesn't show one, contact ENS support and ask for a full member export.)
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

## Decisions you'll make during the import

A few decisions worth making with intent rather than at the prompt. None is
irreversible — see [Backing out](#backing-out) — but it's faster to think them
through in advance than to redo work.

### 1. Lapsed members: bring them or leave them

ENS marks lapsed members Active = No, and they come in as **lapsed**. Bringing
them is usually the right call: it keeps their history, and lets you reach out
one last time through Blast Email with a "we noticed you lapsed" note.

If you'd rather not, either delete the No rows from the CSV in a spreadsheet
before importing, or import everyone and then delete them on **Members** —
filter by status Lapsed, tick them, and choose **Delete** from Bulk actions.

### 2. Member numbers

The ENS **Membership Number** becomes each member's number, so references in
old records ("Member #284") still work. SocietyPress doesn't number members on
its own, so if you clear that column before importing, your members will have
no number at all. Leave it unless you have a reason.

The ENS **Member Record ID** is separate. It's how a second import recognizes
the people it already brought in, so keep that column in the file every time.

### 3. Privacy

ENS stores each member's directory choices — whether their name, address,
phone, email and photo show — and the import brings those across person by
person. **Check them before going public.** Some societies have run for years
with loose ENS defaults, every member's home phone number visible to anyone.

The migration is a good moment to tighten up. Under **Settings → Directory**
you can switch off a column, such as phone or email, for the whole directory,
whatever each member chose. Members can change their own choices later in My
Account.

### 4. Cutover timing: parallel run or hard switch

Two options once the import is verified:

- **Parallel run** (1–2 weeks). Keep your ENS site live at its current URL. Build out SocietyPress at a temporary URL (`new.yoursociety.org` or `societypress.yoursite.org`). Members keep using ENS while you finish setting up. When you're ready, swap DNS — SocietyPress takes over the main URL, ENS becomes read-only. Lower risk, more dual-maintenance work.
- **Hard switch**. Schedule a maintenance window, do the cutover in one sitting, ENS goes dark immediately. Faster, more pressure on the day.

Most societies pick parallel run. Hard switch only makes sense if you have a clean cutover date (end of fiscal year, post-AGM) and an ENS contract that's expiring anyway.

---

## Step 4 — Import your members

[A migration, start to finish](ENS-MIGRATION-WALKTHROUGH.md) follows this step
screen by screen with a sample society, if you'd like to see it before you do it.

1. In the SocietyPress admin sidebar, go to **Members → Import**.
2. The first time, SocietyPress asks you to **add yourself as a member**. Do it,
   and use the email address your society has on file for you in ENS — it's how
   the import will recognize your own row.
3. Click **Choose File**, pick the ENS CSV you exported in Step 1, and click
   **Upload & Preview**.
4. Check the field mapping. Every ENS column should already be matched to where
   it belongs, with a sample value from your first member beside it so you can
   see the names are in the name boxes. The first five rows are shown underneath.
   If something looks wrong, fix the CSV in your spreadsheet and upload it again.
5. Leave **Update members already in the system from this file** ticked. It's
   what makes a second import safe.
6. Click **Run Import**.
7. SocietyPress shows you any row that matches an account already on the site.
   On a new site that's you; **Same person** is already chosen, so leave it and
   click **Continue import**. Your row then fills in the member record you made
   in step 2 instead of creating a second you.
8. Watch the progress bar. For a society with 200 members, the import takes
   about 30 seconds; for 2,000, about five minutes.
9. When it finishes, the summary shows:
   - how many members were imported, updated, linked (you) and skipped
   - which membership plans it created, and which names it matched onto plans
     you already had — set prices on any it created
   - any institutions it couldn't confirm, and any photos it couldn't find
   - a line for each row it had trouble with, and why

---

## Step 5 — Verify the import

This is the most important step. Don't skip it.

1. Go to **Members → All Members**. Confirm the total count matches what you exported.
2. Open three or four random members and check that their data is there:
   - Name, address, phone, email — correct?
   - Membership status — correct?
   - Join date — correct?
   - Their household, plan and member number — correct?
3. Open your **own** member record and check it carefully. You're more likely to notice errors on data you know intimately.
4. Search for a member you remember by name. Does the search find them?
5. Visit the public-facing **Member Directory** page. Are the right members showing? Are privacy preferences honored?

If anything is wrong, you have three options:
- **Fix the CSV** and import it again. Members it already brought in are updated, not duplicated.
- **Fix individual records** by hand in the SocietyPress admin.
- **Start the members over** — see [Backing out](#backing-out).

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
- **GitHub issues** (for technical questions and bug reports): `https://github.com/SocietyPressFoundation/SocietyPress/issues`
- **Email:** the contact form on getsocietypress.org

When you ask for help, tell us:
- What step you were on
- What you expected to happen
- What actually happened
- Any error messages, exactly as they appeared (a screenshot is great)

---

## What you might want to enable next

Once your members are imported and the front page looks right, these modules are typically the highest-leverage next steps. Each one is a setting toggle (Settings → Modules) — turn on what fits your society, leave the rest off.

- **First Families / Lineage Programs** — If your society recognizes descendants of historically significant ancestors (First Families of [your county], Pioneer Settlers, Civil War veterans descendants, etc.), enable Lineage Programs. Members apply through a public form, you review proofs in an admin queue, and approved members appear on a public roster with a printable certificate.
- **Picture Wall** — Member-submitted ancestor portrait gallery, distinct from regular event photo albums. Members upload a portrait + ancestor name + relationship; staff approve in a moderation queue.
- **Online Donations** — A complete `[sp_donate]` form with preset amounts, monthly / annual recurring giving, anonymous donations, in-honor-of dedications, and a "cover the processing fee" option. Stripe is wired end-to-end for one-time and recurring; PayPal handles one-time. Receipts include 501(c)(3) language if you've entered your EIN.
- **Online Voting** — Election ballots and bylaw amendments with tier-based eligibility. Replace the paper proxy or the SurveyMonkey workaround with something the board actually trusts.
- **Surname Research Database** — A members-only inquiry index where each member lists the surnames they're researching plus county, state, and year range. Visitors search with Soundex + Metaphone phonetic matching and contact the researching member through an anti-spam form.
- **Database Subscriptions** — A members-area panel that gateways the genealogy databases your society pays for (Ancestry, Fold3, FamilySearch affiliate, NEHGS). One-click handoff with members-only access controls.

These were all added in the run-up to the 2026 Texas State Genealogical Society conference; if you've evaluated SocietyPress before and these weren't there, they are now. See `https://getsocietypress.org/features/` for the full module list.

---

## A note on your old data

The ENS CSV you exported is the only complete snapshot of your member history that exists outside of ENS. **Make a backup of that CSV file and keep it somewhere safe** — a cloud folder, a thumb drive, an email attachment to yourself. After your ENS subscription ends, that CSV is the only copy you'll have.

The same goes for the SocietyPress site you're about to build. SocietyPress includes a one-click full-site export (Settings → Export) that produces a single ZIP containing everything: database, files, photos, and configuration. **Run it monthly.** Keep the last three months of backups. This is your insurance against everything from a forgotten password to a host going out of business.

---

## Every ENS column, and where it lands

You do not need this table to run a migration — the importer recognizes these
column headings on sight and fills the dropdowns in for you. It is here for the
society that wants to know exactly what happened to each field before it commits,
and for the treasurer who is asked six months later where a number came from.

Anything your export contains that is not listed below can still be brought
across: on the import screen, set its dropdown to **Custom field** and it is kept
against the member under its own name, searchable and included in every export.

**Name**

| ENS column | Becomes |
|---|---|
| First Name, Middle Name, Last Name | The member's name |
| Name Prefix, Name Suffix | Prefix and suffix |
| PreferredName | Preferred name |
| Maiden Name, UseMaiden | Maiden name, and whether to show it |
| Address Label Name | Label name, used for mailings |

**Membership**

| ENS column | Becomes |
|---|---|
| Membership Number | Member number |
| Membership Plan | Membership plan |
| Membership Type | Membership type |
| Member Join Date | Join date |
| Expiration Date | Renewal date |
| Member Active | Status — active or lapsed |
| Lifetime | Lifetime member |
| Deceased | Deceased |
| Max Members | Seats on an organizational membership |
| Membership Tie ID | Household — this is what joins spouses to each other |
| Acct. Primary | Which of a couple is the primary account |
| Joint Member, Email of Joint, Phone of Joint | The second person on a joint membership |
| File Name | Organization name, for institutional members |

**Contact**

| ENS column | Becomes |
|---|---|
| Email, Alt. Email | Email and alternate email |
| Telephone, Cell Phone, Business Phone, Fax | Phone, mobile, work phone, fax |
| Toll Free Phone, International Phone, Preferred Phone | Kept as their own fields |
| Address 1, Address 2, City, State / Province, Postal Code, Country | Address |
| Website | Website |

**Seasonal address**

ENS calls this the alternate address. SocietyPress calls it seasonal, and uses
the same begin and end dates ENS does, so a snowbird's mail follows them without
anybody editing anything.

| ENS column | Becomes |
|---|---|
| Alternate Address 1, Alt. Address 2 | Seasonal address |
| Alt. City, Alt. State / Province, Alt. Postal Code, Al. Country | Seasonal city, state, postal code, country |
| Use Seasonal Address | Whether it is in use |
| Use Alt. Info Month Begin, Alt. Contact Day Begin | When it starts |
| Use Alt. Info Month End, Alt. Contact Day End | When it ends |
| Alt. Phone, Alt. International Phone, Alt. Preferred Phone | Seasonal phone numbers |

**What the member agreed to receive**

These are consent settings, and they come across exactly as they stand in ENS.
Nobody is opted into anything by being migrated.

| ENS column | Becomes |
|---|---|
| General Email, Event Email, Newsletter Email | Email preferences |
| Quarterly, Mail Publication / Mail Publications | Whether they take the journal in print |
| Surname Inquiry | Willing to be contacted about surname research |

**What the member agreed to show**

| ENS column | Becomes |
|---|---|
| Mbr. List - Show Name / Address / Phone / Email / Photo | Directory visibility, field by field |

**About the member**

| ENS column | Becomes |
|---|---|
| Birth Year, Birth Month, Birth Day | Date of birth — or just the month and day, when the year is blank or "0000" |
| Gender | Gender |
| Your Skills, Your Interests, Your Education | Skills, interests, education |
| Volunteering? | Willing to volunteer |
| Contact | Contact flag |
| Image Filename | The member's photo |

**Money**

| ENS column | Becomes |
|---|---|
| Amount Paid, Date Paid, Payment Type | A payment record against the member |
| Donation | Recorded as a donation |
| Comment | A note on the payment |

**Administrative**

| ENS column | Becomes |
|---|---|
| Login | Their WordPress username |
| Login Count, Last Login Date | Kept for reference |
| Administrative Notes | Staff notes, never shown to the member |
| Last Updated By, Last Updated Date | Kept, and used by the overwrite rule below |
| ENS Member Record ID | Kept, so a second import can recognize the same person |

### Two of these do more than they look like

**Membership Tie ID** is what makes a couple a couple. If you strip it out of the
CSV, every spouse arrives as an unrelated individual and no amount of later
tidying will reliably pair them up again. Leave it in even if you intend to split
couples into separate members.

**ENS Member Record ID** is how a second import knows it is looking at somebody
it has already seen. Keep it and you can re-import the same file safely. Remove
it and the importer falls back to matching on name and email, which is close but
not the same thing.

**Last Updated Date** matters only if you switch on the overwrite option, which
replaces a member's whole record when the file is newer than what is on the site.
That option is for a complete roster and nothing else — on a partial file it will
blank out every field the file happens not to carry.

---

## Backing out

Four levels, smallest first. Use the smallest one that covers what went wrong.

### Import again

Most mistakes in an import are mistakes in the file. Fix the spreadsheet and
import it again: every member is matched by their ENS Member Record ID and
updated in place, so nothing is duplicated and nobody is lost. You can do this
as many times as you need.

### Clear the members and start again

**Members → Delete All Others** removes every member except you and the site's
other administrators, so you can import a clean copy. It asks you to confirm,
and it can't be undone, so take a backup first (below) if there's anything on
the site you'd want back.

### Restore the site

**Settings → Export & Backup.**

Take a full export *before* the first import and keep it. It is a single ZIP
holding the database, the files, the photos and the configuration. Restoring it
puts the site back exactly as it was, which is the right answer when something
other than the member import has gone wrong — a half-finished settings change, a
theme swap you regret, an import you undid but that left the site feeling wrong.

### Start over

Nothing about a migration is one-way. Your ENS export is a file on your disk; the
site is a WordPress install. If you get halfway through and decide the shape is
wrong, you can empty the members table and import again from the same CSV. The
only thing you cannot recover is time.

Do keep ENS running until you have looked at the imported site with your own eyes
and had one other person do the same. Overlapping for a month costs one month's
subscription and removes every reason to hurry.
