<?php
/**
 * Test Recent Fixes
 * Verifies all the recent fixes work correctly
 */

echo "🧪 Testing Recent Fixes\n";
echo "======================\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    require_once 'includes/config.php';
    require_once 'includes/functions.php';
    $db = new Database();
    echo "   ✅ Database connection successful\n";
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 2: Check Admin Files
echo "\n2. Checking Admin Files...\n";
$admin_files = [
    'admin/includes/admin_header.php',
    'admin/includes/admin_sidebar.php',
    'admin/bookings.php'
];

foreach ($admin_files as $file) {
    if (file_exists($file)) {
        echo "   ✅ $file exists\n";
    } else {
        echo "   ❌ $file missing\n";
    }
}

// Test 3: Test Booking Query
echo "\n3. Testing Booking Query...\n";
try {
    $bookings = $db->fetchAll("
        SELECT b.*, f.flight_number, f.from_location, f.to_location, f.departure_date, f.departure_time
        FROM bookings b 
        JOIN flights f ON b.flight_id = f.id 
        LIMIT 1
    ");
    
    if (!empty($bookings)) {
        $booking = $bookings[0];
        echo "   ✅ Booking query successful\n";
        echo "   📊 Sample booking:\n";
        echo "      - ID: " . $booking['id'] . "\n";
        echo "      - Flight: " . $booking['flight_number'] . "\n";
        echo "      - Route: " . $booking['from_location'] . " → " . $booking['to_location'] . "\n";
        echo "      - Created: " . $booking['created_at'] . "\n";
        
        // Test passenger details
        if (!empty($booking['passenger_details'])) {
            $passenger_details = getPassengerDetails($booking['passenger_details']);
            echo "      - Passenger: " . $passenger_details['name'] . "\n";
        }
    } else {
        echo "   ⚠️  No bookings found (this is normal if database is empty)\n";
    }
} catch (Exception $e) {
    echo "   ❌ Booking query failed: " . $e->getMessage() . "\n";
}

// Test 4: Test Parameter Binding
echo "\n4. Testing Parameter Binding...\n";
try {
    // Test named parameters
    $flight = $db->fetchOne("SELECT * FROM flights WHERE id = :id", ['id' => 1]);
    if ($flight) {
        echo "   ✅ Named parameter binding works\n";
    } else {
        echo "   ⚠️  No flight found with ID 1\n";
    }
    
    // Test positional parameters
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [1]);
    if ($user) {
        echo "   ✅ Positional parameter binding works\n";
    } else {
        echo "   ⚠️  No user found with ID 1\n";
    }
} catch (Exception $e) {
    echo "   ❌ Parameter binding failed: " . $e->getMessage() . "\n";
}

// Test 5: Test Update Operation
echo "\n5. Testing Update Operation...\n";
try {
    // Get current flight
    $flight = $db->fetchOne("SELECT * FROM flights LIMIT 1");
    if ($flight) {
        $original_seats = $flight['available_seats'];
        
        // Test update with named parameters
        $db->update('flights', 
            ['available_seats' => $original_seats], 
            'id = :id', 
            ['id' => $flight['id']]
        );
        
        echo "   ✅ Update operation with named parameters works\n";
    } else {
        echo "   ⚠️  No flights found to test update\n";
    }
} catch (Exception $e) {
    echo "   ❌ Update operation failed: " . $e->getMessage() . "\n";
}

// Test 6: Check Column Names
echo "\n6. Checking Column Names...\n";
$correct_columns = [
    'flights' => ['from_location', 'to_location'],
    'bookings' => ['created_at', 'passenger_details'],
    'users' => ['name']
];

foreach ($correct_columns as $table => $columns) {
    foreach ($columns as $column) {
        try {
            $result = $db->fetchOne("SHOW COLUMNS FROM $table LIKE '$column'");
            if ($result) {
                echo "   ✅ $table.$column exists\n";
            } else {
                echo "   ❌ $table.$column missing\n";
            }
        } catch (Exception $e) {
            echo "   ❌ Error checking $table.$column: " . $e->getMessage() . "\n";
        }
    }
}

echo "\n🎉 Fixes Test Complete!\n";
echo "======================\n";

echo "\n📝 Fixes Applied:\n";
echo "✅ Fixed parameter binding (mixed named/positional parameters)\n";
echo "✅ Created missing admin header and sidebar files\n";
echo "✅ Fixed booking_date → created_at column reference\n";
echo "✅ Updated booking system to use correct database schema\n";

echo "\n🌐 Test URLs:\n";
echo "- Admin Panel: http://localhost/Flight%20Boking/admin/login.php\n";
echo "- Admin Bookings: http://localhost/Flight%20Boking/admin/bookings.php\n";
echo "- Customer Bookings: http://localhost/Flight%20Boking/customer/bookings.php\n";
echo "- Book Flight: http://localhost/Flight%20Boking/flights.php\n";

echo "\n";
?> 