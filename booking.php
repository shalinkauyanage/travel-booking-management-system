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
    $_SESSION['flash'] = "Please login to book a package.";
    header("Location: login.html");
    exit();
}

// allow ONLY customer
if ($_SESSION['role'] != 'customer') {
    $_SESSION['flash'] = "Access denied. Customer only.";
    header("Location: login.html");
    exit();
}

$customer_id = $_SESSION['user_id'];
$customer_name = $_SESSION['name'] ?? 'Traveler';
$customer_email = $_SESSION['email'] ?? '';

// ── Get package details from URL (for display) ───────────────
$package_name = isset($_GET['name']) ? trim($_GET['name']) : '';
$package_price = isset($_GET['price']) ? (float)$_GET['price'] : 0;
$package_id = null;

if ($package_name) {
    // Fetch full package details from database
    $stmt = $pdo->prepare("SELECT id, name, price, description, destination, duration_days, availability FROM packages WHERE name = ?");
    $stmt->execute([$package_name]);
    $package = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($package) {
        $package_id = $package['id'];
        $package_price = $package['price'];
        $package_availability = $package['availability'];
    }
}

// ── Process POST request (form submission) ───────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'process_booking') {

    // Get form data
    $package_id = (int) ($_POST['package_id'] ?? 0);
    $package_name = trim($_POST['package_name'] ?? '');
    $travel_date = $_POST['date'] ?? '';
    $travelers = (int) ($_POST['people'] ?? 1);
    $total_price = (float) ($_POST['price'] ?? 0);
    $email = trim($_POST['email'] ?? '');
    $special_requests = trim($_POST['special_requests'] ?? '');

    // Validation
    $errors = [];

    if (empty($package_name) || $package_id <= 0) {
        $errors[] = "Invalid package selection.";
    }

    if (empty($travel_date)) {
        $errors[] = "Travel date is required.";
    }

    if ($travelers < 1) {
        $errors[] = "At least 1 traveler is required.";
    }

    if ($travelers > 20) {
        $errors[] = "Maximum 20 travelers per booking.";
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email address is required.";
    }

    if ($total_price <= 0) {
        $errors[] = "Invalid price calculation.";
    }

    // If validation passes, proceed with booking
    if (empty($errors)) {
        try {
            // Verify package exists and get current availability
            $stmt = $pdo->prepare("SELECT id, price, availability FROM packages WHERE id = ? AND name = ?");
            $stmt->execute([$package_id, $package_name]);
            $package = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$package) {
                $_SESSION['flash'] = "Package not found.";
                header("Location: dashboard.php?section=packages");
                exit();
            }

            $base_price = $package['price'];
            $availability = $package['availability'];

            // Check availability
            if ($availability < $travelers) {
                $_SESSION['flash'] = "Sorry, only $availability spot(s) available for this package.";
                header("Location: dashboard.php?section=packages");
                exit();
            }

            // Verify total price matches (prevent tampering)
            $calculated_total = $base_price * $travelers;
            if (abs($calculated_total - $total_price) > 0.01) {
                $total_price = $calculated_total;
            }

            // Insert booking with customization fields
            $insert = $pdo->prepare("
                INSERT INTO bookings (user_id, package_id, package_name, price, status, date, custom_date, custom_duration, custom_travelers, special_requests)
                VALUES (?, ?, ?, ?, 'pending', ?, ?, NULL, ?, ?)
            ");
            
            $insert->execute([
                $customer_id,
                $package_id,
                $package_name,
                $total_price,
                $travel_date,
                $travel_date,      
                $travelers,        
                $special_requests
            ]);

            // Decrease availability by number of travelers booked
            $update = $pdo->prepare("UPDATE packages SET availability = availability - ? WHERE id = ?");
            $update->execute([$travelers, $package_id]);

            // Success message
            $_SESSION['flash'] = "Package '$package_name' booked successfully for $travelers traveler(s) on $travel_date! Status: Pending.";

            header("Location: dashboard.php?section=mybookings");
            exit();

        } catch (PDOException $e) {
            $_SESSION['flash'] = "Database error: " . $e->getMessage();
            header("Location: dashboard.php?section=packages");
            exit();
        }
    } else {
        // Validation errors
        $_SESSION['flash'] = "❌ " . implode(" ", $errors);
        header("Location: dashboard.php?section=packages");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Your Trip - GlobeTrek Adventures</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'DM Sans', sans-serif;
    background: linear-gradient(135deg, #0a1628 0%, #0e3a6e 100%);
    min-height: 100vh;
}

nav {
    padding: 20px 40px;
    background: rgba(10, 22, 40, 0.95);
    backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.logo {
    font-family: 'Playfair Display', serif;
    font-size: 24px;
    font-weight: 700;
    color: #fff;
    text-decoration: none;
}

.logo span {
    color: #00b4d8;
}

.back-link {
    color: rgba(255,255,255,0.7);
    text-decoration: none;
    font-size: 14px;
    transition: color 0.2s;
}

.back-link:hover {
    color: #fff;
}

.container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: calc(100vh - 80px);
    padding: 40px 20px;
}

.booking-card {
    background: #fff;
    border-radius: 24px;
    padding: 32px;
    max-width: 550px;
    width: 100%;
    box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
}

.booking-card h2 {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    color: #0a1628;
    margin-bottom: 8px;
}

.subtitle {
    color: #7a8a9a;
    font-size: 14px;
    margin-bottom: 24px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f0f4f9;
}

.package-summary {
    background: linear-gradient(135deg, #f8f4ef 0%, #fff 100%);
    border-radius: 16px;
    padding: 20px;
    margin-bottom: 24px;
    border: 1px solid #f0e6dc;
}

.package-name {
    font-family: 'Playfair Display', serif;
    font-size: 20px;
    font-weight: 700;
    color: #0a1628;
    margin-bottom: 12px;
}

.package-details {
    display: flex;
    justify-content: space-between;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #e0d6cc;
}

.package-detail-item {
    text-align: center;
}

.package-detail-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #7a8a9a;
    margin-bottom: 4px;
}

.package-detail-value {
    font-weight: 600;
    color: #0a1628;
}

.price-highlight {
    font-size: 24px;
    font-weight: 700;
    color: #ff6b2b;
}

.availability {
    display: inline-block;
    background: #e8f5e9;
    color: #2e7d32;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 10px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #7a8a9a;
    margin-bottom: 8px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border: 1.5px solid #e0e8f0;
    border-radius: 12px;
    font-family: 'DM Sans', sans-serif;
    font-size: 15px;
    transition: all 0.2s;
}

.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #2196f3;
    box-shadow: 0 0 0 3px rgba(33,150,243,0.1);
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.total-section {
    background: #f7f9fc;
    border-radius: 16px;
    padding: 20px;
    margin: 24px 0;
    text-align: center;
}

.total-section h3 {
    font-size: 13px;
    color: #7a8a9a;
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.total-price {
    font-size: 36px;
    font-weight: 700;
    color: #ff6b2b;
}

.btn-book {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #2196f3, #00b4d8);
    border: none;
    border-radius: 40px;
    color: white;
    font-family: 'DM Sans', sans-serif;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s, box-shadow 0.2s;
}

.btn-book:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(33,150,243,0.4);
}

.btn-book:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.flash-message {
    background: #e8f5e9;
    border: 1px solid #a5d6a7;
    color: #2e7d32;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    margin-bottom: 20px;
}

.flash-message.error {
    background: #fce4ec;
    border-color: #f8bbd0;
    color: #c62828;
}

.warning-message {
    background: #fff8e1;
    border: 1px solid #ffe082;
    color: #f57f17;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
    margin-bottom: 20px;
}

@media (max-width: 600px) {
    nav {
        padding: 15px 20px;
    }
    
    .booking-card {
        padding: 24px;
    }
    
    .booking-card h2 {
        font-size: 24px;
    }
    
    .total-price {
        font-size: 28px;
    }
}
</style>
</head>
<body>

<nav>
    <a href="dashboard.php?section=packages" class="logo">Globe<span>Trek</span></a>
    <a href="dashboard.php?section=packages" class="back-link">← Back to Packages</a>
</nav>

<div class="container">
    <div class="booking-card">
        <h2>Confirm Your Journey</h2>
        <div class="subtitle">Complete your booking to secure your adventure</div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'no_package'): ?>
        <div class="flash-message error">
            ❌ No package selected. Please choose a package first.
        </div>
        <?php endif; ?>

        <?php if (!$package_name || !$package_id): ?>
        <div class="warning-message">
            ⚠️ No package selected. <a href="dashboard.php?section=packages" style="color: #2196f3;">Browse available packages</a>
        </div>
        <?php else: ?>
        
        <div class="package-summary">
            <div class="package-name"><?= htmlspecialchars($package['name'] ?? $package_name) ?></div>
            <div class="package-details">
                <div class="package-detail-item">
                    <div class="package-detail-label">📍 Destination</div>
                    <div class="package-detail-value"><?= htmlspecialchars($package['destination'] ?? 'Sri Lanka') ?></div>
                </div>
                <div class="package-detail-item">
                    <div class="package-detail-label">⏱️ Duration</div>
                    <div class="package-detail-value"><?= htmlspecialchars($package['duration_days'] ?? 'N/A') ?> days</div>
                </div>
                <div class="package-detail-item">
                    <div class="package-detail-label">💰 Per Person</div>
                    <div class="package-detail-value price-highlight">LKR <?= number_format($package_price, 2) ?></div>
                </div>
            </div>
            <?php if (isset($package_availability)): ?>
            <div class="availability">✓ <?= $package_availability ?> spots available</div>
            <?php endif; ?>
        </div>

        <form method="POST" id="bookingForm">
            <input type="hidden" name="action" value="process_booking">
            <input type="hidden" name="package_id" value="<?= $package_id ?>">
            <input type="hidden" name="package_name" value="<?= htmlspecialchars($package_name) ?>">
            <input type="hidden" id="priceInput" name="price">

            <div class="form-group">
                <label>📧 Email Address</label>
                <input type="email" name="email" id="email" value="<?= htmlspecialchars($customer_email) ?>" placeholder="your@email.com" required>
            </div>

            <div class="form-group">
                <label>👥 Number of Travelers</label>
                <input type="number" id="people" name="people" min="1" max="<?= $package_availability ?? 20 ?>" value="1" required>
                <?php if (isset($package_availability) && $package_availability <= 5): ?>
                <small style="font-size: 11px; color: #f57f17;">⚠️ Only <?= $package_availability ?> spots remaining!</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>📅 Travel Date</label>
                <input type="date" name="date" id="date" required>
            </div>

            

            <div class="total-section">
                <h3>Total Amount</h3>
                <div class="total-price">LKR <span id="totalPrice"><?= number_format($package_price, 2) ?></span></div>
                <small style="color: #7a8a9a;">* Includes all taxes and fees</small>
            </div>

            <button type="submit" class="btn-book">
    Confirm Booking
</button>
        </form>
        
        <?php endif; ?>
    </div>
</div>

<script>
// Get package price from PHP
const packagePrice = <?= $package_price ?>;
const maxAvailability = <?= $package_availability ?? 20 ?>;

// Set minimum date to today
const today = new Date();
today.setHours(0, 0, 0, 0);
const tomorrow = new Date(today);
tomorrow.setDate(tomorrow.getDate() + 1);

const dateInput = document.getElementById('date');
dateInput.min = tomorrow.toISOString().split('T')[0];
dateInput.value = tomorrow.toISOString().split('T')[0];

// Update total price based on traveler count
function updateTotal() {
    let people = parseInt(document.getElementById('people').value) || 1;
    
    // Validate against max availability
    if (people > maxAvailability) {
        document.getElementById('people').value = maxAvailability;
        people = maxAvailability;
        showWarning(`Only ${maxAvailability} spot(s) available for this package.`);
    }
    
    let total = packagePrice * people;
    document.getElementById('totalPrice').innerText = total.toLocaleString();
    document.getElementById('priceInput').value = total;
}

// Show warning message
function showWarning(message) {
    const warningDiv = document.createElement('div');
    warningDiv.className = 'warning-message';
    warningDiv.innerHTML = `⚠️ ${message}`;
    const form = document.getElementById('bookingForm');
    const existingWarning = document.querySelector('.warning-message');
    if (existingWarning) existingWarning.remove();
    form.parentNode.insertBefore(warningDiv, form);
    setTimeout(() => {
        if (warningDiv.parentNode) warningDiv.remove();
    }, 3000);
}

// Event listeners
const peopleInput = document.getElementById('people');
if (peopleInput) {
    peopleInput.addEventListener('input', updateTotal);
}

// Initial calculation
updateTotal();

// Form validation before submit
document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
    const email = document.getElementById('email').value.trim();
    const date = document.getElementById('date').value;
    const people = parseInt(document.getElementById('people').value);
    
    if (!email) {
        e.preventDefault();
        alert('Please enter your email address.');
        return false;
    }
    
    if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
        e.preventDefault();
        alert('Please enter a valid email address.');
        return false;
    }
    
    if (!date) {
        e.preventDefault();
        alert('Please select a travel date.');
        return false;
    }
    
    if (people < 1) {
        e.preventDefault();
        alert('Please enter at least 1 traveler.');
        return false;
    }
    
    if (people > maxAvailability) {
        e.preventDefault();
        alert(`Sorry, only ${maxAvailability} spot(s) available for this package.`);
        return false;
    }
    
    // Confirm booking
    const total = parseFloat(document.getElementById('priceInput').value);
    return confirm(`Confirm booking for ${people} traveler(s) totaling LKR ${total.toLocaleString()}?`);
});
</script>

</body>
</html>