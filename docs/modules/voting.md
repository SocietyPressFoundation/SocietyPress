# Voting & Elections

Online ballots for board elections, bylaw amendments and member surveys. You can limit a ballot to certain membership tiers — for example, only full members vote on bylaw changes.

## What you can do

- Run any number of ballots — elections, referendums, surveys.
- Ask single-choice, multiple-choice or yes/no questions, several to a ballot.
- Set a voting period, or leave the dates blank and open and close voting by hand.
- Let every active member vote, or only members in the tiers you pick.
- One vote per member per ballot, enforced by the system. A vote can't be changed once it's submitted.
- Offer an "Abstain" choice on every question.
- Choose who sees the results, and when.

## How to create a ballot

**SocietyPress → Ballots → Create New Ballot.**

- **Title** — what's being voted on.
- **Description** — context for voters.
- **Ballot Type** — Election, Referendum or Survey. It's a label members see on the ballot.
- **Who Can Vote** — All Active Members, or Specific Membership Tiers (tick the tiers).
- **Voting Period** — start and end date and time. Both are optional.
- **Allow Abstain** — adds an Abstain choice to every question.
- **Results Visibility** — After Voting Closes, Visible During Voting, or Admin Only.

Then add questions with **Add Question**. For each one, the question text, its type (single choice, multiple choice with a maximum number of selections, or yes/no) and its choices. A board election usually has one question per office.

Counting is plurality — the choice with the most votes wins. Ranked-choice isn't available.

Save. A new ballot is a draft; members can't see it yet.

## How to open and close voting

On the **Ballots** list, click **Open Voting** on the ballot. Members can vote from then until the end of the voting period. If you set a start date in the future, the ballot appears when that date arrives.

When the end date passes, the ballot closes by itself. To close it early, or to close a ballot with no end date, click **Close Voting**.

## How members vote

Members vote on a Voting page. If you don't have one, the Ballots screen says so and offers **Create the Voting page**, which makes it and offers to add it to your menu. To make it by hand: **Website → Pages → Add New Page**, set **Page Type** to **Voting**, publish.

Members sign in and go to that page. Under **Open Ballots** they see each ballot they're eligible for, with its closing time, and vote with **Submit Vote**. Ballots they're not eligible for don't appear. Lapsed and expired members, and visitors who aren't signed in, can't vote.

Under **Past Ballots** they see the 20 most recently closed ballots and, unless a ballot's results are Admin Only, its results.

## Who sees the results

- **After Voting Closes** — results appear under Past Ballots once the ballot closes.
- **Visible During Voting** — a member sees the running totals after casting their vote, marked as still in progress.
- **Admin Only** — members never see the results; admins do.

Admins see full results any time from the **Results** link on the Ballots list: eligible voters, votes cast, participation rate, and a bar chart for each question. **Export Results (CSV)** downloads the totals for your minutes.

## Votes are not secret

SocietyPress records which member cast each vote. Members never see who voted for what, but anyone with access to the database could. If your bylaws require a secret ballot, keep using paper for that vote for now. Secret ballots are on the roadmap.

## If something looks wrong

**A member says they should be able to vote but the ballot isn't on their page.** Check their membership is active and their tier is one the ballot allows. Ballots a member isn't eligible for don't show at all.

**Results don't add up to the number of voters.** On a multiple-choice question one voter can pick several options. On single-choice and yes/no questions, abstentions are counted separately.

**A ballot isn't showing on the Voting page.** Check it's been opened on the Ballots list, and that its start date has arrived.

## Related guides

- [Members](members.md) — tiers decide who can vote
- [Governance](governance.md) — committees and meetings
