# Testing and quality gates

Issue #2 adds the same quality checks locally and in GitHub Actions.

## Local commands

PHP tooling requires Composer 2 and PHP 8.1+ (CI uses PHP 8.3):

```bash
composer install
composer check
```

`composer check` runs dependency-free PHP syntax validation, WordPress Coding Standards (PHPCS), PHPStan static analysis, and a guard proving deliberately invalid PHP is rejected by the syntax gate.

Front-end/configuration tooling requires Node.js 22+:

```bash
npm install --ignore-scripts
npm run check
```

This runs ESLint, Stylelint, and parses every repository JSON/YAML file outside generated/dependency/upload directories.

Compose validation:

```bash
cp .env.example .env
docker compose config --quiet
```

Runtime verification remains `./bin/smoke-wordpress.sh` from issue #1.

## What is intentionally excluded

Quality tools exclude generated/dependency/state directories: `vendor/`, `node_modules/`, `wordpress/`, `wp-content/uploads/`, local `.env`, logs, IDE metadata, and backups. Custom plugin/theme source is always included.

## GitHub Actions and branch protection

`.github/workflows/quality.yml` runs on every pull request and every push to `main`. The repository administrator should configure branch protection/rulesets for `main` to require the `php`, `frontend-and-config`, and `compose` jobs before merging, require pull-request review, and block force pushes/deletion. This repository patch documents the intended rules but does not mutate GitHub settings.

## Theme foundation verification (issue #3)

`composer check` also runs `tests/theme/theme-foundation.php`, which guards centralized theme tokens, all required responsive breakpoints, minimum touch-target treatment, reduced-motion handling, flexible image cropping, and the absence of a fixed viewport minimum that would block zoom/reflow.

Manual acceptance still requires rendering representative content at 360, 390/430, 768, 1024, and 1440 px and at 200% browser zoom. Check keyboard focus, portrait/landscape uploads, text reflow, and reduced-motion behavior in a real WordPress/browser environment; do not treat the static assertions as a substitute for that review.

## Content-type verification (issue #4)

`composer check` runs `tests/php/content-types-test.php`. It asserts all five owner-facing post types, REST/Gutenberg support, normal editor capability mapping, revisions, public product/project archives, private FAQ/testimonial/partner behavior, required taxonomies, and the absence of rewrite flushing during normal `init` registration.

The Docker smoke test additionally verifies that the content types remain registered while another installed theme is active, then restores MBA Menuiseries. This demonstrates that durable business content is plugin-owned rather than theme-owned.

## Product editing verification (issue #5)

`composer check` validates product metadata schemas, media types, relationship targets, and optional performance values. In a live WordPress installation, create a draft Product and verify that the structured fields survive a Gutenberg save and reload. Set its cover image through the native Featured Image control and assign gallery images through the Media Library; the gallery must retain editor order and discard non-image IDs. A technical document must be an existing PDF attachment. Select a FAQ and a Project, save, and confirm each related record gains the product ID in `mba_related_products`; removing a selection or deleting the product must remove its backlink. The product detail/gallery presentation belongs to issues #13 and #23.

## Project editing verification (issue #6)

The metadata checks also cover real calendar dates, testimonial target validation, and project field registration. In WordPress, create a draft Project and save/reload its location, case-study details, materials, and optional date. Select photos independently for Before, During, and After; reopen the chooser to confirm current selections, edit captions/alt text in Media Library, drag to reorder, and remove or replace photos. Each gallery must retain its own image IDs and order. Choose installed products and verify `mba_related_projects` on each product; removing the relationship or permanently deleting the project must remove that backlink. Use only city/region and general location taxonomy terms, with client publication consent. Public case-study section rendering and empty optional-section handling are verified with issue #15.

## Reusable content verification (issue #7)

Use FAQ titles as questions and the main editor as answers; link products and verify reverse `mba_related_faqs` IDs after saving/removing links. All three content types expose display order. Partner logos accept images only, and website links accept HTTP/HTTPS. Testimonials accept optional ratings from 1–5. Try featuring a testimonial without consent via both the editor and direct metadata updates; it must remain unfeatured. Confirm consent, feature it, then withdraw consent: featuring must be removed. Review text, FAQ answers, and partner descriptions stay in native WordPress content with revisions. Public optional-field rendering is covered by the homepage, product, and FAQ templates.
