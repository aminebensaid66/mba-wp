# MBA website owner guide

This guide covers routine editing in the MBA Menuiseries WordPress site. The site content is written in approved French; the wp-admin labels below match the installed English interface. If a screen or field is missing, ask the site administrator before installing a plugin or changing theme code.

## Before editing

1. Sign in at the private `/wp-admin/` address supplied by the site administrator. Use your own account; do not share passwords. Editors can manage pages and MBA content settings, while only an Administrator can install plugins or change site-wide technical settings.
2. Open the relevant menu in the left sidebar: **Products**, **Projects**, **FAQs**, **Testimonials**, **Partners**, **Posts**, or **Pages**. **Settings → MBA Settings** contains shared company details.
3. Keep a source of truth for approved copy, image rights, technical documents, and consent records. The blank intake checklist is in [`client-input-required.md`](client-input-required.md); completed records containing personal data belong in MBA’s approved private storage, not Git or public media descriptions.
4. Prefer **Save draft** while editing. Use **Preview** to check the public layout at desktop and mobile widths before publication. Check every link and the French accents in the rendered preview.

## Shared company details

Go to **Settings → MBA Settings**. This screen is a single long form; save with **Save Changes** at the bottom. These values feed shared areas of the site, so update them once here rather than typing a second copy into a page.

- Enter only details MBA has confirmed: legal name, public contact numbers, email, public address, hours, service areas, social links, company history/team, services, certifications, quote response time, homepage copy, SEO defaults, and footer content.
- Phone numbers need a country code such as `+216`; do not guess one. Leave unconfirmed hours, service coverage, response promises, or certifications blank. Empty optional settings are omitted from the public page.
- **Company logo**, **Homepage hero image**, **Homepage introduction image**, **Homepage process image**, **Homepage materials image**, and **Default social share image** use the Media Library chooser. Choose an existing approved image or upload one there first.
- **Favicon** must be square and at least 512 × 512 px. Saving it also updates the native WordPress site icon.
- **Workshop/team gallery** uses **Choose gallery images**. Its order follows the selection order; if you need a different order, remove and select the images in the intended sequence. Add each image’s caption and alt text in the Media Library.
- **Map directions URL** is a normal link. **Google Maps embed URL** expects the Google Maps HTTPS embed URL itself, not copied iframe markup.
- **Require email for quote requests** changes whether the public quote form accepts phone-only requests. Change it only after confirming the decision with the person receiving leads.
- Analytics IDs and permanent redirects are technical settings. Have the site administrator verify consent, the destination path, and the analytics property before saving them.

After saving, open the homepage, contact page, and footer in a new tab. Confirm shared details appear once, links open the correct destination, and no old number or unapproved promise remains.

## Products

1. Open **Products → Add New**. Add the approved product name as the title, and use the main editor for a clear description. The structured **Product details** panel is below the editor; the normal sidebar holds the product category/application terms and **Featured image**.
2. Add a short description, benefits, configurations, glazing options, recommended applications, materials/profile systems, colors/finishes, and maintenance guidance only when the product owner has verified them. Use **Add row**, **Add finish**, or **Add performance row** for repeatable fields. A performance row needs both a label and a verified value with its units/source; do not enter estimates.
3. Set a landscape **Featured image**. In **Product details → Gallery**, select approved images with **Choose gallery images**. Drag thumbnails to set their order; use the × control to remove an image. Removing it from this product does not delete the Media Library file.
4. Use **Choose PDF** for an approved, current technical document. The field accepts PDFs only. Remove the link with **Remove** if the document is withdrawn or superseded.
5. Select **Related FAQs** and **Related projects**. The relationship is maintained on the other record too. Check the linked pages after saving.
6. Set **Feature this product** only when the content owner wants it in the homepage’s featured-products section. There, a lower **Display order** value appears earlier. The public Products archive is ordered by publication date, so this field does not reorder that archive.
7. Save as a draft, preview the product page, and verify the copy, image order, PDF, links, and mobile layout before publishing.

## Projects

1. Open **Projects → Add New**. Use the title and main editor for the approved case-study story. In the sidebar, choose the **Project Type**, **Project Location**, and product-category terms used by the archive filters.
2. In **Project details**, add **City**, **Region**, and **Completion date** only when confirmed. The project date is separate from the WordPress publication date. Never enter a client’s private street address.
3. Complete **Challenge**, **Solution**, and **Result** only from facts approved by MBA and the client. **Materials**, **Profiles**, **Glazing**, and **Colors** are optional.
4. Add the approved images under **Before photos**, **During photos**, and **After photos**. Each section has its own **Choose photos** button. Drag within a section to reorder; use × to remove an image from that section. Set captions, alt text, and crop focus in the Media Library.
5. Select **Installed products** and, if approved, **Related testimonial**. Feature/order controls work like those on Products.
6. **Feature this project** also moves it ahead of non-featured items in the public Projects archive. On the homepage’s featured-project section, a lower **Display order** value appears earlier. Preview the project and confirm that the location reveals no private address, each caption describes the right image, and every identifiable person/property is cleared for publication.

## FAQs, testimonials, and partners

- **FAQ:** Open **FAQs → Add New**. Put the customer’s question in the title and the answer in the main editor. Choose an **FAQ Category** in the sidebar. In **Entry details**, select **Related products** and set optional display order. Use wording verified by the product owner and review answers when a product specification changes.
- **Testimonial:** Open **Testimonials → Add New**. Paste the exact approved review into the main editor. In **Entry details**, fill **Customer name or approved public label**, **Company**, **General location**, **Customer rating (optional, 1–5)**, and **Related project** only where consent covers those details. Select **Customer photo** only with separate image permission. Tick **Publication consent confirmed** before **Feature this entry**; the feature control is disabled without consent. Withdrawing consent unfeatures the review. Keep the consent evidence in MBA’s private records.
- **Partner:** Open **Partners → Add New**. Use the partner name as the title and an approved description in the main editor. In **Entry details**, select **Partner logo**, enter a **Website URL (HTTP/HTTPS)** if approved, and optionally set display order or **Feature this entry**. Confirm permission for every logo and certification mark.

Save a draft first and preview it. Never write a sample quote, invented rating, implied partnership, or certification that MBA has not approved.

## Advice articles and pages

- **Posts → Add New** creates an advice article. Add the approved French title and article in the editor, set its category and **Featured image** in the sidebar, and fill the excerpt if the preview needs a shorter summary. Use Preview and publish only after the author or technical reviewer approves the advice.
- **Pages → All Pages** contains contact, quote, company, FAQ, legal, and other pages. Many are rendered by dedicated MBA blocks/templates. Edit the intended text or page fields, but do not remove the main MBA page block or rearrange a locked homepage composition without the administrator’s help. Check the actual public preview before saving structural changes.
- To create a new article or content record, choose **Add New** from its own menu. Do not reuse an unrelated published page as a draft.

## Images, captions, alt text, and files

The Media Library stores shared files. Upload the original and let WordPress make responsive sizes; do not upload a screenshot of a photograph or repeatedly resize/recompress the same file.

| Use | Recommended source size | File guidance |
| --- | --- | --- |
| Product/project cover or gallery photo | JPEG or WebP, about 1,800–2,400 px on the long edge; provide both landscape and portrait where available | Aim for 300 KB–1.5 MB per web image; keep the camera original privately |
| Logo | Transparent PNG or WebP, at least 600 px wide; preserve its original proportions | Under 200 KB where practical |
| Favicon | Square image, at least 512 × 512 px | PNG or WebP; select it in **MBA Settings → Favicon** |
| Technical document | Current, accessible PDF supplied or approved by MBA | Upload only the approved public version |

These are editorial targets, not application-enforced limits. The upload dialog shows the server’s actual maximum file size. If a legitimate original is larger, ask the administrator to adjust the server limit rather than splitting or degrading it.

To upload, go to **Media → Add New** or choose an image in an editor and use **Upload files**. Open the attachment details and enter:

- **Alt Text:** describe the information the image adds. Example: `Fenêtre coulissante en aluminium installée dans un salon lumineux à Sousse.` Use a neutral factual description, not a list of search terms. For an image that adds no information, leave alt text empty if the editing screen allows it.
- **Caption:** add a short visible credit or context, with permission. Example: `Projet publié avec l’accord du client.` Do not put private names, addresses, consent records, or confidential notes in public caption/description fields.
- **Title/Description:** use a useful library label and a short, factual note when it helps MBA find the file later. Public display depends on the page design; assume captions and alt text may be public.

For a replacement photo, upload/select the new file first, replace the **Featured image** or gallery entry, save, and verify the public page. Only then consider deleting the old Media Library item, and first check it is not used on another page, product, project, logo, or setting. Removing a gallery thumbnail only unlinks it from that gallery. A linked product PDF can be replaced with **Choose PDF** or cleared with **Remove**; confirm the new file opens before publishing.

## Draft, preview, publish, and recover

1. **Save draft** while work or approval is incomplete. A draft is not shown in the public archive.
2. Use **Preview** to inspect desktop and mobile layouts. In the preview, test all links, gallery order, French text, contact actions, and documents. Check image crops and alt text as well as the layout.
3. Before publishing, confirm content accuracy, owner approval, image/document rights, testimonial consent, public location privacy, related records, and the scheduled/publication date. Click **Publish** and confirm the publish prompt. For an existing page use **Update**; use **Preview changes** if WordPress offers it.
4. To correct published text, edit the existing item and click **Update**. If the post type has revisions, open **Revisions**, compare versions, and restore the intended title/body/excerpt version. Preview again after restoring.
5. Revisions do not replace a database/uploads backup. They may not restore custom product/project fields, relationships, image files, or **MBA Settings**. Before removing media or making broad settings changes, ask the administrator to confirm a recent restorable backup. The administrator owns backup scheduling and restoration; the host’s backup plan is documented in [`security-operations.md`](security-operations.md).
6. If a page should no longer be public, change it to **Draft** or **Private** as agreed, and check whether old search/bookmark URLs need a redirect. Do not permanently delete a post or media item until its links and relationships have been reviewed.

## Owner responsibilities

MBA’s named content owner approves product specifications, claims, pricing/warranties, service areas, response promises, company details, and publication dates. The project owner obtains client permission and confirms that people, properties, photos, captions, and results may be published. The testimonial owner keeps consent evidence and removes or anonymizes content promptly when consent is withdrawn. The media owner tracks image/document rights and expiry. Review time-sensitive pages whenever products, policies, staff, contact details, or source documents change, and schedule a regular catalogue and broken-link review.

The site administrator owns accounts/roles, software updates, hosting, backups, restore tests, email delivery, analytics/privacy configuration, and technical incidents. Editors must not put passwords, production keys, private lead details, or consent records in page text or Git. Report a lost account, accidental public disclosure, broken form, missing image, or suspected compromise to the administrator promptly.

## Training and handover checklist

Complete these steps together on staging or with clearly labeled draft content:

- [ ] Sign in with the owner’s own Editor account and identify the administrator contact
- [ ] Update one confirmed item in **MBA Settings**, save, and check the homepage/contact/footer output
- [ ] Create a draft product; add a cover, gallery, category, PDF, and related FAQ/project; reorder and remove one gallery item
- [ ] Create a draft project; fill only approved fields; use each before/during/after gallery; verify product links
- [ ] Create a draft FAQ and set its category and related product
- [ ] Review the consent gate on a draft testimonial and the logo permission fields on a draft partner
- [ ] Upload an image; enter useful alt text/caption; preview at mobile and desktop widths
- [ ] Preview a draft, publish only after approval, then locate and compare its revision history
- [ ] Identify how to request a backup restore and where the private consent/content source records are stored
- [ ] Confirm who reviews contact details, claims, media rights, and the catalogue, and how often

### Handover sign-off

| Item | Record |
| --- | --- |
| MBA content owner |  |
| WordPress Editor account verified (do not record password) |  |
| Site administrator and incident contact |  |
| Training date and attendees |  |
| Backup/restore contact and last verified restore date |  |
| Consent and media-rights record location |  |
| Agreed content review owner and cadence |  |
| Open questions or follow-up date |  |
| MBA representative approval/name |  |
| Administrator approval/name |  |
