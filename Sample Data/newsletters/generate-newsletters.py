#!/usr/bin/env python3
"""
Generate Heritage Valley Quarterly newsletter covers (JPG) and PDFs.

Each issue gets a unique stock photo background with text overlay, plus a
3-page PDF with the cover as page 1 and fake article content.
"""

import os
from PIL import Image, ImageDraw, ImageFont, ImageFilter, ImageEnhance
from reportlab.lib.pagesizes import letter
from reportlab.lib.units import inch
from reportlab.lib.colors import HexColor
from reportlab.platypus import SimpleDocTemplate, Paragraph, Spacer, HRFlowable, Image as RLImage
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_CENTER, TA_JUSTIFY

OUT_DIR = os.path.dirname(os.path.abspath(__file__))
PHOTO_DIR = os.path.join(OUT_DIR, "photos")
FONT_DIR = "/System/Library/Fonts/Supplemental/"

# ---------------------------------------------------------------------------
# Issues — each gets a unique background photo
# ---------------------------------------------------------------------------

ISSUES = [
    ("Winter", "2026", 31, 1, "old-photos.jpg"),
    ("Fall",   "2025", 30, 4, "cemetery.jpg"),
    ("Summer", "2025", 30, 3, "family-studio.jpg"),
    ("Spring", "2025", 30, 2, "old-letters.jpg"),
    ("Winter", "2025", 30, 1, "library.jpg"),
    ("Fall",   "2024", 29, 4, "vintage-bw.jpg"),
    ("Summer", "2024", 29, 3, "family-memories.jpg"),
    ("Spring", "2024", 29, 2, "compass.jpg"),
    ("Winter", "2024", 29, 1, "old-books.jpg"),
    ("Fall",   "2023", 28, 4, "cemetery-grass.jpg"),
    ("Summer", "2023", 28, 3, "globe-books.jpg"),
    ("Spring", "2023", 28, 2, "library-stacks.jpg"),
]

# Fake articles
ARTICLES = [
    {
        "title": "President's Message",
        "author": "Margaret Wilson, President",
        "body": "Dear Members,\n\nAs we look back on another productive quarter, I am grateful for the dedication of our volunteers and the enthusiasm of our growing membership. This quarter saw record attendance at our monthly meetings and several exciting new acquisitions for our library.\n\nOur cemetery indexing project continues to make progress, with the Cemetery Committee completing surveys of three additional burial grounds in the northern part of the county. These records will be added to our searchable online database later this year.\n\nI want to especially thank the Programs Committee for organizing an outstanding lecture series. Our guest speakers have drawn visitors from across the state, and several have become new members as a result.\n\nWarm regards,\nMargaret Wilson",
    },
    {
        "title": "From the Library",
        "author": "Helen Campbell, Librarian",
        "body": "The library has received several notable donations this quarter. The estate of longtime member James Foster contributed a collection of Heritage County atlases dating from 1875 to 1920, along with a complete run of the Heritage Valley Gazette from 1870 to 1895.\n\nWe have also completed cataloging the vertical files donated last year by the Patterson family. These files contain valuable research notes on over forty Heritage Valley families, including original correspondence and unpublished photographs.\n\nOur Open Library hours continue on the first and third Saturdays of each month, 10 AM to 2 PM. No appointment is needed.",
    },
    {
        "title": "Cemetery Committee Report",
        "author": "Robert Harrison, Chair",
        "body": "This quarter the Cemetery Committee surveyed and indexed three previously undocumented family burial plots in rural Heritage County. We recorded a total of 147 new headstone inscriptions, bringing our county-wide total to over 4,200 indexed burials.\n\nThe annual Cemetery Walk at Pioneer Rest Cemetery drew over 60 attendees. Docents portrayed six notable Heritage Valley residents including founding merchant Josiah Wheeler and Civil War nurse Martha Collins.\n\nWe continue our partnership with FindAGrave volunteers to photograph every readable headstone in the county.",
    },
    {
        "title": "Upcoming Programs",
        "author": "Programs Committee",
        "body": "Mark your calendars for these upcoming society events:\n\nMonthly Meeting \u2014 Our next meeting features a presentation on using DNA testing to break through genealogical brick walls. Dr. Rebecca Foster from Ohio State University will demonstrate practical techniques.\n\nCourthouse Records Research Day \u2014 Join us for a hands-on morning at the Heritage County Courthouse. Archivist Janet Mills will guide participants through deed books, probate records, and marriage indexes.\n\nHeritage Valley Founders Day \u2014 Our annual celebration includes period demonstrations, historical displays, and the dedication of a new historical marker.",
    },
    {
        "title": "New Members Welcome",
        "author": "Membership Committee",
        "body": "Please join us in welcoming the following new members who joined this quarter:\n\nDavid and Susan Clark, Springfield\nPatricia Edwards, Heritage Valley\nKenneth Walker, Oakfield\nThe Thornton Family, Millbrook\nJanet Morgan, Cedar Falls\nGeorge Spencer, Heritage Valley\n\nWe now have 250 members, our highest count in five years. If you know someone interested in local history or genealogy, invite them to a meeting.",
    },
    {
        "title": "Research Corner: Ohio Land Records",
        "author": "Virginia Patterson",
        "body": "Ohio's complex land history can be confusing for researchers. The state was divided into several distinct land districts, each with its own survey system and records.\n\nHeritage County falls within the Congress Lands area, where the federal government sold land directly to settlers. Records are held at the National Archives and the Bureau of Land Management's General Land Office website.\n\nAfter the initial federal patent, subsequent transfers were recorded at the county level. Heritage County deed books begin in 1837 and are available at the courthouse. Our society has indexed all deeds through 1880.",
    },
]

# ---------------------------------------------------------------------------
# Font helper
# ---------------------------------------------------------------------------

def load_font(name, size):
    path = os.path.join(FONT_DIR, name)
    if os.path.exists(path):
        try:
            return ImageFont.truetype(path, size)
        except Exception:
            pass
    return ImageFont.load_default(size=size)

# ---------------------------------------------------------------------------
# Generate cover with photo background
# ---------------------------------------------------------------------------

def generate_cover(season, year, volume, issue, photo_file, filename):
    W, H = 600, 800

    # Load and prepare background photo
    photo_path = os.path.join(PHOTO_DIR, photo_file)
    bg = Image.open(photo_path).convert("RGB")

    # Resize to cover, cropping from center
    bg_ratio = bg.width / bg.height
    target_ratio = W / H
    if bg_ratio > target_ratio:
        new_h = bg.height
        new_w = int(new_h * target_ratio)
        left = (bg.width - new_w) // 2
        bg = bg.crop((left, 0, left + new_w, new_h))
    else:
        new_w = bg.width
        new_h = int(new_w / target_ratio)
        top = (bg.height - new_h) // 2
        bg = bg.crop((0, top, new_w, top + new_h))
    bg = bg.resize((W, H), Image.LANCZOS)

    # Darken the photo
    enhancer = ImageEnhance.Brightness(bg)
    bg = enhancer.enhance(0.4)

    img = bg.copy()
    draw = ImageDraw.Draw(img)

    # Fonts
    font_masthead = load_font("Georgia.ttf", 20)
    font_season = load_font("Georgia.ttf", 44)
    font_year = load_font("Georgia.ttf", 30)
    font_label = load_font("Arial.ttf", 13)
    font_vol = load_font("Arial.ttf", 12)

    accent = "#c4933f"

    # Top banner — semi-transparent
    overlay = Image.new("RGBA", (W, 80), (26, 54, 37, 200))
    img.paste(Image.alpha_composite(Image.new("RGBA", (W, 80), (0, 0, 0, 0)), overlay), (0, 0))
    draw = ImageDraw.Draw(img)  # refresh after paste
    draw.text((W // 2, 20), "Heritage Valley", anchor="mt", fill="white", font=font_masthead)
    draw.text((W // 2, 50), "QUARTERLY", anchor="mt", fill=accent, font=font_label)

    # Gold accent line
    draw.rectangle([(30, 88), (W - 30, 90)], fill=accent)

    # Season and year — centered in the image
    draw.text((W // 2, 200), season, anchor="mt", fill="white", font=font_season)
    draw.text((W // 2, 260), year, anchor="mt", fill=accent, font=font_year)

    # Diamond accent
    cx, cy = W // 2, 310
    draw.polygon([(cx, cy - 6), (cx + 6, cy), (cx, cy + 6), (cx - 6, cy)], fill=accent)

    # Bottom banner — semi-transparent
    overlay_bottom = Image.new("RGBA", (W, 70), (26, 54, 37, 200))
    img.paste(Image.alpha_composite(Image.new("RGBA", (W, 70), (0, 0, 0, 0)), overlay_bottom), (0, H - 70))
    draw = ImageDraw.Draw(img)
    vol_text = f"Volume {volume}  \u2022  Number {issue}"
    draw.text((W // 2, H - 50), vol_text, anchor="mt", fill="#cccccc", font=font_vol)
    draw.text((W // 2, H - 28), "Heritage Valley Historical Society", anchor="mt", fill=accent, font=font_vol)

    # Thin gold border
    draw.rectangle([(8, 8), (W - 8, H - 8)], outline=accent, width=1)

    path = os.path.join(OUT_DIR, filename)
    img.save(path, "JPEG", quality=92)
    return path

# ---------------------------------------------------------------------------
# Generate PDF
# ---------------------------------------------------------------------------

def generate_pdf(season, year, volume, issue, cover_path, pdf_filename):
    pdf_path = os.path.join(OUT_DIR, pdf_filename)
    color = "#2d5f3f"

    doc = SimpleDocTemplate(
        pdf_path, pagesize=letter,
        topMargin=0.75 * inch, bottomMargin=0.75 * inch,
        leftMargin=0.85 * inch, rightMargin=0.85 * inch,
    )

    styles = getSampleStyleSheet()
    style_article_title = ParagraphStyle(
        "ArticleTitle", parent=styles["Heading2"],
        fontSize=16, textColor=HexColor(color), spaceBefore=16, spaceAfter=4,
    )
    style_byline = ParagraphStyle(
        "Byline", parent=styles["Normal"],
        fontSize=10, textColor=HexColor("#888888"), spaceAfter=8, fontName="Helvetica-Oblique",
    )
    style_body = ParagraphStyle(
        "ArticleBody", parent=styles["Normal"],
        fontSize=11, leading=15, alignment=TA_JUSTIFY, spaceAfter=12,
    )
    style_footer = ParagraphStyle(
        "NLFooter", parent=styles["Normal"],
        fontSize=9, textColor=HexColor("#aaaaaa"), alignment=TA_CENTER,
    )

    elements = []

    # Page 1: Cover
    if os.path.exists(cover_path):
        img_w = 5.5 * inch
        img_h = img_w * (800 / 600)
        elements.append(Spacer(1, 0.25 * inch))
        elements.append(RLImage(cover_path, width=img_w, height=img_h))

    # Pages 2-3: Articles (rotate selection per issue)
    start = (volume * 4 + issue) % len(ARTICLES)
    for i in range(4):
        art = ARTICLES[(start + i) % len(ARTICLES)]
        elements.append(Spacer(1, 6))
        elements.append(HRFlowable(width="100%", thickness=1, color=HexColor(color), spaceBefore=6, spaceAfter=6))
        elements.append(Paragraph(art["title"], style_article_title))
        elements.append(Paragraph(art["author"], style_byline))
        for para in art["body"].split("\n\n"):
            elements.append(Paragraph(para.replace("\n", "<br/>"), style_body))

    # Footer
    elements.append(Spacer(1, 20))
    elements.append(HRFlowable(width="60%", thickness=0.5, color=HexColor("#cccccc"), spaceBefore=10, spaceAfter=10))
    elements.append(Paragraph(
        f"Heritage Valley Quarterly &bull; {season} {year} &bull; Volume {volume}, Number {issue}",
        style_footer
    ))
    elements.append(Paragraph(
        "Heritage Valley Historical Society &bull; 450 Main Street &bull; Heritage Valley, OH 43001",
        style_footer
    ))

    doc.build(elements)
    return pdf_path

# ---------------------------------------------------------------------------
# Main
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    print("Generating Heritage Valley Quarterly newsletters...\n")

    for season, year, volume, issue, photo in ISSUES:
        base = f"hvq-{year}-{season.lower()}"
        cover_file = f"{base}-cover.jpg"
        pdf_file = f"{base}.pdf"

        print(f"  {season} {year} (Vol. {volume}, No. {issue}) — {photo}")
        cover_path = generate_cover(season, year, volume, issue, photo, cover_file)
        generate_pdf(season, year, volume, issue, cover_path, pdf_file)

    print(f"\nDone. {len(ISSUES)} covers + PDFs in: {OUT_DIR}")
