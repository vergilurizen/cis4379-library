# Library Management System

A PHP-based library management system with backend APIs and a web interface.

## Features

- User authentication (login/register)
- Role-based access (admin/user)
- Browse available library materials
- Admin panel for managing materials
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
│   ├── users.php              # User info endpoints
│   └── helpers.php            # API utilities
├── lib/                        # Shared libraries
│   └── database_functions.php # Core database functions
├── css/                        # Styles
├── index.php                  # Login page
├── register.php               # Registration page
├── catalog.php                # Browse materials
├── admin.php                  # Admin panel
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

- **users** - User accounts with roles
- **materials** - Library materials catalog
- **borrowed_materials** - Borrowing history (future use)
- **notifications** - User notifications (future use)

## Group Project

Repository for CIS4379 group project.

