# AI Assistant

An assistant that answers questions on your website using your own site — your calendar, your catalog, your pages — plus a second one that helps whoever is running the site figure out how to do something. You supply an API key from Anthropic and pay them directly for what it uses. SocietyPress never sees the traffic and takes no cut.

## What you can do

- Put a chat button on your website that answers "when is the next meeting", "do you have anything on Fannin County", "how much is membership".
- Answer from your site's real content, so the answers stay current when the calendar changes.
- Keep it members-only, or take it off the public site and put it on one page instead.
- Give volunteers an "Ask SocietyPress" screen that explains how to do things *on this site* — which features you have switched on, which screens they can open.
- See what visitors are asking, which is usually the most useful thing it produces.
- Cap what it can cost, per person and per day.

## Before you start: the API key

The assistant talks to Claude, made by Anthropic. You need an account and an API key.

1. Go to **console.anthropic.com** and sign up.
2. Add a payment method and a small amount of credit. Start with $10 — for most societies that is months of use.
3. Create an API key. Copy it; you only get to see it once.
4. In SocietyPress: **Settings → AI Assistant**, paste the key, save.

The key is stored encrypted, the same way your payment keys are. If you would rather it not be in the database at all, put it in `wp-config.php` instead:

```php
define( 'SOCIETYPRESS_ANTHROPIC_KEY', 'sk-ant-...' );
```

The constant wins if both are set, and the settings box goes read-only so nobody overwrites it by accident.

## How to switch on the public assistant

**Settings → AI Assistant → Show the assistant to.** Pick who sees it:

- **Everyone** — a chat button in the bottom corner of every page.
- **Logged-in members only** — same button, but only for members. Use this if you want the bill bounded by your membership roll rather than by whoever finds the site.
- **Nobody** — no button anywhere. The shortcode still works, so you can put the assistant on one "Ask us" page instead of following visitors around.

To put it on a single page, add `[societypress_ai_assistant]` to that page. When a page has the shortcode on it, the floating button stays away so you do not get two of them.

## How to change the button icon

**Settings → AI Assistant → Button icon.** Four choices, shown as pictures rather than named in a list:

- **Speech bubble** — the default. Says "this is a chat", which the visitor can already see.
- **Leaf** — the obvious one for a genealogical society.
- **Tree** — a small tree, if you want something less on-the-nose than a leaf.
- **Family tree** — a pedigree chart: two parents joined to a child. The most distinctive of the four, and the only one that could not belong to any other kind of website.

One thing worth knowing before you pick the leaf: Ancestry.com's "shaky leaf" hint is probably the single most recognised icon in genealogy. A leaf on your chat button will remind some of your members of Ancestry — which reads as familiar and on-theme to most people, but occasionally as though the button is an Ancestry feature. The family tree has no such association.

Whichever you pick, the label beside it is whatever you put in "What to call it".

## How to make its answers better

The assistant searches your site for each question and reads the results before answering. Three things improve it, in order of how much they help:

**1. Fill in Settings → Organization.** Address, phone, contact email. These get sent with every question, so "how do I reach you" is answered without a search.

**2. Fill in "Things it should always know"** (Settings → AI Assistant). This is for facts that are true but written nowhere on your site — where to park, that the library closes in August, that research requests go to a particular officer. A few lines here fix the questions that searching cannot.

**3. Fix the site.** If the log shows five people this month asking when meetings are, the answer is buried. Putting it on the home page helps everyone, including the people who never open the chat.

## How to see what people are asking

**Settings → AI Assistant**, scroll past the settings. The last 25 questions from the public assistant are listed there, and the last 30 days of usage — questions answered, tokens used, estimated cost — sit at the top of the page.

The same question asked repeatedly is a signal, not a nuisance. It means the answer exists on your site and nobody can find it.

Conversations are kept for 90 days by default and then deleted. Change that under "Keep conversations for". They are included in member data exports and erasures, so a member asking for their data gets their questions too.

## How to help your volunteers

**Settings → AI Assistant → Help for volunteers**, tick the box. This adds **SocietyPress → Ask SocietyPress** to the menu.

It is different from the public assistant: it knows which modules you have switched on here, which admin screens the person asking is allowed to open, and which screen they were on when they asked. So "how do I add a speaker to an event" gets steps that match this site, with a link to the screen — and if Events is switched off, it says that first instead of giving instructions that go nowhere.

It cannot change anything. It explains what to click.

## How to control what it costs

Three settings under **Cost controls**:

- **Questions per person, per hour** (12 by default). A real conversation is rarely more than a dozen questions.
- **Questions across the whole site, per day** (400 by default). Once reached, visitors are told to come back tomorrow. Volunteers asking through the admin screen do not count toward it.
- **How hard it thinks.** "Quick" is right for looking things up on your site. Raise it only if answers feel shallow.

And the model, under the main settings:

| Model | When to pick it |
| --- | --- |
| **Claude Opus 5** | The default. Best answers, and the most reliable at saying "I don't know" rather than guessing. |
| **Claude Sonnet 5** | Close behind, noticeably cheaper. A good choice for a busy public site. |
| **Claude Haiku 4.5** | Cheapest and fastest. Fine for "when is the meeting"; weaker on anything needing judgement. |

The cost figure on the settings page is an estimate at the model you have selected. Anthropic's own console is the authority on what you actually owe.

## What it will not do

It has **no access to member records** and cannot look anybody up. The member directory is deliberately excluded from everything the assistant is allowed to read — your membership roll is the most sensitive thing you hold, and it does not leave the site.

It cannot take a booking, process a payment, or change anything. It is told to say so and point at your contact details.

It only sees what the person asking could already see. A logged-out visitor asking about a members-only event gets nothing, because the search that feeds it applies the same visibility rules as the rest of the site.

## If something looks wrong

**The chat button isn't there.** Check three things in order: the AI Assistant module is on (Settings → Modules), a key is saved (Settings → AI Assistant), and "Show the assistant to" is not set to Nobody. If the page has the shortcode on it, the floating button hides itself on purpose.

**"The Anthropic API key was rejected."** The key is wrong, was revoked, or the account has no credit. Check console.anthropic.com — a zero balance looks exactly like a bad key from here.

**It says it doesn't know something that is on the site.** It searches the same way your site search does. Try that search yourself: if the site search does not find it either, the content is the problem, not the assistant. Put the fact in "Things it should always know" as a stopgap.

**It gave a wrong answer.** Tell it less, not more. Anything vague in "Things it should always know" gets treated as fact. If the wrong answer came from a stale page, fix the page — the assistant is reading your site faithfully.

**Answers take a long time.** Set "How hard it thinks" to Quick, or move to Claude Haiku 4.5. Questions that make it read a lot of catalog entries are always slower.

**It stopped answering partway through the month.** You hit the daily cap, or the account ran out of credit. Both are on the settings page and in Anthropic's console respectively.
