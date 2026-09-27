# Architecture

## Decision

The development environment uses official container images for WordPress/PHP, MariaDB, and WP-CLI. The application is split between a native block theme and a site-functionality plugin.

This structure was selected because it:

- runs locally with Docker Compose and no host PHP/database requirement;
- uses WordPress's native editing experience;
- keeps durable business content available if the visual theme changes;
- avoids page-builder lock-in;
- gives the owner structured places to update photographs and business details.

The Compose stack is local-development infrastructure only. Production hosting, backups, secrets, TLS, caching, deployment, and upgrade policy must be selected separately.

## Supported local stack

The defaults in `.env.example` are the supported issue-#1 development matrix:

- WordPress 6.8.2 / PHP 8.3 Apache image;
- WP-CLI 2.12.0 / PHP 8.3 image;
- MariaDB 11.4.5;
- Docker Compose v2.

Changing these image tags is an explicit maintenance action and should be followed by the smoke verification in `README.md`.

## Ownership boundaries

### Theme

`wp-content/themes/mba-menuiseries` owns presentation: templates, patterns, styles, navigation presentation, and reusable visual components. Theme activation/deactivation must never create, migrate, or delete durable business content.

### Site Core plugin

`wp-content/plugins/mba-site-core` owns durable business data: content types, taxonomies, metadata, validation, relationships, global settings, and future form integration. Stored content remains in the WordPress database independently of the active theme.

### Media

WordPress Media Library owns uploaded media records and files. Templates consume featured images/galleries selected by editors and must tolerate missing/replaced media. In local Docker development, uploads live in the `wordpress_data` named volume rather than the repository.

## Local persistence and permissions

MariaDB data lives in `database_data`. WordPress core/generated files and uploads live in `wordpress_data`. Both survive container recreation and ordinary restarts. `./bin/reset.sh` is the explicit destructive operation that removes those local volumes.

Theme/plugin source directories are bind-mounted read-only into WordPress. WP-CLI runs as UID/GID `33:33`, matching the `www-data` user used by the official WordPress image for generated files. This avoids root-owned files in the shared WordPress volume.

`.env` is ignored and is the only local location for changed development credentials/configuration. `.env.example` contains variable names and non-secret placeholders only.
