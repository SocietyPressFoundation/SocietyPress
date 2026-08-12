# Known Issues

Problems that are understood but not yet fixed. Each entry says what breaks, why,
and what fixing it would involve — so the next person starts from the diagnosis
rather than the symptom.

---

## Redirects fire after output has begun — "headers already sent"

**Status:** symptom fixed 2026-08-12; the underlying structure is still wrong and
the cleanup below is still worth doing.
**Severity:** was medium — a redirect after saving silently did not happen.

### Fixed part

SocietyPress admin screens now hold the response open while handling a POST, so
the redirect after a save can still set its header. The guard is deliberately
narrow: only pages whose slug is `societypress` or begins `sp-`, and only when
`REQUEST_METHOD` is POST. Ordinary page views are untouched, and nothing in the
plugin streams output (there is not one `flush()` or `ob_flush()` call), so there
is no progressive rendering for a buffer to disturb.

This is not the blanket `ob_start()` warned against further down. That warning was
about using a global buffer *as a substitute for* getting the ordering right; a
scoped buffer that makes the redirect genuinely work is a different thing. The
structural cleanup stays on this list because the ordering is still wrong — it is
simply no longer breaking anything a society would notice.

### One correction to the original diagnosis

The first write-up said output began at the plugin's `sp-admin-utilities` style
block. It does not. `wp-admin/admin-header.php` emits the document long before
`admin_head` fires. What actually happens is that PHP here runs with
`output_buffering` at roughly 4KB, so the response is held until that fills —
which happens partway through that style block. PHP then reports the line where
the buffer *spilled*, not where output started. The style block was a bystander,
and a tokeniser check confirmed the plugin emits nothing at include time (zero
file-scope inline HTML).

The remedy is unchanged, because the real cause — redirecting after rendering has
begun — was never in doubt.

### Still to do

### The symptom

`txsaghs.com`'s debug log fills with entries like:

```
PHP Warning: Cannot modify header information - headers already sent by
(output started at .../societypress.php:10088) in .../wp-includes/pluggable.php on line 1535
```

`pluggable.php:1535` is `wp_redirect()`. Line 10088 is the `admin_head` callback
that prints the `sp-admin-utilities` `<style>` block.

### Why it happens

The plugin handles form submissions **inside its page render functions**, and
those render functions run long after `admin_head` has already sent output. So
the sequence on any admin screen that processes a POST is:

1. `admin_head` fires, printing `<style id="sp-admin-utilities">` — output begins,
   headers are sent
2. the page's render function runs, sees `$_POST`, saves the data
3. it calls `wp_redirect()` to do a post/redirect/get — and cannot, because
   headers went out at step 1

There are **78 `wp_redirect()` / `wp_safe_redirect()` calls inside render or page
functions**, so this is a pattern rather than one mistake. A comment at
`sp_page_editor_table_css` shows the problem was already understood in at least
one place.

The practical effect is that the redirect after a save may not happen. The save
itself works — the data is written before the redirect is attempted — so this
shows up as a page that does not navigate cleanly after saving, and a reload that
re-submits the form, rather than as lost data.

### What fixing it involves

The correct shape is to handle POST on `admin_init` (or `load-{$page_hook}`),
before any output, and let the render function only render. That is the standard
WordPress pattern and it makes post/redirect/get work everywhere.

It is not a small change: 78 call sites across the members, pages, menus, records,
finances and governance screens, each needing its handler lifted out of its
renderer. It wants its own session, done screen group by screen group with a test
after each, not a sweep.

**Do not widen the buffer into a global `ob_start()`.** Buffering *every* admin
page, view and POST alike, to avoid dealing with the ordering would trade a
visible bug for an invisible one. The narrow guard now in place is a floor under
the symptom, not permission to stop caring where output happens.

### Not caused by the 2026-08-12 work

The log entries predate that session's deploy (latest occurrences 19:59 and 20:05
UTC; the deploy ran at roughly 21:33). The Affiliations screen added that day
follows the same in-renderer POST pattern as its neighbours but does not redirect,
so it neither added to this nor was affected by it.

### How to confirm the fix

Save something on an admin screen that redirects afterwards — Members, Pages, or
Donations are the easy ones — and watch `wp-content/debug.log`. Before the fix,
each save appended a `Cannot modify header information` warning naming
`pluggable.php`. After it, the save should navigate cleanly and add nothing to the
log. This was verified by inspection and by exercising the guard's conditions
directly; it has not yet been confirmed by a human saving a real form, which is
the one check worth doing.
