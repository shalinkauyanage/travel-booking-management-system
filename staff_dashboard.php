<?php
// ============================================================
//  GlobeTrek Adventures — Staff Dashboard
//  Access: Staff only (role = 'staff')
// ============================================================
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

// ── AUTH GUARD ───────────────────────────────────────────────
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit();
}

// allow ONLY staff
if ($_SESSION['role'] != 'staff') {
    header("Location: login.php");
    exit();
}

$staff_name = htmlspecialchars($_SESSION['name'] ?? 'Staff Member');
$staff_role = htmlspecialchars($_SESSION['role'] ?? 'staff');

// ── Active section (tab) ─────────────────────────────────────
$section = isset($_GET['section']) ? $_GET['section'] : 'bookings';
$allowed_sections = ['bookings', 'packages', 'customers'];
if (!in_array($section, $allowed_sections)) $section = 'bookings';

// ── Flash messages ───────────────────────────────────────────
$flash = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// ============================================================
//  POST ACTIONS
// ============================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    $action = $_POST['action'];

    // ── 1. UPDATE BOOKING STATUS ─────────────────────────────
    if ($action === 'update_booking_status') {
        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        $new_status = $_POST['status'] ?? '';
        $allowed_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];

        if ($booking_id > 0 && in_array($new_status, $allowed_statuses)) {
            $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $booking_id]);
            $_SESSION['flash'] = "Booking #$booking_id updated to '$new_status'.";
        }
        header("Location: staff_dashboard.php?section=bookings");
        exit;
    }

    // ── 2. UPDATE PACKAGE DETAILS ────────────────────────────
    if ($action === 'update_package') {
        $pkg_id       = (int) ($_POST['package_id'] ?? 0);
        $name         = trim($_POST['name'] ?? '');
        $destination  = trim($_POST['destination'] ?? '');
        $price        = (float) ($_POST['price'] ?? 0);
        $duration     = (int) ($_POST['duration'] ?? 0);
        $description  = trim($_POST['description'] ?? '');
        $availability = (int) ($_POST['availability'] ?? 0);
        $image        = trim($_POST['image'] ?? '');

        if ($pkg_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE packages
                SET name=?, destination=?, price=?, duration_days=?,
                    description=?, availability=?, image=?
                WHERE id=?
            ");
            $stmt->execute([$name, $destination, $price, $duration, $description, $availability, $image, $pkg_id]);
            $_SESSION['flash'] = "Package '$name' updated successfully.";
        }
        header("Location: staff_dashboard.php?section=packages");
        exit;
    }

    // ── 3. UPDATE CUSTOMER DETAILS ───────────────────────────
    if ($action === 'update_customer') {
        $customer_id = (int) ($_POST['customer_id'] ?? 0);
        $full_name   = trim($_POST['full_name'] ?? '');
        $phone       = trim($_POST['phone'] ?? '');
        $country     = trim($_POST['country'] ?? '');

        if ($customer_id > 0) {
            $stmt = $pdo->prepare("
                UPDATE users
                SET name=?
                WHERE id=? AND role='customer'
            ");
            $stmt->execute([$full_name, $customer_id]);
            $_SESSION['flash'] = "Customer record updated.";
        }
        header("Location: staff_dashboard.php?section=customers");
        exit;
    }

    // ── 4. ADD NEW PACKAGE ──────────────────────────────
    if ($action === 'add_package') {
        $name         = trim($_POST['name'] ?? '');
        $destination  = trim($_POST['destination'] ?? '');
        $price        = (float) ($_POST['price'] ?? 0);
        $duration     = (int) ($_POST['duration'] ?? 0);
        $description  = trim($_POST['description'] ?? '');
        $availability = (int) ($_POST['availability'] ?? 0);
        $image        = trim($_POST['image'] ?? '');

        if (!empty($name) && $price > 0) {
            $stmt = $pdo->prepare("
                INSERT INTO packages (name, destination, price, duration_days, description, availability, image)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $destination, $price, $duration, $description, $availability, $image]);
            $_SESSION['flash'] = "New package '$name' added successfully.";
        } else {
            $_SESSION['flash'] = "Failed to add package. Please check required fields.";
        }
        header("Location: staff_dashboard.php?section=packages");
        exit;
    }

    // ── 5. DELETE PACKAGE ───────────────────────────────
    if ($action === 'delete_package') {
        $pkg_id = (int) ($_POST['package_id'] ?? 0);
        if ($pkg_id > 0) {
            $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
            $stmt->execute([$pkg_id]);
            $_SESSION['flash'] = "Package deleted successfully.";
        }
        header("Location: staff_dashboard.php?section=packages");
        exit;
    }
}

// ============================================================
//  DATA QUERIES
// ============================================================

// ── Summary counts ───────────────────────────────────────────
$total_bookings   = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pending_bookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
$total_customers  = $pdo->query("SELECT COUNT(*) FROM users WHERE role='customer'")->fetchColumn();
$total_packages   = $pdo->query("SELECT COUNT(*) FROM packages")->fetchColumn();

// ── Bookings data ────────────────────────────────────────────
$booking_sql = "
    SELECT 
        b.id,
        b.date,
        b.price,
        b.status,
        u.name AS customer_name,
        u.email AS customer_email,
        b.package_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    ORDER BY b.date DESC LIMIT 50
";
$stmt = $pdo->prepare($booking_sql);
$stmt->execute();
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Packages data ────────────────────────────────────────────
$packages = $pdo->query("SELECT * FROM packages ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);

// ── Customers data ───────────────────────────────────────────
$cust_sql = "
    SELECT u.id, u.name, u.email,
           COUNT(b.id) AS booking_count
    FROM users u
    LEFT JOIN bookings b ON b.user_id = u.id
    WHERE u.role = 'customer'
    GROUP BY u.id ORDER BY u.id DESC LIMIT 100
";
$customers = $pdo->query($cust_sql)->fetchAll(PDO::FETCH_ASSOC);

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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Staff Dashboard – GlobeTrek Adventures</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --navy: #0a1628; --ocean: #0e3a6e; --sky: #1a6fb5; --azure: #2196f3;
    --aqua: #00b4d8; --white: #fff; --cream: #f8f4ef;
    --orange: #ff6b2b; --gold: #f4c430; --red: #e74c3c;
    --text-mid: #3a4a5c; --text-light: #7a8a9a;
    --sidebar-w: 240px;
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
.nav-label { font-size: 0.65rem; font-weight: 700; color: rgba(255,255,255,0.28); text-transform: uppercase; letter-spacing: 0.12em; padding: 0 20px; margin-bottom: 6px; }
.nav-item { display: flex; align-items: center; gap: 11px; padding: 10px 14px 10px 20px; color: rgba(255,255,255,0.58); font-size: 0.87rem; font-weight: 500; text-decoration: none; transition: all 0.18s; position: relative; margin: 1px 0; }
.nav-item:hover { background: rgba(255,255,255,0.06); color: #fff; }
.nav-item.active { background: rgba(33,150,243,0.16); color: #fff; }
.nav-item.active::before { content: ''; position: absolute; left: 0; top: 18%; bottom: 18%; width: 3px; background: var(--azure); border-radius: 0 3px 3px 0; }
.nav-icon { font-size: 1rem; width: 18px; text-align: center; }
.badge { margin-left: auto; background: var(--orange); color: #fff; font-size: 0.62rem; font-weight: 700; padding: 2px 6px; border-radius: 50px; }
.sidebar-footer { margin-top: auto; padding: 16px 14px 0; border-top: 1px solid rgba(255,255,255,0.08); }
.staff-card { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: rgba(255,255,255,0.06); border-radius: 10px; }
.staff-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, #43a047, #1b5e20); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.9rem; }
.staff-name { color: #fff; font-size: 0.85rem; font-weight: 600; }
.staff-role { color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: capitalize; }
.logout-link { margin-left: auto; color: rgba(255,255,255,0.35); font-size: 0.82rem; text-decoration: none; padding: 4px; transition: color 0.18s; }
.logout-link:hover { color: #fff; }

/* ── MAIN ────────────────────────────────────────────────── */
.main { flex: 1; display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden; }
.topbar { background: #fff; padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e8edf3; position: sticky; top: 0; z-index: 40; }
.topbar h1 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--navy); }
.topbar p { font-size: 0.78rem; color: var(--text-light); margin-top: 1px; }
.topbar-actions { display: flex; align-items: center; gap: 10px; }
.tag-staff { background: #e8f5e9; color: #2e7d32; font-size: 0.72rem; font-weight: 700; padding: 4px 12px; border-radius: 50px; }
.content { padding: 28px 32px; flex: 1; }

/* ── FLASH ────────────────────────────────────────────────── */
.flash { background: #e8f5e9; border: 1px solid #a5d6a7; color: #2e7d32; padding: 11px 18px; border-radius: 10px; font-size: 0.87rem; margin-bottom: 22px; }

/* ── STAT CARDS ───────────────────────────────────────────── */
.stats { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 28px; }
.stat { background: #fff; border-radius: 14px; padding: 20px 22px; box-shadow: 0 3px 16px rgba(10,22,40,0.07); display: flex; align-items: center; gap: 14px; }
.stat-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; }
.s-blue { background: #e3f2fd; }
.s-orange { background: #fff3e0; }
.s-green { background: #e8f5e9; }
.s-purple { background: #f3e5f5; }
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
.btn-full { width: 100%; }

/* ── TABLE ────────────────────────────────────────────────── */
.tbl-wrap { overflow-x: auto; }
table { width: 100%; border-collapse: collapse; font-size: 0.84rem; }
thead th { text-align: left; padding: 10px 12px; background: #f7f9fc; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: var(--text-light); border-bottom: 1px solid #eef2f7; white-space: nowrap; }
tbody tr { border-bottom: 1px solid #f0f4f9; transition: background 0.15s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: #f7f9fc; }
td { padding: 11px 12px; color: var(--text-mid); vertical-align: middle; }
td strong { color: var(--navy); }

/* ── MODAL STYLES (FIXED) ─────────────────────────────────── */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(10, 22, 40, 0.55);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
}

.modal.show {
    display: flex;
}

.modal-content {
    background: #fff;
    border-radius: 18px;
    padding: 32px;
    max-width: 500px;
    width: 92%;
    position: relative;
    animation: popIn 0.25s ease;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-content h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    color: var(--navy);
    margin-bottom: 20px;
}

.modal-close {
    position: absolute;
    top: 14px;
    right: 14px;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    border: none;
    background: #f0f4f9;
    cursor: pointer;
    font-size: 1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.18s;
}

.modal-close:hover {
    background: #e0e8f0;
}

.modal-content label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-mid);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    display: block;
    margin-bottom: 6px;
}

.modal-content select,
.modal-content input,
.modal-content textarea {
    width: 100%;
    padding: 10px 12px;
    border: 1.5px solid #e0e8f0;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    outline: none;
    transition: border-color 0.2s;
}

.modal-content select:focus,
.modal-content input:focus,
.modal-content textarea:focus {
    border-color: var(--azure);
}

/* Package/Customer Modal Grid */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.form-group.full {
    grid-column: 1 / -1;
}

.form-group label {
    font-size: 0.7rem;
    font-weight: 600;
    color: var(--text-mid);
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0;
}

.form-group input,
.form-group select,
.form-group textarea {
    padding: 10px 12px;
    border: 1.5px solid #e0e8f0;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    outline: none;
}

.form-group textarea {
    resize: vertical;
    min-height: 70px;
}

.modal-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
    justify-content: flex-end;
}

@keyframes popIn {
    from {
        transform: scale(0.93);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.empty {
    text-align: center;
    padding: 48px 20px;
    color: var(--text-light);
}

.empty-icon {
    font-size: 3rem;
    margin-bottom: 12px;
}

@media (max-width: 900px) {
    .stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .sidebar {
        display: none;
    }
    .content {
        padding: 20px 16px;
    }
}
</style>
</head>
<body>

<!-- ═══════════════════ SIDEBAR ═══════════════════ -->
<aside class="sidebar">
    <a href="index.php" class="sidebar-logo">
        <span class="logo-txt">Globe<span>Trek</span></span>
    </a>

    <div class="nav-label">Staff Tools</div>
    <a href="?section=bookings"  class="nav-item <?= $section==='bookings'  ? 'active' : '' ?>">
        <span class="nav-icon">🧳</span> All Bookings
        <?php if ($pending_bookings > 0): ?>
            <span class="badge"><?= $pending_bookings ?></span>
        <?php endif; ?>
    </a>
    <a href="?section=packages"  class="nav-item <?= $section==='packages'  ? 'active' : '' ?>">
        <span class="nav-icon">🗺️</span> Packages
    </a>
    <a href="?section=customers" class="nav-item <?= $section==='customers' ? 'active' : '' ?>">
        <span class="nav-icon">👥</span> Customers
    </a>

    <div class="sidebar-footer">
        <div class="staff-card">
            <div class="staff-av"><?= strtoupper(substr($staff_name, 0, 1)) ?></div>
            <div>
                <div class="staff-name"><?= $staff_name ?></div>
                <div class="staff-role"><?= $staff_role ?></div>
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
            <h1>Staff Dashboard</h1>
            <p>GlobeTrek Adventures · Staff Control Panel</p>
        </div>
        <div class="topbar-actions">
            <span class="tag-staff">Staff Access</span>
        </div>
    </div>

    <div class="content">

        <!-- Flash message -->
        <?php if ($flash): ?>
        <div class="flash"><?= $flash ?></div>
        <?php endif; ?>

        <!-- ── STAT CARDS ── -->
        <div class="stats">
            <div class="stat">
                <div class="stat-icon s-blue">🧳</div>
                <div><div class="stat-val"><?= number_format($total_bookings) ?></div><div class="stat-lbl">Total Bookings</div></div>
            </div>
            <div class="stat">
                <div class="stat-icon s-orange">⏳</div>
                <div><div class="stat-val"><?= number_format($pending_bookings) ?></div><div class="stat-lbl">Pending</div></div>
            </div>
            <div class="stat">
                <div class="stat-icon s-green">👥</div>
                <div><div class="stat-val"><?= number_format($total_customers) ?></div><div class="stat-lbl">Customers</div></div>
            </div>
            <div class="stat">
                <div class="stat-icon s-purple">🗺️</div>
                <div><div class="stat-val"><?= number_format($total_packages) ?></div><div class="stat-lbl">Packages</div></div>
            </div>
        </div>

        <!-- ════════════════════════════════════════
             SECTION 1: ALL BOOKINGS
        ════════════════════════════════════════ -->
        <?php if ($section === 'bookings'): ?>
        <div class="card">
            <div class="card-head">
                <h2>📋 All Bookings</h2>
            </div>

            <?php if (empty($bookings)): ?>
            <div class="empty"><div class="empty-icon">📭</div><p>No bookings found.</p></div>
            <?php else: ?>
            <div class="tbl-wrap">
                <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Customer</th>
                        <th>Package</th>
                        <th>Date</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($bookings as $b): ?>
                <tr>
                    <td><strong>#<?= $b['id'] ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($b['customer_name']) ?></strong><br>
                        <span style="font-size:0.75rem;color:var(--text-light)"><?= htmlspecialchars($b['customer_email']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($b['package_name']) ?></td>
                    <td><?= htmlspecialchars($b['date']) ?></td>
                    <td>LKR <?= number_format($b['price'], 2) ?></td>
                    <td><?= statusBadge($b['status']) ?></td>
                    <td>
                        <button class="btn btn-orange btn-sm"
                            onclick="openStatusModal(<?= $b['id'] ?>, '<?= $b['status'] ?>')">
                            Edit Status
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- ════════════════════════════════════════
             SECTION 2: PACKAGES
        ════════════════════════════════════════ -->
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
                        <td>
                            <?php if (!empty($p['image'])): ?>
                                <img src="<?= htmlspecialchars($p['image']) ?>" 
                                     style="width:50px;height:35px;object-fit:cover;border-radius:6px;">
                            <?php else: ?>
                                <span style="color:#ccc;">—</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($p['name'] ?? 'N/A') ?></strong></td>
                        <td><?= htmlspecialchars($p['destination'] ?? 'N/A') ?></td>
                        <td>LKR <?= number_format($p['price'] ?? 0, 2) ?></td>
                        <td><?= isset($p['duration_days']) ? (int)$p['duration_days'] . ' days' : '0 days' ?></td>
                        <td><?= isset($p['availability']) ? (int)$p['availability'] : 0 ?></td>
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

        <!-- ════════════════════════════════════════
             SECTION 3: CUSTOMERS
        ════════════════════════════════════════ -->
        <?php elseif ($section === 'customers'): ?>
        <div class="card">
            <div class="card-head">
                <h2>👥 Manage Customers</h2>
            </div>

            <?php if (empty($customers)): ?>
            <div class="empty"><div class="empty-icon">👤</div><p>No customers found.</p></div>
            <?php else: ?>
            <div class="tbl-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Country</th>
                            <th>Bookings</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><strong>#<?= $c['id'] ?></strong></td>
                        <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td>—</td>
                        <td>—</td>
                        <td><?= (int)$c['booking_count'] ?></td>
                        <td>—</td>
                        <td>
                            <button class="btn btn-orange btn-sm"
                                onclick="openEditCustomer(<?= htmlspecialchars(json_encode($c)) ?>)">
                                Edit
                            </button>
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

<!-- ═══════════════════════════════════════
     MODALS
═══════════════════════════════════════ -->

<!-- STATUS MODAL -->
<div id="statusModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeStatusModal()">&times;</button>
        <h2>Update Booking Status</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_booking_status">
            <input type="hidden" name="booking_id" id="statusBookingId">
            
            <label>New Status</label>
            <select name="status" id="statusSelect" required>
                <option value="pending">Pending</option>
                <option value="confirmed">Confirmed</option>
                <option value="completed">Completed</option>
                <option value="cancelled">Cancelled</option>
            </select>
            
            <button type="submit" class="btn btn-blue btn-full" style="margin-top:15px;">
                Update Status
            </button>

                        </button>
        </form>
    </div>
</div>

<!-- EDIT PACKAGE MODAL -->
<div id="editPackageModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeEditPackageModal()">&times;</button>
        <h2>✏️ Edit Package Details</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_package">
            <input type="hidden" name="package_id" id="edit_package_id">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Package Name</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>
                <div class="form-group">
                    <label>Destination</label>
                    <input type="text" name="destination" id="edit_destination" required>
                </div>
                <div class="form-group">
                    <label>Price (LKR)</label>
                    <input type="number" step="0.01" name="price" id="edit_price" required>
                </div>
                <div class="form-group">
                    <label>Duration (Days)</label>
                    <input type="number" name="duration" id="edit_duration" required>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" id="edit_description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Availability (Slots)</label>
                    <input type="number" name="availability" id="edit_availability" required>
                </div>
                <div class="form-group full">
                    <label>Image URL</label>
                    <input type="text" name="image" id="edit_image" placeholder="https://example.com/image.jpg">
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeEditPackageModal()">Cancel</button>
                <button type="submit" class="btn btn-blue">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- ADD PACKAGE MODAL -->
<div id="addPackageModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeAddPackageModal()">&times;</button>
        <h2>➕ Add New Package</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_package">
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Package Name *</label>
                    <input type="text" name="name" required>
                </div>
                <div class="form-group">
                    <label>Destination *</label>
                    <input type="text" name="destination" required>
                </div>
                <div class="form-group">
                    <label>Price (LKR) *</label>
                    <input type="number" step="0.01" name="price" required>
                </div>
                <div class="form-group">
                    <label>Duration (Days) *</label>
                    <input type="number" name="duration" required>
                </div>
                <div class="form-group full">
                    <label>Description</label>
                    <textarea name="description" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label>Availability (Slots)</label>
                    <input type="number" name="availability" value="10">
                </div>
                <div class="form-group full">
                    <label>Image URL</label>
                    <input type="text" name="image" placeholder="https://example.com/image.jpg">
                </div>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeAddPackageModal()">Cancel</button>
                <button type="submit" class="btn btn-blue">Add Package</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT CUSTOMER MODAL -->
<div id="editCustomerModal" class="modal">
    <div class="modal-content">
        <button class="modal-close" onclick="closeEditCustomerModal()">&times;</button>
        <h2>✏️ Edit Customer Details</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_customer">
            <input type="hidden" name="customer_id" id="edit_customer_id">
            
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" id="edit_customer_name" required>
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-outline" onclick="closeEditCustomerModal()">Cancel</button>
                <button type="submit" class="btn btn-blue">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
// Status Modal Functions
function openStatusModal(id, status) {
    const modal = document.getElementById('statusModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
    document.getElementById('statusBookingId').value = id;
    document.getElementById('statusSelect').value = status;
}

function closeStatusModal() {
    const modal = document.getElementById('statusModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
}

// Edit Package Modal Functions
function openEditPackageModal(packageData) {
    const modal = document.getElementById('editPackageModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
    
    document.getElementById('edit_package_id').value = packageData.id;
    document.getElementById('edit_name').value = packageData.name;
    document.getElementById('edit_destination').value = packageData.destination;
    document.getElementById('edit_price').value = packageData.price;
    document.getElementById('edit_duration').value = packageData.duration_days;
    document.getElementById('edit_description').value = packageData.description || '';
    document.getElementById('edit_availability').value = packageData.availability;
    document.getElementById('edit_image').value = packageData.image || '';
}

function closeEditPackageModal() {
    const modal = document.getElementById('editPackageModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
}

// Add Package Modal Functions
function openAddPackageModal() {
    const modal = document.getElementById('addPackageModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
}

function closeAddPackageModal() {
    const modal = document.getElementById('addPackageModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
}

// Edit Customer Modal Functions
function openEditCustomer(customerData) {
    const modal = document.getElementById('editCustomerModal');
    modal.style.display = 'flex';
    modal.classList.add('show');
    
    document.getElementById('edit_customer_id').value = customerData.id;
    document.getElementById('edit_customer_name').value = customerData.name;
}

function closeEditCustomerModal() {
    const modal = document.getElementById('editCustomerModal');
    modal.style.display = 'none';
    modal.classList.remove('show');
}

// Close modals when clicking outside
window.onclick = function(event) {
    const modals = ['statusModal', 'editPackageModal', 'addPackageModal', 'editCustomerModal'];
    modals.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (event.target === modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    });
}
</script>

</body>
</html>