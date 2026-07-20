# Mehmaan Hub - Rental Property Platform

A rental property listing platform built with HTML, CSS, JavaScript, PHP, and MySQL.

## Setup Instructions

### 1. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "Import"
3. Choose the file `database/mehmaan_hub.sql`
4. Click "Go" to import
5. Also import `database/add_password_resets.sql` to enable the forgot password (OTP) feature

The database comes pre-loaded with:
- 3 property owners with properties
- 8 properties across Lahore and Rawalpindi
- Multiple images per property (stock photos from Pexels)
- Sample reviews

### 2. Configure Database (if needed)
Edit `includes/config.php` if your MySQL credentials differ:
- DB_HOST: localhost
- DB_USER: root
- DB_PASS: (your password, usually empty for XAMPP)
- DB_NAME: mehmaan_hub

### 3. Run the Project
Place the `php-project` folder in your XAMPP `htdocs` directory and access:
```
http://localhost/mhman-hb/php-project/
```

## Demo Accounts (password for all: owner123 / tenant123 / admin123)
- **Owner 1:** owner@mehmaanhub.com / owner123 (Ahmed Khan - 5 properties)
- **Owner 2:** bilal@mehmaanhub.com / owner123 (Bilal Raza - 3 properties)
- **Owner 3:** fatima@mehmaanhub.com / owner123 (Fatima Sheikh - 2 properties)
- **Tenant:** tenant@mehmaanhub.com / tenant123 (Sara Ali)
- **Admin:** admin@mehmaanhub.com / admin123

## Features

### For Tenants
- Browse and search properties with filters
- View property details with multiple images
- Book properties
- Add properties to wishlist
- Write reviews
- Manage bookings

### For Owners
- Add and manage property listings
- Upload multiple images per property
- View and manage booking requests
- Confirm/cancel bookings

### General
- Responsive design (mobile, tablet, desktop)
- User authentication (login/register)
- Role-based access (tenant, owner, admin)
- Contact form
- Forgot password with OTP verification (email or phone)

> Note: The forgot password flow sends OTP via email using PHPMailer. To enable real email delivery:
> 1. Run `composer install` in the project root to install PHPMailer
> 2. Edit `includes/config.php` and fill in the SMTP constants (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS)
> 3. For Gmail, use an App Password (not your account password) and set MAIL_HOST=smtp.gmail.com, MAIL_PORT=587
> If SMTP is not configured, the OTP is shown in the error message as a fallback for testing.
