# Hero Slider Quick Start Guide

## Setup (2 minutes)

1. Go to **WordPress Admin → Appearance → Customize**
2. Click **Hero Slider** in left sidebar
3. Adjust **Slider Height** if desired (default: 600px)
4. For each slide (1-6):
   - Upload an **Image** OR **Video (MP4)**
   - Add **Text** (optional)
   - Add **Link URL** (optional)
5. Click **Publish**

## Text Formatting

In the text field, you can use HTML tags:

```html
<strong>Bold Text</strong>
Welcome to the
<em>Italic text</em>
San Antonio Genealogical and Historical Society
Founded 1969
```

**Result:** Text appears large and bold with extra emphasis on `<strong>` words.

## Image vs Video

- **Image**: Upload JPG/PNG (1920 x 800px recommended)
- **Video**: Upload MP4 (auto-plays, muted, loops)
- If both are set, **video takes priority**
- Videos work on all modern browsers and mobile devices

## Best Practices

### Images
- Use high-resolution photos (1920px wide minimum)
- Keep important subjects centered
- Darker images work better with white text overlay

### Videos
- Keep videos under 10MB for fast loading
- Use 1920x1080 resolution (or 1920x800 for exact slider size)
- Compress videos before uploading (use Handbrake or similar)
- Test on mobile - videos auto-play but are muted

### Text
- **3 lines maximum** for best readability
- Use `<strong>` tags for your most important words
- Keep it concise - visitors won't read paragraphs on a slider
- White text works best over dark images/videos

### Links
- Link to your most important pages
- Events page, membership page, or featured content
- Leave blank if slide is purely informational

## Examples

### Text-Only Slide
```
<strong>Welcome to the</strong>
San Antonio Genealogical and Historical Society
Founded 1969
```

### Multi-Line with Formatting
```
<strong>Discover Your Roots</strong>
Join us for our annual <em>Family History Conference</em>
<strong>March 15-17, 2026</strong>
```

### Simple and Bold
```
<strong>Preserving History Since 1969</strong>
```

## Troubleshooting

**Text is hard to read:**
- Use darker images/videos
- The theme adds a dark overlay automatically, but very light backgrounds may need darker source material

**Video doesn't play:**
- Make sure it's MP4 format
- Check file size (under 10MB recommended)
- Videos are muted by default (browser requirement for autoplay)

**Slider doesn't appear:**
- Make sure at least one slide has an image or video uploaded
- Empty slides (no image/video) are hidden automatically

**Text looks too small/large:**
- Current styling is optimized for 2-3 lines of text
- Contact developer to adjust font sizes if needed

## Pro Tips

1. **First slide is most important** - Most visitors only see the first slide
2. **Use 3-5 slides total** - Too many and they won't all be seen
3. **Autoplay timing** - Slides change every 5 seconds
4. **Mobile-friendly** - Slider automatically adjusts for phones/tablets
5. **Accessibility** - Navigation arrows and dot pagination included

## Advanced: Edge-to-Edge Display

The slider now spans the full width of the browser window (edge-to-edge) for maximum visual impact. The text content stays centered and contained for readability.

## Need Help?

The slider is fully configured and ready to use. Just upload your media and add your text!

For questions about customization beyond what's in the Customizer, contact your developer.
