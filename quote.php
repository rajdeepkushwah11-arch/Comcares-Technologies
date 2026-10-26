<?php
session_start();
$pageTitle = 'Get a Quote';
require __DIR__ . '/includes/header.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = trim($_POST['name'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $service  = trim($_POST['service'] ?? '');
    $budget   = trim($_POST['budget'] ?? '');
    $timeline = trim($_POST['timeline'] ?? '');
    $details  = trim($_POST['details'] ?? '');

    if ($name === '')  $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if ($service === '') $errors[] = 'Please tell us which service you need a quote for.';
    if ($details === '') $errors[] = 'Please describe your project briefly.';

    if (!$errors) {
        require_once __DIR__ . '/includes/db.php';
        try {
            $stmt = db()->prepare(
                'INSERT INTO quotations (user_id, name, email, phone, service, budget, timeline, details)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $name,
                $email,
                $phone ?: null,
                $service,
                $budget ?: null,
                $timeline ?: null,
                $details,
            ]);
            $success = true;
        } catch (Throwable $e) {
            $errors[] = 'Something went wrong saving your quote request. Please try again.';
        }
    }
}

$services = [
    'Website design & development',
    'Web application / portal',
    'School Sathi (school management)',
    'Maintenance & hosting support',
    'IT consulting',
    'Something else',
];
$budgets  = ['Under ₹25,000', '₹25,000 – ₹1,00,000', '₹1,00,000 – ₹5,00,000', 'Above ₹5,00,000', 'Not sure yet'];
$timelines = ['ASAP', 'Within 1 month', '1–3 months', '3–6 months', 'Flexible'];
?>

<div class="next-site next-contact-page">
    <section class="next-hero next-contact-hero" id="top">
        <div class="next-grid" aria-hidden="true"></div>
        <div class="next-orb next-orb-one" aria-hidden="true"></div>
        <div class="next-orb next-orb-two" aria-hidden="true"></div>
        <div class="next-wrap next-hero-inner">
            <p class="next-eyebrow"><span></span>Quotation / Free estimate</p>
            <h1>Get a clear,<br><em>honest quote.</em></h1>
            <div class="next-hero-bottom">
                <p>Tell us what you need and we'll reply with a detailed, no-obligation estimate within one working day.</p>
                <div class="next-actions">
                    <a href="#form" class="next-button next-button-brand next-ripple">Request a quote <b>&darr;</b></a>
                    <a href="contact.php" class="next-text-link">Or just send a message</a>
                </div>
            </div>
        </div>
        <a href="#form" class="next-scroll">Scroll to the form <i></i></a>
    </section>

    <section class="next-section next-contact-main" id="form">
        <div class="next-wrap next-contact-layout">
            <aside class="next-contact-aside next-animate">
                <p class="next-eyebrow"><span></span>How it works</p>
                <h2>Three steps to <em>your estimate.</em></h2>
                <p class="next-contact-note">Every quote request lands directly in our admin panel — nothing is lost and nothing is shared.</p>
                <ul class="next-contact-channels">
                    <li><span>1. You describe</span><b>Project, budget, timeline</b></li>
                    <li><span>2. We scope</span><b>A short discovery call</b></li>
                    <li><span>3. You receive</span><b>A written, fixed estimate</b></li>
                </ul>
                <div class="next-contact-footline"><span>WEB / SOFTWARE / CLOUD / SUPPORT</span><span>QT&mdash;01</span></div>
            </aside>

            <div class="next-form-card next-animate">
                <?php if ($success): ?>
                    <div class="next-alert next-alert-ok">
                        <b>&check;</b>
                        <div>
                            <strong>Quote request received.</strong>
                            <p>Thank you — we'll send your estimate within one working day.</p>
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

                <form method="post" action="quote.php" class="next-form" id="quoteForm" novalidate>
                    <div class="next-form-row">
                        <div class="next-field">
                            <input type="text" id="name" name="name" placeholder=" " value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                            <label for="name">Your name</label>
                        </div>
                        <div class="next-field">
                            <input type="email" id="email" name="email" placeholder=" " value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                            <label for="email">Email address</label>
                        </div>
                    </div>

                    <div class="next-form-row">
                        <div class="next-field">
                            <input type="tel" id="phone" name="phone" placeholder=" " value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            <label for="phone">Phone (optional)</label>
                        </div>
                        <div class="next-field">
                            <select id="service" name="service" required>
                                <option value="">Choose a service&hellip;</option>
                                <?php foreach ($services as $s): ?>
                                    <option value="<?= $s ?>" <?= ($_POST['service'] ?? '') === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="service">Service needed</label>
                        </div>
                    </div>

                    <div class="next-form-row">
                        <div class="next-field">
                            <select id="budget" name="budget">
                                <option value="">Budget range&hellip;</option>
                                <?php foreach ($budgets as $b): ?>
                                    <option value="<?= $b ?>" <?= ($_POST['budget'] ?? '') === $b ? 'selected' : '' ?>><?= $b ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="budget">Budget (optional)</label>
                        </div>
                        <div class="next-field">
                            <select id="timeline" name="timeline">
                                <option value="">Timeline&hellip;</option>
                                <?php foreach ($timelines as $t): ?>
                                    <option value="<?= $t ?>" <?= ($_POST['timeline'] ?? '') === $t ? 'selected' : '' ?>><?= $t ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="timeline">Timeline (optional)</label>
                        </div>
                    </div>

                    <div class="next-field">
                        <textarea id="details" name="details" rows="6" placeholder=" " required><?= htmlspecialchars($_POST['details'] ?? '') ?></textarea>
                        <label for="details">Describe your project</label>
                    </div>

                    <div class="next-form-foot">
                        <span class="next-form-hint">Free &middot; no obligation &middot; reply in 1 working day.</span>
                        <button type="submit" class="next-button next-button-brand next-ripple">Request my quote <b>&nearr;</b></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    (function() {
        'use strict';
        var form = document.getElementById('quoteForm');
        form.addEventListener('submit', function(ev) {
            var ok = true;
            form.querySelectorAll('input, select, textarea').forEach(function(input) {
                if (!input.checkValidity()) {
                    ok = false;
                    input.classList.add('is-invalid');
                }
            });
            if (!ok) ev.preventDefault();
        });
        form.querySelectorAll('input, select, textarea').forEach(function(input) {
            input.addEventListener('input', function() {
                input.classList.remove('is-invalid');
            });
        });
    })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>