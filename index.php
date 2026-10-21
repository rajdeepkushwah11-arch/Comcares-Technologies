<?php
$pageTitle = 'The Digital Next';
require __DIR__ . '/includes/header.php';
?>

<div class="next-site">
    <section class="next-hero" id="top">
        <div class="next-grid" aria-hidden="true"></div>
        <div class="next-orb next-orb-one" aria-hidden="true"></div>
        <div class="next-orb next-orb-two" aria-hidden="true"></div>
        <div class="next-wrap next-hero-inner">
            <p class="next-eyebrow"><span></span>Comcares Technology / Digital systems</p>
            <h1>Technology built<br>for <em>what's next.</em></h1>
            <div class="next-hero-bottom">
                <p>We design and engineer websites, business software and intelligent digital systems for ambitious teams ready to move forward.</p>
                <div class="next-actions">
                    <a href="contact.php" class="next-button next-button-brand next-ripple">Start a conversation <b>&nearr;</b></a>
                    <a href="#capabilities" class="next-text-link">Explore our work <b>&darr;</b></a>
                </div>
            </div>
            <div class="next-hero-facts">
                <div class="next-fact"><strong data-count="150">0</strong><span>Projects delivered</span></div>
                <div class="next-fact"><strong data-count="98" data-suffix="%">0</strong><span>Client satisfaction</span></div>
                <div class="next-fact"><strong data-count="24" data-suffix="/7">0</strong><span>Support coverage</span></div>
            </div>
        </div>
        <a href="#capabilities" class="next-scroll">Scroll to explore <i></i></a>
    </section>

    <div class="next-ticker" role="region" aria-label="Our capabilities: web platforms, business software, cloud systems, digital strategy, and IT support" tabindex="0" data-autoscroll="true" data-scroll-duration="34">
        <div class="next-ticker-track" aria-hidden="true">
            <span>Web platforms</span><i>&starf;</i><span>Business software</span><i>&starf;</i><span>Cloud systems</span><i>&starf;</i><span>Digital strategy</span><i>&starf;</i><span>IT support</span><i>&starf;</i>
            <span>Web platforms</span><i>&starf;</i><span>Business software</span><i>&starf;</i><span>Cloud systems</span><i>&starf;</i><span>Digital strategy</span><i>&starf;</i><span>IT support</span><i>&starf;</i>
            <span>Web platforms</span><i>&starf;</i><span>Business software</span><i>&starf;</i><span>Cloud systems</span><i>&starf;</i><span>Digital strategy</span><i>&starf;</i><span>IT support</span><i>&starf;</i>
            <span>Web platforms</span><i>&starf;</i><span>Business software</span><i>&starf;</i><span>Cloud systems</span><i>&starf;</i><span>Digital strategy</span><i>&starf;</i><span>IT support</span><i>&starf;</i>
        </div>
    </div>

    <style>
        /* Always-on infinite auto-scroll for the capabilities ticker.
           Driven by assets/horizontal-scroll.js (already loaded site-wide):
           it advances scrollLeft every frame and wraps seamlessly.
           These rules only neutralise the hover/focus pauses so the
           marquee NEVER stops, and hide the scrollbar for a clean strip. */
        .next-ticker { overflow-x: hidden; scrollbar-width: none; -ms-overflow-style: none; }
        .next-ticker::-webkit-scrollbar { display: none; }
        .next-ticker .next-ticker-track { will-change: scroll-position; }
    </style>

    <section class="next-section next-capabilities" id="capabilities">
        <div class="next-wrap">
            <div class="next-section-head next-animate">
                <p class="next-eyebrow"><span></span>What we build</p>
                <h2>One team.<br><em>End-to-end</em> capability.</h2>
                <p class="next-intro">From a first sketch to the systems behind it, we join the dots between strategy, design and dependable engineering.</p>
            </div>
            <div class="next-service-list">
                <a href="services.php" class="next-service"><span>01</span><h3>Digital experiences</h3><p>High-performing websites and digital products that make a clear first impression.</p><b>&nearr;</b></a>
                <a href="services.php" class="next-service"><span>02</span><h3>Custom software</h3><p>Practical portals, dashboards and workflows shaped around your business.</p><b>&nearr;</b></a>
                <a href="services.php" class="next-service"><span>03</span><h3>Cloud &amp; automation</h3><p>Secure, scalable systems that reduce the busywork and keep teams connected.</p><b>&nearr;</b></a>
                <a href="services.php" class="next-service"><span>04</span><h3>Managed technology</h3><p>Proactive support, maintenance and clarity long after launch day.</p><b>&nearr;</b></a>
            </div>
        </div>
    </section>

    <section class="next-dark-panel" id="approach">
        <div class="next-wrap next-process next-animate">
            <div><p class="next-eyebrow"><span></span>How we work</p><h2>Less hand-off.<br>More <em>momentum.</em></h2></div>
            <p class="next-process-copy">Our senior team works closely with yours, turning complex requirements into focused releases&mdash;with a clear path from idea to a solution your team can use.</p>
        </div>
        <div class="next-wrap next-steps next-animate">
            <article><span>01 / Discover</span><h3>Find the real opportunity.</h3><p>We understand the people, process and goals before we propose a solution.</p></article>
            <article><span>02 / Shape</span><h3>Make the path visible.</h3><p>We design the experience and define a delivery plan everyone can follow.</p></article>
            <article><span>03 / Deliver</span><h3>Build for the real world.</h3><p>We launch, improve and support systems that stand up to daily work.</p></article>
        </div>
    </section>

    <section class="next-section next-live" id="live">
        <div class="next-wrap">
            <div class="next-live-head next-animate">
                <p class="next-eyebrow"><span></span>Live from our studio</p>
                <h2>Right now, <em>at Comcares.</em></h2>
            </div>
            <div class="next-live-grid next-animate">
                <article class="next-live-card">
                    <div class="next-live-pulse"><i></i>Deploying now</div>
                    <h3>Logistics portal, phase two</h3>
                    <p>A fleet-tracking dashboard going live for a nationwide distribution client &mdash; 240 vehicles, one screen.</p>
                    <time datetime="2026-10-14">Today &middot; 14:20</time>
                </article>
                <article class="next-live-card next-live-card-alt">
                    <div class="next-live-pulse next-live-pulse-amber"><i></i>In review</div>
                    <h3>AI-assisted support desk</h3>
                    <p>Smart ticket triage that routes customer queries to the right engineer in seconds, not hours.</p>
                    <time datetime="2026-10-14">This week</time>
                </article>
                <article class="next-live-card next-live-card-alt2">
                    <div class="next-live-pulse next-live-pulse-green"><i></i>Shipped</div>
                    <h3>Cloud migration, complete</h3>
                    <p>A legacy ERP moved to the cloud with zero downtime &mdash; 12 years of data, safely across.</p>
                    <time datetime="2026-10-14">Last week</time>
                </article>
            </div>
        </div>
    </section>

    <section class="next-section next-spark" id="estimator">
        <div class="next-wrap next-spark-inner next-animate">
            <div class="next-spark-copy">
                <p class="next-eyebrow"><span></span>Project estimator</p>
                <h2>Price your idea<br>in <em>thirty seconds.</em></h2>
                <p class="next-spark-note">Sliders ghumaiye apna ballpark scope aur budget banane ke liye. Koi email nahi &mdash; yeh bas imaandaar shuruaat hai. All prices in &#8377; (INR).</p>
            </div>
            <form class="next-estimator" onsubmit="return false;">
                <div class="next-est-row">
                    <label>Project type</label>
                    <div class="next-est-pills">
                        <button type="button" class="next-est-pill is-active" data-mult="1">Website</button>
                        <button type="button" class="next-est-pill" data-mult="1.8">Portal</button>
                        <button type="button" class="next-est-pill" data-mult="2.6">Custom software</button>
                    </div>
                </div>
                <div class="next-est-row">
                    <label for="estPages">Pages / modules &mdash; <output id="estPagesOut">5</output></label>
                    <input type="range" id="estPages" min="1" max="30" value="5">
                </div>
                <div class="next-est-row">
                    <label for="estSpeed">Urgency &mdash; <output id="estSpeedOut">Standard</output></label>
                    <input type="range" id="estSpeed" min="0" max="2" value="0" step="1">
                </div>
                <div class="next-est-result">
                    <span>Estimated range (GST exclusive)</span>
                    <strong id="estResult"></strong>
                    <a href="contact.php" class="next-button next-button-brand">Select Quotation <b>&nearr;</b></a>
                </div>
            </form>
        </div>
    </section>

    <section class="next-section next-proof">
        <div class="next-wrap">
            <p class="next-eyebrow"><span></span>Built for progress</p>
            <div class="next-proof-layout next-animate">
                <h2>Good technology<br>should feel <em>simple.</em></h2>
                <div class="next-stats next-animate">
                    <div class="next-stat-card" data-stat="150" data-suffix="+">
                        <div class="next-stat-ring" aria-hidden="true">
                            <svg viewBox="0 0 72 72"><circle class="ring-bg" cx="36" cy="36" r="33"/><circle class="ring-fg" cx="36" cy="36" r="33"/></svg>
                        </div>
                        <strong>150<span>+</span></strong>
                        <p>projects delivered</p>
                    </div>
                    <div class="next-stat-card" data-stat="24" data-suffix="/7">
                        <div class="next-stat-ring" aria-hidden="true">
                            <svg viewBox="0 0 72 72"><circle class="ring-bg" cx="36" cy="36" r="33"/><circle class="ring-fg" cx="36" cy="36" r="33"/></svg>
                        </div>
                        <strong>24<span>/7</span></strong>
                        <p>support available</p>
                    </div>
                    <div class="next-stat-card" data-stat="10" data-suffix="+">
                        <div class="next-stat-ring" aria-hidden="true">
                            <svg viewBox="0 0 72 72"><circle class="ring-bg" cx="36" cy="36" r="33"/><circle class="ring-fg" cx="36" cy="36" r="33"/></svg>
                        </div>
                        <strong>10<span>+</span></strong>
                        <p>years of experience</p>
                    </div>
                    <div class="next-stat-card" data-stat="98" data-suffix="%">
                        <div class="next-stat-ring" aria-hidden="true">
                            <svg viewBox="0 0 72 72"><circle class="ring-bg" cx="36" cy="36" r="33"/><circle class="ring-fg" cx="36" cy="36" r="33"/></svg>
                        </div>
                        <strong>98<span>%</span></strong>
                        <p>client satisfaction</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="next-section next-voices" id="voices">
        <div class="next-wrap">
            <div class="next-live-head next-animate">
                <p class="next-eyebrow"><span></span>Client voices</p>
                <h2>Teams that <em>moved forward.</em></h2>
            </div>
            <div class="next-voice-marquee next-animate" role="region" aria-label="Client testimonials" tabindex="0" data-scroll-duration="48">
                <div class="next-voice-track">
                    <blockquote class="next-voice"><p>&ldquo;They rebuilt our booking platform in eight weeks. Support calls dropped by half.&rdquo;</p><footer><b>RK</b><span>Ravi K. &middot; Hospitality group</span><i>&#9733;&#9733;&#9733;</i></footer></blockquote>
                    <blockquote class="next-voice"><p>&ldquo;The dashboard they built is the first tool my whole team actually opens every morning.&rdquo;</p><footer><b>SM</b><span>Sana M. &middot; Retail operations</span><i>&#9733;&#9733;&#9733;&#9733;</i></footer></blockquote>
                    <blockquote class="next-voice"><p>&ldquo;Cloud migration with zero downtime. I kept waiting for the problem that never came.&rdquo;</p><footer><b>AT</b><span>Arun T. &middot; Manufacturing</span><i>&#9733;&#9733;&#9733;&#9733;</i></footer></blockquote>
                    <blockquote class="next-voice"><p>&ldquo;Responsive, honest and genuinely senior. They talk like partners, not vendors.&rdquo;</p><footer><b>PD</b><span>Priya D. &middot; Fintech</span><i>&#9733;&#9733;&#9733;&#9733;&#9733;</i></footer></blockquote>
                    <blockquote class="next-voice"><p>&ldquo;They rebuilt our booking platform in eight weeks. Support calls dropped by half.&rdquo;</p><footer><b>RK</b><span>Ravi K. &middot; Hospitality group</span><i>&#9733;&#9733;&#9733;&#9733;</i></footer></blockquote>
                    <blockquote class="next-voice"><p>&ldquo;The dashboard they built is the first tool my whole team actually opens every morning.&rdquo;</p><footer><b>SM</b><span>Sana M. &middot; Retail operations</span><i>&#9733;&#9733;&#9733;&#9733;&#9733;</i></footer></blockquote>
                    <blockquote class="next-voice"><p>&ldquo;Cloud migration with zero downtime. I kept waiting for the problem that never came.&rdquo;</p><footer><b>AT</b><span>Arun T. &middot; Manufacturing</span><i>&#9733;&#9733;&#9733;</i></footer></blockquote>
                    <blockquote class="next-voice"><p>&ldquo;Responsive, honest and genuinely senior. They talk like partners, not vendors.&rdquo;</p><footer><b>PD</b><span>Priya D. &middot; Fintech</span><i>&#9733;&#9733;&#9733;</i></footer></blockquote>
                </div>
            </div>
        </div>

    </section>

    <section class="next-dark-panel next-faq-panel" id="faq">
        <div class="next-wrap next-faq-inner next-animate">
            <div><p class="next-eyebrow"><span></span>Common questions</p><h2>Straight<br><em>answers.</em></h2></div>
            <div class="next-faq">
                <details class="next-faq-item" open>
                    <summary>How fast can we start?<i>+</i></summary>
                    <p>Most projects kick off within two weeks of the first call. Urgent work can often start sooner &mdash; just ask.</p>
                </details>
                <details class="next-faq-item">
                    <summary>Do you work with our existing systems?<i>+</i></summary>
                    <p>Yes. We integrate with what you already run and migrate only when it genuinely makes sense.</p>
                </details>
                <details class="next-faq-item">
                    <summary>Who owns the code?<i>+</i></summary>
                    <p>You do. Every repository, credential and design file is handed over at launch.</p>
                </details>
                <details class="next-faq-item">
                    <summary>What happens after launch?<i>+</i></summary>
                    <p>We offer managed support plans with monitoring, updates and a named engineer who knows your system.</p>
                </details>
            </div>
        </div>
    </section>

    <section class="next-contact">
        <div class="next-wrap next-animate"><p class="next-eyebrow"><span></span>Get in touch</p><h2>Have an idea?<br><em>Let's make it real.</em></h2><a href="contact.php" class="next-button next-button-dark next-ripple">Tell us about your project <b>&nearr;</b></a></div>
    </section>
</div>

<script>
(function () {
    'use strict';
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Capabilities ticker: force continuous auto-scroll.
       horizontal-scroll.js pauses while hovered/focused; this page wants
       an always-running marquee, so we strip those pause listeners only
       from the ticker (the testimonials marquee keeps its normal behaviour). */
    var ticker = document.querySelector('.next-ticker');
    if (ticker) {
        var clean = ticker.cloneNode(true); /* clone drops all listeners */
        clean.setAttribute('data-autoscroll', 'true');
        ticker.parentNode.replaceChild(clean, ticker);
    }

    /* scroll reveal */
    var revealEls = document.querySelectorAll('.next-animate');
    if ('IntersectionObserver' in window) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('next-in'); io.unobserve(e.target); } });
        }, { threshold: 0.15 });
        revealEls.forEach(function (el) { io.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('next-in'); });
    }

    /* hero counters are handled by the shared assets/counters.js */

    /* button ripple */
    document.querySelectorAll('.next-ripple').forEach(function (btn) {
        btn.addEventListener('click', function (ev) {
            var r = btn.getBoundingClientRect(), d = Math.max(r.width, r.height) * 2,
                s = document.createElement('span');
            s.className = 'next-ink';
            s.style.width = s.style.height = d + 'px';
            s.style.left = (ev.clientX - r.left - d / 2) + 'px';
            s.style.top = (ev.clientY - r.top - d / 2) + 'px';
            btn.appendChild(s);
            setTimeout(function () { s.remove(); }, 700);
        });
    });

    /* live estimator — all prices in Indian Rupees (₹),
       formatted the Indian way (thousand / lakh separators). */
    var pages = document.getElementById('estPages'),
        speed = document.getElementById('estSpeed'),
        pills = document.querySelectorAll('.next-est-pill'),
        out = document.getElementById('estResult'),
        speedNames = ['Standard', 'Jaldi wala (fast-track)', 'Rush'],
        mult = 1,
        BASE = 25000, PER_UNIT = 15000, SPEED_MULT = [1, 1.25, 1.55];

    /* Indian numbering: last 3 digits, then groups of 2 (1,00,000). */
    function inr(n) {
        var rounded = Math.round(n / 500) * 500;
        var s = String(Math.round(rounded));
        var last3 = s.slice(-3);
        var rest = s.slice(0, -3);
        if (rest) last3 = rest.replace(/\B(?=(\d{2})+(?!\d))/g, ',') + ',' + last3;
        return '\u20B9' + last3;
    }
    function estimate() {
        var units = parseInt(pages.value, 10),
            low = BASE + units * PER_UNIT * mult * SPEED_MULT[speed.value],
            high = low * 1.45;
        document.getElementById('estPagesOut').textContent = units;
        document.getElementById('estSpeedOut').textContent = speedNames[speed.value];
        out.textContent = inr(low) + ' \u2013 ' + inr(high);
    }
    pages.addEventListener('input', estimate);
    speed.addEventListener('input', estimate);
    pills.forEach(function (p) {
        p.addEventListener('click', function () {
            pills.forEach(function (q) { q.classList.remove('is-active'); });
            p.classList.add('is-active');
            mult = parseFloat(p.getAttribute('data-mult'));
            estimate();
        });
    });
    estimate();

    /* tilt on live cards */
    if (!reduce && window.matchMedia('(hover: hover)').matches) {
        document.querySelectorAll('.next-live-card').forEach(function (card) {
            card.addEventListener('mousemove', function (e) {
                var r = card.getBoundingClientRect(),
                    x = (e.clientX - r.left) / r.width - 0.5,
                    y = (e.clientY - r.top) / r.height - 0.5;
                card.style.transform = 'perspective(900px) rotateX(' + (-y * 6) + 'deg) rotateY(' + (x * 8) + 'deg) translateY(-4px)';
            });
            card.addEventListener('mouseleave', function () { card.style.transform = ''; });
        });
    }
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
