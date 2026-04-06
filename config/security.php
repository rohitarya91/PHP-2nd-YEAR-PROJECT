<?php
function app_random_token(int $bytes = 32): string
{
    return bin2hex(random_bytes($bytes));
}

function csrf_token(string $form = 'default'): string
{
    if (!isset($_SESSION['_csrf_tokens']) || !is_array($_SESSION['_csrf_tokens'])) {
        $_SESSION['_csrf_tokens'] = [];
    }

    $tokenData = $_SESSION['_csrf_tokens'][$form] ?? null;
    $isExpired = !is_array($tokenData) || (int) ($tokenData['expires_at'] ?? 0) < time();

    if ($isExpired) {
        $tokenData = [
            'value' => app_random_token(16),
            'expires_at' => time() + 7200,
        ];
        $_SESSION['_csrf_tokens'][$form] = $tokenData;
    }

    return (string) $tokenData['value'];
}

function csrf_field(string $form = 'default'): string
{
    $token = htmlspecialchars(csrf_token($form), ENT_QUOTES, 'UTF-8');

    return '<input type="hidden" name="_csrf" value="' . $token . '">';
}

function validate_csrf_token(?string $token, string $form = 'default'): bool
{
    $tokenData = $_SESSION['_csrf_tokens'][$form] ?? null;

    if (!is_array($tokenData)) {
        return false;
    }

    $storedValue = (string) ($tokenData['value'] ?? '');
    $expiresAt = (int) ($tokenData['expires_at'] ?? 0);

    if ($storedValue === '' || $expiresAt < time() || $token === null) {
        return false;
    }

    return hash_equals($storedValue, $token);
}

function post_csrf_token(): ?string
{
    return isset($_POST['_csrf']) ? (string) $_POST['_csrf'] : null;
}

function request_is_post(): bool
{
    return strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
}

function enforce_csrf_or_errors(array &$errors, string $form = 'default'): bool
{
    if (!request_is_post()) {
        return true;
    }

    if (!validate_csrf_token(post_csrf_token(), $form)) {
        $errors[] = 'Your session token expired. Please refresh and try again.';
        return false;
    }

    return true;
}
