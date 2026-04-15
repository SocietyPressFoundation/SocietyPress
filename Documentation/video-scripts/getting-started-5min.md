# Getting Started with SocietyPress — 5-Minute Walkthrough

**Target length:** 5 minutes (approx. 750–850 words of spoken narration)
**Audience:** Society administrators evaluating or adopting SocietyPress
**Tone:** Plain-English, calm, practical. Not salesy. Not hyperactive.
**Format:** Screen recording with voiceover. Cursor movements should be
deliberate; pause briefly on every admin screen before interacting.

---

## Pre-flight checklist before recording

- [ ] Fresh WordPress install, no pre-existing SocietyPress data
- [ ] `sp-installer.php` staged in the hosting account's `public_html`
- [ ] Browser window sized to 1400×900 (consistent crop)
- [ ] Browser zoomed to 110% (admin UI reads cleanly on video)
- [ ] Bookmarks bar hidden
- [ ] No other tabs open
- [ ] Have one CSV ready with ~5 fake member rows for the import demo
- [ ] Admin account credentials noted but not displayed on screen

---

## Scene 1 — Cold open (0:00–0:15)

**ON SCREEN:** Fresh hosting control panel, open to File Manager,
`sp-installer.php` visible in the list.

**NARRATION:**
> SocietyPress is a free, open-source platform for genealogical and
> historical societies. This video walks through installation, first-time
> setup, importing a member list, and publishing your first event — all
> in under five minutes.

*(Beat. Give people time to register what they're watching.)*

---

## Scene 2 — Install (0:15–1:15)

**ON SCREEN:** Browser address bar. Type `yourdomain.org/sp-installer.php`
and hit enter. Show the installer's landing page.

**NARRATION:**
> You start by uploading one file to your web host — `sp-installer.php`,
> which you got from getsocietypress.org/download/. Then you point your
> browser at it.

*(Click through the installer's welcome screen.)*

> The installer checks your server, asks for a few details, and handles
> the rest. Database credentials — you create those in cPanel's MySQL
> tool first. Then your society's name. Then the administrator account
> you'll use to log in.

*(Fill in the form. Scroll naturally. Don't rush.)*

> The install takes about two minutes. It downloads WordPress, downloads
> the SocietyPress plugin and themes, configures the database, creates
> your admin account, and cleans up after itself.

*(On completion screen)*

> When it's done, you're logged in automatically.

---

## Scene 3 — Setup wizard (1:15–2:15)

**ON SCREEN:** The 3-step Setup Wizard as it appears on first login.

**NARRATION:**
> The setup wizard runs once on your first login. Three steps.

*(Show Step 1: Organization Information)*

> Step one: your society's name, address, contact email, and phone
> number. This is what appears on your public site's footer, on
> membership renewal emails, on invoices. Fill it in once, use it
> everywhere.

*(Show Step 2: Membership Configuration)*

> Step two: membership tiers. How much do you charge for a regular
> membership? A family membership? A lifetime membership? What's the
> term — one year, calendar year, rolling? Set it once and the system
> handles renewals automatically.

*(Show Step 3: Appearance)*

> Step three: the look. Pick a child theme — Heritage, Coastline,
> Prairie, Ledger, or Parlor. Pick your primary and accent colors.
> Upload your logo if you have one. You can change all of this later,
> but it's nice to land somewhere that feels like yours from the start.

*(Click "Finish" and land on the SocietyPress Dashboard)*

---

## Scene 4 — Import members (2:15–3:15)

**ON SCREEN:** SocietyPress Dashboard. Click "Members" in the sidebar,
then "Import."

**NARRATION:**
> Most societies have a member list somewhere — in a spreadsheet, in
> an ENS export, in whatever platform they're leaving. SocietyPress
> imports from a standard CSV.

*(Click "Choose file," select the prepared CSV)*

> Upload the CSV. SocietyPress reads the column headers, shows you a
> preview of the first three rows mapped to its fields, and flags any
> warnings — missing email addresses, duplicate phone numbers, anything
> that looks off.

*(Point to the preview)*

> Organizational members are auto-detected from the name field.
> Duplicates are flagged by email. Joint members — husband-and-wife
> records that were combined in your old system — you choose whether
> to keep them combined or split them into two linked household
> members.

*(Click "Import")*

> For a 200-member society, the import takes about 30 seconds. Two
> thousand members takes about five minutes.

*(Show completion screen)*

> When it finishes, you get a summary — how many imported, how many
> skipped and why — and every new member is searchable immediately.

---

## Scene 5 — First event (3:15–4:15)

**ON SCREEN:** Sidebar: Events → Add New.

**NARRATION:**
> Publishing your first event takes under a minute.

*(Fill in event title: "Monthly Meeting — May 2026")*

> Event title. Date. Location. A short description of what'll happen.

*(Pick an existing category or create one on the fly)*

> Assign a category. Categories show up color-coded on the public
> calendar — meetings one color, workshops another, cemetery walks
> another.

*(Toggle "Online registration" on)*

> Turn on online registration if members should be able to RSVP.
> Capacity limit? Optional. Waitlist? Optional. Stripe or PayPal for
> a ticketed event? Optional.

*(Click "Publish")*

> Publish, and the event is live on your public calendar, members
> can register, and a reminder email goes out 24 hours before the
> event automatically.

---

## Scene 6 — Recap and wrap (4:15–5:00)

**ON SCREEN:** Switch to a browser tab showing the public homepage of
the site you just built. Upcoming event visible. Member directory
populated. Footer shows society info.

**NARRATION:**
> That's a working society website, built from nothing in about five
> minutes. Members imported. First event published. Society branding
> applied.

*(Scroll down slowly so viewers see the full page)*

> There's a lot more SocietyPress can do — newsletter archive, library
> catalog, genealogical record collections, donation tracking,
> volunteer management, online balloting, photo galleries. The
> documentation at getsocietypress.org/docs/ walks through each one.

*(End card)*

> Free. Open source. Yours. Download at getsocietypress.org.

---

## Post-production notes

- **Captions:** burn in throughout. Many viewers are in quiet society
  offices or on mobile muted.
- **Chapters:** mark the 5 scenes as chapters. YouTube/Vimeo pick them
  up, and viewers often come back to one specific part.
- **End card:** 5 seconds. "getsocietypress.org" centered, big.
- **No music:** the content is informational, not emotional. Adding a
  royalty-free soundtrack mostly distracts. If you must, keep it
  ambient and under 15% volume.
- **Thumbnail:** still frame from Scene 3 (Setup Wizard Step 3 with
  the child theme picker visible). It tells viewers what they're
  getting.

---

## Follow-up videos (not this script)

Once the 5-minute overview is published, these are the logical next
videos for the channel:

1. **Importing from ENS — 10 minutes.** Deeper dive on the 73-field CSV
   mapping, joint-member handling, verifying data after import.
2. **Setting up membership tiers — 8 minutes.** Multi-tier societies,
   grace periods, family memberships, lifetime members.
3. **Building your homepage with the page builder — 12 minutes.** Walk
   through 8–10 widgets being added and reordered.
4. **Your first newsletter issue — 6 minutes.** Upload PDF, set
   member-only access, verify on a member account.
5. **Tour of the reports dashboard — 7 minutes.** Annual reports,
   donation reports, expiring memberships, demographics.
