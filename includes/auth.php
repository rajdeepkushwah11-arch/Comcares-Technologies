<?php
// User store backed by MySQL (previously a JSON file)

require_once __DIR__ . '/db.php';

function findUserByEmail(string $email): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    return $user ?: null;
}

function registerUser(string $name, string $email, string $password): array
{
    if (findUserByEmail($email)) {
        return ['ok' => false, 'error' => 'An account with this email already exists. Try logging in.'];
    }
    $stmt = db()->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
    $stmt->execute([
        $name,
        $email,
        password_hash($password, PASSWORD_DEFAULT),
    ]);
    return ['ok' => true];
}

function loginUser(string $email, string $password): array
{
    $user = findUserByEmail($email);
    if (!$user || !password_verify($password, $user['password'])) {
        return ['ok' => false, 'error' => 'Invalid email or password.'];
    }
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    return ['ok' => true];
}
