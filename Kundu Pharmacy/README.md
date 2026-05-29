# Kundu Pharmacy – Medicine Delivery System

A simple PHP + MySQL medicine delivery web application for a small pharmacy.

## Features

- **Customer**: Register, login, place orders with prescription uploads, track order status
- **Admin**: Add villages/medicines, view & manage orders, update delivery status
- **Payment**: Cash on Delivery only

## Requirements

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
- A modern web browser

## Setup Instructions (XAMPP)

### 1. Copy the Project

Copy the entire `Kundu Pharmacy` folder to your XAMPP htdocs directory:

```
C:\xampp\htdocs\kundu_pharmacy
```

Or create a symbolic link / rename the folder as needed.

### 2. Start XAMPP

Open **XAMPP Control Panel** and start:
- **Apache**
- **MySQL**

### 3. Create the Database

1. Open **phpMyAdmin** at [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click **"New"** in the sidebar to create a new database
3. Enter database name: `kundu_pharmacy`
4. Click **"Create"**
5. Click the **"Import"** tab
6. Click **"Choose File"** and select `database.sql` from the project folder
7. Click **"Go"** to import

### 4. Open the Application

Visit: [http://localhost/kundu_pharmacy/](http://localhost/kundu_pharmacy/)

## Default Admin Login

| Field    | Value      |
|----------|------------|
| Username | `admin`    |
| Password | `admin123` |

## Folder Structure

```
kundu_pharmacy/
├── config/
│   └── db.php                 # Database connection
├── css/
│   └── style.css              # Stylesheet
├── uploads/                   # Prescription images
├── admin/
│   ├── admin_login.php
│   ├── admin_dashboard.php
│   ├── add_village.php
│   ├── add_medicine.php
│   ├── view_orders.php
│   └── update_order_status.php
├── index.php                  # Homepage
├── register.php               # Customer registration
├── login.php                  # Customer login
├── dashboard.php              # Customer dashboard
├── place_order.php            # Place new order
├── logout.php                 # Logout
├── database.sql               # SQL schema
└── README.md                  # This file
```

## Order Flow

### Customer
1. Register → Login → Dashboard → Place Order
2. Select village, upload prescription, pick medicines, enter address/phone
3. Order placed with status **"Pending"**

### Admin
1. Login → View Orders
2. **Confirm** order → Mark **"Out for Delivery"**
