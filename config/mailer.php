<?php
$composerAutoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($composerAutoloadPath)) {
    require_once $composerAutoloadPath;
}

function ensure_mail_log_directory(): string
{
    if (!is_dir(MAIL_LOG_DIR)) {
        mkdir(MAIL_LOG_DIR, 0777, true);
    }

    return MAIL_LOG_DIR;
}

function app_mail_html_shell(string $title, string $intro, string $actionUrl = '', string $actionLabel = '', string $footerCopy = ''): string
{
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeIntro = nl2br(htmlspecialchars($intro, ENT_QUOTES, 'UTF-8'));
    $safeFooter = htmlspecialchars($footerCopy, ENT_QUOTES, 'UTF-8');
    $actionMarkup = '';

    if ($actionUrl !== '' && $actionLabel !== '') {
        $safeUrl = htmlspecialchars($actionUrl, ENT_QUOTES, 'UTF-8');
        $safeLabel = htmlspecialchars($actionLabel, ENT_QUOTES, 'UTF-8');
        $actionMarkup = '<p style="margin:24px 0;"><a href="' . $safeUrl . '" style="display:inline-block;padding:12px 20px;border-radius:10px;background:#16a34a;color:#ffffff;text-decoration:none;font-weight:600;">' . $safeLabel . '</a></p>';
    }

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:24px;background:#f1f5f9;font-family:Arial,sans-serif;color:#0f172a;">
  <div style="max-width:620px;margin:0 auto;background:#ffffff;border-radius:18px;padding:32px;box-shadow:0 20px 40px rgba(15,23,42,0.12);">
    <p style="margin:0 0 8px;color:#16a34a;font-size:13px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;">{APP_NAME}</p>
    <h1 style="margin:0 0 16px;font-size:28px;line-height:1.2;">{$safeTitle}</h1>
    <div style="font-size:15px;line-height:1.8;color:#334155;">{$safeIntro}</div>
    {$actionMarkup}
    <p style="margin:28px 0 0;font-size:13px;line-height:1.7;color:#64748b;">{$safeFooter}</p>
  </div>
</body>
</html>
HTML;
}

function send_app_mail(string $toEmail, string $subject, string $htmlBody, string $textBody = ''): array
{
    $toEmail = trim($toEmail);

    if ($toEmail === '') {
        return ['success' => false, 'message' => 'Email address is required.'];
    }

    if (
        class_exists('\\PHPMailer\\PHPMailer\\PHPMailer')
        && MAILER_MODE === 'smtp'
        && SMTP_HOST !== ''
        && SMTP_USERNAME !== ''
        && SMTP_PASSWORD !== ''
    ) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->Port = SMTP_PORT;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = SMTP_ENCRYPTION;
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($toEmail);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $textBody !== '' ? $textBody : strip_tags($htmlBody);
            $mail->send();

            return ['success' => true, 'delivery' => 'smtp'];
        } catch (Throwable $exception) {
            return ['success' => false, 'message' => $exception->getMessage()];
        }
    }

    $directory = ensure_mail_log_directory();
    $filename = $directory . '/mail_' . date('Ymd_His') . '_' . substr(app_random_token(8), 0, 10) . '.html';
    $payload = "<!-- To: {$toEmail} | Subject: {$subject} -->\n" . $htmlBody;

    if (file_put_contents($filename, $payload) === false) {
        return ['success' => false, 'message' => 'Unable to save email preview.'];
    }

    return [
        'success' => true,
        'delivery' => 'log',
        'preview_path' => $filename,
    ];
}
