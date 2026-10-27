<?php
$pageTitle = 'Products';
require __DIR__ . '/includes/header.php';

// Product list — our own products, led by School Sathi
$products = [
    [
        'num' => '01',
        'name' => 'School Sathi',
        'tag' => 'Flagship',
        'desc' => 'A complete school management platform — admissions, fees, attendance, timetables, exams, staff and parent communication in one place.',
        'points' => ['Admissions &amp; fee management', 'Attendance &amp; timetables', 'Parent &amp; staff communication'],
        'url' => 'https://schoolsathi.in/hub/home',
        'external' => true,
        'accent' => '#7ddba3',
    ],
    [
        'num' => '02',
        'name' => 'School Sathi Hub',
        'tag' => 'Portal',
        'desc' => 'The central hub of School Sathi where administrators, teachers and parents log in to track student progress, notices and day-to-day activity.',
        'points' => ['Role-based dashboards', 'Student progress tracking', 'Notices &amp; daily activity'],
        'url' => 'https://schoolsathi.in/hub/home',
        'external' => true,
        'accent' => '#a9bce8',
    ],
    [
        'num' => '03',
        'name' => 'Business Websites &amp; Web Apps',
        'tag' => 'Build',
        'desc' => 'Custom company websites, dashboards, booking systems and portals built with modern PHP/MySQL stacks.',
        'points' => ['Modern PHP/MySQL stack', 'Dashboards &amp; portals', 'Booking &amp; enquiry systems'],
        'url' => 'contact.php',
        'external' => false,
        'accent' => '#e8b36a',
    ],
    [
        'num' => '04',
        'name' => 'IT Support &amp; Maintenance',
        'tag' => 'Support',
        'desc' => 'Ongoing care for your software — updates, backups, monitoring and priority support so systems stay online.',
        'points' => ['Updates &amp; security patches', 'Daily backups', 'Priority response line'],
        'url' => 'contact.php',
        'external' => false,
        'accent' => '#e88a7a',
    ],
];
?>

<div class="next-site next-products">
    <!-- ================= HERO ================= -->
    <section class="next-hero next-products-hero" id="top">
        <div class="next-grid" aria-hidden="true"></div>
        <div class="next-orb next-orb-one" aria-hidden="true"></div>
        <div class="next-orb next-orb-two" aria-hidden="true"></div>
        <div class="next-wrap next-hero-inner">
            <p class="next-eyebrow"><span></span>Products / Built by us</p>
            <h1>Products we<br><em>own and run.</em></h1>
            <div class="next-hero-bottom">
                <p>Beyond client work, we design, build and maintain our own products — starting with School Sathi, our complete school management platform.</p>
                <div class="next-actions-row">
                    <div class="next-actions">
                        <a href="https://schoolsathi.in/hub/home" target="_blank" rel="noopener" class="next-button next-button-brand next-ripple">Visit School Sathi <b>&nearr;</b></a>
                        <a href="#products" class="next-text-link">Browse products <b>&darr;</b></a>
                    </div>
                    <figure class="next-actions-visual" aria-hidden="true">
                        <img src="images/product-school.svg" alt="" width="420" height="240" loading="lazy">
                    </figure>
                </div>
            </div>
            <div class="next-hero-facts">
                <div class="next-fact"><strong data-count="4" data-suffix="">0</strong><span>Live products</span></div>
                <div class="next-fact"><strong data-count="12" data-suffix="+">0</strong><span>Schools onboard</span></div>
                <div class="next-fact"><strong data-count="24" data-suffix="/7">0</strong><span>Platform monitoring</span></div>
            </div>
        </div>
        <a href="#products" class="next-scroll">Scroll to explore <i></i></a>
    </section>

    <div class="next-ticker" role="region" aria-label="Products: School Sathi, School Sathi Hub, business websites, support plans" tabindex="0" data-scroll-duration="34">
        <div class="next-ticker-track" aria-hidden="true">
            <span>School Sathi</span><i>&starf;</i><span>School Sathi Hub</span><i>&starf;</i><span>Web apps</span><i>&starf;</i><span>Support plans</span><i>&starf;</i><span>Built in-house</span><i>&starf;</i>
            <span>School Sathi</span><i>&starf;</i><span>School Sathi Hub</span><i>&starf;</i><span>Web apps</span><i>&starf;</i><span>Support plans</span><i>&starf;</i><span>Built in-house</span><i>&starf;</i>
        </div>
    </div>

    <!-- ================= PRODUCTS ================= -->
    <section class="next-section next-products-list" id="products">
        <div class="next-wrap">
            <div class="next-section-head next-animate">
                <p class="next-eyebrow"><span></span>The line-up</p>
                <h2>Four products,<br><em>one philosophy.</em></h2>
                <p class="next-intro">Everything here runs on real schools and businesses today — built by the same team that answers the support line.</p>
            </div>
            <div class="next-product-cards">
                <?php foreach ($products as $i => $p): ?>
                    <article class="next-product-card next-animate" style="--stagger:<?= $i ?>; --tint:<?= $p['accent'] ?>">
                        <div class="next-product-top">
                            <span class="next-product-num"><?= $p['num'] ?></span>
                            <span class="next-product-tag"><?= $p['tag'] ?></span>
                        </div>
                        <div class="next-product-mark" aria-hidden="true"><i style="background:<?= $p['accent'] ?>"></i></div>
                        <h3><?= $p['name'] ?></h3>
                        <p><?= $p['desc'] ?></p>
                        <ul class="next-product-points">
                            <?php foreach ($p['points'] as $pt): ?><li><?= $pt ?></li><?php endforeach; ?>
                        </ul>
                        <div class="next-product-foot">
                            <a href="<?= $p['url'] ?>" class="next-service-link" aria-label="Explore <?= $p['name'] ?>"
                                <?= $p['external'] ? 'target="_blank" rel="noopener"' : '' ?>>Explore <?= $p['name'] ?> <b>&nearr;</b></a>
                            <a href="order.php?product=<?= str_replace('&', '&', $p['name']) ?>" class="next-service-link" style="margin-left:.9rem">Order <b>&nearr;</b></a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ================= SHOWCASE (dark) ================= -->
    <section class="next-dark-panel" id="showcase">
        <div class="next-wrap next-process next-animate">
            <div>
                <p class="next-eyebrow"><span></span>In the spotlight</p>
                <h2>School Sathi,<br><em>in everyday use.</em></h2>
            </div>
            <p class="next-process-copy">From morning attendance to term-end reports, School Sathi runs the school day for administrators, teachers and parents alike.</p>
        </div>
        <div class="next-wrap next-steps next-animate">
            <article><span>For administrators</span>
                <h3>Run the whole school.</h3>
                <p>Admissions, fees, staffing and compliance — visible on one screen, exportable in one click.</p>
            </article>
            <article><span>For teachers</span>
                <h3>Teach, not type.</h3>
                <p>Attendance in seconds, timetables that manage themselves, and marks that flow straight to reports.</p>
            </article>
            <article><span>For parents</span>
                <h3>Never miss a thing.</h3>
                <p>Notices, progress and fee receipts on the phone — no more chasing the school office.</p>
            </article>
        </div>
    </section>

    <!-- ================= CTA ================= -->
    <section class="next-contact">
        <div class="next-wrap next-animate">
            <p class="next-eyebrow"><span></span>Get a demo</p>
            <h2>Want to see it<br><em>in action?</em></h2>
            <a href="https://schoolsathi.in/hub/home" target="_blank" rel="noopener" class="next-button next-button-dark next-ripple">Open School Sathi Hub <b>&nearr;</b></a>
        </div>
    </section>
</div>

<script>
    (function() {
        'use strict';
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* ---- scroll reveal (staggered) ---- */
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

        /* ---- spotlight follow on product cards ---- */
        if (!reduce && window.matchMedia('(hover: hover)').matches) {
            document.querySelectorAll('.next-product-card').forEach(function(card) {
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