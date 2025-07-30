-- =====================================================
-- FLIGHT BOOKING SYSTEM - COMPLETE DATABASE
-- =====================================================

-- Create database
CREATE DATABASE IF NOT EXISTS flight_booking;
USE flight_booking;

-- =====================================================
-- ADMINS TABLE
-- =====================================================
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- USERS TABLE (CUSTOMERS)
-- =====================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- FLIGHTS TABLE
-- =====================================================
CREATE TABLE flights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    flight_number VARCHAR(50) NOT NULL UNIQUE,
    from_location VARCHAR(100) NOT NULL,
    to_location VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    total_seats INT NOT NULL,
    available_seats INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'inactive', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =====================================================
-- BOOKINGS TABLE
-- =====================================================
CREATE TABLE bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    flight_id INT NOT NULL,
    passengers INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    passenger_details TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (flight_id) REFERENCES flights(id) ON DELETE CASCADE
);

-- =====================================================
-- SAMPLE DATA
-- =====================================================

-- Insert default admin user (password: password)
INSERT INTO admins (username, password, email, full_name) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@flightbooking.com', 'System Administrator');

-- Insert sample users (password: password)
INSERT INTO users (name, email, password, phone) VALUES
('John Doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567890'),
('Jane Smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567891'),
('Mike Johnson', 'mike@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567892'),
('Sarah Wilson', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567893'),
('David Brown', 'david@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+1234567894');

-- Insert sample flights
INSERT INTO flights (flight_number, from_location, to_location, departure_date, departure_time, arrival_time, total_seats, available_seats, price) VALUES
('FL001', 'New York', 'Los Angeles', '2024-02-15', '10:00:00', '13:30:00', 150, 148, 299.99),
('FL002', 'Chicago', 'Miami', '2024-02-16', '14:30:00', '17:45:00', 120, 119, 199.99),
('FL003', 'Boston', 'Seattle', '2024-02-17', '08:15:00', '11:30:00', 180, 177, 349.99),
('FL004', 'Denver', 'Phoenix', '2024-02-18', '16:00:00', '18:30:00', 100, 100, 159.99),
('FL005', 'Atlanta', 'Las Vegas', '2024-02-19', '12:45:00', '15:15:00', 200, 200, 279.99),
('FL006', 'San Francisco', 'Dallas', '2024-02-20', '09:30:00', '14:15:00', 160, 160, 229.99),
('FL007', 'Washington DC', 'Orlando', '2024-02-21', '11:00:00', '13:45:00', 140, 140, 189.99),
('FL008', 'Philadelphia', 'Houston', '2024-02-22', '15:45:00', '18:30:00', 130, 130, 209.99),
('FL009', 'Detroit', 'San Diego', '2024-02-23', '07:20:00', '10:45:00', 170, 170, 259.99),
('FL010', 'Minneapolis', 'Portland', '2024-02-24', '13:15:00', '15:50:00', 110, 110, 179.99);

-- Insert sample bookings
INSERT INTO bookings (user_id, flight_id, passengers, total_amount, status, passenger_details) VALUES
(1, 1, 2, 599.98, 'confirmed', 'John Doe, Jane Doe'),
(2, 2, 1, 199.99, 'pending', 'Jane Smith'),
(3, 3, 3, 1049.97, 'confirmed', 'Mike Johnson, Sarah Johnson, Tom Johnson'),
(4, 1, 1, 299.99, 'cancelled', 'Sarah Wilson'),
(5, 4, 2, 319.98, 'completed', 'David Brown, Lisa Brown'),
(1, 5, 1, 279.99, 'pending', 'John Doe'),
(2, 6, 2, 459.98, 'confirmed', 'Jane Smith, Bob Smith'),
(3, 7, 1, 189.99, 'pending', 'Mike Johnson'),
(4, 8, 3, 629.97, 'confirmed', 'Sarah Wilson, Mark Wilson, Emma Wilson'),
(5, 9, 1, 259.99, 'completed', 'David Brown');

-- =====================================================
-- INDEXES FOR BETTER PERFORMANCE
-- =====================================================
CREATE INDEX idx_flights_departure_date ON flights(departure_date);
CREATE INDEX idx_flights_status ON flights(status);
CREATE INDEX idx_bookings_status ON bookings(status);
CREATE INDEX idx_bookings_user_id ON bookings(user_id);
CREATE INDEX idx_bookings_flight_id ON bookings(flight_id);
CREATE INDEX idx_users_email ON users(email);

-- =====================================================
-- VIEWS FOR ANALYTICS
-- =====================================================

-- View for flight statistics
CREATE VIEW flight_stats AS
SELECT 
    COUNT(*) as total_flights,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_flights,
    SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_flights,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_flights,
    SUM(total_seats) as total_seats,
    SUM(available_seats) as available_seats,
    SUM(total_seats - available_seats) as booked_seats
FROM flights;

-- View for booking statistics
CREATE VIEW booking_stats AS
SELECT 
    COUNT(*) as total_bookings,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
    SUM(CASE WHEN status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
    SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings,
    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_bookings,
    SUM(total_amount) as total_revenue
FROM bookings;

-- View for recent bookings with user and flight details
CREATE VIEW recent_bookings AS
SELECT 
    b.id,
    b.status,
    b.passengers,
    b.total_amount,
    b.created_at,
    u.name as customer_name,
    u.email as customer_email,
    f.flight_number,
    f.from_location,
    f.to_location,
    f.departure_date,
    f.departure_time
FROM bookings b
JOIN users u ON b.user_id = u.id
JOIN flights f ON b.flight_id = f.id
ORDER BY b.created_at DESC;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

-- Procedure to update flight available seats when booking is made
DELIMITER //
CREATE PROCEDURE UpdateFlightSeats(IN flight_id INT, IN passengers INT)
BEGIN
    UPDATE flights 
    SET available_seats = available_seats - passengers 
    WHERE id = flight_id;
END //
DELIMITER ;

-- Procedure to get booking details with user and flight info
DELIMITER //
CREATE PROCEDURE GetBookingDetails(IN booking_id INT)
BEGIN
    SELECT 
        b.*,
        u.name as customer_name,
        u.email as customer_email,
        u.phone as customer_phone,
        f.flight_number,
        f.from_location,
        f.to_location,
        f.departure_date,
        f.departure_time,
        f.arrival_time,
        f.price
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN flights f ON b.flight_id = f.id
    WHERE b.id = booking_id;
END //
DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

-- Trigger to update flight available seats when booking status changes
DELIMITER //
CREATE TRIGGER booking_status_update
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF OLD.status != NEW.status THEN
        IF NEW.status = 'cancelled' AND OLD.status != 'cancelled' THEN
            -- Increase available seats when booking is cancelled
            UPDATE flights 
            SET available_seats = available_seats + OLD.passengers 
            WHERE id = OLD.flight_id;
        ELSEIF OLD.status = 'cancelled' AND NEW.status != 'cancelled' THEN
            -- Decrease available seats when booking is confirmed
            UPDATE flights 
            SET available_seats = available_seats - NEW.passengers 
            WHERE id = NEW.flight_id;
        END IF;
    END IF;
END //
DELIMITER ;

-- =====================================================
-- SAMPLE QUERIES FOR TESTING
-- =====================================================

-- Get all active flights
-- SELECT * FROM flights WHERE status = 'active' AND departure_date >= CURDATE() ORDER BY departure_date, departure_time;

-- Get pending bookings
-- SELECT * FROM bookings WHERE status = 'pending' ORDER BY created_at DESC;

-- Get booking statistics
-- SELECT * FROM booking_stats;

-- Get flight statistics
-- SELECT * FROM flight_stats;

-- Get recent bookings
-- SELECT * FROM recent_bookings LIMIT 10;

-- =====================================================
-- DATABASE COMPLETE
-- ===================================================== 