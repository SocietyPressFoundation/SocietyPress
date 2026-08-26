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

Pre-1.0 development iterations are archived in
[CHANGELOG-pre1.md](CHANGELOG-pre1.md).

---

## [1.1.59] — 2026-08-25

### Changed

**The store's category list now follows you down the page**

Reading down a long store meant scrolling all the way back to the top to switch
to another category. The category list now stays alongside the items as you
scroll, so any category is one click away wherever you are on the page. On a
phone the categories still sit above the items as they always have.

### Added

**Choose how many store items to see at once**

A store with hundreds of publications handed a visitor one enormous page. The
store now shows twenty items to start with and offers Show 20, 50, 100, 500 or
Unlimited above the grid, with page links underneath. A store small enough to
fit on one screen never shows either control.

## [1.1.58] — 2026-08-22

### Fixed

**A newly uploaded file could be listed twice in Files**

WordPress announces a new upload before it has finished recording where the
file lives, so the address SocietyPress saw at that moment and the address it
saw a fraction of a second later were not always the same one. Files matched on
the address, so a file whose two addresses differed was recorded twice and
appeared as two copies of one picture with nothing to tell them apart.

Files now recognises an upload by its media library entry rather than by its
address, and corrects the address in place when it settles.

## [1.1.57] — 2026-08-22

### Changed

**Files now shows every upload, not just the ones something is using**

The Files screen listed a file once SocietyPress had a reason to know about it —
something on the site used it, or somebody added it by hand. A picture uploaded
and not yet used anywhere was missing from Files while sitting in plain view in
the Media library, so the two screens gave different answers to "what files does
this site have," and the smaller number belonged to the screen meant to be the
filing cabinet.

Every upload now appears in Files. Ones nothing is using land in **Unfiled**,
ready to be dragged wherever they belong. Existing uploads are swept in once,
automatically, on update — files already sorted into folders by what uses them
stay where they are.

Deleting a file from the Media library now also removes it from Files, instead
of leaving a row pointing at a file that is gone.

## [1.1.56] — 2026-08-22

### Fixed

**The Files screen was missing from the menu, and hidden from the people allowed
to use it**

Files — every upload the society holds, in folders you keep — was built and
working, but it had never been added to the sidebar's arrangement. It fell out of
every drop down group and sat loose at the bottom of the menu, below the groups,
where nobody would think to look for it.

It also carried the wrong permission. The screen itself lets in anyone with
Website access, but the sidebar entry still demanded a full WordPress
administrator, so the volunteers who run the website could not see the row at all.

Files now sits in the **Website** group beside Media, and anyone with Website
access can see and open it.

## [1.1.55] — 2026-08-22

### Fixed

**Two buttons that both said Cancel**

Calling off a volunteer signup asked you to confirm, and the confirmation box
offered you a choice between "Cancel" and "Cancel." One called off the signup and
the other called off the question, and nothing on the screen told you which was
which. The same thing happened when cancelling an event registration, in three
places.

The buttons now say what they do: **Cancel Signup** beside **Keep Signup**,
**Cancel Registration** beside **Keep Registration**. The confirmation box also
refuses, on its own, to ever show two buttons with the same wording again — so a
future screen can't reintroduce it.

The button on the volunteer page itself now reads **Cancel Signup** rather than a
bare "Cancel" sitting next to your "Signed Up" badge.

*Reported by a SocietyPress user.*

---

## [1.1.54] — 2026-08-21

### Added

**Volunteering no longer happens in silence**

Signing up to volunteer used to save the row and say nothing else. The member got
no acknowledgment, and whoever had to staff the job found out only by opening the
admin and counting. Event registration has written to both sides since the
beginning; volunteering now gets the same treatment.

A member who takes a spot gets a thank-you. A member who lands on the waitlist is
told that, and told again when somebody drops out and their spot comes free —
which used to happen entirely without their knowledge. Cancel a volunteer
opportunity and everyone signed up hears about it.

All four of those are yours to reword under **Communications → Email Templates**,
where they join the welcome, renewal, expiration, donation and subscription
messages. Each one has its own merge tags for the opportunity's title, date, time
and place, plus a `{{opportunity_details}}` tag that drops the whole thing in as a
tidy card.

**The person running the opportunity gets told too**

Every signup, waitlist placement and cancellation now emails a short notice — who
it was, how to reach them, how many spots are filled, and a link straight to the
list of everyone signed up. It goes to the Contact Person named on the
opportunity, so a committee chair hears about their own jobs rather than the
whole society hearing about all of them. With no contact named it falls back to
the society's own address.

### Changed

**Saving a volunteer opportunity takes you back to the list**

The Save button used to redraw the same form with a notice on top, which left no
signal that the job was finished and no way back except the sidebar. It now
returns you to Volunteer Opportunities with the confirmation there.

**A way back from four more editing screens**

Volunteer opportunities, events, speakers and documents were the editing screens
without a "Back to…" link at the top. Every other one in SocietyPress had one.
Now they all do.

### Fixed

**Resetting the subscription confirmation email works**

"Reset to Default" on the Subscription Confirmation template returned an error
instead of the default text, because that message was missing from the list the
Reset button consults. Every template's default now comes from one list, so the
two cannot disagree again.

---

## [1.1.53] — 2026-08-20

### Added

**You choose where the menu sits when the logo is above it**

Putting the logo on its own row hands the whole width to the menu, and until now
the menu always spread itself edge to edge across that width. That suits a long
menu. A short one ended up with its handful of links flung to the far corners of
the header and wide holes between them, and there was no setting that changed it.

Settings → Design now offers **Menu Position** whenever the logo sits above the
menu: centered under the logo, lined up with the logo on the left, or spread
across the full width as before. Centered is the new default, and the preview
updates as you change it.

---

## [1.1.52] — 2026-08-18

### Changed

**Every translatable message now tells its translator what the blanks are**

Messages that fill in a name, a count or an amount reach a translator as
something like "Imported %1$s records. %2$s duplicates skipped" — with nothing
to say what each blank will hold. Guessing wrong reorders the sentence and puts
the wrong number in the wrong place, and the translator has no way to know.

Every one of those messages now carries a note explaining each blank. No visible
change to the software.

---

## [1.1.51] — 2026-08-18

### Changed

**Groundwork for translators**

Two of the menu-editor's confirmation messages, and the note explaining why two
faint labels on Menu Layout were darkened, carried no guidance for anyone
translating or maintaining them. No visible change.

---

## [1.1.50] — 2026-08-18

### Security

**The surname contact form can only reach researchers who asked to be reachable**

The "contact this researcher" button on surname search carried the recipient's
identity in the page itself, and the site would mail whoever it named — a member
who had never listed a surname, someone who had switched surname alerts off, or
the administrator. Only guests were limited to five messages an hour; a signed-in
member had no limit at all.

The recipient is now looked up through the surname registry: they must be an
active member, must actually list a surname, and must still have "Surname match
alerts" switched on. Members are capped at twenty messages an hour, well above
what real research looks like.

### Fixed

**Adding a member no longer throws away everything you typed**

The Add Member form has more than forty fields. Leaving out a required one — or
using an email address already on file — cleared the whole form and returned an
empty page with an error at the top, so the only way forward was to type it all
again. Everything entered now comes back exactly as it was, research surnames
included, with the error explaining what needs attention.

**Deleting a form submission asks first**

Every other Delete in SocietyPress asks before it acts. Form submissions were the
exception: one click on the Delete link removed a message somebody had sent the
society, with nothing in between and no way back.

**Store orders are recorded at the right amount**

Every order written to the history log was recorded as $0.00 when it was created,
whatever the customer actually paid. The amount shown when the order was later
paid was correct, so the two halves of the record disagreed.

**Small text and empty-state guidance are readable**

Several labels on Menu Layout were too faint against the white panel behind
them to meet the contrast standard, including the instructions shown when a
group is empty.

**Album and newsletter covers announce themselves to screen readers**

The cover image on a gallery album and on a newsletter issue was the only thing
inside its link, and it carried no description, so anyone using a screen reader
heard "link" with no indication of which album or issue it led to. Covers now
carry their title, and so does the placeholder shown when there is no cover.

### Changed

**Buttons say what they add**

Members, Groups, Pages, Events, Volunteer Opportunities, Campaigns, Newsletters
and Documents each had a button labelled "Add New". They now read "Add Member",
"Add Event", and so on.

**Putting a form on a page no longer starts with a line of code**

The Forms screen led with `[societypress_form id="7"]` — as a column in the list
and as the first instruction in the form editor. It now points to the Form widget
in the page builder, which is how most people will place a form. The paste-in code
is still there, folded away for anyone who wants it.

**Importing members talks about extra information, not custom fields**

Columns in your file that do not match one of the built-in fields were offered as
"Store as custom field", and were grouped afterwards under "Custom Fields". They
are now "Keep as extra info" and "Extra Info".

**A great deal more of SocietyPress can be translated**

The pages and menu created when SocietyPress is first installed, the wording of
the welcome, renewal, expiry, donation and mailing-list emails, the Events import
screen, the Library catalogue's totals and paging, and several smaller labels were
all written in English in a way that no translation could reach. They now go
through the same translation system as the rest of the product.

---

## [1.1.49] — 2026-08-18

### Fixed

**Button columns are as wide as their buttons, and no wider**

The tables on User Access divided their width evenly between however many
columns they had, so the Grant Access column took half the page while the
name and email beside it were squeezed into the other half — and the
previous release's fix pinned that column to a fixed 140 pixels, which is a
guess that goes wrong the moment a word gets longer or the page is
translated.

Each button column now takes exactly the width its buttons need and hands
the rest to the names beside them.

---

## [1.1.48] — 2026-08-18

### Fixed

**The Grant Access button is no longer hidden behind the search box**

On User Access, searching for someone to grant access to put the search box
on top of the results underneath it, covering the right-hand end of every row
— which is where the Grant Access button sits. The name column looked
squeezed and the button could not be reached at all.

The search row now stays in place above the results instead of floating over
them, and the action column is held narrow so names and email addresses get
the width. The newsletter archive's search box had the same fault and is
fixed with it.

---

## [1.1.47] — 2026-08-18

### Changed

**The directory lists people surname first**

A membership roll is sorted by surname and read by surname, so the directory
now lists "Whitfield, Harold" rather than "Harold Whitfield" — the word the
eye is hunting for now starts every line. Joint memberships read
"Whitfield, Harold & Margaret", and an organisation still appears under its
own name.

Settings → Directory → Name Format switches it back to given name first for
a society that prefers the everyday order. The list is sorted by surname
either way.

**The library catalog searches every word you type**

Typing more than one word used to find only records where those words sat
side by side in that order, so "smith rusk" found almost nothing however
much Smith material the collection held. Every word is now matched
separately, and a record has to contain all of them — in any field, in any
order.

A phrase in quotes is still matched whole, so "first families" finds the
words together. The rule is stated under the search box, and it applies to
the public catalog, the librarian's catalog screen, the export that mirrors
it, and the site-wide search.

### Fixed

**Hiding a document section now hides the files as well**

Unticking "Show this category on the website" took a section off the
documents page but left every file in it downloadable to anyone who still
had the address — a bookmark, a mailed link, or a search engine's cache. A
society taking its board minutes down until the board rules on them was
publishing them the whole time.

The files in a hidden section are now refused too. Anyone who can manage
documents still reaches them from the admin, and nothing is deleted.

---

## [1.1.46] — 2026-08-17

### Fixed

**The email address you choose to send from is now the one used**

Settings offers a name and address for outgoing mail, and nothing was
reading them. Every receipt, notice and reminder went out under the site
title and the WordPress administrator's address instead, so a society that
set up a proper sending address watched it be ignored without a word.

Both fields are now used wherever the site sends mail, falling back to the
site title and the administrator's address only when they are left empty. An
address chosen by another plugin is still left alone.

---

## [1.1.45] — 2026-08-17

### Fixed

**"Mail a check" no longer sends donors to a blank address**

The donation page offered mailing a check whether or not the society had
ever said where to mail it. Choosing it on a site with no address on file
produced a slip naming who to make the check out to and nothing else — the
donor was left holding a check with nowhere to send it, and the society got
a pending gift that was never going to arrive.

The option now appears only once an address is entered under Settings →
Organization, and that field says so, so its absence reads as something to
fill in rather than something broken. A donation page reached without one
explains itself instead of going quiet.

---

## [1.1.44] — 2026-08-17

### Fixed

**A donation could be marked paid by someone else's payment**

Returning from Stripe, the site read which gift to settle from one part of
the web address and which payment to settle it with from another, and never
checked that the two belonged together. Anyone who had completed a payment
of their own held something that would settle a stranger's pending gift —
recording money that was never received and emailing that donor a receipt
for it.

Stripe stamps the gift's own reference onto the payment when checkout
begins, in two separate places. A payment that names a different gift, or
names none, is now refused. Payments arriving directly from Stripe were
never affected; they were already reading the reference off the payment
itself.

Societies should check their donation records for any online gift they
cannot match to money actually received.

The donation form no longer says "your gift has been received" when it
cannot confirm the gift. A confirmed gift gets the thank-you page; anything
else gets the form back without a claim attached to it.

---

## [1.1.43] — 2026-08-17

### Changed

**A real thank-you page after an online gift**

Giving online used to hand you the donation form back, with a small green
line above it saying the gift went through. Read quickly, it looked as
though nothing had happened — and it asked you for money a second time.

Paying now takes you to a page about your gift instead: the amount, the
frequency, where the receipt is headed, a reference number to quote, and
the dedication if you gave in someone's honor. Recurring gifts say plainly
that they will repeat and how to stop them. The 501(c)(3) acknowledgment
appears only for societies that have entered their EIN, so nobody claims a
tax status they do not hold.

The page can only be opened by the person who just gave. Walking the
donation numbers in the address bar shows a stranger nothing.

---

## [1.1.42] — 2026-08-17

### Changed

**Dashboard tiles can be dragged**

Rearranging a tile is now a matter of picking it up and dropping it where
you want it. A line shows where it will land, and the arrangement saves
itself as soon as you let go.

The arrows are still there beside each tile, and stay there. Dragging is
the quicker way when it suits you, but it needs a steady hand, it is
invisible to anyone working from the keyboard, and on a tablet the browser
never reports a drag at all. Either method does the same job and saves the
same way.

---

## [1.1.41] — 2026-08-17

### Fixed

**Tick boxes stretched across the whole column**

On the member edit screen the Joint Membership tick box was drawn as wide as
a text field, with its wording stranded above it. One styling rule was
sizing every field on the form the same way, tick boxes included, so this
affected every tick box and radio button on the screen rather than the one
that got noticed. A tick box is now the size of a tick box, with its wording
beside it on one line.

### Changed

**Site Role now explains itself**

"Site Role" sounds like it means somebody's office in the society, and it
does not — it is what they are allowed to do inside the website's admin
area. A board member reasonably read it the other way and asked what the
field was for, which is a fair question for a field that can hand out
administrator by accident.

The field now says plainly what it controls, notes that ordinary members
should stay on Subscriber, and points administrators at Settings → User
Access, which is where a treasurer or a librarian is actually given the
run of their own area. Anyone who is not an administrator now sees a line
telling them why their list of choices is short, instead of wondering
where the other roles went.

---

## [1.1.40] — 2026-08-17

### Fixed

**Book samples opened in a desktop app instead of on the page**

In Firefox, clicking "See a sample" opened the sample window with the title
at the top and nothing underneath, while the PDF itself opened in a separate
desktop application. Safari was unaffected.

The cause was a missing instruction rather than anything to do with Firefox.
The sample was linked straight at the uploaded file, and a web server hands
an uploaded PDF over with no word on what to do with it — so the browser
falls back to whatever the person has configured for PDFs generally. A
browser told to open PDFs in a desktop reader did precisely that.

Samples are now served the same way newsletters and documents already were,
through an address that states the file is meant to be read where it is. The
store was the last place still pointing at a raw file; that gap is closed.

If a browser has been explicitly set to always open PDFs in another
application, it may still honour that setting — that is a choice made in the
browser, and no website can overrule it.

---

## [1.1.39] — 2026-08-17

### Changed

**The dashboard now shows your job, not everyone's**

The five tiles across the top of the dashboard were always the same five, and
all five were about members. A treasurer signed in to a screen with nothing
about money on it. A librarian signed in to a screen with nothing about the
library. And the dashboard went on offering Upcoming Events long after a
society had turned the Events module off, because nothing on the page had
ever thought to ask.

The dashboard now builds itself out of what your society actually runs and
what you personally are allowed to see. A treasurer gets dues collected this
year, donations, and orders waiting to be filled. A librarian gets the
catalog. Whoever looks after the committees gets volunteer openings, open
ballots, and a count of committees sitting without a chair — the sort of
thing that otherwise goes unnoticed for a year. Nobody sets any of this up;
it is simply right when you sign in.

New tiles cover dues, donations, store orders, events and registrations,
volunteer openings, chairless committees, open ballots, the library catalog,
record collections and records indexed, email subscribers, documents,
newsletters, photos, open research requests, and unread form submissions.
Each one appears only if its module is on and only for people whose role
covers it.

**Tiles you can arrange yourself**

A **Customize tiles** button lets you move any tile earlier or later and hide
the ones you never look at. Hidden tiles are listed underneath while you are
customizing, so putting one back is a click rather than a hunt through
settings. **Start over** returns everything to the way it shipped. Your
arrangement is yours alone — it does not change what anyone else sees.

The controls are arrow buttons rather than drag-and-drop, on purpose: they
work on a trackpad, they work from the keyboard, and they cannot be
half-performed and drop a tile somewhere you did not mean.

### Fixed

**Dead-end shortcuts on the dashboard**

The row of quick links offered Members, Events and Settings to everyone,
including people with no permission to open them and societies with the
module switched off. Each link now appears only when it actually goes
somewhere.

**The dashboard counted its members twice**

Five membership totals were being queried on every dashboard load and then
again by the panels below them. The page now asks once.

---

## [1.1.38] — 2026-08-17

### Changed

**Every PDF can now be read on the page, not just downloaded**

Documents offered one button, Download, and nothing else. A member who wanted
to check a line in the bylaws had to put a copy of the bylaws on their
computer to do it, and anyone reluctant to download things would reasonably
conclude the document was closed to them.

PDF documents now show a **Read** button beside Download. Read opens the
document in a window on the page — the same reader the newsletter archive
has always used. Download saves a copy, as before. Word files, spreadsheets
and anything else with no reader to offer keep the single Download button
they had. In a month-by-month list, such as a run of meeting minutes, the
date itself opens the reader and a small Download link sits beside it.

**Download now actually downloads**

The Download buttons on newsletters and documents both handed the file to
the browser and let it decide, and a browser shown a PDF shows it. Download
now saves the file and Read shows it, so the two buttons do the two
different things their labels promise. Files with an accent or a dash in
the name arrive under their proper name instead of a string of percent
signs.

**One reader everywhere**

The newsletter archive, the store's book samples and now the documents
library all open the same reader, so the same window and the same keyboard
shortcuts greet a member wherever they meet a PDF. On a phone or a small
tablet the reader steps aside and lets the device show the file full
screen, which it does better than any window on a page can.

### Fixed

**Members-only newsletters no longer publish their file address**

Opening an issue in the reader used the file's own address in the uploads
folder, which meant that address was sitting in the page for anyone to copy,
member or not. The reader now goes through the same gate the Download button
does.

---

## [1.1.37] — 2026-08-17

### Fixed

**Filters keep you where you were**

On Documents, choosing "Past 3 months" from the Show list threw you back to
the folder list instead of narrowing the folder you were reading. The
dropdown was rebuilding the web address from scratch and losing the folder
along the way.

The same fault was waiting in every other filter on the public side, so it
was fixed once and applied everywhere. Searching the library catalog now
keeps whatever media type, subject or source you had already picked instead
of quietly dropping them. Filtering events no longer flips you out of the
calendar and back to the list. Searching the resource directory stays inside
the category you were browsing. The surname registry and vertical files keep
their place too.

The Show list on Documents now appears only when the folder you are in
actually holds dated documents. A folder of undated reference sheets used to
offer a date range that could only ever come back empty.

---

## [1.1.36] — 2026-08-16

### Added

**Deleting a file, with the thing that stops you deleting the wrong one**

Files could delete a folder but not a file. Tick what you want gone and press
Delete.

A file something is using will not delete. The check runs before the question
is asked, so you get "3 files? One other is being used and will be kept"
rather than a confirmation followed by a refusal — and a picture cannot
quietly disappear from a store product or a newsletter because somebody was
tidying up.

Delete means delete. The file leaves the whole site, not only this screen,
and the confirmation says so. Nothing outside this site's own uploads folder
is ever touched — a picture hosted somewhere else loses its record here and
stays where it is, because it was never ours to remove.

---

## [1.1.35] — 2026-08-16

### Added

**Folders drag into folders**

Nesting was in the plan and in the wording, and was not actually reachable —
every folder you made came out at the top level. Drop a folder onto another
to put it inside, or onto All files to bring it back out.

A folder cannot be dropped into itself or into anything already inside it.
That would close a loop in the tree: the branch stops being reachable from
the top and everything filed in it disappears from the screen while sitting
untouched in the database.

---

## [1.1.34] — 2026-08-16

### Added

**Files now covers everything that holds a file**

Files started with store products, documents and the library catalog. It now
also knows about member photos, meeting agendas and minutes, event images,
speaker photos, newsletter covers and photo album covers — every place in
SocietyPress that points at a file.

Half of those screens store a file as a web address and half store the media
library entry it came from. Both name a file; they just name it differently,
and Files reads either.

One picture used in three places is one file used three times, not three
copies. Ask what is using it and all three come back.

Replacing a member's photo used to be invisible to this, because each upload
gets a fresh address to defeat browser caching. The record now follows the
replacement, so the folder you filed it in survives somebody changing their
picture, and a photo that is deleted is forgotten rather than left behind as
a broken thumbnail.

On txsaghs this brought in another 112 files — 93 newsletter covers, 13 event
images, 5 album covers, 4 member photos — with nothing filed by hand.

---

## [1.1.33] — 2026-08-16

### Added

**Files, in folders, that stay where you put them**

WordPress has never had folders. Its media library is one flat pile with a
date filter, and it labels everything SocietyPress uploads as "Unattached,"
because the only belonging it understands is "attached to a post" — and a
store product is not a post.

There is now a Files screen that does understand. Drag a file onto a folder
to move it. Tick several and use Move to if you would rather not drag —
everything dragging can do, that menu does too, so nobody on an iPad is
stuck. Make folders, rename them, nest them. Renaming never breaks anything,
because files remember the folder itself and not what it happens to be
called this month.

Every file also knows what is using it. Click "Used by" and it tells you —
the store product, the document, the catalog item — with a link straight to
it. Delete a folder and its contents move to Unfiled rather than being
destroyed; a folder is where you put something, not what it is.

Nothing had to be filed by hand to get started. Every file the plugin was
already pointing at was found and sorted into Store, Documents and Library
on the spot, and store products, documents and catalog items keep the record
current as they are saved from here on.

A document switched between public and members-only physically moves on
disk. It now takes its folder and its history with it, instead of arriving
at the new address looking like a file nobody had ever filed.

### Changed

**Document categories are now called sections**

With folders arriving in Files, two different things were both shaped like
folders and it was not clear which one you were being asked for. They do
different jobs: a **section** decides where a document appears on your
website and who may read it, and a **folder** is simply where you keep the
file. Naming them apart means you can tell at a glance which question a
screen is asking.

Nothing moved. Every document is in the same place it was, under the same
name — only the label on the screen changed.

---

## [1.1.29] — 2026-08-16

### Added

**Pick a product photo instead of pasting its address**

The Image URL field on a store product now has a Choose a file button, the same
one the Sample or Preview field has. It opens the media library filtered to
images, fills the address in for you, and shows the picture right there so you
can tell at a glance you picked the right one.

---

## [1.1.28] — 2026-08-16

### Changed

**Document folders sit two across**

Three columns still made the tiles narrower than a category name wants. Two
gives each one room to sit on a single line, and a short list of folders reads
better as a pair of columns than as a thin strip of them. A phone still gets
one.

---

## [1.1.27] — 2026-08-16

### Fixed

**New documents are protected too, and switching a document changes where it lives**

Protecting members-only files covered the documents already on the site.
Anything uploaded afterwards went to the ordinary uploads folder, so a new
members-only document had its link guarded and its file open to anyone — the
gap that arrangement exists to close, reopening on the next upload.

Saving a document now puts its file on the correct side before the record is
written. A members-only document goes behind the wall; one changed to public
comes back out. That matters both ways: marking an existing public document
members-only used to leave the file exactly where anyone could still fetch it.

**Document folders cap at three columns**

Four made each tile narrow enough that longer category names wrapped badly, and
the row read as a strip rather than a set of folders.

---

## [1.1.26] — 2026-08-16

### Fixed

**Members-only documents are now actually protected**

Marking a document members-only put a permission check in front of the link on
the page. It did not protect the file. Once the address was known — shared,
guessed, or picked up by a search engine — the web server handed the file to
anyone who asked, member or not.

Members-only files now live in a folder the web server refuses to serve, and the
site reads them back through PHP after making the same check the page makes. The
permission finally applies to the document rather than to the link.

Public documents are unaffected and are still served directly, which is faster
and is what "public" ought to mean.

---

## [1.1.25] — 2026-08-16

### Fixed

**Document folders sit in columns rather than a single stack**

The folder grid asked the browser to fit as many columns as it could at a
minimum tile width. In a narrow content column that works out at one, so the
folders came out as a single tall stack.

It now uses set column counts — two on a small screen, three from tablet width,
four on a wide one, and one on a phone — so the folders read as a grid whatever
the page around them is doing.

---

## [1.1.24] — 2026-08-16

### Changed

**The documents page opens as folders**

It used to print every document on the site, one after another, grouped under
headings. That reads fine with a dozen; a society that has been going a while
has hundreds, and the page becomes something nobody scrolls to the bottom of.

Categories are now folders. The page opens on them, each showing its name and
how many documents are inside, and a folder opens to its own contents with a
link back. Members-only folders carry a padlock so it is clear before clicking
what will need signing in for.

Nothing to switch on, and nothing changes for a society with a single category —
with one folder there is nothing to choose between, so its documents show
straight away as they always have.

---

## [1.1.23] — 2026-08-16

### Fixed

**The Privacy settings tab was saving nothing at all**

Every control on Settings → Privacy silently discarded whatever you set. Lock a
section of My Account, change which emails new members get by default, switch
activity logging on — press Save, watch "Settings saved" appear, and nothing
had changed.

Each settings tab is identified by a field that always arrives when the form is
submitted, and only the keys belonging to that tab are written. Every control on
the Privacy tab was a checkbox, and an unticked checkbox sends nothing, so there
was no field that could identify the tab — and with no way to identify it, none
of its settings were ever written. It now has one, and all of them save.

### Added

**You choose how long email delivery records are kept**

Every email the site sends is recorded — who it went to, when, whether it
arrived. That record was being deleted after ninety days by a nightly job, with
nothing anywhere saying so.

Settings → Privacy now has **Keep delivery records for**, offering anything from
90 days to forever. It defaults to forever, which is the new behaviour: nothing
is deleted unless a society decides it should be. A society that would rather
not hold member addresses that long can pick a shorter span.

Blast emails are unaffected either way. What you wrote, who you sent it to and
when are kept permanently, and always were — it was only the delivery record
that expired.

---

## [1.1.22] — 2026-08-16

### Changed

**No download button on a book's sample**

The reader that opens a sample of something the store sells offered to hand over
the file. Reading the sample in place is the point of it; a button offering to
save a copy of a book the society is selling on the same screen sits oddly next
to Add to Cart.

The newsletter archive's reader is untouched — members downloading their own
newsletters is exactly what that is for.

---

## [1.1.21] — 2026-08-16

### Changed

**A menu link's address can be corrected without removing the item**

The address a link pointed at was shown as plain text, so fixing one meant
removing the item and adding it again — losing its place in the menu and
anything else set on the row. It is now a box you can type in, checked the same
way as when the link was first added.

A mistyped address is ignored rather than throwing the save away, because the
same row also carries the wording, the position and who can see it, and one slip
should not cost somebody all of that. The previous address stays, so a menu item
never ends up pointing at nothing.

---

## [1.1.20] — 2026-08-15

### Fixed

**A menu link that isn't a web address is now refused**

Typing a sentence into the new web-address box added a menu item pointing at
nothing instead of saying so. The check only asked whether WordPress could make
a URL out of what was typed, and it can make one out of almost anything. It now
looks for a real site name — a dot, and nothing but letters, digits, dots and
hyphens — so a mistake is caught while you are still on the screen rather than
becoming a broken link on your site.

---

## [1.1.19] — 2026-08-15

### Added

**Menus can now link to another website**

The Menus screen only ever offered pages you already had, or a brand-new page.
Anything that lives somewhere else — an apparel shop run by a supplier, a
catalogue hosted elsewhere, a registration form — could not be added at all, so
a menu item pointing off-site meant asking somebody technical for help. A menu
is not a thing anyone should need help with.

There is now a third option: type the wording you want and paste the address.
Leave the scheme off and `https://` is assumed rather than the whole thing being
rejected over a detail you cannot see. Leave the wording blank and the item is
named after the site it points to, which beats a menu item labelled with a full
web address.

Links to other websites open in a new tab, so your own site is still there when
somebody comes back.

---

## [1.1.18] — 2026-08-15

### Changed

**The menu tucks up under the logo**

A further tightening of the stacked header. What remained between the logo and
the menu was the padding each menu link carries above its own text. The row is
now shifted up by that amount rather than the padding being reduced, so every
link keeps a click target the same size it has always been.

---

## [1.1.17] — 2026-08-15

### Changed

**The menu sits closer to the logo in the stacked header**

There was more space between the logo and the menu than there looked to be any
reason for. Two causes: a gap between the rows that added to the padding the
menu links already carry, and the logo image sitting on a text baseline, which
leaves descender space underneath it that no amount of adjusting margins can
remove. Both are gone, so the menu now sits directly under the logo.

---

## [1.1.16] — 2026-08-15

### Fixed

**The signed-in member's name no longer lands on top of the menu**

With the logo above the menu, a signed-in member saw their own name and photo
sitting over the middle of the navigation, covering whichever items happened to
be underneath.

The user menu is built as a navigation element in its own right, and the rule
telling the main menu to fill the row was reaching it too. The two then split
the row between them and overlapped. The rule now applies only to the main menu.

---

## [1.1.15] — 2026-08-15

### Changed

**The stacked header lines up with the rest of the page**

The stacked header added in 1.1.14 centred the logo and let the menu wrap onto a
second line. Both were wrong in practice. A centred logo sitting above
left-aligned page content reads as a mistake even when it is deliberate, and a
menu that wraps turns one clean row into a ragged block.

The logo now sits against the left content edge, in line with everything else on
the page, and the menu stays on a single row spanning the full width, with the
items spread across it rather than bunched in the middle.

---

## [1.1.14] — 2026-08-15

### Added

**Put your logo above the menu instead of beside it**

In the header as it has always been, the logo and the menu share one row, so the
menu only gets whatever width the logo leaves it. For a society with a crest and
a long name that is not much, and the usual answer has been to shrink the menu
wording until it fits. Past a certain point the items stop fitting at all and
start running underneath the logo.

Settings → Design now has a **Header Layout** choice. Leave it on "Logo beside
the menu" and nothing changes. Choose "Logo above the menu" and the logo gets a
row of its own with the menu spanning the full width beneath it — enough room to
keep the wording readable, show a larger logo, and add to the menu later without
it collapsing.

Full-width screens only. On a phone the menu is a button either way, so stacking
there would only add height.

### Fixed

**The Logo Size setting now actually changes the logo**

Settings → Design has had a Logo Size box for some time. It saved the number and
did nothing with it — the header used a fixed maximum height regardless, so a
society that wanted a larger logo had no way to get one and no clue why. The
setting now does what it says. Sites that never touched it are unchanged.

---

## [1.1.13] — 2026-08-15

### Added

**A "View Site" button on the Dashboard**

The admin toolbar is switched off by default, and it is hidden on the admin
screens as well as the public ones. That is the right default — the toolbar is
clutter for most of the people running a society site — but it left no way to
get from the admin to the site itself. The only route was knowing to edit the
address bar, which is not something to expect of anyone.

There is now a **View Site** button beside the heading on the Dashboard. It
opens the site in a new tab, so the admin stays where it was behind it. Without
a toolbar on the public side, opening in the same tab would leave you looking at
your own site with no way back.

---

## [1.1.12] — 2026-08-15

### Added

**Surname Research is now a page you can just create**

SocietyPress has always had a surname registry — members list the families they
are researching, visitors search it, and anyone who finds a match can send that
member a message without ever being shown their email address. The trouble was
getting it onto your website. The only way in was to build a page with the page
builder and know which block to drop onto it, which is not something anyone
discovers by looking. Societies had the feature and no way to reach it.

"Surname Research" now appears in the page template list alongside Library
Catalog, Vertical Files and the rest. Create a page, pick it from the Template
box, publish. Anything you type into the page itself still shows above the
registry, so you can explain to your members how to add their names.

The page is public, like the vertical files listing. Someone searching for their
family and finding one of your members is how a society gets found at all.

### Changed

**Visitors can reply to your members out of the box**

Letting a signed-out visitor message a researcher used to be switched off until
someone found the setting and turned it on. That is the one thing the registry
is for, so most societies were publishing a list of surnames that no outsider
could act on, with nothing on screen explaining why.

It is now on by default. The message goes through the site — the sender types
their own name and email, and your member's address is never shown to them —
and messages from signed-out visitors are rate-limited to discourage spam. If
you would rather only signed-in members could make contact, the setting is still
there at Settings → Directory, and unticking it is respected.

### Fixed

**Surnames no longer disappear without explanation when a membership lapses**

When a member's membership lapsed, every surname they had listed dropped out of
the registry — out of the search, and out of the A–Z list. Nothing anywhere said
so. One society was running with roughly a third of its registry invisible
without knowing it.

The behaviour has not changed by default, because for many societies the
registry is a member benefit and dropping lapsed members is the point. What has
changed is that you are told: Settings → Directory now shows how many surnames
are currently hidden for this reason, and a new tickbox keeps them listed if you
would rather treat the registry as a research index — the family history is
still true whether or not this year's dues were paid.

---

## [1.1.11] — 2026-08-15

### Fixed

**Theme updates that never finished updating**

The themes gallery could show an update for a theme, sit there while you clicked
Update, report success — and still show the same update waiting for you
afterwards. Clicking again did the same thing. Nothing was broken on your site,
but there was no way to make the notice go away.

The gallery was reading the version number carried inside the plugin, while the
Update button could only fetch files from the most recent published release of
SocietyPress. Whenever the plugin had moved ahead of the last release, the
gallery offered a version that no download could deliver, so the theme on disk
never changed and the notice came straight back.

The gallery now only offers what a download can actually install, so an update
you are offered is one that will finish. In the rarer case where the available
files were *older* than the theme already on your site, the update would quietly
replace it with the older version; updates now stop and tell you the site is
already current instead of going backwards.

### Changed

**The footer's last column now sits against the right margin**

The footer's columns are sized in fractions, so they always span its full width
— but their text does not. A column of short links on a wide screen fills a
fraction of the space it is given and hugs its left edge, leaving the footer
anchored at the left and trailing off into nothing at the right. The last column
is now turned around to sit against the right margin, so there is content on both
edges. Applies to the full three-column layout only: on a tablet the third column
already wraps onto a centred row of its own, and on a phone the whole footer
centres.

### Added

**Affiliation logos: control their size, what sits behind them, and where they sit**

**Website → Affiliations** now sets how large the logos are — Small, Medium,
Large, or an exact height of your own — and whether each one gets a white panel
behind it. Turn the panel off and a logo saved with a see-through background
takes the footer's own colour. Logos are matched by height, so a tall crest and
a wide wordmark still look like they belong on the same row. Each row warns you
when a logo's file type cannot be see-through, so it is clear which file to
replace rather than looking like a setting that does not work.

**Where the logos sit** moves the row — and the wording above it — to the left,
the centre or the right of your footer. Centre suits a footer built as one wide
band; left or right suits a footer whose text is already lined up down one side,
where a centred row of logos lines up with nothing above it. A small preview on
the settings screen shows the choice rather than describing it. Footers that
never touch the setting keep the centred row they already had.

**Which part of the footer** decides whether the logos get a row of their own
below the columns, as before, or move up beside the columns instead. Every footer
column is as tall as the longest one, so a short first column — name, address,
phone, email — leaves a pocket of empty footer underneath it while the opening
hours or the links run on. The second choice puts the logos in that pocket, sat
on the floor of the footer level with the foot of the longest column, so they
cost the footer no extra height at all. On a phone, where the columns stack, they
simply follow the address. A theme with a hand-written footer may have nowhere
inside its columns to put them; there the logos stay in their own row rather than
disappearing, and the choice is kept in case the site changes themes later.

**Catalog Options: the word lists your catalogers pick from**

A new screen at **Library → Catalog Options** owns the Media Type, Subject and
Location lists. Add an entry, rename one, delete one. On the item form those
three fields are now dropdowns instead of empty boxes, each with an
*Add new…* choice that takes a value on the spot — a cataloger with a book in
their hand never has to leave the record to add one word, and what they type
joins the list for everyone. The lists arrive filled with the standard
genealogy-library vocabulary, and each has a button to put it back.

A value already on an item but missing from the list is still offered, so
opening an imported record and saving it cannot silently blank a value the
library has used for years.

**Choose which columns the public catalog shows**

Same screen. Tick the columns a visitor sees in search results and arrow them
into the order you want them read. A catalog imported from an older system
often has nothing in Type or Year, and a column of dashes tells a researcher
nothing while pushing the useful columns off a phone screen.

**A Vertical Files page**

A surname collection filed in folders is not a catalog search, and members look
for it by name. The new **Vertical Files** page type lists them with the search,
paging and rows-per-page controls a member of a society migrating from
EasyNetSites will recognise. The arrow beside a row opens that record underneath
it, so nobody loses their place partway down a list of a thousand surnames.

### Fixed

**Store, Shopping Cart and Records could not be chosen as page types**

All three drew their pages correctly and had done all along, but none appeared
in the page type list on the Edit Page screen — so the only route to them was
WordPress's own Page Attributes box, which SocietyPress hides. A society could
stock the Store from the admin and then find no way to put it on a page.

**Newsletter contents lists were missing entries, and could never be repaired**

Two faults, one on top of the other. The reader that lifts the "Inside this
Issue" block off an issue's front page understood dot leaders written as plain
dots, but not the single … character a word processor substitutes for them —
so an entry whose dots had been prettified was skipped. It also gave up after
two lines it could not read, and because a printed front page is two columns
that a PDF hands over as one line, an ordinary gap in the contents column ended
the scan halfway down the list.

The second fault was worse: the reader refuses to overwrite a contents list that
already exists, which is right for one an editor typed and wrong for its own bad
earlier attempt, and it could not tell the two apart. Any improvement to the
reading could therefore never reach the issues that most needed it.

It now knows its own work. A fresh read replaces an earlier automatic list when
it finds more of the issue, and never touches one you have typed. Saving the
box yourself claims it permanently; emptying it hands it back.

**The condition after every catalog title**

Every item in an imported catalog carries "Good" because that is the setting
nothing has changed, so "(Good)" appeared after every title and told a
researcher nothing. Condition now shows only when it is something other than
good — the cases that actually change whether an item can be borrowed or needs
careful handling.

**A long drop down menu ran off the bottom of the screen**

The admin sidebar's drop down panels are pinned to the window, so anything below
its bottom edge could not be reached by scrolling. A panel now slides up to fit,
and one taller than the window scrolls inside itself with its title staying put.


**A Menus screen built for the person who actually maintains the site**

SocietyPress now has its own Menus screen, at **Website → Menus**, and it
replaces WordPress's Appearance → Menus in the sidebar. The whole menu is on
one page and nothing takes effect until you press Save Changes.

Reordering is an **up/down button on each row** — not drag-and-drop, and not a
column of sort numbers to work out. One click moves an item one place, and
anything nested underneath travels with it. Actions are written out as words —
*Edit page*, *Remove* — instead of a row of small unlabelled icons.

Removing an item is reversible right up until you save: the row greys out and
the button changes to *Keep*, so you can change your mind. After any save,
an **Undo last change** button puts the menu back the way it was.

Each item can be hidden from visitors who aren't logged in, using a single
**Members only** checkbox. Hiding a heading hides everything under it too, so
you never end up with orphaned links pointing at a login wall. Societies that
have set up roles can open **Advanced** on any row and restrict an item to one
of the ten access areas instead; the menu then matches what the permission
system will actually allow, rather than advertising a page that then refuses
the visitor.

You can also add a page to the menu from the same screen — pick one you already
have, or type a name and SocietyPress will create the page and link it in one
step. "I made a page and it isn't in the menu" no longer needs two screens.

**A Records page is created for you**

New sites now get a Records page, in the main menu alongside Library, showing
the searchable index of your transcribed records. Every other part of
SocietyPress that has a public page already got one automatically; records
did not, so a society could transcribe and import thousands of cemetery,
census or obituary entries and have nowhere for a visitor to reach them
short of building the page by hand. If you already have a site, add a page
and choose the **Genealogical Records Search** template.

**Folders for the media library**

Images, PDFs and documents are now sorted into folders — Newsletters, Photo
Galleries, Events, Documents and Site Design — instead of one long list.
Files are filed automatically as they arrive: importing newsletters puts
them in Newsletters, adding gallery photos puts them in Photo Galleries.
You can create your own folders and move files between them at any time,
and anything SocietyPress moves is never moved back out from under you.
A folder filter appears both in the media library and in the file picker.
Existing files are sorted on upgrade, so the folders are useful immediately
rather than after somebody sorts thousands of files by hand.

**Progress bars on every import**

Newsletter, records and bulk records imports now upload in small batches
with a progress bar, a running count, and a list naming any file that
failed. Previously a large upload could stop partway with nothing on screen
to say how far it got.

**Uploading more files than the server allows now says so**

Selecting more newsletters or CSVs than the server accepts in one go used to
discard the extras silently — pick forty newsletters, get twenty, with no
warning. The importers now upload in batches so there is no practical limit,
and the older non-JavaScript path says plainly when files were not received.

**Give someone access to only certain pages**

Content access used to be all or nothing. Under **User Access**, someone with
Content access can now be limited to specific pages: they see only those pages
in their list and cannot open the others. Administrators are never limited.

**List view for album photos**

The album editor has a **Thumbnails / List by name** toggle, so an album of a
hundred similar photographs can be scanned by filename instead of by sight.
Captions, reordering and removal work the same in both views.

**Sort a menu into alphabetical order**

Appearance → Menus has a **Sort A→Z** button. Items nested under another item
stay where they are and are sorted among themselves.

**Samples and previews for store items**

A store item can now offer a sample — a chapter PDF, a table of contents, or a
page on the site — shown as a **See a sample** button on the store page, so a
buyer can look before paying.

The sample opens in a reader on the page rather than sending the buyer at the
PDF itself. A plain link to a PDF downloads it in most browsers, which leaves
somebody who only wanted a glance at the first chapter hunting through their
downloads folder for a file they now have to delete. The reader shows the sample
where they are, with a **Download a Copy** button for anyone who does want to
keep it, and closes back to the store. It uses the browser's own PDF renderer,
so nothing extra is loaded to make it work.

**Tidying the audit log**

The Audit Log screen now shows how many entries it holds, offers to delete
entries older than a period you choose, and lets you set how long entries are
kept from then on. Previously entries were kept for a year with no way to
change it.

**The shared notepad is open to everyone with backend access**

The notepad is a handoff board — what was left half-done, what the next
person needs to know — so it is now available to anyone who can edit content,
not only to people holding a SocietyPress access area. Members cannot see it.

**Hiding a document category**

A document category can be hidden from visitors without deleting it. Its
documents stay in place and it keeps working in the admin; it simply stops
appearing on the website.

**Draft review links**

Any page can now be shared for review before it goes live. Save the page as
a draft and use the new **Review link** on the page editor: it's a private
link anyone can open — no account needed — to see the page exactly as it
will look, with a Preview button and a one-click Copy. The link only ever
unlocks that one draft, works even while the site is set to require login,
and can be turned off at any time by generating a new one. Publishing the
page makes it public in the normal way.

**Description and directions above the Events list**

The Events page now shows whatever you type into its page editor above the
list of events, so you can add a welcome, meeting location, or directions —
matching the other SocietyPress page templates.

### Fixed

**Saving a page left you sitting on the same screen**

You pressed Save, it saved, and nothing else happened — no move to a fresh
page, and a refresh asking whether to send the form again. Saves are now
finished before the page starts being drawn, so the screen moves on the way it
always should have. The Modules screen has been rebuilt properly on top of
that; the rest follow the same route in a later release.

### Changed

**One Appearance screen instead of three**

Choosing a theme, changing your colours and fonts, and saving or loading a
preset are three parts of one job, but they were three separate items in the
sidebar. To change a colour you had to already know that Themes is not where
colours live, that Design is, and that Theme Presets is a different thing
again.

They are now one screen — **Website → Appearance** — with three tabs: *Theme*,
*Colours & Fonts*, and *Presets*. Nothing inside them changed, so everything
works exactly as it did; there is just one door now instead of three.

Old bookmarks still work. The three previous addresses each land on the right
tab rather than going nowhere.

### Added

**Show the organisations your society belongs to**

There is now a screen at **Website → Affiliations** for the logos of the bodies
your society is a member of — a national organisation, a state society, a
lineage group. Add the logo, type the organisation's name, paste a link to
their website, and it appears in the footer of every page.

Rows can be reordered with up and down buttons, and there is an optional line
of wording above them — "Proud member of", or whatever suits. Nothing appears
in the footer until you add something, so a society that does not use this sees
no change at all.

Before this there was no supported way to do it. The footer's three columns
were fixed in the theme, so the only route was the Widgets screen, which said
nothing about logos and did not exist on the plain SocietyPress theme at all.

The organisation's name doubles as the image's alt text, so screen readers
announce where the link goes without anyone having to think about alt text.

**Footer columns on the SocietyPress theme**

The five child themes each let you put widgets in three footer columns; the
plain SocietyPress theme did not, so advice that worked on Heritage was simply
wrong on the parent. It now has the same three columns.

Leaving a column empty keeps the footer exactly as it was — your organisation's
details, quick links and social icons. A column only gives way once you put a
widget in it, so nothing changes underneath a society that never opens the
screen.

### Fixed

**Staff with limited roles could barely see the admin menu**

A Treasurer, Librarian or Archivist signed in and found an almost empty
sidebar, even with their role correctly granted. The ten access areas were
being worked out properly and then overruled: the code that applies them to the
menu ran a step too early, before most screens had been registered, so around
75 of them went on demanding full administrator access.

Every screen now enforces the access area it was meant to. Screens requiring
full administrator rights are down to two — the Admin Sidebar editor and User
Access — which are the two that genuinely should.

If you set up roles and concluded they did not work, they did. This was the
menu ignoring them.

**Two menus called "Menus", and a screen that changed the wrong thing entirely**

The sidebar had grown two entries both labelled **Menus** — the SocietyPress
one, and WordPress's own, sitting loose at the bottom. Only the SocietyPress
screen explains what it does, so a volunteer had a 50/50 chance of making a
change nothing would ever walk them through. WordPress's version is no longer
in the sidebar.

Worse, **Menu Layout** sat under *Website → How it looks*, in among Themes and
Design. It does not touch your website. It arranges the sidebar that staff see
after signing in, for everyone in the society. Somebody looking for their site's
appearance could rearrange the back office for the whole committee without
realising. It is now called **Admin Sidebar**, it lives in *Settings* under a
new **Back office** heading, and the page says plainly that it does not affect
what visitors see.

**Customize** has been removed. It opened WordPress's Customizer, which nothing
in SocietyPress fills in — so it offered a handful of WordPress's own controls
that either repeated *Website → Design* or quietly disagreed with it. Everything
it appeared to offer is on the Design screen.

Three things have been renamed to say what they are:

- **Settings → Website** is now **Settings → Site Basics**. There was already a
  *Website* group in the sidebar, so "check the website settings" pointed at two
  different places. This screen holds the site's title, tagline, admin email,
  timezone, date and time formats, homepage and visibility — basics, not the
  look.
- **Add Images** is now **Photos & Albums**. It manages albums; adding images is
  one thing you do there, so anyone looking for photos already uploaded had no
  reason to think this was the place.
- **Record Payment** no longer appears in the Money list. It never actually did
  — it was named there but could not show up, because it is a screen you reach
  from the button on Payment History.

**Members were sent to a WordPress error screen when they logged in**

Signing in dropped every member on "Sorry, you are not allowed to access this
page." SocietyPress sent everyone who logged in to the admin dashboard without
first checking whether they were allowed there, and ordinary members are not.
On a site migrated from another system, where every imported member arrives as
a Subscriber, that was the entire membership.

Where you land after signing in now depends on who you are. Officers and
volunteers with SocietyPress duties go to the dashboard, exactly as before. A
member arriving for the first time after setting or resetting their password
goes to the Member Portal, so their first view of the site is the page that
explains what is there for them. Every other member goes to the home page. If
you clicked a members-only link and were asked to sign in first, you still
finish the trip to the page you wanted.

**"Require Login" hid the site from your own members**

The Require Login setting on Settings → Website was supposed to make the site
private — visible to members, closed to the public. It was checking for
administrator rights instead of simply being signed in, so members were bounced
back to the login screen on every page, including the member pages the setting
exists to protect. Signing in again just returned them to the login screen. The
setting now means what it says: members in, public out.

**Members who reached a backend page were sent in a circle**

A member who typed a `/wp-admin/` address, or followed an old bookmark to one,
was redirected to the login screen — where, being signed in already, there was
nothing to do but sign in again and arrive back where they started. Members are
now returned to the site itself, and a backend page a member is not allowed to
open shows them the site rather than a stark WordPress error page.

**The sign-in box looked like it only accepted a username**

The field on the sign-in screen was labelled "Login Name", so members with a
forgotten username assumed they were stuck. WordPress has always accepted the
email address there too, and the help text below the field said so — the label
was the only thing claiming otherwise. It now reads "Username or Email".

**A dismissed pop-up could leave the page unable to be typed in**

Every SocietyPress pop-up — the confirmation prompts, alerts, the member detail
panel, the design builder — freezes the page behind it while it is open, so a
stray click can't reach whatever is underneath. Each unfroze only the parts it
had frozen itself, which is correct when one pop-up opens on top of another,
but meant a pop-up that failed part-way through left a piece of the page frozen
with nothing left to thaw it. A frozen area gives no sign at all: boxes look
completely normal and silently ignore every click and keystroke. Opening any
pop-up now clears anything an earlier one abandoned.

**Editor screens filled the debug log with warnings**

If your site has WordPress debugging switched on, every visit to an editor
screen — adding a member, editing an event, writing up a document — wrote a
"Deprecated" warning into `debug.log`. Nothing was broken and nothing appeared
on screen, but over a working day the file filled with hundreds of identical
lines and buried anything that genuinely needed attention. The editor screens
are deliberately kept out of the sidebar, and WordPress was asking them for a
page title they had no way to give it.

**Apostrophes and quotation marks collected stray backslashes**

Anything typed with an apostrophe or a quotation mark came back with a
backslash in front of it — `Beginner's` saved as `Beginner\'s` — and
removing the backslash by hand didn't help, because saving the form put it
straight back. This affected event titles and descriptions, member names and
notes, research surnames, library and resource entries, document titles,
ballot questions, page-builder text, and the sign-up form when it redisplayed
what a visitor had typed.

Entries already stored with a stray backslash are cleaned up for you. The
repair runs by itself the next few times you visit the admin, works through
your existing entries a little at a time so nothing slows down, and notes in
the audit log how many it corrected. Genuine backslashes — a file path in a
document note, for instance — are left alone.

**Imported GENRECORD files lost their record type**

A GENRECORD file that named its type the way the published format guide
does — `Cemetery`, `Marriage`, `Military` and most of the rest — imported as
an untyped general collection. Ten of the fourteen documented types were
affected; only Census, Court, Land and Tax happened to work. The record type
decides how a collection is labelled and grouped, so a shared cemetery index
arrived looking like a miscellaneous pile. Both the spelled-out names and the
short codes are now recognised, and existing collections can be corrected by
editing the collection and choosing the right type.

**Event categories could not be added, renamed or deleted**

The Events → Categories screen looked complete but did nothing: adding a
category, renaming one, changing its colour and deleting one all silently
failed, on every site. Every society was limited to the categories created
when SocietyPress was installed. All of it now works. Deleting a category
leaves its events in place and simply uncategorises them.

**Colouring a table's header row appeared to do nothing**

Using Table → Row background color on a header row had no visible effect,
because the header's own grey covered it. Body rows were unaffected, which
made the feature look unreliable rather than broken.

**Sorting the page list by title appeared to do nothing**

The Pages list showed its Title column as already sorted A→Z when it was not,
so the first click sorted Z→A and looked like nothing had happened.

**Dates and times displayed in the site's timezone instead of as entered**

Event times, meeting dates, newsletter publication dates, membership
expiration dates, document dates, and ballot voting windows are all stored
as plain wall-clock values — 3:00 PM means 3:00 PM, with no timezone
attached. Every screen that displayed them was converting them as though
they were UTC, shifting each one by the site's offset.

On a US Central site that meant:

- A 3:00 PM–5:30 PM event listed as 10:00 AM–12:30 PM
- Any date on the first of a month displayed as the previous month, and any
  date in January displayed as the previous year — a newsletter published
  2016-01-01 read "December 2015"
- Membership expiration dates displayed a day early, including the
  `{{expiration_date}}` token in renewal emails
- Ballot edit screens reloaded the shifted time into their own fields, so
  each re-save moved the voting window further back

The stored data was correct throughout; only the display was wrong, so no
repair of existing records is needed. Voting-window comparisons were already
consistent and were not affected.

**Buttons that appeared to do nothing**

Any admin action whose confirmation dialog re-sent the form lost the button
that triggered it, so the request arrived with no action and the page simply
reloaded unchanged. This affected **Forms → Delete** and **Picture Wall →
Reject**. Confirmation dialogs now also restore the browser validation that
had been skipped.

**Photo album imports silently stopped at 20 images**

Selecting more than 20 images imported only the first 20, with no error and
no indication anything was missing — PHP discards the surplus before the
upload reaches the plugin. Images now upload in small batches with a
progress bar and a per-image error list, so album size is limited only by
available storage.

**Page content ignored on SocietyPress page templates**

Pages using the Research Help, Resources, Library Catalog, Records, Store,
Cart, or Documents templates discarded anything typed into the page editor.
A shortcode placed on such a page never rendered, and there was no way to
add a line of explanation above the listing. Page content now appears above
the module.

**Other fixes**

- The Newsletter Archive page-builder widget showed "No newsletters
  published yet" on sites with a full archive; it was reading a location
  newsletters are no longer stored in.
- Reordering membership plans saved correctly but left the row in place, so
  the change was invisible until the page was reloaded.
- Blank lines added in the page builder's Rich Text block showed in the
  editor but not on the published page.
- Several buttons and time ranges displayed raw escape codes instead of an
  ellipsis or dash.

---

## [1.0.1] — 2026-06-27

A large maintenance-and-feature release on top of the first public 1.0:
bulk importers across every module, online membership dues, deeper
per-society configurability, and a sustained security, accessibility, and
performance pass.

### Added

**Importers**
- ENS Page Maintenance importer (Settings → Import ENS Pages)
- Newsletters bulk importer — multi-PDF upload with automatic cover
  generation and metadata
- Library catalog importer with column-variant auto-detection
- Events importer supporting recurrence, speakers, and slots
- Gallery importer — file upload or URL fetch into a new album
- Bulk records importer — N CSVs become N collections in one pass
- CSV export for record collections

**Membership & dues**
- Online membership dues and multi-year renewal via Stripe
- Escalating renewal banner on the My Account page
- Per-section member self-edit locks
- Society-configurable new-member email-preference defaults
- Admin-managed name prefix/suffix dropdowns
- Print / PDF view for the admin Members list

**Records & genealogy**
- GEDCOM 7.0 export
- Per-surname alternate-spelling lists, used in surname search
- A–Z browse mode for the surname widget
- Researched-surname collection on the join form
- Public (non-member) surname contact on public registries
- Split death-notice vs obituary fields in the obituary record template

**Store**
- Member vs non-member pricing, with "Apply member pricing" on order detail
- Flat-rate shipping option and a complimentary (comp) order action
- Mail-in check donation path for `[sp_donate]`
- Search + date filters and inline sort-order editing in the orders and
  products admin

**Blast email**
- File attachments on blasts
- Status-based audience segments and per-tier audience narrowing
- Per-blast sender name / From-address override
- Clone action, opt-out override for critical notices, and audience
  name + count shown in the send confirmation
- One-click member email health scan

**Documents & resources**
- Members-only access at the document-category level
- Recency filter, per-category "last updated", and Month/Year display
  format for documents
- Drag-to-reorder and optional last-updated date for Resource Links;
  grid layout option on the Resource Links widget

**Events, donations & governance**
- Per-event file attachments and an "Email this event" row action
- Admin-configurable default range for the public Events page
- All-source income summary on the Finances page
- Society notification when a donation arrives; donation search by
  processor transaction ID; Year filter on the donations admin
- Chair dashboard widget and a URL shortener
- Contact form routing to a specific officer or committee chair, with an
  optional preset subject
- Theme exchange Tier 2 — `.spchildtheme` bundle import; Playfair Display
  as a selectable font; a saved-look preset library

### Changed
- Single source of truth for the genealogy service list
- Early renewals stack onto remaining days in rolling mode
- Duplicating an event resets its date to today instead of inheriting the
  original
- Pending/failed donations excluded from campaign raised totals
- Removed HostGator from recommended hosting; refreshed installer
  diagnostics with errno-aware failure messages

### Security
- Hardened the backup feature (capability, path, headers) and added a
  scheduled-backup admin UI with secure download and nightly email
- Closed a ballot double-vote race with an atomic participation gate
- Nonce ordering on refund/help handlers; tightened Reply-To and
  ownership checks
- `ENT_QUOTES`/UTF-8 flags on installer `htmlspecialchars` calls
- Post-rebaseline audit pass over the new importers

### Performance
- Bounded export memory and replaced whole-membership dropdowns
- Streamed GEDCOM export in batches
- Eliminated cold-load and ballot N+1 query overhead

### Accessibility
- `focus-visible` styles and color-contrast fixes
- `aria-describedby` field hints; modal `inert` and `aria-describedby`
- Clearer library catalog tab and calendar-cell semantics
- Confirm-dialog buttons labeled with the action verb; save confirmations
  brought into view and focused

### Fixed
- Frozen password-reset dialog on WordPress 7.0
- Login-acknowledgment modal that could re-trap members
- Theme builder modal that never opened, and numerous unclosed
  `spConfirm()` handlers across the admin
- Setup wizard blank page on Continue, and stray admin notices on the
  wizard screen
- My Account fatal from an unbuilt feature; preset import dropping valid
  fonts via a stale allowlist
- Dead Documents frontend; GENRECORD parser brought into spec conformance

---

## [1.0.0] — 2026-06-01

First public release.

SocietyPress is a free, GPL-licensed WordPress platform that gives a
volunteer-run genealogical or historical society everything it needs to
operate online — members, dues, events, library, newsletters, records,
volunteers, donations, blast email, committees, documents, photos, voting,
lineage programs, and research help — without third-party services.

### What's in 1.0

**Members** (always on)
- Full membership database with custom fields, household/joint members,
  research surnames, research areas, and skills/interests
- Renewal reminders, lapsed-member workflows, lifetime members
- Pending-changes queue so member-initiated edits land in moderation
- CSV import with field mapping; full data export
- XChaCha20-Poly1305 encryption (via libsodium) on phone numbers and
  street addresses at rest

**Optional modules** (toggle in Settings → Modules)
- **Events** — calendar, registration with capacity + waitlist, speakers,
  slot-based events, iCal subscribe, Stripe/PayPal payments, attendance
  check-in, recurring series, external iCal feed sync
- **Library** — full OPAC-style catalog with faceted search, browse-by
  sections, ISBN enrichment, donor tracking, condition + availability
- **Newsletters** — archive with searchable cover gallery and inline PDF
  viewer
- **Resource Links** — categorized external links with maintenance tools
- **Governance** — committees, meeting records, leadership roster
- **Store** — online publication sales with per-item shipping, Stripe
  and PayPal checkout, order management
- **Records** — collection-based historical record sets with custom fields,
  member submissions, search, `.genrecord` open-format import/export, and
  GEDCOM import (5.5/5.5.1/7.0) and export (5.5.1 or 7.0)
- **Donations** — one-time and recurring donations, campaigns,
  acknowledgments, year-end reports
- **Blast Email** — newsletter blasts, segmented sends, plain-text or
  templated HTML, delivery log
- **Photo Gallery** — albums with member uploads, Picture Wall public view
- **Help Requests** — public-facing research-question intake with member
  responses and admin moderation
- **Documents** — gated members-only document library with categories
- **Voting & Elections** — multi-question ballots, eligibility rules,
  anonymous secrecy, results with charts and CSV export
- **Lineage Programs** — "First Families"-style heritage recognition with
  application review and optional public roster
- **Research Services** — paid research request workflow with assignment,
  hours tracking, invoicing

### Themes
- Parent theme + five GPL child themes (Coastline, Heritage, Ledger,
  Parlor, Prairie), each with palette-on-activation and per-section
  Customizer controls

### Site building
- 21+ page-builder widgets covering hero sliders, contact cards, event
  lists, calendars, donation forms, member directories, library catalogs,
  research guides, and more
- Page templates for members-only content, search, and login modal
- PWA manifest with offline fallback page

### Security
- All AJAX endpoints nonce + capability gated
- Stripe + PayPal webhook signature verification
- Server-side outbound HTTP for iCal sync and color extraction is
  DNS-rebinding pinned (CURLOPT_RESOLVE)
- File uploads validated against an explicit MIME allowlist; `.htaccess`
  drops PHP execution in upload paths
- Email header CR/LF stripping in `sp_get_email_headers()` so a stray
  newline in admin-edited From/Reply-To settings cannot inject Bcc/Cc
- Legacy `noenc:` decryption fallback removed; on activation any surviving
  rows are re-encrypted via `sp_maybe_migrate_noenc_members()`
- Stripe Checkout redirect URLs validated via `sp_safe_stripe_checkout_url()`
  at every checkout site (lineage applications, donations, research cases,
  invoice payments)
- GDPR: five exporters and five erasers (donations pseudonymize for IRS
  recordkeeping rather than delete)

### Accessibility
- Custom `spConfirm`/`spAlert` modals with focus management, focus traps,
  inert siblings, and screen-reader-friendly labels
- WCAG AA color contrast across status badges, links, and admin tables
- Form labels, aria-live regions, and `prefers-reduced-motion` guards
- Login acknowledgment modal that requires explicit dismissal
- Filter-bar `<select>` controls in the Members, Events, and Email Log
  list tables now expose visible-label-equivalent `aria-label` text
- Page builder widget cards are keyboard-operable (Enter / Space toggles
  open/closed; `aria-expanded` tracks the body's visible state)
- 48 page-builder widget fields now have programmatically associated
  labels (`for=`/`id=`) across flat and repeater field patterns
- Hero slider per-line size/weight/color selects and per-slide Button
  Text / Button URL inputs each have their own labels
- Theme builder hex-color text inputs are labeled per color
- Lineage application form fields are fully label-associated
- Donations bulk-action "select all" checkbox has an associated label

### Internationalization
- 4,600+ translatable strings, generated `.pot` files for plugin and
  parent theme
- Page template names ("Library Catalog", "Genealogical Records Search",
  "Store", "Shopping Cart", "Documents", "Interest Groups") now
  translatable in the WordPress page Template dropdown
- Frontend event-count plural moved to `_n()` ("1 event" / "%s events")
- Frontend event location labels for virtual events translated
- Design Settings, Event Categories, Event price fields, Page Builder
  "Standard Pages" description, and Events Import file-format helper
  strings wrapped with the `societypress` text domain

### Operations
- One-file installer (`sp-installer.php`) — drop into web root, browse to
  it, follow the wizard, the installer self-deletes after success
- Built-in dashboard update checker against GitHub releases
- Daily maintenance cron, email-log cleanup, renewal reminder cron,
  event reminder cron, iCal feed sync cron

### License
- GPL-2.0-or-later

---
