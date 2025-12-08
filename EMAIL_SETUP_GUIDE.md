# 🔧 SETUP GUIDE - Email Configuration untuk OTP System

## ⚠️ PENTING: Konfigurasi Email HARUS dilakukan sebelum menggunakan fitur registrasi!

---

## 📧 Step 1: Setup Gmail App Password

Kami menggunakan PHPMailer dengan Gmail SMTP. Ikuti langkah berikut:

### 1.1 Enable 2-Step Verification
- Buka [myaccount.google.com](https://myaccount.google.com)
- Klik **Security** di menu sebelah kiri
- Scroll ke "How you sign in to Google"
- Klik **2-Step Verification**
- Ikuti instruksi untuk verifikasi nomor telepon

### 1.2 Generate App Password
- Kembali ke halaman Security
- Scroll ke "How you sign in to Google"
- Klik **App passwords** (hanya muncul jika 2-Step Verification aktif)
- Select "Mail" → "Windows Computer"
- Google akan generate 16-character password
- **Copy password ini** - kita akan butuh

### 1.3 Update Konfigurasi di send_otp.php

Buka file: `/service/send_otp.php`

Cari bagian ini (sekitar baris 55-60):
```php
// Server settings
$mail->isSMTP();
$mail->Host = 'smtp.gmail.com';
$mail->SMTPAuth = true;
$mail->Username = 'your-email@gmail.com'; // ← UBAH INI
$mail->Password = 'your-app-password'; // ← UBAH INI
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;

// Recipients
$mail->setFrom('your-email@gmail.com', 'Knowledge Battle'); // ← UBAH INI
```

**Ganti:**
- `your-email@gmail.com` → Email Gmail Anda yang sudah setup 2-Step Verification
- `your-app-password` → 16-character password dari Step 1.2 (tanpa spasi)

**Contoh:**
```php
$mail->Username = 'muhammadbagashtml@gmail.com';
$mail->Password = 'abcd1234efgh5678'; // APP PASSWORD (16 chars)
$mail->setFrom('muhammadbagashtml@gmail.com', 'Knowledge Battle');
```

---

## 🔒 Alternative: Using Email Service Provider

Jika tidak ingin menggunakan Gmail, Anda bisa menggunakan:

### Option 1: SendGrid
```php
$mail->Host = 'smtp.sendgrid.net';
$mail->Username = 'apikey'; // Literal string 'apikey'
$mail->Password = 'SG.xxxxxxx...'; // SendGrid API Key
$mail->Port = 587;
```

### Option 2: Mailgun
```php
$mail->Host = 'smtp.mailgun.org';
$mail->Username = 'postmaster@yourdomain.com';
$mail->Password = 'key-xxx...'; // Mailgun key
$mail->Port = 587;
```

### Option 3: Mailtrap (Testing)
```php
$mail->Host = 'smtp.mailtrap.io';
$mail->Username = 'your-mailtrap-username';
$mail->Password = 'your-mailtrap-password';
$mail->Port = 465;
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
```

---

## ✅ Testing Email Configuration

Setelah setup, test dengan langkah berikut:

1. **Buka form registrasi:**
   ```
   http://localhost/VSB_project/View/login_register/form_register.php
   ```

2. **Masukkan email Anda:**
   - Gunakan email asli (bukan test email) untuk testing awal
   - Atau gunakan test email service seperti Mailtrap

3. **Klik "Kirim OTP"**

4. **Cek inbox email Anda:**
   - Jika berhasil, Anda akan menerima email dengan OTP code
   - Jika gagal, check error message di halaman atau log PHP

---

## 🐛 Troubleshooting

### Error: "Gagal mengirim email"

**Penyebab kemungkinan:**
1. Email/password salah
2. 2-Step Verification belum aktif
3. App password belum generate
4. Firewall/ISP blocking port 587

**Solusi:**
- Cek ulang username & password di send_otp.php
- Pastikan 2-Step Verification sudah aktif
- Try different port (465 with ENCRYPTION_SMTPS)
- Check server firewall settings

### Error: "Authentication failed"

**Penyebab:**
- Password yang digunakan bukan app password
- Username/password typo

**Solusi:**
- Pastikan menggunakan 16-character APP PASSWORD, bukan password Gmail biasa
- Re-generate app password jika lupa

### Email tidak diterima

**Penyebab:**
- Email masuk folder Spam
- Server configuration salah
- Email domain bermasalah

**Solusi:**
- Check folder Spam di email
- Whitelist domain di email settings
- Coba dengan email berbeda (Gmail, Outlook, etc)

### OTP tidak muncul di step 2

**Penyebab:**
- JavaScript disabled
- Session tidak disimpan
- Form tidak submit properly

**Solusi:**
- Enable JavaScript di browser
- Check browser console (F12) untuk error
- Clear browser cache & retry

---

## 🚀 Production Setup

Untuk production environment:

### 1. Gunakan Environment Variables
Jangan store credentials di source code:

```php
// Buat file .env (tidak di-commit ke git)
// Isi:
MAIL_HOST=smtp.gmail.com
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_FROM_ADDRESS=your-email@gmail.com
```

Kemudian di send_otp.php:
```php
$mail->Username = getenv('MAIL_USERNAME');
$mail->Password = getenv('MAIL_PASSWORD');
$mail->setFrom(getenv('MAIL_FROM_ADDRESS'), 'Knowledge Battle');
```

### 2. Setup Database Logging (Optional)

```sql
CREATE TABLE otp_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    status ENUM('sent', 'verified', 'expired', 'failed') DEFAULT 'sent',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP,
    verified_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_created (created_at)
);
```

### 3. Setup Email Rate Limiting Database

```sql
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL UNIQUE,
    action VARCHAR(50) NOT NULL,
    attempts INT DEFAULT 1,
    first_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_attempt TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ip_action (ip_address, action)
);
```

---

## 📋 Security Checklist

- [ ] 2-Step Verification enabled on Gmail
- [ ] App password generated (not regular password)
- [ ] App password updated in send_otp.php
- [ ] HTTPS enabled on production
- [ ] .env file created & added to .gitignore
- [ ] Rate limiting working
- [ ] OTP expiry set to 5 minutes
- [ ] Email domain verified (if using custom domain)
- [ ] Backup SMTP provider configured
- [ ] Error logging enabled

---

## 📞 Support

Jika ada error saat setup:

1. **Check PHP error log:**
   ```
   D:\xamppp\apache\logs\error.log
   ```

2. **Check SMTP debug (development only):**
   ```php
   $mail->SMTPDebug = SMTP::DEBUG_SERVER; // Show SMTP dialog
   ```

3. **Test SMTP connection:**
   ```bash
   telnet smtp.gmail.com 587
   ```

---

**Status:** ✅ **FOLLOW THIS GUIDE CAREFULLY**

Email configuration adalah langkah PENTING untuk fitur OTP berfungsi dengan baik!
