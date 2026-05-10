# SocietyPress Roles & Permissions Specification

**Status:** Implemented in v1.0 — this spec is retained as historical record. The shipped system covers 10 access areas and 8 role templates as described below; current behavior is canonical and any drift between this spec and the code should be resolved in favor of the code.
**Originally drafted:** 2026-03-12
**Last reviewed:** 2026-05-10

---

## Design Principle

Stoopidly simple. Harold assigns people to jobs. The system figures out what they can see and do. No capability matrices, no permission trees, no enterprise jargon.

---

## Layer 1: Site-Wide Roles

A site-wide role determines which sections of the backend a person can access. Each role is a pre-built bundle of access — but Harold can customize any individual's access by checking/unchecking boxes.

### Pre-Built Roles

| Role | Backend Access |
|------|----------------|
| **Webmaster** | Everything. Full admin. This is Harold. |
| **President / Secretary** | Members, documents, meeting minutes, governance, reports |
| **Treasurer** | Finances, dues, donations, campaigns, reports |
| **Librarian** | Library catalog, resource links, event registrations (front-desk duty) |
| **Events Coordinator** | Events, speakers, calendar, event registrations, event categories |
| **Newsletter Editor** | Newsletter archive (upload/manage PDFs) |
| **Publicist** | Blast email, email templates, email log |
| **Volunteer Coordinator** | Volunteer opportunities, volunteer hours |
| **Committee Chair** | Governance (their committees only — see Layer 2) |
| **Board Member** | Read-only access to reports, finances, members (oversight without edit power) |

### How It Works for Harold

1. Harold goes to a member's profile (or the User Manager)
2. Picks a role from a dropdown — sensible defaults auto-apply
3. Optionally toggles individual access areas on/off
4. Saves. Done.

Harold never sees the word "capability" or "permission." He sees:
- A role dropdown
- A checklist of plain-English access areas ("Library," "Events," "Finances," etc.)
- Checkboxes pre-filled based on the role, fully editable

### Access Areas (the checklist)

These map directly to flyout menu groups. If a box is unchecked, that entire flyout group disappears from the user's admin sidebar.

| Access Area | What It Controls |
|-------------|------------------|
| Members | Member list, import/export, tiers, groups, pending changes |
| Events | Events, categories, speakers, import, registrations |
| Library | Catalog, categories, import, enrichment |
| Resources | Resource links, resource categories |
| Documents | Documents, document categories |
| Newsletters | Newsletter archive |
| Finances | Payments, dues, donations, campaigns, orders |
| Governance | Committees, volunteer hours, volunteer opportunities |
| Email | Blast email, templates, email log |
| Gallery | Photo albums |
| Records | Genealogical record collections |
| Reports | Reports, annual report, audit log |
| Appearance | Themes, pages, media, menus, widgets, design settings |
| Settings | All settings tabs, module toggles |

**Webmaster** gets all boxes checked. Everyone else gets a subset based on their role. Harold can always override.

### Access Levels Within Each Area

For most roles, access means full CRUD (create, read, update, delete) within that area. Two exceptions:

- **Board Member** — read-only across their checked areas. Can view but not edit.
- **Librarian + Event Registrations** — can manage registrations (add/remove attendees) but cannot create/edit/delete events themselves.

This means some access areas need a simple sub-toggle:

- [ ] Events: **Manage events** / **Manage registrations only**
- [ ] Members: **Full access** / **View only**
- [ ] Finances: **Full access** / **View only**

Keep sub-toggles to the minimum. Most areas are all-or-nothing.

---

## Layer 2: Committee-Scoped Access

Committee access is NOT configured by Harold on a permissions screen. It's automatic based on governance data that already exists.

### Rules

1. **Committee Chair** → can manage that committee:
   - Add/remove committee members
   - Create meetings (date, time, location, agenda)
   - Upload meeting minutes
   - Edit committee description

2. **Committee Member** → can view that committee:
   - See member list, meeting schedule, agendas, minutes
   - Cannot edit anything

3. **One person, multiple committees** — Betty can be:
   - Chair of Finance Committee (manages it)
   - Chair of Library Committee (manages it)
   - Member of Events Committee (views it)
   - Each committee is independently scoped

4. **No configuration needed** — Harold already assigns people to committees and marks who's chair through the existing Governance module. Permissions flow from that data automatically.

### What Committee Chairs See in the Backend

When a committee chair logs in, their Governance section shows ONLY their committees — not every committee in the society. If they're also a member of other committees, those appear in a separate "My Committees" read-only view.

A chair's management interface for their committee:
- Committee member list (add/remove)
- Meetings list (create, edit, set agenda)
- Minutes (upload per meeting)
- Committee details (description, meeting schedule)

This is a focused, scoped view — not the full governance admin that Harold sees.

---

## Layer 3: Frontend Member Access (Already Exists)

This layer is already partially built:
- Members-only pages (the checkbox we just built)
- Members-only documents (per-document access_level)
- Directory requires login
- Various SP templates require login

No changes needed here. This spec covers backend access only.

---

## Implementation Strategy

### Database Changes

**New table: `wp_sp_user_roles`**

| Column | Type | Purpose |
|--------|------|---------|
| id | BIGINT AUTO_INCREMENT | Primary key |
| user_id | BIGINT | WordPress user ID |
| role_slug | VARCHAR(50) | Pre-built role slug (webmaster, treasurer, etc.) |
| access_areas | TEXT (JSON) | Array of enabled access area slugs |
| access_modes | TEXT (JSON) | Per-area mode overrides (e.g., {"events": "registrations_only"}) |
| created_at | DATETIME | When assigned |
| updated_at | DATETIME | Last modified |

**No changes to existing tables.** Committee chair/member relationships are already stored in the governance tables.

### WordPress Integration

- SocietyPress roles do NOT use WordPress' native roles/capabilities system. Harold doesn't know what `wp_capabilities` is and shouldn't have to. We store roles in our own table and check them in our own code.
- WordPress admin access: users with an SP role get the `subscriber` WordPress role (can log in) but see a fully custom admin experience — the SocietyPress dashboard with only their permitted sections.
- The existing `admin_init` block-non-admin hook needs to be updated to allow SP-roled users through to their permitted pages.

### Admin Menu Filtering

The existing priority-1000 `admin_menu` hook that hides disabled modules can be extended to also hide sections the current user doesn't have access to. Same mechanism, additional filter source.

### How Login Changes

Currently: non-admins are blocked from `/wp-admin/` entirely.
After: non-admins WITH an SP role see a filtered admin. Non-admins WITHOUT an SP role are still blocked.

---

## What This Does NOT Include (Intentionally)

- **Granular per-record permissions** — no "Betty can see Member #42 but not Member #43." Access is by area, not by row.
- **Approval workflows** — no "treasurer submits, president approves." If you have access, you can act.
- **Time-based access** — no "Betty has access until December." Remove the role when it's over.
- **Audit trail per role change** — the existing audit log captures admin actions. Role assignment is just another admin action.
- **Self-service role requests** — Harold assigns roles. Nobody requests access.

---

## Open Questions for Charles

1. **Should committee chairs be able to send email blasts scoped to their committee?** E.g., Library Committee chair sends an email to just Library Committee members. Or is blast email always the publicist's job?

2. **Should there be a "read-only" version of every access area?** The Board Member role is read-only, but should Harold be able to make ANY role read-only for specific areas? (E.g., treasurer can view members but not edit them.)

3. **Do committee meeting minutes go in the Documents module or in the Governance module?** Right now committees exist in Governance. Minutes could live there (tied to a specific meeting) or in Documents (as a general upload). The ENS site puts them under "Members Only > Meeting Minutes." Governance feels more natural — minutes belong to a meeting, not to a generic document bucket.

4. **Multiple roles per person?** Betty is treasurer AND events committee chair. Does she get one role ("Treasurer") with extra access areas checked, or two roles stacked? Single role + customized checkboxes is simpler.

5. **What should non-webmaster admins see as their "home" screen?** Harold sees the full dashboard. Should the treasurer see a finance-focused dashboard? Or just the same dashboard filtered to relevant widgets?
