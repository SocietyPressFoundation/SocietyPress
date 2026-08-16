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

## [1.2.0] — 2026-08-16

### Added

**An assistant that answers questions using your own site**

A chat button on your website that answers the questions people email you about
— when the next meeting is, whether the library has anything on a particular
county, how much membership costs. It searches your own calendar, catalog,
newsletters and pages before it answers, so it stays current when your site
changes, and it is told plainly to say "I don't know, contact the society"
rather than guess. A wrong answer about a meeting time sends somebody driving
across the county for nothing.

You supply an API key from Anthropic and pay them directly for what it uses.
SocietyPress never sees the traffic. The key is stored encrypted, the same way
your payment keys are, or you can put it in `wp-config.php` and keep it out of
the database entirely.

You choose who sees it: everyone, logged-in members only, or nobody — in which
case `[societypress_ai_assistant]` still lets you put it on one "Ask us" page
instead of following visitors around the site.

The chat button takes a speech bubble, a leaf, a tree, or a pedigree chart —
picked from pictures on the settings screen rather than names in a list, since
nobody can imagine "pedigree" from a dropdown. A leaf or a tree says the
assistant belongs to a genealogical society; the speech bubble only says it is
a chat, which visitors can see for themselves.

**It cannot reach your membership roll.** The member directory is deliberately
excluded from everything the assistant is allowed to read. It only ever sees
what the person asking could already see for themselves, so a logged-out
visitor asking about a members-only event gets nothing.

Settings → AI Assistant also shows the last 25 questions people asked and what
the last 30 days cost. The questions turn out to be the more useful half: five
people in a month asking when meetings are means the meeting time is buried on
your site, and that is a fix on the page rather than something an assistant
should keep papering over.

Per-person and per-day limits cap what it can cost. Three models to choose
between, from cheapest to most capable.

**Help for the volunteer running the site**

"Ask SocietyPress" answers "how do I do X" for *this* site — it knows which
modules you have switched on, which admin screens the person asking is allowed
to open, and which screen they were standing on when they asked. So it gives
steps that match your installation with a link to the screen, and if the
feature is switched off it says so first instead of sending somebody looking
for a menu that is not there. It explains what to click; it cannot change
anything.

**"Report a Problem", filed from the page it happened on**

What normally reaches a developer is an email saying the events page looks
funny on somebody's iPad — no address, no browser, no versions, and by the time
anyone asks, the officer has moved on. Every one of those missing facts is
already known to the browser and the server at the moment the fault is noticed.

So there is now a Report a Problem button in the toolbar — or in the corner of
the page, since SocietyPress hides the toolbar by default. Five plain
questions, none of them technical, and the page address, browser, screen size,
theme, versions and enabled modules are attached automatically. No IP address
is recorded.

Reports land in a queue at Settings → Problem Reports, with blocking problems
at the top regardless of age. Optional email when one arrives. Export to CSV
for the committee write-up or the next webmaster.

If the AI Assistant is configured, each report gets a **Summarise with AI**
button that turns a rambling description into a title, likely causes and steps
to reproduce — reading the diagnostics, so it can spot that a report is really
about a narrow screen when the reporter never mentioned their phone. It never
overwrites what the officer wrote.

Societies tracking faults upstream can add a GitHub repository and token, which
puts a **Send to GitHub** button on each report. Nothing goes automatically,
and the reporter's name and email are never included — that is a public
tracker, and they did not agree to be published.

Both features answer to Tools → Export/Erase Personal Data. Conversations are
deleted on erasure. Problem reports are kept with the reporter scrubbed out,
because the fault they describe is an organisational record and deleting it
would destroy the history of a bug rather than the identity of a person.

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
