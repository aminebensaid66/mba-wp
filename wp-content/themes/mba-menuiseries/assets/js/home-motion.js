(() => {
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (reducedMotion || !window.IntersectionObserver) {
		return;
	}

	const sections = document.querySelectorAll(
		'.mba-homepage > section:not(.mba-home-hero), .mba-home-marquee'
	);

	if (!sections.length) {
		return;
	}

	const observer = new window.IntersectionObserver(
		(entries, currentObserver) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					entry.target.classList.add('is-visible');
					currentObserver.unobserve(entry.target);
				}
			});
		},
		{ threshold: 0.12, rootMargin: '0px 0px -48px 0px' }
	);

	sections.forEach((section) => {
		section.classList.add('mba-reveal');
		observer.observe(section);
	});

	document.documentElement.classList.add('mba-motion-ready');
})();
