# MBA Menuiseries Belhaj Ali — Development Specification

## 1. Project Summary

Build a fast, mobile-first WordPress website for **MBA Menuiseries Belhaj Ali**, a Tunisian company specializing in aluminium fabrication and installation.

The website has two main purposes:

1. Present MBA's products and completed work professionally.
2. Generate qualified enquiries through quote forms, telephone, and WhatsApp.

The owner must be able to update products, projects, photos, FAQs, articles, testimonials, partners, and company details without changing templates or writing code.

**Primary language:** French  
**Future languages:** Arabic and English  
**Website type:** Corporate showcase, portfolio, and lead generation  
**CMS:** WordPress  
**E-commerce:** Not included in the MVP

## 2. MVP Scope

### Public pages

- Homepage (`/`)
- Company (`/entreprise/`)
- Products archive (`/produits/`)
- Individual product pages (`/produits/{slug}/`)
- Projects archive (`/realisations/`)
- Individual project pages (`/realisations/{slug}/`)
- Blog archive (`/conseils/`)
- Individual articles (`/conseils/{slug}/`)
- FAQ (`/faq/`)
- Quote request (`/devis/`)
- Contact (`/contact/`)
- Privacy policy (`/politique-de-confidentialite/`)
- Legal notice (`/mentions-legales/`)
- Custom 404 page

### Global features

- Responsive header and footer
- Sticky desktop/mobile navigation
- Product navigation dropdown
- Persistent mobile call and WhatsApp actions
- Main `Demander un devis` CTA
- Breadcrumbs on internal pages
- Reusable CTA sections
- Editable global company information
- Cookie consent when non-essential tracking is enabled
- Search-engine sitemap and metadata
- Accessible forms and navigation

## 3. Navigation

```text
Accueil
L'entreprise
Produits
  Fenêtres et portes battantes
  Baies et portes coulissantes
  Portes d'entrée
  Volets roulants
  Moustiquaires
  Garde-corps
  Verrières et cloisons vitrées
  Vérandas et pergolas
  Façades et murs-rideaux
Réalisations
Conseils
FAQ
Contact
[Demander un devis]
```

Only products actually offered by MBA should be published.

## 4. Page Requirements

### 4.1 Homepage

Display these sections in this order:

1. Hero image/video, H1, short value proposition, quote CTA, projects CTA
2. Trust highlights: experience, made-to-measure work, installation, service area
3. Featured products selected in WordPress
4. Company introduction and link to company page
5. Reasons to choose MBA
6. Featured projects selected in WordPress
7. Project process: consultation, measurement, quotation, fabrication, installation
8. Available materials, colors, glazing, or finishes
9. Testimonials
10. Partners and certifications
11. Three latest advice articles
12. Final quote, WhatsApp, and telephone CTA

All homepage headings, text, media, selections, and CTA labels must be editable.

### 4.2 Company page

- Company history
- Founder/team presentation
- Values and quality approach
- Workshop and team gallery
- Services: advice, measurement, fabrication, installation, after-sales support
- Certifications and partners
- Real service zones
- Featured projects
- Quote CTA

### 4.3 Products archive

- Page introduction
- Product category filters
- Responsive product-card grid
- Card image, name, excerpt, category, and link
- Empty-state message if a filter has no results
- Quote CTA

Filtering may use normal page reloads for the MVP. JavaScript/AJAX filtering is optional.

### 4.4 Product detail

- Hero/cover image
- Name, category, and short introduction
- Benefits
- Opening/configuration types
- Materials and profile systems
- Glass options
- Colors and finishes
- Verified performance information
- Recommended applications
- Maintenance information
- Image gallery/lightbox
- Optional downloadable brochure or technical sheet
- Related projects
- Related FAQs
- Related products
- Product-aware quote CTA

The quote link must pass the product ID or slug to the quote form and preselect it.

### 4.5 Projects archive

- Visual project grid
- Filters: product category, project type, and location
- Featured projects
- Card image, project name, general location, year, and installed products
- Pagination or `Load more`

### 4.6 Project detail

- Project name and cover image
- General location; never require a private street address
- Completion month/year
- Residential/commercial/industrial project type
- Products installed
- Project challenge
- MBA solution
- Result
- Materials, profiles, glazing, colors, and options
- Before/during/after image gallery
- Optional testimonial
- Related products and projects
- `Vous avez un projet similaire ?` CTA

### 4.7 Blog

- Article archive with category filters and pagination
- Article cover, title, excerpt, author/reviewer, published date, updated date
- Related products, projects, and articles
- Contextual quote CTA
- Social share metadata

### 4.8 FAQ

- FAQ category navigation
- Accessible accordion interface
- Questions editable and reorderable in WordPress
- Product-specific FAQs automatically shown on related product pages
- Only add FAQ schema when the visible content is eligible

### 4.9 Quote page

Form fields:

| Field | Type | Required |
|---|---|---:|
| Full name | Text | Yes |
| Telephone | Tel | Yes |
| Email | Email | Configurable |
| City/governorate | Text or select | Yes |
| Customer type | Select | Yes |
| Project type | Select | Yes |
| Products | Multi-select | Yes |
| Approximate quantity/dimensions | Text | No |
| Desired timeframe | Select | No |
| Message | Textarea | Yes |
| Photos/plans | File upload | No |
| Preferred contact method | Radio | Yes |
| Privacy consent | Checkbox | Yes |
| Source product/project | Hidden | Automatic |

Form behavior:

- Validate fields on the client and server.
- Accept only approved document/image extensions.
- Set a configurable maximum file count and file size.
- Rename uploaded files safely and prevent script execution.
- Send the lead to MBA by email through authenticated SMTP.
- Save a private lead entry in WordPress or the selected CRM.
- Send an acknowledgement email to the customer when an email is supplied.
- Show a confirmation page/message with expected response time.
- Record landing page, source page, and UTM values where consent permits.
- Include spam protection, rate limiting, and a honeypot or equivalent.
- Do not expose uploads through easily guessed public URLs.

### 4.10 Contact page

- Phone, WhatsApp, email, address, and opening hours
- Google Maps embed loaded according to consent configuration
- External directions link that works without loading the embed
- Short contact form
- Service areas
- Workshop/showroom visit information
- Social profiles

## 5. WordPress Data Model

### 5.1 Custom post type: `product`

**Supports:** title, editor, excerpt, featured image, revisions  
**Archive:** `/produits/`

Custom fields:

- `short_description`
- `benefits` — repeater
- `configurations` — repeater
- `materials_profiles` — rich text
- `glazing_options` — repeater
- `colors_finishes` — repeater/image
- `performance_details` — repeater with label/value
- `applications` — repeater
- `maintenance`
- `gallery` — multiple images
- `technical_document` — PDF
- `featured` — boolean
- `display_order` — integer
- `related_faqs` — relationship

Taxonomies:

- `product_category`
- `application_type` — optional

### 5.2 Custom post type: `project`

**Supports:** title, editor, excerpt, featured image, revisions  
**Archive:** `/realisations/`

Custom fields:

- `location_city`
- `location_region`
- `completion_date`
- `project_type`
- `challenge`
- `solution`
- `result`
- `materials`
- `profiles`
- `glazing`
- `colors`
- `gallery_before`
- `gallery_during`
- `gallery_after`
- `testimonial` — relationship
- `featured` — boolean
- `display_order` — integer
- `related_products` — relationship

Taxonomies:

- `project_type`: residential, commercial, industrial, other
- `project_location`
- Reuse or relate `product_category`

### 5.3 Custom post type: `faq`

Fields:

- Title = question
- `answer` — rich text
- `faq_category`
- `related_products` — relationship
- `display_order` — integer

### 5.4 Custom post type: `testimonial`

Fields:

- `customer_name_or_label`
- `company` — optional
- `location`
- `review`
- `rating` — optional, 1–5
- `customer_photo` — optional
- `related_project` — optional
- `publication_consent_confirmed` — boolean
- `featured` — boolean

### 5.5 Custom post type: `partner`

Fields:

- Partner name
- Logo
- Website URL
- Description
- `display_order`
- `featured`

### 5.6 Global site settings

Create one options/settings screen containing:

- Legal business name
- Short company description
- Logo and favicon
- Main phone and secondary phone
- WhatsApp number and default message
- Enquiry email
- Address
- Google Maps URL/embed configuration
- Opening hours
- Service areas
- Facebook, Instagram, LinkedIn, TikTok, and YouTube URLs
- Default quote response time
- Footer content
- Default CTA labels
- Partner/certification disclaimer if needed

Changing a global value must update it everywhere it is displayed.

## 6. WordPress Administration

- Use Gutenberg for standard pages and articles.
- Use locked patterns/templates for layout-sensitive pages.
- Use structured fields for products, projects, FAQs, testimonials, and partners.
- Allow admins to mark products/projects as featured and change their order.
- Display image ratio, dimensions, and file-size guidance near upload fields.
- Preserve post revisions.
- Owner receives an `Editor` account with only the permissions needed.
- Administrator access is reserved for technical maintenance.
- Do not require the owner to edit PHP, CSS, shortcodes, or complex page-builder rows.

## 7. Components

Create reusable components/blocks for:

- Header
- Desktop navigation and mobile drawer
- Footer
- Hero
- Breadcrumbs
- Section heading
- Product card/grid
- Project card/grid
- Filter controls
- Image gallery/lightbox
- Benefit/specification list
- Process steps
- Testimonial card/slider
- Partner logo grid
- FAQ accordion
- Article card
- Related-content section
- Quote CTA
- WhatsApp button
- Mobile call bar
- Forms, validation, errors, success messages, and loading states
- Empty state
- Pagination or load-more control

## 8. Design Requirements

- Modern architectural visual style
- Original MBA project photos prioritized over stock imagery
- Brand colors based on the approved MBA logo
- Strong typography and generous whitespace
- Consistent product and project image ratios
- Minimum body text size of approximately 16 px
- Touch targets of at least approximately 44 × 44 px
- Visible keyboard focus styles
- Sufficient WCAG AA color contrast
- Reduced-motion support
- No autoplay audio
- Avoid unnecessary animation and sliders

Required responsive checks:

- 360 px mobile
- 390/430 px mobile
- 768 px tablet
- 1024 px laptop/tablet landscape
- 1440 px desktop

## 9. SEO Requirements

- Editable SEO title and meta description for all indexable content
- One clear H1 per page
- Clean permanent URLs
- Canonical tags
- XML sitemap
- Robots configuration
- Open Graph and social share images
- Breadcrumb structured data
- Organization/LocalBusiness structured data using verified MBA details
- Article structured data on blog posts
- Relevant Product or Service structured data without invented prices/reviews
- Redirect management
- Image alt-text fields and guidance
- Internal links among products, projects, FAQs, and articles
- Google Search Console setup at launch
- No indexation of staging, form confirmations, private leads, attachment pages, or internal search results where inappropriate

## 10. Performance Requirements

- Mobile-first implementation
- Responsive `srcset`/`sizes` images
- WebP/AVIF when supported
- Lazy-load below-the-fold images and embeds
- Do not lazy-load the primary hero/LCP image
- Local or privacy-compliant font delivery with limited weights
- Page caching and compression
- Minimize plugins and third-party scripts
- Prevent layout shift by reserving media dimensions
- Defer non-critical JavaScript
- Performance test the homepage, products archive, product page, projects archive, and an image-heavy project page
- Target good Core Web Vitals under realistic mobile conditions

## 11. Accessibility Requirements

- Semantic headings and landmarks
- Keyboard-operable menus, filters, lightbox, accordions, and forms
- Skip-to-content link
- Labeled form controls and understandable errors
- Error summary or focus movement after failed submission
- Screen-reader-friendly status messages
- Meaningful alternative text for informative images
- Decorative images ignored by assistive technology
- No interaction that depends only on hover
- Respect browser zoom and text resizing

## 12. Security and Privacy

- HTTPS-only production site
- Strong admin passwords and two-factor authentication
- Least-privilege user roles
- Login attempt protection/rate limiting
- Daily database backups and regular full-site backups stored off-server
- Staging environment and rollback process
- WordPress, theme, and plugin update policy
- Form nonce/CSRF protection
- Sanitization on input and escaping on output
- Strict file upload validation
- SMTP with SPF, DKIM, and DMARC configured on the sending domain
- Privacy policy covering forms, uploads, analytics, maps, and WhatsApp
- Configurable lead/upload retention period
- Consent management before non-essential tracking or map cookies
- Publication consent for testimonials and private-property project photos

## 13. Analytics and Events

Configure privacy-compliant analytics and track:

- `quote_form_start`
- `quote_form_submit`
- `quote_form_error`
- `contact_form_submit`
- `phone_click`
- `whatsapp_click`
- `email_click`
- `directions_click`
- `brochure_download`
- Product and project page views

Do not send names, phone numbers, emails, messages, or uploaded filenames to analytics.

## 14. Suggested Implementation

- Supported WordPress and PHP versions available at development/launch time
- Lightweight custom block theme or carefully selected block theme
- Gutenberg and reusable patterns
- Custom post types and taxonomies registered in a site functionality plugin
- Structured fields implemented with a maintained field solution or native APIs
- Form solution supporting protected uploads, saved entries, notifications, and spam controls
- SEO plugin
- Caching/performance solution
- Backup and security solution
- SMTP/transactional email provider
- Development, staging, and production environments

Exact plugins should be chosen after checking hosting compatibility, licensing, update history, accessibility, and whether the feature is actually needed.

## 15. Development Milestones

### Phase 1 — Discovery and setup

- Confirm products, service zones, languages, brand assets, and contact workflow
- Confirm hosting and domain
- Set up Git repository, local environment, staging, and deployment process
- Install/configure WordPress and base theme
- Define design tokens and content model

### Phase 2 — Backend/content model

- Register post types and taxonomies
- Create custom fields and global settings
- Configure roles and permissions
- Build form workflow and email delivery
- Add sample content for development

### Phase 3 — Frontend

- Build global components
- Build all archives and single templates
- Build static pages
- Add filters, galleries, CTAs, and responsive behavior
- Implement empty, loading, error, and success states

### Phase 4 — Content and optimization

- Import approved French content and images
- Optimize media
- Configure SEO and structured data
- Configure analytics, consent, security, caching, backups, and SMTP

### Phase 5 — Testing and launch

- Functional, responsive, browser, accessibility, and performance testing
- Test every form and notification path
- Content/legal review with MBA
- Owner training
- Production launch and indexing checks

## 16. Definition of Done

The MVP is complete when:

- All scoped pages and templates exist and contain approved content.
- The owner can add/edit products, projects, photos, FAQs, testimonials, partners, and articles without developer help.
- Global contact information updates everywhere from one settings screen.
- Product/project relationships and filters work correctly.
- Product/project quote links preselect and record the relevant item.
- Quote and contact forms validate, submit, save, and send notifications successfully.
- Upload validation and privacy controls are verified.
- Telephone, WhatsApp, email, and directions links work on mobile.
- Navigation, accordions, filters, galleries, and forms work with keyboard input.
- Key templates pass responsive checks in current Chrome, Safari, Firefox, and Edge.
- No placeholder content, broken links, unauthorized images, or unverified claims remain.
- Sitemap, metadata, canonical URLs, redirects, structured data, analytics, and Search Console are configured.
- SMTP, backups, security, caching, consent, and uptime monitoring are active.
- Production is indexable while staging remains blocked from indexing.
- The owner receives credentials, documentation, and training.

## 17. Content Needed Before Launch

- Logo, favicon, brand colors, and fonts
- Legal company name and registration details
- Address, phone, WhatsApp, email, hours, and service zones
- Company story, experience, team, and workshop information
- Final product/service list
- Verified product specifications, available options, warranties, and maintenance advice
- Certifications/partner evidence and logo-use permission
- Original photos grouped by project
- Project location, date, type, products, challenge, solution, and result
- Testimonials and publication consent
- Social account links
- Quote response time and receiving email
- Privacy/legal information
- Approved French copy

## 18. Out of Scope for MVP

- Online payments or WooCommerce store
- Customer accounts
- Live product configurator
- Instant binding price calculation
- ERP/accounting integration
- Automated WhatsApp API conversations
- Native mobile application
- Advanced CRM automation
- Arabic/English content entry unless separately approved

These can be planned as later phases without changing the core product/project structure.

