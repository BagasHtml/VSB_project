<?php
if(isset($_POST['login'])) {
    session_start();
    include '../../service/db.php';

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            if ($user['role'] === 'admin' || $user['role'] === 'developer') {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                header("Location: admin_panel.php");
                exit();
            } else {
                header("Location: admin_login.php?error=" . urlencode("Akses ditolak! Kamu bukan admin."));
                exit();
            }
        } else {
            header("Location: admin_login.php?error=" . urlencode("Password salah."));
            exit();
        }
    } else {
        header("Location: admin_login.php?error=" . urlencode("Email tidak ditemukan."));
        exit();
    }
}

$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.3.3/dist/tailwind.min.css" rel="stylesheet">
<style>
  body { background: linear-gradient(to right, #1f2937, #111827); font-family: 'Poppins', sans-serif; }
  .form-box { background: rgba(255,255,255,0.05); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.1); border-radius: 1rem; padding: 2rem; max-width: 400px; margin: 6rem auto; }
  .form-box h2 { font-size: 1.75rem; font-weight: 700; color: #f87171; text-align: center; margin-bottom: 1.5rem; }
  .input-box { position: relative; margin-bottom: 1rem; }
  .input-box input { width: 100%; padding: 0.75rem; border-radius: 0.5rem; border: none; background: rgba(255,255,255,0.1); color: white; }
  .input-box label { position: absolute; top: 50%; left: 1rem; transform: translateY(-50%); color: #9ca3af; pointer-events: none; transition: 0.3s; }
  .input-box input:focus + label, .input-box input:not(:placeholder-shown) + label { top: -0.5rem; left: 0.75rem; font-size: 0.75rem; color: #f87171; }
  .btn { width: 100%; padding: 0.75rem; border-radius: 0.5rem; background: #f87171; color: white; font-weight: 600; cursor: pointer; transition: 0.3s; border: none; }
  .btn:hover { background: #ef4444; }
  .error { color: #f87171; text-align: center; margin-bottom: 1rem; background: rgba(248, 113, 113, 0.1); padding: 0.75rem; border-radius: 0.5rem; }
</style>
</head>
<body>
<div class="form-box">
  <h2>Admin Login</h2>
  <?php if($error): ?>
    <div class='error'><?= $error ?></div>
  <?php endif; ?>
  <form method="post" action="">
    <div class="input-box">
      <input type="email" name="email" required placeholder=" ">
      <label>Email</label>
    </div>
    <div class="input-box">
      <input type="password" name="password" required placeholder=" ">
      <label>Password</label>
    </div>
    <button type="submit" name="login" class="btn">Masuk sebagai Admin</button>
  </form>
</div>
</body>
</html>