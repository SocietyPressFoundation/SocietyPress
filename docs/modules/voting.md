# Voting & Elections

Online ballots for board elections, bylaw amendments, and member surveys. Tier-based eligibility (e.g., "only full members vote on bylaw changes; subscriber-tier members don't") is built in.

## What you can do

- Run any number of ballots — board elections, bylaw votes, surveys.
- Single-choice, multi-choice, or yes/no question types.
- Open and close periods (votes accepted only between dates).
- Tier-based eligibility (every active member, or only specific membership tiers).
- One vote per eligible member, enforced by the system.
- Optional anonymous voting (votes recorded but not tied to the voter).
- Results page with vote counts, percentages, eligible-vs-cast turnout.
- Optional "abstain" choice on every question.

## How to create a ballot

**SocietyPress → Voting & Elections → Add Ballot.**

- **Title** — what's being voted on.
- **Description** — context for voters.
- **Open / close dates** — when voting is accepted.
- **Eligibility** — All active members, or specific tiers.
- **Anonymous** — toggle.
- **Allow abstain** — toggle.

Save. Then add questions. Each question:

- Question text.
- Type — single choice, multi choice, yes/no.
- Choices — for single/multi-choice.

Multiple questions per ballot. A board election typically has one ballot with one question per office (or one question listing all candidates if you're using ranked-choice — currently SocietyPress is plurality only; ranked-choice is roadmapped).

## How members vote

The ballot appears on any page hosting the voting widget (drop it from the page builder, or use `[sp_ballot id="..."]`). Eligible members see the questions and submit. Non-eligible members see a message explaining why they can't vote ("This ballot is open only to full members").

After submitting, the member sees a confirmation. They can't change their vote — submission is final.

If they hit the page after the close date, they see a results page (if results have been published) or a "voting has closed" message.

## How to publish results

**SocietyPress → Voting & Elections → [Ballot] → Results.** Two options:

1. **Publish to members** — results visible to logged-in members.
2. **Publish to public** — results visible to anyone.

Until you click Publish, results are visible only to staff (admins). This lets you verify totals before announcing.

## How tier eligibility works

When you pick "Specific tiers" for eligibility, you check off which membership tiers are allowed to vote. Members in any *unchecked* tier see "you're not eligible for this ballot."

This is the standard pattern for: bylaw votes (only full members, not subscribers); board elections (only members in good standing, not honorary or expired); surveys (everyone, including subscribers).

## If something looks wrong

**A member says they should be eligible but the system disagrees.** Check their membership tier on their member record. Eligibility is per-tier; if their tier isn't in the ballot's allowed list, they don't vote.

**Results don't add up.** The ballot might allow multi-choice answers (a single voter can pick multiple options on one question). Verify the question type. For yes/no votes, total responses should equal the number of voters.

**Anonymous ballot still shows voter names somewhere.** It shouldn't. Anonymous ballots write `voter_user_id = NULL` to the ballot_votes table. If you see names in any results view, file a bug — that's incorrect.

**Ballot disappeared from the voting page.** Check the open/close dates. If "now" is outside the window, the ballot doesn't render. Edit dates if you need to extend.

## Related guides

- [Members](members.md) — tiers drive eligibility
- [Governance](governance.md) — bylaw votes pair with meeting minutes
