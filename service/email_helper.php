<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../config/config.php';
require __DIR__ . '/../vendor/autoload.php';

/**
 * Fungsi universal untuk kirim email
 */
function sendEmailBase($recipient_email, $username, $subject, $body)
{
    $recipient_email = $recipient_email ?? '';
    $username = $username ?? '';

    try {
        $mail = new PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($recipient_email, $username);
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return ['success' => true];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Kirim email verifikasi pertama kali
 */
function sendVerificationEmail($recipient_email, $username, $token)
{
    $recipient_email = $recipient_email ?? '';
    $username = $username ?? '';
    $token = $token ?? '';

    $link = APP_URL . "/service/verify_email.php?token=" . urlencode($token) . "&email=" . urlencode($recipient_email);

    $body = "
<html>
<head>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<style>
body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background:#f6f6f6; margin:0; padding:0;}
.container { max-width:600px; margin:30px auto; background:#fff; border-radius:10px; padding:30px; box-shadow:0 4px 20px rgba(0,0,0,0.08);}
h1 { color:#ef4444; font-size:24px; margin-bottom:15px; text-align:center;}
p { color:#374151; font-size:16px; line-height:1.5;}
a.button { display:inline-block; padding:12px 25px; background:#ef4444; color:#fff; border-radius:6px; text-decoration:none; font-weight:600; margin-top:20px;}
.footer { font-size:12px; color:#9ca3af; text-align:center; margin-top:25px;}
@media (max-width:480px){ .container { padding:20px; } h1 { font-size:20px; } }
</style>
</head>
<body>
<div class='container'>
    <h1>Verifikasi Email Anda</h1>
    <p>Halo <b>$username</b>, terima kasih telah mendaftar di <b>Knowledge Battle Forum</b>.</p>
    <p>Silakan klik tombol di bawah untuk memverifikasi email dan mengaktifkan akun Anda:</p>
    <p style='text-align:center;'><a class='button' href='$link'>Verifikasi Email</a></p>
    <p>Jika tombol tidak berfungsi, salin dan tempel link berikut di browser Anda:</p>
    <p style='word-break:break-all; font-size:14px;'>$link</p>
    <p>Link ini berlaku selama 24 jam.</p>
    <div class='footer'>&copy; 2025 Knowledge Battle Forum</div>
</div>
</body>
</html>";

    return sendEmailBase($recipient_email, $username, 'Verifikasi Email Anda', $body);
}

/**
 * Kirim email verifikasi ulang (resend)
 */
function sendResendVerificationEmail($recipient_email, $username, $token)
{
    $recipient_email = $recipient_email ?? '';
    $username = $username ?? '';
    $token = $token ?? '';

    $link = APP_URL . "/service/verify_email.php?token=" . urlencode($token) . "&email=" . urlencode($recipient_email);

    $body = "
<html>
<head>
<meta name='viewport' content='width=device-width, initial-scale=1.0'>
<style>
body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; background:#f6f6f6; margin:0; padding:0;}
.container { max-width:600px; margin:30px auto; background:#fff; border-radius:10px; padding:30px; box-shadow:0 4px 20px rgba(0,0,0,0.08);}
h1 { color:#ef4444; font-size:24px; margin-bottom:15px; text-align:center;}
p { color:#374151; font-size:16px; line-height:1.5;}
a.button { display:inline-block; padding:12px 25px; background:#ef4444; color:#fff; border-radius:6px; text-decoration:none; font-weight:600; margin-top:20px;}
.footer { font-size:12px; color:#9ca3af; text-align:center; margin-top:25px;}
@media (max-width:480px){ .container { padding:20px; } h1 { font-size:20px; } }
</style>
</head>
<body>
<div class='container'>
    <h1>Verifikasi Ulang Email</h1>
    <p>Halo <b>$username</b>, Anda meminta link verifikasi ulang.</p>
    <p>Silakan klik tombol di bawah untuk mengaktifkan akun Anda:</p>
    <p style='text-align:center;'><a class='button' href='$link'>Verifikasi Email</a></p>
    <p>Jika tombol tidak berfungsi, salin dan tempel link berikut di browser:</p>
    <p style='word-break:break-all; font-size:14px;'>$link</p>
    <p>Link ini berlaku selama 24 jam.</p>
    <div class='footer'>&copy; 2025 Knowledge Battle Forum</div>
</div>
</body>
</html>";

    return sendEmailBase($recipient_email, $username, 'Verifikasi Email Ulang', $body);
}
?>
