<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Knowledge Battle</title>
    <link rel="stylesheet" href="../../Design/Css/auth.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<div class="wrapper">
    <div class="form-box">
        <!-- Step 1: Email Verification -->
        <div id="step-email">
            <h2><i class="bi bi-person-plus-fill"></i> Daftar Akun</h2>
            <p class="form-subtitle">Masukkan email untuk memulai</p>

            <?php
            session_start();
            $error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
            $success = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : '';
            
            if($error): ?>
            <div class="error-message">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?php
                switch($error) {
                    case 'email_exists': echo 'Email sudah terdaftar. Silakan masuk atau gunakan email lain'; break;
                    case 'invalid_email': echo 'Format email tidak valid'; break;
                    case 'otp_failed': echo 'Gagal mengirim OTP. Periksa email dan coba lagi'; break;
                    case 'rate_limit': echo 'Terlalu banyak permintaan. Coba lagi dalam beberapa menit'; break;
                    case 'email_not_verified': echo 'Email belum diverifikasi. Mulai dari awal'; break;
                    case 'empty_fields': echo 'Semua field harus diisi'; break;
                    case 'invalid_username': echo 'Username harus 3-20 karakter, hanya huruf, angka, dan underscore'; break;
                    case 'password_too_short': echo 'Password minimal 6 karakter'; break;
                    case 'password_mismatch': echo 'Password tidak cocok'; break;
                    case 'username_exists': echo 'Username sudah digunakan'; break;
                    case 'registration_failed': echo 'Pendaftaran gagal. Coba lagi'; break;
                    default: echo htmlspecialchars($error);
                }
                ?>
            </div>
            <?php endif; ?>

            <form action="../../service/send_otp.php" method="post">
                <div class="input-box">
                    <input type="email" name="email" required autocomplete="email" placeholder="nama@example.com">
                    <label><i class="bi bi-envelope"></i> Alamat Email</label>
                </div>

                <button class="btn" type="submit">
                    <i class="bi bi-send"></i> Kirim OTP
                </button>

                <div class="social-divider">
                    <span>atau daftar dengan</span>
                </div>

                <div class="social-buttons">
                    <a href="../../service/oauth/google_login.php?redirect=register" class="social-btn google" title="Daftar dengan Google">
                        <i class="bi bi-google"></i> Google
                    </a>
                    <a href="../../service/oauth/facebook_login.php?redirect=register" class="social-btn facebook" title="Daftar dengan Facebook">
                        <i class="bi bi-facebook"></i> Facebook
                    </a>
                </div>

                <p class="switch">
                    Sudah punya akun?
                    <a href="form_login.php">Masuk sekarang</a>
                </p>
            </form>
        </div>

        <!-- Step 2: OTP Verification (Hidden by default) -->
        <div id="step-otp" style="display: none;">
            <h2><i class="bi bi-shield-check"></i> Verifikasi Email</h2>
            <p class="form-subtitle">Masukkan kode OTP yang dikirim ke email</p>

            <div id="otp-email" class="info-message">
                <i class="bi bi-info-circle"></i> Kode telah dikirim ke: <strong id="email-display"></strong>
            </div>

            <form action="../../service/verify_otp.php" method="post">
                <input type="hidden" name="email" id="otp-email-input">

                <div class="otp-inputs">
                    <input type="text" class="input-box otp-input" name="otp" maxlength="1" pattern="[0-9]" required autocomplete="off">
                    <input type="text" class="input-box otp-input" name="otp" maxlength="1" pattern="[0-9]" required autocomplete="off">
                    <input type="text" class="input-box otp-input" name="otp" maxlength="1" pattern="[0-9]" required autocomplete="off">
                    <input type="text" class="input-box otp-input" name="otp" maxlength="1" pattern="[0-9]" required autocomplete="off">
                    <input type="text" class="input-box otp-input" name="otp" maxlength="1" pattern="[0-9]" required autocomplete="off">
                    <input type="text" class="input-box otp-input" name="otp" maxlength="1" pattern="[0-9]" required autocomplete="off">
                </div>

                <button class="btn" type="submit">
                    <i class="bi bi-check-circle"></i> Verifikasi OTP
                </button>

                <div class="timer">
                    Kode kadaluarsa dalam <span class="time" id="timer">5:00</span>
                    <button type="button" class="resend-btn" id="resend-btn">Kirim ulang</button>
                </div>

                <p class="switch">
                    <button type="button" onclick="backToEmail()" style="background:none; border:none; color:#FF2800; cursor:pointer; text-decoration:underline;">
                        Gunakan email lain
                    </button>
                </p>
            </form>
        </div>

        <!-- Step 3: Profile Setup -->
        <div id="step-profile" style="display: none;">
            <h2><i class="bi bi-person"></i> Lengkapi Profil</h2>
            <p class="form-subtitle">Isi informasi akun Anda</p>

            <form action="../../service/complete_register.php" method="post">
                <input type="hidden" name="email" id="profile-email-input">

                <div class="input-box">
                    <input type="text" name="username" required autocomplete="off" placeholder="nama_pengguna">
                    <label><i class="bi bi-person"></i> Nama Pengguna (3-20 karakter)</label>
                </div>

                <div class="input-box">
                    <input type="password" name="password" id="password" required autocomplete="new-password" placeholder="••••••••">
                    <label><i class="bi bi-lock"></i> Kata Sandi (minimal 6 karakter)</label>
                </div>

                <div class="input-box">
                    <input type="password" name="password_confirm" id="password_confirm" required autocomplete="new-password" placeholder="••••••••">
                    <label><i class="bi bi-lock-check"></i> Konfirmasi Kata Sandi</label>
                </div>

                <div id="password-strength" style="margin: 15px 0; display:none; position: relative; z-index: 1;">
                    <div style="font-size: 12px; color: #999; margin-bottom: 5px; font-weight: 600;">
                        Kekuatan password: <span id="strength-text">Lemah</span>
                    </div>
                    <div style="width: 100%; height: 6px; background: rgba(255,255,255,0.1); border-radius: 3px; overflow: hidden; box-shadow: inset 0 1px 2px rgba(0,0,0,0.3);">
                        <div id="strength-bar" style="width: 0%; height: 100%; background: #ef4444; transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 3px; box-shadow: 0 0 10px rgba(239, 68, 68, 0.5);"></div>
                    </div>
                </div>

                <button class="btn" type="submit">
                    <i class="bi bi-check-circle"></i> Selesai Pendaftaran
                </button>

                <p class="switch">
                    Sudah punya akun?
                    <a href="form_login.php">Masuk sekarang</a>
                </p>
            </form>
        </div>
    </div>
</div>

<script>
// OTP Input Handler
document.querySelectorAll('.otp-input').forEach((input, index) => {
    input.addEventListener('input', (e) => {
        if (e.target.value.length === 1 && index < 5) {
            document.querySelectorAll('.otp-input')[index + 1].focus();
        }
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && e.target.value === '' && index > 0) {
            document.querySelectorAll('.otp-input')[index - 1].focus();
        }
    });
});

// Timer
function startTimer() {
    let time = 300; // 5 minutes
    const timerEl = document.getElementById('timer');
    const resendBtn = document.getElementById('resend-btn');
    
    const interval = setInterval(() => {
        time--;
        const minutes = Math.floor(time / 60);
        const seconds = time % 60;
        timerEl.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        
        if (time <= 0) {
            clearInterval(interval);
            resendBtn.disabled = false;
            timerEl.textContent = 'Kadaluarsa';
        }
    }, 1000);
}

// Back to email
function backToEmail() {
    document.getElementById('step-otp').style.display = 'none';
    document.getElementById('step-email').style.display = 'block';
}

// Move to OTP step
function moveToOTP(email) {
    document.getElementById('email-display').textContent = email;
    document.getElementById('otp-email-input').value = email;
    document.getElementById('step-email').style.display = 'none';
    document.getElementById('step-otp').style.display = 'block';
    document.querySelectorAll('.otp-input')[0].focus();
    startTimer();
}

// Move to profile step
function moveToProfile(email) {
    document.getElementById('profile-email-input').value = email;
    document.getElementById('step-otp').style.display = 'none';
    document.getElementById('step-profile').style.display = 'block';
}

// Check localStorage for state
window.addEventListener('load', () => {
    const state = sessionStorage.getItem('register_state');
    const email = sessionStorage.getItem('register_email');
    
    if (state === 'otp' && email) {
        moveToOTP(email);
    } else if (state === 'profile' && email) {
        moveToProfile(email);
    }
});

// Password strength checker with text feedback
document.getElementById('password')?.addEventListener('input', function() {
    const strength = document.getElementById('password-strength');
    const bar = document.getElementById('strength-bar');
    const text = document.getElementById('strength-text');
    const password = this.value;
    let score = 0;
    
    strength.style.display = 'block';
    
    if (password.length >= 6) score++;
    if (password.length >= 10) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^a-zA-Z0-9]/.test(password)) score++;
    
    const percentage = (score / 5) * 100;
    bar.style.width = percentage + '%';
    
    let strengthLabel = 'Lemah';
    let color = '#ef4444';
    
    if (score <= 2) {
        strengthLabel = 'Lemah';
        color = '#ef4444';
    } else if (score <= 3) {
        strengthLabel = 'Sedang';
        color = '#f97316';
    } else if (score <= 4) {
        strengthLabel = 'Kuat';
        color = '#eab308';
    } else {
        strengthLabel = 'Sangat Kuat';
        color = '#22c55e';
    }
    
    bar.style.background = color;
    bar.style.boxShadow = `0 0 10px ${color}80`;
    text.textContent = strengthLabel;
    text.style.color = color;
});

// Form submission handlers
document.querySelector('#step-email form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const email = formData.get('email');
    const btn = e.target.querySelector('button');
    const originalHTML = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Sedang mengirim...';
    
    try {
        const response = await fetch('../../service/send_otp.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            sessionStorage.setItem('register_state', 'otp');
            sessionStorage.setItem('register_email', email);
            
            // Show success message briefly
            btn.style.background = '#22c55e';
            btn.innerHTML = '<i class="bi bi-check-circle"></i> OTP Dikirim!';
            
            setTimeout(() => {
                moveToOTP(email);
            }, 800);
        } else {
            showErrorMessage(result.message, 'step-email');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    } catch (error) {
        showErrorMessage('Terjadi kesalahan. Coba lagi.', 'step-email');
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
});

document.querySelector('#step-otp form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const inputs = document.querySelectorAll('.otp-input');
    const otp = Array.from(inputs).map(i => i.value).join('');
    const email = document.getElementById('otp-email-input').value;
    const btn = e.target.querySelector('button');
    const originalHTML = btn.innerHTML;
    
    if (otp.length !== 6) {
        showErrorMessage('Masukkan kode OTP 6 digit lengkap', 'step-otp');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memverifikasi...';
    
    try {
        const response = await fetch('../../service/verify_otp.php', {
            method: 'POST',
            body: JSON.stringify({ email, otp }),
            headers: { 'Content-Type': 'application/json' }
        });
        
        const result = await response.json();
        
        if (result.success) {
            btn.style.background = '#22c55e';
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Verifikasi Berhasil!';
            
            sessionStorage.setItem('register_state', 'profile');
            setTimeout(() => {
                moveToProfile(email);
            }, 800);
        } else {
            showErrorMessage(result.message, 'step-otp');
            inputs.forEach(i => i.value = '');
            inputs[0].focus();
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    } catch (error) {
        showErrorMessage('Terjadi kesalahan. Coba lagi.', 'step-otp');
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
});
    
    const result = await response.json();
    
    if (result.success) {
        sessionStorage.setItem('register_state', 'profile');
        moveToProfile(email);
    } else {
        alert('Error: ' + result.message);
        inputs.forEach(i => i.value = '');
        inputs[0].focus();
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-check-circle"></i> Verifikasi OTP';
    };

// Resend OTP
document.getElementById('resend-btn')?.addEventListener('click', async (e) => {
    e.preventDefault();
    const email = document.getElementById('otp-email-input').value;
    const btn = e.target;
    
    btn.disabled = true;
    btn.textContent = 'Mengirim ulang...';
    
    try {
        const response = await fetch('../../service/send_otp.php', {
            method: 'POST',
            body: new URLSearchParams({ email })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessMessage('OTP telah dikirim ulang!', 'step-otp');
            startTimer();
        } else {
            showErrorMessage(result.message, 'step-otp');
        }
    } catch (error) {
        showErrorMessage('Terjadi kesalahan. Coba lagi.', 'step-otp');
    }
    
    btn.disabled = false;
    btn.textContent = 'Kirim ulang';
});

// Helper function to show error messages
function showErrorMessage(message, stepId) {
    const step = document.getElementById(stepId);
    const existingError = step.querySelector('.error-message');
    if (existingError) existingError.remove();
    
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message';
    errorDiv.innerHTML = `<i class="bi bi-exclamation-circle-fill"></i> ${message}`;
    errorDiv.style.animation = 'slideDown 0.4s ease';
    
    const form = step.querySelector('form');
    form.parentNode.insertBefore(errorDiv, form);
    
    setTimeout(() => {
        if (existingError) existingError.remove();
    }, 5000);
}

// Helper function to show success messages
function showSuccessMessage(message, stepId) {
    const step = document.getElementById(stepId);
    const existingSuccess = step.querySelector('.success-message');
    if (existingSuccess) existingSuccess.remove();
    
    const successDiv = document.createElement('div');
    successDiv.className = 'success-message';
    successDiv.innerHTML = `<i class="bi bi-check-circle-fill"></i> ${message}`;
    successDiv.style.animation = 'slideDown 0.4s ease';
    
    const form = step.querySelector('form');
    form.parentNode.insertBefore(successDiv, form);
    
    setTimeout(() => {
        successDiv.remove();
    }, 4000);
}

// Profile form submission handler
document.querySelector('#step-profile form')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    const password = formData.get('password');
    const passwordConfirm = formData.get('password_confirm');
    const btn = e.target.querySelector('button');
    const originalHTML = btn.innerHTML;
    
    // Validation
    if (password !== passwordConfirm) {
        showErrorMessage('Password tidak cocok', 'step-profile');
        return;
    }
    
    if (password.length < 6) {
        showErrorMessage('Password minimal 6 karakter', 'step-profile');
        return;
    }
    
    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Membuat akun...';
    
    try {
        const response = await fetch('../../service/complete_register.php', {
            method: 'POST',
            body: formData
        });
        
        if (response.ok) {
            btn.style.background = '#22c55e';
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Akun Dibuat!';
            
            setTimeout(() => {
                window.location.href = '../../View/login_register/form_login.php?success=registration_complete';
            }, 1000);
        } else {
            const text = await response.text();
            const url = new URL(text, window.location.origin);
            const error = url.searchParams.get('error');
            const errorMessages = {
                'empty_fields': 'Semua field harus diisi',
                'invalid_username': 'Username harus 3-20 karakter, hanya huruf, angka, dan underscore',
                'password_too_short': 'Password minimal 6 karakter',
                'password_mismatch': 'Password tidak cocok',
                'username_exists': 'Username sudah digunakan',
                'email_exists': 'Email sudah terdaftar',
                'registration_failed': 'Pendaftaran gagal. Coba lagi'
            };
            
            const message = errorMessages[error] || 'Terjadi kesalahan. Coba lagi.';
            showErrorMessage(message, 'step-profile');
            btn.disabled = false;
            btn.innerHTML = originalHTML;
        }
    } catch (error) {
        showErrorMessage('Terjadi kesalahan. Coba lagi.', 'step-profile');
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }
});
</script>
</body>
</html>
