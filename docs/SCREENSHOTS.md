# MBA issues #1–#5 screenshot verification manifest

Base commit: `bf6683007c42f8efa0b2133fdaf2465f299c0eb9`

## Environment blocker

The implementation was prepared and statically/unit-tested in a sandbox that does **not** provide Docker, a database service, or a runnable WordPress installation. A Chromium executable is present, but there is no truthful public/admin WordPress URL to automate against. Therefore the required browser screenshots were **not captured** in this environment. No mockups or fabricated admin screenshots are included as substitutes.

The source patch includes Docker-based setup/smoke tooling and CI so the receiving agent can perform the missing live verification on a machine with Docker.

## Required captures and status

| Intended filename | Viewport | URL/screen | Test data | What must be verified | Current status |
|---|---:|---|---|---|---|
| `homepage-mobile-390.png` | 390×844 | `/` | default local site | no clipping/overlap, readable type, focus styles, responsive media | NOT CAPTURED — no live WordPress |
| `homepage-desktop-1440.png` | 1440×1000 | `/` | default local site | wide layout, spacing, image treatment, no console errors | NOT CAPTURED — no live WordPress |
| `products-admin-list.png` | desktop | `/wp-admin/edit.php?post_type=mba_product` | at least one local test product | owner-facing labels, cover/featured/order columns | NOT CAPTURED — no live WordPress |
| `product-editor-fields.png` | desktop | Add/Edit Product | local test product only | structured repeaters, relationships, gallery/PDF controls, ratio/dimension/format/file-size/alt/caption/consent guidance | NOT CAPTURED — no live WordPress |
| `product-public-preview.png` | desktop | public product permalink/preview | local test product only | CPT resolves publicly and generic theme fallback renders without runtime/console errors; dedicated product template is issue #13, not this batch | NOT CAPTURED — no live WordPress |
| `products-empty-state.png` | desktop | Products admin with no products, or another relevant validation/empty state | empty catalogue | understandable empty state and no broken UI | NOT CAPTURED — no live WordPress |

## Exact receiving-agent verification sequence

From a fresh checkout of the exact base commit:

```bash
git checkout bf6683007c42f8efa0b2133fdaf2465f299c0eb9
git apply --check /path/to/mba-issues-1-5.patch
git apply /path/to/mba-issues-1-5.patch
cp .env.example .env
./bin/smoke-wordpress.sh
```

The smoke command installs WordPress, activates the plugin/theme, creates a product plus generated local test image, restarts WordPress/database containers, checks persistence, and verifies the product type remains registered while another installed theme is active.

For the browser pass:

1. Run `./bin/setup.sh` and open the `WP_SITE_URL` in `.env`.
2. Log in with the local admin credentials from `.env`.
3. Capture the six screens above with real browser automation at the listed viewports.
4. For Product editor testing, add portrait and landscape images from locally generated/owned test media; do not use competitor media unless its exact provenance is recorded in `docs/sample-media-sources.md`.
5. Exercise all Product editor controls by keyboard, save/reload, reorder gallery images, relate a FAQ/project, and verify backlinks.
6. Verify the public homepage/product screen at 200% zoom and with reduced motion enabled.
7. On every public capture, inspect the browser console for errors and the Network panel for failed first-party requests.
8. Confirm there is no accidental ALUMED branding, copy, technical claim, or project attribution in any capture.

## Limitations

This archive is intentionally manifest-only because creating screenshots without a running WordPress site would misrepresent verification. The batch must not be called fully verified until the live WordPress and browser checks above succeed and the missing PNG files are produced.
