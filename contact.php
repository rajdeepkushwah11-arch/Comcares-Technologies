<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

$pageTitle = 'Contact';
$pageDesc = 'Get in touch with Comcares Technology — tell us about the website, software or system you are building and we will reply within one working day.';
require __DIR__ . '/includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errors[] = 'Your session expired. Please go back and try again.';
    }
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $topic   = trim($_POST['topic'] ?? 'General enquiry');
    $message = trim($_POST['message'] ?? '');

    if ($name === '')        $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($message === '')     $errors[] = 'Please enter a message.';

    if (!$errors) {
        // Save the message to the MySQL contact_messages table
        require_once __DIR__ . '/includes/db.php';
        try {
            $stmt = db()->prepare('INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, "[{$topic}] " . $message]);
                $_SESSION['contact_done'] = true;
                header('Location: contact.php?sent=1');
                exit;
            } catch (Throwable $e) {
            // DB unreachable (e.g. MySQL not running in this environment).
            // The message is not lost: keep the user's input and show a clear error.
            $errors[] = 'Something went wrong saving your message. Please try again.';
        }
    }
}

if (!empty($_SESSION['contact_done'])) {
    $success = true;
    unset($_SESSION['contact_done']);
}

$topics = ['New website', 'Custom software', 'School Sathi demo', 'Support & maintenance', 'General enquiry'];
$requestedTopic = trim($_GET['topic'] ?? '');
$requestedMessage = trim($_GET['message'] ?? '');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && in_array($requestedTopic, $topics, true)) {
    $_POST['topic'] = $requestedTopic;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $requestedMessage !== '') {
    $_POST['message'] = substr($requestedMessage, 0, 1000);
}
?>

<div class="next-site next-contact-page">
    <!-- ================= HERO ================= -->
    <section class="next-hero next-contact-hero" id="top">
        <div class="next-grid" aria-hidden="true"></div>
        <div class="next-orb next-orb-one" aria-hidden="true"></div>
        <div class="next-orb next-orb-two" aria-hidden="true"></div>
        <div class="next-wrap next-hero-inner">
            <p class="next-eyebrow"><span></span>Contact / Start a conversation</p>
            <h1>Tell us what<br><em>you're building.</em></h1>
            <div class="next-hero-bottom">
                <p>Share a little about the idea, the challenge, or the system you want to improve. We reply to every message within one working day.</p>
                <div class="next-actions-row">
                    <div class="next-actions">
                        <a href="#form" class="next-button next-button-brand next-ripple">Write to us <b>&darr;</b></a>
                        <a href="mailto:hello@comcares.com" class="next-text-link">hello@comcares.com</a>
                    </div>
                    <figure class="next-actions-visual" aria-hidden="true">
                        <img src="images/hero-dashboard.svg" alt="" width="420" height="240" loading="lazy">
                    </figure>
                </div>
            </div>
        </div>
        <a href="#form" class="next-scroll">Scroll to the form <i></i></a>
    </section>

    <!-- ================= FORM ================= -->
    <section class="next-section next-contact-main" id="form">
        <div class="next-wrap next-contact-layout">
            <!-- aside -->
            <aside class="next-contact-aside next-animate">
                <p class="next-eyebrow"><span></span>A good place to start</p>
                <h2>What would <em>progress</em> look like?</h2>
                <p class="next-contact-note">Tell us your goals, your current workflow, and the people your solution needs to help. We use that context to understand where to begin.</p>
                <ul class="next-contact-channels">
                    <li><span>Email</span><a href="mailto:hello@comcares.com">hello@comcares.com</a></li>
                    <li><span>Response time</span><b>Within 1 working day</b></li>
                    <li><span>Availability</span><b>Mon&ndash;Sat, 9:00&ndash;18:00 IST</b></li>
                    <li><span>Support</span><b>24/7 for Partner clients</b></li>
                </ul>
                <div class="next-contact-footline"><span>WEB / SOFTWARE / CLOUD / SUPPORT</span><span>CC&mdash;03</span></div>
            </aside>

            <!-- form card -->
            <div class="next-form-card next-animate">
                <?php if ($success): ?>
                    <div class="next-alert next-alert-ok">
                        <b>&check;</b>
                        <div>
                            <strong>Message received.</strong>
                            <p>Thank you — we'll reply within one working day.</p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php foreach ($errors as $error): ?>
                    <div class="next-alert next-alert-err"><b>!</b>
                        <div><strong>Hold on.</strong>
                            <p><?= htmlspecialchars($error) ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <form method="post" action="contact.php" class="next-form" id="contactForm" novalidate>
                    <?= csrfField() ?>
                    <div class="next-form-row">
                        <div class="next-field">
                            <input type="text" id="name" name="name" placeholder=" " value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            <label for="name">Your name</label>
                            <i class="next-field-icon" aria-hidden="true">&#9679;</i>
                        </div>
                        <div class="next-field">
                            <input type="email" id="email" name="email" placeholder=" " value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            <label for="email">Email address</label>
                            <i class="next-field-icon" aria-hidden="true">&#9993;</i>
                        </div>
                    </div>

                    <div class="next-form-row next-form-row-full">
                        <span class="next-form-label">What's this about?</span>
                        <div class="next-topic-pills">
                            <?php foreach ($topics as $i => $t): ?>
                                <button type="button" class="next-topic-pill<?= ($_POST['topic'] ?? $topics[0]) === $t ? ' is-active' : '' ?>" data-topic="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></button>
                            <?php endforeach; ?>
                        </div>
                        <input type="hidden" name="topic" id="topicInput" value="<?= htmlspecialchars($_POST['topic'] ?? $topics[0]) ?>">
                    </div>

                    <div class="next-field">
                        <textarea id="message" name="message" rows="6" placeholder=" " required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        <label for="message">What are you working on?</label>
                    </div>

                    <div class="next-form-foot">
                        <span class="next-form-hint">No newsletters. No sales calls. Just a reply.</span>
                        <button type="submit" class="next-button next-button-brand next-ripple">Send your message <b>&nearr;</b></button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- ================= CTA ================= -->
    <section class="next-contact next-contact-end">
        <div class="next-wrap next-animate">
            <p class="next-eyebrow"><span></span>Prefer talking?</p>
            <h2>Ideas move faster<br><em>out loud.</em></h2>
            <a href="mailto:hello@comcares.com" class="next-button next-button-dark next-ripple">Email us directly <b>&nearr;</b></a>
        </div>
    </section>
</div>

<script>
    (function() {
        'use strict';
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        /* ---- scroll reveal ---- */
        var revealEls = document.querySelectorAll('.next-animate');
        if ('IntersectionObserver' in window) {
            var io = new IntersectionObserver(function(entries) {
                entries.forEach(function(e) {
                    if (e.isIntersecting) {
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

        /* ---- topic pills ---- */
        var pills = document.querySelectorAll('.next-topic-pill'),
            topicInput = document.getElementById('topicInput');
        pills.forEach(function(p) {
            p.addEventListener('click', function() {
                pills.forEach(function(q) {
                    q.classList.remove('is-active');
                });
                p.classList.add('is-active');
                topicInput.value = p.getAttribute('data-topic');
            });
        });

        /* ---- float-label + live validation ---- */
        var form = document.getElementById('contactForm');
        form.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function(input) {
            input.addEventListener('blur', function() {
                if (input.value.trim() !== '' && input.checkValidity()) input.classList.add('is-valid');
                else input.classList.remove('is-valid');
            });
            input.addEventListener('input', function() {
                input.classList.remove('is-invalid');
            });
        });

        /* ---- inline validation before submit (keeps PHP validation as backstop) ---- */
        form.addEventListener('submit', function(ev) {
            var ok = true;
            var firstInvalid = null;
            form.querySelectorAll('input[type="text"], input[type="email"], textarea').forEach(function(input) {
                if (!input.checkValidity()) {
                    ok = false;
                    input.classList.add('is-invalid');
                    if (!firstInvalid) firstInvalid = input;
                }
            });
            if (firstInvalid) firstInvalid.focus();
            if (!ok && !reduce) {
                form.classList.remove('next-shake');
                void form.offsetWidth; /* restart animation */
                form.classList.add('next-shake');
                ev.preventDefault();
            }
            /* if valid, let the PHP form submit normally */
        });
    })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
