<?php
require __DIR__ . '/includes/config.php';
require __DIR__ . '/includes/auth.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$errors = [];
$complete = false;
$isValid = false;

try {
    $isValid = findValidPasswordReset($token) !== null;
} catch (Throwable $e) {
    error_log('Password reset validation failed: ' . $e->getMessage());
    $errors[] = 'The password-reset service is temporarily unavailable.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isValid) {
    $password = $_POST['password'] ?? '';
    $confirmation = $_POST['confirm_password'] ?? '';
    if (!validateCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session expired. Please try again.';
    } elseif ($password !== $confirmation) {
        $errors[] = 'Passwords do not match.';
    } else {
        $result = resetPassword($token, $password);
        if ($result['ok']) {
            $complete = true;
        } else {
            $errors[] = $result['error'];
            $isValid = findValidPasswordReset($token) !== null;
        }
    }
}

$pageTitle = 'Reset password';
require __DIR__ . '/includes/header.php';
?>

<div class="auth-wrap">
    <div class="auth-bg" aria-hidden="true"><span class="grid-overlay"></span><span class="auth-beam"></span></div>
    <div class="auth-scene">
        <div class="auth-card auth-reset-card">
            <aside class="auth-brand">
                <div class="auth-brand-top"><span class="auth-logo-badge">C</span><span class="auth-logo-name"><?= htmlspecialchars(SITE_NAME) ?></span></div>
                <div class="auth-brand-copy">
                    <span class="auth-eyebrow">SECURE PASSWORD RESET</span>
                    <h1 class="auth-brand-headline">Choose a stronger <em>password.</em></h1>
                    <p class="auth-brand-sub">This secure link can be used once to protect your Comcares account.</p>
                </div>
                <div class="auth-brand-foot"><span class="auth-live-dot"></span> Secure account recovery</div>
            </aside>
            <section class="auth-panel auth-recovery-panel">
                <?php if ($complete): ?>
                    <div class="recovery-status" role="status">
                        <span class="recovery-status-icon" aria-hidden="true">&#10003;</span>
                        <h2>Password updated</h2>
                        <p>Your password has been reset. You can now sign in with your new password.</p>
                        <a href="login.php" class="btn btn-primary btn-block auth-submit">Log in now <b>&rarr;</b></a>
                    </div>
                <?php elseif (!$isValid): ?>
                    <div class="recovery-status">
                        <span class="recovery-status-icon is-error" aria-hidden="true">!</span>
                        <h2>Link unavailable</h2>
                        <p>This reset link is invalid, expired, or has already been used.</p>
                        <a href="forgot-password.php" class="btn btn-primary btn-block auth-submit">Request a new link</a>
                    </div>
                <?php else: ?>
                    <a class="recovery-back" href="login.php">&larr; Back to login</a>
                    <h1>Set new <em>password</em></h1>
                    <p class="auth-sub">Use at least 8 characters. This link will be invalid after you save the new password.</p>
                    <?php foreach ($errors as $error): ?>
                        <div class="alert error shake"><?= htmlspecialchars($error) ?></div>
                    <?php endforeach; ?>
                    <form method="post" action="reset-password.php" novalidate>
                        <?= csrfField() ?>
                        <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                        <div class="field">
                            <input type="password" name="password" id="newPassword" placeholder=" " autocomplete="new-password" minlength="8" required autofocus>
                            <label>New password</label>
                            <span class="field-icon">&#128274;</span>
                        </div>
                        <div class="field">
                            <input type="password" name="confirm_password" placeholder=" " autocomplete="new-password" minlength="8" required>
                            <label>Confirm new password</label>
                            <span class="field-icon">&#128274;</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block auth-submit">Save new password <b>&rarr;</b></button>
                    </form>
                <?php endif; ?>
            </section>
        </div>
    </div>
    <a href="index.php" class="auth-back-link">&larr; Back to website</a>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
