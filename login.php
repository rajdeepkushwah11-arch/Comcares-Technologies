<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

$errors = [];
$mode = $_POST['mode'] ?? $_GET['mode'] ?? 'login';
$mode = $mode === 'register' ? 'register' : 'login';
$oldEmail = '';
$oldName = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $oldEmail = $email;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (!$errors && $mode === 'register') {
        $name = trim($_POST['name'] ?? '');
        $oldName = $name;
        if ($name === '') $errors[] = 'Please enter your full name.';
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

$pageTitle = 'Login';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-bg" aria-hidden="true">
        <span class="orb orb-1"></span><span class="orb orb-2"></span><span class="orb orb-3"></span>
        <span class="grid-overlay"></span>
        <canvas id="particles" class="particles"></canvas>
    </div>

    <div class="auth-scene" id="authScene">
        <div class="auth-card <?= $mode === 'register' ? 'flipped' : '' ?>" id="authCard">
            <!-- ============ LOGIN FACE ============ -->
            <div class="auth-face auth-front">
                <div class="auth-logo">
                    <span class="auth-logo-badge">C</span>
                    <span class="auth-logo-name"><?= htmlspecialchars(SITE_NAME) ?></span>
                </div>
                <h2>Welcome <span class="gradient-text">Back</span></h2>
                <p class="auth-sub">Log in to manage your projects and stay updated.</p>

                <?php foreach ($errors as $err): ?>
                    <div class="alert error shake"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>
                <?php if ($flash): ?>
                    <div class="alert success"><?= htmlspecialchars($flash) ?></div>
                <?php endif; ?>

                <form method="post" action="login.php" novalidate>
                    <input type="hidden" name="mode" value="login">
                    <div class="field">
                        <input type="email" name="email" placeholder=" " value="<?= htmlspecialchars($oldEmail) ?>" required>
                        <label>Email address</label>
                        <span class="field-icon">✉</span>
                    </div>
                    <div class="field">
                        <input type="password" name="password" placeholder=" " required>
                        <label>Password</label>
                        <span class="field-icon">🔒</span>
                    </div>
                    <div class="auth-row">
                        <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
                        <a href="#" class="auth-link">Forgot password?</a>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        Login <span class="btn-arrow">→</span>
                    </button>
                </form>

                <p class="auth-switch">Don't have an account?
                    <a href="login.php?mode=register" class="auth-link strong" id="toRegister">Create one — it's free</a>
                </p>
            </div>

            <!-- ============ REGISTER FACE ============ -->
            <div class="auth-face auth-back">
                <div class="auth-logo">
                    <span class="auth-logo-badge">C</span>
                    <span class="auth-logo-name"><?= htmlspecialchars(SITE_NAME) ?></span>
                </div>
                <h2>Create Your <span class="gradient-text">Account</span></h2>
                <p class="auth-sub">Join us in under a minute — no credit card needed.</p>

                <?php foreach ($errors as $err): ?>
                    <div class="alert error shake"><?= htmlspecialchars($err) ?></div>
                <?php endforeach; ?>

                <form method="post" action="login.php" novalidate>
                    <input type="hidden" name="mode" value="register">
                    <div class="field">
                        <input type="text" name="name" placeholder=" " value="<?= htmlspecialchars($oldName) ?>" required>
                        <label>Full name</label>
                        <span class="field-icon">👤</span>
                    </div>
                    <div class="field">
                        <input type="email" name="email" placeholder=" " value="<?= htmlspecialchars($oldEmail) ?>" required>
                        <label>Email address</label>
                        <span class="field-icon">✉</span>
                    </div>
                    <div class="field">
                        <input type="password" name="password" placeholder=" " required minlength="6">
                        <label>Password (min 6 characters)</label>
                        <span class="field-icon">🔒</span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">
                        Create Account <span class="btn-arrow">→</span>
                    </button>
                </form>

                <p class="auth-switch">Already registered?
                    <a href="login.php" class="auth-link strong" id="toLogin">Log in instead</a>
                </p>
            </div>
        </div>
    </div>

    <a href="index.php" class="auth-back-link">← Back to website</a>
</div>

<script>
    (function() {
        var card = document.getElementById('authCard');
        if (!card) return;
        var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        // Smooth flip when switching between login and register
        document.querySelectorAll('#toRegister, #toLogin').forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                if (reduce) {
                    location.href = link.href;
                    return;
                }
                card.classList.toggle('flipped');
                setTimeout(function() {
                    location.href = link.href;
                }, 550);
            });
        });

        // Subtle parallax tilt following the mouse
        var scene = document.getElementById('authScene');
        if (scene && !reduce && window.matchMedia('(hover: hover)').matches) {
            scene.addEventListener('mousemove', function(e) {
                var r = scene.getBoundingClientRect();
                var rx = ((e.clientY - r.top) / r.height - 0.5) * -6;
                var ry = ((e.clientX - r.left) / r.width - 0.5) * 8;
                card.style.transform = 'rotateX(' + rx + 'deg) rotateY(' + (ry + (card.classList.contains('flipped') ? 180 : 0)) + 'deg)';
            });
            scene.addEventListener('mouseleave', function() {
                card.style.transform = card.classList.contains('flipped') ? 'rotateY(180deg)' : '';
            });
        }
    })();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>