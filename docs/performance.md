# Performance budgets and audit report

## Targets and measurement conditions

Core Web Vitals are field metrics. Target the 75th-percentile production visit at “good”: LCP ≤ 2.5 s, INP ≤ 200 ms, and CLS ≤ 0.1 ([web.dev thresholds](https://web.dev/articles/defining-core-web-vitals-thresholds)). Lab runs help find regressions but do not establish field compliance.

| Budget | Target |
| --- | --- |
| Theme stylesheet | ≤ 5 KiB gzip |
| MBA theme/plugin first-party JavaScript on a representative page | ≤ 7 KiB gzip, excluding WordPress core |
| LCP photo derivative | ≤ 350 KiB transfer where uploaded source and server format support it |
| Server response (TTFB) | ≤ 800 ms on production-like hosting |
| Production Core Web Vitals | p75 LCP ≤ 2.5 s, INP ≤ 200 ms, CLS ≤ 0.1 |

For a reproducible lab report, seed the same homepage, three products, three projects, and at least five full-resolution photos per project in both revisions. Use the same WordPress/PHP/database versions and hosting location. Run Chrome Lighthouse’s mobile preset with a cold browser cache and default mobile throttling, five times per page; report medians for the homepage, product archive/detail, project archive, and photo-heavy project detail. Capture screenshots and JSON reports. A separate warm-cache pass assesses host caching. Use PageSpeed Insights/CrUX after launch for field p75 results; Lighthouse scores alone cannot prove field Core Web Vitals.

Third-party maps remain unloaded until the visitor activates the map button. The analytics manager is loaded only when a production or staging measurement ID is configured; Google’s tag is requested only after analytics consent. The theme uses system fonts and does not fetch a hosted font family.

## Before/after report for issue #25

The before snapshot is the pre-issue runtime on `origin/main` (`09855ff`). The after snapshot is this issue branch, measured locally on September 28, 2026. These are lab measurements, not production field data.

| Snapshot | Result |
| --- | --- |
| Before | WordPress bootstrap failed before rendering: `add_meta_box()` was called during `init`, where the admin-only function was unavailable. Lighthouse could not collect a valid page baseline. |
| After | The bootstrap now registers SEO post metadata on `init` and editor boxes on `add_meta_boxes`. All five representative pages rendered; their five-run median Lighthouse performance scores were 100. |

| Representative page | Lighthouse score (median) | LCP (median) | TBT (median) | CLS (median) |
| --- | ---: | ---: | ---: | ---: |
| Homepage | 100 | 1,208 ms | 0 ms | 0.000 |
| Product archive | 100 | 1,205 ms | 0 ms | 0.000 |
| Product detail | 100 | 1,205 ms | 0 ms | 0.000 |
| Project archive | 100 | 1,206 ms | 0 ms | 0.000 |
| Photo-heavy project detail | 100 | 1,279 ms | 0 ms | 0.000 |

Conditions: WordPress 6.8.2, PHP 8.3, MariaDB 11.4.5, Chrome 154, Lighthouse 13.5.0 mobile preset, cold browser profile, five runs per route. The local fixture contained three products, three projects, six generated 1920×1280 JPEG originals, and six before/during/after gallery entries on each project; WordPress generated the requested WebP derivatives. This report is attached as `docs/performance.md`; raw Lighthouse JSON reports remained local, and the temporary WordPress fixture was removed after measurement.

Lighthouse’s first homepage run was a cold-start outlier (score 76, LCP 1,225 ms, TBT 1,137 ms); the following four scored 100 with 0 ms TBT. The median is reported above and all other routes had a median TBT of 0 ms.

The base had no valid before Lighthouse result because it failed before rendering; therefore the scores above demonstrate the repaired branch’s lab baseline, not a measured speed delta from a working prior revision. The tracked assets’ source bytes are identical before and after this issue; cache-busting and budget enforcement improve delivery correctness and guard future growth, not current transfer size.

| Asset | Before | After | Change |
| --- | ---: | ---: | ---: |
| Theme CSS | 18,177 B (3,727 B gzip) | 18,177 B (3,727 B gzip) | 0 B |
| Theme navigation JS | 448 B (290 B gzip) | 448 B (290 B gzip) | 0 B |
| Theme mobile conversion JS | 377 B (242 B gzip) | 377 B (242 B gzip) | 0 B |
| Theme project gallery JS | 1,034 B (388 B gzip) | 1,034 B (388 B gzip) | 0 B |
| Theme map JS | 521 B (307 B gzip) | 521 B (307 B gzip) | 0 B |
| Optional analytics manager JS | 8,594 B (2,485 B gzip) | 8,594 B (2,485 B gzip) | 0 B |

The change versions theme scripts from their file modification times so a deployment that changes a script does not leave returning visitors using a stale hard-cached copy. The existing responsive-image work makes the hero an HTML-discoverable eager image with high fetch priority, supplies `srcset`/`sizes`, and lazy-loads below-fold cards and galleries. Maps and analytics keep their explicit consent gates.

## Runtime report and limitations

The controlled pre-change archive reports identified the first product/project card image as the LCP element. Its request was discoverable in HTML, but `loading="lazy"` and no high-priority hint failed Lighthouse’s LCP discovery checklist. After the change, both archive image requests pass all three checks: discoverable, eager, and `fetchpriority="high"`.

| Archive LCP check | Before | After |
| --- | --- | --- |
| Discoverable in initial HTML | Pass | Pass |
| Eagerly loaded | Fail (`loading="lazy"`) | Pass (`loading="eager"`) |
| High priority hint | Fail | Pass (`fetchpriority="high"`) |
| Lighthouse LCP discovery audit | Fail | Pass |

Median LCP/TBT/CLS did not materially change on localhost because there is no real network latency; the improvement is that a known LCP image is no longer deferred.

The local test does not provide a production-like network path or hosting cache. INP is a field metric and cannot be established from this lab report. The CI compose job validates Compose configuration only, so production host TTFB/caching, real client image transfer sizes, and 75th-percentile field Core Web Vitals remain unverified. The production performance targets remain to be checked after launch with PageSpeed Insights/CrUX.
