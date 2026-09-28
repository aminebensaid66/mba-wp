#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

if [ ! -f .env ]; then
	echo "Create .env from .env.example before seeding local sample content." >&2
	exit 1
fi

compose() {
	docker compose -f docker-compose.yml -f docker-compose.sample-content.yml --profile tools "$@"
}

site_url=$(compose run --rm cli wp option get siteurl)
case "$site_url" in
	http://localhost|http://localhost:*|https://localhost|https://localhost:*|https://localhost/*|http://127.0.0.1|http://127.0.0.1:*|https://127.0.0.1|https://127.0.0.1:*) ;;
	*)
		echo "Refusing to seed sample content outside a localhost site: $site_url" >&2
		exit 1
		;;
esac

compose run --rm cli wp eval-file /var/www/html/wp-content/mba-sample-content/seed.php
