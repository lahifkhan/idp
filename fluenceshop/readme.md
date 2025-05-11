# FluenceShop - eCommerce Website for Influencers

FluenceShop is a PHP-based eCommerce platform designed specifically for influencers to purchase equipment and accessories for content creation.

## Features

- User registration and authentication system
- Product catalog with search and filter functionality
- Shopping cart system
- Checkout process
- Order management
- User profile management
- Product reviews and ratings
- Responsive design for all devices

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- XAMPP, WAMP, MAMP, or any PHP development environment

## Installation

1. **Clone or download the repository**

2. **Move the project to your local server directory**
   - For XAMPP: Move the project folder to `htdocs` directory
   - For WAMP: Move the project folder to `www` directory
   - For MAMP: Move the project folder to `htdocs` directory

3. **Create a database**
   - Open phpMyAdmin (usually at http://localhost/phpmyadmin)
   - Create a new database named `fluenceshop`
   - Import the database schema from `database/fluenceshop.sql`

4. **Configure database connection**
   - Open `includes/db_connect.php`
   - Update the database credentials if needed:
     ```php
     $servername = "localhost";
     $username = "root";
     $password = ""; // Update if your MySQL has a password
     $dbname = "fluenceshop";
     ```

5. **Start your local server (XAMPP, WAMP, MAMP, etc.)**

6. **Access the website**
   - Open your browser and navigate to `http://localhost/fluenceshop`

## Directory Structure

```
fluenceshop/
├── assets/
│   ├── css/
│   │   ├── style.css
│   │   └── responsive.css
│   ├── js/
│   │   └── main.js
│   └── images/
├── database/
│   └── fluenceshop.sql
├── includes/
│   ├── cart_actions.php
│   ├── db_connect.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── logout.php
├── index.php
├── products.php
├── product.php
├── cart.php
├── checkout.php
├── login.php
├── register.php
├── profile.php
└── readme.md
```

## Default User Accounts

For testing purposes, the database comes with three pre-configured user accounts:

1. **Username:** johndoe
   **Email:** john@example.com
   **Password:** password123

2. **Username:** janedoe
   **Email:** jane@example.com
   **Password:** password123

3. **Username:** sarasmith
   **Email:** sara@example.com
   **Password:** password123

## Demo Products and Categories

The database includes sample products in the following categories:
- Cameras
- Lighting
- Audio Equipment
- Tripods & Stabilizers
- Accessories

## License

This project is available for personal and commercial use.

## Support

If you encounter any issues or have questions, please contact support@fluenceshop.com.