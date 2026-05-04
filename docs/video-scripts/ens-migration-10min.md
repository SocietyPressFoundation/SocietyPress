# Migrating from ENS to SocietyPress — 10-Minute Deep Dive

**Target length:** 10 minutes
**Audience:** Society administrators or webmasters leaving Easynet Solutions
(ENS / EasyNetSites) for SocietyPress. Assumes they've already watched
(or will watch) the 5-minute Getting Started video.
**Tone:** Patient, reassuring, specific. This is the video a nervous
webmaster watches at midnight before committing to a weekend migration.

---

## Pre-flight checklist before recording

- [ ] Fresh SocietyPress install (ideally on a subdomain you'll discard)
- [ ] An actual ENS member CSV export (scrubbed of real member data if public)
- [ ] 2 test member rows in ENS export that demonstrate joint members
- [ ] 1 row that's missing an email (to show duplicate handling)
- [ ] Browser zoomed to 110%, window 1400×900
- [ ] Split-screen setup: ENS admin on left, SocietyPress admin on right,
      for the comparison segments

---

## Scene 1 — Cold open (0:00–0:30)

**ON SCREEN:** ENS admin panel on the left, SocietyPress admin on the right.

**NARRATION:**
> If your society is running on ENS — Easynet Solutions, sometimes called
> EasyNetSites — and you're thinking about moving to SocietyPress, this
> is a full walkthrough of how that migration actually works. Ten
> minutes, no skipped steps, no hand-waving.

*(Beat.)*

> You'll see the CSV export from ENS. You'll see the import into
> SocietyPress. You'll see how joint members get handled, how
> duplicates are caught, and how to verify everything landed right
> before you commit to cutting over.

---

## Scene 2 — What transfers, what doesn't (0:30–1:45)

**ON SCREEN:** Split screen with a three-column visual: Transfers Cleanly /
Needs Translation / Doesn't Transfer. Could use the same structure as the
`/ens-migration/` page.

**NARRATION:**
> Before we export anything, here's the honest picture of what crosses
> the bridge.

*(Move through the three columns one at a time.)*

> **Transfers cleanly**: names, addresses, phones, emails, membership
> status, join dates, birth dates, directory privacy preferences,
> administrative notes, and your ENS member record IDs — those stay as
> your member numbers so cross-references still work.

> **Needs a bit of translation**: country free-text gets normalized to
> ISO codes. Yes/No active flags become active/inactive/lapsed. Joint
> members — husband-and-wife records ENS stored as one row — you
> choose: keep combined or split into linked household members.

> **Doesn't transfer**: member passwords, by design. ENS stores them
> hashed, nobody can decrypt them, and that's fine — every member sets
> a fresh password via "Forgot password" on first login. Photo files
> come across in two steps: the CSV has filenames but the images need
> a separate upload. ENS's internal audit metadata doesn't follow you,
> either.

> Nothing about this list should be surprising. If something here *is*
> surprising for your society's specific ENS setup, pause, email us
> before you migrate, and we'll talk through the edge case.

---

## Scene 3 — Export from ENS (1:45–3:30)

**ON SCREEN:** ENS administrator panel. Navigate to Membership → Export.

**NARRATION:**
> Step one: log into ENS as administrator. The menus vary slightly by
> ENS version, but you're looking for Membership or Members, and inside
> it, Export or Download CSV.

*(Click through. Show the export button.)*

> Click Export. You'll get a CSV file with about 73 columns — the full
> ENS export. Save it somewhere you'll remember. A Desktop folder named
> "Society Migration" works fine.

*(Open the CSV in Excel or Numbers.)*

> Open the file and sanity-check it. Do you see your member rows?
> Names, addresses, emails populated? Good. If the file looks empty or
> garbled, stop here and ask ENS support to re-export. It's part of
> your subscription.

*(Scroll through the CSV briefly.)*

> While you're in ENS, also grab: events calendar exports, newsletter
> PDFs, any library catalog you've built, and photo galleries if you
> have them. We'll bring those across in later steps.

---

## Scene 4 — Install SocietyPress (3:30–4:30)

**ON SCREEN:** Browser address bar, typing the sp-installer URL.

**NARRATION:**
> Next, install SocietyPress on your new hosting account. We cover this
> in detail in the 5-minute Getting Started video — if you haven't
> watched that, pause and go watch it now, then come back. For this
> video I'll fast-forward.

*(Time-lapse the installer: landing, form, completion.)*

> About two minutes later, you're logged into a fresh SocietyPress
> install, and the 3-step setup wizard starts. Fill in your society's
> name, your membership tiers, and your colors. Finish. Now you're at
> the Dashboard.

---

## Scene 5 — Prepare for import (4:30–5:30)

**ON SCREEN:** SocietyPress admin. Navigate to Members → Settings.

**NARRATION:**
> Before you import, let's configure a few things so the import has
> somewhere sensible to land.

*(Point to membership tiers setting.)*

> You set up your tiers in the wizard already, but double-check them
> here. Your ENS export has a tier name per row, and SocietyPress
> matches on that text. If ENS has "Individual" and you set up
> "Standard" in the wizard, the import will create a new tier called
> "Individual" — not what you want. Adjust tier names now so they line
> up.

*(Point to year/date-format settings if applicable.)*

> Check your date format. ENS exports dates in YYYY-MM-DD, which
> SocietyPress reads natively, so you usually don't need to change
> anything here.

---

## Scene 6 — Run the import (5:30–7:15)

**ON SCREEN:** Members → Import screen.

**NARRATION:**
> Now the main event. Members, Import.

*(Click "Choose file," select the ENS CSV.)*

> Upload the CSV. SocietyPress reads the column headers, recognizes the
> 86-column ENS format, and shows you a preview.

*(Point to the preview panel.)*

> Number of rows it found. First three rows mapped to SocietyPress
> fields so you can eyeball it. Any warnings — rows missing required
> fields, duplicate emails, malformed data.

*(Show the joint-member choice.)*

> Joint member behavior — this is the one real decision. Combined
> keeps husband-and-wife on one row with both names. Split creates two
> linked household members, one record each, so each person can have
> their own email, their own login, their own directory listing. My
> recommendation: split, if your members have distinct email addresses.
> Combined, if they share one.

*(Click Import.)*

> Click Import. Progress bar.

*(Time-lapse the import.)*

> For a 200-member society, 30 seconds. Two thousand members, about
> five minutes. When it finishes, you get a summary: how many
> imported, how many skipped, and why.

*(Show the import log link.)*

> Download the import log CSV. Every row logs whether it was imported,
> skipped-as-duplicate, or errored. Keep this file — it's your audit
> trail for the migration.

---

## Scene 7 — Verify (7:15–8:30)

**ON SCREEN:** Members → All Members.

**NARRATION:**
> This is the step nobody skips and everyone thinks about skipping.

*(Show the full member count.)*

> First, member count. Does it match what you exported? If you
> exported 847 rows and the import says 845, find the two missing.
> The import log tells you why.

*(Open a few random members.)*

> Open three or four random members. Name spelled right. Address
> populated. Email correct. Membership status — active, inactive,
> lapsed — matches ENS. Join date correct. Notes carried across.

*(Open your own record — use a fake "Charles" record if not demoing on
real data.)*

> Open your own member record. You know your own data better than
> anyone's, and you'll spot errors on your own record that you'd miss
> elsewhere.

*(Do a search for a member by name.)*

> Search for a member by name. Does it find them? Good. Visit the
> public Member Directory. Are the right members showing? Are
> privacy preferences honored?

---

## Scene 8 — Bring the rest (8:30–9:30)

**ON SCREEN:** Series of short clips — Events Import, Library Import,
Newsletters Add New.

**NARRATION:**
> Members are usually 80% of the migration pain. Everything else is
> simpler.

*(Show Events → Import.)*

> Events: if you have an ENS calendar export, run it through Events,
> Import. Otherwise, create your upcoming events fresh — most
> societies don't bother importing past events.

*(Show Library → Import.)*

> Library: Library, Import. SocietyPress takes a generic CSV and lets
> you map the columns if your ENS export uses odd names.

*(Show Newsletters → Add New.)*

> Newsletter PDFs: Newsletters, Add New. Upload each PDF individually
> or use bulk upload if you have a lot. Covers auto-generate from the
> first page.

*(Brief on photos and records.)*

> Photos: recreate your gallery structure under Photos and Videos,
> then upload images via the Media Library. Records collections:
> Records, Import — these are usually the messiest data, so budget
> time for them.

---

## Scene 9 — Cut over (9:30–10:00)

**ON SCREEN:** Domain registrar DNS panel.

**NARRATION:**
> When you're ready to make SocietyPress live, point your domain at
> the new host. Your hosting provider has instructions specific to
> your domain registrar.

*(Brief pause.)*

> Two things before you cut over. One: email your members a week
> ahead. Short note explaining the move, reassuring them their
> membership and account history are coming with them, and telling
> them to click "Forgot password" if they can't log in after the
> switch. Two: don't cancel ENS for at least 30 days. Keep it running
> as insurance in case you need to re-export anything.

*(End card)*

> That's the migration. Full guide at getsocietypress.org/ens-migration
> if you want the written version. Good luck, and welcome to
> SocietyPress.

---

## Post-production notes

- **Split-screen moments** (Scene 1, Scene 2) need careful cropping —
  both sides visible and readable at 720p.
- **Scrub CSV data** before recording. Fake names and fake emails.
- **Chapter markers** at each scene boundary.
- **Text callouts** over the CSV preview screen in Scene 6 — highlighting
  "Name" column, "Active Y/N" column, and "Joint" column with arrows.
- **Duration flex:** if a scene runs long, Scene 8 (bringing the rest)
  can be trimmed to 45 seconds without losing clarity.
