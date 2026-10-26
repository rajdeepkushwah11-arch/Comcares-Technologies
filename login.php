<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

$errors = [];
$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'login';
$mode = $mode === 'register' ? 'register' : 'login';
$oldEmail = '';
$oldName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    }
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $oldEmail = $email;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }

    if (!$errors && $mode === 'register') {
        $name = trim($_POST['name'] ?? '');
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $acceptedTerms = isset($_POST['terms']);
        $oldName = $name;
        if ($name === '') $errors[] = 'Please enter your full name.';
        if ($password !== $confirmPassword) $errors[] = 'Passwords do not match.';
        if (!$acceptedTerms) $errors[] = 'Please accept the account terms to continue.';
        if (!$errors) {
            $result = registerUser($name, $email, $password);
            if ($result['ok']) {
                loginUser($email, $password);
                header('Location: index.php?welcome=1');
                exit;
            }
            $errors[] = $result['error'];
        }
    }

    if (!$errors && $mode === 'login') {
        $result = loginUser($email, $password);
        if ($result['ok']) {
            header('Location: index.php');
            exit;
        }
        $errors[] = $result['error'];
    }
}

$flash = '';
if (!empty($_SESSION['auth_error'])) {
    $flash = $_SESSION['auth_error'];
    unset($_SESSION['auth_error']);
}

$pageTitle = $mode === 'register' ? 'Create your account' : 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-bg" aria-hidden="true">
        <span class="grid-overlay"></span>
        <span class="auth-beam"></span>
        <canvas id="particles" class="particles"></canvas>
    </div>

    <div class="auth-scene" id="authScene">
        <div class="auth-card <?= $mode === 'register' ? 'show-register' : '' ?>" id="authCard">
            <!-- ============ BRAND PANEL ============ -->
            <aside class="auth-brand">
                <div class="auth-brand-top">
                    <span class="auth-logo-badge">C</span>
                    <span class="auth-logo-name"><?= htmlspecialchars(SITE_NAME) ?></span>
                </div>
                <div class="auth-brand-copy">
                    <span class="auth-eyebrow" id="authEyebrow"><?= $mode === 'register' ? 'NEW CLIENT PORTAL' : 'CLIENT PORTAL' ?></span>
                    <h2 class="auth-brand-headline" id="authHeadline"><?= $mode === 'register' ? 'Your next project starts <em>here.</em>' : 'Technology built for <em>what\'s next.</em>' ?></h2>
                    <p class="auth-brand-sub" id="authBrandSub"><?= $mode === 'register' ? 'Create your secure workspace to begin collaborating with the Comcares team.' : 'Log in to manage your projects, track deliveries and stay updated with our studio.' ?></p>
                </div>
                <ul class="auth-brand-points">
                    <li><span>&starf;</span>End-to-end digital delivery</li>
                    <li><span>&starf;</span>Secure, session-based accounts</li>
                    <li><span>&starf;</span>24/7 support coverage</li>
                </ul>
                <div class="auth-brand-foot">
                    <span class="auth-live-dot"></span> Comcares Innovation Services Pvt. Ltd.
                </div>
            </aside>

            <!-- ============ FORM PANEL ============ -->
            <div class="auth-panel">
                <div class="auth-tabs" role="tablist">
                    <a href="login.php" class="auth-tab <?= $mode === 'login' ? 'is-active' : '' ?>" id="toLogin">Login</a>
                    <a href="login.php?mode=register" class="auth-tab <?= $mode === 'register' ? 'is-active' : '' ?>" id="toRegister">Create account</a>
                    <span class="auth-tab-glider <?= $mode === 'register' ? 'right' : '' ?>" aria-hidden="true"></span>
                </div>

                <!-- LOGIN FORM -->
                <div class="auth-view auth-view-login <?= $mode === 'login' ? 'is-active' : '' ?>">
                    <h2>Welcome <em>back</em></h2>
                    <p class="auth-sub">Good to see you again. Enter your details to continue.</p>

                    <?php foreach ($errors as $err): ?>
                        <div class="alert error shake"><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>
                    <?php if ($flash): ?>
                        <div class="alert success"><?= htmlspecialchars($flash) ?></div>
                    <?php endif; ?>

                    <form method="post" action="login.php" novalidate>
                        <input type="hidden" name="mode" value="login">
                        <?= csrfField() ?>
                        <div class="field">
                            <input type="email" name="email" placeholder=" " value="<?= htmlspecialchars($oldEmail) ?>" required>
                            <label>Email address</label>
                            <span class="field-icon">&#9993;</span>
                        </div>
                        <div class="field">
                            <input type="password" name="password" placeholder=" " id="loginPass" required>
                            <label>Password</label>
                            <span class="field-icon">&#128274;</span>
                            <button type="button" class="pass-toggle" data-target="loginPass" aria-label="Show password">&#128065;</button>
                        </div>
                        <div class="auth-row">
                            <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
                            <a href="forgot-password.php" class="auth-link">Forgot password?</a>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block auth-submit">
                            <span class="auth-submit-label">Login <b>&rarr;</b></span>
                            <span class="auth-submit-spin" aria-hidden="true"></span>
                        </button>
                    </form>

                    <p class="auth-switch">New to Comcares?
                        <a href="login.php?mode=register" class="auth-link strong">Create a free account</a>
                    </p>
                </div>

                <!-- REGISTER FORM -->
                <div class="auth-view auth-view-register <?= $mode === 'register' ? 'is-active' : '' ?>">
                    <h2>Join <em>Comcares</em></h2>
                    <p class="auth-sub">Create your client workspace. It takes less than a minute.</p>

                    <?php foreach ($errors as $err): ?>
                        <div class="alert error shake"><?= htmlspecialchars($err) ?></div>
                    <?php endforeach; ?>

                    <form method="post" action="login.php" novalidate>
                        <input type="hidden" name="mode" value="register">
                        <?= csrfField() ?>
                        <div class="field">
                            <input type="text" name="name" placeholder=" " value="<?= htmlspecialchars($oldName) ?>" required>
                            <label>Full name</label>
                            <span class="field-icon">&#128100;</span>
                        </div>
                        <div class="field">
                            <input type="email" name="email" placeholder=" " value="<?= htmlspecialchars($oldEmail) ?>" required>
                            <label>Email address</label>
                            <span class="field-icon">&#9993;</span>
                        </div>
                        <div class="field">
                            <input type="password" name="password" placeholder=" " id="regPass" required minlength="8">
                            <label>Password (min 8 characters)</label>
                            <span class="field-icon">&#128274;</span>
                            <button type="button" class="pass-toggle" data-target="regPass" aria-label="Show password">&#128065;</button>
                        </div>
                        <div class="pass-strength" aria-hidden="true"><i></i></div>
                        <p class="password-hint" id="passwordHint">Use 8+ characters with a mix of letters, numbers, and symbols.</p>
                        <div class="field">
                            <input type="password" name="confirm_password" placeholder=" " id="confirmPass" required minlength="8">
                            <label>Confirm password</label>
                            <span class="field-icon">&#128274;</span>
                            <button type="button" class="pass-toggle" data-target="confirmPass" aria-label="Show password">&#128065;</button>
                        </div>
                        <label class="terms-check"><input type="checkbox" name="terms" required> <span>I agree to the <a href="contact.php">account terms</a> and privacy policy.</span></label>
                        <button type="submit" class="btn btn-primary btn-block auth-submit">
                            <span class="auth-submit-label">Create account <b>&rarr;</b></span>
                            <span class="auth-submit-spin" aria-hidden="true"></span>
                        </button>
                    </form>

                    <p class="auth-switch">Already registered?
                        <a href="login.php" class="auth-link strong">Log in instead</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <a href="index.php" class="auth-back-link">&larr; Back to website</a>
</div>

<script>
    (function() {
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Staggered field reveal on load
        document.querySelectorAll('.auth-view.is-active .field, .auth-view.is-active .pass-strength, .auth-view.is-active .password-hint, .auth-view.is-active .terms-check, .auth-view.is-active .auth-row, .auth-view.is-active .auth-submit, .auth-view.is-active .auth-switch, .auth-brand-top, .auth-brand-copy, .auth-brand-points li, .auth-brand-foot').forEach(function(el, i) {
            el.style.animationDelay = (0.08 + i * 0.07) + 's';
            el.classList.add('auth-reveal');
        });

        // Tab glider + view switch
        var tabs = document.querySelectorAll('.auth-tab');
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                var goingToRegister = tab.id === 'toRegister';
                if (tab.classList.contains('is-active')) { return; }
                tabs.forEach(function(t) { t.classList.remove('is-active'); });
                tab.classList.add('is-active');
                var glider = document.querySelector('.auth-tab-glider');
                if (glider) { glider.classList.toggle('right', goingToRegister); }
                var loginView = document.querySelector('.auth-view-login');
                var regView = document.querySelector('.auth-view-register');
                if (loginView && regView) {
                    loginView.classList.toggle('is-active', !goingToRegister);
                    regView.classList.toggle('is-active', goingToRegister);
                }
                var eyebrow = document.getElementById('authEyebrow');
                var headline = document.getElementById('authHeadline');
                var brandSub = document.getElementById('authBrandSub');
                if (eyebrow && headline && brandSub) {
                    eyebrow.textContent = goingToRegister ? 'NEW CLIENT PORTAL' : 'CLIENT PORTAL';
                    headline.innerHTML = goingToRegister ? 'Your next project starts <em>here.</em>' : 'Technology built for <em>what\'s next.</em>';
                    brandSub.textContent = goingToRegister ? 'Create your secure workspace to begin collaborating with the Comcares team.' : 'Log in to manage your projects, track deliveries and stay updated with our studio.';
                }
                // Update the URL without reloading so the tab state is shareable
                if (window.history && window.history.replaceState) {
                    window.history.replaceState(null, '', goingToRegister ? 'login.php?mode=register' : 'login.php');
                }
            });
        });

        // Show / hide password
        document.querySelectorAll('.pass-toggle').forEach(function(btn) {
            btn.addEventListener('click', function() {
                var input = document.getElementById(btn.getAttribute('data-target'));
                if (!input) return;
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.classList.toggle('is-on', show);
                input.focus();
            });
        });

        // Live password strength meter
        var regPass = document.getElementById('regPass');
        var meter = document.querySelector('.pass-strength');
        if (regPass && meter) {
            regPass.addEventListener('input', function() {
                var v = regPass.value;
                var score = 0;
                if (v.length >= 8) score++;
                if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
                if (/\d/.test(v)) score++;
                if (/[^A-Za-z0-9]/.test(v)) score++;
                meter.className = 'pass-strength s' + score;
            });
        }

        // Button loading spinner on submit
        document.querySelectorAll('.auth-submit').forEach(function(btn) {
            btn.closest('form').addEventListener('submit', function() {
                btn.classList.add('is-loading');
                btn.disabled = true;
            });
        });

        // Subtle parallax tilt following the mouse
        var scene = document.getElementById('authScene');
        var card = document.getElementById('authCard');
        if (scene && card && !reduce && window.matchMedia('(hover: hover)').matches) {
            scene.addEventListener('mousemove', function(e) {
                var r = scene.getBoundingClientRect();
                var rx = ((e.clientY - r.top) / r.height - 0.5) * -3;
                var ry = ((e.clientX - r.left) / r.width - 0.5) * 4;
                card.style.transform = 'rotateX(' + rx + 'deg) rotateY(' + ry + 'deg)';
            });
            scene.addEventListener('mouseleave', function() {
                card.style.transform = '';
            });
        }

        // Floating particles background (respect reduced motion)
        var canvas = document.getElementById('particles');
        if (canvas && !reduce) {
            var ctx = canvas.getContext('2d');
            var dots = [];
            var resize = function() {
                canvas.width = canvas.offsetWidth;
                canvas.height = canvas.offsetHeight;
            };
            resize();
            window.addEventListener('resize', resize);
            for (var i = 0; i < 45; i++) {
                dots.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    r: Math.random() * 1.6 + 0.4,
                    vx: (Math.random() - 0.5) * 0.25,
                    vy: (Math.random() - 0.5) * 0.25,
                    a: Math.random() * 0.5 + 0.15
                });
            }
            (function tick() {
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                for (var i = 0; i < dots.length; i++) {
                    var d = dots[i];
                    d.x += d.vx; d.y += d.vy;
                    if (d.x < 0) d.x = canvas.width; if (d.x > canvas.width) d.x = 0;
                    if (d.y < 0) d.y = canvas.height; if (d.y > canvas.height) d.y = 0;
                    ctx.beginPath();
                    ctx.arc(d.x, d.y, d.r, 0, Math.PI * 2);
                    ctx.fillStyle = 'rgba(169,188,232,' + d.a + ')';
                    ctx.fill();
                }
                requestAnimationFrame(tick);
            })();
        }
    })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
