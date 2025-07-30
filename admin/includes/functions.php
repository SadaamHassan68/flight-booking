<?php
// Handle different include paths depending on where this file is called from
if (file_exists('../includes/database.php')) {
    require_once '../includes/database.php';
    require_once '../includes/functions.php';
} else {
    require_once 'includes/database.php';
    require_once 'includes/functions.php';
}

// Admin-specific authentication functions
function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function getAdminById($id) {
    $db = new Database();
    return $db->fetchOne("SELECT * FROM admins WHERE id = ?", [$id]);
}

// Token validation functions (admin-specific)
function validateAdminToken($token) {
    $db = new Database();
    
    // Create tokens table if it doesn't exist
    $db->query("CREATE TABLE IF NOT EXISTS admin_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        token VARCHAR(64) NOT NULL UNIQUE,
        expires_at TIMESTAMP NOT NULL,
        used TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_token (token),
        INDEX idx_expires (expires_at)
    )");
    
    $token_data = $db->fetchOne("
        SELECT * FROM admin_tokens 
        WHERE token = ? AND expires_at > NOW() AND used = 0
    ", [$token]);
    
    return $token_data !== false;
}

function markAdminTokenUsed($token) {
    $db = new Database();
    return $db->update('admin_tokens', ['used' => 1], 'token = :token', ['token' => $token]);
}

function cleanExpiredTokens() {
    $db = new Database();
    return $db->query("DELETE FROM admin_tokens WHERE expires_at < NOW() OR used = 1");
}

// Admin-specific data functions

function getAllFlights() {
    $db = new Database();
    return $db->fetchAll("SELECT * FROM flights ORDER BY departure_date DESC");
}



function getAllUsers() {
    $db = new Database();
    return $db->fetchAll("SELECT * FROM users ORDER BY created_at DESC");
}

function getBookingStats() {
    $db = new Database();
    return $db->fetchOne("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status IN ('confirmed', 'completed') THEN total_amount ELSE 0 END) as total_revenue
        FROM bookings
    ");
}

function getFlightStats() {
    $db = new Database();
    return $db->fetchOne("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
            SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
            SUM(available_seats) as total_available_seats
        FROM flights
    ");
}

function getRecentBookings($limit = 10) {
    $db = new Database();
    $limit = (int)$limit; // Cast to integer to prevent SQL injection and type issues
    return $db->fetchAll("
        SELECT b.*, u.name as user_name, f.flight_number, f.from_location, f.to_location
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN flights f ON b.flight_id = f.id 
        ORDER BY b.created_at DESC 
        LIMIT $limit
    ");
}

function getPendingBookings() {
    $db = new Database();
    return $db->fetchAll("
        SELECT b.*, u.name as user_name, f.flight_number, f.from_location, f.to_location
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN flights f ON b.flight_id = f.id 
        WHERE b.status = 'pending'
        ORDER BY b.created_at ASC
    ");
}

// Email functions (admin-specific)
function sendAdminEmail($to, $subject, $message) {
    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

function sendBookingNotification($booking_id, $status) {
    $db = new Database();
    
    $booking = $db->fetchOne("
        SELECT b.*, u.name as user_name, u.email as user_email, 
               f.flight_number, f.from_location, f.to_location, f.departure_date, f.departure_time
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN flights f ON b.flight_id = f.id 
        WHERE b.id = ?
    ", [$booking_id]);
    
    if (!$booking) {
        return false;
    }
    
    $status_messages = [
        'confirmed' => 'Your booking has been confirmed!',
        'cancelled' => 'Your booking has been cancelled.',
        'completed' => 'Your flight has been completed.',
        'deleted' => 'Your booking has been deleted.'
    ];
    
    $subject = "Booking Update - " . SITE_NAME;
    $message = "
        <h2>Booking Update</h2>
        <p>Dear " . htmlspecialchars($booking['user_name']) . ",</p>
        <p>" . $status_messages[$status] . "</p>
        <h3>Booking Details:</h3>
        <ul>
            <li><strong>Flight:</strong> " . htmlspecialchars($booking['flight_number']) . "</li>
            <li><strong>Route:</strong> " . htmlspecialchars($booking['from_location']) . " → " . htmlspecialchars($booking['to_location']) . "</li>
            <li><strong>Date:</strong> " . formatDate($booking['departure_date']) . "</li>
            <li><strong>Time:</strong> " . formatTime($booking['departure_time']) . "</li>
            <li><strong>Status:</strong> " . ucfirst($status) . "</li>
        </ul>
        <p>Thank you for choosing " . SITE_NAME . "!</p>
    ";
    
    return sendAdminEmail($booking['user_email'], $subject, $message);
}

// Admin-specific UI helper functions
function getStatusBadge($status) {
    $badges = [
        'pending' => '<span class="badge bg-warning">Pending</span>',
        'confirmed' => '<span class="badge bg-info">Confirmed</span>',
        'cancelled' => '<span class="badge bg-danger">Cancelled</span>',
        'completed' => '<span class="badge bg-success">Completed</span>'
    ];
    
    return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
}

// Pagination helper (admin-specific)
function paginate($total_records, $records_per_page, $current_page, $url_pattern) {
    $total_pages = ceil($total_records / $records_per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $html = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center">';
    
    // Previous button
    if ($current_page > 1) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page - 1) . '">Previous</a></li>';
    }
    
    // Page numbers
    for ($i = max(1, $current_page - 2); $i <= min($total_pages, $current_page + 2); $i++) {
        $active = $i == $current_page ? ' active' : '';
        $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . sprintf($url_pattern, $i) . '">' . $i . '</a></li>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $html .= '<li class="page-item"><a class="page-link" href="' . sprintf($url_pattern, $current_page + 1) . '">Next</a></li>';
    }
    
    $html .= '</ul></nav>';
    
    return $html;
}
?> 