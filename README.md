# L9 Fitness Gym - Capstone Project

A modern gym management system built with PHP 8, MySQL, and Bootstrap 5.

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
