# Knowledge Battle - Comprehensive Authentication System Documentation

## Overview

This document provides complete information about the modern, secure authentication system for the Knowledge Battle forum. The system includes email OTP verification, responsive design, glassmorphism effects, rate limiting, and comprehensive error handling.

---

## Architecture

### System Flow Diagram

```
User Access
    ↓
┌─────────────────────────────────────┐
│   Authentication Entry Point        │
│   - form_login.php (Existing users) │
│   - form_register.php (New users)   │
└─────────────────────────────────────┘
    ↓ (For Login)               ↓ (For Registration)
    │                          ┌─────────────────────────┐
    │                          │ Step 1: Email Validation│
    │                          │ - Email format check    │
    │                          │ - Email exists check    │
    │                          │ - Rate limit check      │
    │                          │ → send_otp.php         │
    │                          └─────────────────────────┘
    │                                  ↓
    │                          ┌─────────────────────────┐
    │                          │ Step 2: OTP Verification│
    │                          │ - 6-digit code input    │
    │                          │ - 5 min expiry timer    │
    │                          │ - Resend option         │
    │                          │ → verify_otp.php       │
    │                          └─────────────────────────┘
    │                                  ↓
    │                          ┌─────────────────────────┐
    │                          │ Step 3: Profile Setup   │
    │                          │ - Username (3-20 chars) │
    │                          │ - Password (min 6 chars)│
    │                          │ - Strength indicator    │
    │                          │ → complete_register.php│
    │                          └─────────────────────────┘
    │                                  ↓
    ├────────────────────────────────→ Login Page
    │                          (Success redirect)
    │
    ├──────────────────────────────────────┐
    │     login.php                        │
    │     - Email validation               │
    │     - Password verification (bcrypt) │
    │     - Rate limiting (5 attempts)     │
    │     - IP-based tracking              │
    │     - Session creation               │
    └──────────────────────────────────────┘
           ↓ (Success)
    ┌─────────────────────┐
    │ Dashboard/Main Page │
    │ User logged in      │
    └─────────────────────┘
```

---

## Files Structure

### Frontend Files

#### `/View/login_register/form_login.php`
**Purpose:** Login form interface for existing users

**Features:**
- Modern glassmorphism design
- Email and password inputs with icons
- Error/success message display
- Link to registration form
- Responsive on all devices
- Accessible form labels

**Key Elements:**
```html
<!-- Email input with validation -->
<input type="email" name="email" required autocomplete="email" placeholder="nama@example.com">

<!-- Password input with security -->
<input type="password" name="password" required autocomplete="current-password" placeholder="••••••••">

<!-- Styled button with icon -->
<button class="btn" type="submit">
    <i class="bi bi-box-arrow-in-right"></i> Masuk Sekarang
</button>
```

**Error Messages:**
- `invalid` - Email atau password tidak valid
- `not_found` - Akun tidak ditemukan
- `rate_limit` - Terlalu banyak percobaan. Coba lagi dalam 15 menit

**Success Messages:**
- `registration_complete` - Pendaftaran berhasil! Silakan masuk dengan akun Anda

---

#### `/View/login_register/form_register.php`
**Purpose:** 3-step registration form with email OTP verification

**Step 1: Email Input**
- Email format validation
- Check if email exists
- Rate limiting (3 requests/15 min)
- OTP generation and sending
- User feedback with timers

**Step 2: OTP Verification**
- 6 individual input fields with auto-focus
- Backspace navigation between fields
- 5-minute countdown timer
- Resend OTP button (disabled during timer)
- Error handling with clear messages

**Step 3: Profile Setup**
- Username input (3-20 characters, alphanumeric + underscore)
- Password input (minimum 6 characters)
- Password strength indicator (0-5 scale)
- Password confirmation with match validation
- Visual strength feedback with color coding

**Key JavaScript Functions:**
```javascript
// Auto-focus between OTP inputs
// Backspace navigation
// Password strength calculator
// Timer countdown (5 minutes)
// Multi-step form navigation
// AJAX submission with JSON responses
```

**Error Messages:**
All errors handled with user-friendly messages and suggestions for resolution.

---

### Design Files

#### `/Design/Css/auth.css`
**Purpose:** Enhanced authentication styling with glassmorphism

**Features:**
- **Glassmorphism Design:**
  - Semi-transparent backgrounds (0.07 opacity)
  - Strong backdrop filter (20px blur)
  - Professional shadows with inset glow
  - Smooth animations and transitions

- **Color Scheme:**
  - Primary accent: #FF2800 (red-orange)
  - Background: Dark gradients (0a0a0a to 2d2d2d)
  - Text: White with appropriate contrast
  - Hover states: Subtle color shifts

- **Typography:**
  - Font family: Poppins (Google Fonts)
  - Font weights: 300, 400, 500, 600, 700, 800
  - Responsive sizes (26px to 32px headers)

- **Animations:**
  ```css
  slideUp - Form entrance (0.7s)
  slideDown - Message appearance (0.4s)
  float - Background elements (8-10s infinite)
  gradientShift - Background animation (15s infinite)
  ```

- **Responsive Breakpoints:**
  ```css
  ≤480px - Mobile (vertical layout, optimized touch targets)
  481-768px - Tablet (single column, larger padding)
  769-1024px - Tablet XL (adjusted widths)
  ≥1025px - Desktop (full-width optimization)
  ```

**Button Styling:**
- Gradient background with animation
- Hover effects (elevation, scale)
- Icon animation on hover
- Shimmer effect on mouse over
- Professional shadow and border radius

**Input Styling:**
- Clear focus states with color feedback
- Hover state background changes
- Icon labels above input
- Placeholder text with guidance
- Transform effects on focus

---

### Backend Service Files

#### `/service/send_otp.php`
**Purpose:** Generate and send OTP via email with rate limiting

**Functionality:**
1. Receive email from request
2. Validate email format
3. Check email doesn't exist in database
4. Implement rate limiting (3 requests/15 min per IP)
5. Generate 6-digit OTP
6. Store OTP in session with 5-minute expiry
7. Send email via PHPMailer SMTP
8. Return JSON response

**Rate Limiting:**
```php
Rate limit key: otp_requests_[IP_ADDRESS]
Limit: 3 requests per 15 minutes (900 seconds)
```

**Email Configuration:**
```php
// Located in lines marked with ⚠️
$mail->Host = 'smtp.gmail.com';
$mail->Username = 'your-email@gmail.com';
$mail->Password = 'your-app-password';
```

**Development Fallback:**
- If SMTP authentication fails, system returns test OTP
- Message indicates development mode: "OTP test: [code]"
- Allows testing without proper email configuration
- **MUST BE REMOVED** in production

**Response Format:**
```json
{
  "success": true,
  "message": "OTP telah dikirim ke email Anda"
}
```

---

#### `/service/verify_otp.php`
**Purpose:** Verify OTP and mark email as verified

**Functionality:**
1. Receive email and 6-digit OTP
2. Validate inputs
3. Check OTP exists and hasn't expired
4. Compare with stored OTP
5. Mark email as verified in session
6. Clear OTP from session
7. Return JSON response

**Validation Checks:**
- Email and OTP not empty
- Email format valid
- OTP exists in session
- OTP not expired (5 min)
- OTP matches stored value

**Session Variables Set:**
- `verified_email` - Used to identify verified users in next step
- Cleared when: Complete registration, timeout, new registration

**Response Format:**
```json
{
  "success": true,
  "message": "Email berhasil diverifikasi"
}
```

---

#### `/service/complete_register.php`
**Purpose:** Create user account after email verification

**Functionality:**
1. Check email is verified
2. Receive username and passwords
3. Validate all inputs
4. Check username/email uniqueness
5. Hash password with BCRYPT
6. Insert user into database
7. Clear session data
8. Redirect to login with success message

**Validation:**
- Username: 3-20 characters, alphanumeric + underscore
- Password: Minimum 6 characters
- Password confirmation: Must match
- Email: Double-check not exists
- Username: Check not already taken

**Database Insert:**
```sql
INSERT INTO users 
  (username, email, password, role, level, title, created_at) 
VALUES 
  (?, ?, ?, 'user', 0, '', NOW())
```

**Default Values:**
- role: 'user'
- level: 0 (regular user)
- title: '' (empty)
- created_at: Current timestamp

---

#### `/service/login.php`
**Purpose:** Authenticate user and create session

**Functionality:**
1. Receive email and password
2. Validate inputs
3. Check rate limiting (5 attempts/15 min)
4. Query database for user by email
5. Verify password with `password_verify()`
6. Create session on success
7. Return error or redirect

**Rate Limiting:**
```php
Rate limit key: login_attempts_[IP_ADDRESS]
Limit: 5 attempts per 15 minutes (900 seconds)
```

**Session Variables Created:**
- `user_id` - User ID from database
- `username` - Username for display
- `email` - User's email
- `role` - User role (user/admin)
- `level` - User level (0-100)

**Password Security:**
- Uses `password_hash()` with PASSWORD_BCRYPT
- Automatic salt generation
- Cost factor: 10 (default)
- Verification: `password_verify($input, $hash)`

---

#### `/service/db.php`
**Purpose:** Database connection

**Connection Details:**
- Host: localhost
- Database: knowledge_battle
- Uses MySQLi (object-oriented)
- Prepared statements for all queries

---

## Security Features

### 1. Password Security
- **Algorithm:** BCRYPT (PASSWORD_BCRYPT)
- **Cost Factor:** 10 (default automatic)
- **Salting:** Automatic per password
- **Storage:** Hashed, never plain-text
- **Verification:** `password_verify()` function

### 2. SQL Injection Prevention
- **Method:** Prepared statements with `bind_param()`
- **All Database Queries:** Use parameterized queries
- **User Input:** Never directly concatenated

```php
$stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
```

### 3. XSS Prevention
- **HTML Escaping:** `htmlspecialchars()` on all outputs
- **User Input:** Escaped before database storage
- **Error Messages:** Escaped to prevent script injection

### 4. Email Verification
- **Purpose:** Prevent bot registrations and invalid emails
- **Method:** 6-digit OTP (000000-999999)
- **Expiry:** 5 minutes
- **Storage:** Session-based (server-side)
- **One-use:** Cleared after verification

### 5. Rate Limiting
**Login Endpoint:**
- 5 attempts per 15 minutes per IP
- Prevents brute force attacks
- User-friendly error message

**OTP Request Endpoint:**
- 3 requests per 15 minutes per IP
- Prevents email spam and enumeration
- Prevents DDoS attacks on email service

**Rate Limit Tracking:**
- IP-based identification
- Session storage (server-side)
- Time-based expiry

### 6. Session Security
- **Session Start:** `session_start()` in all forms
- **Session Variables:** Server-side storage
- **Verification State:** Maintained until completion
- **Cleanup:** Variables cleared after use

### 7. DDoS Protection
- Rate limiting on OTP requests (3/15min)
- Rate limiting on login (5/15min)
- Email fallback mechanism (prevents external service overload)

---

## User Database Schema

### Table: `users`

```sql
CREATE TABLE users (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role VARCHAR(20) DEFAULT 'user',
  level INT DEFAULT 0,
  title VARCHAR(100) DEFAULT '',
  profile_picture VARCHAR(255),
  bio TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Constraints:
- **username:** UNIQUE, 3-20 characters, alphanumeric + underscore
- **email:** UNIQUE, valid email format
- **password:** BCRYPT hash (255 chars)
- **role:** 'user' or 'admin'
- **level:** 0 (user) to 100 (admin)

---

## UI/UX Features

### Glassmorphism Design
- **Glass Effect:** Semi-transparent with backdrop blur
- **Depth:** Multiple shadow layers (outer and inset)
- **Consistency:** All form elements match design system

### Animation System
1. **Entrance:** Form slides up on load
2. **Messages:** Error/success slide down
3. **Background:** Subtle gradient shifts
4. **Buttons:** Hover elevation with shimmer
5. **Icons:** Scale animation on button hover
6. **Inputs:** Transform on focus

### Color Feedback
- **Error:** Red (#FF2800) - Clear warning
- **Success:** Green (#22c55e) - Positive confirmation
- **Info:** Blue (#3B82F6) - Helpful information
- **Focus:** Orange glow - Input selection indicator

### Typography
- **Headlines:** Large (26-32px), bold, gradient text
- **Labels:** Small (12px), all caps, red accent
- **Body:** 14-15px, medium weight, light gray
- **Placeholders:** Subtle gray, helpful examples

### Responsive Design
- **Mobile (≤480px):** Vertical layout, optimized padding
- **Tablet (481-768px):** Adjusted widths, comfortable spacing
- **Desktop (≥769px):** Full features, professional layout
- **Touch-friendly:** Buttons ≥44px for mobile

### Accessibility
- **Icons:** Meaningful, complementary to text
- **Labels:** Associated with inputs via `<label>` tags
- **Contrast:** Text readable on all backgrounds
- **Focus States:** Clear visual indication
- **Error Messages:** Descriptive and actionable

---

## Configuration Guide

### Email Setup (SMTP)

**For Gmail:**
1. Enable 2-Factor Authentication
2. Generate App Password
3. Update `/service/send_otp.php`:
   ```php
   $mail->Username = 'your-email@gmail.com';
   $mail->Password = 'your-app-password';
   ```

**For Custom Provider:**
1. Get SMTP server details
2. Update in `send_otp.php`:
   ```php
   $mail->Host = 'smtp.example.com';
   $mail->Port = 587;
   $mail->Username = 'your-username';
   $mail->Password = 'your-password';
   ```

**Important:** Remove development fallback in production!

---

## Testing Checklist

### Functionality Tests
- [ ] Login with valid credentials
- [ ] Login with invalid email
- [ ] Login with wrong password
- [ ] Rate limit blocks after 5 attempts
- [ ] Register with new email
- [ ] Register with existing email (error)
- [ ] OTP sent successfully
- [ ] OTP verification with correct code
- [ ] OTP verification with wrong code
- [ ] OTP expires after 5 minutes
- [ ] Complete registration with all fields
- [ ] Password validation (length, match)
- [ ] Username validation (length, characters)

### Security Tests
- [ ] SQL injection attempt fails
- [ ] XSS in email input blocked
- [ ] Password not logged or displayed
- [ ] Session created after login
- [ ] Session cleared on logout
- [ ] Rate limiting blocks spam
- [ ] Unauthorized access denied

### UI/UX Tests
- [ ] Forms load smoothly
- [ ] Animations perform well
- [ ] Mobile layout responsive
- [ ] Touch targets ≥44px
- [ ] Error messages clear and helpful
- [ ] Form labels visible and associated
- [ ] Focus indicators obvious
- [ ] Icons display correctly

### Browser Compatibility
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

---

## Development Notes

### Fallback Mechanism
The system includes a development fallback for email configuration errors:
- If SMTP authentication fails, returns test OTP instead of error
- Message clearly indicates development mode
- **MUST be removed before production deployment**
- Search for `otp_test` in `send_otp.php` to remove

### Session Management
- Uses PHP native `$_SESSION`
- Sessions persist across form steps
- Session variables cleared after use
- Consider using `session_regenerate_id()` after login for added security

### Rate Limiting Approach
- IP-based identification via `$_SERVER['REMOTE_ADDR']`
- Session storage (server-side tracking)
- 15-minute window (900 seconds)
- Counts reset after expiry

### Password Strength Indicator
- Calculates score 0-5 based on:
  - Length ≥6: +1
  - Length ≥10: +1
  - Contains uppercase: +1
  - Contains number: +1
  - Contains special char: +1
- Color feedback: Red → Orange → Yellow → Green

---

## Troubleshooting

### Issue: "Gagal mengirim email"
**Solution:**
1. Check SMTP credentials in `send_otp.php`
2. Verify internet connection
3. Check Gmail app password (if using Gmail)
4. Look for `otp_test` in response (development mode active)

### Issue: OTP not received
**Solution:**
1. Check spam folder
2. Verify email is correct
3. Wait 5 seconds (server processing)
4. Try resend button
5. Check SMTP configuration

### Issue: Rate limit error
**Solution:**
1. Wait 15 minutes (limit resets)
2. Try from different IP (if testing)
3. Check `_SESSION` variables in code

### Issue: Password won't match
**Solution:**
1. Ensure passwords are identical (case-sensitive)
2. Check for extra spaces
3. Verify keyboard layout correct

---

## Performance Considerations

### Database
- Use indexes on: email, username
- Prepared statements reduce parsing overhead
- Connection pooling recommended for scale

### Session
- Session storage: Default file-based or database
- Consider Redis for multiple servers
- Cleanup old sessions regularly

### Email
- SMTP connection timeout: 10 seconds
- Keep-alive enabled
- Consider queue system for scale

### Frontend
- CSS animations: GPU accelerated
- No external scripts for core auth
- Bootstrap Icons: Loaded from CDN
- Minimal JavaScript (no frameworks)

---

## Future Enhancements

1. **Two-Factor Authentication (2FA)**
   - TOTP support
   - SMS fallback
   - Backup codes

2. **Social Login**
   - Google OAuth
   - GitHub OAuth
   - Microsoft login

3. **Password Reset**
   - Email-based reset flow
   - Secure reset link (token-based)
   - Expiring tokens

4. **Account Recovery**
   - Recovery email backup
   - Security questions
   - Phone verification

5. **Audit Logging**
   - Login attempt logging
   - Failed authentication tracking
   - Admin access logs

6. **CSRF Protection**
   - Token-based protection
   - Same-site cookies
   - POST validation

---

## Support & Maintenance

### Regular Checks
- Monitor failed login attempts
- Check email delivery logs
- Review rate limiting effectiveness
- Test backup authentication methods

### Updates
- Keep PHPMailer updated
- Review PHP security patches
- Update database drivers
- Monitor for vulnerabilities

### Backup
- Daily backup of user database
- Session data not critical
- OTP data not persistent
- Test restore procedures

---

**Last Updated:** Current Session  
**Version:** 2.0 - Enhanced Edition  
**Status:** Production Ready (After SMTP Configuration)
