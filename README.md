# CRI Travels - PHP MySQL Backend

## Setup Instructions

### 1. Database Setup
1. Start your MySQL server (XAMPP, WAMP, or standalone MySQL)
2. Open phpMyAdmin or MySQL command line
3. Import the database schema:
   ```
   mysql -u root -p < database.sql
   ```
   Or simply copy and paste the contents of `database.sql` into phpMyAdmin SQL tab

### 2. Configuration
1. Edit `config/database.php` and update the database credentials:
   - `DB_USER`: Your MySQL username (default: root)
   - `DB_PASS`: Your MySQL password (default: empty for XAMPP)
   - `DB_NAME`: Database name (default: cri_travels)

### 3. Default Admin Account
- **Email**: admin@critravels.com
- **Password**: admin123
- **User Type**: Admin

### 4. File Structure
```
/
├── config/
│   ├── database.php     # Database connection
│   ├── auth.php         # Authentication functions
│   └── logout.php       # Logout handler
├── admin/
│   ├── dashboard.php    # Admin dashboard
│   ├── manage_clients.php
│   ├── manage_drivers.php
│   └── manage_trips.php
├── login.php            # Login page for all user types
├── register.php         # Registration page
├── database.sql         # Database schema
└── [existing HTML files]
```

### 5. Features

#### User Types:
1. **Client**: Can book trips and view their bookings
2. **Driver**: Can view assigned trips
3. **Admin**: Full access to manage clients, drivers, and trips

#### Admin Features:
- View dashboard with statistics
- Manage all clients (view, edit, delete)
- Manage all drivers (view, edit, delete)
- Manage all trips (assign drivers, update status)

### 6. Integration with Existing Website
- Update navigation links in your HTML files to include:
  - Link to `login.php` for user login
  - Link to `register.php` for new user registration

### 7. Security Features
- Password hashing using PHP's `password_hash()` and `password_verify()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Role-based access control

### 8. Next Steps
1. Set up a web server (Apache/Nginx)
2. Place all files in the web root directory
3. Configure PHP to enable sessions
4. Test the login system with the default admin account
5. Create test client and driver accounts
6. Start managing your travel business!

### 9. Recommended Enhancements
- Add email verification for new registrations
- Implement password reset functionality
- Add trip booking form for clients
- Create driver dashboard to view assigned trips
- Add payment integration
- Implement real-time notifications
