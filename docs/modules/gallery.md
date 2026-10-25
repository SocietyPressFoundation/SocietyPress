# Gallery

Photo albums for your society's events and collections, plus a member-submitted "Picture Wall" of ancestor portraits.

## What you can do

- Create photo albums with a title, description and cover image.
- Make an album public or members-only, and set the order albums appear in.
- Add many photos at once, and view an album's photos as thumbnails or as a list by name.
- Show albums on any page with the **Photo Gallery** page-builder widget; photos open in a lightbox.
- Picture Wall — a gallery of ancestor portraits that members submit and staff approve. (See the note below: it can't be switched on from the admin yet.)

## How to make an album

**SocietyPress → Photos & Albums → Add New Album.** Album Title, Description, Cover Image, Visibility (Public or Members Only) and Sort Order. Save.

Then click **Add Photos** and choose them from your computer or the Media Library. The album list uses the cover image as each album's thumbnail.

## How to display albums

Drop the **Photo Gallery** widget on a page with the page builder and pick the album, or leave the album unset to show all of them as cards — click a card to open the album. The grid narrows on a phone, and clicking a photo opens it larger with its caption.

## The Picture Wall

A Picture Wall is an album members add to: they upload an ancestor's portrait with the ancestor's name, their relationship and a short note, and staff approve it before it appears. The pieces exist — the `[sp_picture_wall_submit album="…"]` form, the `[sp_picture_wall album="…" columns="4"]` display, and the **Picture Wall Pending** queue with Approve and Reject — but an album has to be marked as taking submissions before the form will accept anything, and there's no setting on the album screen for that yet. It's on the [roadmap](https://github.com/SocietyPressFoundation/SocietyPress/blob/main/ROADMAP.md).

## If something looks wrong

**Photos didn't upload.** Your host limits how large an uploaded file can be. WordPress shows the limit on **Media → Add New**. If a photo is bigger, resize it (most genealogy photos are fine at 2 MB or less) or ask your host to raise the limit.

**A photo from an iPhone won't upload.** iPhones save photos as HEIC, which browsers and WordPress often can't use. Change the iPhone's camera to "Most Compatible" under Settings → Camera → Formats, or convert the photo to JPG before uploading.

**The lightbox isn't opening.** Check the browser's console for an error and report it with the page address.

## Related guides

- [Page Builder](page-builder.md) — placement on pages
