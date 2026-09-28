#!/usr/bin/env sh
set -eu

cd "$(dirname "$0")/.."

project_name=mba_wp_e2e
site_url=http://127.0.0.1:18081

if docker ps -aq --filter "label=com.docker.compose.project=$project_name" | grep -q .; then
	echo "Refusing to reuse existing $project_name containers; remove that disposable test project first." >&2
	exit 1
fi
if docker volume ls -q --filter "label=com.docker.compose.project=$project_name" | grep -q .; then
	echo "Refusing to delete existing $project_name volumes; inspect the disposable test project first." >&2
	exit 1
fi

# Load only the committed local placeholders. Never consume the developer's .env for this disposable stack.
# shellcheck disable=SC1091
set -a
. ./.env.example
set +a
export WORDPRESS_PORT=18081
export WP_SITE_URL=$site_url

compose() {
	docker compose --project-name "$project_name" --env-file .env.example -f docker-compose.yml -f docker-compose.e2e.yml "$@"
}

cleanup() {
	mkdir -p test-results
	compose logs wordpress > test-results/wordpress.log 2>&1 || true
	compose down --volumes --remove-orphans >/dev/null 2>&1 || true
}
trap cleanup EXIT HUP INT TERM

compose config --quiet
compose up -d database wordpress

attempt=0
until compose --profile tools run --rm cli wp core version >/dev/null 2>&1; do
	attempt=$((attempt + 1))
	if [ "$attempt" -ge 40 ]; then
		compose logs wordpress database >&2 || true
		exit 1
	fi
	sleep 2
done

if ! compose --profile tools run --rm cli wp core is-installed >/dev/null 2>&1; then
	compose --profile tools run --rm cli wp core install \
		--url="$WP_SITE_URL" \
		--title="$WP_SITE_TITLE E2E" \
		--admin_user="$WP_ADMIN_USER" \
		--admin_password="$WP_ADMIN_PASSWORD" \
		--admin_email="$WP_ADMIN_EMAIL" \
		--skip-email
fi

compose --profile tools run --rm cli wp plugin activate mba-site-core
compose --profile tools run --rm cli wp theme activate mba-menuiseries
compose --profile tools run --rm cli wp rewrite structure '/%postname%/' --hard
compose --profile tools run --rm cli wp eval-file /var/www/html/wp-content/mba-e2e/prepare.php

npx playwright install chromium firefox webkit
if [ "${E2E_INCLUDE_EDGE:-0}" = '1' ]; then
	npx playwright install msedge
fi

if [ "${E2E_INCLUDE_EDGE:-0}" = '1' ]; then
	PLAYWRIGHT_BASE_URL=$site_url npx playwright test "$@"
elif [ "$#" -gt 0 ]; then
	PLAYWRIGHT_BASE_URL=$site_url npx playwright test "$@"
else
	PLAYWRIGHT_BASE_URL=$site_url npx playwright test --project=chromium --project=firefox --project=webkit "$@"
fi
