<?php
session_start();
$pageTitle = 'Order';
require __DIR__ . '/includes/header.php';

$errors = [];
$success = false;

$products = [
    'School Sathi'                  => ['plans' => ['Basic', 'Standard', 'Enterprise']],
    'School Sathi Hub'              => ['plans' => ['Portal access', 'Full suite']],
    'Business Website / Web App'    => ['plans' => ['Starter site', 'Custom build', 'E-commerce']],
    'IT Support & Maintenance Plan' => ['plans' => ['Monthly', 'Quarterly', 'Annual']],
];

$selected = trim($_GET['product'] ?? '');
if (!isset($products[$selected])) {
    // Accept "Business Websites & Web Apps" style names from products.php links
    $map = [
        'Business Websites & Web Apps'  => 'Business Website / Web App',
        'IT Support & Maintenance'      => 'IT Support & Maintenance Plan',
    ];
    if (isset($map[$selected])) $selected = $map[$selected];
    if (!isset($products[$selected])) $selected = '';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product      = trim($_POST['product'] ?? '');
    $plan         = trim($_POST['plan'] ?? '');
    $name         = trim($_POST['name'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $organisation = trim($_POST['organisation'] ?? '');
    $quantity     = max(1, (int)($_POST['quantity'] ?? 1));
    $message      = trim($_POST['message'] ?? '');

    if (!isset($products[$product])) $errors[] = 'Please choose a product.';
    if ($name === '')  $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';

    if (!$errors) {
        require_once __DIR__ . '/includes/db.php';
        try {
            $stmt = db()->prepare(
                'INSERT INTO purchases (user_id, product, plan, name, email, phone, organisation, quantity, message)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $product,
                $plan ?: null,
                $name,
                $email,
                $phone ?: null,
                $organisation ?: null,
                $quantity,
                $message ?: null,
            ]);
            $success = true;
        } catch (Throwable $e) {
            $errors[] = 'Something went wrong saving your order. Please try again.';
        }
    }
}
?>
<div class="next-site next-contact-page">
    <section class="next-hero next-contact-hero" id="top">
        <div class="next-grid" aria-hidden="true"></div>
        <div class="next-orb next-orb-one" aria-hidden="true"></div>
        <div class="next-orb next-orb-two" aria-hidden="true"></div>
        <div class="next-wrap next-hero-inner">
            <p class="next-eyebrow"><span></span>Order / Start your purchase</p>
            <h1>Get <em><?= $selected ? htmlspecialchars($selected) : 'our products' ?></em><br>working for you.</h1>
            <div class="next-hero-bottom">
                <p>Place an order request and our team confirms pricing, onboarding and timelines directly with you — usually the same day.</p>
                <div class="next-actions">
                    <a href="#form" class="next-button next-button-brand next-ripple">Start my order <b>&darr;</b></a>
                    <a href="products.php" class="next-text-link">Back to products</a>
                </div>
            </div>
        </div>
        <a href="#form" class="next-scroll">Scroll to the form <i></i></a>
    </section>

    <section class="next-section next-contact-main" id="form">
        <div class="next-wrap next-contact-layout">
            <aside class="next-contact-aside next-animate">
                <p class="next-eyebrow"><span></span>What happens next</p>
                <h2>Simple, <em>human onboarding.</em></h2>
                <p class="next-contact-note">Your order request is saved securely in our system. No payment is taken on this page — we confirm details with you first.</p>
                <ul class="next-contact-channels">
                    <li><span>1. Request</span><b>You submit this form</b></li>
                    <li><span>2. Confirm</span><b>We call / email to finalise scope</b></li>
                    <li><span>3. Onboard</span><b>Setup, training and go-live</b></li>
                </ul>
                <div class="next-contact-footline"><span>WEB / SOFTWARE / CLOUD / SUPPORT</span><span>OR&mdash;01</span></div>
            </aside>

            <div class="next-form-card next-animate">
                <?php if ($success): ?>
                    <div class="next-alert next-alert-ok">
                        <b>&check;</b>
                        <div>
                            <strong>Order request received.</strong>
                            <p>Thank you — our team will contact you shortly to confirm the details.</p>
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

                <form method="post" action="order.php" class="next-form" id="orderForm" novalidate>
                    <div class="next-form-row">
                        <div class="next-field">
                            <select id="product" name="product" required>
                                <option value="">Choose a product&hellip;</option>
                                <?php foreach ($products as $pname => $meta): ?>
                                    <option value="<?= $pname ?>" <?= ($_POST['product'] ?? $selected) === $pname ? 'selected' : '' ?>><?= $pname ?></option>
                                <?php endforeach; ?>
                            </select>
                            <label for="product">Product</label>
                        </div>
                        <div class="next-field">
                            <select id="plan" name="plan">
                                <option value="">Plan (optional)&hellip;</option>
                                <?php
                                $currentProduct = $_POST['product'] ?? $selected;
                                if (isset($products[$currentProduct])) {
                                    foreach ($products[$currentProduct]['plans'] as $pl) {
                                        $sel = ($_POST['plan'] ?? '') === $pl ? 'selected' : '';
                                        echo '<option value="' . $pl . '" ' . $sel . '>' . $pl . '</option>';
                                    }
                                }
                                ?>
                            </select>
                            <label for="plan">Plan</label>
                        </div>
                    </div>

                    <div class="next-form-row">
                        <div class="next-field">
                            <input type="text" id="name" name="name" placeholder=" " value="<?= htmlspecialchars($_POST['name'] ?? ($_SESSION['user_name'] ?? '')) ?>" required>
                            <label for="name">Your name</label>
                        </div>
                        <div class="next-field">
                            <input type="email" id="email" name="email" placeholder=" " value="<?= htmlspecialchars($_POST['email'] ?? ($_SESSION['user_email'] ?? '')) ?>" required>
                            <label for="email">Email address</label>
                        </div>
                    </div>

                    <div class="next-form-row">
                        <div class="next-field">
                            <input type="tel" id="phone" name="phone" placeholder=" " value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                            <label for="phone">Phone (optional)</label>
                        </div>
                        <div class="next-field">
                            <input type="text" id="organisation" name="organisation" placeholder=" " value="<?= htmlspecialchars($_POST['organisation'] ?? '') ?>">
                            <label for="organisation">School / company (optional)</label>
                        </div>
                    </div>

                    <div class="next-form-row">
                        <div class="next-field">
                            <input type="number" id="quantity" name="quantity" min="1" max="999" value="<?= htmlspecialchars($_POST['quantity'] ?? '1') ?>">
                            <label for="quantity">Licences / quantity</label>
                        </div>
                        <div></div>
                    </div>

                    <div class="next-field">
                        <textarea id="message" name="message" rows="5" placeholder=" "><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                        <label for="message">Anything we should know? (optional)</label>
                    </div>

                    <div class="next-form-foot">
                        <span class="next-form-hint">No upfront payment &middot; we confirm before anything begins.</span>
                        <button type="submit" class="next-button next-button-brand next-ripple">Place order request <b>&nearr;</b></button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
    (function() {
        'use strict';
        var form = document.getElementById('orderForm');
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

        /* keep plan options in sync with the chosen product (reload preserves entries) */
        document.getElementById('product').addEventListener('change', function() {
            form.querySelector('input[name="name"]').focus();
        });
    })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>