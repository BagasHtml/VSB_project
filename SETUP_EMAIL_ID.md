# 📧 Panduan Setup Email OTP - Knowledge Battle

## Masalah Saat Ini
Email belum terkirim karena **kredensial email belum dikonfigurasi**. File sudah siap, hanya perlu isi dengan data email Anda.

---

## Solusi: Setup Email dalam 3 Langkah

### 📍 Langkah 1: Siapkan Gmail (5 menit)

Jika menggunakan Gmail:

1. **Buka:** https://myaccount.google.com
2. **Klik:** Security (Keamanan) di sidebar kiri
3. **Cari:** "2-Step Verification"
4. **Klik:** Enable 2-Step Verification
5. **Ikuti:** Instruksi Google (SMS atau authenticator app)

✅ Selesai dengan langkah ini? Lanjut ke Langkah 2

---

### 📍 Langkah 2: Dapatkan App Password (2 menit)

1. **Buka:** https://myaccount.google.com/apppasswords
2. **Pilih:**
   - App: `Mail`
   - Device: `Windows Computer` (atau device Anda)
3. **Klik:** `Generate`
4. **Copy:** Password 16 karakter yang muncul (contoh: `abcd efgh ijkl mnop`)
5. **Simpan:** Di tempat aman

✅ Selesai dengan langkah ini? Lanjut ke Langkah 3

---

### 📍 Langkah 3: Isi Konfigurasi Email (1 menit)

**File untuk diedit:** `/service/config.email.php`

**Buka file tersebut dan ubah:**

```php
'username' => 'your-email@gmail.com',  ← GANTI dengan Gmail Anda
'password' => 'your-app-password',     ← GANTI dengan 16-char password dari Langkah 2
'email' => 'your-email@gmail.com',     ← GANTI dengan Gmail Anda
```

**Contoh hasil (jangan copy ini, gunakan data Anda):**
```php
'username' => 'budi.hermawan@gmail.com',
'password' => 'abcd efgh ijkl mnop',
'email' => 'budi.hermawan@gmail.com',
```

✅ **Selesai! Email siap digunakan.**

---

## Test Email Sekarang

1. **Buka browser:** http://localhost/VSB_project/View/login_register/form_register.php
2. **Masukkan email:** Email test Anda (misalnya: test@gmail.com)
3. **Klik:** "Kirim OTP"
4. **Tunggu:** 2-3 detik
5. **Cek email:** Inbox email Anda
6. ✅ **Email OTP sudah terkirim!**

---

## Troubleshooting

### ❌ "SMTP Error: Could not authenticate"

**Artinya:** Password salah atau tidak dikonfigurasi

**Solusi:**
1. Pastikan 2FA Gmail sudah enabled
2. Pastikan password 16 karakter (dengan spasi)
3. Copy-paste password dengan teliti (jangan ada spasi ekstra)
4. Cek konfigurasi di `config.email.php` benar

### ❌ "Email tidak dikirim ke inbox"

**Artinya:** Email mungkin masuk spam atau email tidak terkirim

**Solusi:**
1. Cek folder **Spam/Junk**
2. Tunggu 3-5 menit untuk delivery
3. Coba email yang berbeda
4. Cek konfigurasi SMTP benar

### ❌ "OTP test: 123456"

**Artinya:** Email belum dikonfigurasi, system pakai fallback testing

**Solusi:**
1. Isi kredensial email di `config.email.php`
2. Simpan file
3. Refresh browser dan coba lagi

---

## Untuk Email Provider Lain

### Microsoft Outlook/Hotmail

Edit `config.email.php`:
```php
'host' => 'smtp-mail.outlook.com',
'port' => 587,
'secure' => 'tls',
'username' => 'your-email@outlook.com',
'password' => 'your-outlook-password',
'email' => 'your-email@outlook.com',
```

### Yahoo Mail

Edit `config.email.php`:
```php
'host' => 'smtp.mail.yahoo.com',
'port' => 465,
'secure' => 'ssl',
'username' => 'your-email@yahoo.com',
'password' => 'your-app-password', // Generate di Yahoo
'email' => 'your-email@yahoo.com',
```

### Server Email Custom

Edit `config.email.php` dengan detail dari provider Anda:
```php
'host' => 'smtp.provider-anda.com',
'port' => 587,           // atau 465 untuk SSL
'secure' => 'tls',       // atau 'ssl'
'username' => 'username-anda',
'password' => 'password-anda',
'email' => 'email-anda@domain.com',
```

---

## Development Mode (Pakai Test OTP)

Jika ingin test tanpa setup email:

1. **Biarkan** `config.email.php` dengan placeholder values
2. **Saat register** system akan return OTP test
3. **Gunakan** OTP tersebut untuk test registration flow
4. **Selesai** test, ubah ke konfigurasi real email

⚠️ **Jangan pakai di production!**

---

## Keamanan

### Penting!
- **Jangan share** file `config.email.php` dengan orang lain
- **Jangan push ke GitHub** file ini (sudah harus di .gitignore)
- **Gunakan app password** Gmail (bukan main password)
- **Enable 2FA** pada email account

### Production Checklist
- [ ] Email dikonfigurasi dengan benar
- [ ] Test email terkirim
- [ ] Hapus file `config.email.php` dari version control
- [ ] Gunakan environment variables (untuk deployment)
- [ ] Setup HTTPS
- [ ] Monitor email delivery

---

## FAQ

**Q: Apakah password Gmail saya aman?**  
A: Ya! Kita menggunakan app password, bukan main password. App password bisa dihapus kapan saja dan hanya bisa dipakai untuk email.

**Q: Apakah bisa pakai email biasa (bukan Gmail)?**  
A: Ya! Bisa pakai Outlook, Yahoo, atau SMTP server custom. Tinggal ubah konfigurasi di `config.email.php`.

**Q: Berapa lama OTP berlaku?**  
A: 5 menit. Setelah itu harus request OTP baru.

**Q: Berapa kali bisa request OTP?**  
A: 3 kali per 15 menit (untuk prevent spam). Tunggu 15 menit jika limit tercapai.

**Q: Email sudah dikonfigurasi, kenapa masih "OTP test"?**  
A: Refresh browser cache (Ctrl+Shift+R atau Cmd+Shift+R). Jika masih, cek apakah file `config.email.php` sudah tersimpan dengan benar.

---

## Kontak Bantuan

Jika ada masalah:
1. Cek folder inbox dan spam
2. Baca bagian Troubleshooting di atas
3. Pastikan semua kredensial benar
4. Coba dengan email provider berbeda
5. Check console browser (F12) untuk error messages

---

**Catatan:** Sistem sudah siap! Hanya perlu isi email credentials di file `config.email.php` dan email akan langsung berfungsi.
