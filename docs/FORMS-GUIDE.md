# Building and Using Forms

Forms let you collect information from visitors and members — volunteer
sign-ups, research questions, cemetery-access requests, event RSVPs,
anything you'd normally handle by email or paper. You build the form once,
put it on a page, and every time someone fills it out you get an email and
a saved copy you can look back on any time.

No technical knowledge is needed. If you can fill out a form, you can build
one.

---

## Before you start: turn Forms on

Forms is one of the optional features. If you don't see **Forms** in the
left-hand menu, it just needs switching on:

1. In the left menu, go to **Settings → Modules**.
2. Find **Forms** in the list and switch it on.
3. Save.

**Forms** now appears in the left-hand menu. You only ever do this once.

---

## Part 1 — Build a form

### Step 1: Start a new form

1. Click **Forms** in the left menu.
2. Click **Add New Form** (top of the page). The first time, the button
   says **Create Your First Form** — same thing.

You're now on the form editor.

### Step 2: Name it

At the top you'll see two boxes:

- **Form name** — this is just for *you*. It's the name you'll see in your
  list of forms. Visitors never see it. Something like `Volunteer Interest`
  or `Research Request` is perfect.
- **Heading shown to visitors** — optional. This is the title that appears
  *above* the form on the page, for the public to see. Leave it blank if
  you don't want a heading.

### Step 3: Add your questions (fields)

Each thing you want to ask is a **field**. Click **Add field** for each
question. For every field you choose:

- **Field type** — what kind of answer you want (see the list below).
- **Question label** — the wording the visitor reads, e.g. *"Your name"* or
  *"Which cemetery?"*
- **Required** — tick this if the visitor must answer it before they can
  send the form.

Here are the field types you can pick, in plain terms:

| Choose this | When you want… |
|---|---|
| **Single line text** | A short typed answer — a name, a title. |
| **Paragraph text** | A longer typed answer — a message, a description. |
| **Email address** | An email — checked to make sure it looks valid. |
| **Phone number** | A phone number. |
| **Number** | A number only — a quantity, a year. |
| **Date** | A date, chosen from a little calendar. |
| **Drop down menu** | Pick one answer from a list you provide. |
| **Multiple choice (pick one)** | Pick one answer, shown as round buttons. |
| **Checkboxes (pick many)** | Tick as many as apply. |
| **File upload** | Let them attach a document or photo. |

**For the three list types** — *Drop down menu*, *Multiple choice*, and
*Checkboxes* — a **Choices** box appears. Type one option per line:

```
Monday
Tuesday
Wednesday
```

**Reordering questions:** use the little **up and down arrows** on each
field to move it. Use the **trash-can button** to remove one. (There's no
dragging — the arrows do it.)

**About file uploads:** visitors can attach a PDF, an image (JPG, PNG, GIF,
WebP), a Word document, a plain-text file, or a spreadsheet (CSV). The limit
is **8 MB** per file. Videos, zip files, and programs are not allowed.

### Step 4: Decide who gets the submission (Delivery)

Scroll to the **Delivery** card. This controls the email you receive each
time someone submits. (A saved copy is *always* kept regardless — these
settings only shape the email.)

- **Email subject** — the subject line of the email you'll get. Leave blank
  and it just uses the form's name.
- **Send notifications to** — the email address that should receive each
  submission, and optionally a name. **Leave the email blank to use your
  organization's email address** (set under Settings). If you want a
  specific person — say, the Membership Chair — type their address here.
- **Reply-to** — when you hit *Reply* on the notification email, this is
  where your reply goes. Leave it blank and your reply goes straight back to
  the person who filled out the form (as long as they gave an email).
- **Note at top of email** — optional text that appears above the answers in
  the email. Handy for a reminder like *"Forward these to the events team."*
- **Thank-you message** — what the visitor sees on the page right after they
  hit send. The default is *"Thank you! Your message has been sent."* —
  change it to anything you like.

> **Worth knowing:** if you leave the recipient blank *and* your
> organization email isn't set, the submission is still saved — but no email
> goes out. So either set a recipient here or set your organization email
> under Settings, and you'll never miss one.

### Step 5: Publish it

On the right, the **Status** box has two choices:

- **Published — live on the site** — visitors can use it.
- **Draft — hidden from visitors** — only you can see it while you work on
  it.

Click **Save Form**. You'll see a green **Form saved** confirmation.

Once saved, that same box shows you a short code like this:

```
[societypress_form id="4"]
```

That's the "address" of your form. You'll use it in Part 2 to place the form
on a page. You can copy it right from there.

---

## Part 2 — Put the form on a page

There are two ways, and they do exactly the same thing. Use whichever feels
easier.

### Option A — the short code

1. Open (or create) the page where you want the form — say, a "Volunteer"
   page.
2. Paste the short code from Step 5, for example `[societypress_form id="4"]`,
   into the page where you want the form to appear.
3. Update/publish the page.

The code disappears when the page is viewed and the real form shows in its
place.

### Option B — the page builder

If you build the page with SocietyPress's page builder:

1. Add the **Form** block (it's in the "action" group of blocks).
2. In its settings, use the **Which form?** dropdown to pick your form by
   name.

That's it — no code to copy.

> A form marked **Draft** won't show to the public even if it's on a page.
> You'll see a small "this is a draft" note as an editor; visitors see
> nothing. Set it to **Published** when you're ready.

---

## Part 3 — Read what people send in

### See your submissions

1. Click **Forms** in the left menu.
2. On the form's row, click **Submissions** (there's also a count showing
   how many have come in).

You'll see a list of everyone who's filled it out — when it arrived, who
from, and a short preview. **Unread ones are in bold.** Click any row (or
**View**) to see every answer in full. If someone attached a file, it's a
clickable link.

Tick the boxes on the left to **Mark as read** or **Delete** in bulk.

### Save them to a spreadsheet

On the Submissions page, click **Download CSV**. That gives you a
spreadsheet file (opens in Excel or Numbers) with every submission — one row
each, a column per question. Good for handing a list to a committee or
keeping your own records.

---

## Good to know

A few honest limits, so nothing surprises you:

- **Forms are open to everyone.** There's no "members only" setting — any
  visitor can fill one out. That's usually what you want for sign-ups and
  contact forms.
- **The thank-you is a message, not a new page.** After someone submits, the
  form is replaced by your thank-you message on the same page. It doesn't
  send them off to a different page.
- **Export before you delete.** Deleting a form also deletes all of its saved
  submissions, and that can't be undone. If you might want the records,
  click **Download CSV** first. (The plugin will ask you to confirm before
  deleting.)
- **Renaming a question later** can jumble older submissions, because the
  saved answers are filed under the question's wording at the time. If you
  need to change a question a lot, it's cleaner to add a new field.
- **Spam is handled for you.** Forms quietly block automated spam and limit
  how many times the same person can submit in an hour. You don't have to
  set anything up. On a shared computer (like one at the library), if many
  people submit within an hour, a later person might be asked to wait — rare,
  but worth knowing.

---

## A quick example, start to finish

Say you want a **volunteer sign-up** form:

1. **Forms → Add New Form.**
2. **Form name:** `Volunteer Sign-Up`. **Heading:** `Volunteer With Us`.
3. Add fields:
   - *Single line text* — "Your name" — Required
   - *Email address* — "Your email" — Required
   - *Phone number* — "Best phone number"
   - *Checkboxes* — "Where would you like to help?" with choices:
     `Library`, `Events`, `Research`, `First Families`
   - *Paragraph text* — "Anything else we should know?"
4. **Delivery:** put the Volunteer Coordinator's email in **Send
   notifications to**; set **Thank-you message** to
   `Thank you — we'll be in touch soon!`
5. **Status: Published → Save Form.**
6. Copy the short code, paste it onto your Volunteer page, publish.
7. As people sign up, watch them arrive under **Forms → Submissions**, and
   download the list for your next volunteer meeting.

Done. You built a working form and never touched a line of anything
technical.
