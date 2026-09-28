#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

if [ "${1:-}" != '--confirm' ] || [ "$#" -ne 1 ]; then
	echo 'This permanently deletes only records created by the local sample seeder.' >&2
	echo 'Review the target WordPress environment, then rerun with --confirm.' >&2
	exit 2
fi
if [ ! -f .env ]; then
	echo 'Create .env before connecting to the local WordPress stack.' >&2
	exit 1
fi

compose() {
	docker compose -f docker-compose.yml -f docker-compose.sample-content.yml --profile tools "$@"
}

site_url=$(compose run --rm cli wp option get siteurl)
case "$site_url" in
	http://localhost|http://localhost:*|https://localhost|https://localhost:*|https://localhost/*|http://127.0.0.1|http://127.0.0.1:*|https://127.0.0.1|https://127.0.0.1:*) ;;
	*)
		echo "Refusing to remove sample content outside a localhost site: $site_url" >&2
		exit 1
		;;
esac

compose run --rm cli wp eval-file /var/www/html/wp-content/mba-sample-content/remove.php
