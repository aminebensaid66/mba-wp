# Local sample content

All records created by `bin/seed-sample-content.sh` are synthetic and start as WordPress drafts. The command is manual, requires the local `.env`, and is never called on plugin activation, CI deployment, or site startup. Every title, body, term, caption, alt text, and bundled SVG says that it is fictitious and must be replaced. The illustrations are original neutral geometry in landscape (3:2), portrait (2:3), and square (1:1) ratios; they are not photographs of MBA work or real buildings.

For approved production content, required source fields, rights, and sign-off, use [the client input checklist](../client-input-required.md) and [the migration guide](../content-migration.md).

Run the command only against the local development stack after installing WordPress and activating MBA Site Core:

```sh
./bin/setup.sh
bin/seed-sample-content.sh
```

The seeder creates one draft product, one draft project, one draft FAQ, related taxonomy terms, three Media Library illustrations with alt text/captions/dimensions, and working product/project/FAQ relationships. It is idempotent: a rerun finds the stable hidden sample keys and preserves edits. Empty project location and gallery fields are intentional examples of optional data. Open each record in its normal WordPress editor to practice updating fields.

Do not run the seeder on staging or production. Draft status keeps the records out of public archives and search. Before production launch, remove every record and illustration carrying `_mba_sample_content` / `_mba_sample_key`, then verify the public catalogue contains only MBA-approved material. If a sample is accidentally published, unpublish it immediately and replace its content and media before republishing.

The fixture includes no testimonials, partner endorsements, certifications, technical measurements, prices, warranties, customer identities, addresses, or performance claims. Do not convert a synthetic example into a business statement without written owner approval. Remove seeded records from the local database with `bin/remove-sample-content.sh --confirm` when the examples are no longer needed; this removes only records carrying the seeder's internal marker.
