#!/bin/bash
# SocietyPress — read the traffic history collected by snapshot-traffic.sh
#
# WHY A SEPARATE READER: the raw JSONL is a pile of cumulative totals, and a
# cumulative total cannot answer the only question that matters — is anyone
# actually finding this? "15 downloads" reads as alarming or trivial depending
# entirely on whether it happened in one hour or over three months. This script
# prints the deltas between consecutive snapshots, which is what turns the
# counter into a rate.
#
# Usage:
#   ./scripts/traffic-report.sh            Summary + recent activity
#   ./scripts/traffic-report.sh --all      Every snapshot, not just recent

set -o pipefail

DATA_DIR="${SP_ANALYTICS_DIR:-$HOME/Library/Application Support/SocietyPress/analytics}"
HIST="$DATA_DIR/traffic-history.jsonl"

if [ ! -s "$HIST" ]; then
    echo "No traffic history yet at $HIST"
    echo "Run ./scripts/snapshot-traffic.sh first."
    exit 1
fi

LIMIT=14
[ "$1" = "--all" ] && LIMIT=100000

SNAPS="$(wc -l < "$HIST" | tr -d ' ')"
FIRST="$(head -1 "$HIST" | jq -r '.snapshot_at')"
LAST="$(tail -1 "$HIST" | jq -r '.snapshot_at')"

echo "SocietyPress traffic history"
echo "  snapshots: $SNAPS   first: $FIRST   latest: $LAST"
echo

# Current standing totals, per release asset.
echo "Release downloads (cumulative):"
tail -1 "$HIST" | jq -r '.releases[]? | "  \(.tag)  \(.assets[]? | "\(.name)  \(.downloads)")"'
TOTAL="$(tail -1 "$HIST" | jq '[.releases[]?.assets[]?.downloads] | add // 0')"
echo "  ------"
echo "  total: $TOTAL"
echo

# The interesting part: what changed between snapshots. A run of zeros means
# nothing is happening, which is itself the answer most of the time.
echo "Change between snapshots (most recent last):"
tail -n "$LIMIT" "$HIST" | jq -s -r '
    [ .[] | {
        at: .snapshot_at,
        dl: ([.releases[]?.assets[]?.downloads] | add // 0),
        v:  (.views_14d.count // 0),
        vu: (.views_14d.uniques // 0),
        cu: (.clones_14d.uniques // 0),
        st: (.stars // 0)
      } ] as $rows
    | $rows
    | to_entries
    | .[]
    | . as $e
    # Guard the index explicitly: jq treats $rows[-1] as the LAST element, so on
    # the first row this would silently compare the entry against itself and
    # report a delta of +0 instead of marking it as the baseline.
    | (if $e.key > 0 then $rows[$e.key - 1] else null end) as $prev
    | "  \($e.value.at)   downloads \($e.value.dl)" +
      (if $prev then " (\(if ($e.value.dl - $prev.dl) >= 0 then "+" else "" end)\($e.value.dl - $prev.dl))" else " (first)" end) +
      "   views14d \($e.value.v)/\($e.value.vu)u   cloners14d \($e.value.cu)   stars \($e.value.st)"
'
echo

# Referrers tell you HOW anyone arrived, which is the difference between
# organic discovery and a bot sweeping the release API.
echo "Referrers (latest snapshot):"
tail -1 "$HIST" | jq -r '
    if (.referrers | length) > 0
    then .referrers[] | "  \(.referrer): \(.count) (\(.uniques) unique)"
    else "  none recorded" end'
echo

echo "Reading this:"
echo "  Views are humans. Clones and release-asset downloads are heavily automated —"
echo "  crawlers, mirrors and package bots all increment them. A download delta with"
echo "  no matching view delta is almost certainly not a person."
