# SocietyPress WordPress Theme

Premium standalone WordPress theme for genealogical and historical societies.

## Version

**Current Version:** 1.22d

## Description

SocietyPress is a professional, accessible WordPress theme designed specifically for genealogical and historical societies. It integrates seamlessly with the SocietyPress membership management plugin to provide:

- Member directories
- Event calendars and management
- Research resource libraries
- Document archives
- News and announcements
- Responsive, mobile-friendly design
- WCAG 2.1 Level AA accessibility compliance

## Requirements

- **WordPress:** 6.0 or higher
- **PHP:** 8.0 or higher
- **SocietyPress Plugin:** Required for full functionality (member directories, events, etc.)

## Installation

1. Upload the `societypress` folder to `/wp-content/themes/`
2. Activate the theme in WordPress Admin → Appearance → Themes
3. Install and activate the SocietyPress plugin for full functionality
4. Configure theme settings in Appearance → Customize

## Features

### Core Features
- **Standalone Theme** - Not a child theme, complete theme structure
- **Custom CSS Grid/Flexbox Layout** - No Bootstrap dependency
- **Hero Carousel** - Swiper.js powered slider for homepage
- **Mobile Menu** - Accessible, touch-friendly navigation
- **Widget Areas** - Sidebar + 3 footer columns
- **Three Navigation Menus** - Primary, Footer, Utility (top bar)
- **Custom Image Sizes** - Optimized for different use cases

### Accessibility Features
- Skip to content link
- Screen reader text support
- ARIA attributes throughout
- Keyboard navigation support
- Semantic HTML5 markup
- Focus management

### Events Integration
- Custom template for `sp_event` post type
- Event metadata display (date, time, location)
- Registration badges
- Event categories
- Responsive event cards

### Typography
- System font stacks (no external fonts)
- Responsive font sizing
- Optimized for readability

## File Structure

```
societypress/
├── assets/
│   └── js/
│       └── main.js           # Theme JavaScript
├── template-parts/
│   ├── content.php            # Post template
│   ├── content-none.php       # No results template
│   └── content-sp_event.php   # Event template
├── CREDITS.md                 # Third-party library credits
├── footer.php                 # Site footer
├── functions.php              # Theme setup and functions
├── header.php                 # Site header
├── index.php                  # Main template (fallback)
├── README.md                  # This file
└── style.css                  # Main stylesheet with CSS variables

```

## CSS Custom Properties

The theme uses CSS custom properties (CSS variables) for consistent design tokens:

- **Colors:** Primary palette (50-900), Neutral palette, Semantic colors
- **Typography:** Font families, sizes, line heights
- **Spacing:** 1-24 scale (4px-96px)
- **Layout:** Max widths, content width
- **Effects:** Border radius, shadows, transitions
- **Z-index:** Layering scale

See `style.css` for full variable reference.

## Navigation Menus

Configure in **Appearance → Menus**:

1. **Primary Menu** - Main navigation in header
2. **Footer Menu** - Links in footer bottom
3. **Utility Menu** - Top bar (contact info, member login, etc.)

## Widget Areas

Configure in **Appearance → Widgets**:

1. **Sidebar** - Right sidebar on posts/pages
2. **Footer 1** - Left footer column
3. **Footer 2** - Center footer column
4. **Footer 3** - Right footer column

## Theme Functions

### Plugin Integration Functions

```php
sp_get_event_date( $post_id )           // Get event date
sp_get_event_time( $post_id )           // Get event time
sp_get_event_location( $post_id )       // Get event location
sp_get_formatted_datetime( $post_id )   // Get formatted date/time
sp_is_registration_required( $post_id ) // Check if registration required
societypress_plugin_is_active()         // Check if plugin is active
```

### Template Tags

```php
societypress_posted_on()    // Display post date
societypress_posted_by()    // Display post author
societypress_entry_footer() // Display categories/tags
```

## Development

### Version Numbering

Version increments by 0.01 for each code change during development:
- 1.0.0 → 1.01d → 1.02d, etc.
- Gives 99 versions before needing new system
- 'd' suffix indicates development version

### Code Style

- Complete files only (never snippets)
- Detailed WHY comments explaining reasoning
- Code maintainability over clever shortcuts
- WordPress Coding Standards compliance
- PHP 8.0+ typed properties and return types

## Third-Party Libraries

### Swiper.js
- **Version:** 11.0.0
- **License:** MIT
- **Usage:** Hero carousel slider
- **Loading:** Conditional (only on pages that need it)
- **Source:** CDN (jsdelivr)

See `CREDITS.md` for full license text and attribution.

## Browser Support

- Chrome (latest 2 versions)
- Firefox (latest 2 versions)
- Safari (latest 2 versions)
- Edge (latest 2 versions)
- Mobile browsers (iOS Safari, Chrome Mobile)

## License

**Proprietary** - Commercial license required for production use.

For licensing information, visit: https://stricklindevelopment.com/license/

## Support

- **Documentation:** https://stricklindevelopment.com/docs/societypress-theme/
- **Support:** https://stricklindevelopment.com/support/
- **GitHub:** https://github.com/charles-stricklin/SocietyPress

## Author

**Stricklin Development**
- Website: https://getsocietypress.org/
- GitHub: https://github.com/charles-stricklin/

## Changelog

### 1.22d - 2026-01-27
- **Fixed Header with Content Spacing**
  - Added body padding-top (170px) to account for fixed header height
  - Content no longer hidden behind fixed header
  - Prevents slider and page content from being obscured
  - Mobile adjustment: 150px padding on screens 480px and below
  - WHY: When position: fixed is used, header is removed from document flow
  - Ensures all content visible and accessible below the fixed header

### 1.21d - 2026-01-27
- **Truly Fixed Header**
  - Changed header from `position: sticky` to `position: fixed`
  - Added `left: 0` and `right: 0` to span full viewport width
  - Header now absolutely locked at top of viewport
  - No movement when scrolling (completely stationary)
  - WHY: User reported header still moved with sticky positioning

### 1.20d - 2026-01-27
- **Proper Header Layout - Logo and Menu at Edges**
  - Header container: 1800px max-width, centered on page
  - Logo positioned at far LEFT edge of container
  - Navigation menu positioned at far RIGHT edge of container
  - Removed gap between logo and nav (space-between does the work)
  - Container padding: 24px on sides
  - Creates wide, spacious header matching reference design
  - Logo and menu stay at edges when scrolling (sticky header)

### 1.19d - 2026-01-27
- **Fixed Header (Stays in Place)**
  - Header remains fixed at top of viewport when scrolling
  - No shadow or border appears on scroll (removed completely)
  - Clean, seamless appearance maintained
  - Navigation always accessible

### 1.18d - 2026-01-27
- **Removed Sticky Header Behavior** (reverted in 1.19d)
  - Changed header from `position: sticky` to `position: relative`
  - Header now scrolls naturally with page content
  - Removed box-shadow effect that appeared on scroll
  - Cleaner, simpler scrolling experience
  - Header stays at top of page, scrolls away when you scroll down

### 1.17d - 2026-01-27
- **Individual Text Color Per Slide**
  - Each slide (1-6) now has its own text color control
  - Perfect for matching text visibility to different backgrounds
  - White text on dark images/videos, dark text on light backgrounds
  - Customizer → Hero Slider → "Slide X - Text Color" for each slide
  - All text elements (h1-h6, paragraphs, bold, italic) inherit the slide color
  - Global "Slider Text Color" still available as fallback default
  - Maximum flexibility for creating readable, beautiful slides

### 1.16d - 2026-01-27
- **Centered Header Layout with 1800px Max Width**
  - Header content now centers within 1800px max-width container (was 1200px)
  - Logo aligns to left edge of 1800px container
  - Navigation menu aligns to right edge of 1800px container
  - Creates balanced, spacious layout matching reference design
- **Tighter Menu Item Spacing**
  - Reduced gap between menu items from 16px to 4px
  - Reduced horizontal padding on menu links from 12px to 8px
  - Creates more compact, professional menu appearance
  - Search and Login buttons match tighter spacing

### 1.15d - 2026-01-27
- **Removed Header Border**
  - Eliminated separator line between header and page content
  - Creates seamless visual flow from header to slider
  - Cleaner, more modern appearance

### 1.14d - 2026-01-27
- **Slider Text Color Control**
  - Added "Slider Text Color" picker in Customizer → Hero Slider
  - Change text color from default white to any color
  - Live preview: see color changes instantly
  - Applies to all text sizes (h1-h6, paragraphs)
  - Default: White (#ffffff) for maximum visibility

### 1.13d - 2026-01-27
- **Slider Text Control & Vertical Centering**
  - **Individual Line Size Control**: Use heading tags for different text sizes
    - `<h1>` = Largest (5xl, 800 weight)
    - `<h2>` = Large (4xl, 700 weight)
    - `<h3>` = Medium (3xl, 700 weight)
    - `<h4>` = Default size (2xl, 600 weight)
    - Regular text = Base size
  - **Perfect Vertical Centering**: Text now centers vertically in slider using flexbox
  - **Removed Background Gaps**: Eliminated gray background showing above/below slider
  - **True Edge-to-Edge**: Slider now seamlessly fills viewport with no margins
  - **Responsive Text Scaling**: All heading sizes scale down appropriately on tablets and mobile
  - **Example Usage**:
    ```html
    <h2>Welcome to the</h2>
    the society
    <h4>Founded 1959</h4>
    ```

### 1.12d - 2026-01-27
- **Hero Slider: Video Backgrounds + Enhanced Text Styling**
  - **MP4 Video Support**: Upload MP4 videos as slide backgrounds
    - Auto-play, muted, looping video backgrounds
    - Video takes priority if both image and video are set
    - Full object-fit coverage for responsive display
  - **Enhanced Text Styling**:
    - Larger, bolder text (2xl size, 600 weight by default)
    - HTML formatting support: `<strong>` for extra bold, `<em>` for italic
    - Improved text shadows for better readability over videos
    - Wider text container (900px) for longer headlines
    - Bold tags render at 800 weight for maximum impact
  - **Edge-to-Edge Display**:
    - Slider now spans full viewport width (edge-to-edge)
    - Customizable height via Customizer (300-1000px)
    - Live preview of height changes
  - **Responsive Typography**:
    - Desktop: 2xl bold text
    - Tablet (1280px): xl bold text
    - Mobile (768px): lg bold text
    - Maintains visual hierarchy across all breakpoints

### 1.11d - 2026-01-27
- **Customizer-Based Hero Slider - COMPLETE**
  - Created slider management directly in Customizer (no post type needed)
  - **6 Slide Slots** in Appearance → Customize → Hero Slider
  - **Per-Slide Controls:**
    - Image upload (1920 x 800px recommended)
    - Text overlay (supports HTML formatting)
    - Link URL (optional - makes entire slide clickable)
  - Only slides with images display (empty slots hidden automatically)
  - Simpler workflow: upload, configure, publish
  - No need to manage posts or custom post types
  - All slider content in one centralized location

### 1.10d - 2026-01-27
- **Page Background Color Control**
  - Added "Page Background Color" setting to Customizer Colors section
  - Live preview support for instant visual feedback
  - Default color: #f5f5f4 (light gray)
  - Customizer now has 8 color controls total

### 1.09d - 2026-01-27
- **Homepage with Hero Slider - COMPLETE**
  - Created `front-page.php` (251 lines) - Automatic homepage template
  - Hero slider using Swiper.js with fade effect and autoplay
  - Displays up to 5 most recent posts with featured images
  - Navigation arrows and pagination dots
  - Full keyboard and screen reader accessibility
  - **Homepage Sections:**
    - Hero slider with post excerpts and "Read More" buttons
    - Upcoming Events (3 most recent future events)
    - Latest News (3 most recent blog posts)
    - Optional custom content from page editor
  - **Hero Slider Features:**
    - 600px height (responsive: 500px tablet, 400px mobile)
    - Auto-play with 5-second delay
    - Smooth fade transitions
    - Dark gradient overlay for text readability
    - White call-to-action buttons
    - Fallback slide if no posts with images exist
  - **Comprehensive CSS:**
    - Hero slider styles with responsive breakpoints
    - Homepage section layouts (events, news)
    - Grid layouts for content cards
    - Section titles and CTAs
    - Mobile-optimized typography and spacing
  - **JavaScript:**
    - Swiper initialization with accessibility features
    - Keyboard navigation support
    - ARIA labels for screen readers
  - Swiper.js already configured to load on front page
  - CSS: Added 280+ lines for hero slider and homepage sections
  - JavaScript: Added 50+ lines for Swiper initialization

### 1.08d - 2026-01-27
- **Phase 5: Customizer Options - COMPLETE**
  - Created `/inc/customizer.php` (784 lines) - Comprehensive customization system
  - Created `/assets/js/customizer.js` (147 lines) - Live preview functionality
  - **Colors Section:**
    - Primary color
    - Header background and text colors
    - Footer background and text colors
    - Body text color
    - Link color
    - Button background and text colors
  - **Typography Section:**
    - Body font family (System, Georgia, Times)
    - Heading font family (System, Georgia, Times)
    - Body font size (12-24px)
    - Menu font size (12-24px)
    - Menu font weight (400-800)
  - **Header Section:**
    - Logo height (40-200px)
    - Header padding (0-50px)
    - Sticky header toggle
  - **Layout Section:**
    - Content width (960-1600px)
    - Sidebar width (250-400px)
  - **Footer Section:**
    - Footer columns (1-4)
    - Footer padding (0-100px)
  - **Buttons Section:**
    - Button background color
    - Button text color
    - Button border radius (0-50px)
  - Live preview with postMessage transport for instant visual feedback
  - Color adjustment helper function for hover states
  - Web-safe font stacks (no external font loading)
  - All settings properly sanitized and validated
  - Full integration with WordPress Customizer API

### 1.07d - 2026-01-27
- **Removed All Boxes/Borders from Search & Login**
  - Search: Pure text only, no box, no border, no background
  - Log In/Out: Pure text only, no box, no border, no background
  - Hover state: Text color changes only (no background color)
  - Completely clean like other menu items
- **Simplified Footer Copyright**
  - Removed "All rights reserved" text
  - Now shows: "© 2026 Site Name"
  - Cleaner, simpler footer

### 1.06d - 2026-01-27
- **the society Design Matching + Mobile Breakpoint Change**
  - Changed mobile breakpoint from 768px to 1280px
  - Hamburger menu now appears at 1280px and below
  - Simplified hamburger to standard 3-line design (no animation)
  - Search and Log In styled as regular menu items (no borders/icons in desktop)
  - Menu items: Home | Events | About Us ▼ | Resources ▼ | Store | Contact | Search | Log In
  - Matches the society website design
  - Removed icon-only buttons, now text-based menu items
  - CSS: 1921 lines

### 1.05d - 2026-01-26
- **Menu Right Alignment + Search + Login/Logout**
  - Entire menu aligned flush with right margin
  - Added search button with dropdown search form
  - Added Log In/Log Out link (detects logged-in state)
  - Search appears between menu items and login/logout
  - Desktop: Search icon button opens dropdown below
  - Mobile: Search appears as full-width button in mobile menu
  - Login/logout shows appropriate icon and text based on user state
  - All items maintain right alignment in navigation wrapper
  - CSS: 1993 lines
  - JavaScript: 384 lines

### 1.03d - 2026-01-26
- **Dropdown Menu Support**
  - Multi-level dropdown menus with small down arrows on parent items
  - Desktop: Hover to reveal submenus with smooth animations
  - Mobile: Click/tap to toggle submenus (accordion style)
  - Keyboard accessible with focus states
  - Support for 3+ levels of nesting
  - Both primary menu and utility menu support dropdowns
  - Arrow rotates when submenu is open
  - CSS: 1847 lines
  - JavaScript: 337 lines

### 1.02d - 2026-01-26
- **Phase 3: Components & Styling - COMPLETE**
  - Comprehensive CSS (1635 lines)
  - Fully styled components (header, footer, navigation, cards, forms, buttons)
  - Responsive design with mobile breakpoints
  - Enhanced JavaScript (260 lines)
  - Event category filtering with animations
  - Sticky header with scroll effects
  - Back-to-top button
  - Accessibility announcements
  - Complete visual design implementation

### 1.01d - 2026-01-26
- **Phase 2: Core Templates - COMPLETE**
  - single.php (posts with sidebar and comments)
  - page.php (standard pages)
  - archive.php (category/tag/author/date archives)
  - search.php (search results)
  - 404.php (error page with helpful navigation)
  - sidebar.php (widget area)
  - comments.php (comment list and form)
  - searchform.php (custom search form)
  - template-full-width.php (no sidebar)
  - template-events.php (events calendar page)
  - content-search.php (search result template)

### 1.0.0 - 2026-01-26
- **Phase 1: Foundation - COMPLETE**
  - Core file structure
  - CSS custom properties
  - Header and footer templates
  - Basic post templates
  - Events integration
  - Mobile menu functionality
  - Plugin dependency check
  - Accessibility features
  - Swiper.js integration
  - Widget areas
  - Navigation menus

---

**Next Phases:**
- Phase 4: Events Display Integration (calendar views, advanced filtering)
- Phase 6: Polish & Performance (optimization, animations, refinements)
