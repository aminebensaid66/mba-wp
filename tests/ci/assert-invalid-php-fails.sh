#!/usr/bin/env sh
set -eu

tmp="$(mktemp)"
trap 'rm -f "$tmp"' EXIT
printf '%s\n' '<?php function broken( {' > "$tmp"

if php -l "$tmp" >/dev/null 2>&1; then
  echo "Expected php -l to reject deliberately invalid PHP." >&2
  exit 1
fi

echo "Invalid PHP correctly fails the syntax gate."
