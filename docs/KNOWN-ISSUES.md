# Known Issues

Problems that are understood but not yet fixed. Each entry says what breaks, why,
and what fixing it would involve — so the next person starts from the diagnosis
rather than the symptom.

---

## Redirects fire after output has begun — "headers already sent"

**Status:** open. Diagnosed 2026-08-12, not fixed.
**Severity:** medium. Produces PHP warnings and, on some hosts, a redirect that
silently does not happen. Cookies set at the same moment are also lost.

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

**Do not "fix" this by adding a global `ob_start()`.** It would bury the warning
while leaving the ordering wrong, and buffering every admin page to paper over a
structural problem trades a visible bug for an invisible one.

### Not caused by the 2026-08-12 work

These entries predate that session's deploy (latest occurrences 19:59 and 20:05
UTC; the deploy ran at roughly 21:33). The new Affiliations screen follows the
same in-renderer POST pattern as its neighbours but does not redirect, so it
neither adds to this nor is affected by it.
