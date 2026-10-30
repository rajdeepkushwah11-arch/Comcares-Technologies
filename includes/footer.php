</main>
<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <a href="index.php" class="footer-brand brand-lockup" aria-label="Comcares Innovation Services Private Limited, home">
                <span class="brand-name">COMCARES</span>
                <span class="brand-tagline">Innovation Services Pvt. Ltd.</span>
            </a>
            <div class="footer-column">
                <h2>Company</h2>
                <a href="about.php">About us</a>
                <a href="services.php">What we do</a>
                <a href="contact.php">Contact</a>
            </div>
            <div class="footer-column">
                <h2>Services</h2>
                <a href="service.php?service=digital-experiences">Website design &amp; development</a>
                <a href="service.php?service=custom-software">Web applications</a>
                <a href="service.php?service=managed-technology">Maintenance &amp; hosting</a>
                <a href="service.php?service=it-consulting">IT consulting</a>
            </div>
            <div class="footer-column footer-connect">
                <h2>Start a conversation</h2>
                <p>Tell us what you are planning and where technology could help.</p>
                <a class="footer-contact-link" href="contact.php">Get in touch <span aria-hidden="true">&nearr;</span></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Comcares Innovation Services Pvt. Ltd. All rights reserved.</p>
            <a href="index.php">Comcares Technology</a>
        </div>
    </div>
</footer>

<div id="scrollProgress"></div>
<div id="cursorGlow"></div>

<!-- Scroll to top -->
<button id="siteToTop" class="site-totop" aria-label="Back to top" title="Back to top">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" width="18" height="18" aria-hidden="true"><path d="M18 15l-6-6-6 6"/></svg>
</button>

<!-- ============ COMCARES floating assistant ============ -->
<div class="cc-chat-root" id="ccChat">
    <div class="cc-panel" role="dialog" aria-modal="false" aria-label="COMCARES assistant chat">
        <div class="cc-head">
            <span class="cc-avatar" aria-hidden="true">🤖</span>
            <span class="cc-titles">
                <b>COMCARES Assistant</b>
                <span id="ccStatus">Checking availability…</span>
            </span>
            <button class="cc-close" type="button" aria-label="Close chat">✕</button>
        </div>
        <div class="cc-body" aria-live="polite" aria-label="Conversation"></div>
        <form class="cc-form">
            <label class="sr-only" for="ccInput">Type your message</label>
            <textarea class="cc-input" id="ccInput" rows="1" placeholder="Ask about services, School Sathi, pricing…" maxlength="1000"></textarea>
            <button class="cc-send" type="submit" aria-label="Send message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 2 11 13"/><path d="M22 2 15 22 11 13 7 13z"/></svg>
            </button>
        </form>
    </div>
    <button class="cc-launcher" type="button" aria-expanded="false" aria-label="Chat with COMCARES">
        <svg class="cc-icon-chat" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <svg class="cc-icon-close" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M18 6 6 18M6 6l12 12"/></svg>
        <span class="cc-dot" aria-hidden="true"></span>
    </button>
    <span class="cc-tip" aria-hidden="true">Chat with COMCARES</span>
    <div class="cc-hint" role="button" tabindex="0"><b>COMCARES Assistant</b>Questions about our services or School Sathi? Ask me here.</div>
</div>

<link rel="stylesheet" href="assets/chatbot.css?v=5">
<script src="assets/chatbot.js" defer></script>
<script src="assets/counters.js?v=3" defer></script>
<script src="assets/horizontal-scroll.js?v=7" defer></script>
<script src="assets/premium.js" defer></script>
<script src="assets/fx-effects.js?v=1" defer></script>

<script>
(function () {
    'use strict';
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------- Scroll-reveal with stagger ---------- */
    var reveals = document.querySelectorAll('.reveal');
    reveals.forEach(function (el, i) { el.style.setProperty('--d', Math.min(i * 0.08, 0.6) + 's'); });
    if (!('IntersectionObserver' in window) || reduceMotion) {
        reveals.forEach(function (el) { el.classList.add('visible'); });
    } else {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        reveals.forEach(function (el) { observer.observe(el); });
    }

    /* ---------- Typewriter ---------- */
    var tw = document.getElementById('typewriter');
    if (tw && !reduceMotion) {
        var words = JSON.parse(tw.getAttribute('data-words'));
        var caret = document.createElement('span');
        caret.className = 'typewriter-caret';
        tw.parentNode.appendChild(caret);
        var wi = 0, ci = 0, deleting = false;
        (function tick() {
            var word = words[wi];
            tw.textContent = word.substring(0, ci);
            if (!deleting) {
                if (ci++ < word.length) { setTimeout(tick, 65); return; }
                deleting = true; setTimeout(tick, 2100); return;
            }
            if (ci-- > 0) { setTimeout(tick, 35); return; }
            deleting = false; wi = (wi + 1) % words.length; setTimeout(tick, 350);
        })();
    }

    /* ---------- Animated counters ---------- */
    var counters = document.querySelectorAll('.counter');
    function animateCounter(el) {
        var target = parseInt(el.getAttribute('data-target'), 10);
        var start = null;
        function frame(ts) {
            if (!start) start = ts;
            var p = Math.min((ts - start) / 1600, 1);
            p = 1 - Math.pow(1 - p, 3); /* easeOutCubic */
            el.textContent = Math.round(target * p);
            if (p < 1) requestAnimationFrame(frame);
        }
        requestAnimationFrame(frame);
    }
    if ('IntersectionObserver' in window && !reduceMotion) {
        var cObs = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (e.isIntersecting) { animateCounter(e.target); cObs.unobserve(e.target); }
            });
        }, { threshold: 0.5 });
        counters.forEach(function (c) { cObs.observe(c); });
    } else {
        counters.forEach(function (c) { c.textContent = c.getAttribute('data-target'); });
    }

    /* ---------- Particles ---------- */
    var canvas = document.getElementById('particles');
    if (canvas && !reduceMotion) {
        var ctx = canvas.getContext('2d');
        var parts = [];
        function resize() { canvas.width = canvas.offsetWidth; canvas.height = canvas.offsetHeight; }
        resize(); window.addEventListener('resize', resize);
        for (var i = 0; i < 55; i++) {
            parts.push({
                x: Math.random() * canvas.width,
                y: Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 0.4,
                vy: (Math.random() - 0.5) * 0.4,
                r: Math.random() * 1.8 + 0.6
            });
        }
        (function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (var a = 0; a < parts.length; a++) {
                var p = parts[a];
                p.x += p.vx; p.y += p.vy;
                if (p.x < 0 || p.x > canvas.width) p.vx *= -1;
                if (p.y < 0 || p.y > canvas.height) p.vy *= -1;
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fillStyle = 'rgba(140, 180, 255, 0.5)';
                ctx.fill();
                for (var b = a + 1; b < parts.length; b++) {
                    var q = parts[b], dx = p.x - q.x, dy = p.y - q.y;
                    var d2 = dx * dx + dy * dy;
                    if (d2 < 13000) {
                        ctx.beginPath();
                        ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y);
                        ctx.strokeStyle = 'rgba(140, 180, 255,' + (0.16 * (1 - d2 / 13000)) + ')';
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(draw);
        })();
    }

    /* ---------- Page-ready fade-in ---------- */
    document.body.classList.add('page-ready');

    /* ---------- Scroll progress bar ---------- */
    var bar = document.getElementById('scrollProgress');
    var toTop = document.getElementById('siteToTop');
    function onScroll() {
        var h = document.documentElement;
        var pct = (h.scrollTop / (h.scrollHeight - h.clientHeight)) * 100;
        if (bar) bar.style.width = pct + '%';
        if (toTop) toTop.classList.toggle('is-visible', window.scrollY > 500);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    /* ---------- Scroll-to-top ---------- */
    if (toTop) {
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' });
        });
    }

    /* ---------- Page-transition fade ---------- */
    if (!reduceMotion) {
        document.querySelectorAll('a[href]').forEach(function (a) {
            var href = a.getAttribute('href');
            if (!href || href.charAt(0) === '#' || href.indexOf('mailto') === 0 ||
                href.indexOf('http') === 0 || a.target === '_blank') return;
            a.addEventListener('click', function (e) {
                e.preventDefault();
                document.body.classList.remove('page-ready');
                var dest = href;
                setTimeout(function () { window.location.href = dest; }, 200);
            });
        });
    }

    /* ---------- Cursor glow ---------- */
    var glow = document.getElementById('cursorGlow');
    if (glow && !reduceMotion) {
        window.addEventListener('mousemove', function (e) {
            glow.style.left = e.clientX + 'px';
            glow.style.top = e.clientY + 'px';
        }, { passive: true });
    }

    /* ---------- Mobile nav toggle ---------- */
    var toggle = document.getElementById('navToggle');
    var nav = document.getElementById('mainNav');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    /* ---------- Active nav highlighting ---------- */
    var page = location.pathname.split('/').pop() || 'index.php';
    document.querySelectorAll('.site-header nav a').forEach(function (a) {
        if (a.getAttribute('href') === page) a.classList.add('active');
    });

    /* ---------- Ripple on buttons ---------- */
    document.querySelectorAll('.btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (reduceMotion) return;
            var rect = btn.getBoundingClientRect();
            var size = Math.max(rect.width, rect.height);
            var r = document.createElement('span');
            r.className = 'ripple';
            r.style.width = r.style.height = size + 'px';
            r.style.left = (e.clientX - rect.left - size / 2) + 'px';
            r.style.top = (e.clientY - rect.top - size / 2) + 'px';
            btn.appendChild(r);
            setTimeout(function () { r.remove(); }, 700);
        });
    });

    /* ---------- 3D tilt on cards ---------- */
    if (!reduceMotion && window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('.tilt').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var rect = card.getBoundingClientRect();
                var rx = ((e.clientY - rect.top) / rect.height - 0.5) * -10;
                var ry = ((e.clientX - rect.left) / rect.width - 0.5) * 10;
                card.style.transform = 'perspective(700px) rotateX(' + rx + 'deg) rotateY(' + ry + 'deg) translateY(-6px)';
            });
            card.addEventListener('mouseleave', function () { card.style.transform = ''; });
        });
    }
})();
</script>
<script>
/* Motion for the Comcares Next landing page */
(function () {
    'use strict';
    var site = document.querySelector('.next-site');
    if (!site || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    var targets = site.querySelectorAll('.next-section-head, .next-service, .next-process > *, .next-steps article, .next-proof-layout, .next-contact .next-wrap');
    targets.forEach(function (element) { element.classList.add('next-animate'); });

    if ('IntersectionObserver' in window) {
        var revealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('next-in');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.16 });
        targets.forEach(function (element) { revealObserver.observe(element); });
    } else {
        targets.forEach(function (element) { element.classList.add('next-in'); });
    }
})();
</script>
</body>
</html>
