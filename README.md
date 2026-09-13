# MBA Menuiseries Belhaj Ali

WordPress website for MBA Menuiseries Belhaj Ali, built as a maintainable product catalogue, project portfolio, and lead-generation platform.

## Architecture

- `wp-content/themes/mba-menuiseries`: custom native block theme
- `wp-content/plugins/mba-site-core`: products, projects, FAQs, testimonials, partners, taxonomies, and global company settings
- `PROJECT.md`: authoritative functional specification
- `MBA_MENUISERIES_WEBSITE_SPEC.md`: market research and business requirements
- `AI_DEVELOPMENT_PROMPT.md`: agent implementation workflow

Business content is stored independently of the theme. The owner can replace photos, edit product details, publish projects, and change contact information from WordPress without modifying templates.

## Local setup

Requirements: Docker Desktop and Docker Compose.

```bash
cp .env.example .env
docker compose up -d
```

Open <http://localhost:8080> and complete the WordPress installation. Then activate:

1. **MBA Site Core** plugin
2. **MBA Menuiseries** theme

Optional WP-CLI usage:

```bash
docker compose --profile tools run --rm cli wp plugin activate mba-site-core
docker compose --profile tools run --rm cli wp theme activate mba-menuiseries
```

## Content editing

- Products and their featured photos: **Products**
- Completed work and galleries: **Projects**
- Frequently asked questions: **FAQs**
- Reviews: **Testimonials**
- Suppliers/certifications: **Partners**
- Phone, WhatsApp, email, address, opening hours, and social links: **MBA Settings**
- Normal pages and advice articles: native WordPress editor

Never add client claims, certifications, warranties, reviews, or technical values until MBA confirms them.

## Useful commands

```bash
docker compose up -d
docker compose logs -f wordpress
docker compose down
docker compose config --quiet
```

## Status

This first commit provides the development environment, editable content foundation, and block-theme shell. Feature delivery is tracked through GitHub issues.

