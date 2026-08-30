# Known Issues

Problems that are understood but not yet fully resolved. Each entry says what
breaks, why, and what finishing it would involve — so the next person starts from
the diagnosis rather than the symptom.

---

## Redirects fire after output has begun — "headers already sent"

**Status:** symptom fixed 2026-08-12. The underlying structure is still wrong; the
cleanup under *Still to do* is worth finishing.
**Severity:** was medium — saving worked, but the screen did not move on afterwards.

### The symptom

A production site's debug log filled with entries like:

```
PHP Warning: Cannot modify header information - headers already sent by
(output started at .../societypress.php:10088) in .../wp-includes/pluggable.php on line 1535
```

`pluggable.php:1535` is `wp_redirect()`. In practice a society saw a save that
took effect but left them sitting on the same screen, and a browser reload that
offered to submit the form again.

### Why it happens

The plugin processes form submissions **inside its page render functions**. A page
callback runs after `wp-admin/admin-header.php` has already begun writing the
document, so by the time a render function has saved the data and reached its
`wp_redirect()`, the response is on its way out and no header can be set.

There are **78 `wp_redirect()` / `wp_safe_redirect()` calls inside render or page
functions**, spread across about thirty screens, so this is a pattern rather than
a single mistake. A comment at `sp_page_editor_table_css` shows it was already
understood in at least one place.

**A correction to the first write-up of this issue.** It claimed output began at
the plugin's `sp-admin-utilities` style block, because that is the file and line
PHP named. That is not what happens. `admin-header.php` emits the document well
before `admin_head` fires. PHP here runs with `output_buffering` at roughly 4KB,
so the response is held until that much has accumulated — which happens partway
through that style block. PHP reports where the buffer *spilled*, not where output
started, and the style block was a bystander. A tokeniser check confirmed the
plugin emits nothing at include time (zero file-scope inline HTML). The real
cause — redirecting after rendering has begun — was never in doubt, so the remedy
did not change.

### What was fixed

SocietyPress admin screens now hold the response open while handling a POST, so
the redirect after a save can still set its header. The guard is narrow on
purpose: the page slug must be `societypress` or begin `sp-`, and the request must
be a POST — the only combination in which these redirects run. Ordinary page views
are untouched. Nothing in the plugin streams output (not one `flush()` or
`ob_flush()` call anywhere), so there is no progressive rendering for a buffer to
interfere with, and PHP releases the buffer at shutdown by itself.

### Still to do

The correct shape is to handle POST on `admin_init` or `load-{$page_hook}`, before
any output, and leave the render function to render. That is the standard
WordPress pattern and makes post/redirect/get work without any help.

That means lifting the handler out of each of ~30 render functions, where it is
currently threaded through the markup it prints. It wants its own session, done
screen group by screen group with a test after each, rather than one sweep. The
buffering above is a floor under the symptom so this can be done deliberately
instead of urgently.

**Do not widen the buffer into a global `ob_start()`.** Buffering every admin
request, view and POST alike, to avoid dealing with the ordering would trade a
visible bug for an invisible one — and would hide the next instance of this rather
than surface it.

### How to confirm

Save something on a screen that redirects afterwards — Members, Pages or Donations
are the easy ones — and watch `wp-content/debug.log`. Before the fix, every such
save appended a `Cannot modify header information` warning naming `pluggable.php`.
After it, the save should navigate cleanly and add nothing to the log.

Verified by inspection and by exercising the guard's conditions directly. **Not
yet confirmed by a person saving a real form** — that is the one check still worth
doing, and the quickest way to close this out.

### Not caused by the 2026-08-12 work

The log entries predate that session's deploy (latest occurrences 19:59 and 20:05
UTC; the deploy ran at roughly 21:33). The Affiliations screen added that day
follows the same in-renderer POST pattern as its neighbours but does not redirect,
so it neither added to this nor was affected by it.

---

## ENS import: institutional members lose their name unless Membership Type is exactly "Organization"

**Status:** fixed in 1.5.4. Found 2026-08-29 while running a full ENS import end
to end for [the migration walkthrough](ENS-MIGRATION-WALKTHROUGH.md), fixed the
same evening. Kept here because the diagnosis explains why the guard exists and
why the replacement is a whitelist rather than fuzzy matching.
**Severity:** was medium — data was silently discarded, with no error and nothing
in the log to notice afterwards.

### The symptom

An ENS row that carries a contact person's name *and* an institution name in the
**File Name** column imports as an ordinary individual, and the institution's name
is dropped. The member arrives with the right tier and the right membership type
string, so nothing looks wrong until somebody notices the library is filed under
its librarian.

Observed with a two-row test, identical but for one column:

| File Name | Membership Type | Result |
|---|---|---|
| Fairhaven Public Library | `Organization` | `member_type=organization`, name preserved |
| Marengo Township Historical Museum | `Institutional` | `member_type=individual`, name `NULL` |

### Why it happens

`sp_process_import_batch()` treats a row with both personal and organization names
as an organization only when the Membership Type column equals the literal string
`organization` (lowercased and trimmed).

The guard itself is right and should stay. In most ENS exports File Name holds the
member's own surname for every individual, so trusting a populated File Name alone
would convert an entire membership into organizations. The problem is only that the
confirmation is one exact word.

### How it was fixed

`sp_import_is_organization_type()` now holds the accepted vocabulary, and the
guard tests against it instead of one hardcoded string. The value is normalised
first — lowercased, punctuation flattened so "Non-Profit" and "Org." match, and a
trailing "Member"/"Members" stripped — then matched exactly against
`organization`, `organisation`, `org`, `organizational`, `organisational`,
`institution`, `institutional`, `corporate`, `corporation`, `company`,
`business`, `nonprofit`, `non profit` and `agency`. The list is filterable
through `sp_import_organization_types`.

The match stays exact rather than fuzzy, and the list stays conservative on
purpose. "Library", "Museum", "Society" and "Church" are *not* in it: they name
institutions, but a society may equally use them for an individual tier
("Library Member"), and wrongly converting a membership into organizations is a
far worse failure than the one being fixed.

That is why the second half matters. Rows whose type is not matched are counted
in `orgs_unmatched`, with up to ten distinct values collected in
`orgs_unmatched_types`, and the import results screen reports both — "3 rows had
an organization name that was not imported… Values seen: Affiliate." A society
with its own vocabulary is told which rows to fix instead of losing the name in
silence, and re-importing the corrected file updates rather than duplicates.

---

## ENS import creates membership tiers that duplicate the built-in ones

**Status:** fixed in 1.5.5. Found 2026-08-29 alongside the issue above, fixed the
same evening. Kept for the reasoning about why the alias table is deliberately
small.
**Severity:** was low — nothing broke, but every migrated society had tidying to do.

### The symptom

SocietyPress installs five tiers: Individual, Joint/Family, Student, Lifetime,
Honorary. The importer creates a tier for each distinct plan name in the CSV
without checking whether an equivalent already exists, so a file using the names
"Joint", "Life" and "Institutional" leaves the society with eight:

```
Individual · Joint/Family · Student · Lifetime · Honorary · Joint · Life · Institutional
```

"Joint" and "Joint/Family" are the same tier under two names, as are "Life" and
"Lifetime". Members are assigned correctly; the list is just wrong.

### How it was fixed

Exact case-insensitive matching was already there; only synonyms were missing.
`sp_import_normalize_tier_name()` now reduces a plan name to a comparison key —
punctuation flattened so "Joint/Family", "Joint & Family" and "Joint-Family"
agree, and a trailing "Membership"/"Member"/"Plan"/"Level"/"Tier" stripped — and
existing tiers are indexed under that key alongside their exact names. A CSV
saying "Joint" or "Life" therefore lands on the built-in Joint/Family or Lifetime
instead of creating a near-duplicate beside it. The alias table is filterable
through `sp_import_tier_aliases`.

An exact name match still wins over an alias match, so a society that has
genuinely created both "Joint" and "Joint/Family" keeps them apart and each
retains its own members.

**Why the alias table stays small:** it covers only alternative names for the
five tiers SocietyPress ships with, where a duplicate is guaranteed to be a
wording difference rather than a real distinction. A society's own "Sustaining",
"Patron" or "Senior" tier is never folded into anything. Merging two tiers a
society means to keep apart is a worse failure than leaving a duplicate behind,
because it puts members on a plan — and a price — that is not theirs.

Both halves of the decision are now reported. `tiers_created` and
`tiers_matched` (CSV name => existing tier name) come back from the importer and
the results screen prints both, so a match made on the society's behalf is
visible immediately rather than discovered in Settings later.

Verified on the walkthrough fixture against a clean install: a file naming
"Joint", "Life" and "Institutional" produced six tiers rather than eight —
Joint matched onto Joint/Family, Life onto Lifetime, Institutional created,
since no built-in equivalent exists — with members correctly distributed
13 / 6 / 1 / 2.
