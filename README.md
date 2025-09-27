# L9 Fitness Gym - Capstone Project

# 🏋️ L9 Fitness Gym - Complete Fitness Center Management System

A comprehensive fitness center management system with an AI-powered chatbot assistant, membership management, class booking, and admin dashboard.

## 🚀 Quick Start

### Prerequisites
- **XAMPP** (or Apache + PHP + MySQL)
- **PHP 7.4+**
- **MySQL 5.7+**

### 🔧 Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/uniqstha/Capstone.git
   cd Capstone
   ```

2. **Place in web server directory:**
   - Copy the entire project to your `htdocs` folder (XAMPP) or web server root
   - Example: `C:\xampp\htdocs\Capstone\`

3. **Start your web server:**
   - Start Apache and MySQL in XAMPP Control Panel

4. **Run setup (Choose one):**
   
   **Windows:**
   ```cmd
   setup.bat
   ```
   
   **Linux/Mac:**
   ```bash
   chmod +x setup.sh
   ./setup.sh
   ```
   
   **Manual setup:**
   ```bash
   php setup_db.php
   ```

5. **Access your website:**
   ```
   http://localhost/Capstone/public/
   ```

### 🎯 First Time Setup

1. **Create Admin User:** Visit `http://localhost/Capstone/public/create_admin.php`
2. **Test Registration:** Go to registration page and create a regular user
3. **Try the Chatbot:** Click the 💬 button in the bottom-right corner

## ✨ Features

### 🏃‍♂️ User Features
- **User Registration & Login** - Secure authentication system
- **Membership Plans** - Multiple tiers with different pricing
- **Class Booking** - Reserve spots in fitness classes
- **Profile Management** - Update personal information and view stats
- **Payment Integration** - Checkout system for memberships

### 🤖 AI Chatbot Assistant
- **Intelligent Responses** - Pattern-matching for gym-related queries
- **24/7 Availability** - Always ready to help users
- **Context Aware** - Understands gym hours, pricing, classes, and more
- **Fallback System** - Works even without external AI APIs

### 👑 Admin Features
- **Dashboard** - Complete overview of gym operations
- **User Management** - View and manage all registered users
- **Settings** - Configure gym settings and preferences
- **Analytics** - Track usage and performance metrics

### 📱 Modern Interface
- **Responsive Design** - Works on desktop, tablet, and mobile
- **Bootstrap 5** - Modern, professional styling
- **Dynamic Effects** - Smooth animations and transitions
- **Dark Theme** - Sleek fitness-focused design

## 🗄️ Database Structure

The system includes 31+ tables covering:
- User management and roles
- Membership plans and payments
- Class scheduling and bookings
- Chatbot conversations and analytics
- Equipment and trainer management

## 🔒 Security Features

- **CSRF Protection** - Secure forms with token validation
- **Password Hashing** - Bcrypt encryption for user passwords
- **Session Management** - Secure user sessions
- **Input Validation** - Protection against SQL injection and XSS

## 🌐 Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+

## 📞 Support

For issues or questions:
- Check the setup scripts output for common problems
- Ensure Apache and MySQL are running
- Verify PHP version compatibility
- Check database connection settings in `config/db.php`

## 🏆 Project Status

**Status:** ✅ Complete and Production Ready
**Version:** 1.0.0
**Last Updated:** September 2025

## 🚀 Quick Setup

1. **Start XAMPP** (Apache + MySQL)
2. **Database Setup:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Run `database/schema.sql` to create tables
   - Run `database/seed.sql` to insert sample data
3. **Copy project** to `htdocs/capsronenewedits/`
4. **Visit:** http://localhost/capsronenewedits/public/

## 🔐 Demo Login Credentials

All demo users use password: **Password123**

- **Admin:** admin@l9.local
- **Member:** tina@l9.local  
- **Member:** mia@l9.local
- **Trainer:** mike@l9.local
- **Staff:** sarah@l9.local

## 🏋️ Features

- **Member Management:** Registration, login, profile management
- **Class Booking:** Schedule classes, manage capacity, waitlists
- **Membership Plans:** Monthly, quarterly, yearly memberships
- **Admin Dashboard:** User management, analytics, system settings
- **Modern UI:** Responsive design with Beast Mode theme
- **Database:** Complete gym management schema with relationships

## 🛠️ Tech Stack

- **Backend:** PHP 8, MySQL, PDO
- **Frontend:** Bootstrap 5, Custom CSS, JavaScript
- **Security:** CSRF protection, password hashing, input validation
- **Architecture:** MVC pattern with helpers and layouts

## 📁 Project Structure

```
capsronenewedits/
├── app/
│   ├── helpers/           # Auth, CSRF, validation helpers
│   └── views/layouts/     # Header, footer templates
├── config/                # Database and app configuration
├── database/             # SQL schema and seed data
├── public/               # Web accessible files
│   ├── assets/css/       # Stylesheets
│   ├── assets/js/        # JavaScript files
│   └── *.php            # Application pages
└── README.md
```

## 🎯 Capstone Requirements Met

- ✅ User Authentication & Authorization
- ✅ CRUD Operations (Users, Classes, Memberships)
- ✅ Database Design & Relationships
- ✅ Responsive Web Design
- ✅ Input Validation & Security
- ✅ Session Management
- ✅ Modern UI/UX Design
- ✅ Admin Panel Functionality

---
**L9 Fitness Gym** - Unleash the Beast! 💪
