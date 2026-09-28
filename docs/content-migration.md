# Content preparation and migration guide

Use this process when replacing local examples or moving approved MBA content into a new WordPress environment. Migrate first to staging, then repeat the verified import for production. Never import the repository's synthetic sample drafts into a public site.

## Prepare and approve the source

1. Name an MBA content owner and editor for each product, project, FAQ, and media group. Keep the original files and a dated export read-only as the source of truth.
2. Complete [the client input checklist](client-input-required.md). Obtain written publication and media permissions before importing photographs, quotations, partner marks, or identifiable people. Record the consent owner/date and any limits on channels, captions, cropping, or expiry in the source register; never invent missing details.
3. Build a mapping sheet with one row per source item and these columns: content type, stable source ID, destination slug, title, body, summary, status, author, original creation/publication dates with timezone, taxonomy names, product/project/FAQ relationships, featured image filename, ordered gallery filenames, and each attachment's title, caption, description, alt text, credit, copyright/permission note, focal crop, and source path. [`content-migration-map.csv`](content-migration-map.csv) is a blank starter template; store the completed copy in the approved private project location, not Git.
4. Preserve original filenames in a separate column even if a destination filename must be normalized. Keep dates in ISO 8601 form with the original timezone documented. Mark unknown values as unknown; leave optional fields empty instead of guessing.

## Transfer content and media

1. Take and verify a database and uploads backup of both source and destination. Record the WordPress, PHP, and plugin/theme versions. Test the restoration procedure before any production import.
2. Use **Tools → Export** or WP-CLI's WordPress WXR export for selected post types and date ranges. In **Tools → Import → WordPress**, enable attachment download when source URLs remain reachable. For a large or private migration, transfer uploads separately and retain the WXR file as the post/term/date reference. Do not copy SQL rows by hand.
3. Import media before repairing relationships. Verify attachment title, caption, description, alt text, original date, MIME type, dimensions, and file URL in the Media Library. WordPress importers may skip unavailable source files or produce new attachment IDs; compare every source filename to the destination attachment and record the old-to-new ID mapping.
4. Re-select featured images and ordered galleries in each product/project editor. Re-select related products, projects, and FAQs through the standard relationship controls after all destination posts exist. Source database IDs are not portable; never assume a migrated ID stayed the same. Confirm reverse relationships as well.
5. Preserve destination slugs when URLs are part of an existing campaign or search listing. When a slug must change, record the old and new absolute paths and add a permanent redirect. Keep publication dates only when they reflect the original record and have been approved; distinguish publication date from project completion date.

## Verify before release

Compare imported item counts by content type, taxonomy, status, date, and author against the mapping sheet. Open every mapped product/project/FAQ in wp-admin and verify media, metadata, dates, slugs, and relationships. Check representative portrait, landscape, and square images at mobile and desktop widths. Crawl old paths and internal links, review redirect targets, inspect the public pages, and check WordPress Site Health and PHP/browser logs. Resolve missing media or relationships before launch. Retain the source export, mapping sheet, consent register, backup IDs, and verification record with the release notes.

Avoid broad database search/replace for URLs: serialized values can be corrupted. If a domain changes, use a serialization-aware WordPress migration tool or `wp search-replace` with a tested dry run and a fresh backup. Do not rewrite attachment URLs unless the destination files and metadata have been verified.

The migration guide does not certify any sample or migrated content as accurate. MBA owns the final review and approval.
