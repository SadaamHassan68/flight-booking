<?php
// Admin Sidebar - Side navigation for admin panel
?>
<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh;">
    <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <i class="fas fa-plane fa-2x text-white mb-2"></i>
            <h5 class="text-white"><?php echo SITE_NAME; ?></h5>
            <small class="text-white-50">Admin Panel</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>" href="index.php" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'flights.php' ? 'active' : ''; ?>" href="flights.php" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-plane me-2"></i>
                    Manage Flights
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'add_flight.php' ? 'active' : ''; ?>" href="add_flight.php" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-plus-circle me-2"></i>
                    Add New Flight
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'bookings.php' ? 'active' : ''; ?>" href="bookings.php" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-ticket-alt me-2"></i>
                    Manage Bookings
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white-50">
            <span>Quick Actions</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="bookings.php?status=pending" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-clock me-2"></i>
                    Pending Bookings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="flights.php?status=active" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-check-circle me-2"></i>
                    Active Flights
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../index.php" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-eye me-2"></i>
                    View Public Site
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-white-50">
            <span>Account</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link" href="profile.php" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-user-cog me-2"></i>
                    Profile Settings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php" style="color: rgba(255, 255, 255, 0.8); padding: 12px 20px; margin: 2px 0; border-radius: 8px; transition: all 0.3s ease;">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
.sidebar .nav-link:hover,
.sidebar .nav-link.active {
    color: white !important;
    background: rgba(255, 255, 255, 0.1);
    transform: translateX(5px);
}
</style> 