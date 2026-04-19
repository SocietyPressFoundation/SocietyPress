#!/bin/bash
# scripts/deploy.local.example.sh
#
# Template for configuring a secondary deploy target alongside the public
# demo site. Copy this file to scripts/deploy.local.sh (which is gitignored)
# and fill in the values for your private site. When that file exists, every
# deploy.sh target will also push to the secondary location.
#
# Intended use: personal staging sites, private testbeds, or anywhere you
# want to mirror the canonical demo deploy without committing your private
# site details to the public repo.

# Tilde-prefixed wp-content path on the SSH host (same host alias as the
# primary deploy — see HOST in deploy.sh). Leave both values empty to skip.
SECONDARY_BASE="~/domains/yourprivatesite.example/public_html/wp-content"
SECONDARY_LABEL="yourprivatesite.example"
