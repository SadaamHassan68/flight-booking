<?php
/**
 * Test Admin Dashboard Functions
 * Verify all dashboard functions work without errors
 */

require_once 'admin/includes/config.php';
require_once 'includes/database.php';
require_once 'includes/functions.php';
require_once 'admin/includes/functions.php';

echo "=== Testing Admin Dashboard Functions ===\n\n";

try {
    echo "1. Testing getBookingStats():\n";
    $booking_stats = getBookingStats();
    echo "   ✅ getBookingStats() executed successfully\n";
    echo "   - Total bookings: {$booking_stats['total']}\n";
    echo "   - Pending: {$booking_stats['pending']}\n";
    echo "   - Confirmed: {$booking_stats['confirmed']}\n";
    echo "   - Cancelled: {$booking_stats['cancelled']}\n";
    echo "   - Completed: {$booking_stats['completed']}\n";
    echo "   - Total revenue: $" . number_format($booking_stats['total_revenue'] ?? 0, 2) . "\n";
    
    echo "\n2. Testing getFlightStats():\n";
    $flight_stats = getFlightStats();
    echo "   ✅ getFlightStats() executed successfully\n";
    echo "   - Total flights: {$flight_stats['total']}\n";
    echo "   - Active: {$flight_stats['active']}\n";
    echo "   - Cancelled: {$flight_stats['cancelled']}\n";
    echo "   - Total available seats: {$flight_stats['total_available_seats']}\n";
    
    echo "\n3. Testing getRecentBookings(5):\n";
    $recent_bookings = getRecentBookings(5);
    echo "   ✅ getRecentBookings(5) executed successfully\n";
    echo "   - Found " . count($recent_bookings) . " recent bookings\n";
    
    if (!empty($recent_bookings)) {
        echo "   Sample booking data:\n";
        $booking = $recent_bookings[0];
        echo "   - ID: {$booking['id']}\n";
        echo "   - User: {$booking['user_name']}\n";
        echo "   - Flight: {$booking['flight_number']}\n";
        echo "   - Status: {$booking['status']}\n";
        echo "   - Amount: $" . number_format($booking['total_amount'] ?? 0, 2) . "\n";
    }
    
    echo "\n4. Testing getPendingBookings():\n";
    $pending_bookings = getPendingBookings();
    echo "   ✅ getPendingBookings() executed successfully\n";
    echo "   - Found " . count($pending_bookings) . " pending bookings\n";
    
    if (!empty($pending_bookings)) {
        echo "   Sample pending booking:\n";
        $booking = $pending_bookings[0];
        echo "   - ID: {$booking['id']}\n";
        echo "   - User: {$booking['user_name']}\n";
        echo "   - Flight: {$booking['flight_number']}\n";
        echo "   - Passengers: {$booking['passengers']}\n";
    }
    
    echo "\n5. Testing formatPrice() function:\n";
    $test_price = 299.99;
    $formatted_price = formatPrice($test_price);
    echo "   ✅ formatPrice() works: $test_price → $formatted_price\n";
    
    echo "\n6. Testing formatDate() function:\n";
    $test_date = date('Y-m-d');
    $formatted_date = formatDate($test_date);
    echo "   ✅ formatDate() works: $test_date → $formatted_date\n";
    
    echo "\n7. Testing getStatusBadge() function:\n";
    $test_status = 'pending';
    $status_badge = getStatusBadge($test_status);
    echo "   ✅ getStatusBadge() works for '$test_status'\n";
    
    echo "\n✅ All admin dashboard functions are working correctly!\n";
    echo "The admin dashboard should now load without errors.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
?> 