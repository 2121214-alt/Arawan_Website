<?php
session_start();
include 'db.php';

$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
$latest_requests = [];
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $stmt = mysqli_prepare($conn, "SELECT request_type, purpose, status FROM requests WHERE user_id = ? ORDER BY created_at DESC LIMIT 2");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $latest_requests[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Arawan E-Bayanan Services</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<nav class="navbar">
    <a class="brand" href="index.php"><span class="brand-icon">⌂</span>Arawan E-Bayanan</a>
    <div class="nav-links">
        <a class="active" href="index.php">Home</a>
        <a href="request.php">Request docs</a>
        <a href="#announcements">Announcements</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a class="btn" href="dashboard.php">Dashboard</a>
            <a class="btn btn-primary" href="logout.php">Logout</a>
        <?php else: ?>
            <a class="btn" href="login.php">Log in</a>
            <a class="btn btn-primary" href="register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>

<section class="hero">
    <div>
        <span class="tag">Barangay Digital Services</span>
        <h1>Arawan E-Bayanan Services</h1>
        <p>Submit barangay document requests online, track your reference number, and receive request updates from the barangay office.</p>
        <div class="actions">
            <a class="btn btn-primary" href="request.php">Submit new request</a>
            <a class="btn" href="dashboard.php">Track status</a>
        </div>
    </div>

    <div class="panel">
        <h2>Resident Portal</h2>
        <p class="muted">Manage your requests in one place.</p>
        <?php if (isset($_SESSION['user_id']) && count($latest_requests) > 0): ?>
            <?php foreach ($latest_requests as $request): ?>
            <div class="mini-card">
                <span class="status <?php echo strtolower($request['status']); ?>"><?php echo htmlspecialchars($request['status']); ?></span>
                <h3><?php echo htmlspecialchars($request['request_type']); ?></h3>
                <p><?php echo htmlspecialchars($request['purpose']); ?></p>
            </div>
            <?php endforeach; ?>
        <?php elseif (isset($_SESSION['user_id'])): ?>
            <div class="mini-card">
                <span class="status pending">No Requests</span>
                <h3>Start your first request</h3>
                <p>Submit a document request to track your status here.</p>
            </div>
        <?php else: ?>
            <div class="mini-card"><span class="status pending">Pending</span><h3>Barangay Clearance</h3><p>Under staff review</p></div>
            <div class="mini-card"><span class="status approved">Approved</span><h3>Certificate of Residency</h3><p>Ready for release</p></div>
        <?php endif; ?>
    </div>
</section>

<section class="how-section" id="services">
  <div class="container">
    <div class="how-header">
      <span class="section-kicker">How it works</span>
      <h2>From request to release <span></span><br>in four steps.</h2>
      <p>No more multiple visits. Submit once and we'll notify you every step of the way.</p>
    </div>

    <div class="how-grid">
      <article class="how-card">
        <div class="step-number">01</div>
        <div class="step-icon teal">
          <svg viewBox="0 0 24 24">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
            <circle cx="12" cy="7" r="4"/>
          </svg>
        </div>
        <h3>Register & log in</h3>
        <p>Create your resident account using your name, email, and password. Once approved, you can access the portal securely.</p>
      </article>

      <article class="how-card">
        <div class="step-number">02</div>
        <div class="step-icon blue">
          <svg viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="12" y1="11" x2="12" y2="17"/>
            <line x1="9" y1="14" x2="15" y2="14"/>
          </svg>
        </div>
        <h3>Fill the request form</h3>
        <p>Select the document type, enter your purpose, and submit the request through the online form.</p>
      </article>

      <article class="how-card">
        <div class="step-number">03</div>
        <div class="step-icon amber">
          <svg viewBox="0 0 24 24">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
          </svg>
        </div>
        <h3>Staff reviews & approves</h3>
        <p>Barangay staff verifies your request and updates its status from pending to processing, approved, or rejected.</p>
      </article>

      <article class="how-card">
        <div class="step-number">04</div>
        <div class="step-icon green">
          <svg viewBox="0 0 24 24">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
        </div>
        <h3>Track and receive updates</h3>
        <p>You’ll get notified when your request status changes. Check your dashboard anytime for updates and remarks.</p>
      </article>
    </div>
  </div>
</section>

<section class="announcement-section" id="announcements">
  <div class="container">
    <div class="announcement-header">
      <span class="section-kicker">Announcements</span>
      <h2>Stay updated with<br>your barangay.</h2>
      <p>Official notices, schedules, and community programs from Barangay Arawan San Antonio.</p>
    </div>

    <div class="announcement-grid">
      <?php if ($announcements && mysqli_num_rows($announcements) > 0): ?>
        <?php while ($announcement = mysqli_fetch_assoc($announcements)): ?>
          <?php
            $category = $announcement['category'] ?? 'Notice';

            switch ($category) {
                case 'Health':
                    $card_class = 'card-health';
                    $tag_class = 'tag-health';
                    break;

                case 'Program':
                    $card_class = 'card-program';
                    $tag_class = 'tag-program';
                    break;

                case 'Advisory':
                    $card_class = 'card-advisory';
                    $tag_class = 'tag-advisory';
                    break;

                case 'Notice':
                default:
                    $card_class = 'card-notice';
                    $tag_class = 'tag-notice';
                    $category = 'Notice';
                    break;
            }

            $date = !empty($announcement['created_at'])
                ? date('M j, Y', strtotime($announcement['created_at']))
                : date('M j, Y');
          ?>

          <article class="announcement-card <?php echo $card_class; ?>">
            <div class="announcement-top">
              <span class="announcement-tag <?php echo $tag_class; ?>">
                <?php echo htmlspecialchars($category); ?>
              </span>

              <span class="announcement-date">
                <?php echo $date; ?>
              </span>
            </div>

            <h3><?php echo htmlspecialchars($announcement['title']); ?></h3>
            <p><?php echo htmlspecialchars($announcement['content']); ?></p>

            <a href="login.php" class="announcement-link">
              Read more
              <span>→</span>
            </a>
          </article>
        <?php endwhile; ?>

      <?php else: ?>
        <article class="announcement-card card-notice">
          <div class="announcement-top">
            <span class="announcement-tag tag-notice">Notice</span>
            <span class="announcement-date"><?php echo date('M j, Y'); ?></span>
          </div>

          <h3>Online Request System</h3>
          <p>Residents may now submit document requests online and track their status through the portal.</p>

          <a href="register.php" class="announcement-link">
            Register now
            <span>→</span>
          </a>
        </article>
      <?php endif; ?>
    </div>
  </div>
</section>

<footer class="footer">
    <div>
        <h3>Arawan E-Bayanan Services</h3>
        <p>The official digital portal for Barangay Arawan residents.</p>
    </div>
    <div>
        <h4>Services</h4>
        <p>Barangay Clearance</p>
        <p>Certificate of Residency</p>
        <p>Certificate of Indigency</p>
        <p>Business Permit</p>
    </div>
    <div>
        <h4>Portal</h4>
        <p>Request Documents</p>
        <p>Track Status</p>
        <p>Announcements</p>
    </div>
</footer>

</body>
</html>
