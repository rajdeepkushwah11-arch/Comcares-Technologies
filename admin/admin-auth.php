    <?php
// Separate admin authentication — completely independent of the public users table
session_start();
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';

function findAdminByEmail(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM admins WHERE email = ?');
    $stmt->execute([$email]);
    $admin = $stmt->fetch();
    return $admin ?: null;
}

function adminLogin(string $email, string $password): array
{
    $admin = findAdminByEmail($email);
    if (!$admin || !password_verify($password, $admin['password'])) {
        return ['ok' => false, 'error' => 'Invalid admin credentials.'];
    }
    // Mark this session as an ADMIN session (separate keys from public users)
    $_SESSION['admin_id']    = $admin['id'];
    $_SESSION['admin_name']  = $admin['name'];
    $_SESSION['admin_email'] = $admin['email'];
    return ['ok' => true];
}

function requireAdmin(): void
{
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}

function adminLogout(): void
{
    unset($_SESSION['admin_id'], $_SESSION['admin_name'], $_SESSION['admin_email']);
}
