<?php
// Shared helper for sending verification emails

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailer/src/Exception.php';
require_once __DIR__ . '/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/phpmailer/src/SMTP.php';

function sendVerificationEmail(string $email, string $token): bool {
    $mail = new PHPMailer(true);
    try {
        $mail->SMTPDebug  = 2;                          // ← shows full SMTP log
        $mail->Debugoutput = function($str, $level) {
            error_log("SMTP: $str");                    // logs to PHP error log
        };

        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->setFrom(MAIL_FROM, MAIL_NAME);
        $mail->addAddress($email);

        $link = SITE_URL . '/verify-email.php?token=' . urlencode($token);

        $mail->isHTML(true);
        $mail->Subject = 'Verify your ' . SITE_NAME . ' email';
        $mail->AltBody = "Verify your email: $link";
        $mail->Body    = '
<!DOCTYPE html>
<html>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,sans-serif">
  <table width="100%" cellpadding="0" cellspacing="0">
    <tr><td align="center" style="padding:40px 20px">
      <table width="500" cellpadding="0" cellspacing="0"
             style="background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08)">
        <tr><td style="background:linear-gradient(135deg,#6c63ff,#5a52e0);padding:32px;text-align:center">
          <h1 style="color:#fff;margin:0;font-size:24px">🚀 ' . SITE_NAME . '</h1>
        </td></tr>
        <tr><td style="padding:40px 32px;text-align:center">
          <div style="font-size:48px;margin-bottom:16px">✉️</div>
          <h2 style="color:#1a1a2e;font-size:20px;margin:0 0 12px">Verify your email address</h2>
          <p style="color:#666;font-size:14px;line-height:1.6;margin:0 0 28px">
            Thanks for signing up! Click the button below to verify your email.<br>
            This link is valid for <strong>24 hours</strong>.
          </p>
          <a href="' . $link . '"
             style="display:inline-block;padding:14px 36px;background:linear-gradient(135deg,#6c63ff,#5a52e0);
                    color:#fff;font-size:15px;font-weight:700;border-radius:10px;text-decoration:none">
            Verify Email →
          </a>
          <p style="color:#aaa;font-size:12px;margin:24px 0 0">
            Or copy this link:<br>
            <a href="' . $link . '" style="color:#6c63ff;word-break:break-all">' . $link . '</a>
          </p>
        </td></tr>
        <tr><td style="background:#f7f8fa;padding:20px 32px;text-align:center">
          <p style="color:#bbb;font-size:12px;margin:0">
            If you didn\'t create an account, you can safely ignore this email.
          </p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>';

        $mail->send();
        error_log("✅ Verification email sent successfully to: $email");
        error_log("✅ Verification link: $link");
        return true;

    } catch (Exception $e) {
        error_log("❌ Verification email FAILED to: $email");
        error_log("❌ PHPMailer error: " . $mail->ErrorInfo);
        return false;
    }
}