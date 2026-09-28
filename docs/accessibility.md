# Accessibility review

This review records source-level fixes and the manual checks required on a populated WordPress staging site. Automated PHP assertions cover the shared skip link, focusable main landmarks, native FAQ disclosures, reduced-motion/focus styles, and contrast ratios for the theme palette. They do not replace a rendered-page accessibility scanner or assistive-technology testing.

## Implemented and automated

- A visible-on-focus French skip link is emitted before the header. Every theme page template and dynamic content block exposes one `main#main[tabindex="-1"]` destination.
- Site navigation uses the WordPress Navigation block’s responsive overlay and keyboard behavior. All custom controls retain native links, buttons, inputs, selects, and form labels.
- FAQs use native `details` and `summary`, so expanded state, keyboard operation, and no-JavaScript behavior stay aligned.
- Project photos use a native modal `dialog`, a named close button, an accessible title and caption, explicit initial focus, Escape dismissal, and focus restoration to the invoking thumbnail.
- Keyboard focus uses a two-tone visible indicator on light and dark surfaces. The theme palette’s ink, aluminium, accent, and focus colors exceed 4.5:1 against white; reduced-motion preferences disable smooth scrolling and minimize animation and transition durations.
- Forms use associated labels, required state, native validation, and status regions for server responses. Map embeds require an explicit button before loading.

## Manual staging checklist

Complete these checks at desktop and mobile widths with realistic published content. Record browser, screen reader, date, and any issue found before launch.

- [ ] Tab from the address bar: the skip link appears first. Activate it on every template type and confirm focus and viewport move to the page’s main content.
- [ ] Tab through desktop navigation, nested product links, and the mobile navigation overlay. Confirm a visible focus indicator, logical order, Escape/close behavior, and focus restoration after closing.
- [ ] Open a project gallery photo using only the keyboard. Confirm the dialog receives focus, Tab stays within it, Escape and the close button dismiss it, and focus returns to the same thumbnail. Verify the photo description and caption with a screen reader.
- [ ] Operate FAQ disclosures with Enter and Space and confirm their announced expanded state matches the answer visibility. Repeat with JavaScript disabled.
- [ ] Submit quote and contact forms empty and with invalid values. Confirm labels, required fields, browser validation messages, server status announcements, and focus on errors are understandable with a screen reader.
- [ ] Inspect heading order, page/header/main/footer landmarks, navigation names, image alternatives, fieldset legends, and link/button names with NVDA + Firefox or VoiceOver + Safari.
- [ ] Test at 200% browser zoom and at a 320 CSS-pixel viewport. Confirm content reflows without horizontal page scrolling, obscured controls, or loss of function.
- [ ] Check text and control contrast on the rendered site, including configured editor content, logos, photographs, hover/focus states, and validation/status messages. Test Windows High Contrast/forced-colors mode.
- [ ] Enable reduced motion in the operating system and confirm there is no required animation or smooth-scroll behavior.
- [ ] Run axe DevTools (or axe-core) on the homepage, product/project archives and details, FAQ, quote form, contact form, and blog article. Resolve all serious and critical findings; record moderate/minor findings and exceptions.

## Remaining validation limits

The repository has no running WordPress instance in this environment: Docker is installed but its daemon is unavailable. Therefore this change has source-level assertions and palette contrast calculations, but the rendered-page axe scan, 200% zoom check, keyboard walkthrough, forced-colors check, and screen-reader tests remain unverified until staging is available. WordPress content entered by an editor (including image alternatives and page-builder blocks) also needs review on the populated site.
