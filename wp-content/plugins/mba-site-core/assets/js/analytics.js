/* global window, URLSearchParams */
(function () {
	'use strict';
	var config = window.MBAAnalyticsConfig || {};
	var storageKey = 'mba-privacy-preferences-v1';
	var state = { analytics: false };
	var loaded = false;
	var trackedForms = new WeakSet();
	var trackedErrors = new WeakSet();
	var root;

	function readState() {
		try {
			var stored = JSON.parse(window.localStorage.getItem(storageKey) || 'null');
			if (stored && stored.version === 1 && typeof stored.analytics === 'boolean') {
				state.analytics = stored.analytics;
				return true;
			}
		} catch {
			return false;
		}
		return false;
	}

	function persistState() {
		try {
			window.localStorage.setItem(storageKey, JSON.stringify({ version: 1, necessary: true, analytics: state.analytics }));
		} catch {
			// Keep this choice for the current page when browser storage is unavailable.
		}
	}

	function track(name, parameters) {
		if (!state.analytics || !loaded || typeof window.gtag !== 'function') {
			return;
		}
		window.gtag('event', name, parameters || {});
	}

	function clearAnalyticsCookies() {
		document.cookie.split(';').forEach(function (cookie) {
			var name = cookie.split('=')[0].trim();
			if (name === '_ga' || name.indexOf('_ga_') === 0) {
				document.cookie = name + '=; Max-Age=0; path=/; SameSite=Lax';
			}
		});
	}

	function enableAnalytics() {
		if (loaded) {
			window['ga-disable-' + config.measurementId] = false;
			window.gtag('consent', 'update', { analytics_storage: 'granted' });
			return;
		}
		window.dataLayer = window.dataLayer || [];
		window.gtag = function () { window.dataLayer.push(arguments); };
		window.gtag('js', new Date());
		window.gtag('consent', 'default', { analytics_storage: 'granted', ad_storage: 'denied', ad_user_data: 'denied', ad_personalization: 'denied' });
		var safePagePath = config.contentType === 'product' ? '/products/[product]' : (config.contentType === 'project' ? '/projects/[project]' : '/');
		window.gtag('config', config.measurementId, {
			send_page_view: false,
			page_location: window.location.origin + safePagePath,
			page_referrer: window.location.origin + '/',
			allow_google_signals: false,
			allow_ad_personalization_signals: false
		});
		var script = document.createElement('script');
		script.async = true;
		script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(config.measurementId);
		document.head.appendChild(script);
		loaded = true;
		if (config.contentType === 'product') {
			track('product_view', { content_type: 'product' });
		} else if (config.contentType === 'project') {
			track('project_view', { content_type: 'project' });
		}
	}

	function setAnalyticsConsent(allowed) {
		state.analytics = allowed;
		persistState();
		if (allowed) {
			enableAnalytics();
		} else if (loaded) {
			window['ga-disable-' + config.measurementId] = true;
			window.gtag('consent', 'update', { analytics_storage: 'denied' });
			clearAnalyticsCookies();
		}
		if (root) {
			root.hidden = true;
		}
	}

	function button(label, callback) {
		var element = document.createElement('button');
		element.type = 'button';
		element.className = 'mba-consent__button';
		element.textContent = label;
		element.addEventListener('click', callback);
		return element;
	}

	function buildPreferences() {
		var labels = config.labels || {};
		root = document.createElement('section');
		root.className = 'mba-consent';
		root.setAttribute('role', 'region');
		root.setAttribute('aria-label', labels.title || 'Privacy preferences');
		root.hidden = true;
		var title = document.createElement('h2');
		title.textContent = labels.title || 'Privacy preferences';
		var description = document.createElement('p');
		description.textContent = labels.description || '';
		var controls = document.createElement('div');
		controls.className = 'mba-consent__controls';
		var preferences = document.createElement('div');
		preferences.className = 'mba-consent__custom';
		preferences.hidden = true;
		var label = document.createElement('label');
		var checkbox = document.createElement('input');
		checkbox.type = 'checkbox';
		checkbox.checked = state.analytics;
		label.appendChild(checkbox);
		label.appendChild(document.createTextNode(' ' + (labels.analytics || 'Allow anonymous usage analytics')));
		preferences.appendChild(label);
		preferences.appendChild(button(labels.save || 'Save preferences', function () { setAnalyticsConsent(checkbox.checked); }));
		controls.appendChild(button(labels.accept || 'Accept analytics', function () { setAnalyticsConsent(true); }));
		controls.appendChild(button(labels.reject || 'Reject analytics', function () { setAnalyticsConsent(false); }));
		controls.appendChild(button(labels.customize || 'Manage preferences', function () {
			preferences.hidden = !preferences.hidden;
			checkbox.checked = state.analytics;
			if (!preferences.hidden) {
				checkbox.focus();
			}
		}));
		root.appendChild(title);
		root.appendChild(description);
		root.appendChild(controls);
		root.appendChild(preferences);
		document.body.appendChild(root);
		document.querySelectorAll('[data-mba-consent-open]').forEach(function (link) {
			link.addEventListener('click', function (event) {
				event.preventDefault();
				root.hidden = false;
				checkbox.checked = state.analytics;
				root.querySelector('h2').setAttribute('tabindex', '-1');
				root.querySelector('h2').focus();
			});
		});
	}

	function trackFormStart(form, name) {
		if (trackedForms.has(form)) {
			return;
		}
		trackedForms.add(form);
		track(name + '_start', { form_type: name === 'quote_form' ? 'quote' : 'contact' });
	}

	function trackClick(event) {
		var link = event.target.closest('a');
		if (!link) {
			return;
		}
		var href = link.getAttribute('href') || '';
		if (href.indexOf('tel:') === 0) {
			track('phone_click');
		} else if (href.indexOf('mailto:') === 0) {
			track('email_click');
		} else if (href.indexOf('https://wa.me/') === 0) {
			track('whatsapp_click');
		} else if (link.dataset.mbaAnalyticsEvent === 'directions_click') {
			track('directions_click');
		} else if (link.hasAttribute('download') || /\.pdf(?:[?#]|$)/i.test(href)) {
			track('brochure_download');
		}
	}

	function bindFormEvents() {
		document.querySelectorAll('.mba-quote-form').forEach(function (form) {
			form.addEventListener('focusin', function () { trackFormStart(form, 'quote_form'); }, { once: true });
			form.addEventListener('invalid', function () {
				if (!trackedErrors.has(form)) {
					trackedErrors.add(form);
					track('quote_form_error', { form_type: 'quote', stage: 'client_validation' });
				}
			}, true);
			form.addEventListener('submit', function () {
				if (form.checkValidity()) {
					track('quote_form_submit', { form_type: 'quote' });
				}
			});
		});
		document.querySelectorAll('.mba-contact-form').forEach(function (form) {
			form.addEventListener('focusin', function () { trackFormStart(form, 'contact_form'); }, { once: true });
			form.addEventListener('invalid', function () {
				if (!trackedErrors.has(form)) {
					trackedErrors.add(form);
					track('contact_form_error', { form_type: 'contact', stage: 'client_validation' });
				}
			}, true);
			form.addEventListener('submit', function () {
				if (form.checkValidity()) {
					track('contact_form_submit', { form_type: 'contact' });
				}
			});
		});
	}

	function trackFormOutcome() {
		var params = new URLSearchParams(window.location.search);
		var quoteStatus = params.get('quote_status');
		var contactStatus = params.get('contact_status');
		var changed = false;
		if (quoteStatus) {
			if (['success', 'email_error', 'ack_error'].indexOf(quoteStatus) === -1) {
				track('quote_form_error', { form_type: 'quote', stage: 'server_validation' });
			}
			params.delete('quote_status');
			params.delete('quote_ref');
			changed = true;
		}
		if (contactStatus) {
			if (['success', 'email_error', 'ack_error'].indexOf(contactStatus) === -1) {
				track('contact_form_error', { form_type: 'contact', stage: 'server_validation' });
			}
			params.delete('contact_status');
			params.delete('contact_ref');
			changed = true;
		}
		if (changed && window.history && window.history.replaceState) {
			var cleanUrl = window.location.pathname + (params.toString() ? '?' + params.toString() : '') + window.location.hash;
			window.history.replaceState(null, '', cleanUrl);
		}
	}

	var hasChoice = readState();
	buildPreferences();
	document.addEventListener('click', trackClick);
	bindFormEvents();
	if (!hasChoice) {
		root.hidden = false;
	} else if (state.analytics) {
		enableAnalytics();
	}
	trackFormOutcome();
}());
