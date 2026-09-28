# Production launch and post-launch runbook

This is the release checklist for issue #30. The repository and CI can verify application behavior, but they cannot establish hosting, DNS, email delivery, legal approval, backups, monitoring, or Search Console access. A person responsible for each production service must complete the evidence fields below. Keep completed evidence in the private operations record; never put secrets, lead data, or recovery codes in Git.

**Current state:** no production host, domain, mail provider, backup target, monitoring contact, or explicit launch approval has been recorded in this repository. All production gates below therefore start unchecked. A green CI run is evidence for the code only; it is not a production release sign-off.

## 1. Name owners and record the release

- [ ] MBA content approver and final sign-off contact named
- [ ] Technical release owner and rollback authority named
- [ ] Hosting provider, support path, production domain, DNS owner, and target launch window recorded
- [ ] Production and staging URLs recorded; staging has access control and is excluded from search
- [ ] Release commit/tag, database backup ID, uploads backup ID, and private evidence folder recorded
- [ ] MBA explicitly approves launching the reviewed production version

| Role | Name/contact (store privately) | Confirmed on |
| --- | --- | --- |
| MBA content approver |  |  |
| Technical/release owner |  |  |
| Hosting/DNS support |  |  |
| Form enquiry recipient |  |  |
| Post-launch incident contact |  |  |

Do not put passwords, MFA recovery codes, API keys, private mailboxes, or personal lead details in this file.

## 2. Production platform and recovery

- [ ] Hosting supports the selected WordPress/PHP/MariaDB versions, HTTPS, scheduled tasks, image processing, sufficient upload limits, and the required rewrite rules
- [ ] Production is a distinct environment from local and staging; production secrets are stored in the host's secret manager
- [ ] HTTPS certificate is active for the apex/www hostnames that will be used; renewal is monitored; HTTP redirects to the canonical HTTPS host
- [ ] WordPress salts and database/admin credentials are unique production secrets; dashboard file editing is disabled
- [ ] MFA is enabled for hosting, DNS, and privileged WordPress accounts; every staff member has an individual least-privilege account
- [ ] Core, PHP, theme, plugin, and database update owner/cadence/emergency patch procedure recorded
- [ ] Database and uploads are backed up automatically to a separate protected location; retention and encryption are confirmed
- [ ] A full backup has been restored to a non-production environment; record the restore date, duration, and result
- [ ] Recovery-point and recovery-time targets are agreed; only the technical release owner can authorize a production restore
- [ ] Uptime, PHP/WordPress errors, disk/database capacity, certificate expiry, scheduled tasks, and backup failures alert a named owner
- [ ] Caching/CDN and image optimization are configured; forms, admin pages, private lead routes, and protected uploads bypass public caches

Use [`security-operations.md`](security-operations.md) for the detailed security, retention, and recovery controls. Do not mark a backup as verified merely because a backup job says “success”; restore it and inspect both database records and uploaded files.

## 3. Approved content, privacy, and accessibility

- [ ] MBA approves every public product/service, technical value, warranty, price, certification, partner mark, contact detail, service area, response promise, and date
- [ ] No seeded sample record/marker, example page text, fabricated testimonial, competitor copy/media, unlicensed asset, or unverified claim remains
- [ ] Every project photo/story has documented client/property/person permission; only city/region appears publicly unless a more precise address is expressly approved
- [ ] Testimonials use exact approved wording and attribution; publication consent is recorded and a withdrawal contact is known
- [ ] Image and document credits, captions, alt text, licenses, source records, and permitted use are reviewed; private consent evidence is stored outside public media fields
- [ ] Public Privacy Policy and legal notice are approved and accurately describe forms, uploads, retention, analytics, maps, and contact channels
- [ ] Privacy/data retention and erasure procedures have an owner; a request can be completed within the approved policy
- [ ] Keyboard, zoom/reflow, focus, form labels/errors, reduced motion, image alternatives, and representative screen-reader use pass manual review
- [ ] Owner has completed the training and handover checklist in [`owner-guide.md`](owner-guide.md)

Track source approval through [`client-input-required.md`](client-input-required.md). Do not use synthetic development entries as launch content.

## 4. Email, forms, uploads, and consent

- [ ] Production sender domain and transactional mail provider are approved; SPF, DKIM, and DMARC records are verified by the mail owner
- [ ] Authenticated SMTP credentials are configured as secrets; sender/from and reply-to addresses use approved domains
- [ ] Test quote and contact submissions reach the intended MBA inbox; customer acknowledgements arrive only when an address is supplied and approved
- [ ] Rejected uploads, maximum size/count, spam/rate limits, duplicate submissions, validation, and mail/network failure produce safe messages
- [ ] A test lead and its attachment are visible only to authorized administrators; an Editor and logged-out visitor cannot fetch the private file
- [ ] Lead retention, attachment deletion, privacy export/erasure, and mailbox handling are verified on production or an equivalent staging copy
- [ ] Call, WhatsApp, email, directions, and map actions use the approved production details and work on a mobile device
- [ ] Non-essential analytics and embedded map requests remain blocked until the visitor makes the required consent choice; reject/revoke behavior is verified
- [ ] Production analytics ID is distinct from staging; no names, email/phone numbers, messages, upload names, query strings, or raw link targets are sent
- [ ] GA4 Enhanced Measurement options that automatically record outbound clicks/file downloads are disabled when using the site’s redacted events

The isolated end-to-end suite substitutes email and cannot prove live delivery, SPF/DKIM/DMARC alignment, inbox placement, human spam levels, or provider outage alerts. Capture production/staging evidence for those checks.

## 5. Search, domain, and release checks

Complete the domain cutover only after sections 1–4 are signed:

1. Lower DNS TTL in advance if the DNS owner and host recommend it. Confirm the current site and rollback target before editing records.
2. Deploy the reviewed commit to staging first. Restore a current production-like backup, import only approved content/media, verify URL/relationship mapping, and complete the staging acceptance below.
3. Confirm staging is blocked by authentication or an equivalent access control and has `noindex`; do not rely on `robots.txt` alone to hide confidential staging content.
4. Back up production immediately before cutover and record the backup IDs. Deploy the same reviewed release and approved content; do not seed development samples.
5. Set the canonical site URL, HTTPS redirects, timezone, permalink structure, and production email. Verify both apex/www behavior and representative old-URL redirects.
6. Check the production robots setting: production pages are indexable, staging remains blocked, and private leads, confirmations, attachment views, internal searches, and inappropriate filtered pages remain excluded.
7. Inspect canonical tags, titles/descriptions, Open Graph data, business schema, and the XML sitemap. Submit the production sitemap in Google Search Console using an authorized MBA account and capture the submission evidence.
8. Run smoke and critical journey checks over the public production domain. Confirm lead email, private file protection, consent, analytics, phone/WhatsApp links, mobile layout, and error monitoring.
9. Record DNS values, deploy time, commit, backup IDs, test results, Search Console result, approver, and any accepted limitation in the private release record.

## 6. Acceptance evidence mapped to `PROJECT.md`

Record a production or staging URL, result, date, and evidence reference for every row. Repository CI and documentation links are supporting evidence, not substitutes for the live checks.

| `PROJECT.md` Definition of Done | Required evidence before launch | Status / evidence / date |
| --- | --- | --- |
| Scoped pages/templates contain approved content | MBA content approval; no placeholders, competitor material, unlicensed images, private address, or unverified claim |  |
| Owner edits business content without code | Completed Editor training; product/project/FAQ/photo/article update previewed and restored if needed |  |
| Global contact details update everywhere | Change approved setting on staging; verify header/footer/contact/form/mobile actions, then check production |  |
| Product/project relationships and filters work | Test category/type/location/product filters and links against migrated approved records |  |
| Product/project quote links preselect and record the source | Submit one product-sourced and one project-sourced request; verify private lead fields |  |
| Quote/contact forms validate, save, and notify | Production-domain submissions, private lead records, recipient/customer mail, invalid and failure paths |  |
| Upload validation/privacy controls work | Allowed/rejected file matrix, size/count limits, private-file access tests for Admin/Editor/visitor |  |
| Phone, WhatsApp, email, directions work on mobile | Device/browser test with approved real destination values |  |
| Navigation, accordions, filters, galleries, forms work by keyboard | Manual keyboard and assistive-technology record for each critical control |  |
| Key templates pass Chrome, Safari, Firefox, Edge | Browser/OS/device versions, sizes, screenshots/report, and resolved defect list |  |
| No placeholders, broken links, unauthorized images, or unverified claims | Full-site crawl plus MBA editorial/rights sign-off |  |
| Sitemap, metadata, canonicals, redirects, schema, analytics, Search Console configured | URL samples, structured-data review, redirect tests, consent evidence, Search Console sitemap receipt |  |
| SMTP, backups, security, caching, consent, uptime monitoring active | Delivery/DNS records, successful restore, MFA/account review, cache/privacy test, alert test |  |
| Production indexable; staging blocked | Production robots/meta/header result plus staging authentication and noindex result |  |
| Owner receives credentials, documentation, and training | Private credential-vault handover receipt, guide review, sign-off names/date |  |

The development evidence currently available is the automated quality suite, including the isolated cross-browser run and application-level checks documented in [`testing.md`](testing.md). This does not check off production rows above.

## 7. Rollback and incident response

Before deployment, the release owner records the previous release, compatible database/uploads backup pair, DNS values/TTL, host rollback command or support path, and person authorized to act. If the site is unsafe or critical journeys fail:

1. Record the time, symptom, affected URL, and release identifier without copying personal enquiry data into public tickets.
2. Pause further publishing and, if needed, disable the affected form or route through the hosting control plane. Keep a privacy-safe incident record.
3. For a code-only regression, redeploy the last known-good release. For data damage, restore the matching database and uploads backup together to a separate recovery environment first, validate it, then have the authorized owner approve the production restore.
4. Recheck HTTPS, forms/mail, protected files, content/relationships, consent, indexing, and monitoring after rollback. Restore DNS only with the DNS owner's agreement; DNS rollback may take time to propagate.
5. Notify the named MBA and technical contacts, preserve relevant logs securely, and document cause, data exposure assessment, actions, and next owner.

Never overwrite the only production copy during investigation. Follow the host's incident and personal-data breach procedure where applicable.

## 8. Post-launch schedule

**Immediately after cutover:** technical owner checks uptime, error logs, backups, scheduled tasks, form notifications, protected uploads, consent and analytics behavior, and Search Console coverage. MBA checks calls/messages and confirms enquiry ownership.

**After 24 hours and after 7 days:** repeat public critical journeys; review mail delivery, errors, broken links, backup completion, uptime alerts, and indexation. Record defects and assign owners/dates. Review real user traffic only through the consented analytics configuration.

**Ongoing:** review core/plugin/PHP updates, privileged accounts/MFA, backups and restore evidence, lead retention, media/testimonial consent, contact details, product claims, certificates, sitemap/index coverage, and monitoring contacts on the cadence agreed with MBA and hosting.

## 9. Final approval record

| Approval | Name | Version/commit | Date/time and timezone | Evidence reference |
| --- | --- | --- | --- | --- |
| MBA content and launch approval |  |  |  |  |
| Technical release approval |  |  |  |  |
| Backup restore verified by |  |  |  |  |
| Mail/form acceptance verified by |  |  |  |  |
| DNS/Search Console verified by |  |  |  |  |
