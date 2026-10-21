# SocietyPress iPad Kiosk — Design Specification

**Status:** Proposed — design only, nothing built yet.
**Drafted:** 2026-09-15

---

## What this is

A native iPadOS app, distributed on the App Store, that a society sets up as a walk-up kiosk — at the door of an event, on the front table of the library, or at a membership booth — and pairs to their own SocietyPress site. One app, any society, self-paired. It talks to a new kiosk-scoped REST API on the SocietyPress plugin; it does not reuse the existing admin AJAX endpoints, which assume a logged-in admin at a desk, not an unattended or volunteer-staffed public device.

It covers three workflows:

1. Event/class registration and check-in — pre-registered check-in, walk-in registration, ticket purchase, meal/dietary choice, seat limits, 1-on-1 hourly sessions within a workshop.
2. Library visitor sign-in/out — a guest register, not a circulation system.
3. Membership join and dues renewal, including in-person card payment.

## What this is not

- Not a library circulation system. SocietyPress's library module is deliberately reference-only, no checkouts (`docs/modules/library.md`). Sign-in/out here tracks *who is in the building and why*, not what they borrowed.
- Not a replacement for the existing admin-side event/member tools. The kiosk is a public-facing front door onto the same data; `SocietyPress → Events`, `→ Members`, `→ Orders` remain the back office.
- Not (in this design) a cash register. A kiosk can't make change. Cash/check payment is logged as a pending reconciliation item for the treasurer, not collected by the device.

---

## Design principles

- **A member of the public may be standing alone in front of this thing.** Every flow has to survive someone walking away mid-form, tapping the wrong thing, or a volunteer needing to step in and take over. No flow should be able to double-charge a card or double-register a person because of a tap that happened twice.
- **The screen must never carry the previous visitor's information.** Idle timeout returns to an attract screen and clears all in-progress form state.
- **Everything a volunteer would otherwise configure in the admin stays configurable in the admin.** Meal choices, visit-purpose lists, ticket types — none of it is hardcoded in the app. The kiosk reads its configuration from the site, same as the rest of SocietyPress.
- **Payment requires a live connection; the rest of the kiosk doesn't have to.** A card transaction needs to reach Stripe to authorize — it cannot be queued and replayed later. Registration, check-in, and library sign-in/out can queue offline and sync. The design has to treat those as two different reliability guarantees, not one blanket "offline mode."

---

## Architecture

### Native app, not a web wrapper

Native iPadOS app (Swift/SwiftUI). This is required, not just preferred, because:

- Card-present payment via a Bluetooth/Lightning reader needs the Stripe Terminal SDK, which is native-only.
- Offline queuing and sync need real local storage (Core Data / SQLite) and background sync logic that a sandboxed web view can't provide reliably.
- Single App Mode / Guided Access lockdown (keeping the public from backing out to the home screen) is an OS-level, native-app feature.

Where a full native UI isn't worth building — e.g., embedding Stripe's own hosted card-entry element for the manual-entry fallback — the app can host a scoped web view for that one screen, same pattern the existing Store/Donations checkout already uses server-side.

### Pairing and auth

No per-user login on a shared device. Instead:

1. Admin goes to a new **SocietyPress → Kiosks** page, clicks "Add Kiosk," names it (e.g. "Front Desk iPad"), and picks which modules it may use (Events / Library / Members — any combination).
2. Admin gets a 6-digit pairing code, valid 10 minutes, shown once.
3. On first launch, the app asks for the society's site URL and that code. It exchanges the code for a long-lived device token over HTTPS and stores it in the iPad's Keychain.
4. Every kiosk API call sends the token as a bearer credential. The server checks it against `sp_kiosk_devices` and rejects any call outside that device's module scope — enforced server-side, not just hidden in the UI, since a jailbroken or misconfigured device shouldn't be able to reach a module it wasn't granted.
5. Admin can revoke a device from the same page (lost iPad, decommissioned kiosk) — the token stops working immediately.

New capability: kiosk management sits under the existing `sp_manage_settings` capability, alongside Modules and User Access.

New tables:

```
sp_kiosk_devices
  id, site_label, name, modules_enabled (csv: events,library,members),
  token_hash, paired_at, last_seen_at, status (active|revoked)

sp_kiosk_pairing_codes
  id, code, kiosk_device_id, expires_at, used_at, created_by
```

### New REST namespace: `societypress/v1/kiosk/*`

Everything the app talks to is new — the existing `wp_ajax_sp_*` endpoints stay admin-only and untouched.

| Endpoint | Method | Purpose |
|---|---|---|
| `/kiosk/pair` | POST | Exchange a pairing code for a device token |
| `/kiosk/config` | GET | Module scope, society name/logo, active ticket types, visit-purpose list, registration custom fields |
| `/kiosk/members/search` | GET | Look up a member by name/email/phone (for check-in, renewal, library sign-in) |
| `/kiosk/events/{id}/slots` | GET | Available time slots + remaining capacity (reuses `sp_event_slots`) |
| `/kiosk/events/{id}/register` | POST | Walk-in or pre-registered-with-changes registration, member or guest |
| `/kiosk/events/{id}/checkin` | POST | Mark an existing registration as checked in |
| `/kiosk/library/visits` | POST | Open a visitor sign-in |
| `/kiosk/library/visits/{id}/signout` | PATCH | Close it |
| `/kiosk/members/join` | POST | Create a new member record + WP account |
| `/kiosk/members/{id}/renew` | POST | Renew dues on an existing member |
| `/kiosk/payments/card-present/intent` | POST | Create a Stripe Terminal connection token / payment intent for the reader |
| `/kiosk/sync/batch` | POST | Replay a batch of queued offline actions |

Every write endpoint accepts a client-generated `action_id` (UUID). The server treats a repeated `action_id` as a no-op replay, not a duplicate — this is what makes offline retry and sync safe without double-registering or double-signing-in someone.

### Offline behavior

- **Queues and syncs:** walk-in registration, check-in, library sign-in/out, membership join/renew *record-keeping* (the non-payment part).
- **Cannot queue — requires a live connection at the moment of use:** the card transaction itself. If the reader can't reach Stripe, the app says so plainly and offers "try again" or "collect payment info now, charge when back online is not an option — here's what to tell them" rather than pretending it queued a charge.
- Local storage on-device (Core Data) holds the queue; a background sync task drains it when connectivity returns, using the `action_id` idempotency above. Conflicts that matter (e.g., two kiosks both fill the last seat on a capacity-limited slot while offline) resolve first-synced-wins; the loser's registration flips to waitlisted, same status the online flow already uses, and the app surfaces that to whoever's running the queue reconciliation.

### Payment

- Primary: a Bluetooth/Lightning **Stripe reader**, driven by the Stripe Terminal SDK. Card-present rates, no manual key-in.
- Fallback: manual card entry via a Stripe-hosted element (same PCI-scope pattern the Store/Donations checkout already uses), for when a kiosk has no reader attached or the reader's unavailable.
- Cash/check: logged as a pending line item against the registration or membership record for the treasurer to reconcile at the desk — the kiosk doesn't try to collect or make change.
- New `payment_method` value: `card_present`, alongside the existing `stripe`/`paypal`/etc. on `sp_member_payments` and `sp_event_registrations`.

---

## Feature 1: Event/class registration & check-in

### Reused as-is
- `sp_event_registrations` guest fields (`guest_name`, `guest_email`, `guest_phone`, nullable `user_id`) — a walk-in *is* a guest registration.
- `sp_event_slots` for the 1-on-1 hourly-session case: a slot with `capacity = 1` per hour is exactly a 1-on-1 booking. No new schema needed for this; the kiosk just needs a slot picker against the existing table.
- `status` enum (confirmed/waitlisted/cancelled) for capacity enforcement.

### New schema

```
sp_event_ticket_types
  id, event_id, name, price_member, price_nonmember,
  capacity (nullable), sort_order, is_active

sp_event_registration_fields        -- admin-defined per event, e.g. "Meal choice"
  id, event_id, label, field_type (select|text|checkbox),
  options (json, for select), is_required, sort_order

sp_event_registration_field_values
  id, registration_id, field_id, value
```

`sp_event_registration_fields` is generic on purpose — it covers meal choice, dietary restrictions, t-shirt size, "bringing a guest," anything a specific society's event needs, all admin-editable per event rather than a fixed column list. This is the same instinct as everything else in the plugin: no hardcoded user-facing content.

### Modified schema

Add to `sp_event_registrations`: `ticket_type_id` (nullable FK), `walk_in` (tinyint, default 0), `checked_in_at` (datetime, nullable), `checked_in_via` (varchar — `kiosk` / `admin` / `qr`).

Check-in itself is still "set `attended = 1`" (reusing the existing column the treasurer's report already relies on) — the new columns just capture *when* and *how* it happened, for the kiosk's own audit trail.

### Flows

- **Pre-registered check-in:** search by name/email, or scan a QR code from their confirmation email (recommend adding QR to the existing confirmation email template — cuts the search step entirely for a line at the door). Tap to check in.
- **Walk-in registration:** search finds no member → capture guest name, optional email/phone, pick ticket type, answer any per-event custom fields, pick a slot if the event has 1-on-1/multi-session slots, pay if there's a fee. Respects the event's `registration_limit` and any per-slot `capacity` — full means waitlisted, same as online registration today.
- **Ticket purchase at the door:** same as walk-in registration with a fee attached, charged via the reader or manual-entry fallback.

---

## Feature 2: Library sign-in/out

This has no existing analog in the plugin — it's a visitor log, deliberately separate from any notion of circulation.

### New schema

```
sp_library_visit_purposes           -- admin-editable, not hardcoded
  id, label, sort_order, is_active, logs_volunteer_hours (tinyint)

sp_library_visits
  id, member_id (nullable), visitor_name, visitor_email (nullable),
  visitor_phone (nullable), is_member (tinyint), purpose_id,
  signed_in_at, signed_out_at (nullable), kiosk_device_id, notes
```

Purpose list ships with sensible defaults (Research / Volunteering / Meeting / Other) but is admin-editable, same as event/library categories elsewhere.

### Flows

- Name capture is always required; member/non-member is a toggle (member → optional lookup to attach `member_id`); email/phone is explicitly optional for non-members, matching the ask.
- If purpose is "Volunteering" and that purpose has `logs_volunteer_hours` set, the sign-out writes a row to the existing `sp_volunteer_hours` table with `source_type = 'library_duty'` — that source type already exists in the plugin's volunteer-hours attribution enum, it's just never been written to by anything except manual admin entry. This closes a loop that already half-exists.
- **Sign-out is the weak point of every kiosk like this — people just leave.** Design for it rather than assuming it works: an end-of-day cron auto-closes any visit still open past closing time (using the event/store settings' existing timezone handling), and the admin gets a simple "still open" list to close stragglers by hand. Don't advertise accurate visit-duration reporting without this fallback built in from the start.

---

## Feature 3: Membership join & dues renewal

### Reused as-is
- `sp_membership_tiers`, `sp_member_payments`.
- The Stripe Payment Element / PayPal pattern already used by Store and Donations, for the manual-entry payment fallback.

### Flows

- **Join:** a constrained version of the existing admin "Add Member" form — name, email, phone, address, tier pick — filled in by the walk-up visitor themselves. Creates the WP user + `sp_members` row and, if there's a fee, takes payment right there.
- **Renew:** search by name/email, confirm identity (last name + something else, not just a name match, given this is an unattended public device), show current tier and amount due, pay.
- **Cash/check:** neither is collectible by the device. The kiosk logs the intended tier/amount as a pending item the treasurer reconciles at the desk against the physical payment — same pattern as the cash/check-elsewhere handling that already exists conceptually in the Orders/Donations flow, just without an "unpaid pending order" the kiosk itself can settle.

---

## Security & privacy

- **Idle timeout** returns to an attract screen and discards any in-progress form after a short window (needs a number — see open questions). This matters more here than on a typical POS kiosk because non-member phone/email is being captured in a public lobby.
- **Device lockdown:** recommend Single App Mode via a configuration profile (Apple Business Manager / Apple Configurator) as the primary lockdown, with Guided Access as the fallback for societies without MDM. Either way, a volunteer needs a way *inside the app* to back out to settings without knowing the OS-level passcode — an admin PIN screen reachable from a low-key gesture (e.g. a long-press on the logo), not a big visible "exit kiosk" button.
- **Non-member data capture** (name/optional email/phone at both the event walk-in flow and the library sign-in flow) needs to go through the same encryption-at-rest and export/erasure machinery `docs/modules/privacy-gdpr.md` already describes for members — a non-member's optional contact info is still personal data subject to the same rules, it shouldn't get a separate, weaker path just because it came from a kiosk.
- **Device revocation** must take effect immediately (token check on every request, not a cached grant) since a lost or stolen iPad is the realistic threat model for hardware that lives on an unattended table.

---

## Open questions

These are genuinely undecided — flagging rather than quietly picking an answer:

1. **Attended vs. unattended assumption.** Is a volunteer always standing at this iPad, or is it sometimes truly self-service with nobody nearby? This changes how much confirmation/guardrail UI each flow needs (an "are you sure" screen matters a lot more unsupervised).
2. **Idle-timeout duration** and whether it's admin-configurable per kiosk or a fixed value.
3. **One app, three modes, or does an admin configure a kiosk to run just one workflow** (e.g., a library-only kiosk that never shows the events flow) via the existing per-device module scope? The module-scope design above already supports either — this is about whether the app shows a mode picker or just goes straight into its one configured mode.
4. **Receipt/confirmation delivery** — email only, SMS, on-screen only, or a receipt printer as an optional accessory? Printer support is a real hardware/cost decision, not just a UI toggle.
5. **App Store listing.** Public app anyone can download and pair (like a lot of POS companion apps), or distributed privately per society via Apple Business Manager? Affects App Store review requirements (an app whose entire purpose is unusable without a paired backend has its own review considerations) and how a new society gets it onto their iPad.
6. **Identity confirmation on renewal.** What counts as "confirmed enough" for an unattended device to show someone else's dues status and take a payment against their account — last name + email, last name + zip, something else?

---

## Suggested build order

Not phased releases — everything here ships together — but this is the order that avoids building on top of something not yet proven:

**Foundational (build first, everything else depends on it):**
- Kiosk pairing, device tokens, module scoping, the new REST namespace.
- Offline queue + sync with `action_id` idempotency.

**Core workflows (parallelizable once the above exists):**
- Event check-in + walk-in registration + ticket types/custom fields.
- Library sign-in/out.
- Membership join/renew.

**Payment (needs the reader hardware in hand to build against):**
- Stripe Terminal integration, manual-entry fallback, `card_present` payment method plumbing.

**Hardening:**
- Idle timeout / screen-clear, admin PIN exit, device lockdown guidance, privacy/erasure integration for non-member data.
