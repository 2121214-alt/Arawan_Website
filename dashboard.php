<?php
include 'auth_check.php';
include 'db.php';
require_once 'helpers.php';
require_once 'csrf.php';

$user_id = $_SESSION['user_id'];
$fullname = $_SESSION['fullname'];
$email = $_SESSION['email'];
$first_name = explode(' ', trim($fullname))[0] ?: $fullname;

$userStmt = mysqli_prepare($conn, "SELECT id_number, created_at FROM users WHERE id = ? LIMIT 1");
mysqli_stmt_bind_param($userStmt, "i", $user_id);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$userInfo = mysqli_fetch_assoc($userResult) ?: [];
$resident_id = $userInfo['id_number'] ?? 'USR-' . str_pad($user_id, 4, '0', STR_PAD_LEFT);
$joined_at = isset($userInfo['created_at']) ? date('M j, Y', strtotime($userInfo['created_at'])) : '';

$stmt = mysqli_prepare($conn, "SELECT * FROM requests WHERE user_id = ? ORDER BY created_at DESC");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$requests = [];
$stats = ['Pending' => 0, 'Processing' => 0, 'Approved' => 0, 'Rejected' => 0];
while ($row = mysqli_fetch_assoc($result)) {
    $requests[] = $row;
    if (isset($stats[$row['status']])) {
        $stats[$row['status']]++;
    }
}
$total_requests = count($requests);
$recent_requests = array_slice($requests, 0, 3);

$announcements = [];
$annQuery = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
if ($annQuery) {
    while ($item = mysqli_fetch_assoc($annQuery)) {
        $announcements[] = $item;
    }
}

$notifications = [];
$notifStmt = mysqli_prepare($conn, 'SELECT id, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 10');
mysqli_stmt_bind_param($notifStmt, 'i', $user_id);
mysqli_stmt_execute($notifStmt);
$notifResult = mysqli_stmt_get_result($notifStmt);
if ($notifResult) {
    while ($item = mysqli_fetch_assoc($notifResult)) {
        $notifications[] = $item;
    }
}
$unread_count = 0;
foreach ($notifications as $item) {
    if (!$item['is_read']) {
        $unread_count++;
    }
}
$submitted_notice = isset($_GET['submitted']);
$csrf = ensure_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Dashboard — Arawan E-Bayanan Services</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="app">
  <aside class="sidebar" id="sidebar">
  <a class="sidebar-brand" href="index.php">
  <div class="sidebar-logo">
    <svg viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
      <polyline points="9 22 9 12 15 12 15 22"/>
    </svg>
  </div>
  <div>
    <div class="sidebar-brand-name">Arawan E-Bayanan</div>
    <div class="sidebar-brand-sub">Resident Portal</div>
  </div>
</a>

    <nav class="sidebar-nav">
      <div class="sidebar-section-label">Main</div>
      <div class="sidebar-link active" id="lnk-dashboard" onclick="switchView('dashboard', this)">
        <svg viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
        Dashboard
      </div>
      <div class="sidebar-link" id="lnk-requests" onclick="switchView('requests', this)">
        <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        My Requests
        <span class="sidebar-badge"><?php echo $stats['Pending']; ?></span>
      </div>
      <div class="sidebar-link" id="lnk-new-request" onclick="switchView('new-request', this)">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
        New Request
      </div>
      <div class="sidebar-section-label">Account</div>
      <div class="sidebar-link" id="lnk-profile" onclick="switchView('profile', this)">
        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        My Profile
      </div>
      <div class="sidebar-link" id="lnk-notifications" onclick="switchView('notifications', this)">
        <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
        Notifications
        <span class="sidebar-badge" id="notificationCount"><?php echo $unread_count; ?></span>
      </div>
      <div class="sidebar-section-label">Support</div>
      <div class="sidebar-link" onclick="showToast('Help center coming soon!')">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        Help Center
      </div>
    </nav>

    <div class="sidebar-footer">
      <div class="user-pill">
        <div class="user-avatar"><?php echo strtoupper(substr($first_name, 0, 1)); ?></div>
        <div class="user-info">
          <div class="user-name"><?php echo htmlspecialchars($fullname); ?></div>
          <div class="user-id"><?php echo htmlspecialchars($resident_id); ?></div>
        </div>
        <a class="logout-btn" href="logout.php" title="Logout">
          <svg viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
        </a>
      </div>
      <div style="margin-top:0.85rem; font-size:0.75rem; color:var(--gray-500);">Joined <?php echo $joined_at ?: 'recently'; ?></div>
    </div>
  </aside>

  <div class="mobile-overlay" id="mobileOverlay" onclick="closeSidebar()"></div>

  <div class="main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="topbar-hamburger" onclick="toggleSidebar()" aria-label="Open menu">
          <span></span><span></span><span></span>
        </button>
        <div class="topbar-title" id="topbarTitle">Dashboard</div>
      </div>
      <div class="topbar-right">
        <div class="icon-btn" onclick="switchView('notifications', document.getElementById('lnk-notifications'))" title="Notifications">
          <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
          <?php if ($unread_count > 0): ?><span class="notif-dot"></span><?php endif; ?>
        </div>
        <div class="icon-btn" onclick="switchView('profile', document.getElementById('lnk-profile'))" title="Profile">
          <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>
      </div>
    </div>

    <div class="content">
    <?php if ($submitted_notice): ?>
  <div id="successAlert" class="auth-alert success visible dashboard-notice" role="status">
    Request submitted successfully. You can track it below.
  </div>
<?php endif; ?>
      <div class="view active" id="view-dashboard">
        <div class="welcome-banner">
          <div class="welcome-text">
            <h2>Good day, <em style="color:var(--teal-200)"><?php echo htmlspecialchars($first_name); ?></em> 👋</h2>
            <p>You have <?php echo $stats['Pending']; ?> pending requests awaiting processing.</p>
          </div>
          <div class="welcome-action" onclick="switchView('new-request', document.getElementById('lnk-new-request'))">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>
            New Request
          </div>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--amber-50)">
              <svg viewBox="0 0 24 24" stroke="var(--amber-700)"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            </div>
            <div class="stat-value"><?php echo $stats['Pending']; ?></div>
            <div class="stat-label">Pending</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-50)">
              <svg viewBox="0 0 24 24" stroke="var(--blue-700)"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </div>
            <div class="stat-value"><?php echo $stats['Processing']; ?></div>
            <div class="stat-label">Processing</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--green-50)">
              <svg viewBox="0 0 24 24" stroke="var(--green-700)"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div class="stat-value"><?php echo $stats['Approved']; ?></div>
            <div class="stat-label">Approved</div>
          </div>
          <div class="stat-card">
            <div class="stat-icon" style="background:var(--teal-50)">
              <svg viewBox="0 0 24 24" stroke="var(--teal-700)"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            </div>
            <div class="stat-value"><?php echo $total_requests; ?></div>
            <div class="stat-label">Total Requests</div>
          </div>
        </div>

        <div class="card-2col">
          <div class="card">
            <div class="section-header">
              <h3 style="font-size:1rem">Recent Requests</h3>
              <button class="btn-xs" onclick="switchView('requests', document.getElementById('lnk-requests'))">View all</button>
            </div>
            <?php if (count($recent_requests) > 0): ?>
              <?php foreach ($recent_requests as $row): ?>
                <?php $colorClass = strtolower($row['status']); ?>
                <div class="mini-req">
                  <div>
                    <div class="mini-req-name"><?php echo htmlspecialchars($row['request_type']); ?></div>
                    <div class="mini-req-ref"><?php echo htmlspecialchars($row['reference_number']); ?></div>
                  </div>
                  <span class="badge badge-<?php echo $colorClass === 'approved' ? 'ready' : strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p style="color:var(--gray-500);">You have not submitted any requests yet.</p>
            <?php endif; ?>
          </div>

          <div class="card">
            <div class="section-header">
              <h3 style="font-size:1rem">Recent Activity</h3>
            </div>
            <div class="timeline">
              <?php if (count($recent_requests) > 0): ?>
                <?php foreach ($recent_requests as $index => $row): ?>
                  <?php $created = date('M j, Y, g:i A', strtotime($row['created_at'])); ?>
                  <div class="timeline-item">
                    <div class="timeline-dot teal">
                      <svg viewBox="0 0 24 24" stroke="var(--teal-700)"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                    <div class="timeline-body">
                      <div class="timeline-title"><?php echo htmlspecialchars($row['request_type']); ?> <?php echo htmlspecialchars($row['status']); ?></div>
                      <div class="timeline-desc"><?php echo htmlspecialchars($row['purpose']); ?></div>
                      <div class="timeline-time"><?php echo $created; ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p style="color:var(--gray-500);">No recent activity yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="card">
          <div class="section-header">
            <h3 style="font-size:1rem">Barangay Announcements</h3>
          </div>
          <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:.65rem">
            <?php if (count($announcements) > 0): ?>
              <?php foreach ($announcements as $announcement): ?>
                <div style="padding:.9rem;border-radius:var(--radius-md); background:var(--teal-50); border:1px solid var(--teal-200)">
                  <div style="font-size:.66rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--teal-700);margin-bottom:.3rem">Announcement</div>
                  <div style="font-size:.85rem;font-weight:600;color:var(--gray-900);margin-bottom:.25rem"><?php echo htmlspecialchars($announcement['title']); ?></div>
                  <p style="font-size:.78rem"><?php echo htmlspecialchars($announcement['content']); ?></p>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div style="padding:.9rem;border-radius:var(--radius-md);background:var(--gray-50);border:1px solid var(--gray-100)">
                <div style="font-size:.85rem;font-weight:600;color:var(--gray-900);margin-bottom:.25rem">No announcements yet</div>
                <p style="font-size:.78rem;color:var(--gray-600)">Check back later for barangay updates and notices.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="view" id="view-requests">
        <div class="section-header" style="margin-bottom:.9rem">
          <h2 style="font-size:1.4rem">My Requests</h2>
          <button class="btn-primary" onclick="switchView('new-request', document.getElementById('lnk-new-request'))" style="display:inline-flex;align-items:center;gap:6px;font-size:.8rem;padding:8px 13px">
            <svg viewBox="0 0 24 24" style="width:13px;height:13px;stroke:#fff;fill:none;stroke-width:2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            New Request
          </button>
        </div>

        <div class="filters-row">
          <div class="search-wrap">
            <svg viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input class="search-input" type="text" placeholder="Search by reference or document type…" oninput="filterRequests(this.value)">
          </div>
          <select class="filter-select" onchange="filterByStatus(this.value)">
            <option value="">All Status</option>
            <option value="pending">Pending</option>
            <option value="processing">Processing</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
        </div>

        <div class="table-wrap">
          <table id="requests-table">
            <thead>
              <tr>
                <th>Reference No.</th>
                <th>Document Type</th>
                <th>Purpose</th>
                <th>Date Filed</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php if (count($requests) > 0): ?>
                <?php foreach ($requests as $row): ?>
                  <?php $requestDate = date('M d, Y', strtotime($row['created_at'])); ?>
                  <tr>
                    <td class="mono"><?php echo htmlspecialchars($row['reference_number']); ?></td>
                    <td style="font-weight:500;color:var(--gray-900)"><?php echo htmlspecialchars($row['request_type']); ?></td>
                    <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                    <td><?php echo $requestDate; ?></td>
                    <td><span class="badge badge-<?php echo strtolower($row['status']) === 'approved' ? 'ready' : strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                    <td>
  <button 
    type="button"
    class="btn-xs view-request-btn"
    data-ref="<?php echo htmlspecialchars($row['reference_number'], ENT_QUOTES); ?>"
    data-type="<?php echo htmlspecialchars($row['request_type'], ENT_QUOTES); ?>"
    data-status="<?php echo htmlspecialchars($row['status'], ENT_QUOTES); ?>"
    data-purpose="<?php echo htmlspecialchars($row['purpose'], ENT_QUOTES); ?>"
    data-date="<?php echo htmlspecialchars($requestDate, ENT_QUOTES); ?>"
    data-note="<?php echo htmlspecialchars($row['remarks'] ?? 'No additional remarks.', ENT_QUOTES); ?>"
  >
    View
  </button>
</td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="6">No requests yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="view" id="view-new-request">
        <div style="max-width:660px">
          <h2 style="font-size:1.4rem;margin-bottom:.2rem">New Document Request</h2>
          <p style="margin-bottom:1.5rem;font-size:.875rem">Click below to start a new request using the barangay request form.</p>
          <div class="card" style="margin-bottom:.85rem">
            <h3 style="font-size:.95rem;margin-bottom:.2rem">Ready to submit?</h3>
            <p style="font-size:.78rem;margin-bottom:.9rem">Choose the document type and provide the purpose on the next page.</p>
            <button class="btn-primary" onclick="window.location.href='request.php'" style="width:100%;">Go to Request Form</button>
          </div>
          <button class="btn-sm" onclick="switchView('dashboard', document.getElementById('lnk-dashboard'))" style="padding:12px 18px">Back to Dashboard</button>
        </div>
      </div>

      <div class="view" id="view-profile">
        <div style="max-width:620px">
          <div class="profile-header">
            <div class="profile-avatar-lg"><?php echo strtoupper(substr($first_name, 0, 1)); ?></div>
            <div>
              <h2 style="font-size:1.3rem;margin-bottom:.2rem"><?php echo htmlspecialchars($fullname); ?></h2>
              <div class="profile-rid"><?php echo htmlspecialchars($resident_id); ?></div>
              <p style="font-size:.82rem;margin-top:.2rem">Registered Resident · Joined <?php echo $joined_at ?: 'recently'; ?></p>
            </div>
          </div>
          <div class="card" style="margin-bottom:.85rem">
            <h3 style="font-size:.95rem;margin-bottom:.9rem">Personal Information</h3>
            <div class="form-row form-group">
              <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Full Name</label>
                <input class="form-input" type="text" value="<?php echo htmlspecialchars($fullname); ?>" disabled>
              </div>
              <div class="form-group" style="margin-bottom:0">
                <label class="form-label">Resident ID</label>
                <input class="form-input" type="text" value="<?php echo htmlspecialchars($resident_id); ?>" disabled>
              </div>
            </div>
          </div>
          <div class="card" style="margin-bottom:.85rem">
            <h3 style="font-size:.95rem;margin-bottom:.9rem">Account & Contact</h3>
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <input class="form-input" type="email" value="<?php echo htmlspecialchars($email); ?>" disabled>
            </div>
          </div>
          <button class="btn-primary" onclick="showToast('Profile settings are not editable here yet.')" style="padding:11px 24px">Manage Profile</button>
        </div>
      </div>

      <div class="view" id="view-notifications">
        <div style="max-width:720px">
          <div class="section-header" style="margin-bottom:.9rem">
            <h2 style="font-size:1.4rem">Notifications</h2>
            <button class="btn-xs" onclick="markAllRead()">Mark all read</button>
          </div>
          <div class="table-wrap notif-list">
            <?php if (count($notifications) > 0): ?>
              <?php foreach ($notifications as $notification): ?>
                <div class="notif-item <?php echo !$notification['is_read'] ? 'unread' : ''; ?>">
                  <div class="notif-indicator"></div>
                  <div class="notif-content">
                    <div class="notif-title">Portal Update</div>
                    <div class="notif-body"><?php echo htmlspecialchars($notification['message']); ?></div>
                    <div class="notif-time"><?php echo date('M j, Y, g:i A', strtotime($notification['created_at'])); ?></div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="empty-state">
                <h3>No notifications yet</h3>
                <p>Updates about your requests and account will appear here.</p>
              </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="requestModal">
  <div class="modal">
    <div class="modal-header">
      <h3 id="modal-doctype">Request Details</h3>
      <button class="modal-close" onclick="closeModal()" aria-label="Close modal">
        <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="modal-body">
      <div style="font-family:'DM Mono',monospace;font-size:.78rem;color:var(--gray-500);margin-bottom:.85rem" id="modal-ref"></div>
      <div class="info-grid" style="margin-bottom:.85rem">
        <div class="info-cell">
          <div class="info-label">Status</div>
          <span class="badge" id="modal-status-badge" style="margin-top:2px"></span>
        </div>
        <div class="info-cell">
          <div class="info-label">Date Filed</div>
          <div class="info-val" id="modal-date"></div>
        </div>
        <div class="info-cell">
          <div class="info-label">Purpose</div>
          <div class="info-val" id="modal-purpose"></div>
        </div>
      </div>
      <div style="padding:.9rem;background:var(--teal-50);border-radius:var(--radius-md);border:1px solid var(--teal-200);margin-bottom:1.1rem">
        <div style="font-size:.66rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--teal-700);margin-bottom:.3rem">Staff Note</div>
        <p style="font-size:.85rem;color:var(--teal-900)" id="modal-note"></p>
      </div>
      <button class="btn-primary" onclick="closeModal()" style="width:100%;padding:11px">Close</button>
    </div>
  </div>
</div>

<div class="toast" id="toast">
  <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
  <span id="toastMsg">Done.</span>
</div>

<script>
  function switchView(viewId, linkEl) {
    document.querySelectorAll('.view').forEach(v => v.classList.remove('active'));
    document.getElementById('view-' + viewId).classList.add('active');
    document.querySelectorAll('.sidebar-link').forEach(l => l.classList.remove('active'));
    if (linkEl) linkEl.classList.add('active');
    const titles = { dashboard:'Dashboard', requests:'My Requests', 'new-request':'New Request', profile:'My Profile', notifications:'Notifications' };
    document.getElementById('topbarTitle').textContent = titles[viewId] || '';
    closeSidebar();
    window.scrollTo(0,0);
  }
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('mobileOverlay').classList.toggle('open');
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('mobileOverlay').classList.remove('open');
  }
  function viewRequest(ref, type, status, purpose, date, note) {
  const modal = document.getElementById('requestModal');

  if (!modal) {
    console.error('Request modal not found.');
    return;
  }

  document.getElementById('modal-doctype').textContent = type || 'Request Details';
  document.getElementById('modal-ref').textContent = ref || '';
  document.getElementById('modal-date').textContent = date || '';
  document.getElementById('modal-purpose').textContent = purpose || 'No purpose provided.';
  document.getElementById('modal-note').textContent = note || 'No additional remarks.';

  const badge = document.getElementById('modal-status-badge');

  const statusClass = {
    'Pending': 'badge-pending',
    'Processing': 'badge-processing',
    'Approved': 'badge-ready',
    'Rejected': 'badge-rejected'
  };

  badge.className = 'badge ' + (statusClass[status] || 'badge-pending');
  badge.textContent = status || 'Pending';

  modal.classList.add('open');
}

document.querySelectorAll('.view-request-btn').forEach(function(button) {
  button.addEventListener('click', function() {
    viewRequest(
      this.dataset.ref,
      this.dataset.type,
      this.dataset.status,
      this.dataset.purpose,
      this.dataset.date,
      this.dataset.note
    );
  });
});

  function closeModal() { document.getElementById('requestModal').classList.remove('open'); }
  document.getElementById('requestModal').addEventListener('click', function(e) { if (e.target === this) closeModal(); });
  function markAllRead() {
    fetch('mark_notifications_read.php', { method: 'POST', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: 'csrf_token=<?php echo htmlspecialchars($csrf); ?>' }).catch(() => null);
    document.querySelectorAll('.notif-item.unread').forEach(n => n.classList.remove('unread'));
    const counter = document.getElementById('notificationCount'); if (counter) counter.textContent = '0';
    document.querySelectorAll('.notif-dot').forEach(dot => dot.remove());
    showToast('All notifications marked as read.');
  }
  let toastTimer;
  function showToast(msg) {
    const t = document.getElementById('toast');
    document.getElementById('toastMsg').textContent = msg;
    t.classList.add('show');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => t.classList.remove('show'), 3200);
  }
  function filterRequests(val) {
    const rows = document.querySelectorAll('#requests-table tbody tr');
    val = val.toLowerCase();
    rows.forEach(r => { r.style.display = r.textContent.toLowerCase().includes(val) ? '' : 'none'; });
  }
  function filterByStatus(val) {
    const rows = document.querySelectorAll('#requests-table tbody tr');
    rows.forEach(r => {
      if (!val) { r.style.display = ''; return; }
      const badge = r.querySelector('.badge');
      r.style.display = badge && badge.textContent.toLowerCase().includes(val) ? '' : 'none';
    });
  }
  const successAlert = document.getElementById("successAlert");

if (successAlert) {
  setTimeout(function () {
    successAlert.classList.add("hide");

    setTimeout(function () {
      successAlert.remove();

      const url = new URL(window.location.href);
      url.searchParams.delete("submitted");
      window.history.replaceState({}, document.title, url.pathname + url.search);
    }, 400);
  }, 5000);
}
</script>
</body>
</html>
