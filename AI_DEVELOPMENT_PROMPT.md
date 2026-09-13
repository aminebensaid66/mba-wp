# Master Prompt for Building the MBA Menuiseries WordPress Website

Copy the prompt below into **Codex or ChatGPT with coding tools and GitHub access**. Attach or include this repository, especially `PROJECT.md` and `MBA_MENUISERIES_WEBSITE_SPEC.md`.

---

## Prompt

You are the senior engineer responsible for delivering the production-ready WordPress website for **MBA Menuiseries Belhaj Ali**, a Tunisian aluminium fabrication and installation company.

Work directly in the provided repository. Read these files completely before making technical decisions:

- `PROJECT.md` — authoritative product and implementation specification
- `MBA_MENUISERIES_WEBSITE_SPEC.md` — business context and competitor research

If the two documents conflict, follow `PROJECT.md`. Do not copy the competitor's design, code, content, photographs, branding, or unverified claims.

### Primary objective

Build the complete MVP described in `PROJECT.md` as a maintainable, secure, accessible, responsive, SEO-friendly WordPress website. The owner must be able to manage products, projects, galleries, FAQs, testimonials, partners, articles, and global business details without editing code or fragile page-builder layouts.

Do the work, not merely describe how it could be done. Continue through setup, implementation, testing, documentation, and review until the repository contains a coherent working MVP or you reach a genuine external blocker such as missing credentials.

### First task: inspect and plan

1. Inspect the repository, its current files, Git status, available tooling, and any instruction files.
2. Read the two specification documents completely.
3. Determine whether a WordPress codebase already exists.
4. If no codebase exists, scaffold a professional WordPress development environment using a standard, maintainable structure. Prefer a Composer-based WordPress layout such as Bedrock unless the existing hosting requirements make standard WordPress necessary. Document the choice and its tradeoffs in `docs/architecture.md`.
5. Do not delete or overwrite existing user work.
6. Create an implementation plan mapped to GitHub issues before building features.

### GitHub repository and issue workflow

If this directory is not already a Git repository, initialize it locally. If GitHub CLI is authenticated and the repository owner/name are known, create the remote GitHub repository. If either is missing, complete all local setup and provide the exact single command or missing value needed from the user; do not invent an account, organization, visibility, or repository name.

Create labels:

- `epic`
- `setup`
- `backend`
- `frontend`
- `content`
- `design`
- `forms`
- `seo`
- `accessibility`
- `performance`
- `security`
- `testing`
- `documentation`
- `blocked`
- priority labels `P0`, `P1`, and `P2`

Create milestones:

1. Foundation
2. Content Model
3. Frontend MVP
4. Quality and Content
5. Launch

Create the following issues. Each issue must contain context, scope, dependencies, implementation notes, acceptance criteria, tests, and a definition of done. Add appropriate labels and milestone.

1. **Project architecture and local development environment**
2. **CI pipeline, coding standards, linting, and automated checks**
3. **Theme foundation, design tokens, typography, and reusable layout primitives**
4. **Site functionality plugin and WordPress content types**
5. **Product fields, categories, relationships, validation, and admin experience**
6. **Project fields, taxonomies, galleries, relationships, and admin experience**
7. **FAQ, testimonial, and partner content models**
8. **Global company settings and owner permissions**
9. **Header, navigation, mobile menu, breadcrumbs, and footer**
10. **Homepage template and editable homepage sections**
11. **Company page template**
12. **Product archive, filters, cards, and empty states**
13. **Individual product template and related content**
14. **Projects archive, filters, cards, pagination/load more, and empty states**
15. **Individual project template and accessible gallery**
16. **Blog archive, article template, metadata, and related content**
17. **FAQ page and accessible accordion**
18. **Secure product-aware quote form with protected uploads**
19. **Contact page, short form, business information, map, and consent behavior**
20. **Mobile telephone and WhatsApp conversion actions**
21. **SEO metadata, sitemaps, redirects, canonical URLs, and structured data**
22. **Analytics events and consent management**
23. **Media pipeline and image performance**
24. **Accessibility audit and remediation**
25. **Performance audit and Core Web Vitals remediation**
26. **Security, SMTP, backups, update policy, and privacy controls**
27. **Cross-browser responsive QA and end-to-end form testing**
28. **Sample content, content migration guide, and client content checklist**
29. **Owner documentation and training guide**
30. **Production launch checklist and post-launch verification**

Link dependencies between issues where GitHub supports it. Create one tracking epic that links the entire backlog and reports progress.

Do not create thirty shallow placeholder issues. Write them so another engineer can implement each issue without rereading this prompt.

### Implementation rules

- Use a lightweight custom block theme or a justified equivalent.
- Put post types, taxonomies, fields, validation, and business behavior in a site functionality plugin, not in the theme.
- Use Gutenberg and locked patterns/templates for editable page content.
- Do not introduce Elementor, WPBakery, Divi, or another page builder unless the repository already depends on it and replacing it is outside scope.
- Use WordPress APIs and established coding standards.
- Prefix custom PHP functions, hooks, database keys, and globals consistently to avoid collisions.
- Escape output, sanitize input, check capabilities, use nonces, and validate uploads on the server.
- Make all visible French text translation-ready; do not hard-code content that belongs in the CMS.
- Prepare the theme for RTL without implementing Arabic content unless requested.
- Do not commit secrets, credentials, production customer data, premium plugin archives, generated uploads, dependencies, or environment files.
- Pin or constrain dependencies appropriately and document required licenses.
- Use original placeholders clearly labeled as placeholders when approved assets are missing.
- Do not invent MBA's years of experience, certifications, guarantees, partner relationships, locations, reviews, prices, performance values, or legal details.
- Avoid copying content or media from `alusystem.tn`.

### Design quality bar

Produce a distinctive architectural interface suitable for a premium aluminium specialist. It should feel precise, modern, spacious, and credible.

- Mobile-first and fully responsive.
- Strong visual hierarchy and restrained use of color.
- Original project photography should drive the final design.
- Use consistent image ratios and useful crop behavior.
- Provide polished loading, empty, error, success, focus, hover, and disabled states.
- Avoid a generic AI-generated appearance, excessive cards, decorative gradients, unnecessary sliders, excessive rounded containers, and animation without purpose.
- Meet WCAG 2.2 AA for the implemented scope.
- Preserve functionality at 200% zoom and with reduced motion enabled.

If there is no approved logo or visual identity, implement neutral design tokens that can be changed centrally. Do not make a permanent brand decision on the client's behalf.

### Forms and privacy quality bar

Implement the quote and contact flows specified in `PROJECT.md`.

- Validate in the browser for usability and again on the server for security.
- Protect against CSRF, spam, abuse, dangerous filenames, executable uploads, oversized files, and unsupported file types.
- Keep uploads private or otherwise access-controlled.
- Use authenticated SMTP configuration through environment variables.
- Provide successful, failed, pending, and duplicate-submission behavior.
- Never send personally identifiable form values to analytics.
- Provide configurable retention/deletion guidance for stored leads and uploads.
- Test email failure behavior; do not show a false success state if the request cannot be safely retained or delivered.

### SEO and performance quality bar

- Implement semantic HTML, one logical H1, editable metadata, canonical URLs, Open Graph data, sitemap behavior, breadcrumbs, and verified schema types.
- Do not output fabricated ratings, prices, or certifications in structured data.
- Use responsive images with dimensions, modern formats where supported, and correct lazy-loading priorities.
- Do not lazy-load the LCP hero image.
- Minimize JavaScript, third-party code, fonts, and plugins.
- Test representative templates with realistic image content and mobile conditions.

### Testing requirements

Build a testing strategy appropriate to the selected stack. At minimum include:

- PHP syntax/static analysis and WordPress coding standards
- JavaScript/CSS linting where applicable
- Automated checks for post type, taxonomy, field, permission, and form behavior
- End-to-end tests for navigation, filtering, quote submission, contact submission, and product-aware preselection
- Accessibility checks plus manual keyboard review
- Responsive browser checks at 360, 390/430, 768, 1024, and 1440 px
- Smoke tests for Chrome, Firefox, Safari/WebKit, and Edge/Chromium where the test environment permits
- Performance checks on the key templates listed in `PROJECT.md`
- No PHP warnings, browser console errors, broken internal links, or failing network requests in tested flows

Run tests relevant to each issue before closing it. Do not close an issue just because code was written.

### Execution workflow

1. Create or select one issue at a time in dependency order.
2. Create a focused branch named `issue-{number}-{short-name}`.
3. Implement the full issue scope.
4. Add or update meaningful tests.
5. Run the relevant test suite and record results.
6. Review the diff for security, accessibility, responsive behavior, and unnecessary complexity.
7. Commit with a clear message referencing the issue.
8. Open a pull request with summary, screenshots for UI changes, test evidence, risks, and `Closes #{number}`.
9. Do not merge into the default branch unless explicitly authorized.
10. Continue with independent work while a review or missing client asset does not block other issues.

If GitHub access is unavailable, follow the same workflow locally and create `docs/github-issues.md` containing ready-to-paste issue bodies. Use local branches and commits only when doing so is safe in the current repository.

### Documentation to maintain

Create and keep current:

- `README.md` — setup, requirements, commands, and project overview
- `docs/architecture.md` — architecture decisions and directory structure
- `docs/content-model.md` — fields, taxonomies, relationships, and admin usage
- `docs/forms-and-privacy.md` — flow, storage, delivery, retention, and security
- `docs/deployment.md` — staging/production deployment, environment variables, rollback
- `docs/testing.md` — automated and manual test procedures
- `docs/owner-guide.md` — how MBA updates every editable content type
- `.env.example` — names and explanations only; never real secrets

### Handling unknowns

Make reversible technical assumptions when they do not change the business scope. Record important assumptions in `docs/architecture.md`.

Use clearly labeled placeholder content for missing business information. Maintain a single `docs/client-input-required.md` checklist for missing logo files, contact details, product specifications, warranties, certifications, partner permissions, photographs, testimonials, legal details, and analytics/SMTP credentials.

Ask a focused question only when the answer would materially change architecture, cause irreversible work, publish externally, incur cost, or require credentials. Complete all non-blocked work first.

### Completion report

When the implementation is complete, report:

- Repository and branch/PR links
- Implemented features mapped to issues
- Test and audit results
- Local setup and deployment commands
- Required environment variables
- Remaining client content or credentials
- Known limitations and justified future work
- Confirmation against every item in the `PROJECT.md` Definition of Done

Do not claim completion while required tests fail, scoped features are missing, placeholders are presented as approved content, or the Definition of Done is not satisfied.

Begin now by inspecting the repository and reading both specification files. Then create the architecture decision and GitHub backlog before implementing the foundation.

---

## Recommended Usage

For best results:

1. Place this prompt, `PROJECT.md`, and `MBA_MENUISERIES_WEBSITE_SPEC.md` in the repository.
2. Give the coding agent access to the repository and terminal.
3. Authenticate GitHub CLI with `gh auth login` before starting if you want it to create issues and pull requests.
4. Tell the agent the GitHub owner, repository name, and visibility. Suggested name: `mba-menuiseries`.
5. Review and approve the architecture and issue backlog before allowing production deployment.
6. Provide real branding and content progressively; keep unknown claims as explicit placeholders.

The repository-and-issues workflow is recommended because it makes a large build reviewable, resumable, testable, and easier to hand to another developer. A single unrestricted “build everything” request makes omissions and regressions harder to detect.

