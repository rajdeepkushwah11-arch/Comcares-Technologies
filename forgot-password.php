<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

$errors = [];
$sent = false;
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } else {
        $result = createPasswordReset($email);
        if ($result['ok']) {
            $sent = true;
        } else {
            $errors[] = $result['error'];
        }
    }
}

$pageTitle = 'Forgot password';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-bg" aria-hidden="true"><span class="grid-overlay"></span><span class="auth-beam"></span></div>
    <div class="auth-scene">
        <div class="auth-card auth-reset-card">
            <aside class="auth-brand">
                <div class="auth-brand-top">
                    <span class="auth-logo-badge">C</span>
                    <span class="auth-logo-name"><?= htmlspecialchars(SITE_NAME) ?></span>
                </div>
                <div class="auth-brand-copy">
                    <span class="auth-eyebrow">ACCOUNT RECOVERY</span>
                    <h1 class="auth-brand-headline">Back to <em>what matters.</em></h1>
                    <p class="auth-brand-sub">We will send a secure reset link to the email address associated with your account.</p>
                </div>
                <div class="auth-brand-foot"><span class="auth-live-dot"></span> Secure account recovery</div>
            </aside>
            <section class="auth-panel auth-recovery-panel">
                <?php if ($sent): ?>
                    <div class="recovery-status" role="status">
                        <span class="recovery-status-icon" aria-hidden="true">&#9993;</span>
                        <h2>Check your inbox</h2>
                        <p>If an account is registered with <strong><?= htmlspecialchars($email) ?></strong>, a password-reset link has been sent there.</p>
                        <p class="password-hint">The link works for 24 hours and can only be used once.</p>
                        <a href="login.php" class="btn btn-primary btn-block auth-submit">Return to login</a>
                    </div>
                <?php else: ?>
                    <a class="recovery-back" href="login.php">&larr; Back to login</a>
                    <h1>Forgot your <em>password?</em></h1>
                    <p class="auth-sub">Enter your account email and we will send you a reset link.</p>

                    <?php foreach ($errors as $error): ?>
                        <div class="alert error shake"><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>

                    <form method="post" action="forgot-password.php" novalidate>
                        <?= csrfField() ?>
                        <div class="field">
                            <input type="email" name="email" placeholder=" " value="<?= htmlspecialchars($email) ?>" autocomplete="email" required autofocus>
                            <label>Email address</label>
                            <span class="field-icon">&#9993;</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block auth-submit">Send reset link <b>&rarr;</b></button>
                    </form>
                    <p class="auth-switch">Remembered your password? <a class="auth-link strong" href="login.php">Log in</a></p>
                <?php endif; ?>
            </section>
        </div>
    </div>
    <a href="index.php" class="auth-back-link">&larr; Back to website</a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
