#!/bin/bash
# SocietyPress — GitHub traffic and release download snapshotter
#
# WHY THIS EXISTS: GitHub keeps repository traffic data for only 14 days and
# then discards it permanently. Release asset download counts are kept, but
# only as a single running total with no history — so a counter reading "15"
# tells you nothing about whether that was fifteen hits in one afternoon from
# a crawler or one real person a week for fifteen weeks.
#
# That distinction is the whole question when you are trying to work out
# whether anyone has actually found the project. This script appends a dated
# snapshot on every run, so the numbers accumulate into a history that can
# answer it. It is append-only and never rewrites past entries.
#
# Run it on a schedule (see scripts/install-traffic-cron.sh). Missing a few
# days is survivable; missing more than 14 loses the view/clone detail for
# the gap forever, because GitHub will already have dropped it.
#
# Output: one JSON object per line (JSONL) in analytics/traffic-history.jsonl.
# JSONL is used rather than a single JSON array so an interrupted run can
# never corrupt the file — a partial line is the only damage possible, and
# the reader below skips unparseable lines.

set -o pipefail

REPO="SocietyPressFoundation/SocietyPress"
DATA_DIR="${SP_ANALYTICS_DIR:-$HOME/Library/Application Support/SocietyPress/analytics}"
OUT="$DATA_DIR/traffic-history.jsonl"

mkdir -p "$DATA_DIR"

# Fail early with a clear message rather than writing a snapshot full of nulls.
if ! command -v gh >/dev/null 2>&1; then
    echo "ERROR: gh CLI not found. Install it or put it on PATH." >&2
    exit 1
fi

if ! gh auth status >/dev/null 2>&1; then
    echo "ERROR: gh is not authenticated. Run: gh auth login" >&2
    exit 1
fi

# Timestamp is generated once and shared by every field in this snapshot so a
# single run is always internally consistent, even if the API calls straddle
# midnight.
STAMP="$(date -u +%Y-%m-%dT%H:%M:%SZ)"

# --- Traffic (14-day rolling window — this is the perishable data) ----------
VIEWS="$(gh api "repos/$REPO/traffic/views" 2>/dev/null)"
CLONES="$(gh api "repos/$REPO/traffic/clones" 2>/dev/null)"
REFERRERS="$(gh api "repos/$REPO/traffic/popular/referrers" 2>/dev/null)"
PATHS="$(gh api "repos/$REPO/traffic/popular/paths" 2>/dev/null)"

# --- Release asset download counts (cumulative totals) ---------------------
RELEASES="$(gh api "repos/$REPO/releases" --paginate 2>/dev/null)"

# --- Repo-level signals ----------------------------------------------------
REPO_INFO="$(gh api "repos/$REPO" 2>/dev/null)"

# Assemble one snapshot object. Every input is passed through --argjson with an
# 'empty means null' guard so a single failed API call degrades that one field
# instead of aborting the whole snapshot.
# -c is essential, not cosmetic: without it jq pretty-prints and a single
# snapshot lands as ~200 lines, which breaks the one-object-per-line contract
# that makes this file append-safe and readable with `tail`.
jq -n -c \
    --arg stamp "$STAMP" \
    --argjson views "${VIEWS:-null}" \
    --argjson clones "${CLONES:-null}" \
    --argjson referrers "${REFERRERS:-null}" \
    --argjson paths "${PATHS:-null}" \
    --argjson releases "${RELEASES:-null}" \
    --argjson repo "${REPO_INFO:-null}" \
    '{
        snapshot_at: $stamp,
        views_14d:  ($views  | if . then {count: .count, uniques: .uniques, days: .views} else null end),
        clones_14d: ($clones | if . then {count: .count, uniques: .uniques, days: .clones} else null end),
        referrers:  $referrers,
        popular_paths: $paths,
        stars:    ($repo | if . then .stargazers_count else null end),
        forks:    ($repo | if . then .forks_count else null end),
        watchers: ($repo | if . then .subscribers_count else null end),
        releases: ($releases | if . then [.[] | {
            tag: .tag_name,
            published_at: .published_at,
            assets: [.assets[] | {name: .name, downloads: .download_count}]
        }] else null end)
    }' >> "$OUT"

if [ $? -ne 0 ]; then
    echo "ERROR: snapshot failed to write." >&2
    exit 1
fi

# Report the headline numbers so a scheduled run leaves a useful log line and a
# manual run tells you something without needing a second command.
TOTAL_DL="$(jq -s '.[-1].releases // [] | [.[].assets[].downloads] | add // 0' "$OUT")"
SNAPSHOTS="$(wc -l < "$OUT" | tr -d ' ')"
echo "Snapshot $SNAPSHOTS recorded at $STAMP"
echo "  cumulative release downloads: $TOTAL_DL"
echo "  file: $OUT"
