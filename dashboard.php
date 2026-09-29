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

// ── AUTH GUARD (CUSTOMER ONLY) ───────────────────────────────
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.html");
    exit();
}

// allow ONLY customer
if ($_SESSION['role'] != 'customer') {
    header("Location: login.html");
    exit();
}

$customer_name = htmlspecialchars($_SESSION['name'] ?? 'Traveler');
$customer_email = htmlspecialchars($_SESSION['email'] ?? '');
$customer_id = $_SESSION['user_id'];

// ── Active section (tab) ─────────────────────────────────────
$section = isset($_GET['section']) ? $_GET['section'] : 'packages';
$allowed_sections = ['packages', 'mybookings', 'profile'];
if (!in_array($section, $allowed_sections)) $section = 'packages';

// ── Customize modal state ───────────────────────────────────
$customize_booking_id = isset($_GET['customize']) ? (int)$_GET['customize'] : 0;
$customize_data = null;
if ($customize_booking_id > 0 && $section === 'mybookings') {
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$customize_booking_id, $customer_id]);
    $customize_data = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ── Flash messages ───────────────────────────────────────────
$flash = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

// ============================================================
//  POST ACTIONS
// ============================================================

// Book a package
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // ── BOOK PACKAGE ─────────────────────────────────────────
    if ($action === 'book_package') {
        $package_id = (int) ($_POST['package_id'] ?? 0);
        $package_name = trim($_POST['package_name'] ?? '');
        $price = (float) ($_POST['price'] ?? 0);

        if ($package_id > 0 && !empty($package_name)) {
            // Check if package exists and has availability
            $check = $pdo->prepare("SELECT availability FROM packages WHERE id = ?");
            $check->execute([$package_id]);
            $pkg = $check->fetch(PDO::FETCH_ASSOC);

            if ($pkg && $pkg['availability'] > 0) {
                // Create booking with customization fields
                $stmt = $pdo->prepare("
                    INSERT INTO bookings (user_id, package_id, package_name, price, status, date)
VALUES (?, ?, ?, ?, 'pending', CURDATE())
                ");
                $stmt->execute([$customer_id, $package_id, $package_name, $price]);

                // Decrease availability
                $update = $pdo->prepare("UPDATE packages SET availability = availability - 1 WHERE id = ?");
                $update->execute([$package_id]);

                $_SESSION['flash'] = "Package '$package_name' booked successfully! Status: Pending.";
            } else {
                $_SESSION['flash'] = "Sorry, this package is no longer available.";
            }
        } else {
            $_SESSION['flash'] = "Invalid booking request.";
        }

        header("Location: dashboard.php?section=packages");
        exit;
    }

    // ── CANCEL BOOKING ───────────────────────────────────────
    if ($action === 'cancel_booking') {
        $booking_id = (int) ($_POST['booking_id'] ?? 0);

        if ($booking_id > 0) {
            // Get package_id and status before cancel
            $stmt = $pdo->prepare("SELECT package_id, status FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $customer_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking && strtolower(trim($booking['status'])) == 'pending') {
                // Update booking status to cancelled
                $cancel = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ? AND user_id = ?");
                $cancel->execute([$booking_id, $customer_id]);

                // Increase availability back
                if ($booking['package_id']) {
                    $restore = $pdo->prepare("UPDATE packages SET availability = availability + 1 WHERE id = ?");
                    $restore->execute([$booking['package_id']]);
                }

                $_SESSION['flash'] = "Booking #$booking_id has been cancelled.";
            } else {
                $_SESSION['flash'] = " Only pending bookings can be cancelled.";
            }
        }

        header("Location: dashboard.php?section=mybookings");
        exit;
    }

    // ── DELETE BOOKING (Customization: Delete) ────────────────
    if ($action === 'delete_booking') {
        $booking_id = (int) ($_POST['booking_id'] ?? 0);

        if ($booking_id > 0) {
            $stmt = $pdo->prepare("SELECT package_id, status FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $customer_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking && strtolower(trim($booking['status'])) == 'pending') {
                // Restore availability
                if ($booking['package_id']) {
                    $restore = $pdo->prepare("UPDATE packages SET availability = availability + 1 WHERE id = ?");
                    $restore->execute([$booking['package_id']]);
                }
                // Delete booking
                $delete = $pdo->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
                $delete->execute([$booking_id, $customer_id]);

                $_SESSION['flash'] = " Booking #$booking_id has been deleted.";
            } else {
                $_SESSION['flash'] = " Only pending bookings can be deleted.";
            }
        }

        header("Location: dashboard.php?section=mybookings");
        exit;
    }

    // ── UPDATE PROFILE ───────────────────────────────────────
    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!empty($name) && !empty($email)) {
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'customer'");
            $stmt->execute([$name, $email, $customer_id]);

            // Update session
            $_SESSION['name'] = $name;
            $_SESSION['email'] = $email;
            $customer_name = htmlspecialchars($name);
            $customer_email = htmlspecialchars($email);

            $_SESSION['flash'] = "Profile updated successfully!";
        } else {
            $_SESSION['flash'] = " Name and email cannot be empty.";
        }

        header("Location: dashboard.php?section=profile");
        exit;
    }

    // ── CUSTOMIZE BOOKING (Save customized details) ───────────
    if ($action === 'customize_booking') {
        $booking_id = (int) ($_POST['booking_id'] ?? 0);
        $custom_date = !empty($_POST['custom_date']) ? $_POST['custom_date'] : null;
        $custom_duration = !empty($_POST['custom_duration']) ? (int)$_POST['custom_duration'] : null;
        $custom_travelers = !empty($_POST['custom_travelers']) ? (int)$_POST['custom_travelers'] : null;
        $special_requests = trim($_POST['special_requests'] ?? '');

        if ($booking_id > 0) {
            $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ? AND user_id = ?");
            $stmt->execute([$booking_id, $customer_id]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($booking && strtolower(trim($booking['status'])) == 'pending') {
                $update = $pdo->prepare("
                    UPDATE bookings 
                    SET custom_date = ?, custom_duration = ?, custom_travelers = ?, special_requests = ?
                    WHERE id = ? AND user_id = ?
                ");
                $update->execute([$custom_date, $custom_duration, $custom_travelers, $special_requests, $booking_id, $customer_id]);

                $_SESSION['flash'] = " Booking #$booking_id has been customized!";
            } else {
                $_SESSION['flash'] = " Only pending bookings can be customized.";
            }
        }

        header("Location: dashboard.php?section=mybookings");
        exit;
    }
}

// ============================================================
//  DATA QUERIES
// ============================================================

// ── All available packages (with availability > 0) ───────────
$packages_sql = "SELECT * FROM packages ORDER BY id DESC";
$packages = $pdo->query($packages_sql)->fetchAll(PDO::FETCH_ASSOC);

// ── Customer's bookings ──────────────────────────────────────
$mybookings_sql = "
    SELECT 
        b.id, 
        b.date, 
        b.price, 
        b.status, 
        b.package_name,
        b.package_id,
        b.custom_date,
        b.custom_duration,
        b.custom_travelers,
        b.special_requests
    FROM bookings b
    WHERE b.user_id = ?
    ORDER BY b.date DESC
";
$stmt = $pdo->prepare($mybookings_sql);
$stmt->execute([$customer_id]);
$mybookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
<title>My Dashboard – GlobeTrek Adventures</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --navy: #0a1628; --ocean: #0e3a6e; --sky: #1a6fb5; --azure: #2196f3;
    --aqua: #00b4d8; --white: #fff; --cream: #f8f4ef;
    --orange: #ff6b2b; --gold: #f4c430; --green: #2e7d32;
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
.sidebar-footer { margin-top: auto; padding: 16px 14px 0; border-top: 1px solid rgba(255,255,255,0.08); }
.customer-card { display: flex; align-items: center; gap: 10px; padding: 10px 12px; background: rgba(255,255,255,0.06); border-radius: 10px; }
.customer-av { width: 34px; height: 34px; border-radius: 50%; background: linear-gradient(135deg, var(--azure), var(--aqua)); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 0.9rem; }
.customer-name { color: #fff; font-size: 0.85rem; font-weight: 600; }
.customer-role { color: rgba(255,255,255,0.4); font-size: 0.7rem; text-transform: capitalize; }
.logout-link { margin-left: auto; color: rgba(255,255,255,0.35); font-size: 0.82rem; text-decoration: none; padding: 4px; transition: color 0.18s; }
.logout-link:hover { color: #fff; }

/* ── MAIN ────────────────────────────────────────────────── */
.main { flex: 1; display: flex; flex-direction: column; min-height: 100vh; overflow-x: hidden; }
.topbar { background: #fff; padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e8edf3; position: sticky; top: 0; z-index: 40; }
.topbar h1 { font-family: 'Playfair Display', serif; font-size: 1.2rem; color: var(--navy); }
.topbar p { font-size: 0.78rem; color: var(--text-light); margin-top: 1px; }
.topbar-actions { display: flex; align-items: center; gap: 10px; }
.tag-customer { background: #e3f2fd; color: #1565c0; font-size: 0.72rem; font-weight: 700; padding: 4px 12px; border-radius: 50px; }
.content { padding: 28px 32px; flex: 1; }

/* ── FLASH ────────────────────────────────────────────────── */
.flash { background: #e8f5e9; border: 1px solid #a5d6a7; color: #2e7d32; padding: 11px 18px; border-radius: 10px; font-size: 0.87rem; margin-bottom: 22px; }

/* ── SECTION CARD ─────────────────────────────────────────── */
.card { background: #fff; border-radius: 16px; padding: 22px 24px; box-shadow: 0 3px 16px rgba(10,22,40,0.07); margin-bottom: 24px; }
.card-head { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
.card-head h2 { font-family: 'Playfair Display', serif; font-size: 1.1rem; color: var(--navy); }

/* ── PACKAGE GRID ─────────────────────────────────────────── */
.packages-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 24px;
}
.package-card {
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 3px 16px rgba(10,22,40,0.07);
    transition: transform 0.2s, box-shadow 0.2s;
    border: 1px solid #eef2f7;
}
.package-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px rgba(10,22,40,0.12);
}
.package-img {
    width: 100%;
    height: 160px;
    object-fit: cover;
    background: #eef2f7;
}
.package-info {
    padding: 18px;
}
.package-name {
    font-family: 'Playfair Display', serif;
    font-size: 1.1rem;
    font-weight: 700;
    color: var(--navy);
    margin-bottom: 6px;
}
.package-dest {
    font-size: 0.75rem;
    color: var(--text-light);
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 4px;
}
.package-desc {
    font-size: 0.8rem;
    color: var(--text-mid);
    margin-bottom: 12px;
    line-height: 1.4;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.package-meta {
    display: flex;
    justify-content: space-between;
    align-items: baseline;
    margin-bottom: 14px;
    flex-wrap: wrap;
    gap: 6px;
}
.package-price {
    font-size: 1.2rem;
    font-weight: 700;
    color: var(--orange);
}
.package-duration {
    font-size: 0.72rem;
    background: #f0f4f9;
    padding: 3px 8px;
    border-radius: 50px;
    color: var(--text-mid);
}
.package-slots {
    font-size: 0.7rem;
    color: var(--green);
    font-weight: 600;
    margin-bottom: 12px;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 9px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.83rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.18s;
}
.btn-blue { background: var(--azure); color: #fff; }
.btn-blue:hover { background: var(--sky); }
.btn-orange { background: var(--orange); color: #fff; }
.btn-orange:hover { background: #e85a1b; }
.btn-red { background: #e74c3c; color: #fff; }
.btn-red:hover { background: #c0392b; }
.btn-purple { background: #8e44ad; color: #fff; }
.btn-purple:hover { background: #6c3483; }
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

/* ── CUSTOMIZE MODAL ───────────────────────────────────────── */
.modal {
    display: <?= $customize_data ? 'flex' : 'none' ?>;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}
.modal-content {
    background: #fff;
    border-radius: 20px;
    max-width: 500px;
    width: 90%;
    padding: 28px;
    position: relative;
}
.modal-content h2 { font-family: 'Playfair Display', serif; font-size: 1.3rem; margin-bottom: 12px; }
.modal-close {
    position: absolute;
    top: 16px;
    right: 20px;
    font-size: 24px;
    text-decoration: none;
    color: #999;
}
.modal-close:hover { color: #333; }

/* ── PROFILE FORM ─────────────────────────────────────────── */
.profile-form { max-width: 500px; }
.form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
.form-group label { font-size: 0.75rem; font-weight: 600; color: var(--text-mid); text-transform: uppercase; letter-spacing: 0.05em; }
.form-group input, .form-group textarea, .form-group select {
    padding: 10px 14px;
    border: 1.5px solid #e0e8f0;
    border-radius: 10px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.88rem;
    outline: none;
    transition: border-color 0.2s;
}
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { border-color: var(--azure); }
.form-group textarea { resize: vertical; min-height: 80px; }

.empty { text-align: center; padding: 48px 20px; color: var(--text-light); }
.empty-icon { font-size: 3rem; margin-bottom: 12px; }

@media (max-width: 900px) {
    .packages-grid { grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
    .sidebar { display: none; }
    .content { padding: 20px 16px; }
}
</style>
</head>
<body>

<!-- ═══════════════════ SIDEBAR ═══════════════════ -->
<aside class="sidebar">
    <a href="index.php" class="sidebar-logo">
        <span class="logo-txt">Globe<span>Trek</span></span>
    </a>

    <div class="nav-label">Travel Hub</div>
    <a href="?section=packages" class="nav-item <?= $section === 'packages' ? 'active' : '' ?>">
        <span class="nav-icon">🗺️</span> Browse Packages
    </a>
    <a href="?section=mybookings" class="nav-item <?= $section === 'mybookings' ? 'active' : '' ?>">
        <span class="nav-icon">📅</span> My Bookings
    </a>
    <a href="?section=profile" class="nav-item <?= $section === 'profile' ? 'active' : '' ?>">
        <span class="nav-icon">👤</span> My Profile
    </a>

    <div class="sidebar-footer">
        <div class="customer-card">
            <div class="customer-av"><?= strtoupper(substr($customer_name, 0, 1)) ?></div>
            <div>
                <div class="customer-name"><?= $customer_name ?></div>
                <div class="customer-role">Traveler</div>
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
            <h1>Traveler Dashboard</h1>
            <p>GlobeTrek Adventures · Explore the World</p>
        </div>
        <div class="topbar-actions">
            <span class="tag-customer">Traveler Access</span>
        </div>
    </div>

    <div class="content">

        <!-- Flash message -->
        <?php if ($flash): ?>
        <div class="flash"><?= $flash ?></div>
        <?php endif; ?>

        <!-- ═══════════════════ BROWSE PACKAGES SECTION ═══════════════════ -->
        <?php if ($section === 'packages'): ?>
        <div class="card">
            <div class="card-head">
                <h2>🗺️ Explore Our Adventure Packages</h2>
                <p style="font-size:0.8rem;color:#7a8a9a;"><?= count($packages) ?> packages available</p>
            </div>

            <?php if (empty($packages)): ?>
                <div class="empty">
                    <div class="empty-icon">🏝️</div>
                    <p>No packages available at the moment. Please check back later!</p>
                </div>
            <?php else: ?>
                <div class="packages-grid">
                    <?php foreach ($packages as $pkg): ?>
                    <div class="package-card">

    <?php if (!empty($pkg['image'])): ?>
        <img src="<?= htmlspecialchars($pkg['image'] ?? '') ?>" class="package-img"
             alt="<?= htmlspecialchars($pkg['name'] ?? 'Package') ?>">
    <?php else: ?>
        <div class="package-img"
             style="background: linear-gradient(135deg, #1a6fb5, #00b4d8); display:flex;align-items:center;justify-content:center;color:white;font-size:2rem;">
            🏔️
        </div>
    <?php endif; ?>

    <div class="package-info">

        <div class="package-name">
            <?= htmlspecialchars($pkg['name'] ?? 'Unnamed Package') ?>
        </div>

        <div class="package-dest">
            📍 <?= htmlspecialchars($pkg['destination'] ?? 'Sri Lanka') ?>
        </div>

        <div class="package-desc">
            <?= htmlspecialchars(substr($pkg['description'] ?? 'No description available', 0, 100)) ?>...
        </div>

        <div class="package-meta">
            <span class="package-price">
                LKR <?= number_format($pkg['price'] ?? 0, 2) ?>
            </span>

            <span class="package-duration">
                ⏱️ <?= isset($pkg['duration_days']) ? $pkg['duration_days'] : 0 ?> days
            </span>
        </div>

        

        <a href="booking.php?name=<?= urlencode($pkg['name']) ?>&price=<?= $pkg['price'] ?>" 
   class="btn btn-blue btn-full"
   style="display:flex;justify-content:center;align-items:center;text-decoration:none;
   <?= (isset($pkg['availability']) && $pkg['availability'] <= 0) ? 'pointer-events:none;opacity:0.5;' : '' ?>">
   
   <?= (isset($pkg['availability']) && $pkg['availability'] <= 0) ? 'Sold Out' : 'Book Now' ?>
</a>

    </div>
</div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ═══════════════════ MY BOOKINGS SECTION ═══════════════════ -->
        <?php elseif ($section === 'mybookings'): ?>
        <div class="card">
            <div class="card-head">
                <h2>📅 My Travel Bookings</h2>
            </div>

            <?php if (empty($mybookings)): ?>
                <div class="empty">
                    <div class="empty-icon">✈️</div>
                    <p>You haven't booked any packages yet.</p>
                    <a href="?section=packages" class="btn btn-blue" style="margin-top: 12px;">Browse Packages →</a>
                </div>
            <?php else: ?>
                <div class="tbl-wrap">
                    <table>
                        <thead>
                            <tr><th>Booking ID</th><th>Package</th><th>Date</th><th>Price</th><th>Status</th><th>Customizations</th><th>Actions</th></tr>
                        </thead>
                        <tbody>
                        <?php foreach ($mybookings as $b): ?>
                            <tr>
                                <td><strong>#<?= $b['id'] ?></strong></td>
                                <td><?= htmlspecialchars($b['package_name']) ?></td>
                                <td>
    <?= !empty($b['custom_date']) ? $b['custom_date'] : $b['date'] ?>
</td>

<td>
    LKR <?= number_format($b['price'], 2) ?>
</td>
                                <td><?= statusBadge($b['status']) ?></td>
                                <td>
    <?= !empty($b['custom_date']) ? 'Updated' : '—' ?>
</td>
                                <td>
                                    <?php if (strtolower(trim($b['status'])) === 'pending'): ?>
                                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                                        <a href="?section=mybookings&customize=<?= $b['id'] ?>" class="btn btn-purple btn-sm">Customize</a>
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this booking permanently? This action cannot be undone.');">
                                            <input type="hidden" name="action" value="delete_booking">
                                            <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                            <button type="submit" class="btn btn-red btn-sm">Delete</button>
                                        </form>
                                        
                                    </div>
                                    <?php else: ?>
                                        <span style="font-size:0.7rem;color:#999;">No actions</span>
                                    <?php endif; ?>
                                 </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Customization Modal -->
        <?php if ($customize_data): ?>
        <div class="modal" id="customizeModal" style="display:flex;">
            <div class="modal-content">
                <a href="?section=mybookings" class="modal-close">&times;</a>
                <h2>Customize Your Tour</h2>
                <p style="margin-bottom: 20px; font-size:0.85rem;">Customize details for <strong><?= htmlspecialchars($customize_data['package_name']) ?></strong></p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="customize_booking">
                        <input type="hidden" name="booking_id" value="<?= $customize_data['id'] ?>">
    
    <div class="form-group">
        <label>📅 Preferred Travel Date</label>
        <input type="date" name="custom_date" value="<?= htmlspecialchars($customize_data['custom_date'] ?? '') ?>">
        <small style="font-size:0.7rem;color:#7a8a9a;">Leave blank to keep original schedule</small>
    </div>

    <div class="form-group">
        <label>⏱️ Custom Duration (days)</label>
        <input type="number" name="custom_duration" min="1" max="30" placeholder="e.g., 7" value="<?= htmlspecialchars($customize_data['custom_duration'] ?? '') ?>">
        <small style="font-size:0.7rem;color:#7a8a9a;">Adjust trip length if needed</small>
    </div>

    <div class="form-group">
        <label>👥 Number of Travelers</label>
        <input type="number" name="custom_travelers" min="1" max="20" placeholder="e.g., 2" value="<?= htmlspecialchars($customize_data['custom_travelers'] ?? '') ?>">
    </div>

    <div class="form-group">
        <label>💬 Special Requests / Dietary / Accommodation</label>
        <textarea name="special_requests" placeholder="Any special requirements? We'll do our best to accommodate!"><?= htmlspecialchars($customize_data['special_requests'] ?? '') ?></textarea>
    </div>

    <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 20px;">
        <a href="?section=mybookings" class="btn btn-outline">Cancel</a>
        <button type="submit" class="btn btn-blue">Save Customizations</button>
    </div>
</form>
            </div>
        </div>
        <?php endif; ?>

        

        <!-- ═══════════════════ PROFILE SECTION ═══════════════════ -->
        <?php elseif ($section === 'profile'): ?>
        <div class="card">
            <div class="card-head">
                <h2>👤 My Profile</h2>
            </div>

            <form method="POST" class="profile-form">
                <input type="hidden" name="action" value="update_profile">

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?= $customer_name ?>" required>
                </div>

                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= $customer_email ?>" required>
                </div>

                <div class="form-group">
                    <label>Member Since</label>
                    <input type="text" value="<?= date('F j, Y') ?>" disabled style="background:#f7f9fc; color:#7a8a9a;">
                    
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-blue">Update Profile</button>
                    <a href="?section=mybookings" class="btn btn-outline" style="margin-left: 8px;">View My Trips</a>
                </div>
            </form>

            
        </div>
        <?php endif; ?>

    </div> <!-- /.content -->
</div> <!-- /.main -->

<!-- JavaScript for view customizations modal -->
<script>
    <script>
function escapeHtml(str) {
    if (!str) return '';

    return str.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    }).replace(/[\uD800-\uDBFF][\uDC00-\uDFFF]/g, function(c) {
        return c;
    });
}

// Close customize modal when clicking outside
window.onclick = function(event) {
    const customizeModal = document.getElementById('customizeModal');

    if (customizeModal && event.target === customizeModal) {
        window.location.href = '?section=mybookings';
    }
}
</script>

</body>
</html>