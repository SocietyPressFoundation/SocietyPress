# Changelog

All notable changes to SocietyPress are recorded here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

SocietyPress uses a single running version shared by the plugin, the parent theme
and every child theme — they are built, released and installed together, so they
carry one number rather than drifting apart. The patch digit increments each
release, without rolling over: 1.1.9, 1.1.10, 1.1.11, and so on. A change in the
first or second digit marks a milestone release, not a technical distinction about
the kind of change involved.

Entries describe user-visible changes only. For the underlying commits, see
[the Git log](https://github.com/SocietyPressFoundation/SocietyPress/commits/main).

The 1.0 and 1.1 development line is archived in
[CHANGELOG-1.x.md](CHANGELOG-1.x.md); the pre-1.0 work in
[CHANGELOG-pre1.md](CHANGELOG-pre1.md).

---

## [1.5.16] — 2026-09-03

**Anyone can buy from your store now — no account required.** Until this
release the shop only worked for signed-in members: a visitor who clicked Add
to Cart got no response at all, and the cart page told them to log in first.
That is backwards for a society, because the people buying your cemetery
transcriptions and surname indexes are usually researchers who found you
through a search engine and have no reason to want an account.

A visitor's basket now follows them for thirty days, and they check out with
just a name and an email for the receipt. If they do have a membership, the
cart offers them a chance to sign in for member pricing rather than quietly
charging them full price — and anything already in their basket comes with them
through the login instead of vanishing.

**Orders now record where to send things.** SocietyPress has always had places
to store a shipping address on an order and has never once filled them in, so
no society was ever told where to post what somebody bought. Checkout now asks,
for members and visitors alike, and the answer is saved with the order.

---

## [1.5.15] — 2026-09-03

**The store now has a front door.** SocietyPress has had a working shop for
some time — products, member pricing, card and PayPal checkout, orders, refunds
— and almost nobody could find it. The Store and Cart pages were never among
the pages a new site is given, and the routine that creates those pages stops
the moment a site has any page at all, so no society running SocietyPress has
ever been handed either one. Both are now created for you, and a **Cart** appears
in your main menu with a count of what is in it, on every page of the site.

If you already had a page using the Store or Cart layout, yours is left exactly
as it is.

---

## [1.5.14] — 2026-09-03

**Website > Files works again.** The screen carried a "What is using this file"
panel that was meant to stay out of sight until you asked for it. Its styling
overruled that, so the panel and its full-page dimming layer were sitting over
the Files screen from the moment it loaded — an empty box you had not asked
for, and an invisible sheet over everything behind it that quietly ate every
click. Adding files, opening a folder and ticking a box all did nothing, and
the panel's close button did nothing either, so the only way out was the
browser's back button. The panel now stays down until you open it, closes when
you close it, and Files takes clicks normally.

Marking anything hidden now holds across the whole of SocietyPress, so no other
screen can grow the same fault.

---

## [1.5.13] — 2026-09-02

**Record review learned to tell a list from a mistake.** The first pass of the
new Review check treated every column as free text, so a Book column holding
twelve titles across forty thousand rows had its longest title — "Sexton Burial
Records City Cemetery #7" — reported as an oddity five hundred times over. A
column whose values are drawn from a short list is now recognised as a list, and
judged on whether it is filled in rather than on how long its entries are.

Two more things it was wrong about: a transcriber's aside counted towards the
length of a name, so "Frank [inf of]" looked like a three-word given name and
thousands of good rows were set aside; and a middle initial "E." was read as the
Spanish name particle *e*, which flagged everyone in the index recorded by their
initials. Notes in brackets no longer count towards a name's length, and a
single letter is treated as an initial. On a real 40,548-row cemetery index this
took the queue from 2,279 rows to 872, and what is left are genuine faults —
surnames split mid-word, maiden names stranded in the wrong column.

---

## [1.5.12] — 2026-09-02

**Records → Review finds the rows an import split wrongly.** An index typed up
from a book arrives as one line per entry, and splitting that line into columns
is guesswork — "Abbot Martha Louisa Woodhull" is a surname and three given
names, or a surname and a maiden name, and no importer can tell which. Most
guesses are right. A few hundred in forty thousand are not, and once they are
rows in a table they look exactly like the ones that are.

Every collection now has a Review button. It reads through the collection and
sets aside the rows that look wrong: a column that is filled in on nearly every
other record and empty on this one, a column that normally holds two words
holding five, an entry beginning with De or Van where that is normally part of
the name beside it, a quotation mark opened and never closed, a whole entry that
is a note in brackets rather than a name. Each one is listed with its columns
laid out for editing and two buttons — save the fix, or say it is right as it
is. The count of what is waiting rides on the Review button in the collections
list, because a queue nobody is told about is a queue nobody works.

Nothing here is configured. The checks work out what normal looks like from the
collection itself rather than from any knowledge of what a column is called, so
they apply to a cemetery index, a marriage register or a set of obituaries
without being told which is which. The check runs in slices so a collection of
any size finishes rather than timing out, and it can be run again after a batch
of corrections — rows already accepted stay accepted.

---

## [1.5.11] — 2026-09-02

**The help desk takes screenshots now.** Ask for Help had nowhere to put a
picture, so a volunteer describing a fault had to put the screen into words —
and tickets have arrived saying "got the attached screen" with nothing attached,
leaving the person answering to guess at what the picture would have shown.
Both the form and every reply in the conversation now take files, so a
screenshot can travel in either direction: yours when you file, and yours again
when somebody answering asks to see the screen. Up to five files a message, 5 MB
each, pictures and PDFs. Attaching a screenshot with nothing typed is a
perfectly good reply.

Screenshots of an admin screen are screenshots of members, so these are not put
in the media library, where everything is readable by anyone who knows the
address. They are stored outside it and handed back only to the person who filed
the ticket and whoever answers tickets. The notification email says a file is
waiting rather than carrying it, so nothing leaves the login the rest of the
ticket sits behind.

---

## [1.5.10] — 2026-09-02

**A menu item you add now appears, however deep you put it.** Every theme
decided for itself how many levels of drop down menu it would draw, and the six
of them disagreed — three levels in the SocietyPress theme, two in Ledger and
Parlor, one in Coastline. WordPress does not warn about a menu item nested
deeper than the theme allows. It simply renders nothing for it. So a society
that built Resources → Research → Bexar County Records → the five record pages
watched the record pages vanish from the menu with no error and nothing to
click, and a society that switched to Coastline lost every drop down on the site
at once.

Depth is now unlimited, and no theme sets it any more: all six ask the same
shared function for their navigation, so the six of them cannot drift apart
again. Build the menu your society's material actually needs and it renders.

**Deep menus stay on the screen and stay navigable.** Flyouts open to the right
of their parent, so a menu four or five levels deep used to walk off the edge of
the window — with the level you were reaching for as the part that disappeared.
A flyout with no room on the right now opens to the left instead. In the phone
panel each level is indented one step further than its parent, so level five
reads as level five rather than as more of level two. Every item that opens a
menu announces itself to a screen reader whether or not JavaScript has loaded,
and Escape now closes one level and returns you to the item that opened it,
rather than collapsing the whole menu and making you start over.

---

## [1.5.9] — 2026-09-03

**Records results sort by clicking a column heading, and open A–Z.** A
transcribed collection is imported in the order it was typed — for a cemetery
roster or a surname index, alphabetical — and the search then ordered the
results by the row number the import handed out, newest first. That ran the
whole index backwards: every visitor who arrived before typing anything into the
search box got Z to A.

Results now arrive sorted A–Z on the first column, and every heading that names
a single value is a link — click it to sort by that column, click it again to
reverse, with an arrow marking the column in use. The sort runs across the whole
collection rather than the page on screen, so the first page of a sort by Death
Date holds the earliest dates in the collection and not merely the earliest of
the twenty-five rows already visible. Records with that column empty go last in
both directions, and a column of numbers sorts as numbers — Lot 9 before Lot 10
rather than after Lot 1. On a phone, where the table drops its heading row
entirely, the sort appears as two dropdowns beside the search box.

**A society with one collection now sees that collection's own columns.** The
results table showed a Collection column beside a run-together Summary column
until a collection was chosen from the filter — and a society with a single
collection has no filter to choose from. They were left with a column repeating
the same name next to a column of mashed-together values, neither of which can
be ordered by anything a reader recognizes. Their only collection is now treated
as the chosen one, so the table shows its own field names as columns.

**An import can no longer point two spreadsheet columns at the same field.** The
mapping screen allowed it, and the result was two stored values for one field:
the record editor showed one of them and deleted the other on the next save, and
the detail panel printed whichever came back last. The first column mapped to a
field now keeps it.

---

## [1.5.8] — 2026-09-01

**A record collection can now be searched from any page.** The genealogical
records search was only available as a page-builder widget or on the dedicated
records template. That left out the page most societies actually want it on —
the research page they have already written, with their own table of local
cemeteries and their own notes about which courthouse burned in which year.
Rebuilding that page in the builder to gain a search box is not a trade anyone
makes, so in practice the search stayed where visitors weren't, and the page
went on linking out to a file download instead.

Paste `[societypress_records]` into any page or post and the search appears
there. Add `collection="cemeteries-index"` to search one collection — the slug
or the ID both work — or leave it off to search all of them. Access rules are
unchanged: a members-only collection still asks for a login, wherever the
shortcode sits.

---

## [1.5.7] — 2026-09-01

**Importing a record collection no longer throws away repeated lines.** When a
transcribed index lists the same entry more than once, it usually means what it
says. A sexton's book records "Grant Ira [triplets of]" three times because
three infants were buried; a cemetery volume lists two Groenkes on the same page
with the same name. The record importer treated any row identical to one it had
already seen as a duplicate and skipped it, so those extra burials never
arrived — and the only trace was a "duplicates skipped" number that looked
reassuring. It now counts copies instead of merely noticing them: a collection
takes as many copies as the file offers, while importing the same file a second
time still adds nothing. On a 40,000-row county index this was the difference
between 311 lost records and none.

The import summary now also reports how many identical rows it kept, so a
genuine copy-paste slip is still visible rather than silently doubled.

---

## [1.5.6] — 2026-09-01

**More on the formatting bar, and the same bar everywhere.** Underline,
justify, a Highlight button, a divider line and a special-characters button
(©, ½, é, —) have joined the toolbar above the Content box. The divider and
the special characters were already in SocietyPress, but they sat on a second
row behind the "Toolbar Toggle" button, which there was no reason to know
existed — so in practice they may as well not have been there.

**The Rich Text block on a page now has the full toolbar.** Building a page out
of blocks, the Rich Text block gave you five buttons — bold, italic, two kinds
of list, and a link — while the Content box on the same screen had the Table
menu, the Picture menu, fonts, sizes and colours. Same kind of box, two
different sets of buttons, and no way to tell why. They are now one toolbar.

**Highlight follows your theme.** Highlighted text takes its colour from your
society's accent colour rather than having a colour written into the page, so
a page highlighted under one theme still looks right after you switch to
another one.

---

## [1.5.5] — 2026-08-29

**Importing no longer leaves you with the same tier twice.** SocietyPress
starts with five membership tiers, and an import used to add one for every
plan name in your file without checking whether it already had that tier under
a slightly different word. A society whose export said "Joint" and "Life"
finished the migration with eight tiers — "Joint" sitting beside
"Joint/Family", "Life" beside "Lifetime" — and a tidying job nobody asked for.
Those names are now recognised as the tiers you already have.

**The import tells you what it did to your tier list.** Which tiers it created,
and which names it matched onto tiers you already had, both now appear on the
results screen when the import finishes. Folding one name onto another is a
judgement made on your behalf, so it is said out loud rather than left for you
to find in Settings — and if two of them should have stayed separate, you know
straight away which members to move.

Genuinely different tiers are left alone. Only the five SocietyPress ships with
have alternative names recognised; a society's own "Sustaining", "Patron" or
"Senior" tier is never folded into anything, because putting members on a plan
and a price that isn't theirs would be worse than a duplicate in a list.

---

## [1.5.4] — 2026-08-29

**Imported institutions keep their names.** A library or museum in an ENS
export usually appears as a contact person's name with the institution in the
File Name column, and SocietyPress would only file it as an organization when
the Membership Type column said the exact word "Organization." Societies write
"Institutional," or "Corporate," or "Non-Profit" — and when they did, the
institution's name was thrown away during the import with no error and nothing
in the log to notice. The import now recognises the ordinary ways of saying
the same thing.

**And when it still doesn't recognise the word, it says so.** Any row that
carried an organization name the import decided not to use is now counted on
the results screen, along with the Membership Type values it saw, so a society
using its own vocabulary is told which rows to look at rather than finding out
months later that the library is filed under its librarian. Correcting those
rows and importing the file again is safe — a second import updates the same
members instead of duplicating them.

---

## [1.5.3] — 2026-08-29

**The store's page-size control is three buttons instead of a box to type in.**
Choosing how many items to see at once no longer means typing a number and
pressing Go, and no longer means knowing that zero is the way to ask for
everything. The store now offers 20, 50 and All as buttons, with the current
one highlighted. The buttons appear only when the catalog runs past two pages
at the default size — a store with forty items or fewer shows just the page
links, since a page-length choice on a list that short is one more thing to
read for no gain.

---

## [1.5.2] — 2026-08-29

Removes a private domain reference from the shipped plugin and repairs the
build-time scanner that was supposed to have caught it.

**A code comment named a private development site.** A comment explaining the
affiliation-logo safety net cited the maintainer's own test site by name to
illustrate the case it guards against. The illustration was never necessary —
the sentence makes its point better in general terms, since any society that
has customized its footer is in exactly that position — and the name had
shipped inside every release since 1.1.11. The comment now describes the case
without naming anyone.

**The leak scanner had never run.** `build-softaculous.sh` announced
"Scanning for data leaks..." on every build and checked nothing, in three
independent ways: it read its pattern list from a local configuration file that
had never been created, so the list was empty and a guard skipped the scan
entirely; it looked only at the plugin file, never at the parent theme or the
five child themes that ship in the same bundle; and it recorded a hit in a
variable that nothing ever read, so even a detected leak printed a warning and
let the build succeed. The scanner now refuses to build when it has no patterns
configured, scans everything that ships, and exits non-zero on a hit. A scanner
that cannot fail the build is decoration.

**The Softaculous package was missing its wordmark.** `info.xml` opens its
catalog description with an inline `logo.png` that did not exist, so the listing
would have rendered a broken image against its own text. It is a separate file
from the square catalog icon and keeps its literal name rather than taking the
assigned script ID, which is why it went unnoticed.

---

## [1.5.1] — 2026-08-28

Repairs the Softaculous package, which had drifted far enough from both the
product and Softaculous's actual file format that a one-click install would
have produced a half-working site.

**The install bundle was missing the plugin's assets.** The builder copied
`societypress.php` and the translations but not `assets/`, so every Softaculous
install would have shipped without the PWA icons and favicons and without the
CSS and JS behind the gallery viewer, the events pages, the editor table, the
searchable select and the leadership search. The builder now copies them, and
refuses to build at all if they are absent rather than quietly producing a
broken bundle.

**The package files were written against a schema that does not exist.**
Softaculous ignores tags it does not recognise without reporting anything, so
the catalog entry would have appeared with no description, no version and no
PHP requirement, and the installation form would have been empty. The metadata,
install form and upgrade form have been rewritten against the shipped
WordPress package, and the file index rewritten in the plain-text format
Softaculous actually reads.

**Installs no longer hide themselves from search engines.** The installer
forced the "discourage search engines" setting on for every new site, and the
switch that undoes it is buried three menus deep where a volunteer will never
find it. A society exists to be found, so the install form now asks, and
defaults to visible.

**The database table prefix is honoured.** It was hardcoded to `wp_`, which
meant two SocietyPress sites could never share one database. The install form
now offers the prefix and the installer uses it.

**Smaller things.** The install form gained a site tagline field, so a new site
no longer launches carrying WordPress's "Just another WordPress site"
placeholder. Child themes are no longer excluded from upgrades — they ship as
part of one versioned set, and holding them back was what made the Theme
Gallery offer an update that installed and then reappeared forever. The
declared install footprint is now measured from a real build rather than
guessed, and the builder warns when it falls behind.

---

## [1.5.0] — 2026-08-28

The first release SocietyPress considers finished.

Everything a genealogical or historical society needs to run online, in one
free, GPL-licensed WordPress product: members and dues, events and
registrations, a library catalog, record collections, newsletters, blast email,
donations, a store, committees and meetings, ballots, volunteers, documents,
photo galleries, lineage programs, research help, and a help desk of its own.
Eighteen optional modules on top of Members and Finances, which are always
available, so a society runs the parts it actually uses.

**For the volunteer who has to operate it.** A page builder rather than a
theme editor. A setup wizard on first run. Every module screen tells you when
the public cannot see it yet and offers to make the page — and then to put it
in your menu. Nothing that renders is hardcoded: every visible string is
editable from the admin by somebody with no technical background.

**For the society that is moving.** A migration path from EasyNetSites that
reads their export column by column, a handbook that names every field and what
it becomes, and three ways to back out if it goes wrong.

**For the society that outgrows the look.** Five child themes ship with the
product. The Theme Exchange adds saved looks and bundles from other societies,
and full child themes that have been read line by line before they carry a
badge.

**Under it.** 77 custom tables, ~107 AJAX endpoints, XChaCha20-Poly1305
encryption for sensitive member data, GDPR exporters and erasers, a PWA, and
~7,900 translatable strings with templates for the plugin and parent theme.

**What it tells us.** Once a week an install sends its society name, website
address and version, and nothing else. Settings → Privacy shows exactly what
went and says plainly that there is no switch.

The development history that led here is archived in
[CHANGELOG-1.x.md](CHANGELOG-1.x.md).

