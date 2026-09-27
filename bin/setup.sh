#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

if ! command -v docker >/dev/null 2>&1; then
  echo "Docker is required. Install Docker Desktop (with Compose v2) and retry." >&2
  exit 1
fi

if [ ! -f .env ]; then
  cp .env.example .env
  echo "Created .env from .env.example (local placeholder credentials only)."
fi

# shellcheck disable=SC1091
set -a
. ./.env
set +a

docker compose config --quiet
docker compose up -d database wordpress

attempt=0
until docker compose --profile tools run --rm cli core version >/dev/null 2>&1; do
  attempt=$((attempt + 1))
  if [ "$attempt" -ge 30 ]; then
    echo "WordPress files did not become ready in time." >&2
    docker compose logs wordpress >&2 || true
    exit 1
  fi
  sleep 2
done

if ! docker compose --profile tools run --rm cli core is-installed >/dev/null 2>&1; then
  docker compose --profile tools run --rm cli core install \
    --url="$WP_SITE_URL" \
    --title="$WP_SITE_TITLE" \
    --admin_user="$WP_ADMIN_USER" \
    --admin_password="$WP_ADMIN_PASSWORD" \
    --admin_email="$WP_ADMIN_EMAIL" \
    --skip-email
fi

docker compose --profile tools run --rm cli plugin activate mba-site-core
docker compose --profile tools run --rm cli theme activate mba-menuiseries

echo "MBA local WordPress is ready at $WP_SITE_URL"
