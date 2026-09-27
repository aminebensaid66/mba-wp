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
