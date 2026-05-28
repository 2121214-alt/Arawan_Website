<?php
include 'auth_check.php';
require_once 'csrf.php';
$csrf = ensure_csrf_token();
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Request Documents</title>
<link rel="stylesheet" href="style.css?v=3">
</head>
<body>
<nav class="navbar">
    <a class="brand" href="index.php"><span class="brand-icon">⌂</span>Arawan E-Bayanan</a>
    <div class="nav-links">
        <a href="dashboard.php">Dashboard</a>
        <a class="btn btn-primary" href="logout.php">Logout</a>
    </div>
</nav>

<main class="auth-wrap request-page-wrap">
    <article class="auth-card request-card">
        <div class="auth-brand-header compact">
            <span class="auth-brand-logo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg></span>
            <span class="auth-brand-name">Document Request</span>
            <span class="auth-brand-sub">Submit one request at a time</span>
        </div>
        <section class="auth-panel active">
            <h1 class="auth-heading">Submit new request</h1>
            <p class="auth-subheading">Choose the document type and enter a clear purpose for faster review.</p>

            <?php if ($error): ?>
                <p class="auth-error" role="alert"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form action="request_submit.php" method="POST" class="auth-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">

                <div class="form-group">
                    <label class="form-label" for="request_type">Document type</label>
                    <select id="request_type" name="request_type" class="form-select" required>
                        <option value="">Select document</option>
                        <option value="Barangay Clearance">Barangay Clearance</option>
                        <option value="Certificate of Residency">Certificate of Residency</option>
                        <option value="Certificate of Indigency">Certificate of Indigency</option>
                        <option value="Business Permit">Business Permit</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="purpose">Purpose</label>
                    <textarea id="purpose" class="form-input" name="purpose" rows="4" maxlength="255" placeholder="Example: Employment requirement" required></textarea>
                </div>

                <button class="btn-auth" type="submit"><span class="btn-label">Submit request</span></button>
                <a class="btn btn-ghost full-width" href="dashboard.php">Cancel</a>
            </form>
        </section>
    </article>
</main>
</body>
</html>
