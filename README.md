# MBA Menuiseries Belhaj Ali

WordPress website for MBA Menuiseries Belhaj Ali, built as a maintainable product catalogue, project portfolio, and lead-generation platform.

## Architecture

- `wp-content/themes/mba-menuiseries`: custom native block theme; presentation only.
- `wp-content/plugins/mba-site-core`: durable products, projects, FAQs, testimonials, partners, taxonomies, metadata, and global business settings.
- WordPress Media Library: owner-managed original photographs and documents, persisted in the Docker `wordpress_data` volume locally.
- `PROJECT.md`: authoritative functional specification.

Changing or deactivating the theme must not delete business content. Products, projects, relationships, settings, and Media Library records belong to WordPress/the site plugin, not theme templates.

## Local requirements

- Docker Desktop or Docker Engine with Docker Compose v2.
- Ports: `8080` by default (change `WORDPRESS_PORT` in `.env` if needed).
- No host PHP, Composer, Node.js, or database installation is required for issue #1.

The checked local stack is defined in `.env.example`: WordPress 6.8.2 on PHP 8.3, MariaDB 11.4.5, and WP-CLI 2.12.0. Production versions must be reviewed separately before launch.

## One-command setup

From a fresh checkout:

```bash
./bin/setup.sh
```

The script creates an ignored `.env` from `.env.example`, validates Compose, starts MariaDB/WordPress, installs WordPress when necessary, and activates **MBA Site Core** and **MBA Menuiseries**. Open <http://localhost:8080> unless you changed `WP_SITE_URL`/`WORDPRESS_PORT`.

The values in `.env.example` are local placeholders, not production credentials. Keep real credentials in the ignored `.env` or deployment secret store.

### Verify the fresh installation and persistence

```bash
./bin/smoke-wordpress.sh
```

The smoke test confirms plugin/theme activation, creates a published product plus a generated PNG attachment, restarts the database and WordPress containers, confirms both records persist, and fails if the WordPress debug log contains PHP warnings/notices/deprecations/fatal errors.

### Reset local state

```bash
./bin/reset.sh
# non-interactive disposable environments only:
./bin/reset.sh --yes
```

Reset removes the project containers and named database/media volumes and deletes only the ignored local `.env`. It never deletes repository source files.

## Persistence and directory ownership

- `database_data` persists MariaDB data across normal `docker compose down`/restart operations.
- `wordpress_data` persists the WordPress installation and `wp-content/uploads` media.
- The theme and `mba-site-core` plugin are bind-mounted **read-only** from this repository; edit them on the host, not in wp-admin.
- WordPress/Apache owns generated WordPress files and uploads inside the named volume. WP-CLI runs as UID/GID `33:33` to match the official WordPress image.
- `./bin/reset.sh` intentionally deletes the named volumes; ordinary restarts do not.

## Content editing

- Products, cover/gallery photos, technical PDFs, optional specifications, FAQs, and related projects: **Products**
- Case studies, general location, installed products, and separate before/during/after galleries: **Projects**
- Frequently asked questions: **FAQs**
- Reviews: **Testimonials**
- Suppliers/certifications: **Partners**
- Phone, WhatsApp, email, address, opening hours, and social links: **MBA Settings**
- Normal pages and advice articles: native WordPress editor

Never add client claims, certifications, warranties, reviews, or technical values until MBA confirms them.

See [company settings API and Editor permissions](docs/company-settings.md) for global fields and template/block usage.

## Useful commands

```bash
docker compose up -d
docker compose ps
docker compose logs -f wordpress
docker compose --profile tools run --rm cli wp --info
docker compose config --quiet
docker compose down
```

For clearly labeled local sample drafts, follow [`docs/sample-content/README.md`](docs/sample-content/README.md). For approved-site imports, use [`docs/content-migration.md`](docs/content-migration.md) and [`docs/client-input-required.md`](docs/client-input-required.md). Never seed sample content on staging or production.
