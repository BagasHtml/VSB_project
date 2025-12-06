<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="../../Design/Css/auth.css">
</head>
<body>

<div class="wrapper">
    <div class="form-box">
        <h2>Daftar Akun</h2>

        <form action="../../service/register.php" method="post">
            <div class="input-box">
                <input type="text" name="username" required>
                <label>Nama Lengkap</label>
            </div>

            <div class="input-box">
                <input type="email" name="email" required>
                <label>Email</label>
            </div>

            <div class="input-box">
                <input type="password" name="password" required>
                <label>Password</label>
            </div>

            <button class="btn" type="submit">Daftar</button>

            <p class="switch">
                Sudah punya akun?
                <a href="form_login.php">Login</a>
            </p>
        </form>
    </div>
</div>

<script src="/Design/script.js"></script>
</body>
</html>
