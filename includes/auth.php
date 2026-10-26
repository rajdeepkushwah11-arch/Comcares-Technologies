<?php
// User store backed by MySQL (previously a JSON file)

require_once __DIR__ . '/db.php';

function findUserByEmail(string $email): ?array
{
    $stmt = db()->prepare('SELECT id, name, email, password_hash, status, created_at FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function registerUser(string $name, string $email, string $password): array
{
    $name     = trim($name);
    $email    = strtolower(trim($email));

    if ($name === '' || mb_strlen($name) > 100) {
        return ['ok' => false, 'error' => 'Please enter your name (max 100 characters).'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 150) {
        return ['ok' => false, 'error' => 'Please enter a valid email address (max 150 characters).'];
    }
    if (strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Password must be at least 8 characters long.'];
    }
    try {
        if (findUserByEmail($email)) {
            return ['ok' => false, 'error' => 'An account with this email already exists. Try logging in.'];
        }
        $stmt = db()->prepare('INSERT INTO users (name, email, password_hash, status) VALUES (?, ?, ?, ?)');
        $stmt->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), 'active']);
        return ['ok' => true];
    } catch (PDOException $e) {
        // 23000 = integrity constraint violation (duplicate email, etc.)
        if ($e->getCode() === '23000') {
            return ['ok' => false, 'error' => 'An account with this email already exists. Try logging in.'];
        }
        error_log('Registration failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'The registration database is not ready. Start MySQL in XAMPP and import database.sql in phpMyAdmin, then try again.'];
    }
}

function loginUser(string $email, string $password): array
{
    $user = findUserByEmail($email);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }
    if ($user['status'] !== 'active') {
        return ['ok' => false, 'error' => 'Your account is not active. Please contact support.'];
    }
    session_regenerate_id(true);
    $_SESSION['user_id']    = (int)$user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    return ['ok' => true];
}

function createPasswordReset(string $email): array
{
    $email = strtolower(trim($email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'error' => 'Please enter a valid email address.'];
    }

    try {
        $user = findUserByEmail($email);
        // Keep the same response for unknown addresses so user accounts cannot be enumerated.
        if (!$user) {
            return ['ok' => true, 'sent' => true];
        }

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', time() + RESET_LINK_TTL);
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')->execute([$user['id']]);
        $pdo->prepare('INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, ?)')
            ->execute([$user['id'], $tokenHash, $expiresAt]);
        $pdo->commit();

        $resetUrl = passwordResetUrl($token);
        $subject = SITE_NAME . ' password reset';
        $message = "Hello " . $user['name'] . ",\n\n" .
            "We received a request to reset your password. Use this link to choose a new password:\n\n" .
            $resetUrl . "\n\n" .
            "This link is valid for 24 hours and can be used once. If you did not request this, you can ignore this email.";
        $headers = [
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'From: ' . SITE_NAME . ' <' . RESET_FROM_EMAIL . '>',
        ];

        if (!mail($user['email'], $subject, $message, implode("\r\n", $headers))) {
            $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE token_hash = ?')->execute([$tokenHash]);
            return ['ok' => false, 'error' => 'We could not send the reset email. Please try again later.'];
        }
        return ['ok' => true, 'sent' => true];
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Password reset request failed: ' . $e->getMessage());
        if ($e instanceof PDOException) {
            return ['ok' => false, 'error' => 'The password-reset database is unavailable. Start MySQL in XAMPP and import database.sql in phpMyAdmin, then try again.'];
        }
        return ['ok' => false, 'error' => 'We could not process your request. Please try again later.'];
    }
}

function passwordResetUrl(string $token): string
{
    if (SITE_URL !== '') {
        return rtrim(SITE_URL, '/') . '/reset-password.php?token=' . rawurlencode($token);
    }

    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/');
    // Vercel routes public PHP pages through /api/router.php, but reset links belong at the site root.
    $scriptDirectory = str_ends_with($scriptName, '/api/router.php') ? '/' : dirname($scriptName);
    $basePath = $scriptDirectory === '/' || $scriptDirectory === '.' ? '' : rtrim($scriptDirectory, '/');
    return $scheme . '://' . $host . $basePath . '/reset-password.php?token=' . rawurlencode($token);
}

function findValidPasswordReset(string $token): ?array
{
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) {
        return null;
    }
    $stmt = db()->prepare(
        'SELECT pr.id, pr.user_id, u.email FROM password_resets pr INNER JOIN users u ON u.id = pr.user_id
         WHERE pr.token_hash = ? AND pr.used_at IS NULL AND pr.expires_at > NOW() LIMIT 1'
    );
    $stmt->execute([hash('sha256', $token)]);
    $reset = $stmt->fetch();
    return $reset ?: null;
}

function resetPassword(string $token, string $password): array
{
    if (strlen($password) < 8) {
        return ['ok' => false, 'error' => 'Password must be at least 8 characters long.'];
    }
    try {
        $reset = findValidPasswordReset($token);
        if (!$reset) {
            return ['ok' => false, 'error' => 'This reset link is invalid, expired, or has already been used.'];
        }
        $pdo = db();
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
            ->execute([password_hash($password, PASSWORD_DEFAULT), $reset['user_id']]);
        $pdo->prepare('UPDATE password_resets SET used_at = NOW() WHERE user_id = ? AND used_at IS NULL')
            ->execute([$reset['user_id']]);
        $pdo->commit();
        return ['ok' => true];
    } catch (Throwable $e) {
        if (isset($pdo) && $pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Password reset failed: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'We could not reset your password. Please try again later.'];
    }
}

function logoutUser(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

function isUserLoggedIn(): bool
{
    return isset($_SESSION['user_id']);
}

function currentUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}

function requireLogin(string $redirect = 'login.php'): void
{
    if (!isUserLoggedIn()) {
        header('Location: ' . $redirect);
        exit;
    }
}

function createCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(createCsrfToken(), ENT_QUOTES, 'UTF-8') . '">';
}
