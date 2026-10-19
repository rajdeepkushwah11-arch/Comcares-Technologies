<?php
$pageTitle = 'Services';
require __DIR__ . '/includes/header.php';

$services = [
    [
        'num' => '01',
        'name' => 'Digital experiences',
        'tag' => 'Websites',
        'desc' => 'High-performing websites and online stores that make the right first impression — and hold attention after it.',
        'points' => ['Custom design, no templates', 'SEO-ready from day one', 'Loads fast on every device'],
        'from' => 450000,
    ],
    [
        'num' => '02',
        'name' => 'Custom software',
        'tag' => 'Portals &amp; tools',
        'desc' => 'Portals, dashboards, booking systems and internal tools shaped around the way your business actually works.',
        'points' => ['Built to your workflow', 'Role-based access &amp; reporting', 'Integrates with what you run'],
        'from' => 900000,
    ],
    [
        'num' => '03',
        'name' => 'Cloud &amp; automation',
        'tag' => 'Infrastructure',
        'desc' => 'Secure, scalable cloud systems and automations that remove the busywork and keep teams connected anywhere.',
        'points' => ['Zero-downtime migrations', 'Backup &amp; disaster recovery', 'Workflow automation'],
        'from' => 650000,
    ],
    [
        'num' => '04',
        'name' => 'Managed technology',
        'tag' => 'Support',
        'desc' => 'Proactive monitoring, updates and a named engineer who knows your system — clarity long after launch day.',
        'points' => ['24/7 uptime monitoring', 'Security patches &amp; updates', 'Direct line to an engineer'],
        'from' => 25000,
    ],
    [
        'num' => '05',
        'name' => 'IT consulting',
        'tag' => 'Strategy',
        'desc' => 'Practical, jargon-free advice on tools, security and infrastructure — before you spend a rupee you regret.',
        'points' => ['Technology roadmaps', 'Security audits', 'Vendor-neutral recommendations'],
        'from' => 150000,
    ],
    [
        'num' => '06',
        'name' => 'E-commerce builds',
        'tag' => 'Online stores',
        'desc' => 'Stores that convert: clean catalogues, frictionless checkout and the reporting to know what is selling.',
        'points' => ['Payment gateway setup', 'Inventory &amp; order flows', 'Conversion-focused UX'],
        'from' => 700000,
    ],
];

$engagements = [
    ['01', 'Discover', 'A working session to map your goals, constraints and the people the solution must serve.'],
    ['02', 'Shape', 'A visible plan — designs, scope and a delivery timeline everyone can follow.'],
    ['03', 'Build', 'Focused releases with demos you can actually click, not slide decks.'],
    ['04', 'Run', 'Launch, measure, improve — and stay available when it matters.'],
];
?>

<div class="next-site next-services">
    <!-- ================= HERO ================= -->
    <section class="next-hero next-services-hero" id="top">
        <div class="next-grid" aria-hidden="true"></div>
        <div class="next-orb next-orb-one" aria-hidden="true"></div>
        <div class="next-orb next-orb-two" aria-hidden="true"></div>
        <div class="next-wrap next-hero-inner">
            <p class="next-eyebrow"><span></span>Services / What we build</p>
            <h1>Digital work,<br><em>made useful.</em></h1>
            <div class="next-hero-bottom">
                <p>A focused set of capabilities for building, improving and supporting the systems your business depends on — nothing you don't need.</p>
                    <div class="next-actions">
                        <a href="contact.php" class="next-button next-button-brand next-ripple">Request a quote <b>&nearr;</b></a>
                        <a href="#services" class="next-text-link">Browse services <b>&darr;</b></a>
                    </div>
                </div>
                <div class="next-hero-facts">
                    <div class="next-fact"><strong data-count="6" data-suffix="">0</strong><span>Core services</span></div>
                    <div class="next-fact"><strong data-count="150" data-suffix="+">0</strong><span>Projects delivered</span></div>
                    <div class="next-fact"><strong data-count="98" data-suffix="%">0</strong><span>Client satisfaction</span></div>
                </div>
            </div>
            <a href="#services" class="next-scroll">Scroll to explore <i></i></a>
    </section>

    <div class="next-ticker" role="region" aria-label="Service areas: websites, custom software, cloud, automation, support, consulting" tabindex="0" data-scroll-duration="34">
        <div class="next-ticker-track" aria-hidden="true">
            <span>Websites</span><i>&starf;</i><span>Custom software</span><i>&starf;</i><span>Cloud systems</span><i>&starf;</i><span>Automation</span><i>&starf;</i><span>Support</span><i>&starf;</i><span>Consulting</span><i>&starf;</i>
            <span>Websites</span><i>&starf;</i><span>Custom software</span><i>&starf;</i><span>Cloud systems</span><i>&starf;</i><span>Automation</span><i>&starf;</i><span>Support</span><i>&starf;</i><span>Consulting</span><i>&starf;</i>
        </div>
    </div>

    <!-- ================= SERVICES ================= -->
    <section class="next-section next-services-list" id="services">
        <div class="next-wrap">
            <div class="next-section-head next-animate">
                <p class="next-eyebrow"><span></span>The catalogue</p>
                <h2>Six ways we<br><em>move you forward.</em></h2>
                <p class="next-intro">Every engagement is scoped honestly and priced transparently. The figures below are honest starting points, not bait.</p>
            </div>
            <div class="next-service-cards">
                <?php foreach ($services as $i => $s): ?>
                    <article class="next-service-card next-animate" style="--stagger:<?= $i ?>">
                        <div class="next-service-top">
                            <span class="next-service-num"><?= $s['num'] ?></span>
                            <span class="next-service-tag"><?= $s['tag'] ?></span>
                        </div>
                        <h3><?= $s['name'] ?></h3>
                        <p><?= $s['desc'] ?></p>
                        <ul class="next-service-points">
                            <?php foreach ($s['points'] as $p): ?><li><?= $p ?></li><?php endforeach; ?>
                        </ul>
                        <div class="next-service-foot">
                            <span class="next-service-price">From <b>&#8377;<?= number_format($s['from']) ?></b></span>
                            <a href="contact.php" class="next-service-link" aria-label="Discuss <?= $s['name'] ?>">Discuss this <b>&nearr;</b></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ================= ENGAGEMENT ================= -->
    <section class="next-dark-panel" id="engagement">
        <div class="next-wrap next-process next-animate">
            <div>
                <p class="next-eyebrow"><span></span>How an engagement runs</p>
                <h2>Four steps.<br><em>No surprises.</em></h2>
            </div>
            <p class="next-process-copy">The same path every time, whether we are building a landing page or replacing a core system. You always know what happens next.</p>
        </div>
        <div class="next-wrap next-steps next-animate">
            <?php foreach ($engagements as $e): ?>
                <article><span><?= $e[0] ?> / <?= $e[1] ?></span>
                    <h3><?= $e[1] ?> comes first.</h3>
                    <p><?= $e[2] ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- ================= COMPARISON / TIERS ================= -->
    <section class="next-section next-tiers" id="tiers">
        <div class="next-wrap">
            <div class="next-live-head next-animate">
                <p class="next-eyebrow"><span></span>Ways to work together</p>
                <h2>Pick a <em>starting point.</em></h2>
            </div>
            <div class="next-tiers-grid next-animate">
                <article class="next-tier">
                    <span class="next-tier-name">Project</span>
                    <div class="next-tier-price"><strong>&#8377;4.5L</strong><span>starting scope</span></div>
                    <p>One clear build — a website, a portal or a migration — delivered end-to-end with a fixed plan.</p>
                    <ul>
                        <li>Fixed scope &amp; timeline</li>
                        <li>Full handover of code &amp; assets</li>
                        <li>30 days of included support</li>
                    </ul>
                    <a href="contact.php" class="next-button next-button-brand next-ripple">Scope a project <b>&nearr;</b></a>
                </article>
                <article class="next-tier next-tier-featured">
                    <span class="next-tier-flag">Most chosen</span>
                    <span class="next-tier-name">Partner</span>
                    <div class="next-tier-price"><strong>&#8377;1L</strong><span>per month</span></div>
                    <p>A senior team on retainer — building, improving and maintaining your systems month after month.</p>
                    <ul>
                        <li>Priority engineering hours</li>
                        <li>Continuous improvements</li>
                        <li>Named engineer &amp; 24/7 cover</li>
                    </ul>
                    <a href="contact.php" class="next-button next-button-brand next-ripple">Become a partner <b>&nearr;</b></a>
                </article>
                <article class="next-tier">
                    <span class="next-tier-name">Advice</span>
                    <div class="next-tier-price"><strong>&#8377;15k</strong><span>per session</span></div>
                    <p>A 90-minute working session with a senior engineer to pressure-test an idea or plan.</p>
                    <ul>
                        <li>Architecture &amp; tool reviews</li>
                        <li>Cost &amp; risk assessment</li>
                        <li>Written recommendations</li>
                    </ul>
                    <a href="contact.php" class="next-button next-button-dark next-ripple">Book a session <b>&nearr;</b></a>
                </article>
            </div>
        </div>
    </section>

    <!-- ================= FAQ ================= -->
    <section class="next-dark-panel next-faq-panel" id="faq">
        <div class="next-wrap next-faq-inner next-animate">
            <div>
                <p class="next-eyebrow"><span></span>Service questions</p>
                <h2>Before you<br><em>ask.</em></h2>
            </div>
            <div class="next-faq">
                <details class="next-faq-item" open>
                    <summary>Can you take over an existing project?<i>+</i></summary>
                    <p>Yes. We start with a short technical audit, tell you honestly what we found, and quote the rescue — not a rewrite you don't need.</p>
                </details>
                <details class="next-faq-item">
                    <summary>How is pricing decided?<i>+</i></summary>
                    <p>By scope, not by hours billed. You approve a fixed plan and price before we build, and it doesn't drift without your sign-off.</p>
                </details>
                <details class="next-faq-item">
                    <summary>Do you offer ongoing support?<i>+</i></summary>
                    <p>Every build includes 30 days of support. After that, Partner plans cover monitoring, updates and improvements.</p>
                </details>
                <details class="next-faq-item">
                    <summary>What technologies do you use?<i>+</i></summary>
                    <p>PHP/MySQL, modern JavaScript, and major cloud platforms — chosen for longevity and easy handover, not fashion.</p>
                </details>
            </div>
        </div>
    </section>

    <!-- ================= CTA ================= -->
    <section class="next-contact">
        <div class="next-wrap next-animate">
            <p class="next-eyebrow"><span></span>Next step</p>
            <h2>See something that<br><em>fits?</em></h2>
            <a href="contact.php" class="next-button next-button-dark next-ripple">Tell us about your project <b>&nearr;</b></a>
        </div>
    </section>
</div>

<script>
    (function() {
        'use strict';
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* ---- scroll reveal (staggered for service cards) ---- */
        var revealEls = document.querySelectorAll('.next-animate');
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
                        var st = e.target.style.getPropertyValue('--stagger');
                        if (st) e.target.style.transitionDelay = (parseInt(st, 10) % 3) * 0.1 + 's';
                        e.target.classList.add('next-in');
                        io.unobserve(e.target);
                    }
                });
            }, {
                threshold: 0.12
            });
            revealEls.forEach(function(el) {
                io.observe(el);
            });
        } else {
            revealEls.forEach(function(el) {
                el.classList.add('next-in');
            });
        }

        /* ---- hero counters handled by shared assets/counters.js ---- */

        /* ---- button ripple ---- */
        document.querySelectorAll('.next-ripple').forEach(function(btn) {
            btn.addEventListener('click', function(ev) {
                var r = btn.getBoundingClientRect(),
                    d = Math.max(r.width, r.height) * 2,
                    s = document.createElement('span');
                s.className = 'next-ink';
                s.style.width = s.style.height = d + 'px';
                s.style.left = (ev.clientX - r.left - d / 2) + 'px';
                s.style.top = (ev.clientY - r.top - d / 2) + 'px';
                btn.appendChild(s);
                setTimeout(function() {
                    s.remove();
                }, 700);
            });
        });

        /* ---- spotlight follow on service cards ---- */
        if (!reduce && window.matchMedia('(hover: hover)').matches) {
            document.querySelectorAll('.next-service-card').forEach(function(card) {
                card.addEventListener('mousemove', function(e) {
                    var r = card.getBoundingClientRect();
                    card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
                    card.style.setProperty('--my', (e.clientY - r.top) + 'px');
                });
            });
        }
    })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>