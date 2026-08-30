# A migration, start to finish

The [migration guide](ENS-MIGRATION-GUIDE.md) tells you what to do. This
page shows you what actually happens when somebody does it — one complete
ENS import, run on a clean SocietyPress install, with the real numbers and
the two places it didn't go quite as expected.

Nothing here is a mockup. Every figure below came out of an actual import.

---

## The society

Cedar County Genealogical Society is invented, and so is every person in
its member file. It exists so this walkthrough can show you real behaviour
without publishing anybody's real membership list.

Its ENS export holds **22 members in 87 columns** — the standard ENS
member export plus the dues and payment block that societies collecting
money through ENS will also have. Twenty-two is small on purpose. It is
big enough to contain every awkward case and small enough that you can
check the result by eye, which is exactly what you want the first time you
try this.

The file was built to be difficult in the ways real society files are
difficult:

| What's in it | Why it's there |
|---|---|
| 10 ordinary individual members | The easy case, and most of any real file |
| 3 married couples, 6 rows | Each couple shares one **Membership Tie ID** — this is what makes them one membership rather than two |
| 1 snowbird | Winters in Florida, so the row carries a full seasonal address and the months it applies |
| 1 life member | Paid once in 2004, no expiration date, joined 1994 |
| 1 lapsed member | Stopped renewing in 2025 |
| 1 deceased member | Marked deceased, family asked that the surname file stay open |
| 2 institutional members | A library and a museum, each with a named contact person |

---

## What happened

The import was run the way an administrator would run it: upload the file,
accept the column mapping SocietyPress suggests, go. No hand-editing of
the CSV, no custom mapping.

```
imported: 22    updated: 0    skipped: 0    errors: none
```

All 22 rows landed on the first pass. Nothing was skipped and nothing
errored. Here is what the site looked like afterward.

**The couples became couples.** All three pairs arrived sharing a
household, matched on their Membership Tie ID:

```
2001 — Harold Whitmore + Greta Whitmore
2002 — Silas Duplantier + Ramona Duplantier
2003 — Theodore Ashby + Winifred Ashby
```

This is the single most important thing to check after your own import. If
your couples arrive as six unrelated people instead of three memberships,
your Tie ID column didn't come across, and it is far easier to fix that now
than after a renewal season.

**Statuses sorted themselves out.** 20 active, 1 inactive, 1 lapsed —
matching the file exactly. The deceased member came across marked deceased
and set inactive, with the administrative note preserved.

**The seasonal address survived intact**, including the dates it applies:

```
Gustav Lindholm — Naples, FL — 11-01 through 04-15
```

That means the Quarterly gets mailed to Florida in the winter without
anybody having to remember to change it.

**Three membership tiers were created automatically** from the plan names
in the file: Joint, Life, and Institutional.

---

## Two things that surprised us

Both of these are worth knowing before you import your own file. Neither
is hard to work around once you know.

### Your tier list will have duplicates

SocietyPress ships with five membership tiers already defined —
Individual, Joint/Family, Student, Lifetime, Honorary. The import creates
tiers for whatever plan names your file contains, and it does not try to
match them against the ones already there.

So a file with plans called "Joint" and "Life" produces this:

```
Individual · Joint/Family · Student · Lifetime · Honorary · Joint · Life · Institutional
```

Eight tiers where you wanted four. "Joint" and "Joint/Family" mean the same
thing; so do "Life" and "Lifetime". Nothing is broken and no member is on
the wrong plan, but your tier list needs a tidy-up after import: rename the
one you want to keep, move any members off the duplicate, and delete it.

The safest time to do this is immediately, before members start renewing
against a tier you're about to remove.

### An institution needs the word "Organization"

This one is quieter, and it can lose data.

Institutional members — libraries, museums, partner societies — usually
appear in an ENS export as a contact person's name with the institution's
name in the **File Name** column. SocietyPress will store that institution
as a proper organizational member, but only when the row's **Membership
Type** column says the literal word `Organization`.

The reason is defensive: in most ENS exports, File Name holds the member's
own surname for ordinary individuals. If SocietyPress treated every
populated File Name as an institution, it would turn your entire membership
into organizations. So it requires the Membership Type column to confirm it.

The catch is that societies rarely use the word "Organization". They write
"Institutional", or "Library", or "Affiliate". When they do, the row imports
as an individual and **the institution's name is silently discarded** — no
error, no warning, nothing in the log.

Both of these were in the test file, and the difference is stark:

| File Name | Membership Type | Result |
|---|---|---|
| Fairhaven Public Library | `Organization` | Organizational member, name preserved |
| Marengo Township Historical Museum | `Institutional` | Individual member, museum name lost |

**Fixed in 1.5.4.** The import now recognises the ordinary ways of saying the
same thing — "Institutional", "Corporate", "Company", "Non-Profit", "Agency"
and a few more. Re-running the same file afterwards filed the museum correctly
without a single edit to the spreadsheet:

```
Fairhaven Public Library          Organization    → organizational member
Marengo Township Historical Museum Institutional  → organizational member
```

The list is deliberately conservative, though. "Library", "Museum", "Society"
and "Church" are *not* treated as organizations, because a society might just
as easily use one of those words for an individual tier — and turning your
whole membership into organizations would be a much worse mistake than the one
being avoided.

So if your society uses a word of its own, the import will still file that row
as an individual. What it will no longer do is stay quiet about it: the results
screen now tells you how many rows had an organization name it didn't use, and
which Membership Type values it saw. Correct those rows and import again.

---

## Running it twice is safe

This is the part most people are afraid of, so it is worth stating plainly:
**importing the same file a second time does not duplicate anybody.**

The file was re-imported, unchanged except for one corrected Membership
Type value:

```
imported: 0    updated: 22    skipped: 0
member count: 22 (unchanged)
```

Twenty-two updates, zero new records, same total. SocietyPress matches each
incoming row against members it already has using the **ENS Member Record
ID** column, which is stable across exports. That is what makes a second
import an update rather than a duplication.

This matters more than it sounds. It means you can import, discover
something wrong, fix it in the spreadsheet, and import again — as many
times as you need — without cleaning up after yourself. It also means you
can do a trial import weeks before your real cutover, then re-import the
current file on the day, and only the changes come across.

The one thing that breaks this: if your export doesn't include the ENS
Member Record ID column, SocietyPress has nothing stable to match on. Check
for it before your first import.

---

## What to check after your own import

In this order, because each one is cheaper to fix than the one after it:

1. **Total count.** Does the number of members match the number of rows in
   your file? If not, look at the skipped count and the error list.
2. **Your couples.** Pick three you know and confirm each is one membership
   with two people, not two memberships.
3. **Your tier list.** Expect duplicates. Tidy them now.
4. **Your institutional members.** Confirm the institution's name appears,
   not just the contact person's.
5. **A seasonal address**, if you have any. Confirm the months came across
   along with the address.
6. **Your own record.** You are in that file too, and you will notice a
   mistake in your own data faster than in anyone else's.

If something is wrong, fix the spreadsheet and import again. That's what
the re-import safety is for.

---

## Where this was run

On a throwaway WordPress install, not on a live site and not on the public
demo. That is the recommended way to do your own first pass: a scratch
install, a real file, and no consequences. When you are satisfied the
result is right, do it again on the site you actually intend to keep.

The demo site at [demo.getsocietypress.org](https://demo.getsocietypress.org)
carries a different dataset and is not part of this walkthrough.
