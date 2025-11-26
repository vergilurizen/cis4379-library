# Library Management System

A PHP-based library management system with backend APIs and a web interface.

## Features

- User authentication (login/register)
- Role-based access (admin/user)
- Browse available library materials
- Shopping cart functionality
- Material rental/borrowing system
- User rental history ("My Rentals")
- Admin panel for managing materials
- Admin order management (view and update rental statuses)
- RESTful PHP APIs for all operations
- CSV export functionality

## Technology Stack

- **Backend:** PHP
- **Database:** MySQL
- **Server:** MAMP
- **Architecture:** Shared functions with API endpoints

## Project Structure

```
public/
├── api/                        # Backend REST APIs
│   ├── auth.php               # Authentication endpoints
│   ├── materials.php          # Materials CRUD endpoints
│   ├── rentals.php            # Rental/order endpoints
│   ├── users.php              # User info endpoints
│   └── helpers.php            # API utilities
├── lib/                        # Shared libraries
│   └── database_functions.php # Core database functions
├── css/                        # Styles
├── index.php                  # Login page
├── register.php               # Registration page
├── catalog.php                # Browse materials
├── cart.php                   # Shopping cart
├── my_rentals.php             # User rental history
├── admin.php                  # Admin panel
├── admin_orders.php           # Admin order management
├── logout.php                 # Logout
├── export.php                 # CSV export
└── config.php                 # Database configuration

sql/
└── db_lms.sql                 # Database schema
```

## Setup

1. **Start MAMP**
   - Open MAMP and start servers

2. **Create Database**
   - Open phpMyAdmin
   - Create database: `db_lms`
   - Import: `sql/db_lms.sql`

3. **Configure Database**
   - Edit `public/config.php`
   - Update credentials if needed (default: root/root)

4. **Access Application**
   - URL: `http://localhost/index.php`
   - Default admin: `admin` / `password`

## API Endpoints

### Authentication
- `POST /api/auth.php?action=login` - User login
- `POST /api/auth.php?action=register` - User registration
- `POST /api/auth.php?action=logout` - User logout

### Materials
- `GET /api/materials.php` - List all materials
- `GET /api/materials.php?available=1` - List available materials
- `GET /api/materials.php?id=X` - Get single material
- `POST /api/materials.php` - Add material (admin only)
- `PUT /api/materials.php` - Update material (admin only)
- `DELETE /api/materials.php?id=X` - Delete material (admin only)

### Users
- `GET /api/users.php` - Get current user info

### Rentals/Orders
- `GET /api/rentals.php` - Get current user's rentals (requires login)
- `GET /api/rentals.php?all=1` - Get all rentals (admin only)
- `GET /api/rentals.php?id=X` - Get single rental by ID (owner or admin)
- `POST /api/rentals.php` - Rent materials (checkout) - requires login
  - Body: `{"material_ids": [1, 2, 3]}`
- `PUT /api/rentals.php` - Update rental status (admin only)
  - Body: `{"rental_id": 1, "status": "Delivered"}` or `{"rental_id": 1, "status": "Returned"}`
  - Allowed statuses: `Pending`, `Delivered`, `Returned`

All API endpoints return JSON in the format:
```json
{
  "success": true/false,
  "data": {...},
  "message": "..."
}
```

## Architecture

The application uses a **shared functions architecture**:
- `lib/database_functions.php` contains all database operations
- Frontend pages include and call these functions directly
- API endpoints also use the same functions but return JSON responses
- No HTTP/cURL overhead between PHP components

## Default Credentials

**Admin Account:**
- Username: `admin`
- Password: `password`

## Database Schema

The database consists of four main tables:

- **users** - User accounts with roles (admin/user)
  - Stores username, password (bcrypt hashed), and role
- **materials** - Library materials catalog
  - Stores title, author, category, and availability status
- **borrowed_materials** - Rental/borrowing records
  - Tracks user rentals with status (Pending/Delivered/Returned)
  - Links users to materials with timestamps
- **notifications** - User notifications (reserved for future use)

## Group Project

Repository for CIS4379 group project.

