# Governance

Committees, officers, meetings, volunteer hours. The administrative backbone — who's on which committee, who chairs what, when did the board last meet, how many hours did Mary contribute this year.

## What you can do

- Track committees with names, chairs, members, and active/inactive flags.
- Assign committee chairs who get a "My Committee" dashboard with scoped access.
- Record meeting minutes (board, membership, committee). Attach agendas + minutes PDFs.
- Track volunteer hours by activity and source (committee work, event volunteering, library duty, help responses, research cases, etc.).
- Generate annual volunteer-hours reports (board, IRS Form 990 Schedule O, grantor reports).

## Committees

**SocietyPress → Committees.** Add committee, set name, optional slug, optional description, optional chair (a WP user). Active flag controls whether it appears in dropdowns. Sort order controls display sequence.

Once committees exist, they show up everywhere relevant — the volunteer-opportunity edit form's committee dropdown, the meeting edit form's committee picker, the public committee roster widget.

Members are added to committees through the volunteer-roles system. **SocietyPress → Volunteer Roster** lets you assign members to committees with role labels ("Chair," "Vice Chair," "Member").

## Committee chairs

Anyone set as `chair_user_id` on an active committee gets a chair dashboard at **SocietyPress → My Committee**. The dashboard is scoped to their committee(s) — upcoming meetings, upcoming events, open volunteer opportunities, recent minutes — with one-click links to create or edit each.

Chair status is derived live from the committees table; no separate role assignment to forget. A chair can chair multiple committees; the dashboard aggregates across all of them.

Admins still see the full menu, unchanged. Chairs see a trimmed menu — just their committee dashboard plus filtered list pages for events, meetings, and volunteer opportunities they own.

## Meetings & minutes

**SocietyPress → Meetings & Minutes.** Add meeting, pick type (Board / Membership / Committee), pick committee (only for Committee type), set date, location, attendance, agenda URL, minutes URL, inline notes, visibility (public / members-only / committee-only).

Visibility lets you keep board meeting notes members-only, committee meeting notes committee-only, and membership meeting notes public.

The meeting edit page accepts both an attached PDF (agenda or minutes) and inline notes. Different societies record at different formality — the inline notes field is for societies that just paste a paragraph; the PDF attachments are for formal minutes documents.

## Volunteer hours

`sp_volunteer_hours` is the unified ledger. Every helping action — committee work, event volunteering, library duty, help responses, paid research cases — writes a row here.

**SocietyPress → Volunteer Hours** shows the full ledger. Filter by member, by source type (help_request / research_case / committee / event / meeting / library_duty / other), by date range. Export as CSV for reporting.

Member-attributed totals appear in three places:

- The member's record (Volunteer Hours tab).
- The member directory if you've enabled the "Volunteer Hours" widget on each member's card.
- The `[sp_my_volunteer_hours]` shortcode on a member-area page (logged-in member sees their own).

For annual reporting, total all rows in a date range, group by source type. The "Volunteer Stats" widget on a public dashboard does this automatically.

## How to use this for IRS Form 990

Schedule O of Form 990 asks for narrative about the organization's mission and activities, including volunteer involvement. The volunteer-hours export gives you a defensible number. Run **SocietyPress → Volunteer Hours → Export → Year 2026** and the CSV contains every logged hour with attribution.

For grantor reports requesting a volunteer-hour total: same export, sum the hours column.

## If something looks wrong

**A chair can't see "My Committee."** Check the committee record — `chair_user_id` must point at the WP user ID, not the member ID. They're often the same number but sometimes not. Edit the committee, re-pick the chair from the dropdown, save.

**Volunteer hours aren't aggregating.** Check that `source_type` is set on the rows. Older imports (pre-2026-04) didn't write a source_type — those rows show under "Other" in the breakdown. Run a quick SQL update to backfill if you care about clean reports.

**Meeting visibility isn't enforced.** Members-only / committee-only visibility uses the active session. Verify the visitor is logged in (members-only) and is in the right committee (committee-only). The committee membership is derived from sp_volunteer_roles where role_type = 'committee'.

**Committee dropdown is empty on event/meeting forms.** The committee table has rows with `active = 0`. Activate the ones you want visible (or add new ones).

## Related guides

- [Help Requests](help-requests.md) — response time logs into volunteer hours
- [Research Services](research-services.md) — case hours log here too
- [Voting](voting.md) — tier eligibility for board elections
- [Members](members.md) — committee assignments are member-attributed
