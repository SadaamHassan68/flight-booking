<?php
// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');
if (!defined('DB_NAME')) define('DB_NAME', 'flight_booking');

// Application configuration
if (!defined('SITE_NAME')) define('SITE_NAME', 'Flight Booking System');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/Flight%20Boking');
if (!defined('ADMIN_URL')) define('ADMIN_URL', SITE_URL . '/admin');

// Email configuration (for notifications)
if (!defined('SMTP_HOST')) define('SMTP_HOST', 'smtp.gmail.com');
if (!defined('SMTP_PORT')) define('SMTP_PORT', 587);
if (!defined('SMTP_USER')) define('SMTP_USER', 'your-email@gmail.com');
if (!defined('SMTP_PASS')) define('SMTP_PASS', 'your-app-password');

// Admin-specific email configuration
if (!defined('ADMIN_EMAIL')) define('ADMIN_EMAIL', 'admin@flightbooking.com');

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('UTC');
?> 