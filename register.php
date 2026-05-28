<?php
session_start();
include 'db.php';
require_once 'csrf.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name'] ?? '');
    $middle_name = trim($_POST['middle_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $id_number = trim($_POST['id_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = trim($_POST['csrf_token'] ?? '');

    if ($first_name === '' || $last_name === '' || $id_number === '' || $email === '' || $password === '') {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format.';
    } elseif (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error = 'Password must be at least 8 characters long, include an uppercase letter and a number.';
    } elseif (!validate_csrf($csrf_token)) {
        $error = 'Invalid or expired security token. Please try again.';
    } else {
        $fullname = trim($first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name);

        $check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check, "s", $email);
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($result) > 0) {
            $error = 'Email already exists.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "INSERT INTO users(fullname, first_name, middle_name, last_name, email, password, id_number) VALUES(?, ?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, "sssssss", $fullname, $first_name, $middle_name, $last_name, $email, $hashed_password, $id_number);

            if (mysqli_stmt_execute($stmt)) {
                header('Location: login.php?registered=1');
                exit();
            }
            $error = 'Registration failed. Please try again.';
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
  <title>Register | Arawan E-Bayanan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="page-bg"></div>
  <div class="page-grid"></div>

  <nav>
    <a class="brand" href="index.php">
      <span class="brand-icon">⌂</span>
      Arawan E-Bayanan
    </a>
    <a class="btn btn-ghost" href="login.php">Already have an account?</a>
  </nav>

  <main class="auth-wrapper">
    <article class="auth-card">
      <div class="auth-brand-header">
        <span class="auth-brand-logo">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 4L4 8v6c0 5 3 8 8 10s8-5 8-10V8z"></path></svg>
        </span>
        <span class="auth-brand-name">Arawan E-Bayanan</span>
        <span class="auth-brand-sub">Resident registration</span>
      </div>

      <section class="auth-panel active">
        <h1 class="auth-heading">Create your account</h1>
        <p class="auth-subheading">Register to submit requests, check announcements, and access your resident dashboard.</p>

        <?php if (!empty($error)): ?>
          <p class="auth-error" role="alert"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <form action="register.php" method="POST" class="auth-form">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">

          <div class="form-2col">
            <div class="form-group">
              <label class="form-label" for="first_name">First name</label>
              <input id="first_name" class="form-input" type="text" name="first_name" placeholder="First name" required value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
              <label class="form-label" for="last_name">Last name</label>
              <input id="last_name" class="form-input" type="text" name="last_name" placeholder="Last name" required value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>">
            </div>
          </div>

          <div class="form-group">
            <label class="form-label" for="middle_name">Middle name</label>
            <input id="middle_name" class="form-input" type="text" name="middle_name" placeholder="Optional" value="<?php echo htmlspecialchars($_POST['middle_name'] ?? ''); ?>">
          </div>

          <div class="form-group">
            <label class="form-label" for="id_number">Barangay ID number</label>
            <input id="id_number" class="form-input" type="text" name="id_number" placeholder="Enter your ID number" required value="<?php echo htmlspecialchars($_POST['id_number'] ?? ''); ?>">
          </div>

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
              <input id="password" class="form-input" type="password" name="password" placeholder="Create a password" required>
            </div>
          </div>

          <label class="form-check" for="terms">
            <input id="terms" type="checkbox" name="terms" required>
            <span class="check-box"><svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
            <span class="check-label">I agree to the <a href="#">terms and privacy policy</a></span>
          </label>

          <button type="submit" class="btn-auth" aria-label="Create account">
            <span class="btn-label">Create account</span>
          </button>
        </form>

        <p class="auth-footer-note">Already registered? <a href="login.php">Log in</a>.</p>
      </section>

      <div class="security-badge">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l7 4v6c0 5-3 8-7 10-4-2-7-5-7-10V6z"></path></svg>
        Account creation protected by barangay security policy
      </div>
    </article>
  </main>
</body>
</html>
