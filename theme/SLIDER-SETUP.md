# Hero Slider Setup Guide

## How the Slider Works

The hero slider automatically displays your **5 most recent blog posts that have featured images**. Each slide shows:
- The post's featured image as the background
- The post title
- The post excerpt
- A "Read More" button that links to the full post

## Setting Up Slider Content

### Step 1: Create or Edit a Post

1. Go to **WordPress Admin → Posts → Add New** (or edit an existing post)
2. Write your post title and content
3. Add an excerpt if you want custom text in the slider (optional - WordPress will auto-generate one if you don't)

### Step 2: Add a Featured Image

**This is critical - posts without featured images won't appear in the slider.**

1. In the post editor, look for the **Featured Image** panel on the right sidebar
2. Click **Set featured image**
3. Upload an image or select from your media library
4. **Recommended image size:** 1920 x 800 pixels (or at least 1920 width)
5. Click **Set featured image** to save

### Step 3: Publish the Post

1. Click **Publish** (or **Update** if editing)
2. The post will automatically appear in the slider on your homepage
3. The slider shows the 5 most recent posts with featured images, newest first

## What Links Where

- **Slide background + "Read More" button** → Goes to the full blog post
- **Slider navigation arrows** → Advance through slides
- **Pagination dots (bottom)** → Jump to specific slide

## Customizing Slider Behavior

The slider has these settings (in `/assets/js/main.js` if you want to change them):

- **Auto-play delay:** 5 seconds per slide
- **Transition:** Smooth fade effect (600ms)
- **Loop:** Continuous (goes back to first slide after last)
- **Pause on interaction:** No (keeps playing even when user clicks)

## If You Don't Have 5 Posts with Images

That's fine! The slider will show however many you have:
- **0 posts with images** → Shows default slide with your site name and tagline
- **1-4 posts with images** → Shows those posts and loops through them
- **5+ posts with images** → Shows the 5 most recent

## Advanced: Customizing What Appears in the Slider

If you want to change which posts appear in the slider, edit `/front-page.php` around line 30:

```php
$slider_query = new WP_Query(
    array(
        'posts_per_page' => 5,  // Change number of slides here
        'post_status'    => 'publish',
        'meta_query'     => array(
            array(
                'key'     => '_thumbnail_id',
                'compare' => 'EXISTS',
            ),
        ),
        'orderby'        => 'date',
        'order'          => 'DESC',
    )
);
```

You could also:
- Change `'orderby' => 'date'` to `'orderby' => 'rand'` for random slides
- Add a category filter to only show posts from specific categories
- Add a custom field to mark posts as "featured" for the slider

## Best Practices

1. **Image size:** Use 1920 x 800px images for best results
2. **Image content:** Keep important elements centered - edges may be cropped on mobile
3. **Text length:** Keep post titles under 60 characters for slider
4. **Excerpts:** Keep excerpts under 150 characters - long text gets cut off
5. **Number of slides:** 3-5 slides is ideal - too many and visitors won't see them all

## Troubleshooting

**Problem:** Slider shows default slide instead of my posts
- **Solution:** Make sure posts have featured images set

**Problem:** Old posts are showing in slider
- **Solution:** The slider shows most recent posts - publish new posts to replace them

**Problem:** Slide images are blurry
- **Solution:** Use larger images (at least 1920px wide)

**Problem:** Can't see text on slides
- **Solution:** The theme adds a dark overlay, but very light images might need darker photos

**Problem:** Slider doesn't auto-play
- **Solution:** Check browser console for JavaScript errors - Swiper.js might not be loading
