#!/usr/bin/env python3
"""
Generate a circular logo for Heritage Valley Historical Society.

Dark green circle with gold accent ring, "HV" monogram in white serif,
"EST. 1962" below. Clean, classic, works at small sizes.
"""

import os
from PIL import Image, ImageDraw, ImageFont

FONT_DIR = "/System/Library/Fonts/Supplemental/"
OUT_DIR = os.path.dirname(os.path.abspath(__file__))

def load_font(name, size):
    path = os.path.join(FONT_DIR, name)
    if os.path.exists(path):
        try:
            return ImageFont.truetype(path, size)
        except Exception:
            pass
    return ImageFont.load_default(size=size)

# Canvas — 512x512 with transparent background
SIZE = 512
img = Image.new("RGBA", (SIZE, SIZE), (0, 0, 0, 0))
draw = ImageDraw.Draw(img)

cx, cy = SIZE // 2, SIZE // 2
radius = 240

# Colors
dark_green = "#1a3625"
mid_green = "#2d5f3f"
gold = "#c4933f"
white = "#ffffff"

# Outer gold ring
draw.ellipse(
    [(cx - radius, cy - radius), (cx + radius, cy + radius)],
    fill=gold
)

# Inner dark green circle
inner_r = radius - 8
draw.ellipse(
    [(cx - inner_r, cy - inner_r), (cx + inner_r, cy + inner_r)],
    fill=dark_green
)

# Inner accent ring (subtle)
accent_r = radius - 20
draw.ellipse(
    [(cx - accent_r, cy - accent_r), (cx + accent_r, cy + accent_r)],
    outline=gold, width=1
)

# "HV" monogram — large serif
font_mono = load_font("Georgia Bold.ttf", 160)
draw.text((cx, cy - 20), "HV", anchor="mm", fill=white, font=font_mono)

# Gold decorative line under monogram
line_w = 80
draw.rectangle(
    [(cx - line_w, cy + 60), (cx + line_w, cy + 63)],
    fill=gold
)

# "EST. 1962" below
font_est = load_font("Arial.ttf", 32)
draw.text((cx, cy + 95), "EST. 1962", anchor="mm", fill=gold, font=font_est)

# Save full size
logo_path = os.path.join(OUT_DIR, "hvhs-logo.png")
img.save(logo_path, "PNG")
print(f"Logo saved: {logo_path} ({SIZE}x{SIZE})")

# Save a small version for favicon/header use
small = img.resize((192, 192), Image.LANCZOS)
small_path = os.path.join(OUT_DIR, "hvhs-logo-192.png")
small.save(small_path, "PNG")
print(f"Small logo: {small_path} (192x192)")
