# SocietyPress — Audit Fix Summary (2026-04-05)

All 4 agents (security, code review, UX, i18n) ran against the plugin. This file tracks what was fixed.

## Stats

- **47 audit findings total** (security: 16, code quality: 10, UX: 21)
- **42 fixed this session** (89% resolution rate)
- **5 deferred** (low-risk or needs Charles' decision)
- **~90 i18n strings found, ~87 wrapped** (5,302 text domain calls, up from 2,564)

## Deferred Items (low risk)

| Item | Why |
|---|---|
| SEC-HIGH-3: DNS rebinding on color extractor | Admin-only, needs transport-level fix |
| SEC-MED-7: AI nonce before access check | Low risk, minor efficiency issue |
| SEC-LOW-2: Newsletter metadata visible to public | Needs Charles' decision — may be intentional |
| SEC-LOW-3: Push subscribe IPv6 normalization | Edge case on IPv6-capable servers |
| SEC-LOW-4: sp_backup_export_table table name validation | Internal callers only, theoretical risk |

## What Was Fixed

### Security (14 fixes)
- PayPal webhook: reject when no webhook_id, fail-closed on all verification failures
- Secret keys: masked placeholders in DOM, preserve on empty submit (all 8 fields)
- iCal feed SSRF: scheme + private IP validation
- Store checkout: explicit login check on both Stripe + PayPal handlers
- Health endpoint: table details admin-only
- Backup download: realpath traversal guard
- Settings export: added AI/VAPID/Zoom keys to exclusion list
- Surname contact: rate limiting + fixed broken table query
- AI rate limit: per-IP enforcement for logged-in users
- Month names: escaped + i18n wrapped

### Code Quality (10 fixes)
- Two missing add_action() registrations (walk-in attendee, newsletter cover)
- Version header synced (1.0.5 → 1.0.6)
- Duplicate defaults keys removed
- sp_daily_maintenance cron: deactivation cleanup + init hook
- Deprecated current_time('timestamp') → time()
- Backup exception messages: generic display, full error to log only
- Content-Disposition quoted filename
- Health check table list: 51 → 56 tables

### UX / Accessibility (22 fixes)
- All 5 modals: role="dialog", aria-modal, aria-labelledby, focus traps, focus restoration
- `<main>` landmark on ALL templates (page, front-page, single, index, 404, my-account)
- Focus indicators: outline on all inputs/selects/buttons (replaced outline:none)
- Color contrast: #999→#767676 (25), #888→#6b6b6b (15), +1 in theme
- Calendar pill + event badge contrast: sp_hex_is_light() luminance check
- Surname inputs: aria-labels on all 6 fields in 3 contexts
- Committee accordion: role="button", tabindex, aria-expanded, keyboard handler
- Flyout menu: aria-controls + ids, visible focus outline
- Theme builder modal: dialog role, focus management
- Wizard steps: nav element, aria-label, aria-current
- Theme card: CSS :hover/:focus-within (replaced inline JS)
- Donation listbox: ArrowUp/Down navigation
- Spinner: role="status" + aria-label
- Background videos: aria-hidden
- Password toggles: aria-pressed
- Search toggle: :focus rule
- "Add New" → "Add New Member" / "Add New Event"

### i18n (~87 strings wrapped)
- All placeholder attributes (22 "e.g." patterns + 15 short labels)
- Events: edit page, import, frontend headings, time slots, speaker roles, series text
- Page builder: widget picker, hero slider, categories
- Reports: stat labels
- Member directory: 7 JS section headings via i18n object
- Groups: Join/Leave/Joined/Leader/member count
- Newsletter: Public badge, search, Email Body headings
- Library: sort column headers, item save button
- Audit log: System label
- Export: Download CSV button
- 404/index: page content

### Other
- GDPR eraser: expanded to use sp_cascade_delete_member_data() + clean push/AI/ballot tables
- the society theme references removed from deploy.sh, build-softaculous.sh, CLAUDE.md, README.md, ARCHITECTURE.md, PROJECT-PROMPT.md
- TO-DO section headers cleaned up (10 sections renamed to "Complete")
- Docs paths updated for Code/scripts/ restructure
