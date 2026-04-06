<?php
if (!function_exists('app_env')) {
    function app_env(string $key, string $default = ''): string
    {
        $value = getenv($key);

        if ($value === false || $value === '') {
            return $default;
        }

        return (string) $value;
    }
}

if (!defined('APP_NAME')) {
    define('APP_NAME', app_env('APP_NAME', 'Harvest Fresh'));
}

if (!defined('APP_ENVIRONMENT')) {
    define('APP_ENVIRONMENT', app_env('APP_ENVIRONMENT', 'local'));
}

if (!defined('APP_BASE_URL')) {
    define('APP_BASE_URL', rtrim(app_env('APP_BASE_URL', 'http://localhost/rohit/demoPHP/PHP_PROJECT'), '/'));
}

if (!defined('MAILER_MODE')) {
    define('MAILER_MODE', strtolower(app_env('MAILER_MODE', 'log')));
}

if (!defined('MAIL_FROM_EMAIL')) {
    define('MAIL_FROM_EMAIL', app_env('MAIL_FROM_EMAIL', 'no-reply@example.com'));
}

if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', app_env('MAIL_FROM_NAME', APP_NAME));
}

if (!defined('SMTP_HOST')) {
    define('SMTP_HOST', app_env('SMTP_HOST', 'smtp.example.com'));
}

if (!defined('SMTP_PORT')) {
    define('SMTP_PORT', (int) app_env('SMTP_PORT', '587'));
}

if (!defined('SMTP_USERNAME')) {
    define('SMTP_USERNAME', app_env('SMTP_USERNAME', ''));
}

if (!defined('SMTP_PASSWORD')) {
    define('SMTP_PASSWORD', app_env('SMTP_PASSWORD', ''));
}

if (!defined('SMTP_ENCRYPTION')) {
    define('SMTP_ENCRYPTION', app_env('SMTP_ENCRYPTION', 'tls'));
}

if (!defined('PAYMENT_GATEWAY_MODE')) {
    define('PAYMENT_GATEWAY_MODE', strtolower(app_env('PAYMENT_GATEWAY_MODE', 'test')));
}

if (!defined('PAYMENT_GATEWAY_NAME')) {
    define('PAYMENT_GATEWAY_NAME', app_env('PAYMENT_GATEWAY_NAME', 'Test Gateway'));
}

if (!defined('MAIL_LOG_DIR')) {
    define('MAIL_LOG_DIR', dirname(__DIR__) . '/storage/mail_logs');
}
