<?php
/**
 * Test SQL Fix
 * Verify that the getRecentBookings function works without SQL errors
 */

require_once 'admin/includes/config.php';
require_once 'includes/database.php';
require_once 'admin/includes/functions.php';

echo "=== Testing SQL Fix ===\n\n";

try {
    echo "1. Testing getRecentBookings function:\n";
    $recent_bookings = getRecentBookings(5);
    echo "   ✅ getRecentBookings(5) executed successfully\n";
    echo "   Found " . count($recent_bookings) . " recent bookings\n";
    
    if (!empty($recent_bookings)) {
        echo "   Sample booking:\n";
        $booking = $recent_bookings[0];
        echo "   - User: {$booking['user_name']}\n";
        echo "   - Flight: {$booking['flight_number']}\n";
        echo "   - Route: {$booking['from_location']} → {$booking['to_location']}\n";
    }
    
    echo "\n2. Testing getRecentBookings with default limit:\n";
    $recent_bookings_default = getRecentBookings();
    echo "   ✅ getRecentBookings() executed successfully\n";
    echo "   Found " . count($recent_bookings_default) . " recent bookings (default limit)\n";
    
    echo "\n3. Testing getPendingBookings function:\n";
    $pending_bookings = getPendingBookings();
    echo "   ✅ getPendingBookings() executed successfully\n";
    echo "   Found " . count($pending_bookings) . " pending bookings\n";
    
    echo "\n4. Testing getBookingStats function:\n";
    $booking_stats = getBookingStats();
    echo "   ✅ getBookingStats() executed successfully\n";
    echo "   Total bookings: {$booking_stats['total']}\n";
    echo "   Pending: {$booking_stats['pending']}\n";
    echo "   Confirmed: {$booking_stats['confirmed']}\n";
    
    echo "\n5. Testing getFlightStats function:\n";
    $flight_stats = getFlightStats();
    echo "   ✅ getFlightStats() executed successfully\n";
    echo "   Total flights: {$flight_stats['total']}\n";
    echo "   Active: {$flight_stats['active']}\n";
    echo "   Cancelled: {$flight_stats['cancelled']}\n";
    
    echo "\n✅ All SQL functions are working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?> 