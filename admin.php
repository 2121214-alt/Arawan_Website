<?php
session_start();
include 'db.php';
require_once 'csrf.php';
require_once 'helpers.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    header('Location: dashboard.php');
    exit();
}

$csrf = ensure_csrf_token();

$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT requests.*, users.fullname, users.email FROM requests JOIN users ON users.id = requests.user_id WHERE 1=1";

if ($filter_status) {
    $query .= " AND requests.status = '" . mysqli_real_escape_string($conn, $filter_status) . "'";
}

if ($search) {
    $search_term = mysqli_real_escape_string($conn, $search);
    $query .= " AND (requests.reference_number LIKE '%$search_term%' OR users.fullname LIKE '%$search_term%' OR requests.request_type LIKE '%$search_term%')";
}

$query .= " ORDER BY requests.created_at DESC";
$result = mysqli_query($conn, $query);

$total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM requests"));
$pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM requests WHERE status = 'Pending'"));
$processing = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM requests WHERE status = 'Processing'"));
$approved = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM requests WHERE status = 'Approved'"));
$rejected = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM requests WHERE status = 'Rejected'"));

$announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC");
$pending_users = mysqli_query($conn, "SELECT id, first_name, middle_name, last_name, email, id_number, approval_status, created_at FROM users WHERE role = 'resident' AND approval_status = 'Pending' ORDER BY created_at DESC");
$approved_users = mysqli_query($conn, "SELECT id, first_name, middle_name, last_name, email, id_number, approval_status, created_at FROM users WHERE role = 'resident' AND approval_status = 'Approved' ORDER BY created_at DESC LIMIT 10");
$rejected_users = mysqli_query($conn, "SELECT id, first_name, middle_name, last_name, email, id_number, approval_status, created_at FROM users WHERE role = 'resident' AND approval_status = 'Rejected' ORDER BY created_at DESC LIMIT 10");
$pending_user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND approval_status = 'Pending'"));
$approved_user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND approval_status = 'Approved'"));
$rejected_user_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'resident' AND approval_status = 'Rejected'"));

function resident_name($row) {
    return trim($row['first_name'] . ' ' . ($row['middle_name'] ? $row['middle_name'] . ' ' : '') . $row['last_name']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="style.css?v=2">
</head>
<body class="admin-page">

<nav class="navbar">
    <a class="brand" href="index.php"><span class="brand-icon">⌂</span>Arawan E-Bayanan</a>
    <div class="nav-links">
        <a href="admin.php">Dashboard</a>
        <a class="btn btn-primary" href="logout.php">Logout</a>
    </div>
</nav>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand"><a href="admin.php">Arawan E-Bayanan</a></div>
        <ul class="menu">
            <li><a href="#dashboard" class="menu-item">Dashboard</a></li>
            <li><a href="#requests" class="menu-item">Requests</a></li>
            <li><a href="#registrations" class="menu-item">Registrations (<?php echo (int)$pending_user_count['count']; ?>)</a></li>
            <li><a href="#announcements" class="menu-item">Announcements</a></li>
            <li><a href="logout.php" class="menu-item">Logout</a></li>
        </ul>
    </aside>

    <div class="admin-content">
        <section id="dashboard" class="admin-section">
            <h1>Admin Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($_SESSION['fullname']); ?>!</p>
            <div class="stats">
                <div class="stat"><h2>Total Requests</h2><p><?php echo (int)$total['count']; ?></p></div>
                <div class="stat"><h2>Pending</h2><p><?php echo (int)$pending['count']; ?></p></div>
                <div class="stat"><h2>Processing</h2><p><?php echo (int)$processing['count']; ?></p></div>
                <div class="stat"><h2>Approved</h2><p><?php echo (int)$approved['count']; ?></p></div>
                <div class="stat"><h2>Rejected</h2><p><?php echo (int)$rejected['count']; ?></p></div>
            </div>
            <div class="stats" style="margin-top:1rem">
                <div class="stat"><h2>Pending Residents</h2><p><?php echo (int)$pending_user_count['count']; ?></p></div>
                <div class="stat"><h2>Approved Residents</h2><p><?php echo (int)$approved_user_count['count']; ?></p></div>
                <div class="stat"><h2>Rejected Residents</h2><p><?php echo (int)$rejected_user_count['count']; ?></p></div>
            </div>
        </section>

        <section id="requests" class="admin-section">
            <h1>Review Requests</h1>
            <p class="admin-lead">Search, filter, and update resident document requests.</p>
            <div class="admin-card">
            <form method="GET" action="admin.php#requests" class="admin-filters">
                <input type="text" name="search" placeholder="Search by reference, resident name, or document type..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">All Status</option>
                    <option value="Pending" <?php echo $filter_status === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="Processing" <?php echo $filter_status === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                    <option value="Approved" <?php echo $filter_status === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                    <option value="Rejected" <?php echo $filter_status === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="admin.php#requests" class="btn">Clear</a>
            </form>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Resident</th>
                            <th>Reference</th>
                            <th>Document</th>
                            <th>Purpose</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['reference_number']); ?></td>
                                <td><?php echo htmlspecialchars($row['request_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                <td><span class="admin-badge admin-badge-<?php echo strtolower($row['status']); ?>"><?php echo htmlspecialchars($row['status']); ?></span></td>
                                <td><?php echo date('M j, Y', strtotime($row['created_at'])); ?></td>
                                <td class="admin-actions">
                                    <form method="POST" action="approve.php" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    <form method="POST" action="reject.php" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </form>
                                    <form method="POST" action="admin_update.php" class="inline-form">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                                        <input type="hidden" name="request_id" value="<?php echo (int)$row['id']; ?>">
                                        <select name="status">
                                            <option value="Pending" <?php echo $row['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Processing" <?php echo $row['status'] === 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Approved" <?php echo $row['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="Rejected" <?php echo $row['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                        <input type="text" name="remarks" placeholder="Remarks" value="<?php echo htmlspecialchars($row['remarks'] ?? ''); ?>">
                                        <button type="submit" class="btn">Update</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="7" class="empty-cell">No requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        <section id="registrations" class="admin-section">
            <h1>Resident Registrations</h1>
            <p class="admin-lead">Review new sign-ups and manage resident account status.</p>

            <div class="admin-card">
            <h2 class="admin-card-title">Pending approval <span class="admin-count"><?php echo (int)$pending_user_count['count']; ?></span></h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>ID Number</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($pending_users && mysqli_num_rows($pending_users) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($pending_users)): ?>
                                <tr>
                                    <td class="col-name"><?php echo htmlspecialchars(resident_name($user)); ?></td>
                                    <td class="col-email"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="col-mono"><?php echo htmlspecialchars($user['id_number']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                    <td>
                                        <div class="admin-btn-group">
                                            <form method="POST" action="approve_user.php" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                                <button type="submit" class="btn btn-success">Approve</button>
                                            </form>
                                            <form method="POST" action="reject_user.php" class="inline-form">
                                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                                                <input type="hidden" name="id" value="<?php echo (int)$user['id']; ?>">
                                                <button type="submit" class="btn btn-danger">Reject</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="empty-cell">No pending registrations.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <div class="admin-card">
            <h2 class="admin-card-title">Recently approved</h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>ID Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($approved_users && mysqli_num_rows($approved_users) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($approved_users)): ?>
                                <tr>
                                    <td class="col-name"><?php echo htmlspecialchars(resident_name($user)); ?></td>
                                    <td class="col-email"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="col-mono"><?php echo htmlspecialchars($user['id_number']); ?></td>
                                    <td><span class="admin-badge admin-badge-approved"><?php echo htmlspecialchars($user['approval_status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="empty-cell">No approved residents yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>

            <div class="admin-card">
            <h2 class="admin-card-title">Recently rejected</h2>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>ID Number</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($rejected_users && mysqli_num_rows($rejected_users) > 0): ?>
                            <?php while ($user = mysqli_fetch_assoc($rejected_users)): ?>
                                <tr>
                                    <td class="col-name"><?php echo htmlspecialchars(resident_name($user)); ?></td>
                                    <td class="col-email"><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td class="col-mono"><?php echo htmlspecialchars($user['id_number']); ?></td>
                                    <td><span class="admin-badge admin-badge-rejected"><?php echo htmlspecialchars($user['approval_status']); ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4" class="empty-cell">No rejected residents.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            </div>
        </section>

        <section id="announcements" class="admin-section">
            <h1>Announcements</h1>
            <div class="card" style="margin-bottom:1.5rem">
                <h2>Create announcement</h2>
                <form method="POST" action="admin_announcements.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                    <input type="hidden" name="action" value="create">
                    <label>Category</label>
                    <select name="category" required>
                        <option value="Notice">Notice</option>
                        <option value="Advisory">Advisory</option>
                        <option value="Program">Program</option>
                        <option value="Health">Health</option>
                    </select>

                    <label>Title</label>
                    <input type="text" name="title" required>

                    <label>Content</label>
                    <textarea name="content" rows="4" required></textarea>
                    <button type="submit" class="btn btn-primary">Publish</button>
                </form>
            </div>

            <?php if ($announcements && mysqli_num_rows($announcements) > 0): ?>
                <?php mysqli_data_seek($announcements, 0); ?>
                <?php while ($announcement = mysqli_fetch_assoc($announcements)): ?>
                    <div class="card" style="margin-bottom:1rem">
                        <form method="POST" action="admin_announcements.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="action" value="update">
                            <input type="hidden" name="announcement_id" value="<?php echo (int)$announcement['id']; ?>">
                            <label>Title</label>
                            <input type="text" name="title" value="<?php echo htmlspecialchars($announcement['title']); ?>" required>
                            <label>Content</label>
                            <textarea name="content" rows="3" required><?php echo htmlspecialchars($announcement['content']); ?></textarea>
                            <div style="display:flex;gap:.5rem;margin-top:.5rem">
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                        <form method="POST" action="admin_announcements.php" style="margin-top:.5rem" onsubmit="return confirm('Delete this announcement?');">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf); ?>">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="announcement_id" value="<?php echo (int)$announcement['id']; ?>">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No announcements yet.</p>
            <?php endif; ?>
        </section>
    </div>
</div>

</body>
</html>
