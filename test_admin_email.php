<?php
/**
 * Test Admin Email Constant
 * Verify that ADMIN_EMAIL is properly defined
 */

echo "=== Testing ADMIN_EMAIL Constant ===\n\n";

// Test 1: Check if ADMIN_EMAIL is defined after including main config
echo "1. Testing after including main config:\n";
require_once 'includes/config.php';
if (defined('ADMIN_EMAIL')) {
    echo "   ✅ ADMIN_EMAIL is defined: " . ADMIN_EMAIL . "\n";
} else {
    echo "   ❌ ADMIN_EMAIL is not defined\n";
}

// Test 2: Check if ADMIN_EMAIL is defined after including admin config
echo "\n2. Testing after including admin config:\n";
require_once 'admin/includes/config.php';
if (defined('ADMIN_EMAIL')) {
    echo "   ✅ ADMIN_EMAIL is defined: " . ADMIN_EMAIL . "\n";
} else {
    echo "   ❌ ADMIN_EMAIL is not defined\n";
}

// Test 3: Test the sendAdminEmail function
echo "\n3. Testing sendAdminEmail function:\n";
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'admin/includes/functions.php';

try {
    // This should not throw an error now
    $result = sendAdminEmail('test@example.com', 'Test Subject', 'Test Message');
    echo "   ✅ sendAdminEmail function executed without error\n";
    echo "   Result: " . ($result ? "Email sent" : "Email not sent") . "\n";
} catch (Exception $e) {
    echo "   ❌ Error in sendAdminEmail: " . $e->getMessage() . "\n";
}

// Test 4: Test the sendBookingNotification function
echo "\n4. Testing sendBookingNotification function:\n";
try {
    // This should not throw an error now
    $result = sendBookingNotification(1, 'confirmed');
    echo "   ✅ sendBookingNotification function executed without error\n";
    echo "   Result: " . ($result ? "Notification sent" : "Notification not sent") . "\n";
} catch (Exception $e) {
    echo "   ❌ Error in sendBookingNotification: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?> 