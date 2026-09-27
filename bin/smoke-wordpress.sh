#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

[ -f .env ] || { echo "Run ./bin/setup.sh first." >&2; exit 1; }

run_wp() {
  docker compose --profile tools run --rm cli "$@"
}

run_wp plugin is-active mba-site-core
run_wp theme is-active mba-menuiseries

product_id="$(run_wp post create --post_type=mba_product --post_title='Persistence smoke product' --post_status=publish --porcelain)"

# A valid 1x1 PNG, generated only inside the disposable CLI container.
attachment_id="$(docker compose --profile tools run --rm cli sh -lc '
  printf "%s" "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=" | base64 -d > /tmp/mba-smoke.png
  wp media import /tmp/mba-smoke.png --post_id='"$product_id"' --title="Persistence smoke image" --porcelain
')"
run_wp post meta update "$product_id" _thumbnail_id "$attachment_id" >/dev/null

docker compose restart database wordpress >/dev/null
sleep 5

run_wp post get "$product_id" --field=post_title | grep -qx 'Persistence smoke product'
run_wp post get "$attachment_id" --field=post_type | grep -qx 'attachment'
run_wp post meta get "$product_id" _thumbnail_id | grep -qx "$attachment_id"

if docker compose exec -T wordpress sh -lc 'test -f /var/www/html/wp-content/debug.log && grep -E "PHP (Warning|Notice|Deprecated|Fatal error)" /var/www/html/wp-content/debug.log'; then
  echo "PHP diagnostics were written to wp-content/debug.log." >&2
  exit 1
fi

echo "Smoke test passed: activation, product/media creation, restart persistence, and PHP warning scan."
