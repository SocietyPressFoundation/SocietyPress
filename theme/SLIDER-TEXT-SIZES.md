# Slider Text Size Guide

## How to Control Individual Line Sizes

You can now use HTML heading tags to make each line a different size.

## Text Size Options

From **LARGEST** to smallest:

### `<h1>` - Extra Large (Huge!)
```html
<h1>Welcome to the Society</h1>
```
**Best for:** Main headline, biggest impact

---

### `<h2>` - Large
```html
<h2>Sample Genealogical Society</h2>
```
**Best for:** Organization name, primary message

---

### `<h3>` - Medium-Large
```html
<h3>Discover Your Roots</h3>
```
**Best for:** Secondary headlines, taglines

---

### `<h4>` - Medium (Default)
```html
<h4>Founded 1959</h4>
```
**Best for:** Dates, locations, supporting text

---

### Regular Text (no tag) - Base Size
```html
Join us for events and research opportunities
```
**Best for:** Descriptions, body text

---

## Complete Examples

### Example 1: Three Different Sizes
```html
<h1>Welcome</h1>
<h2>Sample Genealogical Society</h2>
<h4>Founded 1959</h4>
```

**Result:**
- "Welcome" = Extra Large
- "Springfield..." = Large
- "Founded 1959" = Medium

---

### Example 2: Organization Intro
```html
<h2>the society</h2>
Preserving family history since 1959
<h3>Join Us Today</h3>
```

**Result:**
- Organization name = Large
- Middle line = Base size
- Call to action = Medium-Large

---

### Example 3: Event Announcement
```html
<h1>Annual Conference</h1>
<h3>March 15-17, 2026</h3>
Registration now open
```

**Result:**
- "Annual Conference" = Extra Large
- Date = Medium-Large
- Registration text = Base size

---

## Mix with Bold and Italic

You can combine heading sizes with `<strong>` (bold) and `<em>` (italic):

```html
<h2>Welcome to the <strong>Best</strong> Society</h2>
<h4>Est. <em>1959</em></h4>
```

---

## Tips

1. **Don't overuse `<h1>`** - It's VERY large. Use sparingly for maximum impact.
2. **`<h2>` is great for organization names** - Large enough to be prominent, not overwhelming.
3. **Use regular text for longer descriptions** - Easier to read.
4. **Keep it simple** - 2-3 different sizes per slide is plenty.
5. **Test on mobile** - Text automatically scales down on smaller screens.

---

## Current Text in Your Slide

Your current slide text is:
```
Welcome to the
the society
Founded 1959
```

To make it more impactful, try:
```html
<h3>Welcome to the</h3>
<h1>the society</h1>
<h4>Founded 1959</h4>
```

Or for a different look:
```html
<h2>Welcome to the</h2>
<h2>the society</h2>
<h3>Founded 1959</h3>
```

---

## Where to Edit

1. Go to **WordPress Admin → Appearance → Customize**
2. Click **Hero Slider**
3. Find your slide (e.g., "Slide 1 - Text")
4. Paste your HTML with heading tags
5. Click **Publish**

The changes appear instantly on your homepage!
