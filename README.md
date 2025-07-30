# Flight Booking System

A complete Flight Booking System built with PHP and MySQL, featuring both customer and admin functionalities.

## ✈️ Features

### Customer Features
- **User Registration & Login**: Secure user authentication system
- **Flight Search**: Search flights by source, destination, and date
- **Flight Booking**: Book flights with passenger details
- **Booking Management**: View and manage personal bookings
- **Responsive Design**: Modern, mobile-friendly interface

### Admin Features
- **Admin Dashboard**: Overview of system statistics
- **Flight Management**: Add, edit, and delete flights
- **Booking Management**: View all bookings and update status
- **Customer Management**: View customer information
- **Real-time Updates**: Instant status updates and notifications

## 🗃️ Database Structure

The system uses MySQL with the following tables:

- **users**: Customer and admin user accounts
- **flights**: Flight information and availability
- **bookings**: Customer flight bookings

## 🛠️ Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, Bootstrap 5
- **Icons**: Font Awesome 6
- **Server**: Apache/Nginx (XAMPP/WAMP recommended)

## 📁 File Structure

```
Flight Booking/
├── admin/                 # Admin panel files
│   ├── index.php         # Admin dashboard
│   ├── flights.php       # Flight management
│   ├── bookings.php      # Booking management
│   └── customers.php     # Customer management
├── customer/             # Customer area files
│   ├── book_flight.php   # Flight booking page
│   ├── bookings.php      # Customer bookings
│   └── profile.php       # Customer profile
├── includes/             # Core system files
│   ├── config.php        # Configuration settings
│   ├── database.php      # Database connection class
│   └── functions.php     # Utility functions
├── assets/               # Static assets
│   ├── css/             # Stylesheets
│   └── images/          # Images and icons
├── index.php            # Main landing page
├── login.php            # Login page
├── register.php         # Registration page
├── flights.php          # Flight listing page
├── logout.php           # Logout functionality
├── database.sql         # Database schema
└── README.md            # This file
```

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- XAMPP/WAMP/MAMP (recommended for local development)

### Setup Instructions

1. **Clone/Download the Project**
   ```bash
   # Place the project in your web server directory
   # For XAMPP: C:\xampp\htdocs\Flight Booking
   # For WAMP: C:\wamp\www\Flight Booking
   ```

2. **Database Setup**
   - Open phpMyAdmin or your MySQL client
   - Create a new database named `flight_booking`
   - Import the `database.sql` file to create tables and sample data

3. **Configuration**
   - Open `includes/config.php`
   - Update database connection settings:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'flight_booking');
     ```
   - Update site URL if needed:
     ```php
     define('SITE_URL', 'http://localhost/Flight%20Boking');
     ```

4. **Email Configuration (Optional)**
   - Update SMTP settings in `includes/config.php` for email notifications
   - Uncomment email sending code in booking functions

5. **Access the System**
   - Open your browser and navigate to the project URL
   - Default admin credentials:
     - Username: `admin`
     - Password: `password`

## 👥 User Roles

### Customer
- Register and login
- Search and book flights
- View booking history
- Cancel bookings (pending status only)

### Admin
- Access admin dashboard
- Manage flights (add, edit, delete)
- View all bookings
- Update booking status (confirm, cancel, complete)
- View customer information

## 🔧 Configuration Options

### Database Settings
Edit `includes/config.php` to modify:
- Database connection parameters
- Site name and URL
- Email settings

### Email Notifications
To enable email notifications:
1. Configure SMTP settings in `includes/config.php`
2. Uncomment email sending code in booking functions
3. Ensure your server supports mail() function or use PHPMailer

### Security Features
- Password hashing using PHP's built-in `password_hash()`
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- Session-based authentication

## 🎨 Customization

### Styling
- Modify `assets/css/style.css` for custom styling
- Bootstrap 5 classes are used for responsive design
- Font Awesome icons can be replaced or customized

### Functionality
- Add new features by extending the existing classes
- Modify database schema in `database.sql`
- Add new admin features in the `admin/` directory

## 📱 Responsive Design

The system is fully responsive and works on:
- Desktop computers
- Tablets
- Mobile phones
- All modern browsers

## 🔒 Security Considerations

- All user inputs are sanitized
- SQL queries use prepared statements
- Passwords are hashed using secure algorithms
- Session management prevents unauthorized access
- Admin areas are protected with role-based access

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `includes/config.php`
   - Ensure MySQL service is running
   - Verify database name exists

2. **Page Not Found (404)**
   - Check file permissions
   - Verify .htaccess configuration
   - Ensure mod_rewrite is enabled (if using URL rewriting)

3. **Session Issues**
   - Check PHP session configuration
   - Verify session storage permissions
   - Clear browser cookies if needed

4. **Email Not Working**
   - Check SMTP settings
   - Verify server mail configuration
   - Test with a simple mail() function

## 📞 Support

For support or questions:
1. Check the troubleshooting section above
2. Review the code comments for implementation details
3. Ensure all prerequisites are met
4. Verify database and server configurations

## 📄 License

This project is open source and available under the MIT License.

## 🔄 Updates

To update the system:
1. Backup your database and files
2. Replace files with new versions
3. Run any database migration scripts
4. Test all functionality

---

**Note**: This is a demonstration system. For production use, implement additional security measures, error logging, and proper backup procedures. 