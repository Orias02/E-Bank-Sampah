/**
 * Interaksi Landing Page E-Bank Sampah FILKOM.
 * Dimuat dari resources/js/app.js lewat: import './landing';
 * Setiap bagian aman dijalankan walau elemennya tidak ada di halaman.
 */

const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const $ = (selector, root = document) => root.querySelector(selector);
const $$ = (selector, root = document) => [...root.querySelectorAll(selector)];

/* ------------------------------------------------------------------
   Header: efek saat di-scroll + menu mobile
------------------------------------------------------------------- */
function initHeader() {
    const header = $('#site-header');
    if (!header) return;

    const toggle = $('#menu-toggle');
    const menu = $('#mobile-menu');

    const onScroll = () => {
        header.dataset.scrolled = String(window.scrollY > 40);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    const setMenu = (open) => {
        header.dataset.menu = open ? 'open' : 'closed';
        toggle?.setAttribute('aria-expanded', String(open));
        toggle?.setAttribute('aria-label', open ? 'Tutup menu' : 'Buka menu');
        if (menu) menu.inert = !open;
    };
    setMenu(false);

    toggle?.addEventListener('click', () => setMenu(header.dataset.menu !== 'open'));
    menu?.addEventListener('click', (e) => {
        if (e.target.closest('a')) setMenu(false);
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') setMenu(false);
    });
    document.addEventListener('click', (e) => {
        if (header.dataset.menu === 'open' && !header.contains(e.target)) setMenu(false);
    });
}

/* ------------------------------------------------------------------
   Scroll-spy: menu aktif mengikuti section + garis penanda yang bergeser
------------------------------------------------------------------- */
function initScrollSpy() {
    const sections = $$('[data-section]');
    const nav = $('#main-nav');
    const indicator = $('#nav-indicator');
    if (!sections.length) return;

    const desktopLinks = nav ? $$('[data-nav-link]', nav) : [];
    let activeId = sections[0].id;

    const linkFor = (id) => desktopLinks.find((a) => a.hash === `#${id}`);

    const moveIndicator = (link, animate = true) => {
        if (!nav || !indicator || !link) return;
        const navBox = nav.getBoundingClientRect();
        const linkBox = link.getBoundingClientRect();
        if (!navBox.width) return; // nav disembunyikan (mobile)

        const x = linkBox.left - navBox.left + (linkBox.width - indicator.offsetWidth) / 2;
        if (!animate) indicator.style.transition = 'none';
        indicator.style.transform = `translateX(${x}px)`;
        indicator.style.opacity = '1';
        if (!animate) {
            void indicator.offsetWidth; // paksa reflow supaya posisi awal tidak "meluncur"
            indicator.style.transition = '';
        }
    };

    const setActive = (id, animate = true) => {
        activeId = id;
        $$('[data-nav-link]').forEach((a) => {
            const on = a.hash === `#${id}`;
            a.dataset.active = String(on);
            if (on) a.setAttribute('aria-current', 'page');
            else a.removeAttribute('aria-current');
        });
        moveIndicator(linkFor(id), animate);
    };

    // Section yang melewati garis tengah layar dianggap aktif.
    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) setActive(entry.target.id);
            });
        },
        { rootMargin: '-45% 0px -50% 0px' },
    );
    sections.forEach((s) => observer.observe(s));

    // Garis penanda mengintip ke menu yang di-hover, lalu kembali ke yang aktif.
    desktopLinks.forEach((a) => {
        a.addEventListener('mouseenter', () => moveIndicator(a));
        a.addEventListener('focus', () => moveIndicator(a));
    });
    nav?.addEventListener('mouseleave', () => moveIndicator(linkFor(activeId)));
    nav?.addEventListener('focusout', () => moveIndicator(linkFor(activeId)));

    setActive(activeId, false);
    window.addEventListener('resize', () => moveIndicator(linkFor(activeId), false));
    document.fonts?.ready.then(() => moveIndicator(linkFor(activeId), false));
}

/* ------------------------------------------------------------------
   Hero: gambar latar bergeser halus mengikuti kursor + parallax saat scroll
------------------------------------------------------------------- */
function initHeroParallax() {
    const hero = $('#beranda');
    const bg = $('#hero-bg');
    if (!hero || !bg || reducedMotion) return;

    const finePointer = window.matchMedia('(pointer: fine)').matches;
    let targetX = 0;
    let targetY = 0;
    let currentX = 0;
    let currentY = 0;
    let running = false;
    let visible = true;

    const render = () => {
        currentX += (targetX - currentX) * 0.08;
        currentY += (targetY - currentY) * 0.08;
        const scrollShift = Math.min(window.scrollY, window.innerHeight) * 0.25;
        bg.style.transform = `translate3d(${currentX}px, ${currentY + scrollShift}px, 0)`;

        const moving = Math.abs(targetX - currentX) > 0.1 || Math.abs(targetY - currentY) > 0.1;
        if (visible && moving) requestAnimationFrame(render);
        else running = false;
    };

    const kick = () => {
        if (running || !visible) return;
        running = true;
        requestAnimationFrame(render);
    };

    if (finePointer) {
        hero.addEventListener('pointermove', (e) => {
            const box = hero.getBoundingClientRect();
            targetX = ((e.clientX - box.left) / box.width - 0.5) * -24;
            targetY = ((e.clientY - box.top) / box.height - 0.5) * -16;
            kick();
        });
        hero.addEventListener('pointerleave', () => {
            targetX = 0;
            targetY = 0;
            kick();
        });
    }

    window.addEventListener('scroll', kick, { passive: true });
    new IntersectionObserver(([entry]) => {
        visible = entry.isIntersecting;
        kick();
    }).observe(hero);
}

/* ------------------------------------------------------------------
   Elemen muncul saat masuk layar
------------------------------------------------------------------- */
function initReveal() {
    const items = $$('.reveal');
    if (!items.length) return;

    if (reducedMotion || !('IntersectionObserver' in window)) {
        items.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                obs.unobserve(entry.target);
            });
        },
        { threshold: 0.12, rootMargin: '0px 0px -6% 0px' },
    );
    items.forEach((el) => observer.observe(el));
}

/* ------------------------------------------------------------------
   Kartu dengan cahaya mengikuti kursor
------------------------------------------------------------------- */
function initSpotlight() {
    $$('.spotlight').forEach((el) => {
        el.addEventListener('pointermove', (e) => {
            const box = el.getBoundingClientRect();
            el.style.setProperty('--mx', `${e.clientX - box.left}px`);
            el.style.setProperty('--my', `${e.clientY - box.top}px`);
        });
    });
}

/* ------------------------------------------------------------------
   Langkah menjadi nasabah (tab interaktif)
------------------------------------------------------------------- */
function initSteps() {
    const tabs = $$('[data-step]');
    const panels = $$('[data-step-panel]');
    const bar = $('#step-progress');
    const next = $('[data-step-next]');
    if (!tabs.length) return;

    let current = 0;

    const show = (index, focus = false) => {
        current = (index + tabs.length) % tabs.length;
        tabs.forEach((tab, i) => {
            const on = i === current;
            tab.setAttribute('aria-selected', String(on));
            tab.tabIndex = on ? 0 : -1;
        });
        panels.forEach((panel, i) => {
            panel.dataset.active = String(i === current);
        });
        if (bar) bar.style.width = `${((current + 1) / tabs.length) * 100}%`;
        if (focus) tabs[current].focus();
    };

    tabs.forEach((tab, i) => {
        tab.addEventListener('click', () => show(i));
        tab.addEventListener('keydown', (e) => {
            const keys = {
                ArrowDown: current + 1,
                ArrowRight: current + 1,
                ArrowUp: current - 1,
                ArrowLeft: current - 1,
                Home: 0,
                End: tabs.length - 1,
            };
            if (e.key in keys) {
                e.preventDefault();
                show(keys[e.key], true);
            }
        });
    });
    next?.addEventListener('click', () => show(current + 1));
    show(0);
}

/* ------------------------------------------------------------------
   Simulasi nilai setoran (berat × harga per kg, harga contoh)
------------------------------------------------------------------- */
function initSimulator() {
    const root = $('#simulator');
    if (!root) return;

    const chips = $$('[data-waste]', root);
    const range = $('#sim-weight', root);
    const number = $('#sim-weight-number', root);
    const totalEl = $('#sim-total', root);
    const formulaEl = $('#sim-formula', root);
    if (!chips.length || !range || !number || !totalEl || !formulaEl) return;

    const rupiah = new Intl.NumberFormat('id-ID');
    const kilo = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 2 });

    const selected = chips.find((c) => c.getAttribute('aria-pressed') === 'true') ?? chips[0];
    let price = Number(selected.dataset.price);
    let weight = Number(number.value) || 0;
    let shown = Number(totalEl.dataset.value) || 0;
    let frame;

    const animateTo = (target) => {
        cancelAnimationFrame(frame);
        if (reducedMotion) {
            shown = target;
            totalEl.textContent = rupiah.format(target);
            return;
        }
        const from = shown;
        const start = performance.now();
        const duration = 450;
        const tick = (now) => {
            const t = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - t, 3);
            shown = Math.round(from + (target - from) * eased);
            totalEl.textContent = rupiah.format(shown);
            if (t < 1) frame = requestAnimationFrame(tick);
        };
        frame = requestAnimationFrame(tick);
    };

    const update = () => {
        formulaEl.textContent = `${kilo.format(weight)} kg × Rp ${rupiah.format(price)}/kg`;
        animateTo(Math.round(weight * price));
    };

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            chips.forEach((c) => c.setAttribute('aria-pressed', String(c === chip)));
            price = Number(chip.dataset.price);
            update();
        });
    });

    range.addEventListener('input', () => {
        weight = Number(range.value);
        number.value = String(weight);
        update();
    });

    number.addEventListener('input', () => {
        weight = Math.min(500, Math.max(0, Number(number.value) || 0));
        range.value = String(Math.min(weight, Number(range.max)));
        update();
    });
    number.addEventListener('blur', () => {
        number.value = String(weight);
    });
}

/* ------------------------------------------------------------------
   FAQ akordeon (satu terbuka dalam satu waktu)
------------------------------------------------------------------- */
function initFaq() {
    const items = $$('[data-faq]');
    if (!items.length) return;

    const setOpen = (item, open) => {
        item.dataset.open = String(open);
        $('[data-faq-trigger]', item)?.setAttribute('aria-expanded', String(open));
    };

    items.forEach((item) => {
        $('[data-faq-trigger]', item)?.addEventListener('click', () => {
            const willOpen = item.dataset.open !== 'true';
            items.forEach((other) => setOpen(other, other === item && willOpen));
        });
    });
}

/* ------------------------------------------------------------------
   Tombol "Salin" (kontak Helpdesk)
------------------------------------------------------------------- */
function initCopy() {
    $$('[data-copy]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const text = btn.dataset.copy;
            try {
                await navigator.clipboard.writeText(text);
            } catch {
                const area = document.createElement('textarea');
                area.value = text;
                area.style.cssText = 'position:fixed;opacity:0';
                document.body.appendChild(area);
                area.select();
                document.execCommand('copy');
                area.remove();
            }

            const label = $('[data-copy-label]', btn);
            const original = label?.dataset.original ?? label?.textContent;
            if (label) {
                label.dataset.original = original;
                label.textContent = 'Tersalin';
            }
            btn.dataset.copied = 'true';
            setTimeout(() => {
                if (label) label.textContent = original;
                delete btn.dataset.copied;
            }, 1600);
        });
    });
}

/* ------------------------------------------------------------------
   Tombol kembali ke atas
------------------------------------------------------------------- */
function initToTop() {
    const btn = $('#to-top');
    if (!btn) return;

    const onScroll = () => {
        btn.dataset.visible = String(window.scrollY > 700);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
    });
}

function init() {
    initHeader();
    initScrollSpy();
    initHeroParallax();
    initReveal();
    initSpotlight();
    initSteps();
    initSimulator();
    initFaq();
    initCopy();
    initToTop();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}