# Admin Management System - Flight Booking System

This document provides a comprehensive guide for managing admin users in the Flight Booking System.

## Table of Contents

1. [Overview](#overview)
2. [Installation](#installation)
3. [Admin Creation Methods](#admin-creation-methods)
4. [Security Features](#security-features)
5. [Usage Examples](#usage-examples)
6. [Troubleshooting](#troubleshooting)
7. [Best Practices](#best-practices)

## Overview

The Admin Management System provides multiple secure methods for creating and managing admin users in the Flight Booking System. It includes command-line tools, web interfaces, and comprehensive security features.

### Key Features

- **Multiple Creation Methods**: Command line, interactive, web interface
- **Secure Token System**: Cryptographically secure tokens for web registration
- **Password Validation**: Strong password requirements
- **Duplicate Prevention**: Check for existing usernames and emails
- **User Promotion**: Promote existing customers to admin
- **Activity Logging**: Track all admin-related activities

## Installation

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Flight Booking System database

### Setup Steps

1. **Ensure Database is Ready**
   ```bash
   # Import the complete database
   mysql -u root -p < complete_database.sql
   ```

2. **Verify File Structure**
   ```
   Flight Boking/
   ├── admin/
   │   ├── includes/
   │   │   ├── config.php
   │   │   ├── database.php
   │   │   └── functions.php
   │   ├── login.php
   │   ├── register.php
   │   └── index.php
   ├── create_admin_simple.php
   ├── setup_admin.php
   ├── generate_admin_token.php
   └── ADMIN_SETUP.md
   ```

3. **Test Database Connection**
   ```bash
   php create_admin_simple.php --help
   ```

## Admin Creation Methods

### 1. Command Line Tool (`create_admin_simple.php`)

The most flexible method for creating admin users.

#### Interactive Creation
```bash
php create_admin_simple.php --create
```

#### Command Line Creation
```bash
php create_admin_simple.php --create-admin --username admin --email admin@example.com --password SecurePass123 --full-name "System Administrator"
```

#### List Admin Users
```bash
php create_admin_simple.php --list
```

#### List All Users
```bash
php create_admin_simple.php --all-users
```

#### Promote User to Admin
```bash
php create_admin_simple.php --promote 1
```

#### Generate Registration Token
```bash
php create_admin_simple.php --token
```

### 2. Interactive Setup (`setup_admin.php`)

Menu-driven interface for admin management.

```bash
php setup_admin.php
```

**Menu Options:**
- Create New Admin User
- List All Admin Users
- List All Users (Customers)
- Promote User to Admin
- Generate Admin Registration Token
- Reset Admin Password
- Delete Admin User
- System Information

### 3. Web-Based Token Generator (`generate_admin_token.php`)

Generate secure tokens for web-based admin registration.

**Access:** `http://localhost/Flight%20Boking/generate_admin_token.php`

**Features:**
- Generate secure tokens
- Clean expired tokens
- Copy registration URLs
- Security best practices

### 4. Web-Based Registration (`admin/register.php`)

Secure admin registration using tokens.

**Access:** `http://localhost/Flight%20Boking/admin/register.php?token=<TOKEN>`

**Features:**
- Token validation
- Form validation
- Secure password hashing
- Duplicate prevention

## Security Features

### Password Security

- **Minimum Length**: 6 characters
- **Hashing**: Uses `password_hash()` with `PASSWORD_DEFAULT`
- **Verification**: Uses `password_verify()` for login

### Token Security

- **Generation**: Uses `random_bytes(32)` for cryptographically secure tokens
- **Expiration**: Tokens expire after 24 hours
- **Single Use**: Tokens are marked as used after registration
- **Storage**: Tokens stored in database with expiration tracking

### Input Validation

- **Username**: Minimum 3 characters, unique
- **Email**: Valid email format, unique
- **Password**: Minimum 6 characters
- **Full Name**: Minimum 2 characters

### Database Security

- **Prepared Statements**: All database queries use prepared statements
- **Input Sanitization**: All inputs are sanitized
- **SQL Injection Prevention**: Parameterized queries

## Usage Examples

### Initial System Setup

1. **Create First Admin**
   ```bash
   # Interactive method
   php setup_admin.php
   
   # Command line method
   php create_admin_simple.php --create-admin --username admin --email admin@company.com --password SecurePass123 --full-name "System Administrator"
   ```

2. **Verify Admin Creation**
   ```bash
   php create_admin_simple.php --list
   ```

3. **Access Admin Panel**
   - URL: `http://localhost/Flight%20Boking/admin/login.php`
   - Username: `admin`
   - Password: `SecurePass123`

### Adding Additional Admins

#### Method 1: Command Line
```bash
php create_admin_simple.php --create-admin --username manager --email manager@company.com --password ManagerPass456 --full-name "Manager User"
```

#### Method 2: Interactive
```bash
php setup_admin.php
# Choose option 1: Create New Admin User
```

#### Method 3: Web-Based (Recommended for non-technical users)
```bash
# Generate token
php create_admin_simple.php --token

# Share the registration URL with the new admin
# They can register through the web interface
```

### Promoting Existing Users

1. **List All Users**
   ```bash
   php create_admin_simple.php --all-users
   ```

2. **Promote User**
   ```bash
   php create_admin_simple.php --promote 5
   ```

### Managing Admin Users

#### List Admins
```bash
php create_admin_simple.php --list
```

#### Reset Password
```bash
php setup_admin.php
# Choose option 6: Reset Admin Password
```

#### Delete Admin
```bash
php setup_admin.php
# Choose option 7: Delete Admin User
```

## Troubleshooting

### Common Issues

#### 1. Database Connection Failed
**Error:** `Database connection failed: ...`

**Solution:**
- Check database credentials in `admin/includes/config.php`
- Ensure MySQL service is running
- Verify database exists

#### 2. Invalid Admin Credentials
**Error:** `Invalid admin credentials`

**Solution:**
- Use `fix_admin_password.php` to reset password
- Ensure password is properly hashed
- Check username/email spelling

#### 3. Token Expired
**Error:** `Invalid or expired registration token`

**Solution:**
- Generate new token: `php create_admin_simple.php --token`
- Tokens expire after 24 hours
- Clean expired tokens: Use web interface

#### 4. Duplicate Admin
**Error:** `Admin with this username or email already exists`

**Solution:**
- Use different username/email
- Check existing admins: `php create_admin_simple.php --list`
- Delete existing admin if needed

### Debug Tools

#### Password Reset Tool
```bash
# Access: http://localhost/Flight%20Boking/fix_admin_password.php
# Fixes plain text password issues
```

#### Database Check
```bash
php create_admin_simple.php --list
php create_admin_simple.php --all-users
```

## Best Practices

### Security Best Practices

1. **Strong Passwords**
   - Use at least 8 characters
   - Include uppercase, lowercase, numbers, symbols
   - Avoid common passwords

2. **Token Management**
   - Generate tokens only when needed
   - Share tokens through secure channels
   - Delete tokens immediately after use
   - Regularly clean expired tokens

3. **Access Control**
   - Limit admin access to trusted users
   - Monitor admin account creation
   - Regularly review admin permissions
   - Use unique credentials for each admin

4. **System Maintenance**
   - Keep PHP and MySQL updated
   - Regularly backup database
   - Monitor system logs
   - Test admin functionality regularly

### Operational Best Practices

1. **Documentation**
   - Keep admin credentials secure
   - Document admin creation procedures
   - Maintain user access logs
   - Update this documentation

2. **Backup and Recovery**
   - Regular database backups
   - Test recovery procedures
   - Store backups securely
   - Document recovery steps

3. **Monitoring**
   - Monitor admin login attempts
   - Track admin activities
   - Review system logs
   - Set up alerts for suspicious activity

## File Reference

### Core Files

| File | Purpose |
|------|---------|
| `create_admin_simple.php` | Command line admin management |
| `setup_admin.php` | Interactive admin setup |
| `generate_admin_token.php` | Web-based token generator |
| `admin/register.php` | Web-based admin registration |
| `admin/login.php` | Admin login page |
| `admin/includes/config.php` | Database configuration |
| `admin/includes/functions.php` | Helper functions |
| `admin/includes/database.php` | Database class |

### Database Tables

| Table | Purpose |
|-------|---------|
| `admins` | Admin user accounts |
| `users` | Customer accounts |
| `admin_tokens` | Registration tokens |
| `flights` | Flight information |
| `bookings` | Booking records |

### Security Files

| File | Purpose |
|------|---------|
| `fix_admin_password.php` | Password reset tool |
| `ADMIN_SETUP.md` | This documentation |

## Support

For issues or questions:

1. Check this documentation
2. Review troubleshooting section
3. Check system logs
4. Verify database connectivity
5. Test with debug tools

## Version History

- **v1.0**: Initial admin management system
- **v1.1**: Added token-based registration
- **v1.2**: Enhanced security features
- **v1.3**: Added comprehensive documentation

---

**Last Updated:** <?php echo date('Y-m-d H:i:s'); ?>
**System:** Flight Booking System Admin Management 