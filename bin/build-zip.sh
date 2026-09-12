#!/usr/bin/env bash
#
# Package the theme for upload through Appearance > Themes > Add New > Upload.
# Produces dist/studiogreen.zip with a single top-level studiogreen/ folder,
# which is the layout WordPress expects.

set -euo pipefail

root="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
theme="studiogreen"
out="$root/dist"

cd "$root"

if [ ! -f "$theme/style.css" ]; then
  echo "error: $theme/style.css not found. Run this from the project checkout." >&2
  exit 1
fi

# Fail early rather than shipping a theme that white-screens on activation.
if command -v php >/dev/null 2>&1; then
  echo "Checking PHP syntax..."
  find "$theme" -name '*.php' -print0 | xargs -0 -n1 php -l >/dev/null
  echo "  ok"
fi

version="$(sed -n 's/^ *Version: *//p' "$theme/style.css" | head -1)"
echo "Packaging $theme ${version:-(no version found)}"

mkdir -p "$out"
rm -f "$out/$theme.zip"
zip -r -q "$out/$theme.zip" "$theme" -x '*.DS_Store' -x '*/.git/*' -x '*__MACOSX*'

echo "Wrote $out/$theme.zip ($(du -h "$out/$theme.zip" | cut -f1))"
