#!/bin/bash
#
# build-softaculous.sh — Assemble a clean SocietyPress ZIP for Softaculous
#
# WHY: The Softaculous package needs a pristine WordPress + SocietyPress bundle
# with no SAGHS data, no child themes, no credentials, no personal info.
# This script downloads fresh WordPress, copies the plugin and parent theme,
# and creates the final societypress.zip ready for submission.
#
# Usage: ./build-softaculous.sh
# Output: softaculous/societypress.zip

set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
BUILD_DIR="$SCRIPT_DIR/softaculous/build"
OUTPUT_ZIP="$SCRIPT_DIR/softaculous/societypress.zip"
WP_URL="https://wordpress.org/latest.zip"

echo "=== SocietyPress Softaculous Package Builder ==="
echo ""

# ---- Clean up any previous build ----
if [ -d "$BUILD_DIR" ]; then
    echo "Cleaning previous build..."
    rm -rf "$BUILD_DIR"
fi
if [ -f "$OUTPUT_ZIP" ]; then
    rm -f "$OUTPUT_ZIP"
fi

mkdir -p "$BUILD_DIR/tmp"

# ---- Download fresh WordPress ----
echo "Downloading WordPress..."
curl -sL "$WP_URL" -o "$BUILD_DIR/tmp/wordpress.zip"

echo "Extracting WordPress..."
unzip -q "$BUILD_DIR/tmp/wordpress.zip" -d "$BUILD_DIR/tmp"

# WordPress extracts to tmp/wordpress/ — move contents to build root
mv "$BUILD_DIR/tmp/wordpress/"* "$BUILD_DIR/"
mv "$BUILD_DIR/tmp/wordpress/".[!.]* "$BUILD_DIR/" 2>/dev/null || true

# ---- Remove WordPress defaults we don't need ----
echo "Cleaning WordPress defaults..."
rm -f "$BUILD_DIR/wp-config-sample.php"
rm -f "$BUILD_DIR/readme.html"
rm -f "$BUILD_DIR/license.txt"
rm -rf "$BUILD_DIR/wp-content/plugins/akismet"
rm -f "$BUILD_DIR/wp-content/plugins/hello.php"

# Remove all default themes — SP has its own
rm -rf "$BUILD_DIR/wp-content/themes/twenty"*

# ---- Copy SocietyPress plugin ----
echo "Copying SocietyPress plugin..."
mkdir -p "$BUILD_DIR/wp-content/plugins/societypress"
cp "$SCRIPT_DIR/plugin/societypress.php" "$BUILD_DIR/wp-content/plugins/societypress/"

# Copy languages directory if it exists
if [ -d "$SCRIPT_DIR/plugin/languages" ]; then
    cp -r "$SCRIPT_DIR/plugin/languages" "$BUILD_DIR/wp-content/plugins/societypress/"
fi

# ---- Copy SocietyPress parent theme ----
echo "Copying SocietyPress parent theme..."
if [ -d "$SCRIPT_DIR/theme" ]; then
    cp -r "$SCRIPT_DIR/theme" "$BUILD_DIR/wp-content/themes/societypress"
else
    echo "WARNING: theme/ directory not found. Skipping theme copy."
fi

# ---- Copy child themes (all generic ones, NOT saghs) ----
echo "Copying child themes..."
for CHILD_DIR in "$SCRIPT_DIR"/theme-*/; do
    CHILD_NAME=$(basename "$CHILD_DIR")
    # Skip SAGHS — that's site-specific, not for distribution
    if [ "$CHILD_NAME" = "theme-saghs" ]; then
        echo "  Skipping $CHILD_NAME (site-specific)"
        continue
    fi
    # Strip the "theme-" prefix for the WP theme directory name
    THEME_SLUG="${CHILD_NAME#theme-}"
    echo "  Including $THEME_SLUG"
    cp -r "$CHILD_DIR" "$BUILD_DIR/wp-content/themes/$THEME_SLUG"
done

# ---- Verify no SAGHS or personal data leaked in ----
echo "Scanning for personal data leaks..."
LEAKS=0

# Check for SAGHS references in the plugin (excluding comments and the ENS import mapping)
if grep -l "kndgs\.org\|saghs.*secret\|charle24\|charles@" "$BUILD_DIR/wp-content/plugins/societypress/societypress.php" 2>/dev/null; then
    echo "  WARNING: Found potential personal references in plugin file."
    LEAKS=1
fi

# Check no child themes snuck in
if [ -d "$BUILD_DIR/wp-content/themes/saghs" ] || [ -d "$BUILD_DIR/wp-content/themes/theme-saghs" ]; then
    echo "  ERROR: SAGHS child theme found in build. Removing..."
    rm -rf "$BUILD_DIR/wp-content/themes/saghs" "$BUILD_DIR/wp-content/themes/theme-saghs"
fi

# Check no wp-config with credentials exists
if [ -f "$BUILD_DIR/wp-config.php" ]; then
    echo "  ERROR: wp-config.php found in build. Removing..."
    rm -f "$BUILD_DIR/wp-config.php"
fi

# Check no .git directory
if [ -d "$BUILD_DIR/.git" ]; then
    rm -rf "$BUILD_DIR/.git"
fi

# ---- Create the ZIP ----
echo "Creating societypress.zip..."
cd "$BUILD_DIR"
zip -rq "$OUTPUT_ZIP" . -x "*.DS_Store" "*__MACOSX*" "*.git*"
cd "$SCRIPT_DIR"

# ---- Clean up build directory ----
echo "Cleaning up..."
rm -rf "$BUILD_DIR"

# ---- Report ----
SIZE=$(du -h "$OUTPUT_ZIP" | cut -f1)
echo ""
echo "=== Build complete ==="
echo "  Output: $OUTPUT_ZIP"
echo "  Size:   $SIZE"
echo ""
echo "Next steps:"
echo "  1. Add logo + screenshots to softaculous/images/"
echo "  2. Email softaculous/ directory contents to sales@softaculous.com"
echo "  3. Or test locally: copy files to /var/softaculous/societypress/"
