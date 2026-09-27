# Global company settings

Administrators and Editors can use **Settings → MBA Settings**. Editors receive only `manage_mba_settings`; normal WordPress Editor content and media permissions remain in effect. They do not receive `manage_options`, plugin installation, or file-editing permissions.

All company fields are optional. Store only confirmed details. Phone numbers require an international `+` country code and are normalized to E.164 syntax; no country code is guessed. Invalid email addresses, URLs, media IDs, and map embed sources are rejected with a settings error. Map embed configuration accepts a Google Maps HTTPS embed URL, never arbitrary iframe HTML. The logo is an image attachment; the favicon must be square and at least 512 px and synchronizes the native site icon.

PHP templates and server-rendered blocks must call `mba_core_setting('mba_email')` (or another key from `mba_core_settings_fields()`) rather than duplicating contact data. The getter returns validated strings, zero for missing image IDs, and an empty string for unknown or missing text. Escape values for their output context and omit empty sections. Getters read the current option on each render, so a saved change takes effect everywhere these APIs are used.

Use `mba_core_phone_display()`, `mba_core_phone_url()`, and `mba_core_whatsapp_url()` for contact actions. Pass `mba_secondary_phone` to the phone helpers for the second number. WhatsApp links remove the leading `+` and URL-encode the configured message; they return an empty string without a valid number.

Block templates can use the dynamic block, which applies escaping and omits missing values:

```html
<!-- wp:mba/company-detail {"key":"mba_phone"} /-->
<!-- wp:mba/company-detail {"key":"mba_whatsapp","label":"WhatsApp"} /-->
<!-- wp:mba/company-detail {"key":"mba_logo_id"} /-->
<!-- wp:mba/company-detail {"key":"mba_footer_content"} /-->
```

The header/footer, forms, contact page, and schema implementation must consume this API in their respective issues. Templates must not publish example contact details or an unconfirmed response-time promise.
