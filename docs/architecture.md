# Architecture

## Decision

The project starts with the official Docker WordPress image, a custom block theme, and a site-functionality plugin.

This structure was selected because it:

- runs locally without requiring PHP or Composer on the host;
- uses WordPress's native editing experience;
- keeps business content available if the visual theme changes;
- avoids page-builder lock-in;
- gives the owner structured places to update photos and details.

Production hosting and deployment automation remain to be confirmed. The Docker configuration is for development and must not be treated as production infrastructure.

## Ownership boundaries

### Theme

The theme owns presentation: templates, patterns, styles, navigation presentation, and reusable visual components.

### Site Core plugin

The plugin owns durable business data: content types, taxonomies, metadata, validation, relationships, global settings, and future form integration.

### Media

WordPress Media Library stores originals. Templates use featured images and galleries chosen by editors. Future media work must add upload guidance, responsive formats, crop rules, and performance safeguards without preventing the owner from replacing photographs.

