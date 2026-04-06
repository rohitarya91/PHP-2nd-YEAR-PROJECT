<?php
function fetch_user_by_email(mysqli $conn, string $email): ?array
{
    $stmt = $conn->prepare(
        "SELECT id, name, email, role, password
         FROM users
         WHERE email = ?
         LIMIT 1"
    );
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($id, $name, $userEmail, $role, $passwordHash);
    $user = null;

    if ($stmt->fetch()) {
        $user = [
            'id' => (int) $id,
            'name' => (string) $name,
            'email' => (string) $userEmail,
            'role' => (string) $role,
            'password' => (string) $passwordHash,
        ];
    }

    $stmt->close();

    return $user;
}

function fetch_user_for_login(mysqli $conn, string $identifier): ?array
{
    $identifier = trim($identifier);
    if ($identifier === '') {
        return null;
    }

    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        return fetch_user_by_email($conn, $identifier);
    }

    if (ctype_digit($identifier)) {
        $id = (int) $identifier;
        $stmt = $conn->prepare(
            "SELECT id, name, email, role, password
             FROM users
             WHERE id = ?
             LIMIT 1"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->bind_result($userId, $name, $userEmail, $role, $passwordHash);
        $user = null;

        if ($stmt->fetch()) {
            $user = [
                'id' => (int) $userId,
                'name' => (string) $name,
                'email' => (string) $userEmail,
                'role' => (string) $role,
                'password' => (string) $passwordHash,
            ];
        }

        $stmt->close();

        if ($user) {
            return $user;
        }
    }

    $stmt = $conn->prepare(
        "SELECT id, name, email, role, password
         FROM users
         WHERE phone_number = ?
         LIMIT 1"
    );
    $stmt->bind_param('s', $identifier);
    $stmt->execute();
    $stmt->bind_result($userId, $name, $userEmail, $role, $passwordHash);
    $user = null;

    if ($stmt->fetch()) {
        $user = [
            'id' => (int) $userId,
            'name' => (string) $name,
            'email' => (string) $userEmail,
            'role' => (string) $role,
            'password' => (string) $passwordHash,
        ];
    }

    $stmt->close();

    return $user;
}

function login_user_session(array $user): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = strtolower(trim((string) ($user['role'] ?? 'user')));
}

function update_user_last_login(mysqli $conn, int $userId): void
{
    $loginStmt = $conn->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?");
    $loginStmt->bind_param('i', $userId);
    $loginStmt->execute();
    $loginStmt->close();
}

function verify_user_password(mysqli $conn, array $user, string $password): bool
{
    $storedPassword = (string) ($user['password'] ?? '');
    if ($storedPassword === '' || $password === '') {
        return false;
    }

    if (password_verify($password, $storedPassword)) {
        if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param('si', $passwordHash, $user['id']);
            $stmt->execute();
            $stmt->close();
        }

        return true;
    }

    // Support legacy rows where a password may still be stored in plain text.
    if (strpos($storedPassword, '$2y$') !== 0 && hash_equals($storedPassword, $password)) {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param('si', $passwordHash, $user['id']);
        $stmt->execute();
        $stmt->close();

        return true;
    }

    return false;
}
