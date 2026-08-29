#!/bin/bash
#
# build-softaculous.sh — Assemble a clean SocietyPress ZIP for Softaculous
#
# WHY: The Softaculous package needs a pristine WordPress + SocietyPress bundle
# with no site-specific data, no private child themes, no credentials, no personal info.
# This script downloads fresh WordPress, copies the plugin and parent theme,
# and creates the final societypress.zip ready for submission.
#
# Usage: ./build-softaculous.sh
# Output: softaculous/societypress.zip

set -e

PROJECT_ROOT="$(cd "$(dirname "$0")/.." && pwd)"
BUILD_DIR="$PROJECT_ROOT/Code/softaculous/build"
OUTPUT_ZIP="$PROJECT_ROOT/Code/softaculous/societypress.zip"
INFO_XML="$PROJECT_ROOT/Code/softaculous/info.xml"
WP_URL="https://wordpress.org/latest.zip"

echo "=== SocietyPress Softaculous Package Builder ==="
echo ""

# ---- Sync info.xml <ver> with the plugin's Version: header ----
# WHY: info.xml's <ver> drove silent drift in past releases — the bundle
# would ship a newer plugin while Softaculous's catalog still advertised
# an older version. Pulling the truth from the plugin header on every
# build means the two can't disagree.
PLUGIN_VER=$(grep -E '^[[:space:]]*\*[[:space:]]*Version:' \
             "$PROJECT_ROOT/Code/plugin/societypress.php" \
             | head -n1 \
             | sed -E 's/^[[:space:]]*\*[[:space:]]*Version:[[:space:]]*//')
if [ -z "$PLUGIN_VER" ]; then
    echo "ERROR: Could not parse Version: header from plugin file."
    exit 1
fi
echo "Plugin version: $PLUGIN_VER"
# In-place rewrite of the single <ver>...</ver> line. macOS and Linux sed
# disagree on -i, so a tempfile + mv keeps the script portable.
# The tag is <version>, not <ver> — an earlier revision of info.xml used a tag
# name Softaculous does not recognise, so this rewrite silently matched nothing.
# info.xml follows the reference package's style of putting a tag's value on
# its own line, so the match has to span newlines — sed works a line at a time
# and would silently rewrite nothing.
# The ^\t* anchor is load-bearing. Without it the match can start at a tag
# name mentioned inside a comment and run to the real closing tag, deleting
# everything between — which is precisely what happened once. Elements in
# info.xml therefore must each begin on their own line.
PLUGIN_VER="$PLUGIN_VER" perl -0777 -i -pe \
    's|^(\t*)<version>.*?</version>|"$1<version>\n$1\t$ENV{PLUGIN_VER}\n$1</version>"|sme' \
    "$INFO_XML"
if ! tr -d ' \t\n' < "$INFO_XML" | grep -q "<version>${PLUGIN_VER}</version>"; then
    echo "ERROR: Could not write <version> into $(basename "$INFO_XML")."
    echo "       The tag is missing or malformed — refusing to ship a package"
    echo "       whose advertised version does not match the plugin."
    exit 1
fi
echo "Updated $(basename "$INFO_XML") <version> to $PLUGIN_VER"
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
cp "$PROJECT_ROOT/Code/plugin/societypress.php" "$BUILD_DIR/wp-content/plugins/societypress/"

# The plugin is single-file but not self-contained: assets/ carries the PWA
# icons and favicons plus the CSS and JS the gallery viewer, events pages,
# editor table, searchable select and leadership search all enqueue by URL.
# WHY this is called out: it was missing for months, so every Softaculous
# install shipped a plugin whose front end was quietly half-dead. deploy.sh's
# bundle target has always copied it; this script had drifted out of step.
if [ -d "$PROJECT_ROOT/Code/plugin/assets" ]; then
    cp -r "$PROJECT_ROOT/Code/plugin/assets" "$BUILD_DIR/wp-content/plugins/societypress/"
else
    echo "  ERROR: Code/plugin/assets not found — the bundle would ship a plugin"
    echo "         with no icons, CSS or JS. Refusing to build."
    exit 1
fi

# Copy languages directory if it exists
if [ -d "$PROJECT_ROOT/Code/plugin/languages" ]; then
    cp -r "$PROJECT_ROOT/Code/plugin/languages" "$BUILD_DIR/wp-content/plugins/societypress/"
fi

# ---- Copy SocietyPress parent theme ----
echo "Copying SocietyPress parent theme..."
if [ -d "$PROJECT_ROOT/Code/theme" ]; then
    cp -r "$PROJECT_ROOT/Code/theme" "$BUILD_DIR/wp-content/themes/societypress"
    # The getsocietypress.org marketing site lives nested inside Code/theme/.
    # It is NOT part of the shippable product — getsocietypress.org stays the
    # place people learn/demo/download, never something bundled into a society's
    # install. Strip it so it can't ride along in the package.
    rm -rf "$BUILD_DIR/wp-content/themes/societypress/getsocietypress"
else
    echo "WARNING: theme/ directory not found. Skipping theme copy."
fi

# ---- Copy child themes ----
echo "Copying child themes..."
for CHILD_DIR in "$PROJECT_ROOT"/Code/theme-*/; do
    CHILD_NAME=$(basename "$CHILD_DIR")
    # Strip the "theme-" prefix for the WP theme directory name
    THEME_SLUG="${CHILD_NAME#theme-}"
    echo "  Including $THEME_SLUG"
    cp -r "$CHILD_DIR" "$BUILD_DIR/wp-content/themes/$THEME_SLUG"
done

# ---- Verify no personal or site-specific data leaked in ----
#
# WHY THIS BLOCK IS SHAPED THE WAY IT IS: all three of its parts were broken at
# once, and the failure was silent in every direction, which is how a private-
# domain reference rode a code comment in the shipped plugin from 1.1.11 to 1.5.2.
#
#   1. scripts/build.local.sh never existed, so LEAK_PATTERNS stayed empty and
#      the `if [ -n ... ]` guard skipped the scan entirely. The build still
#      printed "Scanning for data leaks..." while checking nothing.
#   2. Only the plugin file was scanned. The parent theme and five child themes
#      ship in the same bundle and were never looked at.
#   3. LEAKS was set to 1 on a hit and then never read, so even a detected leak
#      printed a WARNING and let the build succeed.
#
# A scanner that cannot fail the build is decoration. This one exits non-zero.
echo "Scanning for data leaks..."
LEAKS=0

# Patterns of strings that should never appear in a shippable bundle. Define
# them in scripts/build.local.sh (gitignored) with names, emails, or domains
# that are specific to your development environment. See
# scripts/build.local.example.sh for the format.
LEAK_PATTERNS=""
if [ -f "$PROJECT_ROOT/scripts/build.local.sh" ]; then
    # shellcheck source=/dev/null
    source "$PROJECT_ROOT/scripts/build.local.sh"
fi

if [ -z "$LEAK_PATTERNS" ]; then
    # An unconfigured scanner is the state that let the last leak ship. Say so
    # loudly rather than reporting a clean scan that never happened.
    echo "  ERROR: No LEAK_PATTERNS configured."
    echo "         Copy scripts/build.local.example.sh to scripts/build.local.sh"
    echo "         and list the strings that must never ship. Refusing to claim"
    echo "         a clean scan without running one."
    exit 1
fi

# Scan everything that ships, not just the plugin — the themes go in the same
# bundle. -I skips binaries so images and fonts don't produce noise.
if grep -rIl "$LEAK_PATTERNS" "$BUILD_DIR/wp-content/" 2>/dev/null | grep -q .; then
    echo "  ERROR: Leak patterns found in the assembled bundle:"
    grep -rIn "$LEAK_PATTERNS" "$BUILD_DIR/wp-content/" 2>/dev/null | sed 's|^|    |'
    LEAKS=1
fi

if [ "$LEAKS" -ne 0 ]; then
    echo ""
    echo "  Refusing to build a bundle carrying private references."
    exit 1
fi
echo "  Clean — no leak patterns in the assembled bundle."

# Check no private child themes snuck in

# Check no wp-config with credentials exists
if [ -f "$BUILD_DIR/wp-config.php" ]; then
    echo "  ERROR: wp-config.php found in build. Removing..."
    rm -f "$BUILD_DIR/wp-config.php"
fi

# Check no .git directory
if [ -d "$BUILD_DIR/.git" ]; then
    rm -rf "$BUILD_DIR/.git"
fi

# ---- Remove the WordPress download staging directory ----
# WHY: The script downloads wordpress.zip into $BUILD_DIR/tmp/ and extracts it,
# but neither file gets cleaned up before the zip step below. Without this,
# the final package ends up shipping ~27 MB of duplicate WordPress core inside
# itself.
rm -rf "$BUILD_DIR/tmp"

# ---- Measure the installed footprint ----
# WHY: info.xml's <space> tells Softaculous how much room an install needs.
# Understate it and Softaculous green-lights installs onto accounts that cannot
# hold the result; the failure lands mid-install, on the volunteer.
EXTRACTED_BYTES=$(du -sk "$BUILD_DIR" | cut -f1)
EXTRACTED_BYTES=$(( EXTRACTED_BYTES * 1024 ))
# Same multiline caveat as <version> above, and the surrounding comment also
# mentions the tag by name — so match the element across newlines rather than
# grepping for the word.
DECLARED_SPACE=$(perl -0777 -ne 'print $1 if m|^\t*<space>\s*(\d+)\s*</space>|sm' "$INFO_XML")
echo "Extracted footprint: $EXTRACTED_BYTES bytes (info.xml declares ${DECLARED_SPACE:-unset})"
if [ -n "$DECLARED_SPACE" ] && [ "$DECLARED_SPACE" -lt "$EXTRACTED_BYTES" ]; then
    echo "  WARNING: <space> in info.xml is smaller than the actual build."
    echo "           Raise it to at least $EXTRACTED_BYTES before submitting."
fi

# ---- Create the ZIP ----
echo "Creating societypress.zip..."
cd "$BUILD_DIR"
zip -rq "$OUTPUT_ZIP" . -x "*.DS_Store" "*__MACOSX*" "*.git*"
cd "$PROJECT_ROOT"

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
