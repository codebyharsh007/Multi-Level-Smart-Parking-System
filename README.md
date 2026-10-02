# Multi-Level Smart Parking Management System

A web-based **Multi-Level Smart Parking Management System** developed using Core PHP, MySQL, JavaScript, AJAX, HTML and CSS.

## Project Overview

The system helps manage multiple parking floors, parking slots, vehicle entry and exit, receipts, staff, pricing, revenue and parking reports from a centralized dashboard.

## Key Features

- Multi-level floor management
- Dynamic parking slot management
- Vehicle entry and exit
- Parking fee calculation
- Entry and exit receipts
- Email receipt functionality
- Revenue analytics
- Parking reports
- Staff management
- Vehicle search
- Payment tracking
- Admin authentication
- AJAX-based dynamic operations

## Technology Stack

| Technology | Purpose |
|---|---|
| HTML5 | User Interface |
| CSS3 | Styling |
| JavaScript | Client-side functionality |
| AJAX | Dynamic operations |
| PHP | Backend development |
| MySQL | Database |
| PHPMailer | Email functionality |
| XAMPP | Local development |

## Project Structure

```text
msp/
├── admin/
├── ajax/
├── assets/
│   ├── css/
│   ├── js/
│   └── images/
├── config/
│   ├── db.example.php
│   └── session.php
├── database/
│   └── msp_demo.sql
├── partials/
├── PHPMailer/
├── receipts/
├── staff/
├── dashboard.php
├── index.php
├── login.php
├── logout.php
├── reports.php
├── search.php
├── vehicle_entry.php
├── mail_config.example.php
├── .gitignore
└── README.md
```

# How to Run Locally

## Requirements

- XAMPP
- PHP 8.x
- MySQL
- Apache
- Web Browser

## Setup

### 1. Clone or Download

Clone the repository from GitHub or download it as a ZIP file.

### 2. Copy the Project

Copy the project folder to:

```text
C:\xampp\htdocs\msp
```

### 3. Start XAMPP

Open XAMPP Control Panel and start:

```text
Apache
MySQL
```

### 4. Create Database

Open:

```text
http://localhost/phpmyadmin/
```

Create a database named:

```text
msp
```

### 5. Import Demo Database

Select the `msp` database, click **Import**, select:

```text
database/msp_demo.sql
```

and click **Go**.

### 6. Configure Database

Copy:

```text
config/db.example.php
```

and rename the copy to:

```text
config/db.php
```

For a default XAMPP installation:

```php
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'msp';
$DB_PORT = 3306;
```

Update these values if your local MySQL configuration is different.

### 7. Configure Email (Optional)

Copy:

```text
mail_config.example.php
```

and rename it to:

```text
mail_config.php
```

Add your local SMTP configuration.

**Never upload a Gmail password or Gmail App Password to GitHub.**

### 8. Open the Application

```text
http://localhost/msp/
```

# Demo Login

### Admin

```text
Username: Demo Admin
Password: DemoAdmin@123
```

### Staff

```text
Username: Demo Staff
Password: DemoStaff@123
```

These credentials are for local demo/testing only.

# Main Workflow

```text
Login
  ↓
Dashboard
  ↓
Floor Management
  ↓
Parking Slots
  ↓
Vehicle Entry
  ↓
Slot Allocation
  ↓
Vehicle Parking
  ↓
Vehicle Exit
  ↓
Fee Calculation
  ↓
Receipt Generation
  ↓
Revenue & Reports
```

# Multi-Level Parking

The system supports multiple parking floors with configurable:

- Floor capacity
- Vehicle type
- Parking slots
- Floor status
- Slot availability

# Dashboard & Analytics

The dashboard provides parking information such as:

- Total parking capacity
- Available slots
- Occupied slots
- Vehicle statistics
- Revenue information
- Parking activity
- Floor-wise revenue
- Vehicle-wise revenue
- Date-wise revenue

# Receipts

The system supports:

- Entry receipts
- Exit receipts
- Printable receipts
- Email receipts through PHPMailer

# Staff Management

The system supports:

- Staff creation
- Staff information
- Staff status
- Staff roles

# Security & Privacy

The GitHub repository is intended to contain only demo-safe data.

Do **not** upload:

- Real database passwords
- Gmail App Passwords
- SMTP credentials
- API keys
- Real customer information
- Real staff personal information
- Real vehicle records
- Private files

The repository uses:

```text
database/msp_demo.sql
```

for sanitized demo data.

Local configuration files such as:

```text
config/db.php
mail_config.php
```

are excluded using `.gitignore`.

# Local Testing

After installation, test:

- Login
- Dashboard
- Floor Setup
- Parking Slots
- Vehicle Entry
- Vehicle Exit
- Receipt Generation
- Pricing
- Revenue
- Reports
- Staff Management
- Search

# Hackathon Demonstration

The project is demonstrated live using **XAMPP** during the hackathon presentation.

Recommended flow:

```text
Admin Login
     ↓
Dashboard
     ↓
Floor Management
     ↓
Parking Slots
     ↓
Vehicle Entry
     ↓
Slot Allocation
     ↓
Vehicle Exit
     ↓
Receipt
     ↓
Revenue
     ↓
Reports
```

# Future Scope

Possible future enhancements:

- AI-based parking prediction
- Smart parking availability prediction
- Mobile application
- Online parking reservation
- QR-based vehicle entry
- IoT sensor integration
- Number plate recognition
- Real-time notifications
- Cloud deployment
- Digital payment integration
- Advanced analytics

# Developer

**Harsh Singh**

B.Tech – Computer Science Engineering

### Skills

- Full Stack Development
- PHP
- MySQL
- JavaScript
- HTML
- CSS
- AJAX
- Git & GitHub

# License

This project is developed for educational, demonstration and hackathon purposes.

---

**Multi-Level Smart Parking Management System**  
Built with Core PHP, MySQL, JavaScript and AJAX.
