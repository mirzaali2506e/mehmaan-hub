# Mehmaan Hub - Rental Property Platform

A rental property listing platform built with HTML, CSS, JavaScript, PHP, and MySQL.

## Setup Instructions

### 1. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "Import"
3. Choose the file `database/mehmaan_hub.sql`
4. Click "Go" to import
5. Also import `database/add_password_resets.sql` to enable the forgot password (OTP) feature
6. Import `database/add_security_tables.sql` to enable rate limiting and activity logging

The database comes pre-loaded with:
- 3 property owners with properties
- 8 properties across Lahore and Rawalpindi
- Multiple images per property (stock photos from Pexels)
- Sample reviews

### 2. Environment Configuration
1. Copy `.env.example` to `.env` in the project root
2. Edit `.env` and fill in your values:
   - Database credentials (DB_HOST, DB_USER, DB_PASS, DB_NAME)
   - Site URL (SITE_URL)
   - SMTP settings for password reset emails (MAIL_HOST, MAIL_USERNAME, MAIL_PASSWORD, etc.)

### 3. Install PHP Dependencies
Run this in the project root (requires Composer installed):
```
composer install
```
This installs:
- `phpmailer/phpmailer` — for sending real OTP emails via SMTP
- `vlucas/phpdotenv` — for loading environment variables from `.env`

If Composer is not available, the project still works — email sending is skipped with a logged error.

### 4. Configure SMTP (for password reset emails)
Edit `.env` and fill in the SMTP settings:
```
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
```
For Gmail, use an **App Password** (not your account password). Enable 2FA, then generate an App Password from your Google Account security settings.

### 5. Run the Project
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
- Forgot password with OTP verification via email (PHPMailer + SMTP)
- CSRF protection on all forms
- Rate limiting on login, forgot password, and OTP verification
- Security headers (CSP, X-Frame-Options, etc.)
- Secure file upload validation (MIME, extension whitelist, size limit)
- Activity logging for audit trail

## Security

- **CSRF tokens** on every POST form and AJAX request
- **Rate limiting**: 5 login attempts / 15 min, 3 reset requests / 15 min, 5 OTP attempts / 15 min
- **Session regeneration** after successful login (prevents session fixation)
- **Generic login errors** (no user enumeration)
- **OTP never displayed in UI** — sent only via email
- **Secure file uploads**: MIME validation, extension whitelist, 5MB limit, random filenames
- **Security headers**: X-Frame-Options, X-Content-Type-Options, CSP, Referrer-Policy
- **Password hashing** with `password_hash()` / bcrypt
- **OTP hashing** with `password_hash()` — stored as hash, never plaintext
- **OTP expiry** (10 minutes) and resend cooldown (60 seconds)
- **Prepared statements** throughout (SQL injection prevention)
