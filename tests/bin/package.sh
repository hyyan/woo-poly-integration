#!/usr/bin/env bash
set -euo pipefail
repository=$(cd "$(dirname "$0")/../.." && pwd)
output=${1:-"$repository/work/release/woo-poly-integration.zip"}
staging=$(mktemp -d "${TMPDIR:-/tmp}/woopoly-package.XXXXXX")
trap 'rm -rf "$staging"' EXIT
rsync --archive --safe-links --exclude-from="$repository/.exclude-list" "$repository/" "$staging/woo-poly-integration/"
node "$repository/.github/scripts/package.cjs" "$staging" "$output"
