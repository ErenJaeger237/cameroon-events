# 🇨🇲 CameroonEvents - Online Event Booking System

A beautiful, African-themed event booking system built for the Cameroon market with PHP, MySQL, and Bootstrap.

## 🌟 Features

- **African Theme**: Beautiful Cameroon flag colors and cultural design
- **Event Management**: Create, edit, and manage events
- **User Registration & Authentication**: Secure user accounts
- **Booking System**: Easy ticket booking with FCFA pricing
- **QR Code Tickets**: Digital tickets with QR codes for verification
- **Admin Dashboard**: Complete admin panel for management
- **Responsive Design**: Works on all devices
- **Local Currency**: FCFA pricing throughout

## 🚀 Railway Deployment Guide

### Step 1: Create GitHub Repository

1. Create a new repository on GitHub
2. Upload all files from `OnlineEventBookingSystem_FULL` folder
3. Make sure to include:
   - `railway.json`
   - `nixpacks.toml`
   - `init_database.php`
   - All PHP files and assets

### Step 2: Deploy to Railway

1. Go to [railway.app](https://railway.app)
2. Sign up with your GitHub account
3. Click "Deploy from GitHub repo"
4. Select your CameroonEvents repository
5. Railway will automatically detect PHP and start deployment

### Step 3: Add MySQL Database

1. In your Railway project dashboard
2. Click "Add Service" → "Database" → "MySQL"
3. Railway will create a MySQL instance and set environment variables

### Step 4: Initialize Database

1. Once deployed, visit: `https://your-app.railway.app/init_database.php`
2. This will create all tables and sample data
3. Note the admin credentials displayed

### Step 5: Test Your Application

1. Visit your Railway app URL
2. Test user registration and login
3. Try booking an event
4. Access admin dashboard with provided credentials

## 🔧 Environment Variables

Railway automatically sets these for MySQL:
- `MYSQLHOST`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`
- `MYSQLPORT`

## 📱 Default Accounts

### Admin Account
- **Email**: admin@cameroonevents.cm
- **Password**: admin123

### Sample User Account
- **Email**: john@example.com
- **Password**: user123

## 🎨 African Theme Features

- **Cameroon Flag Colors**: Green (#007A3D), Red (#CE1126), Yellow (#FECB00)
- **Cultural Icons**: African drum icons and patterns
- **Local Currency**: FCFA pricing throughout
- **Warm Design**: African sunset gradients and earth tones

## 📁 Project Structure

```
CameroonEvents/
├── admin/              # Admin dashboard and management
├── assets/             # CSS, JS, and images
├── auth/               # Login, registration, logout
├── cart/               # Shopping cart functionality
├── events/             # Event listing and details
├── includes/           # Database and utility functions
├── user/               # User dashboard and history
├── index.php           # Homepage
├── verify.php          # QR code verification
├── railway.json        # Railway configuration
├── nixpacks.toml       # Build configuration
└── init_database.php   # Database setup script
```

## 🛠️ Local Development

1. Set up XAMPP/WAMP with PHP 8.0+
2. Create MySQL database named `event_booking`
3. Import `database.sql` or run `init_database.php`
4. Update database credentials in `includes/db.php`
5. Access via `http://localhost/OnlineEventBookingSystem_FULL`

## 🔒 Security Features

- Password hashing with PHP's `password_hash()`
- SQL injection prevention with prepared statements
- Session management for authentication
- Input sanitization and validation
- QR code verification system

## 📞 Support

For issues or questions about deployment:
1. Check Railway logs in the dashboard
2. Ensure all files are uploaded to GitHub
3. Verify MySQL service is running
4. Run the database initialization script

## 🎯 Post-Deployment Checklist

- [ ] Database initialized successfully
- [ ] Admin login working
- [ ] User registration working
- [ ] Event booking functional
- [ ] QR code generation working
- [ ] PDF ticket download working
- [ ] African theme displaying correctly

---

**Built with ❤️ for Cameroon** 🇨🇲
