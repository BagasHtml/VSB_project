# Email Configuration Guide - Knowledge Battle

## Quick Setup for Gmail (Recommended)

### Step 1: Enable 2-Factor Authentication (Required)
1. Go to https://myaccount.google.com
2. Left sidebar → **Security** 
3. Search for "2-Step Verification"
4. Click **Enable 2-Step Verification**
5. Follow Google's instructions (SMS or authenticator app)

### Step 2: Generate App Password
1. Go to https://myaccount.google.com/apppasswords
2. Select **App**: Mail
3. Select **Device**: Windows Computer (or your device)
4. Click **Generate**
5. Copy the **16-character password** (Google will show it)
6. **Keep this safe** - you'll need it in Step 3

### Step 3: Configure VSB Project

#### Option A: Using Environment Variables (Recommended)

Create or edit `.env` file in project root:
```
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-16-char-app-password
SMTP_FROM=your-email@gmail.com
```

**Note:** Add `.env` to `.gitignore` to keep credentials private

#### Option B: Direct Configuration

Edit `/service/send_otp.php` (lines 69-71):
```php
$mail->Username = 'your-email@gmail.com';        // Your Gmail address
$mail->Password = 'xxxx xxxx xxxx xxxx';         // 16-char app password
$fromEmail = 'your-email@gmail.com';              // Your Gmail address
```

Replace:
- `your-email@gmail.com` → Your actual Gmail address
- `xxxx xxxx xxxx xxxx` → Your 16-character app password (spaces included)

### Step 4: Test Email Sending

1. Open form_register.php in browser
2. Enter test email address
3. Click "Kirim OTP"
4. **Check browser console** (F12) for response
5. Check email inbox for OTP message
6. If email arrives → **Configuration successful!** ✅

---

## For Other Email Providers

### Microsoft Outlook/Hotmail

Edit `/service/send_otp.php` (lines 63-71):
```php
$mail->Host = 'smtp-mail.outlook.com';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->Username = 'your-email@outlook.com';
$mail->Password = 'your-outlook-password';
$fromEmail = 'your-email@outlook.com';
```

### Yahoo Mail

```php
$mail->Host = 'smtp.mail.yahoo.com';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SSL;
$mail->Port = 465;
$mail->Username = 'your-email@yahoo.com';
$mail->Password = 'your-app-password'; // Generate app password
$fromEmail = 'your-email@yahoo.com';
```

### Custom SMTP Server

```php
$mail->Host = 'smtp.your-provider.com';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // or SSL
$mail->Port = 587; // or 465 for SSL
$mail->Username = 'your-username';
$mail->Password = 'your-password';
$fromEmail = 'your-email@domain.com';
```

---

## Troubleshooting

### Issue: "SMTP Error: Could not authenticate"
**Solutions:**
1. Check Gmail 2FA is enabled
2. Verify app password is correct (16 characters)
3. Ensure password is copied exactly (no extra spaces)
4. Try generating new app password
5. Check app email address is correct

### Issue: "SMTP Error: 535 Incorrect authentication code"
**Solution:** Gmail rejected the password. Regenerate app password:
1. Go to https://myaccount.google.com/apppasswords
2. Delete old app password
3. Generate new one
4. Update in send_otp.php

### Issue: "SMTP Error: 530 5.7.0 Must issue a STARTTLS command first"
**Solution:** Gmail requires encryption. Verify lines 67-68:
```php
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
```

### Issue: Email sent but not received
**Solutions:**
1. Check spam/junk folder
2. Wait 2-3 minutes for delivery
3. Check recipient email address is correct
4. Check email HTML formatting in send_otp.php looks right
5. Try sending to different email address

### Issue: "Email tidak dikonfigurasi. Gunakan kode ini untuk testing"
**Meaning:** Credentials are still placeholders
**Solution:** Configure SMTP as shown above

---

## Development Mode (Testing without Email)

If you want to test the registration flow without setting up email:

**The system automatically detects invalid credentials and returns test OTP:**
```json
{
  "success": true,
  "message": "OTP test: 123456 (Email tidak dikonfigurasi. Gunakan kode ini untuk testing)",
  "otp_test": "123456"
}
```

**Use the provided OTP to continue testing.**

### How to Use Development Mode:

1. Leave email credentials as default (placeholders)
2. Try to register
3. System returns test OTP code
4. Use that code in OTP verification step
5. Continue registration flow normally

⚠️ **Remove this fallback before production!**
- Search for `otp_test` in `/service/send_otp.php`
- Delete the development fallback code (see comment in file)

---

## Production Deployment Checklist

Before going live:

- [ ] Email credentials configured (not placeholders)
- [ ] Test email sending works
- [ ] Email HTML formatting looks good
- [ ] Spam filters allow your emails
- [ ] Development fallback code removed from send_otp.php
- [ ] HTTPS enabled (for secure credential handling)
- [ ] Environment variables used (not hardcoded in files)
- [ ] Error logs configured
- [ ] Rate limiting tested and working
- [ ] All user registration flows tested
- [ ] Email template branding correct
- [ ] Email sender name set correctly
- [ ] Monitor email delivery rates

---

## Advanced Configuration

### Using .env File (PHP)

Create `/service/.env`:
```
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_SECURE=tls
SMTP_USERNAME=your-email@gmail.com
SMTP_PASSWORD=your-app-password
SMTP_FROM=your-email@gmail.com
SMTP_FROM_NAME=Knowledge Battle
```

Then update `/service/send_otp.php` to load it:
```php
// Load environment variables
if (file_exists(__DIR__ . '/.env')) {
    $env = parse_ini_file(__DIR__ . '/.env');
    foreach ($env as $key => $value) {
        putenv("$key=$value");
    }
}
```

### Sending Test Email Manually

PHP Script to test configuration:
```php
<?php
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your-email@gmail.com';
    $mail->Password = 'your-app-password';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;
    
    $mail->setFrom('your-email@gmail.com', 'Knowledge Battle');
    $mail->addAddress('recipient@example.com');
    $mail->Subject = 'Test Email';
    $mail->Body = 'Test email configuration is working!';
    
    $mail->send();
    echo "Email sent successfully!";
} catch (Exception $e) {
    echo "Email failed: " . $mail->ErrorInfo;
}
?>
```

---

## Security Best Practices

1. **Never commit credentials** to version control
   - Use .gitignore for .env files
   - Use environment variables

2. **Use app passwords** instead of main password
   - Limits damage if compromised
   - Can be revoked independently
   - Required for Gmail with 2FA

3. **Enable 2FA on email account**
   - Protects your email account
   - Required for app passwords
   - Adds extra security layer

4. **Monitor email logs**
   - Track OTP sending
   - Alert on failed sends
   - Review authentication attempts

5. **Use HTTPS in production**
   - Protects credential transmission
   - Required for user trust
   - Search engines prefer HTTPS

---

## Email Template Customization

To customize the OTP email, edit `/service/send_otp.php` lines 77-99:

```php
$mail->Body = "
<html>
<body style='font-family: Poppins, sans-serif; background: #f5f5f5; padding: 20px;'>
    <div style='max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 12px;'>
        <!-- Header -->
        <h2 style='color: #1a1a1a; text-align: center;'>Verifikasi Email Anda</h2>
        
        <!-- Instructions -->
        <p style='color: #666; text-align: center;'>Gunakan kode OTP berikut untuk menyelesaikan pendaftaran:</p>
        
        <!-- OTP Code (Large) -->
        <div style='background: #f5f5f5; padding: 20px; border-radius: 8px; text-align: center; margin: 25px 0;'>
            <h1 style='color: #FF2800; letter-spacing: 3px; margin: 0; font-size: 32px;'>" . implode(' ', str_split($otp)) . "</h1>
        </div>
        
        <!-- Expiry Notice -->
        <p style='color: #999; text-align: center; font-size: 13px;'>Kode ini berlaku selama 5 menit</p>
        
        <!-- Security Notice -->
        <p style='color: #999; text-align: center; font-size: 13px;'>Jika Anda tidak melakukan permintaan ini, abaikan email ini</p>
        
        <!-- Footer -->
        <p style='color: #999; text-align: center; font-size: 12px;'>© 2024 Knowledge Battle</p>
    </div>
</body>
</html>";
```

**Customizable elements:**
- Colors: Change `#FF2800`, `#1a1a1a`, `#999`, etc.
- Text: Change any message
- Font: Change "Poppins" to preferred font
- Spacing: Adjust padding/margin values
- Logo: Add image URL

---

## Support Resources

- **Gmail App Password Help:** https://support.google.com/accounts/answer/185833
- **Gmail 2FA Setup:** https://support.google.com/accounts/answer/185839
- **PHPMailer Documentation:** https://github.com/PHPMailer/PHPMailer
- **SMTP Settings by Provider:** https://www.arclab.com/en/kb/email/smtp-settings.html

---

**Last Updated:** December 8, 2025  
**Status:** Ready to configure  
**Support:** For issues, check the Troubleshooting section above
