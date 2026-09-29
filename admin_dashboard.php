<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// ── Database connection ──────────────────────────────────────
$host = "localhost";
$dbname = "globetrek";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// ── AUTH GUARD (ADMIN ONLY) ─────────────────────────────────
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.html");
    exit();
}

// allow ONLY admin
if ($_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}

$admin_name = htmlspecialchars($_SESSION['name'] ?? 'Admin User');
$admin_role = htmlspecialchars($_SESSION['role'] ?? 'admin');

// ── Active section (tab) ─────────────────────────────────────
$section = isset($_GET['section']) ? $_GET['section'] : 'dashboard';
$allowed_sections = ['dashboard', 'users', 'packages', 'bookings'];
if (!in_array($section, $allowed_sections)) $section = 'dashboard';

// ── Flash messages ───────────────────────────────────────────
$flash = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}


//  POST ACTIONS

// Delete User (Admin only)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Delete user (cascade delete handled by DB)
    if ($action === 'delete_user') {
        $user_id = (int) ($_POST['user_id'] ?? 0);
        
        // Prevent admin from deleting themselves
        if ($user_id > 0 && $user_id != $_SESSION['user_id']) {
            // Delete user (bookings will cascade if foreign key set)
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $_SESSION['flash'] = "User deleted successfully.";
        } else {
            $_SESSION['flash'] = "Cannot delete your own admin account.";
        }
        
        header("Location: admin_dashboard.php?section=users");
        exit;
    }

    // Add/Update Package
    if ($action === 'save_package') {
        $pkg_id       = (int) ($_POST['package_id'] ?? 0);
        $name         = trim($_POST['name'] ?? '');
        $destination  = trim($_POST['destination'] ?? '');
        $price        = (float) ($_POST['price'] ?? 0);
        $duration     = (int) ($_POST['duration'] ?? 0);
        $description  = trim($_POST['description'] ?? '');
        $availability = (int) ($_POST['availability'] ?? 0);
        $image        = trim($_POST['image'] ?? '');

        if ($pkg_id > 0) {
            // Update existing package
            $stmt = $pdo->prepare("
                UPDATE packages
                SET name=?, destination=?, price=?, duration_days=?,
                    description=?, availability=?, image=?
                WHERE id=?
            ");
            $stmt->execute([$name, $destination, $price, $duration, $description, $availability, $image, $pkg_id]);
            $_SESSION['flash'] = "Package '$name' updated successfully.";
        } else {
            // Insert new package
            $stmt = $pdo->prepare("
                INSERT INTO packages (name, destination, price, duration_days, description, availability, image)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $destination, $price, $duration, $description, $availability, $image]);
            $_SESSION['flash'] = "New package '$name' added successfully.";
        }

        header("Location: admin_dashboard.php?section=packages");
        exit;
    }

    // Delete Package
    if ($action === 'delete_package') {
        $pkg_id = (int) ($_POST['package_id'] ?? 0);
        if ($pkg_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
            $stmt->execute([$pkg_id]);
            $_SESSION['flash'] = "Package deleted successfully.";
        }
        header("Location: admin_dashboard.php?section=packages");
        exit;
    }

    // Update Booking Status
    if ($action === 'update_booking_status') {
        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        $new_status = $_POST['status'] ?? '';
        $allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

        if ($booking_id > 0 && in_array($new_status, $allowed_statuses)) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $booking_id]);
            $_SESSION['flash'] = "Booking #$booking_id updated to '$new_status'.";
        }

        header("Location: admin_dashboard.php?section=bookings");
        exit;
    }
}

// ============================================================
//  DATA QUERIES
// ============================================================

// ── Summary counts ───────────────────────────────────────────
$total_users      = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_customers   = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$total_staff       = $pdo->query("SELECT COUNT(*) FROM users WHERE role='staff'")->fetchColumn();
$total_bookings    = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings  = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
$total_packages    = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();
$total_revenue     = $pdo->query("SELECT SUM(price) FROM bookings WHERE status IN ('confirmed','completed')")->fetchColumn() ?: 0;

// ── Users data (all users) ───────────────────────────────────
$users_sql = "SELECT id, name, email, role FROM users ORDER BY id DESC";
$users = $pdo->query($users_sql)->fetchAll(PDO::FETCH_ASSOC);

// ── Packages data ────────────────────────────────────────────
$packages = $pdo->query("SELECT * FROM packages ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// ── Bookings data with customer & package info ───────────────
$bookings_sql = "
    SELECT 
        b.id, b.date, b.price, b.status, b.package_name,
        u.name AS customer_name, u.email AS customer_email
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    ORDER BY b.date DESC
    LIMIT 100
";
$bookings = $pdo->query($bookings_sql)->fetchAll(PDO::FETCH_ASSOC);

// ── Status badge helper ──────────────────────────────────────
function statusBadge($status) {
    $map = [
        'confirmed'  => ['#e8f5e9', '#2e7d32', '✓ Confirmed'],
        'pending'    => ['#fff8e1', '#f57f17', '⏳ Pending'],
        'cancelled'  => ['#fce4ec', '#c62828', '✕ Cancelled'],
        'completed'  => ['#e3f2fd', '#1565c0', '★ Completed'],
    ];
    $s = $map[$status] ?? ['#f5f5f5', '#555', $status];
    return "<span style='background:{$s[0]};color:{$s[1]};padding:3px 10px;border-radius:50px;font-size:0.72rem;font-weight:700;'>{$s[2]}</span>";
}

function roleBadge($role) {
    if ($role == 'admin') return "<span style='background:#ffebee;color:#c62828;padding:3px 10px;border-radius:50px;font-size:0.72rem;font-weight:700;'>👑 Admin</span>";
    if ($role == 'staff') return "<span style='background:#e3f2fd;color:#1565c0;padding:3px 10px;border-radius:50px;font-size:0.72rem;font-weight:700;'>🛠️ Staff</span>";
    return "<span style='background:#e8f5e9;color:#2e7d32;padding:3px 10px;border-radius:50px;font-size:0.72rem;font-weight:700;'>👤 Customer</span>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Admin Dashboard – GlobeTrek Adventures</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --navy: #0a1628; --ocean: #0e3a6e; --sky: #1a6fb5; --azure: #2196f3;
    --aqua: #00b4d8; --white: #fff; --cream: #f8f4ef;
    --orange: #ff6b2b; --gold: #f4c430; --red: #e74c3c;
    --text-mid: #3a4a5c; --text-light: #7a8a9a;
    --sidebar-w: 260px;
}
body { font-family: 'DM Sans', sans-serif; background: #f0f4f9; color: var(--navy); display: flex; min-height: 100vh; }

/* ── SIDEBAR ─────────────────────────────────────────────── */
.sidebar {
    width: var(--sidebar-w); min-width: var(--sidebar-w);
    background: var(--navy); display: flex; flex-direction: column;
    padding: 24px 0; position: sticky; top: 0; height: 100vh;
}
.sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 0 20px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); margin-bottom: 20px; text-decoration: none; }
.logo-box { width: 34px; height: 34px; background: linear-gradient(135deg, var(--azure), var(--aqua)); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
.logo-txt { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: #fff; font-weight: 700; }
.logo-txt span { color: var(--aqua); }
.nav-label { font-size: 0.65rem; font-weight: 700; color: rgba(255,255,255,0.28); text-transform: uppercase; letter-spacing: 0.12em; padding: 0 20px; margin: 15px 0 6px 0; }
.nav-item { display: flex; align-items: center; gap: 11px; padding: 10px 14px 10px 20px; color: rgba(255,255,255,0.58); font-size: 0.87rem; font-weight: 500; text-decoration: none; transition: all 0.18s; position: relative; margin: 1px 0; }
.nav-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
.nav-item.active { background: rgba(33,150,243,0.16); color: #fff; }
.nav-item.active::before { content: ''; position: absolute; left: 0; top: 18%; bottom: 18%; width: 3px; background: var(--azure); border-radius: 0 3px 3px 0; }
.nav-icon { font-size: 1rem; width: 20px; text-align: center; }
.badge { margin-left: auto; background: var(--orange); color: #fff; font-size: 0.62rem; font-weight: 700; padding: 2px 6px; border-radius: 50px; }
.sidebar-footer { margin-top: auto; padding: 16px 14px 0; border-top: 1px solid rgba(255,255,255,0.08); }
.admin-card { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: rgba(255,255,255,0.06); border-radius: 10px; }
.admin-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #c62828, #ff6b2b); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.9rem; }
.admin-name { color: #fff; font-size: 0.85rem; font-weight: 600; }
.admin-role { color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: capitalize; }
.logout-link { margin-left: auto; color: rgba(255,255,255,0.35); font-size: 0.82rem; text-decoration: none; padding: 4px; transition: color 0.18s; }
.logout-link:hover { color: #fff; }

/* ── MAIN ────────────────────────────────────────────────── */
.main { flex: 1; display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden; }
.topbar { background: #fff; padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e8edf3; position: sticky; top: 0; z-index: 40; }
.topbar h1 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--navy); }
.topbar p { font-size: 0.78rem; color: var(--text-light); margin-top: 1px; }
.topbar-actions { display: flex; align-items: center; gap: 10px; }
.tag-admin { background: #ffebee; color: #c62828; font-size: 0.72rem; font-weight: 700; padding: 4px 12px; border-radius: 50px; }
.content { padding: 28px 32px; flex: 1; }

/* ── FLASH ────────────────────────────────────────────────── */
.flash { background: #e8f5e9; border: 1px solid #a5d6a7; color: #2e7d32; padding: 11px 18px; border-radius: 10px; font-size: 0.87rem; margin-bottom: 22px; }

/* ── STAT CARDS ───────────────────────────────────────────── */
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 18px; margin-bottom: 28px; }
.stat { background: #fff; border-radius: 14px; padding: 20px 22px; box-shadow: 0 3px 16px rgba(10,22,40,0.07); display: flex; align-items: center; gap: 14px; }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.s-blue { background: #e3f2fd; }
.s-orange { background: #fff3e0; }
.s-green { background: #e8f5e9; }
.s-purple { background: #f3e5f5; }
.s-red { background: #ffebee; }
.stat-val { font-family: 'Playfair Display', serif; font-size: 1.45rem; font-weight: 700; color: var(--navy); }
.stat-lbl { font-size: 0.75rem; color: var(--text-light); }

/* ── SECTION CARD ─────────────────────────────────────────── */
.card { background: #fff; border-radius: 16px; padding: 22px 24px; box-shadow: 0 3px 16px rgba(10,22,40,0.07); margin-bottom: 24px; }
.card-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.card-head h2 { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--navy); }

.btn { padding: 8px 16px; border: none; border-radius: 9px; font-family: 'DM Sans', sans-serif; font-size: 0.83rem; font-weight: 600; cursor: pointer; transition: all 0.18s; }
.btn-blue { background: var(--azure); color: #fff; }
.btn-blue:hover { background: var(--sky); }
.btn-orange { background: var(--orange); color: #fff; }
.btn-orange:hover { background: #e85a1b; }
.btn-red { background: var(--red); color: #fff; }
.btn-red:hover { background: #c0392b; }
.btn-sm { padding: 5px 12px; font-size: 0.77rem; }
.btn-outline { background: transparent; border: 1.5px solid #e0e8f0; color: var(--text-mid); }
.btn-outline:hover { border-color: var(--azure); color: var(--azure); }

/* ── TABLE ────────────────────────────────────────────────── */
.tbl-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
thead th { text-align: left; padding: 10px 12px; background: #f7f9fc; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-light); border-bottom: 1px solid #eef2f7; white-space: nowrap; }
tbody tr { border-bottom: 1px solid #f0f4f9; transition: background 0.15s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f7f9fc; }
td { padding: 11px 12px; color: var(--text-mid); vertical-align: middle; }
td strong { color: var(--navy); }

/* ── MODAL ────────────────────────────────────────────────── */
.modal-overlay { display: none; position: fixed; inset: 0; background: rgba(10,22,40,0.55); backdrop-filter: blur(4px); z-index: 200; align-items: center; justify-content: center; }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 18px; padding: 32px; max-width: 560px; width: 92%; box-shadow: 0 30px 80px rgba(0,0,0,0.25); position: relative; animation: popIn 0.25s ease; max-height: 90vh; overflow-y: auto; }
.modal h3 { font-family: 'Playfair Display', serif; font-size: 1.25rem; color: var(--navy); margin-bottom: 20px; }
.modal-close { position: absolute; top: 14px; right: 14px; width: 28px; height: 28px; border-radius: 50%; border: none; background: #f0f4f9; cursor: pointer; font-size: 1rem; display: flex; align-items: center; justify-content: center; }
.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-group.full { grid-column: 1 / -1; }
.form-group label { font-size: 0.75rem; font-weight: 600; color: var(--text-mid); text-transform: uppercase; letter-spacing: 0.05em; }
.form-group input, .form-group select, .form-group textarea {
    padding: 10px 12px; border: 1.5px solid #e0e8f0; border-radius: 10px;
    font-family: 'DM Sans', sans-serif; font-size: 0.88rem; outline: none;
    transition: border-color 0.2s;
}
.form-group input:focus, .form-group select:focus, .form-group textarea:focus { border-color: var(--azure); }
.form-group textarea { resize: vertical; min-height: 70px; }
.modal-actions { display: flex; gap: 10px; margin-top: 20px; justify-content: flex-end; }
@keyframes popIn { from { transform: scale(0.93); opacity: 0; } to { transform: scale(1); opacity: 1; } }

.empty { text-align: center; padding: 48px 20px; color: var(--text-light); }
.empty-icon { font-size: 3rem; margin-bottom: 12px; }

@media (max-width: 900px) { .stats { grid-template-columns: repeat(2,1fr); } .sidebar { display: none; } .content { padding: 20px 16px; } }
</style>
</head>
<body>

<!-- ═══════════════════ SIDEBAR ═══════════════════ -->
<aside class="sidebar">
    <a href="index.php" class="sidebar-logo">
        
        <span class="logo-txt">Globe<span>Trek</span></span>
    </a>

    <div class="nav-label">Admin Controls</div>
    <a href="?section=dashboard" class="nav-item <?= $section === 'dashboard' ? 'active' : '' ?>">
        <span class="nav-icon">📊</span> Dashboard
    </a>
    <a href="?section=users" class="nav-item <?= $section === 'users' ? 'active' : '' ?>">
        <span class="nav-icon">👥</span> Manage Users
    </a>
    <a href="?section=packages" class="nav-item <?= $section === 'packages' ? 'active' : '' ?>">
        <span class="nav-icon">🗺️</span> Manage Packages
    </a>
    <a href="?section=bookings" class="nav-item <?= $section === 'bookings' ? 'active' : '' ?>">
        <span class="nav-icon">📅</span> All Bookings
        <?php if ($pending_bookings > 0): ?>
            <span class="badge"><?= $pending_bookings ?></span>
        <?php endif; ?>
    </a>

    <div class="sidebar-footer">
        <div class="admin-card">
            <div class="admin-av"><?= strtoupper(substr($admin_name, 0, 1)) ?></div>
            <div>
                <div class="admin-name"><?= $admin_name ?></div>
                <div class="admin-role"><?= $admin_role ?></div>
            </div>
            <a href="login.html" class="logout-link" title="Logout">⏏</a>
        </div>
    </div>
</aside>

<!-- ═══════════════════ MAIN ═══════════════════════ -->
<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div>
            <h1>Admin Dashboard</h1>
            <p>GlobeTrek Adventures · Full System Control</p>
        </div>
        <div class="topbar-actions">
            <span class="tag-admin">Admin Access</span>
        </div>
    </div>

    <div class="content">

        <!-- Flash message -->
        <?php if ($flash): ?>
        <div class="flash"><?= $flash ?></div>
        <?php endif; ?>

        <!-- ═══════════════════ DASHBOARD SECTION ═══════════════════ -->
        <?php if ($section === 'dashboard'): ?>
        
        <div class="stats">
            <div class="stat">
                <div class="stat-icon s-blue">👥</div>
                <div><div class="stat-val"><?= number_format($total_users) ?></div><div class="stat-lbl">Total Users</div></div>
            </div>
            <div class="stat">
                <div class="stat-icon s-green">👤</div>
                <div><div class="stat-val"><?= number_format($total_customers) ?></div><div class="stat-lbl">Customers</div></div>
            </div>
            <div class="stat">
                <div class="stat-icon s-purple">🛠️</div>
                <div><div class="stat-val"><?= number_format($total_staff) ?></div><div class="stat-lbl">Staff</div></div>
            </div>
            <div class="stat">
                <div class="stat-icon s-orange">🧳</div>
                <div><div class="stat-val"><?= number_format($total_bookings) ?></div><div class="stat-lbl">Bookings</div></div>
            </div>
            <div class="stat">
                <div class="stat-icon s-blue">🗺️</div>
                <div><div class="stat-val"><?= number_format($total_packages) ?></div><div class="stat-lbl">Packages</div></div>
            </div>
            <div class="stat">
                <div class="stat-icon s-red">💰</div>
                <div><div class="stat-val">LKR <?= number_format($total_revenue, 2) ?></div><div class="stat-lbl">Revenue</div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <h2>📋 Recent Bookings</h2>
                <a href="?section=bookings" class="btn btn-outline btn-sm">View All →</a>
            </div>
            <?php if (empty($bookings)): ?>
                <div class="empty"><div class="empty-icon">📭</div><p>No bookings yet.</p></div>
            <?php else: ?>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Customer</th><th>Package</th><th>Date</th><th>Price</th><th>Status</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach (array_slice($bookings, 0, 8) as $b): ?>
                            <tr>
                                <td><strong>#<?= $b['id'] ?></strong></td>
                                <td><?= htmlspecialchars($b['customer_name']) ?></td>
                                <td><?= htmlspecialchars($b['package_name']) ?></td>
                                <td><?= $b['date'] ?></td>
                                <td>LKR <?= number_format($b['price'], 2) ?></td>
                                <td><?= statusBadge($b['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══════════════════ MANAGE USERS SECTION ═══════════════════ -->
        <?php elseif ($section === 'users'): ?>
        <div class="card">
            <div class="card-head">
                <h2>👥 Manage Users (<?= $total_users ?> total)</h2>
            </div>

            <?php if (empty($users)): ?>
                <div class="empty"><div class="empty-icon">👤</div><p>No users found.</p></div>
            <?php else: ?>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td><strong>#<?= $u['id'] ?></strong></td>
                                <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                                <td><?= htmlspecialchars($u['email']) ?></td>
                                <td><?= roleBadge($u['role']) ?></td>
                                <td>—</td>
                                <td>
                                    <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('⚠️ Delete this user permanently? All their bookings will also be deleted.');">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                                        <button type="submit" class="btn btn-red btn-sm">Delete</button>
                                    </form>
                                    <?php else: ?>
                                        <span style="color:#999; font-size:0.75rem;">Current</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

       <!-- ═══════════════════ MANAGE PACKAGES SECTION ═══════════════════ -->
<?php elseif ($section === 'packages'): ?>
<div class="card">
    <div class="card-head">
        <h2>🗺️ Manage Packages</h2>
        <button class="btn btn-blue" onclick="openAddPackageModal()">➕ Add New Package</button>
    </div>

    <?php if (empty($packages)): ?>
        <div class="empty">
            <div class="empty-icon">📦</div>
            <p>No packages found. Click "Add New Package" to get started.</p>
        </div>
    <?php else: ?>
        <div class="tbl-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Image</th>
                        <th>Package Name</th>
                        <th>Destination</th>
                        <th>Price</th>
                        <th>Duration</th>
                        <th>Slots</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($packages as $p): ?>
                <tr>
                    <td><strong>#<?= $p['id'] ?? '-' ?></strong></td>

                    <!-- IMAGE -->
                    <td>
                        <?php if (!empty($p['image'])): ?>
                            <img src="<?= htmlspecialchars($p['image']) ?>" 
                                 style="width:50px;height:35px;object-fit:cover;border-radius:6px;">
                        <?php else: ?>
                            <span style="color:#ccc;">—</span>
                        <?php endif; ?>
                    </td>

                    <!-- NAME -->
                    <td>
                        <strong><?= htmlspecialchars($p['name'] ?? 'N/A') ?></strong>
                    </td>

                    <!-- DESTINATION (FIXED) -->
                    <td>
                        <?= htmlspecialchars($p['destination'] ?? 'N/A') ?>
                    </td>

                    <!-- PRICE -->
                    <td>
                        LKR <?= number_format($p['price'] ?? 0, 2) ?>
                    </td>

                    <!-- DURATION (FIXED) -->
                    <td>
                        <?= isset($p['duration_days']) ? (int)$p['duration_days'] . ' days' : '0 days' ?>
                    </td>

                    <!-- AVAILABILITY -->
                    <td>
                        <?= isset($p['availability']) ? (int)$p['availability'] : 0 ?>
                    </td>

                    <!-- ACTIONS -->
                    <td>
                        <button class="btn btn-orange btn-sm"
                            onclick='openEditPackageModal(<?= json_encode($p) ?>)'>
                            Edit
                        </button>

                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('Delete this package?');">
                            <input type="hidden" name="action" value="delete_package">
                            <input type="hidden" name="package_id" value="<?= $p['id'] ?>">
                            <button type="submit" class="btn btn-red btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

        <!-- ═══════════════════ ALL BOOKINGS SECTION ═══════════════════ -->
        <?php elseif ($section === 'bookings'): ?>
        <div class="card">
            <div class="card-head">
                <h2>📋 All Bookings (<?= $total_bookings ?> total)</h2>
            </div>

            <?php if (empty($bookings)): ?>
                <div class="empty"><div class="empty-icon">📭</div><p>No bookings found.</p></div>
            <?php else: ?>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr><th>ID</th><th>Customer</th><th>Package</th><th>Date</th><th>Price</th><th>Status</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td><strong>#<?= $b['id'] ?></strong></td>
                                <td><?= htmlspecialchars($b['customer_name']) ?><br><span style="font-size:0.7rem;color:#777;"><?= htmlspecialchars($b['customer_email']) ?></span></td>
                                <td><?= htmlspecialchars($b['package_name']) ?></td>
                                <td><?= $b['date'] ?></td>
                                <td>LKR <?= number_format($b['price'], 2) ?></td>
                                <td><?= statusBadge($b['status']) ?></td>
                                <td>
                                    <button class="btn btn-orange btn-sm" onclick="openStatusModal(<?= $b['id'] ?>, '<?= $b['status'] ?>')">Edit Status</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div><!-- end content -->
</div><!-- end main -->

<!-- ═══════════════════ MODALS ═══════════════════ -->

<!-- Update Booking Status Modal -->
<div class="modal-overlay" id="statusModal">
    <div class="modal">
                <button class="modal-close" onclick="closeStatusModal()">&times;</button>
        <h3>Update Booking Status</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update_booking_status">
            <input type="hidden" name="booking_id" id="statusBookingId">
            
            <div class="form-group">
                <label>New Status</label>
                <select name="status" id="statusSelect" required>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeStatusModal()">Cancel</button>
                <button type="submit" class="btn btn-blue">Update Status</button>
            </div>
        </form>
    </div>
</div>

<!-- Add/Edit Package Modal -->
<div class="modal-overlay" id="packageModal">
    <div class="modal">
        <button class="modal-close" onclick="closePackageModal()">&times;</button>
        <h3 id="packageModalTitle">Add New Package</h3>
        <form method="POST" id="packageForm">
            <input type="hidden" name="action" value="save_package">
            <input type="hidden" name="package_id" id="packageId">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Package Name *</label>
                    <input type="text" name="name" id="packageName" required>
                </div>
                <div class="form-group">
                    <label>Destination *</label>
                    <input type="text" name="destination" id="packageDestination" required>
                </div>
                <div class="form-group">
                    <label>Price (LKR) *</label>
                    <input type="number" step="0.01" name="price" id="packagePrice" required>
                </div>
                <div class="form-group">
                    <label>Duration (Days) *</label>
                    <input type="number" name="duration" id="packageDuration" required>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" id="packageDescription" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Availability (Slots)</label>
                    <input type="number" name="availability" id="packageAvailability" value="10">
                </div>
                <div class="form-group full">
                    <label>Image URL</label>
                    <input type="text" name="image" id="packageImage" placeholder="https://example.com/image.jpg">
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closePackageModal()">Cancel</button>
                <button type="submit" class="btn btn-blue">Save Package</button>
            </div>
        </form>
    </div>
</div>

<script>
// Status Modal Functions
function openStatusModal(id, status) {
    const modal = document.getElementById('statusModal');
    modal.classList.add('open');
    document.getElementById('statusBookingId').value = id;
    document.getElementById('statusSelect').value = status;
}

function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    modal.classList.remove('open');
}

// Package Modal Functions
function openAddPackageModal() {
    const modal = document.getElementById('packageModal');
    const title = document.getElementById('packageModalTitle');
    const form = document.getElementById('packageForm');
    
    title.textContent = 'Add New Package';
    document.getElementById('packageId').value = '';
    document.getElementById('packageName').value = '';
    document.getElementById('packageDestination').value = '';
    document.getElementById('packagePrice').value = '';
    document.getElementById('packageDuration').value = '';
    document.getElementById('packageDescription').value = '';
    document.getElementById('packageAvailability').value = '10';
    document.getElementById('packageImage').value = '';
    
    modal.classList.add('open');
}

function openEditPackageModal(packageData) {
    const modal = document.getElementById('packageModal');
    const title = document.getElementById('packageModalTitle');
    
    title.textContent = 'Edit Package';
    document.getElementById('packageId').value = packageData.id;
    document.getElementById('packageName').value = packageData.name;
    document.getElementById('packageDestination').value = packageData.destination;
    document.getElementById('packagePrice').value = packageData.price;
    document.getElementById('packageDuration').value = packageData.duration_days;
    document.getElementById('packageDescription').value = packageData.description || '';
    document.getElementById('packageAvailability').value = packageData.availability;
    document.getElementById('packageImage').value = packageData.image || '';
    
    modal.classList.add('open');
}

function closePackageModal() {
    const modal = document.getElementById('packageModal');
    modal.classList.remove('open');
}

// Close modals when clicking outside
window.onclick = function(event) {
    const statusModal = document.getElementById('statusModal');
    const packageModal = document.getElementById('packageModal');
    
    if (event.target === statusModal) {
        statusModal.classList.remove('open');
    }
    if (event.target === packageModal) {
        packageModal.classList.remove('open');
    }
}
</script>

</body>
</html>
        