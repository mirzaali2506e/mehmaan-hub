# Mehmaan Hub - Rental Property Platform

A rental property listing platform built with HTML, CSS, JavaScript, PHP, and MySQL.

## Setup Instructions

### 1. Database Setup
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Click "Import"
3. Choose the file `database/mehmaan_hub.sql`
4. Click "Go" to import

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
