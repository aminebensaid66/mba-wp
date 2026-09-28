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

## Global settings verification (issue #8)

The field tests cover international phone normalization, rejection of invalid email/map sources/unknown keys, favicon dimensions, and omission of missing company details. Sign in as an Editor: MBA Settings must be accessible and save through the native options form, while plugin installation and file editing remain unavailable. Save a formatted phone number and check the canonical value, readable display, telephone URL, WhatsApp URL, and `mba/company-detail` block. Change or clear the number and confirm those outputs change together. Invalid map embed sources must produce one validation error. See [the documented API](company-settings.md) for future template consumers.

## Navigation and footer verification (issue #9)

At desktop width, confirm the sticky header exposes the owner-editable company name/logo and quote action, and the Products submenu contains the archive plus non-empty product categories. At 390 px, activate the navigation button, confirm the drawer has a labeled close button and no horizontal overflow, open the Products submenu, then press Escape and confirm the drawer closes. Tab through the drawer and verify focus remains visible and returns to the opener. Breadcrumbs appear on internal pages with the current location marked `aria-current="page"`; the sticky offset keeps anchored content visible. The footer omits empty contact/social/legal sections and only renders confirmed settings, privacy, and published legal-page links.

## Homepage verification (issue #10)

The front page is a locked server-rendered composition. Set homepage hero/section text and approved image IDs in MBA Settings, then replace those images through the Media Library; no template markup changes are needed. The page keeps one H1 and two hero actions, hides empty trust/reason/process/material sections, and uses featured products/projects/testimonials/partners with display-order fallbacks. Testimonials require publication consent; latest published articles appear only when available. Verify the ordered sections from `PROJECT.md`, the neutral fallback heading, no legacy placeholder claim, and the final quote/WhatsApp/telephone actions when valid global settings exist. Test at 360, 390/430, 768, 1024, and 1440 px with 200% zoom.

## Company page verification (issue #11)

Create or open the `/entreprise/` page and confirm the `page-entreprise.html` template renders the settings-driven history, founder/team, values, capabilities, workshop/team gallery, verified certifications, service zones, featured projects, and quote CTA. Replace gallery images through MBA Settings and the Media Library; empty team/certification/gallery sections must disappear. Do not enter unverified certifications or private addresses. Confirm project cards and the quote link work at mobile and desktop widths.

## Product archive verification (issue #12)

Open `/produits/` and verify the category and application filters submit normal GET requests with shareable `categorie` and `application` parameters. Valid selections remain selected after reload, pagination preserves both parameters, and reset removes them. Cards show owner-managed cover/title/excerpt/category data and remain usable without a cover image. A filter with no matches renders a status empty state with reset and quote links. Check keyboard labels, visible focus, and 360–1440 px layouts with JavaScript disabled.

## Product detail verification (issue #13)

Open a published Product and verify the page renders its cover, short description, optional benefits/configurations/materials/glazing/colors/applications/maintenance, labeled verified performance rows, responsive gallery links, and related FAQs/projects/products only when populated. Gallery links must preserve Media Library captions/alt text and expose full-size targets for a lightbox. The technical document must show a PDF link with `application/pdf` and file size when the local file exists. The quote URL must include the current `product_id` and `product_slug`; related products must exclude the current product. Empty metadata must not leave blank headings or placeholder claims.

## Projects archive verification (issue #14)

Open `/realisations/` and submit the project type, general location, and installed-product filters as normal GET parameters (`type_projet`, `lieu`, and `produit`). Verify selected values and pagination survive reload, cards show cover/name/general location/year/installed product names, and no street address is printed. Missing covers still leave usable cards; image crops stay consistent through CSS without modifying originals. Empty results provide reset and quote links and announce their status.

## Project detail verification (issue #15)

Open a published project with case-study fields and before/during/after media. Verify missing phases and specifications do not create empty sections, galleries expose captions and useful alt text, image buttons open a dialog that closes with Escape and returns focus, and related-project links preserve `from_project` context. Confirm the page only displays city/region and never a private street address.

## FAQ verification (issue #17)

Open `/faq/` and category links. Verify questions and answers come from published FAQ entries, category selection is shareable, buttons expose `aria-expanded`/`aria-controls`, keyboard activation works, and all answers remain visible when JavaScript is disabled. Confirm no FAQ schema is emitted for unsupported or empty content.

## Secure quote verification (issue #18)

Submit `/devis/` with valid data and confirm one private Quote Lead is saved, the configured receiving address gets the notification and private attachments, and a supplied email receives an acknowledgement. Test invalid required fields, malformed optional email, email-required setting, oversized/wrong-MIME/too-many files, the honeypot, a repeated token, and mail failure. A mail failure must leave the private lead available in the admin and show an error rather than success. Download an uploaded file as an Editor and verify a logged-out visitor cannot request the same file. Product/project CTAs must retain validated source IDs; UTM values are only stored on consented submissions. Configure authenticated SMTP before production use; `wp_mail()` uses the site's configured mail transport.

## Blog verification (issue #16)

Open `/conseils/`, a category archive, and a published article. Verify category links, nine-item pagination, cover/excerpt cards, readable article width, visible published/updated/author metadata, related articles, and the contextual quote CTA. Confirm article content remains editable through the native post editor.

## Contact page and short form (issue #19)

Open `/contact/` and verify only configured company details, opening hours, service areas, showroom visit guidance, and social links appear. Phone, email, and WhatsApp actions must use their matching link schemes. The directions link must work before map consent; the Google Maps iframe must not exist until its explicit load button is activated. Submit with only a phone and then only an email, and verify success, invalid-input, notification-failure, and duplicate/spam states. Confirm enquiries are stored as private contact leads in wp-admin and that the message form requires privacy consent. With no optional business or social settings configured, their sections must be omitted.

## Mobile conversion actions (issue #20)

At widths below 768 px, configure each combination of phone and WhatsApp number and confirm the fixed action bar contains only configured actions, stays clear of the final page controls, and respects the device safe area. Confirm the call link uses the canonical international number. Set an editable WhatsApp draft, then open the action from a product and a project: the relevant public title should be appended to the prefilled message, which remains editable in WhatsApp. Inspect `mba:conversion-action` events and confirm they contain only `action` and the generic `contentType`, never a phone number, message, or title. At desktop width the bar is hidden; with no contact numbers it is omitted entirely.

## Search metadata and structured data (issue #21)

Configure the site-wide SEO title, description, social image, and a few internal redirects under MBA Settings. Confirm per-content title/description fields and featured images override those defaults. Check canonical URLs and Open Graph/Twitter previews on a page, product, project, article, and paginated archive. Inspect JSON-LD: business schema must use only verified settings, use LocalBusiness only with a configured public address, articles must reflect visible post data, products must not invent offers/prices/ratings, and service entries must come from the visible company capabilities. Verify `/wp-sitemap.xml` excludes attachments and private leads; searches, filtered archives, 404s, attachment views, form confirmation states, and staging sites must be noindex. Redirects use one `/old-path/ => /new-path/` mapping per line, stay on this host, and return a permanent 301. Recheck redirects after changes and remove obsolete mappings; no permalink flush is needed.

## Analytics consent and events (issue #22)

Leave both GA4 IDs blank and confirm no analytics UI or third-party script appears. Set a production ID and a different staging ID; production and staging must select only their own ID, while local/development sends nothing. In a fresh browser, confirm no Google tag or analytics requests occur before consent; reject and verify it remains unloaded; accept and verify it loads; then reopen Privacy settings in the footer, revoke consent, and confirm tracking stops and analytics cookies are cleared. Test phone, WhatsApp, email, directions, PDF download, quote/contact start, submit, client validation, server error, and product/project views. Inspect events to ensure they contain only allowlisted event names and generic form/content types, never link destinations, page query strings, titles, names, phone numbers, emails, messages, or upload filenames. In the GA4 property, disable Enhanced Measurement for outbound clicks and file downloads so automatic events cannot collect raw URLs; use this site’s redacted event handlers instead.

## Responsive media pipeline (issue #23)

Upload a full-resolution JPEG/PNG from the Media Library and replace it in the product cover, project cover/gallery, homepage hero, and company gallery. Originals must remain available for future crops; the Media Library should expose crop focal-point presets. Verify WordPress generates card, hero, gallery, and logo variants plus WebP derivatives only when the active image editor supports WebP. Inspect rendered images for matching `srcset`/`sizes`, intrinsic dimensions, and preserved captions/alt text. Confirm WordPress's normalized image metadata omits camera and capture details while retaining editorial caption, credit, copyright, and orientation; the uploaded original itself is preserved unchanged. The homepage/product/project/article LCP image must be eager with high fetch priority; below-fold cards/galleries stay lazy. At 360–1440 px confirm image aspect ratios reserve layout space and focal points affect cover crops. Test a server-limit image, a file over 25 MB, and an unsupported image format for actionable upload messages. Replace images again and verify the selected attachment changes without destructive preprocessing.
