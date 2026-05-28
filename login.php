<?php
session_start();
include 'db.php';
require_once 'csrf.php';

$error = '';
$notice = '';

if (isset($_GET['registered'])) {
    $notice = 'Registration submitted. Please wait for admin approval before logging in.';
}

if (isset($_SESSION['user_id'])) {
    header($_SESSION['role'] === 'admin' ? 'Location: admin.php' : 'Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf($_POST['csrf_token'] ?? '')) {
        $error = 'Invalid or expired security token. Please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            $error = 'Please enter your email and password.';
        } else {
            $stmt = mysqli_prepare($conn, "SELECT id, fullname, email, password, role, approval_status FROM users WHERE email = ? LIMIT 1");
            mysqli_stmt_bind_param($stmt, "s", $email);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $user = $result ? mysqli_fetch_assoc($result) : null;

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Invalid email or password.';
            } elseif ($user['role'] !== 'admin' && $user['approval_status'] !== 'Approved') {
                $error = $user['approval_status'] === 'Pending'
                    ? 'Your registration is pending admin approval.'
                    : 'Your registration has been rejected. Contact the barangay office for help.';
            } else {
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                ensure_csrf_token();

                header($user['role'] === 'admin' ? 'Location: admin.php' : 'Location: dashboard.php');
                exit();
            }
        }
    }
}

$csrf = ensure_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login | Arawan E-Bayanan</title>
  <link rel="stylesheet" href="style.css?v=3">
</head>
<body>
  <div class="page-bg"></div>
  <div class="page-grid"></div>

  <nav class="navbar auth-nav">
    <a class="brand" href="index.php"><span class="brand-icon">⌂</span>Arawan E-Bayanan</a>
    <a class="btn btn-ghost" href="index.php">Back to Home</a>
  </nav>

  <main class="auth-wrapper">
    <article class="auth-card">
      <div class="auth-brand-header">
        <span class="auth-brand-logo">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 12l4 4 8-8"></path></svg>
        </span>
        <span class="auth-brand-name">Arawan E-Bayanan</span>
        <span class="auth-brand-sub">Resident login portal</span>
      </div>

      <section class="auth-panel active">
        <h1 class="auth-heading">Welcome back</h1>
        <p class="auth-subheading">Sign in to access your dashboard and manage requests.</p>

        <?php if ($notice): ?>
          <p class="auth-alert success visible" role="status"><?php echo htmlspecialchars($notice); ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
          <p class="auth-error" role="alert"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST" class="auth-form">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
          <div class="form-group">
            <label class="form-label" for="email">Email</label>
            <div class="form-input-wrap">
              <svg class="form-input-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16v16H4z"></path><polyline points="4,7 12,13 20,7"></polyline></svg>
              <input id="email" class="form-input" type="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="password">Password</label>
            <div class="form-input-wrap">
              <svg class="form-input-icon" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="9" rx="2"></rect><path d="M8 11V7a4 4 0 0 1 8 0v4"></path></svg>
              <input id="password" class="form-input" type="password" name="password" placeholder="••••••••" required>
            </div>
          </div>

          <button type="submit" class="btn-auth" aria-label="Sign in"><span class="btn-label">Sign in</span></button>
        </form>

        <p class="auth-footer-note">Don't have an account? <a href="register.php">Create one</a>.</p>
      </section>

      <div class="security-badge">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3l8 4v5c0 4-2.7 7.3-8 9-5.3-1.7-8-5-8-9V7z"></path></svg>
        Secure login with resident account validation
      </div>
    </article>
  </main>
</body>
</html>
