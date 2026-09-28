import { test, expect } from '@playwright/test';
import { Buffer } from 'node:buffer';
import { URL, URLSearchParams } from 'node:url';

/* global document, FormData */

const responsiveWidths = [360, 390, 430, 768, 1024, 1440];
const scopedRoutes = [
  '/',
  '/produits/',
  '/realisations/',
  '/contact/',
  '/devis/',
  '/entreprise/',
  '/faq/',
  '/conseils/',
  '/produits/mba-e2e-product/',
  '/realisations/mba-e2e-project/',
];
const expectedHeadings = {
  '/produits/': 'Produits',
  '/realisations/': 'Réalisations',
  '/contact/': 'Contact',
  '/devis/': 'Demander un devis',
  '/entreprise/': 'Une entreprise au service de vos projets',
  '/faq/': 'Questions fréquentes',
  '/conseils/': 'Conseils',
  '/produits/mba-e2e-product/': 'MBA E2E Product',
  '/realisations/mba-e2e-project/': 'MBA E2E Project',
};

async function fillContactForm(page, { email = 'contact-e2e@example.test' } = {}) {
  await page.getByLabel(/Nom complet/).fill('MBA E2E Contact');
  await page.getByLabel(/Téléphone/).fill('+21612345678');
  await page.getByLabel(/E-mail/).fill(email);
  await page.getByLabel(/Votre message/).fill('Automated browser test enquiry.');
  await page.getByLabel(/J’accepte que MBA/).check();
}

async function fillQuoteForm(page, { email = '' } = {}) {
  await page.getByLabel(/Nom complet/).fill('MBA E2E Quote');
  await page.locator('input[name="phone"]').fill('+21612345679');
  if (email) await page.locator('input[name="email"]').fill(email);
  await page.getByLabel(/Ville/).fill('Tunis');
  await page.getByLabel(/Type de client/).selectOption('individual');
  await page.getByLabel(/Type de projet/).selectOption('renovation');
  await page.getByLabel(/Produits souhaités/).selectOption({ label: 'MBA E2E Product' });
  await page.getByLabel(/^Message/).fill('Automated browser test quote.');
  await page.locator('input[name="preferred_contact"][value="phone"]').check();
  await page.getByLabel(/J’accepte que MBA/).check();
}

test('critical routes render without browser or server errors and internal links resolve', async ({ page, baseURL }) => {
  const browserErrors = [];
  const brokenResponses = [];
  page.on('pageerror', (error) => browserErrors.push(error.message));
  page.on('console', (message) => {
    if (message.type() === 'error') browserErrors.push(message.text());
  });
  page.on('response', (response) => {
    if (response.url().startsWith(baseURL) && response.status() >= 400) {
      brokenResponses.push(`${response.status()} ${response.url()}`);
    }
  });

  for (const route of scopedRoutes) {
    const response = await page.goto(route);
    expect(response && response.status(), `Expected ${route} to load`).toBe(200);
    await expect(page.locator('main')).toBeVisible();
    await expect(page.locator('h1')).toHaveCount(1);
    if (expectedHeadings[route]) await expect(page.locator('h1')).toHaveText(expectedHeadings[route]);

    const links = await page.locator('a[href]').evaluateAll((anchors) => anchors.map((anchor) => anchor.href));
    for (const href of new Set(links)) {
      const target = new URL(href);
      if (target.origin !== baseURL || target.hash || target.pathname.startsWith('/wp-admin/')) continue;
      const linkResponse = await page.request.get(target.href);
      expect(linkResponse.status(), `Broken internal link from ${route}: ${target.href}`).toBeLessThan(400);
    }
  }

  expect(browserErrors).toEqual([]);
  expect(brokenResponses).toEqual([]);
});

test('unpublished sample catalogue records never appear in public archives', async ({ page }) => {
  await page.goto('/produits/');
  await expect(page.locator('main')).not.toContainText('Fenêtre fictive en aluminium');
  await page.goto('/realisations/');
  await expect(page.locator('main')).not.toContainText('Projet de démonstration');
});

test('critical templates fit all required viewport widths without horizontal overflow', async ({ page }) => {
  for (const width of responsiveWidths) {
    await page.setViewportSize({ width, height: 900 });
    for (const route of scopedRoutes) {
      await page.goto(route);
      const dimensions = await page.evaluate(() => ({
        viewport: document.documentElement.clientWidth,
        document: document.documentElement.scrollWidth,
        body: document.body.scrollWidth,
      }));
      expect(dimensions.document, `${route} overflows at ${width}px`).toBeLessThanOrEqual(dimensions.viewport + 1);
      expect(dimensions.body, `${route} body overflows at ${width}px`).toBeLessThanOrEqual(dimensions.viewport + 1);
    }
  }
});

test('mobile navigation opens and closes with keyboard support', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  await page.goto('/');
  const openMenu = page.locator('.wp-block-navigation__responsive-container-open');
  await expect(openMenu).toBeVisible();
  await openMenu.click();
  const menu = page.locator('.wp-block-navigation__responsive-container.is-menu-open');
  await expect(menu).toBeVisible();
  await expect(menu.getByRole('link', { name: 'Contact' })).toBeVisible();
  await page.keyboard.press('Escape');
  await expect(menu).toBeHidden();
  await expect(openMenu).toBeFocused();
});

test('product and project archive filters preserve selected values and show matching content', async ({ page }) => {
  await page.goto('/produits/');
  await page.locator('select[name="categorie"]').selectOption({ label: 'MBA E2E Category' });
  await Promise.all([
    page.waitForURL(/categorie=mba-e2e-category/),
    page.locator('.mba-archive-filters button[type="submit"]').click(),
  ]);
  await expect(page.locator('h1')).toHaveText('Produits');
  await expect(page.getByRole('link', { name: 'MBA E2E Product' }).first()).toBeVisible();
  await expect(page.getByRole('link', { name: 'Réinitialiser' })).toBeVisible();

  await page.goto('/realisations/');
  await page.locator('select[name="type_projet"]').selectOption({ label: 'MBA E2E Renovation' });
  await page.locator('select[name="produit"]').selectOption({ label: 'MBA E2E Product' });
  await Promise.all([
    page.waitForURL(/type_projet=mba-e2e-renovation.*produit=/),
    page.locator('.mba-archive-filters button[type="submit"]').click(),
  ]);
  await expect(page.getByRole('link', { name: 'MBA E2E Project' }).first()).toBeVisible();
});

test('project gallery opens as a dialog, closes with Escape, and restores focus', async ({ page }) => {
  await page.goto('/realisations/mba-e2e-project/');
  const trigger = page.locator('[data-lightbox-open]').first();
  await expect(trigger).toBeVisible();
  await trigger.click();
  const dialog = page.locator('dialog[data-lightbox-dialog]');
  await expect(dialog).toBeVisible();
  await expect(dialog.locator('[data-lightbox-image]')).toHaveAttribute('alt', 'Synthetic project gallery photo');
  await page.keyboard.press('Escape');
  await expect(dialog).toBeHidden();
  await expect(trigger).toBeFocused();
});

test('quote preselection, successful contact and quote submissions, and duplicate protection work', async ({ page, baseURL }) => {
  await page.goto('/devis/?product_slug=mba-e2e-product');
  await expect(page.locator('select[name="products[]"] option:checked')).toHaveText('MBA E2E Product');
  await fillQuoteForm(page);
  const formEntries = await page.locator('form.mba-quote-form').evaluate((form) =>
    Array.from(new FormData(form).entries()).filter(([name, value]) => typeof value === 'string' && name !== 'quote_files[]'),
  );
  const formKeys = formEntries.map(([name]) => name);
  expect(formKeys).toEqual(expect.arrayContaining([
    'mba_quote_nonce', 'quote_token', 'full_name', 'phone', 'email', 'location', 'customer_type',
    'project_type', 'products[]', 'message', 'preferred_contact', 'privacy_consent', 'source_signature',
  ]));
  const formValues = Object.fromEntries(formEntries);
  expect(formValues.full_name).toBe('MBA E2E Quote');
  expect(formValues.phone).toBe('+21612345679');
  expect(formValues.email).toBe('');
  expect(formValues.location).toBe('Tunis');
  expect(formValues.customer_type).toBe('individual');
  expect(formValues.project_type).toBe('renovation');
  expect(formValues['products[]']).toMatch(/^\d+$/);
  expect(formValues.message).toBe('Automated browser test quote.');
  expect(formValues.preferred_contact).toBe('phone');
  expect(formValues.privacy_consent).toBe('1');
  const originalPayload = new URLSearchParams(formEntries).toString();
  await Promise.all([
    page.waitForURL(/quote_status=/),
    page.locator('form.mba-quote-form button[type="submit"]').click(),
  ]);
  expect(new URL(page.url()).searchParams.get('quote_status')).toBe('success');
  await expect(page.getByRole('status')).toContainText('enregistrée');

  const duplicate = await page.request.post(new URL('/wp-admin/admin-post.php', baseURL).href, {
    data: originalPayload,
    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-MBA-E2E-Test': '1' },
    maxRedirects: 0,
  });
  expect(duplicate.status()).toBe(302);
  expect(duplicate.headers().location).toContain('quote_status=success');

  await page.goto('/contact/');
  await fillContactForm(page);
  await Promise.all([
    page.waitForURL(/contact_status=success/),
    page.locator('form.mba-contact-form button[type="submit"]').click(),
  ]);
  await expect(page.getByRole('status')).toContainText('transmis');
});

test('quote validation, honeypot spam, and invalid upload produce safe errors', async ({ page }) => {
  await page.goto('/devis/');
  await page.locator('form.mba-quote-form button[type="submit"]').click();
  await expect(page.locator('input[name="full_name"]')).toBeFocused();
  await expect(page.locator('input[name="full_name"]:invalid')).toHaveCount(1);

  await fillQuoteForm(page);
  await page.locator('input[name="website"]').fill('automated spam');
  await Promise.all([
    page.waitForURL(/quote_status=spam/),
    page.locator('form.mba-quote-form button[type="submit"]').click(),
  ]);
  await expect(page.getByRole('status')).toContainText('pas été acceptée');

  await page.goto('/devis/');
  await fillQuoteForm(page);
  await page.locator('input[name="quote_files[]"]').setInputFiles({
    name: 'invalid.png',
    mimeType: 'image/png',
    buffer: Buffer.from('not an image'),
  });
  await Promise.all([
    page.waitForURL(/quote_status=error/),
    page.locator('form.mba-quote-form button[type="submit"]').click(),
  ]);
  await expect(page.getByRole('status')).toContainText('Vérifiez les champs');
});

test('mail failure is shown without claiming a successful contact submission', async ({ page }) => {
  await page.setExtraHTTPHeaders({ 'X-MBA-E2E-Test': '1', 'X-MBA-E2E-Mail': 'fail' });
  await page.goto('/contact/');
  await fillContactForm(page, { email: 'mail-failure-e2e@example.com' });
  await Promise.all([
    page.waitForURL(/contact_status=email_error/),
    page.locator('form.mba-contact-form button[type="submit"]').click(),
  ]);
  await expect(page.getByRole('status')).toContainText('n’a pas reçu la notification');
});

test('network failure never shows a false form-success message', async ({ page }) => {
  await page.goto('/contact/');
  await fillContactForm(page, { email: 'network-failure-e2e@example.com' });
  await page.route('**/wp-admin/admin-post.php', (route) => route.abort('failed'));
  const requestFailure = page.waitForEvent('requestfailed', (request) => request.url().includes('admin-post.php'));
  const click = page.locator('form.mba-contact-form button[type="submit"]').click();
  await requestFailure;
  await click.catch(() => {});
  expect(page.url()).not.toContain('contact_status=success');
  const body = await page.locator('body').innerText().catch(() => '');
  expect(body).not.toContain('votre message a été transmis');
});
