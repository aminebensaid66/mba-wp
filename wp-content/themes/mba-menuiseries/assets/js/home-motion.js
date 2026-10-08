(() => {
	const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	if (reducedMotion || !window.IntersectionObserver) {
		return;
	}

	const root = document.documentElement;
	const visual = document.querySelector('.mba-home-hero__visual');
	let ticking = false;
	const onScroll = () => {
		const y = window.scrollY;
		root.classList.toggle('mba-scrolled', y > 24);
		if (visual && y < window.innerHeight) {
			visual.style.setProperty('--mba-parallax', `${Math.round(y * -0.12)}px`);
		}
		ticking = false;
	};
	window.addEventListener(
		'scroll',
		() => {
			if (!ticking) {
				ticking = true;
				window.requestAnimationFrame(onScroll);
			}
		},
		{ passive: true }
	);
	onScroll();

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
		section.querySelectorAll('.mba-card, li').forEach((item, index) => {
			item.style.setProperty('--mba-i', String(index % 6));
		});
		section.classList.add('mba-reveal');
		observer.observe(section);
	});

	root.classList.add('mba-motion-ready');
})();
