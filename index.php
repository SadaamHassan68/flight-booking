<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$flights = getAvailableFlights();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-plane"></i> <?php echo SITE_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="flights.php">Flights</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <?php if (isAdmin()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/">Admin Panel</a>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="customer/bookings.php">My Bookings</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user"></i> <?php echo $_SESSION['username']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="customer/profile.php">Profile</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold text-white mb-4">
                        Book Your Perfect Flight
                    </h1>
                    <p class="lead text-white mb-4">
                        Discover amazing destinations and book your next adventure with our comprehensive flight booking system.
                    </p>
                    <a href="flights.php" class="btn btn-light btn-lg">
                        <i class="fas fa-search"></i> Search Flights
                    </a>
                </div>
                <div class="col-lg-6">
                    <img src="assets/images/airplane.svg" alt="Airplane" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <!-- Available Flights Section -->
    <section class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-5">Available Flights</h2>
                </div>
            </div>
            
            <?php if (empty($flights)): ?>
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle"></i> No flights available at the moment.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach (array_slice($flights, 0, 6) as $flight): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100 flight-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="card-title mb-0"><?php echo htmlspecialchars($flight['flight_number']); ?></h5>
                                        <span class="badge bg-success"><?php echo formatPrice($flight['price']); ?></span>
                                    </div>
                                    
                                    <div class="flight-route mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="route-point">
                                                <i class="fas fa-plane-departure text-primary"></i>
                                                <div class="route-info">
                                                    <strong><?php echo htmlspecialchars($flight['from_location']); ?></strong>
                                                    <small class="text-muted"><?php echo formatTime($flight['departure_time']); ?></small>
                                                </div>
                                            </div>
                                            <div class="route-line">
                                                <i class="fas fa-plane text-muted"></i>
                                            </div>
                                            <div class="route-point">
                                                <i class="fas fa-plane-arrival text-success"></i>
                                                <div class="route-info">
                                                    <strong><?php echo htmlspecialchars($flight['to_location']); ?></strong>
                                                    <small class="text-muted"><?php echo formatTime($flight['arrival_time']); ?></small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flight-details">
                                        <p class="mb-2">
                                            <i class="fas fa-calendar text-muted"></i>
                                            <span class="ms-2"><?php echo formatDate($flight['departure_date']); ?></span>
                                        </p>
                                        <p class="mb-3">
                                            <i class="fas fa-chair text-muted"></i>
                                            <span class="ms-2"><?php echo $flight['available_seats']; ?> seats available</span>
                                        </p>
                                    </div>
                                    
                                    <?php if (isLoggedIn() && !isAdmin()): ?>
                                        <a href="customer/book_flight.php?flight_id=<?php echo $flight['id']; ?>" class="btn btn-primary w-100">
                                            <i class="fas fa-ticket-alt"></i> Book Now
                                        </a>
                                    <?php elseif (!isLoggedIn()): ?>
                                        <a href="login.php" class="btn btn-outline-primary w-100">
                                            <i class="fas fa-sign-in-alt"></i> Login to Book
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="row">
                    <div class="col-12 text-center">
                        <a href="flights.php" class="btn btn-outline-primary btn-lg">
                            View All Flights
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="text-center mb-5">Why Choose Us?</h2>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                        <h4>Secure Booking</h4>
                        <p class="text-muted">Your personal information and payment details are protected with industry-standard security.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <i class="fas fa-clock fa-3x text-primary mb-3"></i>
                        <h4>24/7 Support</h4>
                        <p class="text-muted">Our customer support team is available round the clock to assist you with any queries.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="text-center">
                        <i class="fas fa-tags fa-3x text-primary mb-3"></i>
                        <h4>Best Prices</h4>
                        <p class="text-muted">We offer competitive prices and regular deals to ensure you get the best value for your money.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><?php echo SITE_NAME; ?></h5>
                    <p class="mb-0">Your trusted partner for flight bookings.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 