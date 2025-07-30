<?php
require_once 'database.php';

// Authentication functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

// Validation functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    return preg_match('/^[\+]?[1-9][\d]{0,15}$/', $phone);
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Password functions
function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Flash message functions
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Email function (basic implementation)
function sendEmail($to, $subject, $message) {
    // This is a basic implementation
    // In production, you should use a proper email library like PHPMailer
    
    $headers = "From: " . SMTP_USER . "\r\n";
    $headers .= "Reply-To: " . SMTP_USER . "\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Format functions
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatTime($time) {
    return date('g:i A', strtotime($time));
}

function formatPrice($price) {
    return '$' . number_format($price, 2);
}

// Database helper functions
function getUserById($id) {
    $db = new Database();
    return $db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
}

function getFlightById($id) {
    $db = new Database();
    return $db->fetchOne("SELECT * FROM flights WHERE id = ?", [$id]);
}

function getBookingById($id) {
    $db = new Database();
    return $db->fetchOne("SELECT * FROM bookings WHERE id = ?", [$id]);
}

function getAvailableFlights() {
    $db = new Database();
    return $db->fetchAll("SELECT * FROM flights WHERE status = 'active' AND available_seats > 0 AND departure_date >= CURDATE() ORDER BY departure_date, departure_time");
}

function getUserBookings($userId) {
    $db = new Database();
    return $db->fetchAll("
        SELECT b.*, f.flight_number, f.from_location, f.to_location, f.departure_date, f.departure_time, f.price
        FROM bookings b 
        JOIN flights f ON b.flight_id = f.id 
        WHERE b.user_id = ? 
        ORDER BY b.created_at DESC
    ", [$userId]);
}

function getAllBookings() {
    $db = new Database();
    return $db->fetchAll("
        SELECT b.*, u.name as user_name, f.flight_number, f.from_location, f.to_location, f.departure_date, f.departure_time, f.price
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        JOIN flights f ON b.flight_id = f.id 
        ORDER BY b.created_at DESC
    ");
}

// Helper function to get passenger details from JSON
function getPassengerDetails($passenger_details_json) {
    $details = json_decode($passenger_details_json, true);
    if (!$details) {
        return [
            'name' => 'Unknown',
            'email' => 'Unknown',
            'phone' => ''
        ];
    }
    return $details;
}
?> 